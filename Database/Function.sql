USE `FDN`;
SET AUTOCOMMIT = false;
SELECT @@autocommit;

-- ===================
-- Password Encryption
-- ===================
-- Doctor table triggers
DROP TRIGGER IF EXISTS trgBeforeInsertDoctor;

DELIMITER $$
CREATE TRIGGER trgBeforeInsertDoctor
BEFORE INSERT ON Doctor
FOR EACH ROW
BEGIN
    -- Hash the incoming plaintext password using SHA2 (256-bit)
    SET NEW.Password = SHA2(NEW.Password, 256);
END $$
DELIMITER ;

-- Update trigger for Doctor table
DROP TRIGGER IF EXISTS trgBeforeUpdateDoctor;

DELIMITER $$
CREATE TRIGGER trgBeforeUpdateDoctor
BEFORE UPDATE ON Doctor
FOR EACH ROW
BEGIN
    -- Re-hash password if it is changed
    IF NEW.Password <> OLD.Password THEN
        SET NEW.Password = SHA2(NEW.Password, 256);
    END IF;
END $$
DELIMITER ;

-- Patient table triggers
DROP TRIGGER IF EXISTS trgBeforeInsertPatient;

DELIMITER $$
CREATE TRIGGER trgBeforeInsertPatient
BEFORE INSERT ON Patient
FOR EACH ROW
BEGIN
    SET NEW.Password = SHA2(NEW.Password, 256);
END $$
DELIMITER ;

-- Update trigger for Patient table
DROP TRIGGER IF EXISTS trgBeforeUpdatePatient;

DELIMITER $$
CREATE TRIGGER trgBeforeUpdatePatient
BEFORE UPDATE ON Patient
FOR EACH ROW
BEGIN
    IF NEW.Password <> OLD.Password THEN
        SET NEW.Password = SHA2(NEW.Password, 256);
    END IF;
END $$
DELIMITER ;

-- Testing
INSERT INTO Doctor (DoctorName, ContactInfo, Workshift, Department, Username, Password)
VALUES ('Shane Han', 'shane@example.com', 'morning', 'Cardiology', 'docShane', 'docpass789');
SELECT DoctorID, Username, Password FROM Doctor WHERE Username = 'docShane';

INSERT INTO Patient (PatientName, ContactInfo, Gender, Username, Password)
VALUES ('Bob Marley', 'bob@example.com', 'Male', 'patBob', 'patpass456');
SELECT PatientID, Username, Password FROM Patient WHERE Username = 'patBob';

-- Update password for doctor
UPDATE Doctor
SET Password = 'newdoctorpass'
WHERE Username = 'docShane';
SELECT DoctorID, Username, Password FROM Doctor WHERE Username = 'docShane';

-- Update password for patient
UPDATE Patient
SET Password = 'newpatientpass'
WHERE Username = 'patBob';
SELECT PatientID, Username, Password FROM Patient WHERE Username = 'patBob';

-- ======================================
-- User Access Function
-- ======================================
-- Sample Data --
INSERT INTO Doctor (DoctorName, ContactInfo, Workshift, Department, Username, Password) VALUES
('Alice Smith', 'alice@example.com', 'morning', 'Cardiology', 'docAlice', 'docpass1'),
('John Doe', 'john@example.com', 'evening', 'Neurology', 'docJohn', 'docpass2');

INSERT INTO Patient (PatientName, ContactInfo, Gender, Username, Password) VALUES
('Bob Lee', 'bob@example.com', 'Male', 'patBob', 'patpass1'),
('Mary Jane', 'mary@example.com', 'Female', 'patMary', 'patpass2');

INSERT INTO Medication (MedicationName, Efficacy) VALUES
('MedX', 'Relieves pain and inflammation'),
('MedY', 'Antibacterial for vaginal use');

INSERT INTO Prescription (PatientID, DoctorID, MedicationID, Dosage, FrequencyInterval, FrequencyUnit, FrequencyTimes, Status, Type, Description, StartDate, EndDate) 
VALUES
(339, 40, 614, '1 rectal foam application', 2, 'week', '12:00, 18:00', 'inactive', 'repeat', 'Take two tablets every 4 hours', '2025-05-17 14:00:00', '2025-09-12 14:00:00'),
(213, 24, 550, '1 vaginal tablet', NULL, NULL, '2025-09-21 08:00, 2025-09-25 08:00, 2025-09-29 08:00', 'inactive', 'custom', 'Shake well before using', '2025-03-12 18:00:30', '2025-10-10 18:00:30');

INSERT INTO Symptom (PatientID, SymptomName, Description) VALUES
(339, 'Headache','Severe headache for 3 days'),
(213, 'Fever','High temperature, chills');

INSERT INTO Reminder (PatientID, PrescriptionID, ScheduledTime, TakenAt, Status) VALUES
(339, 1, '2025-05-17 12:00:00', NULL, 'pending'),
(213, 2, '2025-09-21 08:00:00', NULL, 'pending');

INSERT INTO SideEffect (ReminderID, SideEffectName, Description) VALUES
(1, 'Nausea', 'Feeling nauseous after using MedX'),
(2, 'Drowsiness', 'Sleepiness reported after using MedY');

-- Roles & Privileges --
CREATE ROLE IF NOT EXISTS appDoctor;
CREATE ROLE IF NOT EXISTS appPatient;

CREATE USER IF NOT EXISTS 'doctorUser'@'localhost' IDENTIFIED BY 'doctorUserPwd';
CREATE USER IF NOT EXISTS 'patientUser'@'localhost' IDENTIFIED BY 'patientUserPwd';

GRANT appDoctor TO 'doctorUser'@'localhost';
GRANT appPatient TO 'patientUser'@'localhost';

-- Doctor privileges --
GRANT SELECT ON FDN.Patient TO appDoctor;
GRANT SELECT ON FDN.Symptom TO appDoctor;
GRANT SELECT ON FDN.SideEffect TO appDoctor;
GRANT SELECT ON FDN.Medication TO appDoctor;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.Prescription TO appDoctor;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.Reminder TO appDoctor;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.Doctor TO appDoctor;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.PrescriptionHistory TO appDoctor;

-- Patient privileges --
GRANT SELECT ON FDN.Prescription TO appPatient;
GRANT SELECT ON FDN.PrescriptionHistory TO appPatient;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.Patient TO appPatient;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.Symptom TO appPatient;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.SideEffect TO appPatient;
GRANT SELECT,INSERT,UPDATE,DELETE ON FDN.Reminder TO appPatient;

SET DEFAULT ROLE appDoctor TO 'doctorUser'@'localhost';
SET DEFAULT ROLE appPatient TO 'patientUser'@'localhost';
FLUSH PRIVILEGES;

