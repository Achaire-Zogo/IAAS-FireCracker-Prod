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

app = FastAPI(title="Firecracker VM Manager API")

class VMConfig(BaseModel):
    name: str
    user_id: str  # Identifiant unique de l'utilisateur
    cpu_count: int
    memory_size_mib: int
    disk_size_gb: int
    os_type: str  # 'ubuntu-24.04', 'ubuntu-22.04', 'alpine', 'centos'
    ssh_public_key: Optional[str] = None  # Clé SSH publique de l'utilisateur
    root_password: Optional[str] = None
    tap_device: Optional[str] = "tap0"
    tap_ip: Optional[str] = "172.16.0.1"
    vm_ip: Optional[str] = "172.16.0.2"
    vm_mac: Optional[str] = "00:00:00:00:00:00"

class VMStartConfig(BaseModel):
    name: str
    user_id: str  # Identifiant unique de l'utilisateur
    cpu_count: int
    os_type: str
    memory_size_mib: int
    disk_size_gb: int
    vm_mac: str
    tap_device: Optional[str] = "tap0"
    tap_ip: Optional[str] = "172.16.0.1"
    vm_ip: Optional[str] = "172.16.0.2"

class VMStopConfig(BaseModel):
    name: str
    user_id: str  # Identifiant unique de l'utilisateur
    tap_device: Optional[str] = "tap0"

class VMDeleteConfig(BaseModel):
    name: str
    user_id: str  # Identifiant unique de l'utilisateur
    tap_device: Optional[str] = "tap0"

class VMStatusConfig(BaseModel):
    name: str
    user_id: str  # Identifiant unique de l'utilisateur

class VMStatus(BaseModel):
    name: str
    status: str
    cpu_usage: Optional[float] = None
    memory_usage: Optional[float] = None
    uptime: Optional[str] = None

class CommandResponse(BaseModel):
    success: bool
    message: str
    data: Optional[dict] = None

class FirecrackerAPI:
    def __init__(self, socket_path: str):
        self.socket_path = socket_path

    def _make_request(self, method: str, path: str, data: dict = None) -> dict:
        try:
            curl_cmd = [
                "curl",
                "-X", method,
                "--unix-socket", self.socket_path,
                f"http://localhost{path}"
            ]
            
            if data:
                curl_cmd.extend(["-H", "Content-Type: application/json"])
                curl_cmd.extend(["-d", json.dumps(data)])
            
            result = subprocess.run(
                curl_cmd,
                capture_output=True,
                text=True
            )
            
            if result.returncode == 0:
                try:
                    return json.loads(result.stdout) if result.stdout else {}
                except json.JSONDecodeError:
                    return {"raw_output": result.stdout}
            else:
                logger.error(f"Curl command failed: {result.stderr}")
                return {"error": result.stderr}
                
        except Exception as e:
            logger.error(f"Error making request: {str(e)}")
            return {"error": str(e)}

    def get_metrics(self) -> Dict:
        """
        Récupère les métriques de la VM via l'API Firecracker.
        """
        try:
            logger.info("Getting VM metrics")
            machine_config = self._make_request("GET", "/machine-config")
            # vm_state = self._make_request("GET", "/vm")
            
            return {
                "machine_config": machine_config,
                # "state": vm_state
            }
        except Exception as e:
            logger.error(f"Error getting metrics: {str(e)}")
            return {}

    def start_instance(self) -> bool:
        try:
            logger.info("Starting instance")
            return self._make_request("PUT", "/actions", {"action_type": "InstanceStart"})
        except Exception as e:
            logger.error(f"Error starting instance: {str(e)}")
            return False

    def stop_instance(self) -> bool:
        try:
            logger.info("Stopping instance")
            # D'abord, envoyer un signal d'arrêt gracieux
            self._make_request("PUT", "/actions", {"action_type": "SendCtrlAltDel"})
            
            # Attendre quelques secondes pour l'arrêt gracieux
            time.sleep(5)
            
            # Ensuite, forcer l'arrêt si nécessaire
            return self._make_request("PUT", "/actions", {"action_type": "InstanceHalt"})
        except Exception as e:
            logger.error(f"Error stopping instance: {str(e)}")
            return False

    def get_machine_config(self) -> Dict:
        try:
            logger.info("Getting machine config")
            curl_cmd = [
                "curl",
                "-X", "GET",
                "--unix-socket", self.socket_path,
                "http://localhost/machine-config"
            ]
            
            result = subprocess.run(
                curl_cmd,
                capture_output=True,
                text=True
            )
            
            if result.returncode == 0:
                return json.loads(result.stdout)
            return {}
        except Exception as e:
            logger.error(f"Error getting machine config: {str(e)}")
            return {}

