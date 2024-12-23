# IAAS FireCracker - Cloud VM Management Platform

A modern and user-friendly Infrastructure as a Service (IAAS) platform built with Laravel, allowing users to create and manage virtual machines using FireCracker technology.

![Dashboard Preview](docs/images/dashboard.png)

## Features

- **User Authentication & Authorization**
  - Secure registration and login
  - Role-based access control
  - Password reset functionality

- **Virtual Machine Management**
  - Create custom VMs with various configurations
  - Choose from multiple VM offers (CPU, RAM, Storage)
  - Select from different system images
  - Start/Stop VM operations
  - Real-time VM status monitoring

- **Dashboard & Statistics**
  - Overview of all VMs
  - Resource usage statistics
  - Cost tracking and billing information
  - System health monitoring

- **Security**
  - Secure VM access via SSH
  - Encrypted root passwords
  - CSRF protection
  - XSS prevention

## Prerequisites

- PHP >= 8.8
- Composer
- Node.js & NPM
- MySQL >= 8.0
- FireCracker installed and configured
- Linux environment (Ubuntu 20.04 or later recommended)

## Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/IAAS-FireCracker.git
cd IAAS-FireCracker/laravel-app
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install JavaScript dependencies**
```bash
npm install
```

4. **Environment Setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure your `.env` file**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

FIRECRACKER_PATH=/path/to/firecracker
KERNEL_PATH=/path/to/kernel
ROOTFS_PATH=/path/to/rootfs
```

6. **Database Setup**
```bash
php artisan migrate
php artisan db:seed
```

7. **Build assets**
```bash
npm run build
```

## Running the Application

1. **Start the Laravel development server**
```bash
php artisan serve --host 0.0.0.0
```

2. **Start the Vite development server (in development)**
```bash
npm run dev
```

The application will be available at `http://localhost:8000`

## Usage

1. **Register a new account**
   - Visit the registration page
   - Fill in your details
   - Verify your email address

2. **Create a Virtual Machine**
   - Go to the dashboard
   - Click "New VM"
   - Select VM offer and system image
   - Configure VM settings
   - Click "Create"

3. **Manage Your VMs**
   - View VM details
   - Start/Stop VMs
   - Monitor resource usage
   - Access VM via SSH

## Configuration

### FireCracker Setup

1. Install FireCracker:
```bash
wget https://github.com/firecracker-microvm/firecracker/releases/latest/download/firecracker-x86_64
chmod +x firecracker-x86_64
sudo mv firecracker-x86_64 /usr/local/bin/firecracker
```

2. Configure network:
```bash
sudo ip tuntap add tap0 mode tap
sudo ip addr add 172.16.0.1/24 dev tap0
sudo ip link set tap0 up
```

### System Images

Place your system images in the configured directory and update the database:
```bash
php artisan images:sync
```

## Security

- All VM operations require authentication
- Root passwords are encrypted
- Network isolation between VMs
- Regular security updates

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Authors

- Achaire ZOGO - *Initial work* - [YourGithub](https://github.com/Achaire-Zogo)

## Acknowledgments

- FireCracker team for the amazing VM technology
- Laravel team for the excellent framework
- Bootstrap team for the UI components