-- Authenticate & Authorize Procedure --
DROP PROCEDURE IF EXISTS uspAuthenticateAndAuthorize;
DELIMITER $$
CREATE PROCEDURE uspAuthenticateAndAuthorize(
    IN inRole VARCHAR(10),
    IN inUsername VARCHAR(64),
    IN inPassword VARCHAR(256),
    IN inAction VARCHAR(20),
    IN inTable VARCHAR(64),
    OUT outAllowed TINYINT,
    OUT outMessage VARCHAR(255)
)
authBlock: BEGIN
    DECLARE vCount INT DEFAULT 0;
    DECLARE act VARCHAR(20);

    SET outAllowed = 0;
    SET outMessage = 'Denied by default';
    SET act = UPPER(TRIM(inAction));

    IF act = 'READ' THEN
        SET act = 'SELECT';
    END IF;

    IF inRole NOT IN ('doctor','patient') THEN
        SET outMessage = 'Invalid role';
        LEAVE authBlock;
    END IF;

    IF inRole = 'doctor' THEN
        SELECT COUNT(*) INTO vCount FROM Doctor WHERE Username = inUsername AND Password = SHA2(inPassword, 256);
        IF vCount = 0 THEN
            SET outMessage = 'Authentication failed for doctor';
            LEAVE authBlock;
        END IF;
    ELSE
        SELECT COUNT(*) INTO vCount FROM Patient WHERE Username = inUsername AND Password = SHA2(inPassword, 256);
        IF vCount = 0 THEN
            SET outMessage = 'Authentication failed for patient';
            LEAVE authBlock;
        END IF;
    END IF;

    IF inRole = 'doctor' THEN
        IF (act = 'SELECT' AND inTable IN ('Patient','Symptom','SideEffect','Medication')) OR
           (inTable IN ('Prescription','Reminder','Doctor','PrescriptionHistory') AND act IN ('INSERT','UPDATE','DELETE','CREATE','SELECT')) THEN
            SET outAllowed = 1;
            SET outMessage = CONCAT('Allowed: doctor ', act, ' on ', inTable);
            LEAVE authBlock;
        END IF;
        SET outMessage = 'Doctor not allowed that action on that table';
        LEAVE authBlock;
    END IF;

    IF inRole = 'patient' THEN
        IF (act = 'SELECT' AND inTable IN ('Prescription','PrescriptionHistory')) OR
           (inTable IN ('Patient','Symptom','SideEffect','Reminder') AND act IN ('INSERT','UPDATE','DELETE','CREATE','SELECT')) THEN
            SET outAllowed = 1;
            SET outMessage = CONCAT('Allowed: patient ', act, ' on ', inTable);
            LEAVE authBlock;
        END IF;
        SET outMessage = 'Patient not allowed that action on that table';
        LEAVE authBlock;
    END IF;

END $$
DELIMITER ;

-- Helper Procedure --
DROP PROCEDURE IF EXISTS uspCheckLogin;
DELIMITER $$
CREATE PROCEDURE uspCheckLogin(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    OUT ok TINYINT,
    OUT msg VARCHAR(255)
)
checkBlock: BEGIN
    CALL uspAuthenticateAndAuthorize(role, username, password, 'SELECT', 'Prescription', ok, msg);
END $$
DELIMITER ;

-- CRUD Procedures --
-- Doctor CRUD
DROP PROCEDURE IF EXISTS uspDoctorCRUD;
DELIMITER $$
CREATE PROCEDURE uspDoctorCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN name VARCHAR(40),
    IN contact VARCHAR(40),
    IN workshift VARCHAR(20),
    IN department VARCHAR(40),
    IN userField VARCHAR(40),
    IN passField VARCHAR(256)
)
docBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'Doctor', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE docBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO Doctor (DoctorName, ContactInfo, Workshift, Department, Username, Password)
        VALUES (name, contact, workshift, department, userField, passField);
        SELECT LAST_INSERT_ID() AS InsertedID;
    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT * FROM Doctor;
        ELSE
            SELECT * FROM Doctor WHERE DoctorID = id;
        END IF;
    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE Doctor
        SET DoctorName = COALESCE(NULLIF(name,''), DoctorName),
            ContactInfo = COALESCE(NULLIF(contact,''), ContactInfo),
            Workshift = COALESCE(NULLIF(workshift,''), Workshift),
            Department = COALESCE(NULLIF(department,''), Department),
            Username = COALESCE(NULLIF(userField,''), Username),
            Password = COALESCE(NULLIF(passField,''), Password)
        WHERE DoctorID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM Doctor WHERE DoctorID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- Patient CRUD
DROP PROCEDURE IF EXISTS uspPatientCRUD;
DELIMITER $$
CREATE PROCEDURE uspPatientCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN name VARCHAR(40),
    IN contact VARCHAR(40),
    IN gender VARCHAR(10),
    IN userField VARCHAR(40),
    IN passField VARCHAR(256)
)
patientBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'Patient', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE patientBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO Patient (PatientName, ContactInfo, Gender, Username, Password)
        VALUES (name, contact, gender, userField, passField);
        SELECT LAST_INSERT_ID() AS InsertedID;
    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT * FROM Patient;
        ELSE
            SELECT * FROM Patient WHERE PatientID = id;
        END IF;
    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE Patient
        SET PatientName = COALESCE(NULLIF(name,''), PatientName),
            ContactInfo = COALESCE(NULLIF(contact,''), ContactInfo),
            Gender = COALESCE(NULLIF(gender,''), Gender),
            Username = COALESCE(NULLIF(userField,''), Username),
            Password = COALESCE(NULLIF(passField,''), Password)
        WHERE PatientID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM Patient WHERE PatientID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- Prescription CRUD
DROP PROCEDURE IF EXISTS uspPrescriptionCRUD;
DELIMITER $$
CREATE PROCEDURE uspPrescriptionCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN patientId INT,
    IN doctorId INT,
    IN medicationId INT,
    IN dosage VARCHAR(40),
    IN freqInterval INT,
    IN freqUnit VARCHAR(10),
    IN freqTimes VARCHAR(100),
    IN status VARCHAR(20),
    IN type VARCHAR(20),
    IN description VARCHAR(255),
    IN startDate DATETIME,
    IN endDate DATETIME
)
presBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'Prescription', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE presBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO Prescription (PatientID, DoctorID, MedicationID, Dosage, FrequencyInterval, FrequencyUnit, FrequencyTimes, Status, Type, Description, StartDate, EndDate)
        VALUES (patientId, doctorId, medicationId, dosage, freqInterval, freqUnit, freqTimes, status, type, description, startDate, endDate);
        SELECT LAST_INSERT_ID() AS InsertedID;
    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT p.*, d.DoctorName, pt.PatientName, m.MedicationName
            FROM Prescription p
            LEFT JOIN Doctor d ON p.DoctorID = d.DoctorID
            LEFT JOIN Patient pt ON p.PatientID = pt.PatientID
            LEFT JOIN Medication m ON p.MedicationID = m.MedicationID;
        ELSE
            SELECT p.*, d.DoctorName, pt.PatientName, m.MedicationName
            FROM Prescription p
            LEFT JOIN Doctor d ON p.DoctorID = d.DoctorID
            LEFT JOIN Patient pt ON p.PatientID = pt.PatientID
            LEFT JOIN Medication m ON p.MedicationID = m.MedicationID
            WHERE p.PrescriptionID = id;
        END IF;
    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE Prescription
        SET PatientID = COALESCE(patientId, PatientID),
            DoctorID = COALESCE(doctorId, DoctorID),
            MedicationID = COALESCE(medicationId, MedicationID),
            Dosage = COALESCE(NULLIF(dosage,''), Dosage),
            FrequencyInterval = COALESCE(freqInterval, FrequencyInterval),
            FrequencyUnit = COALESCE(NULLIF(freqUnit,''), FrequencyUnit),
            FrequencyTimes = COALESCE(NULLIF(freqTimes,''), FrequencyTimes),
            Status = COALESCE(NULLIF(status,''), Status),
            Type = COALESCE(NULLIF(type,''), Type),
            Description = COALESCE(NULLIF(description,''), Description),
            StartDate = COALESCE(startDate, StartDate),
            EndDate = COALESCE(endDate, EndDate)
        WHERE PrescriptionID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM Prescription WHERE PrescriptionID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- PrescriptionHistory CRUD
