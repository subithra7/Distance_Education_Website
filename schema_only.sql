DROP SCHEMA public CASCADE;
CREATE SCHEMA public;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 09, 2026 at 07:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
--
-- Database: "admission_db"
--
-- --------------------------------------------------------
--
-- Table structure for table "admin_users"
--
CREATE TABLE "admin_users" (
  "id" INT NOT NULL,
  "username" varchar(100) NOT NULL,
  "password" varchar(255) NOT NULL
) ;
--
-- Dumping data for table "admin_users"
--
-- --------------------------------------------------------
--
-- Table structure for table "approval_logs"
--
CREATE TABLE "approval_logs" (
  "id" INT NOT NULL,
  "application_id" INT DEFAULT NULL,
  "application_no" varchar(30) DEFAULT NULL,
  "action_type" varchar(20) DEFAULT NULL,
  "remark" text DEFAULT NULL,
  "processed_by" varchar(100) DEFAULT NULL,
  "processed_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ;
--
-- Dumping data for table "approval_logs"
--
-- --------------------------------------------------------
--
-- Table structure for table "caste_master"
--
CREATE TABLE "caste_master" (
  "id" SERIAL,
  "community" varchar(20) DEFAULT NULL,
  "caste_name" varchar(150) DEFAULT NULL
) ;
--
-- Dumping data for table "caste_master"
--
-- --------------------------------------------------------
--
-- Table structure for table "certificate_courses"
--
CREATE TABLE "certificate_courses" (
  "id" SERIAL,
  "course_name" varchar(255) DEFAULT NULL,
  "duration" varchar(50) DEFAULT NULL,
  "medium" varchar(50) DEFAULT NULL,
  "eligibility" text DEFAULT NULL,
  "programme_degree" varchar(50) DEFAULT NULL,
  "main_subject" varchar(100) DEFAULT NULL,
  "course_code" varchar(10) NOT NULL
) ;
--
-- Dumping data for table "certificate_courses"
--
-- --------------------------------------------------------
--
-- Table structure for table "diploma_courses"
--
CREATE TABLE "diploma_courses" (
  "id" SERIAL,
  "course_name" varchar(255) DEFAULT NULL,
  "duration" varchar(50) DEFAULT NULL,
  "medium" varchar(50) DEFAULT NULL,
  "eligibility" text DEFAULT NULL,
  "programme_degree" varchar(50) DEFAULT NULL,
  "main_subject" varchar(100) DEFAULT NULL,
  "course_code" varchar(10) NOT NULL
) ;
--
-- Dumping data for table "diploma_courses"
--
-- --------------------------------------------------------
--
-- Table structure for table "districts"
--
CREATE TABLE "districts" (
  "id" SERIAL,
  "state_id" INT NOT NULL,
  "district_name" varchar(100) NOT NULL
) ;
--
-- Dumping data for table "districts"
--
-- --------------------------------------------------------
--
-- Table structure for table "document_uploads"
--
CREATE TABLE "document_uploads" (
  "student_id" INT NOT NULL,
  "sslc" varchar(255) DEFAULT NULL,
  "hsc" varchar(255) DEFAULT NULL,
  "ug" varchar(255) DEFAULT NULL,
  "tc" varchar(255) DEFAULT NULL,
  "migration" varchar(255) DEFAULT NULL,
  "undertaking" varchar(255) DEFAULT NULL,
  "abc_status" varchar(5) DEFAULT NULL,
  "abc_id" varchar(12) DEFAULT NULL
) ;
--
-- Dumping data for table "document_uploads"
--
-- --------------------------------------------------------
--
-- Table structure for table "pg_courses"
--
CREATE TABLE "pg_courses" (
  "id" SERIAL,
  "course_name" varchar(255) DEFAULT NULL,
  "duration" varchar(50) DEFAULT NULL,
  "medium" varchar(50) DEFAULT NULL,
  "eligibility" text DEFAULT NULL,
  "programme_degree" varchar(50) DEFAULT NULL,
  "main_subject" varchar(100) DEFAULT NULL,
  "course_code" varchar(10) NOT NULL
) ;
--
-- Dumping data for table "pg_courses"
--
-- --------------------------------------------------------
--
-- Table structure for table "records"
--
CREATE TABLE "records" (
  "id" SERIAL,
  "application_no" varchar(30) NOT NULL,
  "course_type" varchar(20) DEFAULT NULL,
  "foundation_lang" varchar(50) DEFAULT NULL,
  "programme_name" varchar(150) DEFAULT NULL,
  "main_subject" varchar(150) DEFAULT NULL,
  "medium" varchar(20) DEFAULT NULL,
  "differently_abled" varchar(5) DEFAULT NULL,
  "photo" varchar(255) DEFAULT NULL,
  "name" varchar(150) NOT NULL,
  "street" varchar(200) NOT NULL,
  "town" varchar(100) NOT NULL,
  "state" varchar(100) NOT NULL,
  "district" varchar(100) NOT NULL,
  "pincode" varchar(10) NOT NULL,
  "phone" varchar(15) DEFAULT NULL,
  "mobile" varchar(15) NOT NULL,
  "name_english" varchar(150) NOT NULL,
  "name_tamil" varchar(150) NOT NULL,
  "dob" date NOT NULL,
  "age" INT NOT NULL,
  "guardian_name" varchar(150) NOT NULL,
  "aadhaar" varchar(14) DEFAULT NULL,
  "nationality" varchar(50) DEFAULT NULL,
  "religion" varchar(100) DEFAULT NULL,
  "mother_tongue" varchar(100) DEFAULT NULL,
  "blood_group" varchar(5) DEFAULT NULL,
  "community" varchar(20) DEFAULT NULL,
  "caste" varchar(100) DEFAULT NULL,
  "employment_status" varchar(10) DEFAULT NULL,
  "employment_type" varchar(150) DEFAULT NULL,
  "other_course" varchar(10) DEFAULT NULL,
  "other_course_details" varchar(255) DEFAULT NULL,
  "defence_personnel" SMALLINT DEFAULT 0,
  "ex_servicemen" SMALLINT DEFAULT 0,
  "sslc_school" varchar(150) DEFAULT NULL,
  "sslc_board" varchar(150) DEFAULT NULL,
  "sslc_pass_year" varchar(20) DEFAULT NULL,
  "sslc_reg_no" varchar(50) DEFAULT NULL,
  "sslc_grade" varchar(50) DEFAULT NULL,
  "sslc_max_marks" varchar(20) DEFAULT NULL,
  "hsc_school" varchar(150) DEFAULT NULL,
  "hsc_board" varchar(150) DEFAULT NULL,
  "hsc_pass_year" varchar(20) DEFAULT NULL,
  "hsc_reg_no" varchar(50) DEFAULT NULL,
  "hsc_grade" varchar(50) DEFAULT NULL,
  "hsc_max_marks" varchar(20) DEFAULT NULL,
  "abc_status" varchar(10) DEFAULT NULL,
  "abc_id" varchar(12) DEFAULT NULL,
  "sslc_file" varchar(255) DEFAULT NULL,
  "hsc_file" varchar(255) DEFAULT NULL,
  "ug_file" varchar(255) DEFAULT NULL,
  "tc_file" varchar(255) DEFAULT NULL,
  "migration_file" varchar(255) DEFAULT NULL,
  "undertaking_file" varchar(255) DEFAULT NULL,
  "enclosures" text DEFAULT NULL,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "dip_school" varchar(200) DEFAULT NULL,
  "dip_board" varchar(200) DEFAULT NULL,
  "dip_pass_year" varchar(10) DEFAULT NULL,
  "dip_reg_no" varchar(50) DEFAULT NULL,
  "dip_grade" varchar(50) DEFAULT NULL,
  "dip_max_marks" varchar(50) DEFAULT NULL,
  "ug_school" varchar(200) DEFAULT NULL,
  "ug_board" varchar(200) DEFAULT NULL,
  "ug_pass_year" varchar(10) DEFAULT NULL,
  "ug_reg_no" varchar(50) DEFAULT NULL,
  "ug_grade" varchar(50) DEFAULT NULL,
  "ug_max_marks" varchar(50) DEFAULT NULL,
  "mother_name" varchar(100) NOT NULL,
  "email" varchar(150) NOT NULL,
  "defence_ward" varchar(50) DEFAULT NULL,
  "status" varchar(20) DEFAULT 'Pending',
  "staff_remark" text DEFAULT NULL,
  "processed_by" varchar(100) DEFAULT NULL,
  "processed_at" TIMESTAMP DEFAULT NULL,
  "enrollment_no" varchar(30) DEFAULT NULL,
  "course_code" varchar(10) DEFAULT NULL,
  "course_id" INT DEFAULT NULL
) ;
--
-- Dumping data for table "records"
--
-- --------------------------------------------------------
--
-- Table structure for table "states"
--
CREATE TABLE "states" (
  "id" SERIAL,
  "state_name" varchar(100) NOT NULL
) ;
--
-- Dumping data for table "states"
--
-- --------------------------------------------------------
--
-- Table structure for table "students"
--
CREATE TABLE "students" (
  "id" SERIAL,
  "name" varchar(100) DEFAULT NULL,
  "mobile" varchar(10) DEFAULT NULL,
  "email" varchar(100) DEFAULT NULL,
  "level" varchar(20) DEFAULT NULL,
  "course" varchar(150) DEFAULT NULL
) ;
--
-- Dumping data for table "students"
--
-- --------------------------------------------------------
--
-- Table structure for table "ug_courses"
--
CREATE TABLE "ug_courses" (
  "id" SERIAL,
  "course_name" varchar(255) DEFAULT NULL,
  "duration" varchar(50) DEFAULT NULL,
  "medium" varchar(50) DEFAULT NULL,
  "eligibility" text DEFAULT NULL,
  "programme_degree" varchar(50) DEFAULT NULL,
  "main_subject" varchar(100) DEFAULT NULL,
  "course_code" varchar(10) NOT NULL
) ;
--
-- Dumping data for table "ug_courses"
--
-- --------------------------------------------------------
--
-- Table structure for table "users"
--
CREATE TABLE "users" (
  "id" SERIAL,
  "email" varchar(100) DEFAULT NULL,
  "password" varchar(255) DEFAULT NULL,
  "otp" varchar(6) DEFAULT NULL,
  "is_verified" SMALLINT DEFAULT 0,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "otp_expires_at" TIMESTAMP DEFAULT NULL
) ;
--
-- Dumping data for table "users"
--
--
-- Indexes for dumped tables
--
--
-- Indexes for table "caste_master"
--
ALTER TABLE "caste_master"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "certificate_courses"
--
ALTER TABLE "certificate_courses"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "diploma_courses"
--
ALTER TABLE "diploma_courses"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "districts"
--
ALTER TABLE "districts"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "document_uploads"
--
ALTER TABLE "document_uploads"
  ADD PRIMARY KEY ("student_id");
--
-- Indexes for table "pg_courses"
--
ALTER TABLE "pg_courses"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "records"
--
ALTER TABLE "records"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "application_no" UNIQUE ("application_no");
--
-- Indexes for table "states"
--
ALTER TABLE "states"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "students"
--
ALTER TABLE "students"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "ug_courses"
--
ALTER TABLE "ug_courses"
  ADD PRIMARY KEY ("id");
--
-- Indexes for table "users"
--
ALTER TABLE "users"
  ADD PRIMARY KEY ("id"),
  ADD CONSTRAINT "email" UNIQUE ("email");
--
-- AUTO_INCREMENT for dumped tables
--
--
-- AUTO_INCREMENT for table "caste_master"
--
--
-- Constraints for table "records"
--
ALTER TABLE "records"
  ADD CONSTRAINT "fk_course" FOREIGN KEY ("course_id") REFERENCES "ug_courses" ("id") ON UPDATE CASCADE;
COMMIT;

-- Reset sequences
DO $$
DECLARE
  r RECORD;
BEGIN
  FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = current_schema()) LOOP
    BEGIN
      EXECUTE 'SELECT setval(''' || r.tablename || '_id_seq'', COALESCE((SELECT MAX(id)+1 FROM ' || quote_ident(r.tablename) || '), 1), false)' ;
    EXCEPTION WHEN OTHERS THEN 
      -- ignore
    END;
  END LOOP;
