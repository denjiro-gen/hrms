-- ============================================================
-- BESTLINK COLLEGE OF THE PHILIPPINES - Supabase / PostgreSQL Schema
-- ============================================================

-- Roles
CREATE TABLE roles (
  id SERIAL PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Departments
CREATE TABLE departments (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  description TEXT,
  status VARCHAR(10) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active','Inactive')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employees
CREATE TABLE employees (
  id SERIAL PRIMARY KEY,
  employee_code VARCHAR(20) NOT NULL UNIQUE,
  department_id INT REFERENCES departments(id) ON DELETE SET NULL,
  position VARCHAR(100) NOT NULL,
  supervisor_id INT REFERENCES employees(id) ON DELETE SET NULL,
  first_name VARCHAR(60) NOT NULL,
  middle_name VARCHAR(60),
  last_name VARCHAR(60) NOT NULL,
  suffix VARCHAR(10),
  date_of_birth DATE,
  gender VARCHAR(10) CHECK (gender IN ('Male','Female','Other')),
  civil_status VARCHAR(20) CHECK (civil_status IN ('Single','Married','Widowed','Separated','Divorced')),
  nationality VARCHAR(100),
  religion VARCHAR(100),
  contact_number VARCHAR(20),
  email VARCHAR(150),
  address TEXT,
  employment_type VARCHAR(20) NOT NULL DEFAULT 'Regular' CHECK (employment_type IN ('Regular','Contractual','Part-time','Probationary','Casual')),
  date_hired DATE,
  employment_status VARCHAR(20) NOT NULL DEFAULT 'Active' CHECK (employment_status IN ('Active','Inactive','On Leave','Resigned','Retired','Terminated')),
  salary_grade VARCHAR(20),
  basic_salary NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  profile_photo VARCHAR(255),
  sss_number VARCHAR(30),
  philhealth_number VARCHAR(30),
  pagibig_number VARCHAR(30),
  tin_number VARCHAR(30),
  status VARCHAR(10) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active','Archived')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Users
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  role_id INT NOT NULL REFERENCES roles(id),
  employee_id INT REFERENCES employees(id) ON DELETE SET NULL,
  department_id INT REFERENCES departments(id) ON DELETE SET NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
  status VARCHAR(10) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active','Inactive')),
  last_login TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employee Emergency Contacts
CREATE TABLE employee_emergency_contacts (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  name VARCHAR(120) NOT NULL,
  relationship VARCHAR(60) NOT NULL,
  contact_number VARCHAR(20),
  address TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employee Education
CREATE TABLE employee_education (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  level VARCHAR(30) NOT NULL CHECK (level IN ('Elementary','High School','Senior High School','Vocational','College','Masteral','Doctoral','Other')),
  institution VARCHAR(200) NOT NULL,
  degree VARCHAR(150),
  field_of_study VARCHAR(150),
  year_from SMALLINT,
  year_to SMALLINT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employee Certifications
CREATE TABLE employee_certifications (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  name VARCHAR(200) NOT NULL,
  issuer VARCHAR(200),
  issue_date DATE,
  expiry_date DATE,
  document_path VARCHAR(255),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employee Documents
CREATE TABLE employee_documents (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  document_type VARCHAR(80) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size INT,
  uploaded_by INT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employment History
CREATE TABLE employment_history (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  employer VARCHAR(200) NOT NULL,
  position VARCHAR(150) NOT NULL,
  date_from DATE,
  date_to DATE,
  reason_for_leaving TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Job Postings
CREATE TABLE job_postings (
  id SERIAL PRIMARY KEY,
  job_code VARCHAR(20) NOT NULL UNIQUE,
  position_title VARCHAR(150) NOT NULL,
  department_id INT REFERENCES departments(id) ON DELETE SET NULL,
  employment_type VARCHAR(20) NOT NULL DEFAULT 'Regular' CHECK (employment_type IN ('Regular','Contractual','Part-time','Probationary','Casual')),
  job_description TEXT NOT NULL,
  responsibilities TEXT,
  qualifications TEXT,
  required_skills TEXT,
  preferred_skills TEXT,
  min_education VARCHAR(100),
  experience_required VARCHAR(100),
  salary_min NUMERIC(12,2),
  salary_max NUMERIC(12,2),
  slots INT NOT NULL DEFAULT 1,
  status VARCHAR(20) NOT NULL DEFAULT 'Open' CHECK (status IN ('Open','Closed','On Hold','Cancelled')),
  posted_by INT,
  date_posted DATE NOT NULL,
  closing_date DATE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Applicants
CREATE TABLE applicants (
  id SERIAL PRIMARY KEY,
  first_name VARCHAR(60) NOT NULL,
  middle_name VARCHAR(60),
  last_name VARCHAR(60) NOT NULL,
  email VARCHAR(150),
  contact_number VARCHAR(20),
  address TEXT,
  gender VARCHAR(10) CHECK (gender IN ('Male','Female','Other')),
  date_of_birth DATE,
  highest_education VARCHAR(150),
  years_experience NUMERIC(4,1),
  skills TEXT,
  resume_path VARCHAR(500),
  resume_original_name VARCHAR(255),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Applications
CREATE TABLE applications (
  id SERIAL PRIMARY KEY,
  job_posting_id INT NOT NULL REFERENCES job_postings(id) ON DELETE CASCADE,
  applicant_id INT NOT NULL REFERENCES applicants(id) ON DELETE CASCADE,
  cover_letter TEXT,
  application_date DATE NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Applied' CHECK (status IN ('Applied','Screening','Shortlisted','Interview','Final Interview','Selected','Hired','Rejected')),
  notes TEXT,
  ai_match_score NUMERIC(5,2),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Recruitment AI Results
CREATE TABLE recruitment_ai_results (
  id SERIAL PRIMARY KEY,
  application_id INT NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
  match_score NUMERIC(5,2) NOT NULL,
  matched_skills JSONB,
  missing_skills JSONB,
  recommendation VARCHAR(30) NOT NULL,
  raw_response JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Interviews
CREATE TABLE interviews (
  id SERIAL PRIMARY KEY,
  application_id INT NOT NULL REFERENCES applications(id) ON DELETE CASCADE,
  interview_type VARCHAR(20) NOT NULL DEFAULT 'Initial' CHECK (interview_type IN ('Initial','Technical','HR','Final','Panel')),
  scheduled_date DATE NOT NULL,
  scheduled_time TIME NOT NULL,
  interviewer_id INT,
  location VARCHAR(200),
  notes TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'Scheduled' CHECK (status IN ('Scheduled','Completed','Cancelled','No Show')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Interview Evaluations
CREATE TABLE interview_evaluations (
  id SERIAL PRIMARY KEY,
  interview_id INT NOT NULL REFERENCES interviews(id) ON DELETE CASCADE,
  evaluator_id INT NOT NULL,
  communication_score SMALLINT,
  technical_score SMALLINT,
  attitude_score SMALLINT,
  overall_score NUMERIC(4,2),
  comments TEXT,
  recommendation VARCHAR(40) CHECK (recommendation IN ('Highly Recommended','Recommended','For Consideration','Not Recommended')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Attendance
CREATE TABLE attendance (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  attendance_date DATE NOT NULL,
  time_in TIME,
  time_out TIME,
  total_hours NUMERIC(5,2),
  late_minutes INT NOT NULL DEFAULT 0,
  undertime_minutes INT NOT NULL DEFAULT 0,
  overtime_hours NUMERIC(5,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(20) NOT NULL DEFAULT 'Present' CHECK (status IN ('Present','Late','Absent','Half Day','On Leave','Holiday','Weekend')),
  remarks TEXT,
  recorded_by INT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (employee_id, attendance_date)
);

-- Leave Types
CREATE TABLE leave_types (
  id SERIAL PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  description TEXT,
  max_days_per_year INT NOT NULL DEFAULT 5,
  is_paid BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Leave Balances
CREATE TABLE leave_balances (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  leave_type_id INT NOT NULL REFERENCES leave_types(id),
  year SMALLINT NOT NULL,
  total_days NUMERIC(5,1) NOT NULL DEFAULT 0.0,
  used_days NUMERIC(5,1) NOT NULL DEFAULT 0.0,
  remaining_days NUMERIC(5,1) NOT NULL DEFAULT 0.0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (employee_id, leave_type_id, year)
);

-- Leave Requests
CREATE TABLE leave_requests (
  id SERIAL PRIMARY KEY,
  leave_code VARCHAR(20) NOT NULL UNIQUE,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  leave_type_id INT NOT NULL REFERENCES leave_types(id),
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  total_days NUMERIC(5,1) NOT NULL DEFAULT 1.0,
  reason TEXT NOT NULL,
  supporting_document VARCHAR(500),
  status VARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (status IN ('Pending','Dept Approved','HR Approved','Rejected','Cancelled')),
  dept_head_action VARCHAR(10) NOT NULL DEFAULT 'Pending' CHECK (dept_head_action IN ('Pending','Approved','Rejected')),
  dept_head_id INT, dept_head_remarks TEXT, dept_head_at TIMESTAMPTZ,
  hr_action VARCHAR(10) NOT NULL DEFAULT 'Pending' CHECK (hr_action IN ('Pending','Approved','Rejected')),
  hr_id INT, hr_remarks TEXT, hr_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Payroll Periods
CREATE TABLE payroll_periods (
  id SERIAL PRIMARY KEY,
  period_name VARCHAR(100) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status VARCHAR(15) NOT NULL DEFAULT 'Open' CHECK (status IN ('Open','Closed','Processed')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Payroll
CREATE TABLE payroll (
  id SERIAL PRIMARY KEY,
  payroll_code VARCHAR(30) NOT NULL UNIQUE,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  period_id INT NOT NULL REFERENCES payroll_periods(id),
  basic_salary NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  total_allowances NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  overtime_pay NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  bonus NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  gross_salary NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  sss_deduction NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  philhealth_deduction NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  pagibig_deduction NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  tax_deduction NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  other_deductions NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  total_deductions NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  net_salary NUMERIC(12,2) NOT NULL DEFAULT 0.00,
  payment_status VARCHAR(10) NOT NULL DEFAULT 'Pending' CHECK (payment_status IN ('Pending','Paid','On Hold')),
  payment_date DATE, prepared_by INT, notes TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Performance Evaluations
CREATE TABLE performance_evaluations (
  id SERIAL PRIMARY KEY,
  eval_code VARCHAR(20) NOT NULL UNIQUE,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  evaluator_id INT NOT NULL,
  evaluation_period VARCHAR(50) NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  attendance_score SMALLINT, work_quality_score SMALLINT,
  productivity_score SMALLINT, teamwork_score SMALLINT,
  communication_score SMALLINT, initiative_score SMALLINT,
  professionalism_score SMALLINT, leadership_score SMALLINT,
  overall_score NUMERIC(4,2),
  performance_rating VARCHAR(30) CHECK (performance_rating IN ('Excellent','Very Good','Satisfactory','Needs Improvement','Unsatisfactory')),
  comments TEXT, recommendations TEXT,
  status VARCHAR(15) NOT NULL DEFAULT 'Draft' CHECK (status IN ('Draft','Submitted','Acknowledged')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Benefits
CREATE TABLE benefits (
  id SERIAL PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  benefit_type VARCHAR(20) NOT NULL DEFAULT 'Other' CHECK (benefit_type IN ('Health','Insurance','Allowance','Assistance','Educational','Other')),
  eligibility TEXT,
  amount NUMERIC(12,2),
  status VARCHAR(10) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active','Inactive')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Employee Benefits
CREATE TABLE employee_benefits (
  id SERIAL PRIMARY KEY,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  benefit_id INT NOT NULL REFERENCES benefits(id) ON DELETE CASCADE,
  start_date DATE NOT NULL,
  end_date DATE,
  status VARCHAR(10) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active','Expired','Cancelled')),
  enrolled_by INT, notes TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Disciplinary Records
CREATE TABLE disciplinary_records (
  id SERIAL PRIMARY KEY,
  record_code VARCHAR(20) NOT NULL UNIQUE,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  incident_date DATE NOT NULL,
  incident_type VARCHAR(100) NOT NULL,
  severity VARCHAR(10) NOT NULL DEFAULT 'Minor' CHECK (severity IN ('Minor','Moderate','Serious','Grave')),
  description TEXT NOT NULL,
  action_taken VARCHAR(30) NOT NULL CHECK (action_taken IN ('Verbal Warning','Written Warning','Suspension','Termination','Other')),
  suspension_days INT, hr_notes TEXT, document_path VARCHAR(500),
  status VARCHAR(10) NOT NULL DEFAULT 'Open' CHECK (status IN ('Open','Resolved','Closed')),
  recorded_by INT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Grievances
CREATE TABLE grievances (
  id SERIAL PRIMARY KEY,
  grievance_code VARCHAR(20) NOT NULL UNIQUE,
  employee_id INT NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
  date_filed DATE NOT NULL,
  category VARCHAR(50) NOT NULL CHECK (category IN ('Workplace Harassment','Unfair Treatment','Discrimination','Compensation','Working Conditions','Other')),
  description TEXT NOT NULL,
  document_path VARCHAR(500), assigned_to INT, resolution TEXT,
  status VARCHAR(15) NOT NULL DEFAULT 'Open' CHECK (status IN ('Open','Under Review','Resolved','Closed')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Audit Logs
CREATE TABLE audit_logs (
  id BIGSERIAL PRIMARY KEY,
  user_id INT, username VARCHAR(60), role VARCHAR(50),
  action VARCHAR(80) NOT NULL, module VARCHAR(80) NOT NULL,
  record_id VARCHAR(50), description TEXT NOT NULL, ip_address VARCHAR(45),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Notifications
CREATE TABLE notifications (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(10) NOT NULL DEFAULT 'info' CHECK (type IN ('info','success','warning','danger')),
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  link VARCHAR(500),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- System Settings
CREATE TABLE system_settings (
  id SERIAL PRIMARY KEY,
  setting_key VARCHAR(80) NOT NULL UNIQUE,
  setting_value TEXT, description VARCHAR(255), updated_by INT,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Default Seed Data
INSERT INTO roles (name, slug, description) VALUES
  ('Administrator', 'admin', 'Full system access'),
  ('HR Officer', 'hr', 'Human Resources management'),
  ('Department Head', 'dept_head', 'Department management'),
  ('Employee', 'employee', 'Employee self-service');

INSERT INTO leave_types (name, description, max_days_per_year, is_paid) VALUES
  ('Vacation Leave', 'Annual vacation leave', 15, TRUE),
  ('Sick Leave', 'Medical/illness leave', 15, TRUE),
  ('Emergency Leave', 'Emergency situations', 3, TRUE),
  ('Maternity Leave', 'Maternity leave', 105, TRUE),
  ('Paternity Leave', 'Paternity Leave', 7, TRUE);

-- Insert a default IT Department
INSERT INTO departments (name, code, description) VALUES
('Information Technology', 'IT', 'IT and System Administration');

-- Insert default employees for Demo Accounts
INSERT INTO employees (employee_code, department_id, position, first_name, last_name, email, employment_type, employment_status, basic_salary) VALUES
('EMP-2026-001', 1, 'System Administrator', 'System', 'Admin', 'admin@bestlink.edu.ph', 'Regular', 'Active', 50000.00),
('EMP-2026-002', 1, 'HR Officer', 'HR', 'Officer', 'hr@bestlink.edu.ph', 'Regular', 'Active', 40000.00),
('EMP-2026-003', 1, 'Department Head', 'Dept', 'Head', 'depthead@bestlink.edu.ph', 'Regular', 'Active', 45000.00);

-- Insert the Demo Accounts users (Password for all is: password)
INSERT INTO users (role_id, employee_id, username, email, password_hash, first_name, last_name, status) VALUES
(1, 1, 'admin', 'admin@bestlink.edu.ph', '$2y$12$IQ9cbkK8Y0kYMRzaW2BWou6GrzmT0T7Lz5ZBA7cGVliQ8iNPGZMzy', 'System', 'Admin', 'Active'),
(2, 2, 'hr', 'hr@bestlink.edu.ph', '$2y$12$IQ9cbkK8Y0kYMRzaW2BWou6GrzmT0T7Lz5ZBA7cGVliQ8iNPGZMzy', 'HR', 'Officer', 'Active'),
(3, 3, 'depthead', 'depthead@bestlink.edu.ph', '$2y$12$IQ9cbkK8Y0kYMRzaW2BWou6GrzmT0T7Lz5ZBA7cGVliQ8iNPGZMzy', 'Dept', 'Head', 'Active');
