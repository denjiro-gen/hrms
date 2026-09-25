# Bestlink College HRMS — System Workflow

This document outlines the end-to-end workflow of the Bestlink College HRMS, designed for your Pre-Oral Defense presentation.

## Core Roles & Permissions

The system is built on a Role-Based Access Control (RBAC) architecture with 4 primary roles:

1. **Administrator (`admin`)**: Full system access, can manage user accounts, departments, and system settings.
2. **HR Officer (`hr`)**: Manages employees, recruitment, payroll, benefits, and discipline.
3. **Department Head (`dept_head`)**: Can view their department's employees, attendance, leave, performance, and training.
4. **School Administrator (`school`)**: Executive-level read access to all core modules (Reports, Payroll, Employees) for oversight and auditing.
5. **Employee (`employee`)**: Self-service portal to view own attendance, leaves, benefits, and profile.

---

## 1. System-Wide Architecture

```mermaid
graph TD
    A[Public Portal] -->|Apply for Job| B(Careers Page)
    A -->|Login| C{Authentication System}
    
    C -->|Role: Admin| D[System Configuration]
    C -->|Role: HR / School| E[HR Management Core]
    C -->|Role: Dept Head| F[Department Management]
    C -->|Role: Employee| G[Employee Self-Service]
    
    D --> D1[User Accounts & Roles]
    D --> D2[Department Setup]
    D --> D3[Audit Logs]
    
    E --> E1[Recruitment Module]
    E --> E2[Employee Directory]
    E --> E3[Payroll & Compensation]
    E --> E4[Discipline & Benefits]
    E --> E5[Global Reports]
    
    F --> F1[Staff Attendance]
    F --> F2[Leave Approvals]
    F --> F3[Performance Evals]
    F --> F4[Training Modules]
    
    G --> G1[View Payslips]
    G --> G2[File Leave Requests]
    G --> G3[View Attendance Log]
```

---

## 2. Recruitment to Onboarding Workflow

This is the automated process of hiring a new employee.

```mermaid
sequenceDiagram
    participant Applicant
    participant AI Engine
    participant HR
    participant System
    
    Applicant->>System: Submits CV via Careers Page
    System->>Applicant: Sends "Application Received" Email
    System->>HR: Sends "New Application" Email
    System->>AI Engine: Sends Resume PDF for parsing
    AI Engine-->>System: Returns Skills Match Score (0-100%)
    HR->>System: Reviews AI Score & Shortlists
    HR->>System: Conducts Interview & Changes Status to "Hired"
    System->>System: Auto-generates Employee Record
    System->>System: Auto-generates User Account (Temp Password)
    System->>Applicant: Sends "You are Hired" Email with Login Details
    Applicant->>System: Logs in using Temp Password
    System->>Applicant: Forces Password Change on First Login
```

---

## 3. Payroll Generation Workflow

How the HRMS calculates and generates payroll for employees.

```mermaid
graph LR
    A[Start Payroll Period] --> B[Fetch Employee Base Salary]
    B --> C[Calculate Total Hours Worked]
    C --> D[Deduct Late/Undertime Mins]
    D --> E[Calculate Overtime Pay]
    E --> F[Apply Statutory Deductions]
    F -->|SSS, PhilHealth, Pag-IBIG| G[Calculate Tax (WTX)]
    G --> H[Compute Net Pay]
    H --> I[Generate Payslips]
    I --> J((Finalize Payroll))
```

---

## 4. Leave & Attendance Workflow

```mermaid
stateDiagram-v2
    [*] --> EmployeeFilesLeave
    EmployeeFilesLeave --> Pending: System auto-checks leave balances
    Pending --> DeptHeadReview
    
    DeptHeadReview --> Approved: Dept Head Approves
    DeptHeadReview --> Rejected: Dept Head Rejects
    
    Approved --> SystemDeduct: Auto-deduct from leave balance
    SystemDeduct --> [*]
    Rejected --> [*]
```

---

## 5. Discipline & Performance Workflow

```mermaid
graph TD
    A[Incident Reported] --> B[HR Logs Incident in Discipline Module]
    B --> C{Investigation Status}
    C -->|Pending| D[Gather Evidence]
    C -->|Resolved| E[Issue Disciplinary Action]
    
    E -->|Warning / Suspension| F[Record in Employee Profile]
    F --> G[Affects Performance Evaluation]
    
    H[End of Quarter] --> I[Dept Head Fills Evaluation Form]
    I --> J[Score Calculated]
    J -->|Below 60%| K[Trigger PIP / Training]
    J -->|Above 90%| L[Eligible for Promotion/Bonus]
