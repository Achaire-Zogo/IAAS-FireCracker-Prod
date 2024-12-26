from fastapi import FastAPI, HTTPException, BackgroundTasks, Depends
from pydantic import BaseModel
from typing import Optional, List, Dict
import subprocess
import json
import os
import time
import logging
from datetime import datetime
from sqlalchemy import create_engine, Column, Integer, Float, ForeignKey
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session
import os
from dotenv import load_dotenv

# Charger les variables d'environnement
load_dotenv()

# Configuration de la base de données
MYSQL_USER = os.getenv("DB_USERNAME", "kairoskm")
MYSQL_PASSWORD = os.getenv("DB_PASSWORD", "kairoskm")
MYSQL_HOST = os.getenv("DB_HOST", "localhost")
MYSQL_PORT = os.getenv("DB_PORT", "3306")
MYSQL_DATABASE = os.getenv("DB_DATABASE", "firecracker")

DATABASE_URL = f"mysql+pymysql://{MYSQL_USER}:{MYSQL_PASSWORD}@{MYSQL_HOST}:{MYSQL_PORT}/{MYSQL_DATABASE}"

# Configuration SQLAlchemy
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

# Modèle SQLAlchemy pour la VM
class VirtualMachine(Base):
    __tablename__ = "virtual_machines"

    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    cpu_usage_percent = Column(Float)
    memory_usage_mib = Column(Float)
    disk_usage_bytes = Column(Float)

# Modèle Pydantic pour la validation des données
class MetricsUpdate(BaseModel):
    user_id: int
    vm_id: int
    cpu_usage: float
    memory_usage: float
    disk_usage: float

# Dépendance pour obtenir la session de base de données
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# Configure logging
log_dir = "logs"
os.makedirs(log_dir, exist_ok=True)
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(os.path.join(log_dir, 'firecracker.log')),
        logging.StreamHandler()  # Pour afficher aussi dans la console
    ]
)
logger = logging.getLogger(__name__)

app = FastAPI(title="Firecracker VM Manager API FOR Metrics")



class CommandResponse(BaseModel):
    success: bool
    message: str
    data: Optional[dict] = None



@app.post("/api/cpu_metrics")
async def update_cpu_metrics(metrics: MetricsUpdate, db: Session = Depends(get_db)):
    try:
        # Rechercher la VM
        # print(metrics)
        vm = db.query(VirtualMachine).filter(
            VirtualMachine.id == metrics.vm_id,
            VirtualMachine.user_id == metrics.user_id
        ).first()

        if not vm:
            raise HTTPException(
                status_code=404,
                detail="Machine virtuelle introuvable"
            )

        # Mettre à jour les métriques
        vm.cpu_usage_percent = metrics.cpu_usage
        vm.memory_usage_mib = metrics.memory_usage
        vm.disk_usage_bytes = metrics.disk_usage
        
        # Sauvegarder les changements
        db.commit()

        return {
            "message": "Métriques enregistrées avec succès"
        }

    except HTTPException as e:
        raise e
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Une erreur s'est produite lors de la mise à jour des métriques : {str(e)}"
        )

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=5001)
