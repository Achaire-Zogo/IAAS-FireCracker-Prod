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
VM_DIR="/opt/firecracker/vm/${USER_ID}/${VM_NAME}"
SOCKET_PATH="/tmp/firecracker-sockets/${USER_ID}_${VM_NAME}.socket"
LOG_PATH="/opt/firecracker/logs/firecracker-${USER_ID}_${VM_NAME}.log"

# Vérifier si la VM existe
if [ ! -d "${VM_DIR}" ]; then
    echo "{\"status\": \"not_found\"}"
    exit 0
fi

# Vérifier si la VM est en cours d'exécution
if [ -S "${SOCKET_PATH}" ]; then
    # Obtenir les métriques via l'API Firecracker
    response=$(curl --unix-socket "${SOCKET_PATH}" -s \
      -X GET 'http://localhost/metrics' \
      -H 'Accept: application/json')
    
    if check_curl_response "$response" "Getting VM metrics" ${LINENO} "$LOG_PATH"; then
        echo "{\"status\": \"running\", \"metrics\": ${response}}"
    else
        get_last_error "$LOG_PATH"
        echo "{\"status\": \"error\", \"message\": \"Failed to get metrics\"}"
        exit 1
    fi
else
    echo "{\"status\": \"stopped\"}"
fi
