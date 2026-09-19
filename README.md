# Bestlink College HRMS & AI Service

This document provides instructions on how to set up and run the complete HRMS system, including the main PHP application and the Python-based AI Recruitment Service.

## Prerequisites

Before you begin, ensure you have the following installed on your system:
- **XAMPP** (or equivalent WAMP/MAMP stack) with PHP 8.x and MySQL/MariaDB.
- **Python 3.8+** for running the AI service.
- **Composer** (optional, if PHP dependencies are ever added).

---

## 1. Setting up the Main HRMS System (PHP)

The main HRMS application is built with PHP and runs on an Apache server via XAMPP.

### Steps:
1. **Move to XAMPP Directory**: Ensure this entire project folder (`hrms`) is located inside your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\hrms`).
2. **Database Setup**:
   - Open your XAMPP Control Panel and start **Apache** and **MySQL**.
   - Go to `http://localhost/phpmyadmin`.
   - Create a new database (e.g., `hrms_db`).
   - Import the database schema. (Check the `sql/` directory or `schema.php` for the database structure, and import it into your newly created database).
3. **Configure Database Connection**:
   - If there is a database configuration file (e.g., `includes/db.php` or `config/db.php`), update the database credentials (host, username, password, database name) to match your local setup.
4. **Directory Permissions**:
   - Ensure that the `uploads/` directory and its subdirectories (`resumes/`, `employee_documents/`, `certificates/`) are writable.
5. **Access the Application**:
   - Open your web browser and navigate to `http://localhost/hrms/index.php`.

---

## 2. Setting up the AI Service (Python)

The AI service is a FastAPI application that handles CV parsing and job matching using NLP and Machine Learning.

### Steps:

1. **Open Command Prompt / PowerShell**.
2. **Navigate to the AI Service Directory**:
   ```bash
   cd C:\xampp\htdocs\hrms\ai_service
   ```
3. **Create a Virtual Environment (Recommended)**:
   ```bash
   py -m venv venv
   ```
   *(Note: If `py` doesn't work, try `python -m venv venv` or `python3 -m venv venv` depending on your OS).*
4. **Activate the Virtual Environment**:
   - **Windows**:
     ```bash
     venv\Scripts\activate
     ```
   - **Mac/Linux**:
     ```bash
     source venv/bin/activate
     ```
5. **Install Dependencies**:
   Install the required Python packages from `requirements.txt`:
   ```bash
   pip install -r requirements.txt
   ```
   *Note: This will install FastAPI, Uvicorn, Sentence-Transformers, scikit-learn, pdfplumber, and python-docx.*

6. **Run the AI Service**:
   Start the FastAPI server using Uvicorn:
   ```bash
   python main.py
   ```
   *(Note: Inside the active virtual environment, the `python` command usually works. If not, use `py main.py`).*
   *Alternatively, you can run:*
   ```bash
   uvicorn main:app --host 127.0.0.1 --port 8000 --reload
   ```
7. **Verify AI Service**:
   - Open your browser and go to `http://127.0.0.1:8000/health`.
   - You should see a JSON response like `{"status": "ok", "model_loaded": true, ...}`.

---

## 3. Connecting the System

To ensure the PHP application can communicate with the local AI Service, you need to configure the API endpoint URL.

1. Open `config/config.php` in a text editor.
2. Locate the `AI_SERVICE_URL` constant.
3. Update it to point to your local AI service address:
   ```php
   define('AI_SERVICE_URL', 'http://127.0.0.1:8000');
   ```

## Running the Complete System (Daily Routine)

Every time you want to run the system, you must start both the PHP/MySQL server and the Python AI server:
1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open a terminal, navigate to `C:\xampp\htdocs\hrms\ai_service`, activate your virtual environment, and run `python main.py`.
3. Access the HRMS at `http://localhost/hrms`.
