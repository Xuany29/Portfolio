USE `FDN`;
SET AUTOCOMMIT = false;
SELECT @@autocommit;

-- Index #1: idxPatientName ON Patient(PatientName)
CREATE INDEX idxPatientName ON Patient(PatientName);

SHOW INDEX FROM Patient;

EXPLAIN SELECT PatientID, PatientName, Gender
FROM Patient
WHERE PatientName = 'John Doe';

-- Index #2: idxPatientPrescription ON Prescription(PatientID)
CREATE INDEX idxPrescriptionPatient ON Prescription(PatientID);

SHOW INDEX FROM Prescription;

EXPLAIN
SELECT PrescriptionID, PatientID, StartDate, EndDate
FROM Prescription
WHERE PatientID = 101;

-- Index #3: idxReminderPrescription on Reminder(PrescriptionID)
CREATE INDEX idxReminderPrescription ON Reminder(PrescriptionID);

SHOW INDEX FROM Reminder;

EXPLAIN SELECT ReminderID, ScheduledTime, Status
FROM Reminder
WHERE PrescriptionID = 88;

-- Index #4: idxHistoryPrescription on PrescriptionHistory(PrescriptionID)
CREATE INDEX idxHistoryPrescription ON PrescriptionHistory(PrescriptionID);

SHOW INDEX FROM PrescriptionHistory;

EXPLAIN SELECT HistoryID, ChangeDate, ChangeReason
FROM PrescriptionHistory
WHERE PrescriptionID = 201;

-- Index #5: idxPrescriptionDoctor on Prescription(Doctor_ID)
CREATE INDEX idxPrescriptionDoctor ON Prescription(DoctorID);

SHOW INDEX FROM Prescription;

EXPLAIN
SELECT PrescriptionID, PatientID, MedicationID, StartDate, EndDate
FROM Prescription
WHERE DoctorID = 35;