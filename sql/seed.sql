-- ============================================================
-- BESTLINK HRMS - SEED DATA v1.0
-- Run AFTER database.sql
-- ============================================================
USE `bestlink_hrms`;

-- Roles
INSERT INTO `roles` (`id`,`name`,`slug`,`description`) VALUES
(1,'Admin','admin','System Administrator - Full access'),
(2,'HR','hr','Human Resources Officer'),
(3,'Department Head','dept_head','Department-level access'),
(4,'School','school','School Administration monitoring');

-- Departments
INSERT INTO `departments` (`id`,`name`,`code`,`description`,`status`) VALUES
(1,'Information Technology','IT','IT Department',                   'Active'),
(2,'Computer Engineering','CE','Computer Engineering Department',    'Active'),
(3,'Human Resources','HR','Human Resources Department',             'Active'),
(4,'Finance and Accounting','FIN','Finance Department',             'Active'),
(5,'Student Affairs','SA','Student Affairs Office',                 'Active'),
(6,'Registrar','REG','Office of the Registrar',                     'Active'),
(7,'Academic Affairs','AA','Office of Academic Affairs',            'Active'),
(8,'Administration','ADM','General Administration Office',          'Active');

-- Employees
INSERT INTO `employees` (`id`,`employee_code`,`department_id`,`position`,`supervisor_id`,`first_name`,`middle_name`,`last_name`,`suffix`,`date_of_birth`,`gender`,`civil_status`,`contact_number`,`email`,`address`,`employment_type`,`date_hired`,`employment_status`,`salary_grade`,`basic_salary`,`sss_number`,`philhealth_number`,`pagibig_number`,`tin_number`,`status`) VALUES
(1,'BCP-2018-001',3,'HR Director',NULL,'Maria','Santos','Reyes',NULL,'1980-03-15','Female','Married','09171234501','mreyes@bestlink.edu.ph','Caloocan City','Regular','2018-06-01','Active','SG-24',75000.00,'34-5678900-1','0112345678-0','1234567890-1','123456789000','Active'),
(2,'BCP-2018-002',1,'IT Department Head',NULL,'Jose','Manuel','Cruz',NULL,'1978-07-22','Male','Married','09181234502','jcruz@bestlink.edu.ph','Quezon City','Regular','2018-08-15','Active','SG-22',65000.00,'34-5678901-1','0112345679-0','1234567891-1','123456789001','Active'),
(3,'BCP-2019-003',1,'Systems Administrator',2,'Ana','Bautista','Lim',NULL,'1990-11-05','Female','Single','09191234503','alim@bestlink.edu.ph','Malabon','Regular','2019-02-01','Active','SG-18',45000.00,'34-5678902-1','0112345680-0','1234567892-1','123456789002','Active'),
(4,'BCP-2019-004',1,'Web Developer',2,'Carlos','Antonio','Garcia',NULL,'1993-04-18','Male','Single','09201234504','cgarcia@bestlink.edu.ph','Valenzuela','Regular','2019-06-10','Active','SG-16',38000.00,'34-5678903-1','0112345681-0','1234567893-1','123456789003','Active'),
(5,'BCP-2019-005',1,'Network Engineer',2,'Patricia','Domingo','Torres',NULL,'1991-08-30','Female','Single','09211234505','ptorres@bestlink.edu.ph','Caloocan City','Regular','2019-09-01','Active','SG-17',42000.00,'34-5678904-1','0112345682-0','1234567894-1','123456789004','Active'),
(6,'BCP-2020-006',2,'CE Department Head',NULL,'Ricardo','Felipe','Mendoza',NULL,'1975-12-01','Male','Married','09221234506','rmendoza@bestlink.edu.ph','Marikina','Regular','2020-01-15','Active','SG-22',65000.00,'34-5678905-1','0112345683-0','1234567895-1','123456789005','Active'),
(7,'BCP-2020-007',2,'Professor I',6,'Lourdes','Reyna','Castillo',NULL,'1985-05-14','Female','Married','09231234507','lcastillo@bestlink.edu.ph','Pasig','Regular','2020-03-01','Active','SG-15',35000.00,'34-5678906-1','0112345684-0','1234567896-1','123456789006','Active'),
(8,'BCP-2020-008',2,'Professor II',6,'Roberto','Navarro','Ramos',NULL,'1983-09-25','Male','Single','09241234508','rramos@bestlink.edu.ph','Mandaluyong','Regular','2020-05-01','Active','SG-16',38000.00,'34-5678907-1','0112345685-0','1234567897-1','123456789007','Active'),
(9,'BCP-2020-009',3,'HR Officer',1,'Josephine','Dela','Vega',NULL,'1988-02-19','Female','Single','09251234509','jvega@bestlink.edu.ph','Makati','Regular','2020-07-01','Active','SG-14',32000.00,'34-5678908-1','0112345686-0','1234567898-1','123456789008','Active'),
(10,'BCP-2020-010',3,'HR Assistant',1,'Michael','Angelo','Santos',NULL,'1995-06-11','Male','Single','09261234510','msantos@bestlink.edu.ph','Malabon','Regular','2020-09-01','Active','SG-10',22000.00,'34-5678909-1','0112345687-0','1234567899-1','123456789009','Active'),
(11,'BCP-2021-011',4,'Finance Director',NULL,'Grace','Esperanza','Villanueva',NULL,'1977-01-28','Female','Married','09271234511','gvillanueva@bestlink.edu.ph','Taguig','Regular','2021-01-10','Active','SG-22',65000.00,'34-5678910-1','0112345688-0','1234567900-1','123456789010','Active'),
(12,'BCP-2021-012',4,'Accountant',11,'Fernando','Jose','Aquino',NULL,'1990-10-07','Male','Married','09281234512','faquino@bestlink.edu.ph','Las Pinas','Regular','2021-03-15','Active','SG-15',35000.00,'34-5678911-1','0112345689-0','1234567901-1','123456789011','Active'),
(13,'BCP-2021-013',4,'Finance Officer',11,'Maricel','Cruz','Delos Reyes',NULL,'1992-07-16','Female','Single','09291234513','mdelosreyes@bestlink.edu.ph','Paranaque','Regular','2021-05-01','Active','SG-13',29000.00,'34-5678912-1','0112345690-0','1234567902-1','123456789012','Active'),
(14,'BCP-2021-014',5,'Student Affairs Head',NULL,'Emmanuel','Santos','Pascual',NULL,'1979-04-03','Male','Married','09301234514','epascual@bestlink.edu.ph','Quezon City','Regular','2021-07-01','Active','SG-20',55000.00,'34-5678913-1','0112345691-0','1234567903-1','123456789013','Active'),
(15,'BCP-2021-015',5,'Guidance Counselor',14,'Rowena','Marquez','Ferrer',NULL,'1987-11-22','Female','Married','09311234515','rferrer@bestlink.edu.ph','Caloocan City','Regular','2021-09-01','Active','SG-14',32000.00,'34-5678914-1','0112345692-0','1234567904-1','123456789014','Active'),
(16,'BCP-2022-016',6,'Registrar',NULL,'Teresita','Rivera','Lorenzo',NULL,'1981-08-08','Female','Married','09321234516','tlorenzo@bestlink.edu.ph','San Juan','Regular','2022-01-03','Active','SG-20',55000.00,'34-5678915-1','0112345693-0','1234567905-1','123456789015','Active'),
(17,'BCP-2022-017',6,'Records Officer',16,'Dennis','Pablo','Gutierrez',NULL,'1994-03-15','Male','Single','09331234517','dgutierrez@bestlink.edu.ph','Navotas','Regular','2022-03-01','Active','SG-11',25000.00,'34-5678916-1','0112345694-0','1234567906-1','123456789016','Active'),
(18,'BCP-2022-018',7,'Academic Affairs Head',NULL,'Consuelo','Bautista','Macaraeg',NULL,'1974-06-20','Female','Widowed','09341234518','cmacaraeg@bestlink.edu.ph','Quezon City','Regular','2022-05-16','Active','SG-24',75000.00,'34-5678917-1','0112345695-0','1234567907-1','123456789017','Active'),
(19,'BCP-2022-019',7,'Curriculum Developer',18,'Benedict','Dela Cruz','Hernandez',NULL,'1989-09-09','Male','Married','09351234519','bhernandez@bestlink.edu.ph','Mandaluyong','Regular','2022-08-01','Active','SG-16',38000.00,'34-5678918-1','0112345696-0','1234567908-1','123456789018','Active'),
(20,'BCP-2023-020',8,'School President',NULL,'Alfredo','Garcia','Bautista','Jr.','1965-02-14','Male','Married','09361234520','abautista@bestlink.edu.ph','Quezon City','Regular','2023-01-01','Active','SG-30',120000.00,'34-5678919-1','0112345697-0','1234567909-1','123456789019','Active'),
(21,'BCP-2023-021',8,'VP for Administration',20,'Florencia','Del Monte','Aguilar',NULL,'1970-10-25','Female','Married','09371234521','faguilar@bestlink.edu.ph','Pasig','Regular','2023-02-01','Active','SG-27',90000.00,'34-5678920-1','0112345698-0','1234567910-1','123456789020','Active'),
(22,'BCP-2023-022',1,'Junior Developer',2,'Kevin','Torres','Dimaculangan',NULL,'1997-12-31','Male','Single','09381234522','kdimaculangan@bestlink.edu.ph','Caloocan City','Probationary','2023-06-01','Active','SG-11',25000.00,'34-5678921-1','0112345699-0','1234567911-1','123456789021','Active'),
(23,'BCP-2023-023',2,'Lab Technician',6,'Sheila','Morales','Pascual',NULL,'1996-05-07','Female','Single','09391234523','spascual@bestlink.edu.ph','Marikina','Contractual','2023-08-01','Active','SG-9',20000.00,'34-5678922-1','0112345700-0','1234567912-1','123456789022','Active'),
(24,'BCP-2024-024',3,'HR Intern',1,'Trisha','Navarro','Reyes',NULL,'1999-03-22','Female','Single','09401234524','treyes@bestlink.edu.ph','Las Pinas','Contractual','2024-01-08','Active','SG-7',16000.00,'34-5678923-1','0112345701-0','1234567913-1','123456789023','Active'),
(25,'BCP-2021-025',4,'Payroll Officer',11,'Andres','Bulacan','Soriano',NULL,'1986-07-04','Male','Married','09411234525','asoriano@bestlink.edu.ph','Valenzuela','Regular','2021-11-01','Active','SG-14',32000.00,'34-5678924-1','0112345702-0','1234567914-1','123456789024','Active');

