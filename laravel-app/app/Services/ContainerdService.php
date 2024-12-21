<?php

namespace App\Services;

use App\Models\VirtualMachine;
use App\Models\SshKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class ContainerdService
{
    private string $containerdSocket;
    private string $namespace;

    public function __construct()
    {
        $this->containerdSocket = config('services.containerd.socket', '/run/containerd/containerd.sock');
        $this->namespace = config('services.containerd.namespace', 'firecracker');
    }

    public function createVM(string $name, array $config)
    {
        // Vérifier si la VM existe déjà
        if ($this->vmExists($name)) {
            throw new RuntimeException("Une VM avec le nom {$name} existe déjà");
        }

        // Créer le bundle pour la VM
        $bundlePath = $this->createBundle($name, $config);

        // Lancer la VM avec containerd
        $process = Process::run([
            'ctr',
            '--address', $this->containerdSocket,
            '--namespace', $this->namespace,
            'run',
            '--runtime', 'io.containerd.firecracker.v1',
            '--rm',
            $name,
            $name
        ]);

        if (!$process->successful()) {
            throw new RuntimeException("Échec de la création de la VM : " . $process->errorOutput());
        }

        return [
            'status' => 'created',
            'name' => $name,
            'bundle_path' => $bundlePath
        ];
    }

    public function startVM(string $name)
    {
        $process = Process::run([
            'ctr',
            '--address', $this->containerdSocket,
            '--namespace', $this->namespace,
            'tasks',
            'start',
            $name
        ]);

        if (!$process->successful()) {
            throw new RuntimeException("Échec du démarrage de la VM : " . $process->errorOutput());
        }

        return ['status' => 'running'];
    }

    public function stopVM(string $name)
    {
        $process = Process::run([
            'ctr',
            '--address', $this->containerdSocket,
            '--namespace', $this->namespace,
            'tasks',
            'kill',
            '-s', 'SIGTERM',
            $name
        ]);

        if (!$process->successful()) {
            throw new RuntimeException("Échec de l'arrêt de la VM : " . $process->errorOutput());
        }

        return ['status' => 'stopped'];
    }

    public function deleteVM(string $name)
    {
        // D'abord arrêter la VM si elle est en cours d'exécution
        try {
            $this->stopVM($name);
        } catch (RuntimeException $e) {
            // Ignorer l'erreur si la VM est déjà arrêtée
        }

        $process = Process::run([
            'ctr',
            '--address', $this->containerdSocket,
            '--namespace', $this->namespace,
            'containers',
            'rm',
            $name
        ]);

        if (!$process->successful()) {
            throw new RuntimeException("Échec de la suppression de la VM : " . $process->errorOutput());
        }

        // Nettoyer le bundle
        $this->cleanupBundle($name);

        return ['status' => 'deleted'];
    }

    public function getVMStatus(string $name)
    {
        $process = Process::run([
            'ctr',
            '--address', $this->containerdSocket,
            '--namespace', $this->namespace,
            'tasks',
            'ls'
        ]);

        if (!$process->successful()) {
            throw new RuntimeException("Échec de la récupération du statut de la VM : " . $process->errorOutput());
        }

        $output = $process->output();
        if (str_contains($output, $name)) {
            // Analyser la sortie pour obtenir le statut exact
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                if (str_contains($line, $name)) {
                    $parts = preg_split('/\s+/', trim($line));
                    return [
                        'status' => $parts[4] ?? 'unknown',
                        'pid' => $parts[3] ?? null
                    ];
                }
            }
        }

        return ['status' => 'not_found'];
    }

    private function vmExists(string $name): bool
    {
        $process = Process::run([
            'ctr',
            '--address', $this->containerdSocket,
            '--namespace', $this->namespace,
            'containers',
            'ls'
        ]);

        return str_contains($process->output(), $name);
    }

    private function createBundle(string $name, array $config): string
    {
        $bundlePath = storage_path("app/vm-bundles/{$name}");

        // Créer le répertoire du bundle
        if (!file_exists($bundlePath)) {
            mkdir($bundlePath, 0755, true);
        }

        // Créer le fichier user-data pour la clé SSH
        if (isset($config['ssh_public_key'])) {
            $userData = base64_encode(sprintf(
                "#!/bin/bash\n" .
                "mkdir -p /root/.ssh\n" .
                "echo '%s' >> /root/.ssh/authorized_keys\n" .
                "chmod 700 /root/.ssh\n" .
                "chmod 600 /root/.ssh/authorized_keys\n",
                $config['ssh_public_key']
            ));
        }

        // Créer config.json pour la VM
        $configJson = [
            'ociVersion' => '1.0.1',
            'root' => [
                'path' => $config['rootfs'] ?? '',
                'readonly' => false
            ],
            'process' => [
                'terminal' => true,
                'user' => [
                    'uid' => 0,
                    'gid' => 0
                ],
                'args' => [
                    '/bin/sh'
                ],
                'env' => [
                    'PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'
                ],
                'cwd' => '/',
                'capabilities' => [
                    'bounding' => [
                        'CAP_AUDIT_WRITE',
                        'CAP_KILL',
                        'CAP_NET_BIND_SERVICE'
                    ],
                    'effective' => [
                        'CAP_AUDIT_WRITE',
                        'CAP_KILL',
                        'CAP_NET_BIND_SERVICE'
                    ],
                    'inheritable' => [
                        'CAP_AUDIT_WRITE',
                        'CAP_KILL',
                        'CAP_NET_BIND_SERVICE'
                    ],
                    'permitted' => [
                        'CAP_AUDIT_WRITE',
                        'CAP_KILL',
                        'CAP_NET_BIND_SERVICE'
                    ],
                    'ambient' => [
                        'CAP_AUDIT_WRITE',
                        'CAP_KILL',
                        'CAP_NET_BIND_SERVICE'
                    ]
                ]
            ],
            'linux' => [
                'resources' => [
                    'memory' => [
                        'limit' => intval($config['mem_size_mib']) * 1024 * 1024
                    ],
                    'cpu' => [
                        'quota' => intval($config['vcpu_count']) * 100000,
                        'period' => 100000
                    ]
                ],
                'namespaces' => [
                    ['type' => 'pid'],
                    ['type' => 'network'],
                    ['type' => 'ipc'],
                    ['type' => 'uts'],
                    ['type' => 'mount']
                ]
            ],
            'annotations' => [
                'firecracker.cpu-template' => 'T2',
                'firecracker.kernel-args' => 'console=ttyS0 reboot=k panic=1 pci=off',
                'firecracker.kernel-path' => $config['kernel_path'] ?? '',
                'firecracker.root-drive' => $config['rootfs'] ?? '',
                'firecracker.cpu-count' => (string)$config['vcpu_count'],
                'firecracker.mem-size-mib' => (string)$config['mem_size_mib']
            ]
        ];

        // Ajouter les données utilisateur si présentes
        if (isset($userData)) {
            $configJson['annotations']['firecracker.user-data'] = $userData;
        }

        file_put_contents(
            "{$bundlePath}/config.json",
            json_encode($configJson, JSON_PRETTY_PRINT)
        );
        return $bundlePath;
    }

    private function cleanupBundle(string $name)
    {
        $bundlePath = storage_path("app/vm-bundles/{$name}");
        if (file_exists($bundlePath)) {
            $this->recursiveRemoveDirectory($bundlePath);
        }
    }

    private function recursiveRemoveDirectory($directory)
    {
        if (is_dir($directory)) {
            $objects = scandir($directory);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($directory. DIRECTORY_SEPARATOR .$object) && !is_link($directory."/".$object)) {
                        $this->recursiveRemoveDirectory($directory. DIRECTORY_SEPARATOR .$object);
                    } else {
                        unlink($directory. DIRECTORY_SEPARATOR .$object);
                    }
                }
            }
            rmdir($directory);
        }
    }
}
