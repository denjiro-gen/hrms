import os
import re
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
from services.cv_extractor import extract_text_from_cv, extract_skills_from_text


class JobMatcher:
    def __init__(self):
        self.model_name = 'all-MiniLM-L6-v2'
        self.model = None

    def load_model(self):
        if self.model is None:
            self.model = SentenceTransformer(self.model_name)

    def is_loaded(self):
        return self.model is not None

    def extract_years(self, text):
        """Extract numeric years from experience string."""
        if not text:
            return 0.0
        if isinstance(text, (int, float)):
            return float(text)
        match = re.search(r'(\d+(\.\d+)?)', str(text))
        return float(match.group(1)) if match else 0.0

    def evaluate(self, applicant, job):
        self.load_model()

        # ── 1. Build enriched skill list from manual skills + CV text ──────────
        manual_skills = [s.strip() for s in applicant.skills if s.strip()]
        cv_skills = []

        if applicant.cv_path:
            cv_text = extract_text_from_cv(applicant.cv_path)
            if cv_text:
                cv_skills = extract_skills_from_text(cv_text)
                print(f"[Matcher] Extracted {len(cv_skills)} skills from CV at: {applicant.cv_path}")
            else:
                print(f"[Matcher] CV text extraction returned empty for: {applicant.cv_path}")

        # Merge: manual skills take priority, CV skills fill in extras
        all_skill_keys = set(s.lower() for s in manual_skills)
        for s in cv_skills:
            if s.lower() not in all_skill_keys:
                manual_skills.append(s)
                all_skill_keys.add(s.lower())

        app_skills = manual_skills
        req_skills = job.required_skills

        # ── 2. Semantic Skill Matching ──────────────────────────────────────────
        matched_skills = []
        missing_skills = []
        skill_score = 0.0

        if req_skills and app_skills:
            req_embeddings = self.model.encode(req_skills)
            app_embeddings = self.model.encode(app_skills)
            sim_matrix = cosine_similarity(req_embeddings, app_embeddings)

            total_similarity = 0
            for i, req in enumerate(req_skills):
                best_match_idx = sim_matrix[i].argmax()
                best_match_score = sim_matrix[i][best_match_idx]

                if best_match_score > 0.6:
                    matched_skills.append(req)
                    total_similarity += 1.0
                else:
                    missing_skills.append(req)
                    total_similarity += best_match_score

            skill_score = (total_similarity / len(req_skills)) * 100

        elif not req_skills:
            skill_score = 100.0
        else:
            skill_score = 0.0
            missing_skills = req_skills

        # ── 3. CV Content Bonus: check job description keywords in CV text ──────
        cv_bonus = 0.0
        if applicant.cv_path:
            cv_text = extract_text_from_cv(applicant.cv_path)
            if cv_text and req_skills:
                cv_text_lower = cv_text.lower()
                hits = sum(1 for s in req_skills if s.lower() in cv_text_lower)
                cv_bonus = (hits / len(req_skills)) * 5.0  # up to +5 bonus points
                print(f"[Matcher] CV keyword hit bonus: +{cv_bonus:.1f} pts ({hits}/{len(req_skills)} keywords found in CV body)")

        # ── 4. Experience Check ─────────────────────────────────────────────────
        req_years = self.extract_years(job.experience_required)
        app_years = applicant.experience
        exp_score = 100.0
        if req_years > 0:
            exp_score = 100.0 if app_years >= req_years else (app_years / req_years) * 100.0

        # ── 5. Overall Score ────────────────────────────────────────────────────
        # Weight: 75% Skills (semantic), 20% Experience, 5% CV Content Bonus
        overall_score = (skill_score * 0.75) + (exp_score * 0.20) + cv_bonus
        overall_score = min(100.0, max(0.0, overall_score))

        # ── 6. Recommendation ──────────────────────────────────────────────────
        if overall_score >= 85:
            rec = "Excellent Match"
        elif overall_score >= 70:
            rec = "Good Match"
        elif overall_score >= 50:
            rec = "Moderate Match"
        else:
            rec = "Low Match"

        return {
            "match_score": round(overall_score, 2),
            "matched_skills": list(set(matched_skills)),
            "missing_skills": list(set(missing_skills)),
            "recommendation": rec,
            "cv_skills_extracted": len(cv_skills),
        }
