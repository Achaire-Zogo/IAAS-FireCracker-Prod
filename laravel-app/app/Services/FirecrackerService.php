<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;

class FirecrackerService
{
    private $client;
    private $socketPath;

    public function __construct()
    {
        $this->socketPath = '/tmp/firecracker.sock';
        $this->client = new Client([
            'base_uri' => 'http://localhost/',
            'curl' => [
                CURLOPT_UNIX_SOCKET_PATH => $this->socketPath
            ]
        ]);
    }

    public function createVM($vmId, $config)
    {
        try {
            $response = $this->client->put("machines/{$vmId}", [
                'json' => [
                    'vcpu_count' => $config['vcpu_count'],
                    'mem_size_mib' => $config['mem_size_mib'],
                    'network_interfaces' => [
                        [
                            'iface_id' => 'eth0',
                            'guest_mac' => 'AA:FC:00:00:00:01',
                            'host_dev_name' => 'tap0'
                        ]
                    ],
                    'drives' => [
                        [
                            'drive_id' => 'rootfs',
                            'path_on_host' => $this->getOSImagePath($config['os_type']),
                            'is_root_device' => true,
                            'is_read_only' => false
                        ]
                    ]
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (Exception $e) {
            throw new Exception("Failed to create VM: " . $e->getMessage());
        }
    }

    public function startVM($vmId)
    {
        try {
            $response = $this->client->put("machines/{$vmId}/actions", [
                'json' => [
                    'action_type' => 'InstanceStart'
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (Exception $e) {
            throw new Exception("Failed to start VM: " . $e->getMessage());
        }
    }

    public function stopVM($vmId)
    {
        try {
            $response = $this->client->put("machines/{$vmId}/actions", [
                'json' => [
                    'action_type' => 'SendCtrlAltDel'
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (Exception $e) {
            throw new Exception("Failed to stop VM: " . $e->getMessage());
        }
    }

    private function getOSImagePath($osType)
    {
        $basePath = storage_path('app/vm-images');
        
        switch ($osType) {
            case 'ubuntu':
                return $basePath . '/ubuntu-22.04.img';
            case 'alpine':
                return $basePath . '/alpine.img';
            default:
                throw new Exception("Unsupported OS type: {$osType}");
        }
    }
}