-- Users — password for all accounts: password (using Laravel default bcrypt hash for demo)
-- In production, use: password_hash('BestlinkHRMS@2024', PASSWORD_BCRYPT)
INSERT INTO `users` (`id`,`role_id`,`employee_id`,`department_id`,`username`,`email`,`password_hash`,`first_name`,`last_name`,`status`) VALUES
(1,1,NULL,NULL,'admin','admin@bestlink.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','System','Administrator','Active'),
(2,2,1,3,'hr.admin','hr@bestlink.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Maria','Reyes','Active'),
(3,3,2,1,'it.head','depthead@bestlink.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Jose','Cruz','Active'),
(4,4,20,8,'school','school@bestlink.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Alfredo','Bautista','Active'),
(5,3,6,2,'ce.head','rmendoza@bestlink.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Ricardo','Mendoza','Active'),
(6,3,11,4,'fin.head','gvillanueva@bestlink.edu.ph','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Grace','Villanueva','Active');

-- Leave Types
INSERT INTO `leave_types` (`id`,`name`,`description`,`max_days_per_year`,`is_paid`) VALUES
(1,'Vacation Leave','Annual vacation leave',15,1),
(2,'Sick Leave','Leave due to illness',15,1),
(3,'Emergency Leave','Family emergencies',5,1),
(4,'Service Leave','Government obligations',5,1),
(5,'Maternity Leave','For female employees',105,1),
(6,'Paternity Leave','For male employees',7,1),
(7,'Study Leave','For training/studies',5,0);

