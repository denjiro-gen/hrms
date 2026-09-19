import os
import re

def extract_text_from_cv(file_path: str) -> str:
    """
    Extract plain text from a PDF or DOCX file.
    Returns empty string on failure.
    """
    if not file_path or not os.path.exists(file_path):
        return ""

    ext = os.path.splitext(file_path)[1].lower()

    try:
        if ext == ".pdf":
            return _extract_pdf(file_path)
        elif ext in (".docx", ".doc"):
            return _extract_docx(file_path)
    except Exception as e:
        print(f"[CV Extractor] Failed to extract {file_path}: {e}")
    return ""


def _extract_pdf(path: str) -> str:
    import pdfplumber
    text_parts = []
    with pdfplumber.open(path) as pdf:
        for page in pdf.pages:
            t = page.extract_text()
            if t:
                text_parts.append(t)
    return "\n".join(text_parts)


def _extract_docx(path: str) -> str:
    from docx import Document
    doc = Document(path)
    return "\n".join([p.text for p in doc.paragraphs if p.text.strip()])


def extract_skills_from_text(cv_text: str) -> list[str]:
    """
    Heuristic: pull out likely skill tokens from CV text.
    Looks for lines/sections after keywords like 'skills', 'technologies', etc.
    Also extracts common tech keywords directly from the full text.
    """
    KNOWN_SKILLS = [
        # Programming languages
        "Python","Java","PHP","JavaScript","TypeScript","C","C++","C#","Ruby","Go",
        "Swift","Kotlin","Rust","Scala","R","MATLAB","Bash","Shell",
        # Web
        "HTML","CSS","React","ReactJS","Vue","Vue.js","Angular","Node.js","Next.js",
        "Laravel","Django","Flask","FastAPI","Spring","ASP.NET","jQuery","Bootstrap",
        "Tailwind","REST","RESTful","API","GraphQL","JSON","XML","AJAX","WordPress",
        # Databases
        "MySQL","PostgreSQL","MongoDB","SQLite","Oracle","SQL Server","Redis",
        "Firebase","Cassandra","DynamoDB","MariaDB","SQL","NoSQL",
        # Cloud & DevOps
        "AWS","Azure","GCP","Docker","Kubernetes","CI/CD","Jenkins","Git","GitHub",
        "GitLab","Linux","Unix","Nginx","Apache","Terraform","Ansible",
        # Data & AI
        "Machine Learning","Deep Learning","NLP","TensorFlow","PyTorch","Pandas",
        "NumPy","Scikit-learn","Data Analysis","Data Science","Power BI","Tableau",
        # Networking
        "Networking","TCP/IP","Cisco","CCNA","Firewall","VPN","Network Security",
        # HR/Office
        "HR Operations","Recruitment","Payroll","HRIS","Labor Law","MS Office",
        "Excel","Word","PowerPoint","SAP","Accounting","Financial Reporting",
        "Budgeting","Counseling","Psychology","Communication",
    ]

    found = []
    text_lower = cv_text.lower()
    for skill in KNOWN_SKILLS:
        if skill.lower() in text_lower:
            found.append(skill)

    # Also try to pull comma/bullet separated items near skill section headers
    skill_section_pattern = re.compile(
        r'(?:skills?|technologies|tech stack|competencies|expertise)[:\-\s]+([^\n]{3,200})',
        re.IGNORECASE
    )
    for match in skill_section_pattern.finditer(cv_text):
        raw = match.group(1)
        tokens = re.split(r'[,|•·/]', raw)
        for tok in tokens:
            tok = tok.strip().strip('-').strip()
            if 2 < len(tok) < 50:
                found.append(tok)

    # Deduplicate preserving order
    seen = set()
    result = []
    for s in found:
        key = s.lower()
        if key not in seen:
            seen.add(key)
            result.append(s)
    return result
