FROM ubuntu:22.04

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    python3 \
    python3-pip \
    libguestfs-tools \
    qemu-utils \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# # Install Firecracker
# RUN curl -fsSL https://github.com/firecracker-microvm/firecracker/releases/download/v1.5.0/firecracker-v1.5.0-x86_64.tgz | tar -xz \
#     && mv release-v1.5.0-x86_64/firecracker-v1.5.0-x86_64 /usr/local/bin/firecracker \
#     && chmod +x /usr/local/bin/firecracker

# Copy the Firecracker tar file from the host to the container
COPY ./firecracker-v1.5.0-x86_64.tgz /tmp/firecracker-v1.5.0-x86_64.tgz

# Extract and install Firecracker
RUN tar -xzvf /tmp/firecracker-v1.5.0-x86_64.tgz -C /tmp \
    && mv /tmp/release-v1.5.0-x86_64/firecracker-v1.5.0-x86_64 /usr/local/bin/firecracker \
    && chmod +x /usr/local/bin/firecracker \
    && rm -rf /tmp/*

WORKDIR /app

# Copy application files
COPY requirements.txt .
COPY app.py .

# Install Python dependencies
RUN pip3 install -r requirements.txt

EXPOSE 8000

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000"]
