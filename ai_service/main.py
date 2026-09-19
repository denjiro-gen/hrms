from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
from services.matcher import JobMatcher

app = FastAPI(title="Bestlink HRMS AI Recruiter", version="2.0.0")
matcher = JobMatcher()


class Applicant(BaseModel):
    skills: List[str]
    education: str
    experience: float
    cv_path: Optional[str] = None   # Absolute path to the uploaded CV file on disk


class Job(BaseModel):
    required_skills: List[str]
    preferred_skills: Optional[List[str]] = []
    min_education: str
    experience_required: str


class MatchRequest(BaseModel):
    applicant: Applicant
    job: Job


class MatchResponse(BaseModel):
    match_score: float
    matched_skills: List[str]
    missing_skills: List[str]
    recommendation: str
    cv_skills_extracted: Optional[int] = 0


@app.post("/api/match", response_model=MatchResponse)
async def evaluate_applicant(req: MatchRequest):
    try:
        result = matcher.evaluate(req.applicant, req.job)
        return result
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/health")
def health_check():
    return {"status": "ok", "model_loaded": matcher.is_loaded(), "version": "2.0.0 (CV Analysis)"}


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8000)
