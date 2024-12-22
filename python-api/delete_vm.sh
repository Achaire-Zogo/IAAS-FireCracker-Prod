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

# Arrêter la VM si elle est en cours d'exécution
if [ -S "${SOCKET_PATH}" ]; then
    response=$(curl --unix-socket "${SOCKET_PATH}" -i \
      -X PUT 'http://localhost/actions' \
      -H 'Accept: application/json' \
      -H 'Content-Type: application/json' \
      -d '{
        "action_type": "SendCtrlAltDel"
      }')

    check_curl_response "$response" "Stopping VM before deletion" ${LINENO} "$LOG_PATH" || {
        get_last_error "$LOG_PATH"
        echo "Warning: Failed to stop VM gracefully, proceeding with deletion"
    }
    sleep 2  # Attendre que la VM s'arrête
fi

# Supprimer le socket s'il existe
if [ -S "${SOCKET_PATH}" ]; then
    rm -f "${SOCKET_PATH}"
fi

# Supprimer les logs s'ils existent
if [ -f "${LOG_PATH}" ]; then
    rm -f "${LOG_PATH}"
fi

# Supprimer le répertoire de la VM
if [ -d "${VM_DIR}" ]; then
    rm -rf "${VM_DIR}"
fi

echo "VM deleted successfully"