END;
$$;

CREATE TABLE "lsc_users" (
  "id" SERIAL PRIMARY KEY,
  "username" varchar(100),
  "password" varchar(255),
  "lsc_code" varchar(20),
  "lsc_name" varchar(255)
);

CREATE TABLE "course_fees" (
  "id" SERIAL PRIMARY KEY,
  "course_code" varchar(10),
  "course_name" varchar(255),
  "special_fee" NUMERIC(10,2),
  "tuition_fee" NUMERIC(10,2),
  "general_fee" NUMERIC(10,2),
  "vc_fee" NUMERIC(10,2)
);

CREATE TABLE "staff" (
  "id" SERIAL PRIMARY KEY,
  "username" varchar(100),
  "password" varchar(255),
  "course_type" varchar(100),
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE "staff_users" (
  "id" SERIAL PRIMARY KEY,
  "username" varchar(100),
  "password" varchar(255),
  "department" varchar(20),
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE "records" 
ADD COLUMN "gender" varchar(20) DEFAULT NULL,
ADD COLUMN "disability_certificate" varchar(255) DEFAULT NULL,
ADD COLUMN "signature_file" varchar(255) DEFAULT NULL,
ADD COLUMN "cc_serial_no" varchar(50) DEFAULT NULL,
ADD COLUMN "cc_date_of_issue" DATE DEFAULT NULL,
ADD COLUMN "cc_bottom_serial_no" varchar(50) DEFAULT NULL;
