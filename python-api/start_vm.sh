#!/bin/bash

source "$(dirname "$0")/check_curl_response.sh"

# Vérifier le nombre d'arguments
if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <user_id> <vm_name>"
    exit 1
fi

# Récupérer les arguments
USER_ID=$1
VM_NAME=$2

# Définir les chemins
SOCKET_PATH="/tmp/firecracker-sockets/${USER_ID}_${VM_NAME}.socket"
LOG_PATH="/opt/firecracker/logs/firecracker-${USER_ID}_${VM_NAME}.log"

# Vérifier que le socket existe
if [ ! -S "${SOCKET_PATH}" ]; then
    echo "Error: Socket not found at ${SOCKET_PATH}"
    exit 1
fi

# Créer le répertoire de logs s'il n'existe pas
sudo mkdir -p "$(dirname "$LOG_PATH")"
sudo chmod 777 "$(dirname "$LOG_PATH")"

# Démarrer la VM via l'API Firecracker
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