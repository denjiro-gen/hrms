import os
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import re

class JobMatcher:
    def __init__(self):
        # Using a small, fast model for semantic similarity
        self.model_name = 'all-MiniLM-L6-v2'
        self.model = None
        # Lazy loading of model to speed up startup
        
    def load_model(self):
        if self.model is None:
            self.model = SentenceTransformer(self.model_name)
            
    def is_loaded(self):
        return self.model is not None
        
    def extract_years(self, text):
        """Extract numeric years from experience string"""
        if not text:
            return 0.0
        if isinstance(text, (int, float)):
            return float(text)
        match = re.search(r'(\d+(\.\d+)?)', str(text))
        return float(match.group(1)) if match else 0.0

    def evaluate(self, applicant, job):
        self.load_model()
        
        # 1. Skill Matching (Semantic)
        req_skills = job.required_skills
        app_skills = applicant.skills
        
        matched_skills = []
        missing_skills = []
        skill_score = 0.0
        
        if req_skills and app_skills:
            # Get embeddings
            req_embeddings = self.model.encode(req_skills)
            app_embeddings = self.model.encode(app_skills)
            
            # Calculate similarity matrix
            sim_matrix = cosine_similarity(req_embeddings, app_embeddings)
            
            # For each required skill, find the best matching applicant skill
            total_similarity = 0
            for i, req in enumerate(req_skills):
                best_match_idx = sim_matrix[i].argmax()
                best_match_score = sim_matrix[i][best_match_idx]
                
                # Threshold for considering it a match (e.g. 0.6)
                if best_match_score > 0.6:
                    matched_skills.append(req)
                    total_similarity += 1.0
                else:
                    missing_skills.append(req)
                    total_similarity += best_match_score # Partial credit
                    
            skill_score = (total_similarity / len(req_skills)) * 100
        elif not req_skills:
            skill_score = 100.0
        else:
            skill_score = 0.0
            missing_skills = req_skills

        # 2. Experience check
        req_years = self.extract_years(job.experience_required)
        app_years = applicant.experience
        
        exp_score = 100.0
        if req_years > 0:
            if app_years >= req_years:
                exp_score = 100.0
            else:
                exp_score = (app_years / req_years) * 100.0

        # Overall Score Calculation
        # Weight: 80% Skills, 20% Experience (simplified)
        overall_score = (skill_score * 0.8) + (exp_score * 0.2)
        overall_score = min(100.0, max(0.0, overall_score))

        # Recommendation logic
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
            "recommendation": rec
        }
