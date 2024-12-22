#!/bin/bash

source "$(dirname "$0")/check_curl_response.sh"

# Vérifier le nombre d'arguments
if [ "$#" -ne 5 ]; then
    echo "Usage: $0 <user_id> <vm_name> <os_type> <cpu_count> <memory_size_mib>"
    exit 1
fi

# Récupérer les arguments
USER_ID=$1
VM_NAME=$2
OS_TYPE=$3
VCPU_COUNT=$4
MEM_SIZE_MIB=$5

# Définir les chemins
VM_DIR="/opt/firecracker/vm/${USER_ID}/${VM_NAME}"
SOCKET_PATH="/tmp/firecracker-sockets/${USER_ID}_${VM_NAME}.socket"
LOG_PATH="/opt/firecracker/logs/firecracker-${USER_ID}_${VM_NAME}.log"
PID_FILE="/opt/firecracker/logs/firecracker-${USER_ID}_${VM_NAME}.pid"
KERNEL_PATH="${VM_DIR}/vmlinux-5.10.225"
CUSTOM_VM="${VM_DIR}/${OS_TYPE}.ext4"

TAP_DEVICE="tap0"
TAP_IP="172.16.0.1"
VM_IP="172.16.0.2"
MASK_SHORT="/30"
FC_MAC="06:00:AC:10:00:02"

# Vérifier que la VM existe
if [ ! -d "${VM_DIR}" ]; then
    echo "Error: VM directory not found at ${VM_DIR}"
    exit 1
fi

# Configuration du kernel
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/boot-source' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"kernel_image_path\": \"${KERNEL_PATH}\",
    \"boot_args\": \"console=ttyS0 reboot=k panic=1 pci=off ip=${VM_IP}::${TAP_IP}:255.255.255.252::eth0:off\"
  }")

check_curl_response "$response" "Configuring kernel" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}



# Configuration de la machine
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/machine-config' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"vcpu_count\": ${VCPU_COUNT},
    \"mem_size_mib\": ${MEM_SIZE_MIB},
    \"track_dirty_pages\": true
  }")

check_curl_response "$response" "Configuring machine" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}

# Configuration du rootfs
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/drives/rootfs' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"drive_id\": \"rootfs\",
    \"path_on_host\": \"${CUSTOM_VM}\",
    \"is_root_device\": true,
    \"is_read_only\": false
  }")

check_curl_response "$response" "Configuring rootfs" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}

# Configuration du réseau
ip link set "${TAP_DEVICE}" up || {
    ip tuntap add "${TAP_DEVICE}" mode tap
    ip link set "${TAP_DEVICE}" up
    ip addr add "${TAP_IP}${MASK_SHORT}" dev "${TAP_DEVICE}"
}

# Configuration du réseau
network_config="{
  \"iface_id\": \"eth0\",
  \"guest_mac\": \"${FC_MAC}\",
  \"host_dev_name\": \"${TAP_DEVICE}\"
}"
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT "http://localhost/network-interfaces/eth0" \
  -H "accept: application/json" \
  -H "Content-Type: application/json" \
  -d "${network_config}")

check_curl_response "$response" "Configuring network interface" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}



# Configuration du balloon
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/balloon' \
  -H 'Content-Type: application/json' \
  -d '{
    "amount_mib": 512,
    "deflate_on_oom": true,
    "stats_polling_interval_s": 1
  }')

check_curl_response "$response" "Configuring balloon" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}

# Démarrer la machine
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/actions' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "action_type": "InstanceStart"
  }')

check_curl_response "$response" "Starting VM" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}

echo "VM started successfully"