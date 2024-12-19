from fastapi import FastAPI, HTTPException, BackgroundTasks
from pydantic import BaseModel
import subprocess
import os
import json
import requests
import uuid
import logging
from typing import Dict, List, Optional

app = FastAPI(title="Firecracker VM Manager")

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class VMConfig(BaseModel):
    name: str
    vcpus: int = 2
    memory_mb: int = 1024
    os_type: str
    rootfs_size_gb: int = 5

class VMInfo(BaseModel):
    id: str
    name: str
    status: str
    config: VMConfig

# Store VM information
vms: Dict[str, VMInfo] = {}

class FirecrackerAPI:
    def __init__(self, socket_path: str):
        self.socket_path = socket_path
        self.base_url = f"http+unix://{socket_path}"

    def configure_machine(self, config: Dict) -> bool:
        response = requests.put(
            f"{self.base_url}/machine-config",
            json=config
        )
        return response.status_code == 204

    def configure_kernel(self, kernel_path: str) -> bool:
        config = {
            "kernel_image_path": kernel_path,
            "boot_args": "console=ttyS0 reboot=k panic=1"
        }
        response = requests.put(
            f"{self.base_url}/boot-source",
            json=config
        )
        return response.status_code == 204

    def configure_rootfs(self, rootfs_path: str) -> bool:
        config = {
            "drive_id": "rootfs",
            "path_on_host": rootfs_path,
            "is_root_device": True,
            "is_read_only": False
        }
        response = requests.put(
            f"{self.base_url}/drives/rootfs",
            json=config
        )
        return response.status_code == 204

    def start_instance(self) -> bool:
        response = requests.put(
            f"{self.base_url}/actions",
            json={"action_type": "InstanceStart"}
        )
        return response.status_code == 204

class ImageBuilder:
    def __init__(self, work_dir: str = "/tmp/vm-images"):
        self.work_dir = work_dir
        os.makedirs(work_dir, exist_ok=True)

    def build_image(self, os_type: str, size_gb: int) -> str:
        image_path = f"{self.work_dir}/{os_type}-{uuid.uuid4()}.ext4"
        
        # Use virt-builder to create the base image
        subprocess.run([
            "virt-builder", os_type,
            "--output", image_path,
            "--size", f"{size_gb}G",
            "--format", "raw",
            "--root-password", "password:root123",
            "--install", "openssh-server"
        ], check=True)
        
        return image_path

@app.post("/vms", response_model=VMInfo)
async def create_vm(config: VMConfig, background_tasks: BackgroundTasks):
    vm_id = str(uuid.uuid4())
    socket_path = f"/tmp/firecracker-{vm_id}.socket"
    
    # Create VMInfo object
    vm_info = VMInfo(
        id=vm_id,
        name=config.name,
        status="creating",
        config=config
    )
    vms[vm_id] = vm_info

    # Start VM creation in background
    background_tasks.add_task(
        create_vm_background,
        vm_id,
        socket_path,
        config
    )

    return vm_info

async def create_vm_background(vm_id: str, socket_path: str, config: VMConfig):
    try:
        # Build image
        image_builder = ImageBuilder()
        rootfs_path = image_builder.build_image(
            config.os_type,
            config.rootfs_size_gb
        )

        # Start Firecracker process
        subprocess.Popen([
            "firecracker",
            "--api-sock", socket_path
        ])

        # Configure VM
        api = FirecrackerAPI(socket_path)
        api.configure_machine({
            "vcpu_count": config.vcpus,
            "mem_size_mib": config.memory_mb
        })
        api.configure_kernel("/path/to/vmlinux.bin")
        api.configure_rootfs(rootfs_path)
        api.start_instance()

        # Update VM status
        vms[vm_id].status = "running"

    except Exception as e:
        logger.error(f"Failed to create VM: {e}")
        vms[vm_id].status = "failed"

@app.get("/vms", response_model=List[VMInfo])
async def list_vms():
    return list(vms.values())

@app.get("/vms/{vm_id}", response_model=VMInfo)
async def get_vm(vm_id: str):
    if vm_id not in vms:
        raise HTTPException(status_code=404, detail="VM not found")
    return vms[vm_id]

@app.delete("/vms/{vm_id}")
async def delete_vm(vm_id: str):
    if vm_id not in vms:
        raise HTTPException(status_code=404, detail="VM not found")
    
    socket_path = f"/tmp/firecracker-{vm_id}.socket"
    
    # Stop Firecracker process
    subprocess.run([
        "pkill", "-f", f"firecracker.*{socket_path}"
    ])
    
    # Clean up socket
    if os.path.exists(socket_path):
        os.remove(socket_path)
    
    # Remove VM from storage
    del vms[vm_id]
    
    return {"message": "VM deleted"}