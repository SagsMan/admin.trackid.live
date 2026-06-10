<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //assign icons
        //images stored in public/images/vehicle/types
        $icons = [
            'ecoride' => 'ecoride.png',
            'comfortplus' => 'comfortplus.png',
            'luxelite' => 'luxelite.png',
        ];

        foreach ($icons as $slug => $icon) {
            $vehicleType = \App\Models\VehicleType::where('slug', $slug)->first();
            if (!empty($vehicleType)) {
                $vehicleType->clearMediaCollection();
                $photo = public_path('images/vehicle/types/' . $icon);
                //keep the original image
                $vehicleType->addMedia($photo)
                    ->preservingOriginal()
                    ->toMediaCollection();
            }
        }
    }
}