DROP PROCEDURE IF EXISTS uspPrescriptionHistoryCRUD;
DELIMITER $$
CREATE PROCEDURE uspPrescriptionHistoryCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN prescriptionId INT,
    IN patientId INT,
    IN doctorId INT,
    IN medicationId INT,
    IN dosage VARCHAR(40),
    IN freqInterval INT,
    IN freqUnit VARCHAR(10),
    IN freqTimes VARCHAR(100),
    IN status VARCHAR(20),
    IN type VARCHAR(20),
    IN description VARCHAR(255),
    IN startDate DATETIME,
    IN endDate DATETIME,
    IN changeDate DATETIME,
    IN changeReason VARCHAR(255)
)
histBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'PrescriptionHistory', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE histBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO PrescriptionHistory (
            PrescriptionID, PatientID, DoctorID, MedicationID, Dosage,
            FrequencyInterval, FrequencyUnit, FrequencyTimes, Status,
            StartDate, EndDate, Type, Description, ChangeDate, ChangeReason
        )
        VALUES (
            prescriptionId, patientId, doctorId, medicationId, dosage,
            freqInterval, freqUnit, freqTimes, status,
            startDate, endDate, type, description, changeDate, changeReason
        );
        SELECT LAST_INSERT_ID() AS InsertedID;

    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT ph.*, d.DoctorName, pt.PatientName, m.MedicationName
            FROM PrescriptionHistory ph
            LEFT JOIN Doctor d ON ph.DoctorID = d.DoctorID
            LEFT JOIN Patient pt ON ph.PatientID = pt.PatientID
            LEFT JOIN Medication m ON ph.MedicationID = m.MedicationID;
        ELSE
            SELECT ph.*, d.DoctorName, pt.PatientName, m.MedicationName
            FROM PrescriptionHistory ph
            LEFT JOIN Doctor d ON ph.DoctorID = d.DoctorID
            LEFT JOIN Patient pt ON ph.PatientID = pt.PatientID
            LEFT JOIN Medication m ON ph.MedicationID = m.MedicationID
            WHERE ph.HistoryID = id;
        END IF;

    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE PrescriptionHistory
        SET PrescriptionID = COALESCE(prescriptionId, PrescriptionID),
            PatientID = COALESCE(patientId, PatientID),
            DoctorID = COALESCE(doctorId, DoctorID),
            MedicationID = COALESCE(medicationId, MedicationID),
            Dosage = COALESCE(NULLIF(dosage,''), Dosage),
            FrequencyInterval = COALESCE(freqInterval, FrequencyInterval),
            FrequencyUnit = COALESCE(NULLIF(freqUnit,''), FrequencyUnit),
            FrequencyTimes = COALESCE(NULLIF(freqTimes,''), FrequencyTimes),
            Status = COALESCE(NULLIF(status,''), Status),
            StartDate = COALESCE(startDate, StartDate),
            EndDate = COALESCE(endDate, EndDate),
            Type = COALESCE(NULLIF(type,''), Type),
            Description = COALESCE(NULLIF(description,''), Description),
            ChangeDate = COALESCE(changeDate, ChangeDate),
            ChangeReason = COALESCE(NULLIF(changeReason,''), ChangeReason)
        WHERE HistoryID = id;
        SELECT ROW_COUNT() AS AffectedRows;

    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM PrescriptionHistory WHERE HistoryID = id;
        SELECT ROW_COUNT() AS AffectedRows;

    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- Medication CRUD
DROP PROCEDURE IF EXISTS uspMedicationCRUD;
DELIMITER $$
CREATE PROCEDURE uspMedicationCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN name VARCHAR(40),
    IN efficacy VARCHAR(40)
)
medBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'Medication', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE medBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO Medication (MedicationName, Efficacy)
        VALUES (name, efficacy);
        SELECT LAST_INSERT_ID() AS InsertedID;

    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT * FROM Medication;
        ELSE
            SELECT * FROM Medication WHERE MedicationID = id;
        END IF;

    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE Medication
        SET MedicationName = COALESCE(NULLIF(name,''), MedicationName),
            Efficacy = COALESCE(NULLIF(efficacy,''), Efficacy)
        WHERE MedicationID = id;
        SELECT ROW_COUNT() AS AffectedRows;

    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM Medication WHERE MedicationID = id;
        SELECT ROW_COUNT() AS AffectedRows;

    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- Symptom CRUD
DROP PROCEDURE IF EXISTS uspSymptomCRUD;
DELIMITER $$
CREATE PROCEDURE uspSymptomCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN patientId INT,
    IN name VARCHAR(40),
    IN description VARCHAR(256)
)
symBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'Symptom', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE symBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO Symptom (PatientID, SymptomName, Description)
        VALUES (patientId, name, description);
        SELECT LAST_INSERT_ID() AS InsertedID;
    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT * FROM Symptom;
        ELSE
            SELECT * FROM Symptom WHERE SymptomID = id;
        END IF;
    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE Symptom
        SET PatientID = COALESCE(patientId, PatientID),
            SymptomName = COALESCE(NULLIF(name,''), SymptomName),
            Description = COALESCE(NULLIF(description,''), Description)
        WHERE SymptomID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM Symptom WHERE SymptomID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- Reminder CRUD