-- Job Postings
INSERT INTO `job_postings` (`job_code`,`position_title`,`department_id`,`employment_type`,`job_description`,`responsibilities`,`qualifications`,`required_skills`,`preferred_skills`,`min_education`,`experience_required`,`salary_min`,`salary_max`,`slots`,`status`,`posted_by`,`date_posted`,`closing_date`) VALUES
('JOB-2024-001','PHP Web Developer',1,'Regular','Looking for an experienced PHP Web Developer for our IT team.','Develop web systems; Write clean code; Perform code reviews.','BS Computer Science or IT; PHP and MySQL knowledge.','PHP, MySQL, JavaScript, HTML, CSS','Laravel, Bootstrap, REST API','College','1-2 years',35000.00,50000.00,2,'Open',2,'2024-08-01','2024-10-31'),
('JOB-2024-002','HR Officer',3,'Regular','Looking for a competent HR Officer to support HR operations.','Handle recruitment; Maintain employee records; Process leave.','BS HRM or Psychology; Knowledge of Labor Law.','HR Operations, Employee Records, Recruitment','HRIS, Payroll Processing','College','1 year',25000.00,35000.00,1,'Open',2,'2024-08-15','2024-10-31'),
('JOB-2024-003','Network Administrator',1,'Regular','Seeking a skilled Network Administrator for our network infrastructure.','Manage network; Configure routers; Monitor performance.','BS Computer Engineering or IT; CCNA preferred.','Networking, Cisco, TCP/IP, Network Security','CCNA, Firewall, VPN','College','2 years',40000.00,55000.00,1,'Open',2,'2024-09-01','2024-11-30'),
('JOB-2024-004','Finance Officer',4,'Regular','Finance Officer to assist in financial reporting and accounting.','Prepare financial statements; Process transactions; Budget prep.','BS Accountancy or Finance; CPA is advantage.','Accounting, Financial Reporting, Budgeting','CPA, SAP, Excel','College','2 years',30000.00,45000.00,1,'Closed',2,'2024-07-01','2024-09-30'),
('JOB-2024-005','Guidance Counselor',5,'Regular','Licensed Guidance Counselor for student counseling services.','Provide counseling; Career guidance; Maintain records.','BS Psychology or Guidance Counseling; Licensed.','Counseling, Psychology, Student Development','Career Guidance, Crisis Intervention','College','1 year',28000.00,38000.00,1,'Open',2,'2024-09-10','2024-11-30');

