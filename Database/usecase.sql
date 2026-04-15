USE `FDN`;
SET AUTOCOMMIT = false;
SELECT @@autocommit;

-- Use Case #1: Insert information for a new Doctor
INSERT INTO Doctor (DoctorName, ContactInfo, Workshift, Department, Username, Password)
VALUES ('Lee Ming Kwon', 'leemk@gmail.com', 'morning', 'Cardiology', 'drleemk', 'securepass123');

-- Use Case #2: Update Doctor's Workshift and Department
UPDATE Doctor
SET Workshift = 'night', Department = 'Pulmonology'
WHERE DoctorID = 7;

-- Use Case #3: View Symptoms for a Specific Patient
SELECT
    S.SymptomID,
    P.PatientID,
    S.SymptomName,
    S.Description
FROM Symptom S
JOIN Patient P ON S.PatientID = P.PatientID
WHERE S.PatientID = 44;

-- Use Case #4: Update prescription of a patient
UPDATE Prescription
SET MedicationID = 999, Dosage= '10mg'
WHERE PatientID = 15;

-- Use Case #5: Insert prescription for a certain patient
INSERT INTO Prescription(PrescriptionID, PatientID, DoctorID, MedicationID, Dosage, FrequencyInterval, FrequencyUnit, FrequencyTimes, Status, Type, Description, StartDate, EndDate) VALUES
(789, 345, 32, 567, '20mg', null, null, '2025-11-11 08:00, 2025-11-21 08:00' , 'active','custom','store in dry place', '2025-11-11 09:00:00', '2025-12-11 09:00:00');

-- Use Case #6: Retrieve prescription for a certain patient
SELECT * FROM Prescription
WHERE PatientID = 201;

-- Use Case #7: Extend Prescription Duration
START TRANSACTION;

UPDATE `Prescription`
SET EndDate = '2025-11-28 20:00:00'
WHERE PrescriptionID = 2;

COMMIT;

-- Use Case #8: Update Reminder Status
UPDATE `Reminder`
SET Status = 'taken', TakenAt = NOW()
WHERE ReminderID = 18;

-- Use Case #9: Check patient doses during the past 7 days
SELECT
    p.PrescriptionID,
    m.MedicationName,
    r.ScheduledTime,
    r.Status,
    r.TakenAt
FROM Reminder r
JOIN Prescription p ON r.PrescriptionID = p.PrescriptionID
JOIN Medication m ON p.MedicationID = m.MedicationID
WHERE p.PatientID = 3
  AND r.ScheduledTime >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY r.Status, r.ScheduledTime;

-- Use Case #10: Update Patient Certain Information
UPDATE Patient
SET ContactInfo = 'tvinten_123@weibo.com',
    Username = 'tvinten_123'
WHERE PatientID = 1;

-- Use Case #11: Update Side Effect Description
UPDATE SideEffect
SET Description = 'Severe dry mouth requiring frequent water intake'
WHERE SideEffectID = 2;

-- Use Case #12: Retrieve All Side Effects for a Patient
SELECT
    SE.SideEffectID,
    SE.SideEffectName,
    SE.Description,
    P.PatientName
FROM SideEffect SE
JOIN Reminder R ON SE.ReminderID = R.ReminderID
JOIN Patient P ON R.PatientID = P.PatientID
WHERE P.PatientID = 1;

-- Use Case #13: Review History of Prescription before create a new prescription
SELECT
    ph.HistoryID,
    ph.PrescriptionID,
    m.MedicationName,
    ph.Dosage,
    ph.FrequencyInterval,
    ph.FrequencyUnit,
    ph.FrequencyTimes,
    ph.StartDate,
    ph.EndDate,
    ph.Type,
    ph.Status,
    ph.ChangeDate,
    ph.ChangeReason
FROM PrescriptionHistory ph
JOIN Medication m
    ON ph.MedicationID = m.MedicationID
WHERE ph.PatientID = 3
ORDER BY ph.ChangeDate DESC;

-- Use Case #14: Update Reason for Prescription Change
UPDATE PrescriptionHistory
SET ChangeReason = 'Patient allergy'
WHERE HistoryID = 15;

-- Use Case #15: Delete History Record of Prescription due to erroneous entry
START TRANSACTION;

--Update wrong patient's prescription
UPDATE Prescription
SET Dosage = '1 tablet'
WHERE PatientID = 21;