DROP PROCEDURE IF EXISTS uspReminderCRUD;
DELIMITER $$
CREATE PROCEDURE uspReminderCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN patientId INT,
    IN prescriptionId INT,
    IN scheduled DATETIME,
    IN taken DATETIME,
    IN status VARCHAR(20)
)
remBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'Reminder', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE remBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO Reminder (PatientID, PrescriptionID, ScheduledTime, TakenAt, Status)
        VALUES (patientId, prescriptionId, scheduled, taken, status);
        SELECT LAST_INSERT_ID() AS InsertedID;
    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT * FROM Reminder;
        ELSE
            SELECT * FROM Reminder WHERE ReminderID = id;
        END IF;
    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE Reminder
        SET PatientID = COALESCE(patientId, PatientID),
            PrescriptionID = COALESCE(prescriptionId, PrescriptionID),
            ScheduledTime = COALESCE(scheduled, ScheduledTime),
            TakenAt = COALESCE(taken, TakenAt),
            Status = COALESCE(NULLIF(status,''), Status)
        WHERE ReminderID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM Reminder WHERE ReminderID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- SideEffect CRUD
DROP PROCEDURE IF EXISTS uspSideEffectCRUD;
DELIMITER $$
CREATE PROCEDURE uspSideEffectCRUD(
    IN role VARCHAR(10),
    IN username VARCHAR(64),
    IN password VARCHAR(256),
    IN action VARCHAR(10),
    IN id INT,
    IN reminderId INT,
    IN name VARCHAR(40),
    IN description VARCHAR(256)
)
seBlock: BEGIN
    DECLARE allowed TINYINT DEFAULT 0;
    DECLARE msg VARCHAR(255) DEFAULT '';

    CALL uspAuthenticateAndAuthorize(role, username, password, action, 'SideEffect', allowed, msg);
    IF allowed = 0 THEN
        SELECT msg AS ErrorMessage;
        LEAVE seBlock;
    END IF;

    IF UPPER(action) = 'CREATE' THEN
        INSERT INTO SideEffect (ReminderID, SideEffectName, Description)
        VALUES (reminderId, name, description);
        SELECT LAST_INSERT_ID() AS InsertedID;
    ELSEIF UPPER(action) IN ('READ','SELECT') THEN
        IF id IS NULL OR id = 0 THEN
            SELECT * FROM SideEffect;
        ELSE
            SELECT * FROM SideEffect WHERE SideEffectID = id;
        END IF;
    ELSEIF UPPER(action) = 'UPDATE' THEN
        UPDATE SideEffect
        SET ReminderID = COALESCE(reminderId, ReminderID),
            SideEffectName = COALESCE(NULLIF(name,''), SideEffectName),
            Description = COALESCE(NULLIF(description,''), Description)
        WHERE SideEffectID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSEIF UPPER(action) = 'DELETE' THEN
        DELETE FROM SideEffect WHERE SideEffectID = id;
        SELECT ROW_COUNT() AS AffectedRows;
    ELSE
        SELECT 'Unsupported action' AS ErrorMessage;
    END IF;
END $$
DELIMITER ;

-- Testing CRUD Operations --
-- DOCTOR TESTS --
-- Doctor reads Patient (allowed)
CALL uspPatientCRUD('doctor','docAlice','docpass1','READ', 1, NULL, NULL, NULL, NULL, NULL);

-- Doctor creates a Prescription (allowed)
CALL uspPrescriptionCRUD('doctor','docAlice','docpass1','CREATE', NULL, 213, 24, 550, '1 vaginal tablet', NULL, NULL, '2025-09-21 08:00, 2025-09-25 08:00, 2025-09-29 08:00', 'inactive', 'custom', 'Shake well before using', '2025-03-12 18:00:30', '2025-10-10 18:00:30');
SELECT 'After CREATE Prescription by doctor' AS Info;
SELECT * FROM Prescription;

-- Doctor updates a Prescription (allowed)
CALL uspPrescriptionCRUD('doctor','docAlice','docpass1','UPDATE', 2, 213, 24, 550, '2 vaginal tablets', NULL, NULL, NULL, 'active', 'custom', 'Updated dosage instructions', '2025-03-12 18:00:30', '2025-10-10 18:00:30');
SELECT 'After UPDATE Prescription by doctor' AS Info;
SELECT * FROM Prescription WHERE PrescriptionID = 2;

-- Doctor reads SideEffect (allowed to view)
CALL uspSideEffectCRUD('doctor','docAlice','docpass1','READ', 1, NULL, NULL, NULL);

-- Doctor creates a Doctor (allowed)
CALL uspDoctorCRUD('doctor','docAlice','docpass1','CREATE', NULL, 'Greg House', 'greg@example.com', 'morning', 'Diagnostics', 'greg', 'gregpass');
SELECT 'After CREATE Doctor by doctor' AS Info;
SELECT * FROM Doctor;

-- Doctor reads Medication (allowed)
CALL uspMedicationCRUD('doctor','docAlice','docpass1','READ', NULL, NULL, NULL);

-- Doctor updates PrescriptionHistory (allowed)
CALL uspPrescriptionHistoryCRUD('doctor','docAlice','docpass1','UPDATE', 1, 4, 15, 18, 396, '2 capsules',  NULL, NULL, NULL, 'active', 'repeat', 'Correction: dosage clarified and dates adjusted','2025-03-12 18:00:30', '2025-10-10 18:00:30', NOW(), 'Correction');
SELECT 'After UPDATE PrescriptionHistory by doctor' AS Info;
SELECT * FROM PrescriptionHistory WHERE HistoryID = 1;

-- Doctor tries unauthorized action: attempt to DELETE Patient (denied)
CALL uspPatientCRUD('doctor','docAlice','docpass1','DELETE', 2, NULL, NULL, NULL, NULL, NULL);
SELECT 'After unauthorized DELETE Patient by doctor' AS Info;
SELECT * FROM Patient;

-- PATIENT TESTS --
-- Patient reads Prescription (allowed view)
CALL uspPrescriptionCRUD('patient','patBob','patpass1','READ', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- Patient updates own Patient record (allowed)
CALL uspPatientCRUD('patient','patBob','patpass1','UPDATE', 1, 'Bob Lee EDITED', 'bob.edit@example.com', 'Male', 'patBob', 'patpass1');
SELECT 'After UPDATE Patient by patient' AS Info;
SELECT * FROM Patient WHERE PatientID = 1;

-- Patient creates a Symptom (allowed)
CALL uspSymptomCRUD('patient','patBob','patpass1','CREATE', NULL, 1, 'Cough', 'Dry cough');
SELECT 'After CREATE Symptom by patient' AS Info;
SELECT * FROM Symptom;

-- Patient reads Symptom (allowed)
CALL uspSymptomCRUD('patient','patBob','patpass1','READ', 1, NULL, NULL, NULL);
SELECT 'After READ Symptom by patient' AS Info;
SELECT * FROM Symptom;

-- Patient reads Reminder (allowed)
CALL uspReminderCRUD('patient','patBob','patpass1','READ', 1, NULL, NULL, NULL, NULL, NULL);
SELECT 'After READ Reminder by patient' AS Info;
SELECT * FROM Reminder;

-- Patient tries unauthorized action: create Prescription (denied)
CALL uspPrescriptionCRUD('patient','patBob','patpass1',
    'CREATE', NULL, 1, 1, 1, '1 tablet', 1, 'day', 'morning', 'active', 'repeat', 'Attempt by patient', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)
);
SELECT 'After unauthorized CREATE Prescription by patient' AS Info;
SELECT * FROM Prescription;

