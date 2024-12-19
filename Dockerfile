FROM ubuntu:22.04

# Prevent interactive prompts during package installation
ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    python3 \
    python3-pip \
    libguestfs-tools \
    linux-image-generic \
    qemu-utils \
    qemu-system-x86 \
    libvirt-daemon \
    supermin \
    build-essential \
    sudo \
    && rm -rf /var/lib/apt/lists/*

# Set environment variables for libguestfs
ENV LIBGUESTFS_DEBUG=1
ENV LIBGUESTFS_TRACE=1
ENV SUPERMIN_KERNEL=/boot/vmlinuz-$(uname -r)
ENV SUPERMIN_MODULES=/lib/modules/$(uname -r)
ENV SUPERMIN_KERNEL_VERSION=$(uname -r)

# Update kernel modules
RUN update-initramfs -u

# Copy the Firecracker tar file from the host to the container
COPY ./firecracker-v1.5.0-x86_64.tgz /tmp/firecracker-v1.5.0-x86_64.tgz

# Extract and install Firecracker
RUN tar -xzvf /tmp/firecracker-v1.5.0-x86_64.tgz -C /tmp \
    && mv /tmp/release-v1.5.0-x86_64/firecracker-v1.5.0-x86_64 /usr/local/bin/firecracker \
    && chmod +x /usr/local/bin/firecracker \
    && rm -rf /tmp/*

# Create necessary directories
RUN mkdir -p /tmp/vm-images /var/tmp/builder

# Initialize libguestfs
RUN libguestfs-test-tool || true

WORKDIR /app

# Copy application files
COPY requirements.txt .
COPY app.py .

# Install Python dependencies
RUN pip3 install -r requirements.txt

EXPOSE 8000

# Add startup script
RUN echo '#!/bin/bash\n\
update-initramfs -u\n\
libguestfs-test-tool\n\
uvicorn app:app --host 0.0.0.0 --port 8000' > /start.sh && \
chmod +x /start.sh

CMD ["/start.sh"]