def start_firecracker_process(user_id: str, vm_name: str, socket_path: str) -> None:
    """
    Démarre le processus Firecracker et attend que le socket soit disponible.
    
    Args:
        user_id (str): ID de l'utilisateur
        vm_name (str): Nom de la VM
        socket_path (str): Chemin du socket Firecracker
    
    Raises:
        HTTPException: Si le démarrage échoue ou le timeout est atteint
    """
    logger.info("Starting Firecracker process")
    firecracker_process = subprocess.Popen([
        "./start_firecracker.sh",
        user_id,
        vm_name
    ], stdout=subprocess.PIPE, stderr=subprocess.PIPE)

    # Attendre que le socket soit disponible
    timeout = 30
    start_time = time.time()
    while not os.path.exists(socket_path):
        if time.time() - start_time > timeout:
            stderr_output = firecracker_process.stderr.read().decode()
            stdout_output = firecracker_process.stdout.read().decode()
            logger.error(f"Socket not created after {timeout} seconds")
            logger.error(f"Firecracker stdout: {stdout_output}")
            logger.error(f"Firecracker stderr: {stderr_output}")
            
            # Vérifier les logs Firecracker
            log_path = f"/opt/firecracker/logs/firecracker-{user_id}_{vm_name}.log"
            if os.path.exists(log_path):
                with open(log_path, 'r') as f:
                    logger.error(f"Firecracker logs: {f.read()}")
            
            raise HTTPException(
                status_code=500,
                detail=f"Failed to start Firecracker. Stderr: {stderr_output}"
            )
        time.sleep(0.1)

    logger.info("Socket is available, waiting for API")
    time.sleep(2)  # Attendre que l'API soit prête

@app.get("/")
async def read_root():
    return {"message": "Firecracker VM Manager API"}

@app.post("/vm/create", response_model=CommandResponse)
async def create_vm(vm_config: VMConfig, background_tasks: BackgroundTasks):
    try:
        logger.info(f"Creating VM: {vm_config.name} for user: {vm_config.user_id}")
        
        # Valider les paramètres
        if not vm_config.ssh_public_key and not vm_config.root_password:
            raise HTTPException(
                status_code=400, 
                detail="Either ssh_public_key or root_password must be provided"
            )
            
        if not vm_config.root_password:
            vm_config.root_password = "FirecrackerVM@2024"  # Mot de passe par défaut si non fourni
            
        if not vm_config.ssh_public_key:
            vm_config.ssh_public_key = ""  # Clé SSH vide si non fournie

        # Créer le dossier pour les sockets s'il n'existe pas
        socket_dir = "/tmp/firecracker-sockets"
        os.makedirs(socket_dir, exist_ok=True)
        os.chmod(socket_dir, 0o777)  # Donner les permissions nécessaires
        
        # Définir le chemin du socket unique pour cette VM
        socket_path = f"{socket_dir}/{vm_config.user_id}_{vm_config.name}.socket"
        
        # Supprimer l'ancien socket s'il existe
        if os.path.exists(socket_path):
            os.unlink(socket_path)

        # Démarrer le processus Firecracker
        start_firecracker_process(vm_config.user_id, vm_config.name, socket_path)

        # Créer le dossier de la VM
        vm_path = f"/opt/firecracker/vm/{vm_config.user_id}/{vm_config.name}"
        if os.path.exists(vm_path):
            raise HTTPException(status_code=400, detail="VM already exists")

        os.makedirs(vm_path, exist_ok=True)

        # Préparer l'image personnalisée si elle n'existe pas
        custom_vm = f"/opt/firecracker/vm/{vm_config.user_id}/{vm_config.name}/{vm_config.os_type}.ext4"
        if not os.path.exists(custom_vm):
            logger.info(f"Preparing custom vm for user {vm_config.user_id}")
            prepare_result = subprocess.run(
                ["./prepare_vm_image.sh", 
                 vm_config.os_type,
                 vm_config.user_id,
                 vm_config.ssh_public_key,
                 str(vm_config.disk_size_gb),
                 vm_config.name,
                 vm_config.root_password
                ],
                capture_output=True,
                text=True
            )
            if prepare_result.returncode != 0:
                logger.error(f"Failed to prepare custom vm: {prepare_result.stderr}")
                raise HTTPException(status_code=500, detail="Failed to prepare custom vm")


        #Setup VM
        logger.info("Setting up VM")
        # Vérifier que tous les paramètres sont valides
        if not vm_config.user_id:
            raise HTTPException(status_code=400, detail="User ID is required")
        if not vm_config.name:
            raise HTTPException(status_code=400, detail="Name is required")
        if not vm_config.os_type:
            raise HTTPException(status_code=400, detail="OS type is required")
        if not vm_config.disk_size_gb:
            raise HTTPException(status_code=400, detail="Disk size is required")
        if not vm_config.cpu_count:
            raise HTTPException(status_code=400, detail="CPU count is required")
        if not vm_config.memory_size_mib:
            raise HTTPException(status_code=400, detail="Memory size is required")
        if not vm_config.tap_device:
            raise HTTPException(status_code=400, detail="Tap device is required")
        if not vm_config.tap_ip:
            raise HTTPException(status_code=400, detail="Tap IP is required")
        if not vm_config.vm_ip:
            raise HTTPException(status_code=400, detail="VM IP is required")
        if not vm_config.vm_mac:
            raise HTTPException(status_code=400, detail="VM MAC is required")

        setting_up_vm = subprocess.run(
                ["./setting_vm_image.sh", 
                 vm_config.os_type, 
                 vm_config.user_id, 
                 vm_config.ssh_public_key, 
                 str(vm_config.disk_size_gb), 
                 vm_config.name,
                 str(vm_config.cpu_count),
                 str(vm_config.memory_size_mib),
                 str(vm_config.tap_device),
                 str(vm_config.tap_ip),
                 str(vm_config.vm_ip),
                 str(vm_config.vm_mac)
                ],
                capture_output=True,
                text=True
            )
        if setting_up_vm.returncode != 0:
            logger.error(f"Failed to setting custom vm: {setting_up_vm.stderr}")
            raise HTTPException(status_code=500, detail="Failed to setting custom vm")        

        logger.info(f"VM {vm_config.name} created successfully")
        return CommandResponse(
            success=True,
            message=f"VM {vm_config.name} created successfully",
            data={"pid": 0}
        )

    except Exception as e:
        logger.error(f"Error creating VM: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/vm/start", response_model=CommandResponse)
