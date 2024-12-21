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
SOCKET_PATH="/tmp/firecracker-sockets/${USER_ID}_${VM_NAME}.socket"

# Vérifier que le socket existe
if [ ! -S "${SOCKET_PATH}" ]; then
    echo "Error: Socket not found at ${SOCKET_PATH}"
    exit 1
fi

# Démarrer la VM via l'API Firecracker
curl --unix-socket "${SOCKET_PATH}" -i \
  -X PUT 'http://localhost/actions' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{
    "action_type": "InstanceStart"
  }'

echo "VM started successfully"