-- ======================================
-- Search and Filter
-- ======================================
-- Doctor --
-- search for own profile (eg: workshift) --
SELECT * FROM Doctor
WHERE DoctorID = 45;

-- search for a specific patient + his/her full profile(contact info) --
-- search by patient name --
SELECT * FROM Patient WHERE PatientName = 'Tomas Vinten';
-- search by patient ID --
SELECT * FROM Patient WHERE PatientID = 123;

-- search for patient who missing up/follow the reminder --
SELECT * FROM Reminder WHERE PatientID = 52 AND Status = 'pending';
SELECT * FROM Reminder WHERE PatientID = 67 AND Status = 'missed';
SELECT * FROM Reminder WHERE PatientID = 89 AND Status = 'taken';

-- search for patient who has specific type of disease(symptoms) --
SELECT * FROM Symptom
WHERE PatientID = 123 AND SymptomName = 'Cough';

-- search for past prescription of a patient --
SELECT * FROM PrescriptionHistory
WHERE PatientID = 123;
-- current prescriptions for a patient --
SELECT * FROM Prescription
WHERE PatientID = 32;

-- search for patient's side effects --

-- Patient --
-- search for own profile (eg: patientID) --
SELECT * FROM Patient
WHERE PatientID = 64;

-- search for own prescription --
SELECT * FROM Prescription
WHERE PatientID = 12 AND Status = 'active';

SELECT * FROM Prescription
WHERE PatientID = 26 AND Status = 'inactive';

-- search for own reminder --
SELECT * FROM Reminder
WHERE PatientID = 39;

-- search for own symptoms --
SELECT * FROM Symptom
WHERE PatientID = 79;

-- =================================================================================
-- Auto Generate Reminder based on Prescription & Auto Update Prescription History
-- =================================================================================
-- Procedure
DELIMITER $$

DROP PROCEDURE IF EXISTS uspCreateRemindersForPrescription$$

CREATE PROCEDURE uspCreateRemindersForPrescription(IN p_presc_id INT)
BEGIN
    -- DECLARE VARIABLES
    DECLARE v_patient_id INT DEFAULT 0;
    DECLARE v_start DATETIME;
    DECLARE v_end DATETIME;
    DECLARE v_interval INT DEFAULT 1;
    DECLARE v_unit VARCHAR(16);
    DECLARE v_times_list TEXT;
    DECLARE v_type VARCHAR(16);
    DECLARE v_current_date DATE;
    DECLARE v_time_token VARCHAR(128);
    DECLARE v_scheduled DATETIME;
    DECLARE v_comma_pos INT;
    DECLARE v_unit_upper VARCHAR(16);
    DECLARE v_times_work TEXT;

    main_block: BEGIN

        -- LOAD PRESCRIPTION DATA
        SELECT PatientID, StartDate, EndDate, FrequencyInterval, FrequencyUnit, FrequencyTimes, `Type`
          INTO v_patient_id, v_start, v_end, v_interval, v_unit, v_times_list, v_type
        FROM Prescription
        WHERE PrescriptionID = p_presc_id
        LIMIT 1;

        -- EXIT IF PRESCRIPTION NOT FOUND
        IF v_patient_id IS NULL THEN
            LEAVE main_block;
        END IF;

        -- NORMALIZE VALUES
        SET v_times_list = COALESCE(v_times_list,'');
        SET v_unit_upper = UPPER(COALESCE(v_unit,''));

        -- CUSTOM TYPE
        IF v_type = 'custom' THEN
            WHILE CHAR_LENGTH(TRIM(v_times_list)) > 0 DO
                SET v_comma_pos = INSTR(v_times_list, ',');
                IF v_comma_pos > 0 THEN
                    SET v_time_token = TRIM(LEFT(v_times_list, v_comma_pos - 1));
                    SET v_times_list = TRIM(SUBSTRING(v_times_list, v_comma_pos + 1));
                ELSE
                    SET v_time_token = TRIM(v_times_list);
                    SET v_times_list = '';
                END IF;

                -- Convert string to datetime
                SET v_scheduled = STR_TO_DATE(v_time_token, '%Y-%m-%d %H:%i');

                -- Insert reminder only if scheduled datetime is within start/end
                IF v_scheduled IS NOT NULL THEN
                    IF (v_start IS NULL OR v_scheduled >= v_start) AND (v_end IS NULL OR v_scheduled <= v_end) THEN
                        INSERT INTO Reminder(PatientID, PrescriptionID, ScheduledTime, Status)
                        SELECT v_patient_id, p_presc_id, v_scheduled, 'pending'
                        FROM DUAL
                        WHERE NOT EXISTS (
                            SELECT 1 FROM Reminder r
                            WHERE r.PrescriptionID = p_presc_id AND r.ScheduledTime = v_scheduled
                        );
                    END IF;
                END IF;
            END WHILE;

            -- DONE WITH CUSTOM TYPE
            LEAVE main_block;
        END IF;

        -- REPEAT TYPE
        IF v_type = 'repeat' THEN
            -- Ensure interval and unit are valid
            IF v_interval IS NULL OR v_interval < 1 THEN
                SET v_interval = 1;
            END IF;

            IF v_unit_upper NOT IN ('DAY','WEEK','MONTH') THEN
                SET v_unit_upper = 'DAY';
            END IF;

            -- Default start/end dates
            IF v_start IS NULL THEN
                SET v_start = NOW();
            END IF;
            IF v_end IS NULL THEN
                SET v_end = v_start;
            END IF;

            SET v_current_date = DATE(v_start);

            WHILE v_current_date <= DATE(v_end) DO
                SET v_times_work = REPLACE(v_times_list,' ','');

                WHILE CHAR_LENGTH(v_times_work) > 0 DO
                    SET v_comma_pos = INSTR(v_times_work, ',');
                    IF v_comma_pos > 0 THEN
                        SET v_time_token = LEFT(v_times_work, v_comma_pos - 1);
                        SET v_times_work = SUBSTRING(v_times_work, v_comma_pos + 1);
                    ELSE
                        SET v_time_token = v_times_work;
                        SET v_times_work = '';
                    END IF;

                    SET v_scheduled = STR_TO_DATE(CONCAT(DATE_FORMAT(v_current_date, '%Y-%m-%d'), ' ', v_time_token), '%Y-%m-%d %H:%i');

                    IF v_scheduled IS NOT NULL AND v_scheduled >= v_start AND v_scheduled <= v_end THEN
                        INSERT INTO Reminder(PatientID, PrescriptionID, ScheduledTime, Status)
                        SELECT v_patient_id, p_presc_id, v_scheduled, 'pending'
                        FROM DUAL
                        WHERE NOT EXISTS (
                            SELECT 1 FROM Reminder r 
                            WHERE r.PrescriptionID = p_presc_id AND r.ScheduledTime = v_scheduled
                        );
                    END IF;
                END WHILE;

                -- Increment date based on unit
                IF v_unit_upper = 'DAY' THEN
                    SET v_current_date = DATE_ADD(v_current_date, INTERVAL v_interval DAY);
                ELSEIF v_unit_upper = 'WEEK' THEN
                    SET v_current_date = DATE_ADD(v_current_date, INTERVAL v_interval WEEK);
                ELSEIF v_unit_upper = 'MONTH' THEN
                    SET v_current_date = DATE_ADD(v_current_date, INTERVAL v_interval MONTH);
                ELSE
                    SET v_current_date = DATE_ADD(DATE(v_end), INTERVAL 1 DAY);
                END IF;

            END WHILE;
        END IF;

    END main_block;