-- Applicants
INSERT INTO `applicants` (`id`,`first_name`,`middle_name`,`last_name`,`email`,`contact_number`,`address`,`gender`,`date_of_birth`,`highest_education`,`years_experience`,`skills`) VALUES
(1,'John','Mark','Santos','jmsantos@email.com','09451234601','Quezon City','Male','1998-05-10','BS Information Technology',2.5,'PHP, MySQL, JavaScript, HTML, CSS, Laravel, Bootstrap'),
(2,'Jane','Marie','Dela Cruz','jmdelacruz@email.com','09451234602','Caloocan City','Female','1997-08-22','BS Computer Science',1.5,'PHP, MySQL, JavaScript, Vue.js, HTML, CSS'),
(3,'Mark','Anthony','Villanueva','mavillanueva@email.com','09451234603','Marikina City','Male','1996-12-05','BS Information Technology',3.0,'PHP, MySQL, Laravel, REST API, JavaScript, CSS'),
(4,'Sofia','Rose','Mendoza','srMendoza@email.com','09451234604','Pasig City','Female','1999-03-17','BS Computer Engineering',1.0,'Networking, Cisco, TCP/IP, Linux, Network Security'),
(5,'Ryan','Joseph','Torres','rjtorres@email.com','09451234605','Las Pinas City','Male','1998-07-28','BS Information Technology',2.0,'PHP, MySQL, HTML, CSS, JavaScript, Bootstrap'),
(6,'Karen','Ann','Reyes','kareyes@email.com','09451234606','Muntinlupa City','Female','1997-01-15','BS Psychology',1.5,'HR Operations, Recruitment, Employee Relations, MS Office'),
(7,'Adrian','Carlo','Bautista','acbautista@email.com','09451234607','San Juan','Male','1995-09-20','BS Human Resource Management',3.0,'HR Operations, HRIS, Payroll, Recruitment, Labor Law'),
(8,'Camille','Grace','Gonzales','cggonzales@email.com','09451234608','Mandaluyong City','Female','1998-11-11','BS Accountancy',2.0,'Accounting, Financial Reporting, Budgeting, Excel, SAP'),
(9,'Patrick','Luis','Ramos','plramos@email.com','09451234609','Valenzuela City','Male','1996-06-30','BS Computer Engineering',4.0,'Networking, Cisco, CCNA, Firewall, VPN, TCP/IP'),
(10,'Angelica','Mae','Castro','amcastro@email.com','09451234610','Paranaque City','Female','1999-04-25','BS Psychology',1.0,'Counseling, Psychology, Career Guidance, Communication');

