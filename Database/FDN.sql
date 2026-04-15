CREATE DATABASE IF NOT EXISTS `FDN`;
USE `FDN`;

SET AUTOCOMMIT = false;
SELECT @@autocommit;

-- Doctor Table
DROP TABLE IF EXISTS `Doctor`;
CREATE TABLE `Doctor` (
    DoctorID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    DoctorName VARCHAR(100) NOT NULL,
    ContactInfo VARCHAR(40) NOT NULL,
    Workshift VARCHAR(40) NOT NULL,
    Department VARCHAR(40) NOT NULL,
    Username VARCHAR(40) NOT NULL,
    Password VARCHAR(255) NOT NULL
);

-- Patient Table
DROP TABLE IF EXISTS `Patient`;
CREATE TABLE `Patient` (
    PatientID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    PatientName VARCHAR(100) NOT NULL,
    ContactInfo VARCHAR(40) NOT NULL,
    Gender ENUM('Male', 'Female') NOT NULL,
    Username VARCHAR(40) NOT NULL,
    Password VARCHAR(255) NOT NULL
);

-- Medication Table
DROP TABLE IF EXISTS `Medication`;
CREATE TABLE `Medication`(
    MedicationID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    MedicationName VARCHAR(40) NOT NULL,
    Efficacy VARCHAR(40) NOT NULL
);

-- Symptom Table
DROP TABLE IF EXISTS `Symptom`;
CREATE TABLE `Symptom` (
    SymptomID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    PatientID INT NOT NULL,
    SymptomName VARCHAR(40) NOT NULL,
    Description VARCHAR(255) NOT NULL,
    FOREIGN KEY (PatientID) REFERENCES Patient(PatientID)
);

-- Prescription Table
DROP TABLE IF EXISTS `Prescription`;
CREATE TABLE `Prescription`(
    PrescriptionID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    PatientID INT NOT NULL,
    DoctorID INT NOT NULL,
    MedicationID INT NOT NULL,
    Dosage VARCHAR(40) NOT NULL,
    FrequencyInterval INT DEFAULT NULL,
    FrequencyUnit ENUM('day','week','month') DEFAULT NULL,
    FrequencyTimes VARCHAR(255) NOT NULL,
    Status ENUM('active', 'inactive') NOT NULL,
    Type ENUM('custom','repeat') NOT NULL DEFAULT 'repeat',
    Description VARCHAR(255) DEFAULT NULL,
    StartDate DATETIME NOT NULL,
    EndDate DATETIME NOT NULL,
    FOREIGN KEY (PatientID) REFERENCES Patient(PatientID),
    FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID),
    FOREIGN KEY (MedicationID) REFERENCES Medication(MedicationID)
);

-- Reminder Table
DROP TABLE IF EXISTS `Reminder`;
CREATE TABLE `Reminder` (
    ReminderID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    PatientID INT NOT NULL,
    PrescriptionID INT NOT NULL,
    ScheduledTime DATETIME NOT NULL,
    TakenAt DATETIME DEFAULT NULL,
    Status ENUM('pending', 'taken', 'missed') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (PatientID) REFERENCES Patient(PatientID),
    FOREIGN KEY (PrescriptionID) REFERENCES Prescription(PrescriptionID)
        ON DELETE RESTRICT  -- prevents automatic deletion when prescription is deleted
        ON UPDATE CASCADE   -- updates PrescriptionID if changed
);

-- PrescriptionHistory Table
DROP TABLE IF EXISTS `PrescriptionHistory`;
CREATE TABLE `PrescriptionHistory` (
    HistoryID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    PrescriptionID INT NOT NULL,
    PatientID INT NOT NULL,
    DoctorID INT NOT NULL,
    MedicationID INT NOT NULL,
    Dosage VARCHAR(40) NOT NULL,
    FrequencyInterval INT DEFAULT NULL,
    FrequencyUnit ENUM('day','week','month') DEFAULT NULL,
    FrequencyTimes VARCHAR(255) NOT NULL,
    Status ENUM('active', 'inactive') NOT NULL,
    StartDate DATETIME NOT NULL,
    EndDate DATETIME NOT NULL,
    Type ENUM('custom','repeat') NOT NULL DEFAULT 'repeat',
    Description VARCHAR(255) DEFAULT NULL,
    ChangeDate DATETIME NOT NULL,
    ChangeReason VARCHAR(255) NOT NULL,
    FOREIGN KEY (PrescriptionID) REFERENCES Prescription(PrescriptionID),
    FOREIGN KEY (PatientID) REFERENCES Patient(PatientID),
    FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID),
    FOREIGN KEY (MedicationID) REFERENCES Medication(MedicationID)
);

-- SideEffect Table
DROP TABLE IF EXISTS `SideEffect`;
CREATE TABLE `SideEffect` (
    SideEffectID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ReminderID INT NOT NULL,
    SideEffectName VARCHAR(40) NOT NULL,
    Description VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (ReminderID) REFERENCES Reminder(ReminderID)
);