END$$

-- Triggers

-- BEFORE INSERT: validate type-specific constraints
DROP TRIGGER IF EXISTS `trPrescriptionBeforeInsert`$$
CREATE TRIGGER `trPrescriptionBeforeInsert`
BEFORE INSERT ON `Prescription`
FOR EACH ROW
BEGIN
  IF NEW.`Type` IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Prescription.Type must be set to ''custom'' or ''repeat''.';
  END IF;

  IF NEW.`Type` = 'custom' THEN
    -- custom: do not set repeat fields; require Frequency_times (datetimes)
    IF NEW.FrequencyInterval IS NOT NULL OR NEW.FrequencyUnit IS NOT NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'For Type = ''custom'', FrequencyInterval and FrequencyUnit must be NULL.';
    END IF;
    IF NEW.FrequencyTimes IS NULL OR TRIM(NEW.FrequencyTimes) = '' THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'For Type = ''custom'', FrequencyTimes must contain one or more datetimes (YYYY-MM-DD HH:MM).';
    END IF;
  ELSEIF NEW.`Type` = 'repeat' THEN
    -- repeat: require interval, unit, times, start and end
    IF NEW.FrequencyInterval IS NULL OR NEW.FrequencyUnit IS NULL OR NEW.FrequencyTimes IS NULL OR NEW.StartDate IS NULL OR NEW.EndDate IS NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'For Type = ''repeat'', provide FrequencyInterval, FrequencyUnit, FrequencyTimes, StartDate and EndDate.';
    END IF;
  ELSE
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unknown Type. Allowed: ''custom'', ''repeat''.';
  END IF;
END$$

-- BEFORE UPDATE: validate and insert snapshot into history if OLD was active
DROP TRIGGER IF EXISTS `trgPrescriptionBeforeUpdate`$$
CREATE TRIGGER `trgPrescriptionBeforeUpdate`
BEFORE UPDATE ON `Prescription`
FOR EACH ROW
BEGIN
    -- Validate Type
    IF NEW.`Type` IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Prescription.Type must be set to ''custom'' or ''repeat''.';
    END IF;

    -- CUSTOM TYPE validation
    IF NEW.`Type` = 'custom' THEN
        IF NEW.FrequencyInterval IS NOT NULL OR NEW.FrequencyUnit IS NOT NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'For Type = ''custom'', FrequencyInterval and FrequencyUnit must be NULL.';
        END IF;

        IF NEW.FrequencyTimes IS NULL OR LENGTH(TRIM(NEW.FrequencyTimes)) = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'For Type = ''custom'', FrequencyTimes must contain one or more datetimes (YYYY-MM-DD HH:MM).';
        END IF;

    -- REPEAT TYPE validation
    ELSEIF NEW.`Type` = 'repeat' THEN
        IF NEW.FrequencyInterval IS NULL
           OR NEW.FrequencyUnit IS NULL
           OR NEW.FrequencyTimes IS NULL
           OR NEW.StartDate IS NULL
           OR NEW.EndDate IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'For Type = ''repeat'', provide FrequencyInterval, FrequencyUnit, FrequencyTimes, StartDate, and EndDate.';
        END IF;

    -- UNKNOWN TYPE
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Unknown Type. Allowed: ''custom'', ''repeat''.';
    END IF;

    -- Insert snapshot into PrescriptionHistory if OLD.Status was active
    IF OLD.Status = 'active' THEN
        INSERT INTO `PrescriptionHistory`
        (
            PrescriptionID,
            PatientID,
            DoctorID,
            MedicationID,
            Dosage,
            FrequencyInterval,
            FrequencyUnit,
            FrequencyTimes,
            Status,
            StartDate,
            EndDate,
            Type,
            Description,
            ChangeDate,
            ChangeReason
        )
        VALUES
        (
            OLD.PrescriptionID,
            OLD.PatientID,
            OLD.DoctorID,
            OLD.MedicationID,
            OLD.Dosage,
            OLD.FrequencyInterval,
            OLD.FrequencyUnit,
            OLD.FrequencyTimes,
            OLD.Status,
            OLD.StartDate,
            OLD.EndDate,
            OLD.Type,
            OLD.Description,
            NOW(),
            'Prescription updated'
        );
    ELSEIF OLD.Status = 'inactive' AND NEW.Status = 'active' THEN
        INSERT INTO `PrescriptionHistory`
        (
            PrescriptionID,
            PatientID,
            DoctorID,
            MedicationID,
            Dosage,
            FrequencyInterval,
            FrequencyUnit,
            FrequencyTimes,
            Status,
            StartDate,
            EndDate,
            Type,
            Description,
            ChangeDate,
            ChangeReason
        )
        VALUES
        (
            OLD.PrescriptionID,
            OLD.PatientID,
            OLD.DoctorID,
            OLD.MedicationID,
            OLD.Dosage,
            OLD.FrequencyInterval,
            OLD.FrequencyUnit,
            OLD.FrequencyTimes,
            OLD.Status,
            OLD.StartDate,
            OLD.EndDate,
            OLD.Type,
            OLD.Description,
            NOW(),
            'Prescription updated'
        );
    ELSEIF OLD.Status = 'inactive' THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Cannot update an inactive prescription. Reactivate it first.';
    END IF;
END$$

-- AFTER INSERT: generate reminders when active\
DROP TRIGGER IF EXISTS `trgPrescriptionAfterInsert`$$
CREATE TRIGGER `trgPrescriptionAfterInsert`
AFTER INSERT ON `Prescription`
FOR EACH ROW
BEGIN
  IF NEW.Status = 'active' THEN
    CALL uspCreateRemindersForPrescription(NEW.PrescriptionID);
  END IF;
END$$

