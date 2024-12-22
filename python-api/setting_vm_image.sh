#!/bin/bash

source "$(dirname "$0")/check_curl_response.sh"

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
KERNEL_PATH="${VM_DIR}/vmlinux-5.10.225"
LOG_PATH="${BASE_DIR}/logs/firecracker-${USER_ID}_${VM_NAME}.log"

# Créer le répertoire de logs s'il n'existe pas
sudo mkdir -p "$(dirname "$LOG_PATH")"
sudo chmod 777 "$(dirname "$LOG_PATH")"

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
MASK_SHORT="/30"
FC_MAC="06:00:AC:10:00:02"


sudo ip link del "$TAP_DEV" 2> /dev/null || true
sudo ip tuntap add dev "$TAP_DEV" mode tap
sudo ip addr add "${TAP_IP}${MASK_SHORT}" dev "$TAP_DEV"
sudo ip link set dev "$TAP_DEV" up

# Enable ip forwarding
sudo sh -c "echo 1 > /proc/sys/net/ipv4/ip_forward"
sudo iptables -P FORWARD ACCEPT

HOST_IFACE=$(ip -j route list default |jq -r '.[0].dev')

# Set up microVM internet access
sudo iptables -t nat -D POSTROUTING -o "$HOST_IFACE" -j MASQUERADE || true
sudo iptables -t nat -A POSTROUTING -o "$HOST_IFACE" -j MASQUERADE

# Setup internet access in the guest
sudo ip route add default via "$TAP_IP" dev eth0

# Setup DNS resolution in the guest
sudo echo 'nameserver 8.8.8.8' > /etc/resolv.conf

# Configuration de la VM via l'API Firecracker
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/machine-config' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"vcpu_count\": ${VCPU_COUNT},
    \"mem_size_mib\": ${MEM_SIZE_MIB},
    \"smt\": false
  }")

check_curl_response "$response" "Configuring VM machine config" ${LINENO} "$LOG_PATH" || {
    get_last_error "$LOG_PATH"
    exit 1
}

# Configuration du kernel
response=$(curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/boot-source' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d "{
    \"kernel_image_path\": \"${KERNEL_PATH}\",
    \"boot_args\": \"console=ttyS0 reboot=k panic=1 pci=off\"
  }")

check_curl_response "$response" "Configuring kernel" ${LINENO} "$LOG_PATH" || {
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
network_config="{
  \"iface_id\": \"eth0\",
  \"guest_mac\": \"${FC_MAC}\",
  \"host_dev_name\": \"${TAP_DEV}\"
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



echo "VM configuration completed successfully"