-- Applications
INSERT INTO `applications` (`job_posting_id`,`applicant_id`,`application_date`,`status`,`ai_match_score`) VALUES
(1,1,'2024-08-10','Shortlisted',89.40),
(1,2,'2024-08-12','Screening',75.20),
(1,3,'2024-08-15','Interview',91.50),
(1,5,'2024-08-18','Screening',72.80),
(2,6,'2024-08-20','Shortlisted',83.60),
(2,7,'2024-08-22','Final Interview',94.20),
(3,4,'2024-09-05','Screening',78.30),
(3,9,'2024-09-08','Shortlisted',88.90),
(4,8,'2024-07-10','Hired',86.40),
(5,10,'2024-09-12','Applied',71.50);

-- AI Results
INSERT INTO `recruitment_ai_results` (`application_id`,`match_score`,`matched_skills`,`missing_skills`,`recommendation`) VALUES
(1,89.40,'["PHP","MySQL","JavaScript","HTML","CSS"]','["Laravel"]','High Match'),
(2,75.20,'["PHP","MySQL","JavaScript","HTML","CSS"]','["Laravel","Bootstrap"]','Moderate Match'),
(3,91.50,'["PHP","MySQL","JavaScript","HTML","CSS","Laravel","REST API"]','[]','High Match'),
(5,83.60,'["HR Operations","Recruitment","MS Office"]','["HRIS","Payroll","Labor Law"]','High Match'),
(6,94.20,'["HR Operations","Recruitment","HRIS","Payroll","Labor Law"]','[]','Excellent Match'),
(8,88.90,'["Networking","Cisco","CCNA","TCP/IP","Network Security"]','["Firewall Configuration"]','High Match'),
(9,86.40,'["Accounting","Financial Reporting","Budgeting","Excel"]','["CPA","SAP"]','High Match');