-- AFTER UPDATE: delete pending reminders (only pending) for OLD and recreate for NEW if active
DROP TRIGGER IF EXISTS `trgPrescriptionAfterUpdate`$$
CREATE TRIGGER `trgPrescriptionAfterUpdate`
AFTER UPDATE ON `Prescription`
FOR EACH ROW
BEGIN
  -- Delete only reminders that are pending (i.e., not taken/missed)
  DELETE FROM `Reminder`
  WHERE PrescriptionID = OLD.PrescriptionID
    AND Status = 'pending';

  -- Recreate for NEW prescription if it's active
  IF NEW.Status = 'active' THEN
    CALL uspCreateRemindersForPrescription(NEW.PrescriptionID);
  END IF;
END$$

-- BEFORE DELETE: insert history and delete only pending reminders
DROP TRIGGER IF EXISTS `trgPrescriptionBeforeDelete`$$
CREATE TRIGGER `trgPrescriptionBeforeDelete`
BEFORE DELETE ON `Prescription`
FOR EACH ROW
BEGIN
  -- Insert snapshot into history
  INSERT INTO `PrescriptionHistory`
  (
    PrescriptionID,
    PatientID,
    DoctorID,
    MedicationID,
    Dosage,
    FrequencyInterval,
    FrequencyUnit,
    FrequencyTimes,
    Status,
    StartDate,
    EndDate,
    Type,
    Description,
    ChangeDate,
    ChangeReason
  )
  VALUES
  (
    OLD.PrescriptionID,
    OLD.PatientID,
    OLD.DoctorID,
    OLD.MedicationID,
    OLD.Dosage,
    OLD.FrequencyInterval,
    OLD.FrequencyUnit,
    OLD.FrequencyTimes,
    OLD.Status,
    OLD.StartDate,
    OLD.EndDate,
    OLD.Type,
    OLD.Description,
    NOW(),
    'Prescription deleted'
  );

  -- Delete only pending reminders (preserves taken/missed ones)
  DELETE FROM `Reminder`
  WHERE PrescriptionID = OLD.PrescriptionID
    AND Status = 'pending';
  -- Let the delete proceed (the FK is RESTRICT; because we deleted pending rows, if any non-pending reminders remain,
  -- the RESTRICT will prevent deletion — which is desirable to preserve history). If you prefer to allow delete even when taken/missed exist,
  -- change FK to ON DELETE SET NULL or drop FK and rely on application layer.
END$$
DELIMITER ;

-- Testing

-- (1) Create active prescription with repeat type
INSERT INTO Prescription
(PrescriptionID,PatientID, DoctorID, MedicationID, Dosage, FrequencyInterval, FrequencyUnit, FrequencyTimes, Status, StartDate, EndDate, Type)
VALUES
(5000, 1, 2, 1, '500mg', 1, 'day', '08:00,20:00', 'active', '2025-11-21 08:00:00', '2025-11-25 20:00:00', 'repeat');

-- (2) Create active prescription with custom type
INSERT INTO Prescription
(PrescriptionID, PatientID, DoctorID, MedicationID, Dosage, FrequencyTimes, Status, StartDate, EndDate, Type)
VALUES
(5001, 2, 1, 2, '1000mg', '2025-11-22 09:00,2025-11-24 09:00', 'active', '2025-11-22 09:00:00', '2025-11-24 09:00:00', 'custom');

-- (3) Check generated reminders
SELECT * FROM Reminder WHERE PrescriptionID IN (5000, 5001);

-- (4) Check if custom type prescription with invalid fields is rejected
INSERT INTO Prescription
(PrescriptionID,PatientID, DoctorID, MedicationID, Dosage, FrequencyInterval, FrequencyUnit, FrequencyTimes, Status, StartDate, EndDate, Type)
VALUES
(5002, 1, 2, 1, '500mg', 1, 'day', '08:00,20:00', 'active', '2025-11-21 08:00:00', '2025-11-25 20:00:00', 'custom');

-- (5) Check if repeat type prescription with missing fields is rejected
INSERT INTO Prescription
(PrescriptionID, PatientID, DoctorID, MedicationID, Dosage, FrequencyTimes, Status, StartDate, EndDate, Type)
VALUES
(5003, 2, 1, 2, '1000mg', '2025-11-22 09:00,2025-11-24 09:00', 'active', '2025-11-22 09:00:00', '2025-11-24 09:00:00', 'repeat');

-- (6) Update prescription to change status to inactive
UPDATE `Prescription`
SET Status = 'inactive'
WHERE PrescriptionID = 5001;

-- (7) Check that pending reminders for PrescriptionID 5001 are deleted
SELECT * FROM Reminder WHERE PrescriptionID = 5001;

-- (8) Check that a snapshot is created in PrescriptionHistory
SELECT * FROM PrescriptionHistory WHERE PrescriptionID = 5001;

-- (9) Check that if cannot update inactive prescription
UPDATE `Prescription`
SET EndDate = '2025-12-01 09:00:00'
WHERE PrescriptionID = 5001;

-- (10) Check if reminders are regenerated upon reactivating prescription
UPDATE `Prescription`
SET Status = 'active'
WHERE PrescriptionID = 5001;

SELECT * FROM Reminder WHERE PrescriptionID = 5001;

-- (11) Inactive prescription and check that only pending reminders are deleted
UPDATE Reminder
SET Status = 'taken',
    TakenAt = NOW()
WHERE PrescriptionID = 5000;

SELECT * FROM Reminder WHERE PrescriptionID = 5000;

UPDATE Prescription
SET Status = 'inactive'
WHERE PrescriptionID = 5000;

SELECT * FROM Reminder WHERE PrescriptionID = 5000;

-- ======================================
-- SQL sanitisation
-- ======================================
-- Sanitises textual input by trimming whitespace, stripping control chars and squashing repeated spaces
DROP FUNCTION IF EXISTS ufnSanitizeText;
DELIMITER //
CREATE FUNCTION ufnSanitizeText(rawText TEXT)
RETURNS TEXT
DETERMINISTIC
BEGIN
	IF rawText IS NULL THEN
		RETURN NULL;
	END IF;

	SET rawText = TRIM(rawText);
	SET rawText = REPLACE(rawText, CHAR(9), ' ');   -- tab
	SET rawText = REPLACE(rawText, CHAR(10), ' ');  -- newline
	SET rawText = REPLACE(rawText, CHAR(13), ' ');  -- carriage return
	SET rawText = REPLACE(rawText, CHAR(8), ' ');   -- backspace
	SET rawText = REPLACE(rawText, CHAR(7), ' ');   -- bell
	SET rawText = REPLACE(rawText, CHAR(0), '');    -- null terminator
	SET rawText = REPLACE(rawText, ';', '');        -- statement separator
	SET rawText = REPLACE(rawText, '#', '');        -- comment delimiter
	SET rawText = REPLACE(rawText, '"', '');       -- double quotes
	SET rawText = REPLACE(rawText, '\\', '');     -- backslash
	SET rawText = REPLACE(rawText, '\'', '');      -- literal single quote
	WHILE INSTR(rawText, '  ') > 0 DO
		SET rawText = REPLACE(rawText, '  ', ' ');
	END WHILE;
	SET rawText = REPLACE(rawText, '--', '');
	SET rawText = REPLACE(rawText, '/*', '');
	SET rawText = REPLACE(rawText, '*/', '');
	RETURN rawText;