async def start_vm(vm_start_config: VMStartConfig):
    """
    Démarre une VM existante.
    """
    try:
        # Vérifier si la VM existe
        vm_dir = os.path.join("/opt/firecracker/vm", vm_start_config.user_id, str(vm_start_config.name))
        if not os.path.exists(vm_dir):
            raise HTTPException(status_code=404, detail="VM not found")

        # Déterminer le type d'OS
        os_type = None
        for file in os.listdir(vm_dir):
            if file.endswith(".ext4"):
                os_type = file.replace(".ext4", "")
                break

        if os_type is None:
            raise HTTPException(status_code=404, detail="OS type not found")

        # Définir le chemin du socket unique pour cette VM
        socket_dir = "/tmp/firecracker-sockets"
        os.makedirs(socket_dir, exist_ok=True)
        os.chmod(socket_dir, 0o777)  # Donner les permissions nécessaires

        socket_path = f"{socket_dir}/{vm_start_config.user_id}_{vm_start_config.name}.socket"
        
        # Supprimer l'ancien socket s'il existe
        if os.path.exists(socket_path):
            os.unlink(socket_path)

        # Démarrer le processus Firecracker
        start_firecracker_process(vm_start_config.user_id, vm_start_config.name, socket_path)

        # Démarrer la VM
        logger.info(f"Starting VM {str(vm_start_config.name)}")
        start_result = subprocess.run(
            ["./start_vm.sh",
             vm_start_config.user_id,
             str(vm_start_config.name),
             str(vm_start_config.os_type),
             str(vm_start_config.cpu_count),
             str(vm_start_config.memory_size_mib),
             str(vm_start_config.disk_size_gb),
             str(vm_start_config.tap_device),
             str(vm_start_config.tap_ip),
             str(vm_start_config.vm_ip),
             str(vm_start_config.vm_mac)
            ],
            capture_output=True,
            text=True
        )

        if start_result.returncode != 0:
            logger.error(f"Failed to start VM: {start_result.stderr}")
            raise HTTPException(status_code=500, detail=f"Failed to start VM: {start_result.stderr}")

        return CommandResponse(
            success=True,
            message=f"VM {vm_start_config.name} started successfully"
        )

    except HTTPException as he:
        raise he
    except Exception as e:
        logger.error(f"Error starting VM: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/vm/stop", response_model=CommandResponse)
async def stop_vm(vm_stop_config: VMStopConfig):
    try:
        logger.info(f"Stopping VM: {vm_stop_config.name}")
        
        # Arrêter la VM
        stop_result = subprocess.run(
            ["./stop_vm.sh", vm_stop_config.user_id, vm_stop_config.name,vm_stop_config.tap_device],
            capture_output=True,
            text=True
        )
        
        if stop_result.returncode != 0:
            logger.error(f"Failed to stop VM: {stop_result.stderr}")
            raise HTTPException(status_code=500, detail="Failed to stop VM")

        logger.info(f"VM {vm_stop_config.name} stopped successfully")
        return CommandResponse(
            success=True,
            message=f"VM {vm_stop_config.name} stopped successfully"
        )

    except Exception as e:
        logger.error(f"Error stopping VM: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/vm/delete", response_model=CommandResponse)
