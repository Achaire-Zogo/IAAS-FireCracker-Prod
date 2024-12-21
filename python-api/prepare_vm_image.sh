#!/bin/bash

# Download kernel if not exists
# if [ ! -f "/opt/firecracker/vmlinux-5.10" ]; then
#     sudo mkdir -p /opt/firecracker
#     wget -O vmlinux.tmp "https://s3.amazonaws.com/spec.ccfc.min/firecracker-ci/v1.11/x86_64/vmlinux-5.10"
#     sudo mv vmlinux.tmp /opt/firecracker/vmlinux-5.10
#     sudo chmod +x /opt/firecracker/vmlinux-5.10
# fi

# Usage: ./prepare_vm_image.sh <os_type> <user_id> <ssh_public_key> <disk_size_gb> <vm_name>
# Example: ./prepare_vm_image.sh ubuntu-24.04 user123 "ssh-rsa AAAA..." 5 "zaz"

OS_TYPE=$1
USER_ID=$2
SSH_PUBLIC_KEY=$3
DISK_SIZE_GB=$4
VM_NAME=$5

if [ -z "$OS_TYPE" ] || [ -z "$USER_ID" ] || [ -z "$SSH_PUBLIC_KEY" ] || [ -z "$DISK_SIZE_GB" ] || [ -z "$VM_NAME" ]; then
    echo "Usage: $0 <os_type> <user_id> <ssh_public_key> <disk_size_gb> <vm_name>"
    exit 1
fi

ARCH="$(uname -m)"
BASE_DIR="/opt/firecracker"
VM_DIR="${BASE_DIR}/vm/${USER_ID}/${VM_NAME}"
ROOTFS_DIR="${BASE_DIR}/rootfs"

# Create directories with proper permissions
sudo mkdir -p "${VM_DIR}"
sudo chown -R $USER:$USER "${BASE_DIR}"
sudo chmod -R 755 "${BASE_DIR}"

# Download base image if not exists
BASE_SQUASHFS="${ROOTFS_DIR}/${OS_TYPE}.squashfs.upstream"
if [ ! -f "${BASE_SQUASHFS}" ]; then
    case ${OS_TYPE} in
        "ubuntu-24.04")
            wget -O "${BASE_SQUASHFS}" "https://s3.amazonaws.com/spec.ccfc.min/firecracker-ci/v1.11/${ARCH}/ubuntu-24.04.squashfs"
            ;;
        "ubuntu-22.04")
            wget -O "${BASE_SQUASHFS}" "https://s3.amazonaws.com/spec.ccfc.min/firecracker-ci/v1.11/${ARCH}/ubuntu-22.04.squashfs"
            ;;
        "alpine")
            wget -O "${BASE_SQUASHFS}" "https://s3.amazonaws.com/spec.ccfc.min/firecracker-ci/v1.11/${ARCH}/alpine.squashfs"
            ;;
        "centos")
            wget -O "${BASE_SQUASHFS}" "https://s3.amazonaws.com/spec.ccfc.min/firecracker-ci/v1.11/${ARCH}/centos.squashfs"
            ;;
        *)
            echo "Unsupported OS type: ${OS_TYPE}"
            exit 1
            ;;
    esac
fi

sudo cp "${ROOTFS_DIR}/${OS_TYPE}.squashfs.upstream" "${VM_DIR}/"
cd "${VM_DIR}"

# Extract base image
unsquashfs "${OS_TYPE}.squashfs.upstream"

# Configure SSH for the user
mkdir -p squashfs-root/root/.ssh
echo "${SSH_PUBLIC_KEY}" > squashfs-root/root/.ssh/authorized_keys

# create ext4 filesystem image
CUSTOM_VM_DIR="${VM_DIR}/${OS_TYPE}.ext4"
sudo chown -R root:root squashfs-root
truncate -s ${DISK_SIZE_GB}G "${OS_TYPE}.ext4"
sudo mkfs.ext4 -d squashfs-root -F "${CUSTOM_VM_DIR}"

# Cleanup
cd - > /dev/null
sudo rm -rf "${VM_DIR}/${OS_TYPE}.squashfs.upstream"

echo "Custom rootfs created at: ${CUSTOM_VM_DIR}"