END//
DELIMITER ;

-- Doctor sanitisation trigger
DROP TRIGGER IF EXISTS trgDoctorBeforeInsert;
DELIMITER //
CREATE TRIGGER trgDoctorBeforeInsert
BEFORE INSERT ON Doctor
FOR EACH ROW
BEGIN
	SET NEW.DoctorName = ufnSanitizeText(NEW.DoctorName);
	SET NEW.ContactInfo = ufnSanitizeText(NEW.ContactInfo);
	SET NEW.Workshift = ufnSanitizeText(NEW.Workshift);
	SET NEW.Department = ufnSanitizeText(NEW.Department);
	SET NEW.Username = ufnSanitizeText(NEW.Username);
	SET NEW.Password = ufnSanitizeText(NEW.Password);
END//
DELIMITER ;

-- Patient sanitisation trigger
DROP TRIGGER IF EXISTS trgPatientBeforeInsert;
DELIMITER //
CREATE TRIGGER trgPatientBeforeInsert
BEFORE INSERT ON Patient
FOR EACH ROW
BEGIN
	SET NEW.PatientName = ufnSanitizeText(NEW.PatientName);
	SET NEW.ContactInfo = ufnSanitizeText(NEW.ContactInfo);
	SET NEW.Gender = ufnSanitizeText(NEW.Gender);
	SET NEW.Username = ufnSanitizeText(NEW.Username);
	SET NEW.Password = ufnSanitizeText(NEW.Password);
END//
DELIMITER ;

-- Symptom sanitisation trigger
DROP TRIGGER IF EXISTS trgSymptomBeforeInsert;
DELIMITER //
CREATE TRIGGER trgSymptomBeforeInsert
BEFORE INSERT ON Symptom
FOR EACH ROW
BEGIN
	SET NEW.SymptomName = ufnSanitizeText(NEW.SymptomName);
	SET NEW.Description = ufnSanitizeText(NEW.Description);
END//
DELIMITER ;

-- Reminder sanitisation trigger
DROP TRIGGER IF EXISTS trgReminderBeforeInsert;
DELIMITER //
CREATE TRIGGER trgReminderBeforeInsert
BEFORE INSERT ON Reminder
FOR EACH ROW
BEGIN
	SET NEW.Status = ufnSanitizeText(NEW.Status);
END//
DELIMITER ;

-- Medication sanitisation trigger
DROP TRIGGER IF EXISTS trgMedicationBeforeInsert;
DELIMITER //
CREATE TRIGGER trgMedicationBeforeInsert
BEFORE INSERT ON Medication
FOR EACH ROW
BEGIN
	SET NEW.MedicationName = ufnSanitizeText(NEW.MedicationName);
	SET NEW.Efficacy = ufnSanitizeText(NEW.Efficacy);
END//
DELIMITER ;

-- Prescription sanitisation trigger
DROP TRIGGER IF EXISTS trgPrescriptionBeforeInsert;
DELIMITER //
CREATE TRIGGER trgPrescriptionBeforeInsert
BEFORE INSERT ON Prescription
FOR EACH ROW
BEGIN
	SET NEW.Dosage = ufnSanitizeText(NEW.Dosage);
	SET NEW.FrequencyUnit = ufnSanitizeText(NEW.FrequencyUnit);
	SET NEW.FrequencyTimes = ufnSanitizeText(NEW.FrequencyTimes);
	SET NEW.Status = ufnSanitizeText(NEW.Status);
	SET NEW.Type = ufnSanitizeText(NEW.Type);
	SET NEW.Description = ufnSanitizeText(NEW.Description);
END//
DELIMITER ;

-- PrescriptionHistory sanitisation trigger
DROP TRIGGER IF EXISTS trgPrescriptionHistoryBeforeInsert;
DELIMITER //
CREATE TRIGGER trgPrescriptionHistoryBeforeInsert
BEFORE INSERT ON PrescriptionHistory
FOR EACH ROW
BEGIN
	SET NEW.Dosage = ufnSanitizeText(NEW.Dosage);
	SET NEW.FrequencyUnit = ufnSanitizeText(NEW.FrequencyUnit);
	SET NEW.FrequencyTimes = ufnSanitizeText(NEW.FrequencyTimes);
	SET NEW.Status = ufnSanitizeText(NEW.Status);
	SET NEW.Type = ufnSanitizeText(NEW.Type);
    SET NEW.Description = ufnSanitizeText(NEW.Description);
	SET NEW.ChangeReason = ufnSanitizeText(NEW.ChangeReason);
END//
DELIMITER ;

-- SideEffect sanitisation trigger
DROP TRIGGER IF EXISTS trgSideEffectBeforeInsert;
DELIMITER //
CREATE TRIGGER trgSideEffectBeforeInsert
BEFORE INSERT ON SideEffect
FOR EACH ROW
BEGIN
	SET NEW.SideEffectName = ufnSanitizeText(NEW.SideEffectName);
	SET NEW.Description = ufnSanitizeText(NEW.Description);
END//
DELIMITER ;

-- =================
-- Backup
-- =================
-- Create the following folder (eg: Desktop):
-- MYSQLBackups/scripts
-- MYSQLBackups/backups
-- MYSQLBackups/SQLFiles

-- Open notepad and paste the backup.bat inside it
-- Save as backup.bat and put it inside /scripts
-- Paste all .sql files inside /SQLFiles
-- run the command: .\backup.bat
-- Find all the backup files inside /backups

-- backup.bat
@echo off
set DB_NAME=FDN
set USER=root
set PASS=root
set PORT=3307

:: Create timestamp
for /f "tokens=2-4 delims=/ " %%a in ("%DATE%") do (
    set dd=%%a
    set mm=%%b
    set yyyy=%%c
)
set hh=%TIME:~0,2%
set hh=%hh: =0%
set nn=%TIME:~3,2%
set TIMESTAMP=%yyyy%-%mm%-%dd%_%hh%-%nn%

:: SQL files folder (relative to this script)
set SQL_FOLDER=%~dp0..\SQLFiles

:: Backup folder
set BACKUP_FOLDER=%~dp0..\backups

:: Backup only data
mysqldump -u %USER% -p%PASS% -P %PORT% -h localhost --insert-ignore %DB_NAME% > "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"

:: Append additional SQL files
echo Appending additional SQL files...
type "%SQL_FOLDER%\FDN.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\index.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\insert.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\usecase.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
type "%SQL_FOLDER%\Function.sql" >> "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"

echo Backup completed: "%BACKUP_FOLDER%\%DB_NAME%_%TIMESTAMP%.sql"
pause