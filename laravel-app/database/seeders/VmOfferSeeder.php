<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VmOffer;

class VmOfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'name' => 'Basic',
                'description' => 'Parfait pour les petits projets et le développement',
                'cpu_count' => 1,
                'memory_size_mib' => 1024, // 1 GB
                'disk_size_gb' => 10,
                'price_per_hour' => 0.50,
                'is_active' => true
            ],
            [
                'name' => 'Standard',
                'description' => 'Idéal pour les applications web et les bases de données moyennes',
                'cpu_count' => 2,
                'memory_size_mib' => 2048, // 2 GB
                'disk_size_gb' => 20,
                'price_per_hour' => 1.00,
                'is_active' => true
            ],
            [
                'name' => 'Premium',
                'description' => 'Pour les applications exigeantes et les charges de travail intensives',
                'cpu_count' => 4,
                'memory_size_mib' => 4096, // 4 GB
                'disk_size_gb' => 40,
                'price_per_hour' => 2.00,
                'is_active' => true
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Solutions haute performance pour les entreprises',
                'cpu_count' => 8,
                'memory_size_mib' => 8192, // 8 GB
                'disk_size_gb' => 80,
                'price_per_hour' => 4.00,
                'is_active' => true
            ]
        ];

        foreach ($offers as $offer) {
            VmOffer::create($offer);
        }
    }
}
