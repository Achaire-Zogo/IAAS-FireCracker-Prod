#!/bin/bash

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

# Vérifier si la VM existe
if [ ! -d "${VM_DIR}" ]; then
    echo "{\"status\": \"not_found\"}"
    exit 0
fi

# Vérifier si la VM est en cours d'exécution
if [ -S "${SOCKET_PATH}" ]; then
    # Obtenir les métriques via l'API Firecracker
    METRICS=$(curl --unix-socket "${SOCKET_PATH}" -s \
      -X GET 'http://localhost/metrics' \
      -H 'Accept: application/json')
    
    if [ $? -eq 0 ]; then
        echo "{\"status\": \"running\", \"metrics\": ${METRICS}}"
    else
        echo "{\"status\": \"error\", \"message\": \"Failed to get metrics\"}"
    fi
else
    echo "{\"status\": \"stopped\"}"
fi
