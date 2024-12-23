<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemImage;

class SystemImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            [
                'name' => 'Ubuntu 22.04 LTS',
                'os_type' => 'ubuntu-22.04',
                'version' => '22.04',
                'description' => 'Ubuntu 22.04 LTS (Jammy Jellyfish) est une version LTS (Long Term Support) d\'Ubuntu, offrant 5 ans de support et de mises à jour de sécurité.',
                'image_path' => '/images/system/ubuntu-22.04.png'
            ],
            [
                'name' => 'Ubuntu 24.04 LTS',
                'os_type' => 'ubuntu-24.04',
                'version' => '24.04',
                'description' => 'Ubuntu 24.04 LTS est la dernière version LTS d\'Ubuntu, offrant les dernières fonctionnalités et améliorations.',
                'image_path' => '/images/system/ubuntu-24.04.png'
            ]
        ];

        foreach ($images as $image) {
            SystemImage::create($image);
        }
    }
}
