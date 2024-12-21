#!/bin/bash

# Définir les variables
ROOTFS_DIR="/opt/firecracker/rootfs"
TEMP_DIR="/tmp/firecracker-images"
KERNEL_DIR="/opt/firecracker"

# Créer les répertoires nécessaires
sudo mkdir -p $ROOTFS_DIR
sudo mkdir -p $TEMP_DIR
sudo mkdir -p $KERNEL_DIR

# Fonction pour télécharger et préparer Ubuntu
prepare_ubuntu() {
    echo "Préparation de l'image Ubuntu 22.04..."
    
    # Télécharger l'image cloud Ubuntu
    wget https://cloud-images.ubuntu.com/jammy/current/jammy-server-cloudimg-amd64.img -O $TEMP_DIR/ubuntu.img
    
    # Convertir l'image en ext4
    qemu-img convert -O raw $TEMP_DIR/ubuntu.img $ROOTFS_DIR/ubuntu-22.04.ext4
    
    # Redimensionner l'image à 5GB
    truncate -s 5G $ROOTFS_DIR/ubuntu-22.04.ext4
    e2fsck -f $ROOTFS_DIR/ubuntu-22.04.ext4
    resize2fs $ROOTFS_DIR/ubuntu-22.04.ext4
}

# Fonction pour télécharger et préparer Debian
prepare_debian() {
    echo "Préparation de l'image Debian 11..."
    
    # Télécharger l'image cloud Debian
    wget https://cloud.debian.org/images/cloud/bullseye/latest/debian-11-generic-amd64.qcow2 -O $TEMP_DIR/debian.qcow2
    
    # Convertir l'image en ext4
    qemu-img convert -O raw $TEMP_DIR/debian.qcow2 $ROOTFS_DIR/debian-11.ext4
    
    # Redimensionner l'image à 5GB
    truncate -s 5G $ROOTFS_DIR/debian-11.ext4
    e2fsck -f $ROOTFS_DIR/debian-11.ext4
    resize2fs $ROOTFS_DIR/debian-11.ext4
}

# Fonction pour télécharger et préparer CentOS
prepare_centos() {
    echo "Préparation de l'image CentOS Stream 9..."
    
    # Télécharger l'image cloud CentOS
    wget https://cloud.centos.org/centos/9-stream/x86_64/images/CentOS-Stream-GenericCloud-9-latest.x86_64.qcow2 -O $TEMP_DIR/centos.qcow2
    
    # Convertir l'image en ext4
    qemu-img convert -O raw $TEMP_DIR/centos.qcow2 $ROOTFS_DIR/centos-9.ext4
    
    # Redimensionner l'image à 5GB
    truncate -s 5G $ROOTFS_DIR/centos-9.ext4
    e2fsck -f $ROOTFS_DIR/centos-9.ext4
    resize2fs $ROOTFS_DIR/centos-9.ext4
}

# Télécharger le kernel Linux
download_kernel() {
    echo "Téléchargement du kernel Linux..."
    wget https://s3.amazonaws.com/spec.ccfc.min/img/quickstart_guide/x86_64/kernels/vmlinux.bin -O $KERNEL_DIR/vmlinux
}

# Installation des dépendances nécessaires
echo "Installation des dépendances..."
sudo apt-get update
sudo apt-get install -y wget qemu-utils e2fsprogs cloud-utils

# Exécuter les fonctions de préparation
prepare_ubuntu
prepare_debian
prepare_centos
download_kernel

# Nettoyer les fichiers temporaires
echo "Nettoyage des fichiers temporaires..."
rm -rf $TEMP_DIR

echo "Installation terminée !"
echo "Les images sont disponibles dans $ROOTFS_DIR"
echo "Le kernel est disponible dans $KERNEL_DIR/vmlinux"