-- Attendance
INSERT INTO `attendance` (`employee_id`,`attendance_date`,`time_in`,`time_out`,`total_hours`,`late_minutes`,`overtime_hours`,`status`,`recorded_by`) VALUES
(1,'2024-09-02','07:55:00','17:05:00',9.17,0,1.17,'Present',2),
(1,'2024-09-03','08:05:00','17:00:00',8.92,5,0.00,'Late',2),
(1,'2024-09-04','07:58:00','17:00:00',9.03,0,0.00,'Present',2),
(2,'2024-09-02','07:50:00','17:30:00',9.67,0,1.67,'Present',2),
(2,'2024-09-03','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(2,'2024-09-04','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(3,'2024-09-02','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(3,'2024-09-03',NULL,NULL,NULL,0,0.00,'Absent',2),
(3,'2024-09-04','08:10:00','17:00:00',8.83,10,0.00,'Late',2),
(4,'2024-09-02','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(4,'2024-09-03','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(4,'2024-09-04','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(5,'2024-09-02','08:05:00','17:00:00',8.92,5,0.00,'Late',2),
(5,'2024-09-03','07:55:00','17:00:00',9.08,0,0.00,'Present',2),
(6,'2024-09-02','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(7,'2024-09-02','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(8,'2024-09-02',NULL,NULL,NULL,0,0.00,'Absent',2),
(9,'2024-09-02','08:00:00','17:00:00',9.00,0,0.00,'Present',2),
(10,'2024-09-02','08:15:00','17:00:00',8.75,15,0.00,'Late',2);

-- Leave Requests
INSERT INTO `leave_requests` (`leave_code`,`employee_id`,`leave_type_id`,`start_date`,`end_date`,`total_days`,`reason`,`status`,`dept_head_action`,`dept_head_id`,`dept_head_remarks`,`dept_head_at`,`hr_action`,`hr_id`,`hr_remarks`,`hr_at`) VALUES
('LV-2024-001',3,2,'2024-09-10','2024-09-11',2.0,'Medical check-up and recovery.','HR Approved','Approved',3,'Approved. Get well soon.','2024-09-05 09:00:00','Approved',2,'Approved with pay.','2024-09-05 14:00:00'),
('LV-2024-002',9,1,'2024-09-20','2024-09-24',5.0,'Family vacation - planned in advance.','Pending','Pending',NULL,NULL,NULL,'Pending',NULL,NULL,NULL),
('LV-2024-003',12,3,'2024-09-06','2024-09-06',1.0,'Family emergency.','Dept Approved','Approved',6,'Approved.','2024-09-05 16:00:00','Pending',NULL,NULL,NULL),
('LV-2024-004',7,2,'2024-09-15','2024-09-17',3.0,'Dental surgery and recovery.','HR Approved','Approved',5,'Approved. Get well.','2024-09-08 10:00:00','Approved',2,'Approved.','2024-09-08 15:00:00'),
('LV-2024-005',4,1,'2024-10-07','2024-10-11',5.0,'Annual vacation leave.','Pending','Pending',NULL,NULL,NULL,'Pending',NULL,NULL,NULL);

-- Overtime Requests
INSERT INTO `overtime_requests` (`ot_code`,`employee_id`,`overtime_date`,`start_time`,`end_time`,`total_hours`,`reason`,`status`,`dept_head_action`,`dept_head_id`,`dept_head_remarks`,`dept_head_at`) VALUES
('OT-2024-001',4,'2024-09-06','17:00:00','20:00:00',3.00,'System maintenance and server update.','Dept Approved','Approved',3,'Approved for system maintenance.','2024-09-05 11:00:00'),
('OT-2024-002',3,'2024-09-07','17:00:00','19:00:00',2.00,'Network monitoring during system upgrade.','HR Approved','Approved',3,'Approved.','2024-09-06 08:00:00'),
('OT-2024-003',9,'2024-09-10','17:00:00','21:00:00',4.00,'Payroll processing end of cut-off.','Pending','Pending',NULL,NULL,NULL),
('OT-2024-004',12,'2024-09-12','17:00:00','19:30:00',2.50,'Financial report preparation.','Pending','Pending',NULL,NULL,NULL);

-- Payroll Periods
INSERT INTO `payroll_periods` (`period_name`,`start_date`,`end_date`,`status`) VALUES
('August 2024 - 1st Half','2024-08-01','2024-08-15','Processed'),
('August 2024 - 2nd Half','2024-08-16','2024-08-31','Processed'),
('September 2024 - 1st Half','2024-09-01','2024-09-15','Open');

-- Payroll
INSERT INTO `payroll` (`payroll_code`,`employee_id`,`period_id`,`basic_salary`,`total_allowances`,`overtime_pay`,`bonus`,`gross_salary`,`sss_deduction`,`philhealth_deduction`,`pagibig_deduction`,`tax_deduction`,`other_deductions`,`total_deductions`,`net_salary`,`payment_status`,`payment_date`,`prepared_by`) VALUES
('PAY-2024-001',1,1,37500.00,3000.00,0.00,0.00,40500.00,945.00,562.50,200.00,4567.50,0.00,6275.00,34225.00,'Paid','2024-08-15',2),
('PAY-2024-002',2,1,32500.00,2500.00,0.00,0.00,35000.00,945.00,487.50,200.00,3234.00,0.00,4866.50,30133.50,'Paid','2024-08-15',2),
('PAY-2024-003',3,1,22500.00,1500.00,1800.00,0.00,25800.00,765.00,337.50,200.00,1890.00,0.00,3192.50,22607.50,'Paid','2024-08-15',2),
('PAY-2024-004',4,1,19000.00,1000.00,0.00,0.00,20000.00,630.00,285.00,200.00,1234.00,0.00,2349.00,17651.00,'Paid','2024-08-15',2),
('PAY-2024-005',11,1,32500.00,2500.00,0.00,0.00,35000.00,945.00,487.50,200.00,3234.00,0.00,4866.50,30133.50,'Paid','2024-08-15',2);

-- Performance Evaluations
INSERT INTO `performance_evaluations` (`eval_code`,`employee_id`,`evaluator_id`,`evaluation_period`,`period_start`,`period_end`,`attendance_score`,`work_quality_score`,`productivity_score`,`teamwork_score`,`communication_score`,`initiative_score`,`professionalism_score`,`leadership_score`,`overall_score`,`performance_rating`,`comments`,`recommendations`,`status`) VALUES
('EVAL-2024-001',3,3,'2024 - 1st Semester','2024-01-01','2024-06-30',4,5,4,5,4,4,5,3,4.25,'Very Good','Ana shows exceptional work quality and teamwork.','Consider leadership training.','Submitted'),
('EVAL-2024-002',4,3,'2024 - 1st Semester','2024-01-01','2024-06-30',5,4,4,4,3,4,4,3,3.88,'Very Good','Carlos shows good technical skills. Communication can be improved.','Recommend communication skills training.','Submitted'),
('EVAL-2024-003',7,5,'2024 - 1st Semester','2024-01-01','2024-06-30',4,5,5,5,5,4,5,4,4.63,'Excellent','Lourdes is an outstanding educator.','Nominate for Best Faculty award.','Submitted'),
('EVAL-2024-004',9,2,'2024 - 1st Semester','2024-01-01','2024-06-30',5,5,5,5,5,5,5,4,4.88,'Excellent','Josephine is an excellent HR Officer.','Consider for promotion.','Submitted'),
('EVAL-2024-005',12,6,'2024 - 1st Semester','2024-01-01','2024-06-30',3,4,3,4,4,3,4,3,3.50,'Satisfactory','Fernando meets expectations.','Recommend accounting skills refresher.','Submitted'),
('EVAL-2024-006',22,3,'2024 - 1st Semester','2024-01-01','2024-06-30',4,3,3,4,3,3,4,2,3.25,'Satisfactory','Kevin is still developing.','Mentorship program recommended.','Submitted');

-- Trainings
INSERT INTO `trainings` (`training_code`,`title`,`description`,`provider`,`trainer`,`training_date`,`end_date`,`duration_hours`,`location`,`max_participants`,`status`,`created_by`) VALUES
('TRN-2024-001','Communication Skills Enhancement','Training for verbal and written communication.','Bestlink HR','Prof. Maria Reyes','2024-10-15','2024-10-16',16.0,'Training Room A',25,'Upcoming',2),
('TRN-2024-002','PHP and Laravel Advanced Workshop','Advanced web development workshop.','Tech Solutions Inc.','John Developer','2024-09-20','2024-09-21',16.0,'IT Laboratory 2',15,'Completed',2),
('TRN-2024-003','HR Management Best Practices','Modern HR management practices.','PMAP','PMAP Trainer','2024-08-10','2024-08-12',24.0,'Conference Room',20,'Completed',2),
('TRN-2024-004','Data Privacy and Information Security','Data Privacy Act compliance training.','National Privacy Commission','NPC Representative','2024-11-05','2024-11-05',8.0,'Bestlink Auditorium',100,'Upcoming',2),
('TRN-2024-005','First Aid and Basic Life Support','Basic first aid and CPR training.','Philippine Red Cross','Red Cross Trainer','2024-10-25','2024-10-25',8.0,'Gymnasium',50,'Upcoming',2);

-- Training Participants
INSERT INTO `training_participants` (`training_id`,`employee_id`,`status`,`attendance_status`) VALUES
(2,3,'Completed','Present'),(2,4,'Completed','Present'),(2,22,'Completed','Present'),
(3,1,'Completed','Present'),(3,9,'Completed','Present'),(3,10,'Completed','Present'),
(1,4,'Enrolled',NULL),(1,22,'Enrolled',NULL),(4,1,'Enrolled',NULL),(4,2,'Enrolled',NULL),(4,3,'Enrolled',NULL);

-- Benefits
INSERT INTO `benefits` (`name`,`description`,`benefit_type`,`eligibility`,`amount`,`status`) VALUES
('PhilHealth Coverage','Government-mandated health insurance.','Health','All regular employees',NULL,'Active'),
('Pag-IBIG Housing Loan','Government-mandated housing loan program.','Insurance','All regular employees',NULL,'Active'),
('SSS Benefits','Social Security System benefits.','Insurance','All regular employees',NULL,'Active'),
('Transportation Allowance','Monthly transportation allowance.','Allowance','All regular employees',2000.00,'Active'),
('Meal Allowance','Daily meal allowance.','Allowance','All active employees',100.00,'Active'),
('Educational Assistance','Tuition fee assistance for graduate studies.','Educational','Regular employees 2+ years',15000.00,'Active'),
('Employee Medical Assistance','Financial assistance for major medical procedures.','Health','Regular employees 1+ year',10000.00,'Active'),
('Uniform Allowance','Annual uniform allowance.','Allowance','All regular employees',5000.00,'Active'),
('Rice Allowance','Monthly rice subsidy.','Allowance','All regular employees',1500.00,'Active'),
('13th Month Pay','Mandatory 13th month pay.','Other','All qualified employees',NULL,'Active');

-- Employee Benefits
INSERT INTO `employee_benefits` (`employee_id`,`benefit_id`,`start_date`,`status`,`enrolled_by`) VALUES
(1,1,'2018-06-01','Active',2),(1,2,'2018-06-01','Active',2),(1,3,'2018-06-01','Active',2),(1,4,'2018-06-01','Active',2),
(2,1,'2018-08-15','Active',2),(2,2,'2018-08-15','Active',2),(2,3,'2018-08-15','Active',2),
(3,1,'2019-02-01','Active',2),(3,4,'2019-02-01','Active',2),
(4,1,'2019-06-10','Active',2);

-- Leave Balances
INSERT INTO `leave_balances` (`employee_id`,`leave_type_id`,`year`,`total_days`,`used_days`,`remaining_days`) VALUES
(1,1,2024,15.0,3.0,12.0),(1,2,2024,15.0,2.0,13.0),
(2,1,2024,15.0,5.0,10.0),(2,2,2024,15.0,0.0,15.0),
(3,1,2024,15.0,0.0,15.0),(3,2,2024,15.0,2.0,13.0),
(4,1,2024,15.0,0.0,15.0),(4,2,2024,15.0,1.0,14.0),
(9,1,2024,15.0,0.0,15.0),(9,2,2024,15.0,3.0,12.0);

-- Disciplinary Records
INSERT INTO `disciplinary_records` (`record_code`,`employee_id`,`incident_date`,`incident_type`,`severity`,`description`,`action_taken`,`hr_notes`,`status`,`recorded_by`) VALUES
('DISC-2024-001',22,'2024-07-15','Tardiness','Minor','Employee was consistently late for 5 consecutive days.','Verbal Warning','Employee acknowledged the warning.','Resolved',2),
('DISC-2024-002',10,'2024-08-20','Negligence of Duty','Moderate','Failed to process leave applications on time.','Written Warning','Written warning issued. Performance to be monitored.','Open',2);

-- Grievances
INSERT INTO `grievances` (`grievance_code`,`employee_id`,`date_filed`,`category`,`description`,`assigned_to`,`status`) VALUES
('GRV-2024-001',15,'2024-08-05','Working Conditions','The counseling room lacks proper ventilation and space for private sessions.',2,'Under Review'),
('GRV-2024-002',23,'2024-09-01','Compensation','My current salary grade does not match my actual responsibilities.',2,'Open');

-- Notifications
INSERT INTO `notifications` (`user_id`,`title`,`message`,`type`,`is_read`,`link`) VALUES
(2,'New Leave Request','Josephine Vega submitted a vacation leave request (LV-2024-002).','info',0,'/bestlink_hrms/modules/leave/index.php'),
(2,'New Overtime Request','Josephine Vega submitted OT request (OT-2024-003).','info',0,'/bestlink_hrms/modules/overtime/index.php'),
(2,'New Applicant','Angelica Castro applied for Guidance Counselor.','info',0,'/bestlink_hrms/modules/recruitment/applicants/index.php'),
(2,'Grievance Filed','A new grievance (GRV-2024-002) has been filed by Sheila Pascual.','warning',0,'/bestlink_hrms/modules/grievances/index.php'),
(3,'New Leave Request','Carlos Garcia submitted a vacation leave request (LV-2024-005).','info',0,'/bestlink_hrms/modules/leave/index.php'),
(3,'OT Request Approved','Your OT request (OT-2024-001) has been approved by HR.','success',1,'/bestlink_hrms/modules/overtime/index.php'),
(4,'System Update','New performance analytics features are now available.','info',0,'/bestlink_hrms/dashboard.php');

-- System Settings
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`description`) VALUES
('system_name','Bestlink HRMS','System display name'),
('institution_name','Bestlink College of the Philippines','Full institution name'),
('system_version','1.0.0','Current system version'),
('session_timeout','3600','Session timeout in seconds'),
('work_start_time','08:00','Official work start time'),
('work_end_time','17:00','Official work end time'),
('late_threshold_mins','15','Minutes after start time considered late'),
('ai_service_url','http://127.0.0.1:8000','BERT AI service URL'),
('ai_service_enabled','1','Enable/disable AI service'),
('max_upload_size','5242880','Max file upload size (5MB)'),
('allowed_file_types','pdf,doc,docx,jpg,jpeg,png','Allowed extensions');