async def delete_vm(vm_delete_config: VMDeleteConfig):
    try:
        logger.info(f"Deleting VM: {vm_delete_config.name}")
        
        # Supprimer la VM
        delete_result = subprocess.run(
            ["./delete_vm.sh", vm_delete_config.user_id, vm_delete_config.name, vm_delete_config.tap_device],
            capture_output=True,
            text=True
        )
        
        if delete_result.returncode != 0:
            logger.error(f"Failed to delete VM: {delete_result.stderr}")
            raise HTTPException(status_code=500, detail="Failed to delete VM")

        logger.info(f"VM {vm_delete_config.name} deleted successfully")
        return CommandResponse(
            success=True,
            message=f"VM {vm_delete_config.name} deleted successfully"
        )

    except Exception as e:
        logger.error(f"Error deleting VM: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/vm/status", response_model=VMStatus)
async def get_vm_status(vm_status_config: VMStatusConfig):
    try:
        logger.info(f"Getting status for VM: {vm_status_config.name}")
        
        # Obtenir le statut de la VM
        status_result = subprocess.run(
            ["./status_vm.sh", vm_status_config.user_id, vm_status_config.name],
            capture_output=True,
            text=True
        )
        
        if status_result.returncode != 0:
            logger.error(f"Failed to get VM status: {status_result.stderr}")
            raise HTTPException(status_code=500, detail="Failed to get VM status")

        # Parser la sortie JSON
        try:
            status_data = json.loads(status_result.stdout)
            return VMStatus(
                name=vm_status_config.name,
                status=status_data["status"],
                cpu_usage=status_data.get("metrics", {}).get("cpu_usage"),
                memory_usage=status_data.get("metrics", {}).get("memory_usage"),
                uptime=status_data.get("metrics", {}).get("uptime")
            )
        except json.JSONDecodeError:
            raise HTTPException(status_code=500, detail="Invalid status response format")

    except Exception as e:
        logger.error(f"Error getting VM status: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/vms", response_model=List[VMStatus])
async def list_vms():
    try:
        logger.info("Listing all VMs")
        
        # Obtenir la liste des VMs
        list_result = subprocess.run(
            ["./list_vms.sh"],
            capture_output=True,
            text=True
        )
        
        if list_result.returncode != 0:
            logger.error(f"Failed to list VMs: {list_result.stderr}")
            raise HTTPException(status_code=500, detail="Failed to list VMs")

        # Parser la sortie JSON
        try:
            vms_data = json.loads(list_result.stdout)
            return [
                VMStatus(
                    name=vm["name"],
                    status=vm["status"]
                )
                for vm in vms_data
            ]
        except json.JSONDecodeError:
            raise HTTPException(status_code=500, detail="Invalid list response format")

    except Exception as e:
        logger.error(f"Error listing VMs: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/vm/{user_id}/{vm_name}/metrics")
async def get_vm_metrics(user_id: str, vm_name: str):
    """
    Récupère les métriques d'une VM spécifique.
    """
    try:
        # Construire le chemin du socket
        socket_path = f"/tmp/firecracker-sockets/{user_id}_{vm_name}.socket"
        
        # Vérifier si la VM existe et est en cours d'exécution
        vm_dir = os.path.join("/opt/firecracker/vm", user_id, vm_name)
        pid_file = os.path.join('/opt/firecracker/logs', "firecracker-{user_id}_{vm_name}.pid")
        if not os.path.exists(vm_dir):
            raise HTTPException(status_code=404, detail="VM not found")
            
        if not os.path.exists(socket_path):
            return {
                "success": True,
                "data": {
                    "vm_name": vm_name,
                    "state": "stopped"
                }
            }

        # Vérifier si le processus est en cours d'exécution
        if os.path.exists(pid_file):
            with open(pid_file, 'r') as f:
                pid = f.read().strip()
                try:
                    os.kill(int(pid), 0)  # Vérifie si le processus existe
                except OSError:
                    return {
                        "success": True,
                        "data": {
                            "vm_name": vm_name,
                            "state": "stopped"
                        }
                    }

        # Créer une instance de l'API Firecracker
        api = FirecrackerAPI(socket_path)
        
        # Récupérer les métriques
        metrics = api.get_metrics()
        
        if "error" in metrics:
            raise HTTPException(status_code=500, detail=f"Failed to get metrics: {metrics['error']}")
            
        # Formater la réponse
        return {
            "success": True,
            "data": {
                "vm_name": vm_name,
                "state": "running",
                "machine_config": metrics.get("machine_config", {}),
                # "vm_state": metrics.get("state", {})
            }
        }
        
    except HTTPException as he:
        raise he
    except Exception as e:
        logger.error(f"Error getting VM metrics: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

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
    uvicorn.run(app, host="0.0.0.0", port=5000)