--Restore Patient 21’s prescription that was incorrectly updated
UPDATE Prescription
SET Dosage = '2 tablets'
WHERE PatientID = 21;

--Delete the incorrect history entries for Patient 21
DELETE FROM PrescriptionHistory
WHERE HistoryID = 12
AND PatientID = 21;

DELETE FROM PrescriptionHistory
WHERE HistoryID = 13
AND PatientID = 21;

--Apply the intended update for Patient 12
UPDATE Prescription
SET Dosage = '1 tablet'
WHERE PatientID = 12;

COMMIT;

-- Use Case #16: Patient Insert Multiple Symptoms
START TRANSACTION;

INSERT INTO Symptom (PatientID, SymptomName, Description)
VALUES (101, 'Fever', 'High body temperature for over 2 days'),
       (101, 'Cough', 'Persistent dry cough'),
       (101, 'Fatigue', 'Feeling tired and weak');

COMMIT;

-- Use Case #17: Retrieve Doctors' Information
SELECT DoctorID, DoctorName, Workshift, Department
FROM Doctor
WHERE Department = 'Cardiology';

-- Use Case #18: Delete a doctor record
START TRANSACTION;

DELETE FROM Doctor
WHERE DoctorID = 8;

COMMIT;

-- Use Case #19: Modify symptom description
UPDATE Symptom
SET Description = 'Persistent wet cough with mucus'
WHERE SymptomID = 202 AND PatientID = 101;

-- Use Case #20: Remove incorrect symptom entry
START TRANSACTION;

DELETE FROM Symptom
WHERE SymptomID = 305 AND PatientID = 103;

COMMIT;

-- Use Case #21: Insert a list of medication
START TRANSACTION;

INSERT INTO Medication(MedicationID, MedicationName, Efficacy) 
VALUES (1001, 'Albuterol', 'bronchodilator' ),
       (1002, 'Acetaminophen', 'pain relief, fever reduction'),
       (1003, 'Omeprazole', 'stomach acid reduction'),
       (1004, 'Amoxicillin', 'antibiotic'),
       (1005, 'Atorvastatin', 'lowering cholesterol');

COMMIT;

-- Use Case #22: Retrieve medications having same efficacy
SELECT *
FROM Medication
WHERE Efficacy = 'pain relief';

-- Use Case #23: Remove a medication
START TRANSACTION;

DELETE FROM Medication
WHERE MedicationName ='Pamelor';

COMMIT;

-- Use Case #24: Read a single patient by PatientID
SELECT PatientID, PatientName, ContactInfo, Gender, Username
FROM Patient
WHERE PatientID = 2;

-- Use Case #25: Insert a new patient
INSERT INTO Patient (PatientName, ContactInfo, Gender, Username, Password)
VALUES ('Lim Mei', 'lim.mei@example.com', 'Female', 'limmei_01', 'PAssw0rd!');

-- Use Case #26: Delete patient only if no referencing rows exist (safe delete)
START TRANSACTION;

DELETE FROM Patient
WHERE PatientID = 99
  AND NOT EXISTS (SELECT 1 FROM Prescription WHERE PatientID = 99)
  AND NOT EXISTS (SELECT 1 FROM Symptom WHERE PatientID = 99)
  AND NOT EXISTS (SELECT 1 FROM Reminder WHERE PatientID = 99)
  AND NOT EXISTS (SELECT 1 FROM PrescriptionHistory WHERE PatientID = 99);

COMMIT;

-- Use Case #27: Update side effect description and name
UPDATE SideEffect
SET SideEffectName = 'Severe nausea',
    Description = 'Nausea lasting > 1 hour, consider contacting prescriber'
WHERE SideEffectID = 7;

-- Use Case #28: Delete a side effect by ID
DELETE FROM SideEffect
WHERE SideEffectID = 7;

-- Use Case #29: Insert a new side effect, linked to an existing reminder
INSERT INTO SideEffect (ReminderID, SideEffectName, Description)
VALUES (5, 'Nausea', 'Mild nausea after medication, improves after 30 minutes');

-- Use Case #30: Search patient by partial name with pagination (limit/offset)
SELECT PatientID, PatientName, ContactInfo
FROM Patient
WHERE PatientName LIKE '%Lim%'
ORDER BY PatientName
LIMIT 20 OFFSET 0;