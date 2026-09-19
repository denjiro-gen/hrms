-- ============================================================
-- BESTLINK COLLEGE OF THE PHILIPPINES
-- AI-Based Human Resource Management System
-- Database Schema v1.0
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+08:00";

CREATE DATABASE IF NOT EXISTS `bestlink_hrms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bestlink_hrms`;

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `description` TEXT,
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_code` (`code`),
  KEY `idx_dept_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_code` VARCHAR(20) NOT NULL,
  `department_id` INT UNSIGNED DEFAULT NULL,
  `position` VARCHAR(100) NOT NULL,
  `supervisor_id` INT UNSIGNED DEFAULT NULL,
  `first_name` VARCHAR(60) NOT NULL,
  `middle_name` VARCHAR(60) DEFAULT NULL,
  `last_name` VARCHAR(60) NOT NULL,
  `suffix` VARCHAR(10) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('Male','Female','Other') DEFAULT NULL,
  `civil_status` ENUM('Single','Married','Widowed','Separated','Divorced') DEFAULT NULL,
  `nationality` VARCHAR(100) DEFAULT NULL,
  `religion` VARCHAR(100) DEFAULT NULL,
  `contact_number` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `employment_type` ENUM('Regular','Contractual','Part-time','Probationary','Casual') NOT NULL DEFAULT 'Regular',
  `date_hired` DATE DEFAULT NULL,
  `employment_status` ENUM('Active','Inactive','On Leave','Resigned','Retired','Terminated') NOT NULL DEFAULT 'Active',
  `salary_grade` VARCHAR(20) DEFAULT NULL,
  `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `sss_number` VARCHAR(30) DEFAULT NULL,
  `philhealth_number` VARCHAR(30) DEFAULT NULL,
  `pagibig_number` VARCHAR(30) DEFAULT NULL,
  `tin_number` VARCHAR(30) DEFAULT NULL,
  `status` ENUM('Active','Archived') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_code` (`employee_code`),
  KEY `idx_emp_dept` (`department_id`),
  KEY `idx_emp_status` (`employment_status`),
  KEY `idx_emp_name` (`last_name`,`first_name`),
  CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_emp_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED DEFAULT NULL,
  `department_id` INT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(60) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(60) NOT NULL,
  `last_name` VARCHAR(60) NOT NULL,
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_status` (`status`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `fk_users_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_users_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_emergency_contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `relationship` VARCHAR(60) NOT NULL,
  `contact_number` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ec_emp` (`employee_id`),
  CONSTRAINT `fk_ec_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_education` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `level` ENUM('Elementary','High School','Senior High School','Vocational','College','Masteral','Doctoral','Other') NOT NULL,
  `institution` VARCHAR(200) NOT NULL,
  `degree` VARCHAR(150) DEFAULT NULL,
  `field_of_study` VARCHAR(150) DEFAULT NULL,
  `year_from` YEAR DEFAULT NULL,
  `year_to` YEAR DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_edu_emp` (`employee_id`),
  CONSTRAINT `fk_edu_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_certifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `issuer` VARCHAR(200) DEFAULT NULL,
  `issue_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `document_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cert_emp` (`employee_id`),
  CONSTRAINT `fk_cert_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `document_type` VARCHAR(80) NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doc_emp` (`employee_id`),
  CONSTRAINT `fk_doc_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employment_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `employer` VARCHAR(200) NOT NULL,
  `position` VARCHAR(150) NOT NULL,
  `date_from` DATE DEFAULT NULL,
  `date_to` DATE DEFAULT NULL,
  `reason_for_leaving` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eh_emp` (`employee_id`),
  CONSTRAINT `fk_eh_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_postings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_code` VARCHAR(20) NOT NULL,
  `position_title` VARCHAR(150) NOT NULL,
  `department_id` INT UNSIGNED DEFAULT NULL,
  `employment_type` ENUM('Regular','Contractual','Part-time','Probationary','Casual') NOT NULL DEFAULT 'Regular',
  `job_description` TEXT NOT NULL,
  `responsibilities` TEXT DEFAULT NULL,
  `qualifications` TEXT DEFAULT NULL,
  `required_skills` TEXT DEFAULT NULL,
  `preferred_skills` TEXT DEFAULT NULL,
  `min_education` VARCHAR(100) DEFAULT NULL,
  `experience_required` VARCHAR(100) DEFAULT NULL,
  `salary_min` DECIMAL(12,2) DEFAULT NULL,
  `salary_max` DECIMAL(12,2) DEFAULT NULL,
  `slots` INT UNSIGNED NOT NULL DEFAULT 1,
  `status` ENUM('Open','Closed','On Hold','Cancelled') NOT NULL DEFAULT 'Open',
  `posted_by` INT UNSIGNED DEFAULT NULL,
  `date_posted` DATE NOT NULL,
  `closing_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_code` (`job_code`),
  KEY `idx_job_dept` (`department_id`),
  KEY `idx_job_status` (`status`),
  CONSTRAINT `fk_job_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `applicants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(60) NOT NULL,
  `middle_name` VARCHAR(60) DEFAULT NULL,
  `last_name` VARCHAR(60) NOT NULL,
  `email` VARCHAR(150) DEFAULT NULL,
  `contact_number` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `gender` ENUM('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `highest_education` VARCHAR(150) DEFAULT NULL,
  `years_experience` DECIMAL(4,1) DEFAULT NULL,
  `skills` TEXT DEFAULT NULL,
  `resume_path` VARCHAR(500) DEFAULT NULL,
  `resume_original_name` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_app_name` (`last_name`,`first_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_posting_id` INT UNSIGNED NOT NULL,
  `applicant_id` INT UNSIGNED NOT NULL,
  `cover_letter` TEXT DEFAULT NULL,
  `application_date` DATE NOT NULL,
  `status` ENUM('Applied','Screening','Shortlisted','Interview','Final Interview','Selected','Hired','Rejected') NOT NULL DEFAULT 'Applied',
  `notes` TEXT DEFAULT NULL,
  `ai_match_score` DECIMAL(5,2) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_appl_job` (`job_posting_id`),
  KEY `idx_appl_applicant` (`applicant_id`),
  KEY `idx_appl_status` (`status`),
  CONSTRAINT `fk_appl_job` FOREIGN KEY (`job_posting_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_appl_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recruitment_ai_results` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` INT UNSIGNED NOT NULL,
  `match_score` DECIMAL(5,2) NOT NULL,
  `matched_skills` JSON DEFAULT NULL,
  `missing_skills` JSON DEFAULT NULL,
  `recommendation` VARCHAR(30) NOT NULL,
  `raw_response` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_appl` (`application_id`),
  CONSTRAINT `fk_ai_appl` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` INT UNSIGNED NOT NULL,
  `interview_type` ENUM('Initial','Technical','HR','Final','Panel') NOT NULL DEFAULT 'Initial',
  `scheduled_date` DATE NOT NULL,
  `scheduled_time` TIME NOT NULL,
  `interviewer_id` INT UNSIGNED DEFAULT NULL,
  `location` VARCHAR(200) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('Scheduled','Completed','Cancelled','No Show') NOT NULL DEFAULT 'Scheduled',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_int_appl` (`application_id`),
  CONSTRAINT `fk_int_appl` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interview_evaluations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `interview_id` INT UNSIGNED NOT NULL,
  `evaluator_id` INT UNSIGNED NOT NULL,
  `communication_score` TINYINT UNSIGNED DEFAULT NULL,
  `technical_score` TINYINT UNSIGNED DEFAULT NULL,
  `attitude_score` TINYINT UNSIGNED DEFAULT NULL,
  `overall_score` DECIMAL(4,2) DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `recommendation` ENUM('Highly Recommended','Recommended','For Consideration','Not Recommended') DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ie_interview` (`interview_id`),
  CONSTRAINT `fk_ie_interview` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `attendance_date` DATE NOT NULL,
  `time_in` TIME DEFAULT NULL,
  `time_out` TIME DEFAULT NULL,
  `total_hours` DECIMAL(5,2) DEFAULT NULL,
  `late_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
  `undertime_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
  `overtime_hours` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Present','Late','Absent','Half Day','On Leave','Holiday','Weekend') NOT NULL DEFAULT 'Present',
  `remarks` TEXT DEFAULT NULL,
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_att_emp_date` (`employee_id`,`attendance_date`),
  KEY `idx_att_date` (`attendance_date`),
  KEY `idx_att_status` (`status`),
  CONSTRAINT `fk_att_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `max_days_per_year` INT UNSIGNED NOT NULL DEFAULT 5,
  `is_paid` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_balances` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `year` YEAR NOT NULL,
  `total_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0,
  `used_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0,
  `remaining_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lb_emp_type_year` (`employee_id`,`leave_type_id`,`year`),
  CONSTRAINT `fk_lb_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lb_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `leave_code` VARCHAR(20) NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `leave_type_id` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` DECIMAL(5,1) NOT NULL DEFAULT 1.0,
  `reason` TEXT NOT NULL,
  `supporting_document` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('Pending','Dept Approved','HR Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `dept_head_action` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `dept_head_id` INT UNSIGNED DEFAULT NULL,
  `dept_head_remarks` TEXT DEFAULT NULL,
  `dept_head_at` TIMESTAMP NULL DEFAULT NULL,
  `hr_action` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `hr_id` INT UNSIGNED DEFAULT NULL,
  `hr_remarks` TEXT DEFAULT NULL,
  `hr_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_leave_code` (`leave_code`),
  KEY `idx_lr_emp` (`employee_id`),
  KEY `idx_lr_status` (`status`),
  CONSTRAINT `fk_lr_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lr_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `overtime_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ot_code` VARCHAR(20) NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `overtime_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `total_hours` DECIMAL(5,2) NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('Pending','Dept Approved','HR Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `dept_head_action` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `dept_head_id` INT UNSIGNED DEFAULT NULL,
  `dept_head_remarks` TEXT DEFAULT NULL,
  `dept_head_at` TIMESTAMP NULL DEFAULT NULL,
  `hr_action` ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `hr_id` INT UNSIGNED DEFAULT NULL,
  `hr_remarks` TEXT DEFAULT NULL,
  `hr_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ot_code` (`ot_code`),
  KEY `idx_ot_emp` (`employee_id`),
  KEY `idx_ot_status` (`status`),
  CONSTRAINT `fk_ot_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_periods` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `period_name` VARCHAR(100) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('Open','Closed','Processed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payroll_code` VARCHAR(30) NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `period_id` INT UNSIGNED NOT NULL,
  `basic_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_allowances` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `overtime_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `bonus` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `gross_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sss_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `philhealth_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `pagibig_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_deduction` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_deductions` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('Pending','Paid','On Hold') NOT NULL DEFAULT 'Pending',
  `payment_date` DATE DEFAULT NULL,
  `prepared_by` INT UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payroll_code` (`payroll_code`),
  KEY `idx_pay_emp` (`employee_id`),
  KEY `idx_pay_period` (`period_id`),
  CONSTRAINT `fk_pay_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_period` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `performance_evaluations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `eval_code` VARCHAR(20) NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `evaluator_id` INT UNSIGNED NOT NULL,
  `evaluation_period` VARCHAR(50) NOT NULL,
  `period_start` DATE NOT NULL,
  `period_end` DATE NOT NULL,
  `attendance_score` TINYINT UNSIGNED DEFAULT NULL,
  `work_quality_score` TINYINT UNSIGNED DEFAULT NULL,
  `productivity_score` TINYINT UNSIGNED DEFAULT NULL,
  `teamwork_score` TINYINT UNSIGNED DEFAULT NULL,
  `communication_score` TINYINT UNSIGNED DEFAULT NULL,
  `initiative_score` TINYINT UNSIGNED DEFAULT NULL,
  `professionalism_score` TINYINT UNSIGNED DEFAULT NULL,
  `leadership_score` TINYINT UNSIGNED DEFAULT NULL,
  `overall_score` DECIMAL(4,2) DEFAULT NULL,
  `performance_rating` ENUM('Excellent','Very Good','Satisfactory','Needs Improvement','Unsatisfactory') DEFAULT NULL,
  `comments` TEXT DEFAULT NULL,
  `recommendations` TEXT DEFAULT NULL,
  `status` ENUM('Draft','Submitted','Acknowledged') NOT NULL DEFAULT 'Draft',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eval_code` (`eval_code`),
  KEY `idx_pe_emp` (`employee_id`),
  KEY `idx_pe_evaluator` (`evaluator_id`),
  CONSTRAINT `fk_pe_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trainings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `training_code` VARCHAR(20) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `provider` VARCHAR(200) DEFAULT NULL,
  `trainer` VARCHAR(150) DEFAULT NULL,
  `training_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `duration_hours` DECIMAL(6,1) DEFAULT NULL,
  `location` VARCHAR(200) DEFAULT NULL,
  `max_participants` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('Upcoming','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Upcoming',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_training_code` (`training_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_participants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `training_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `status` ENUM('Enrolled','Completed','Dropped') NOT NULL DEFAULT 'Enrolled',
  `attendance_status` ENUM('Present','Absent','Partial') DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tp_training_emp` (`training_id`,`employee_id`),
  CONSTRAINT `fk_tp_training` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tp_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `training_id` INT UNSIGNED NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `certificate_path` VARCHAR(500) DEFAULT NULL,
  `certificate_number` VARCHAR(100) DEFAULT NULL,
  `issued_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tc_training_emp` (`training_id`,`employee_id`),
  CONSTRAINT `fk_tc_training` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tc_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `benefits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `benefit_type` ENUM('Health','Insurance','Allowance','Assistance','Educational','Other') NOT NULL DEFAULT 'Other',
  `eligibility` TEXT DEFAULT NULL,
  `amount` DECIMAL(12,2) DEFAULT NULL,
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_benefits` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT UNSIGNED NOT NULL,
  `benefit_id` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `status` ENUM('Active','Expired','Cancelled') NOT NULL DEFAULT 'Active',
  `enrolled_by` INT UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_eb_emp` (`employee_id`),
  CONSTRAINT `fk_eb_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_eb_benefit` FOREIGN KEY (`benefit_id`) REFERENCES `benefits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `disciplinary_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_code` VARCHAR(20) NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `incident_date` DATE NOT NULL,
  `incident_type` VARCHAR(100) NOT NULL,
  `severity` ENUM('Minor','Moderate','Serious','Grave') NOT NULL DEFAULT 'Minor',
  `description` TEXT NOT NULL,
  `action_taken` ENUM('Verbal Warning','Written Warning','Suspension','Termination','Other') NOT NULL,
  `suspension_days` INT UNSIGNED DEFAULT NULL,
  `hr_notes` TEXT DEFAULT NULL,
  `document_path` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('Open','Resolved','Closed') NOT NULL DEFAULT 'Open',
  `recorded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_disc_code` (`record_code`),
  KEY `idx_disc_emp` (`employee_id`),
  CONSTRAINT `fk_disc_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `grievances` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grievance_code` VARCHAR(20) NOT NULL,
  `employee_id` INT UNSIGNED NOT NULL,
  `date_filed` DATE NOT NULL,
  `category` ENUM('Workplace Harassment','Unfair Treatment','Discrimination','Compensation','Working Conditions','Other') NOT NULL,
  `description` TEXT NOT NULL,
  `document_path` VARCHAR(500) DEFAULT NULL,
  `assigned_to` INT UNSIGNED DEFAULT NULL,
  `resolution` TEXT DEFAULT NULL,
  `status` ENUM('Open','Under Review','Resolved','Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grievance_code` (`grievance_code`),
  KEY `idx_griev_emp` (`employee_id`),
  CONSTRAINT `fk_griev_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `username` VARCHAR(60) DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(80) NOT NULL,
  `module` VARCHAR(80) NOT NULL,
  `record_id` VARCHAR(50) DEFAULT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_user` (`user_id`),
  KEY `idx_al_module` (`module`),
  KEY `idx_al_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `link` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(80) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;