#!/bin/bash

# Vérifier le nombre d'arguments
if [ "$#" -ne 7 ]; then
    echo "Usage: $0 <os_type> <user_id> <ssh_public_key> <disk_size_gb> <vm_name> <vcpu_count> <mem_size_mib>"
    exit 1
fi

# Récupérer les arguments
OS_TYPE=$1
USER_ID=$2
SSH_PUBLIC_KEY=$3
DISK_SIZE_GB=$4
VM_NAME=$5
VCPU_COUNT=$6
MEM_SIZE_MIB=$7

# Définir les chemins
BASE_DIR="/opt/firecracker"
VM_DIR="${BASE_DIR}/vm/${USER_ID}/${VM_NAME}"
SOCKET_PATH="/tmp/firecracker-sockets/${USER_ID}_${VM_NAME}.socket"
CUSTOM_VM="${VM_DIR}/${OS_TYPE}.ext4"
KERNEL_PATH="${BASE_DIR}/vmlinux-5.10"
LOG_PATH="${BASE_DIR}/logs/firecracker-${USER_ID}_${VM_NAME}.log"

# Vérifier que les fichiers nécessaires existent
if [ ! -f "${KERNEL_PATH}" ]; then
    echo "Error: Kernel file not found at ${KERNEL_PATH}"
    exit 1
fi

if [ ! -f "${CUSTOM_VM}" ]; then
    echo "Error: Custom VM image not found at ${CUSTOM_VM}"
    exit 1
fi

# Configurer le réseau
TAP_DEV="tap0"
TAP_IP="172.16.0.1"
VM_IP="172.16.0.2"

# Créer et configurer l'interface tap si elle n'existe pas
if ! ip link show "${TAP_DEV}" &>/dev/null; then
    sudo ip tuntap add "${TAP_DEV}" mode tap
    sudo ip addr add "${TAP_IP}/24" dev "${TAP_DEV}"
    sudo ip link set "${TAP_DEV}" up
fi

# Configuration de la VM via l'API Firecracker
curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/machine-config' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"vcpu_count\": ${VCPU_COUNT},
    \"mem_size_mib\": ${MEM_SIZE_MIB},
    \"ht_enabled\": false
  }"

# Configuration du kernel
curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/boot-source' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"kernel_image_path\": \"${KERNEL_PATH}\",
    \"boot_args\": \"console=ttyS0 reboot=k panic=1 pci=off\"
  }"

# Configuration du rootfs
curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/drives/rootfs' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"drive_id\": \"rootfs\",
    \"path_on_host\": \"${CUSTOM_VM}\",
    \"is_root_device\": true,
    \"is_read_only\": false
  }"

# Configuration du réseau
curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/network-interfaces/eth0' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"iface_id\": \"eth0\",
    \"guest_mac\": \"AA:FC:00:00:00:01\",
    \"host_dev_name\": \"${TAP_DEV}\"
  }"

echo "VM configuration completed successfully"