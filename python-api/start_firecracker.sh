#!/bin/bash

# Usage: ./start_firecracker.sh <user_id> <vm_name>
USER_ID=$1
VM_NAME=$2

if [ -z "$USER_ID" ] || [ -z "$VM_NAME" ]; then
    echo "Usage: $0 <user_id> <vm_name>"
    exit 1
fi

# Créer le dossier pour les sockets
SOCKET_DIR="/tmp/firecracker-sockets"
mkdir -p "$SOCKET_DIR"
chmod 777 "$SOCKET_DIR"

# Définir le chemin du socket
SOCKET_PATH="${SOCKET_DIR}/${USER_ID}_${VM_NAME}.socket"

# Supprimer le socket s'il existe
rm -f "$SOCKET_PATH"

# Démarrer Firecracker
firecracker \
    --api-sock "$SOCKET_PATH" \
    
