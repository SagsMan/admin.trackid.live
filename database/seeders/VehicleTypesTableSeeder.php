<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VehicleTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('vehicle_types')->delete();

        \DB::table('vehicle_types')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'EcoRide',
                'slug' => 'ecoride',
                'base_fare' => 10.0,
                'distance_fare' => 3.5,
                'time_fare' => 0.7,
                'min_fare' => 25.0,
                'in_order' => 1,
                'is_active' => 1,
                'created_at' => '2025-04-17 13:55:57',
                'updated_at' => '2025-05-02 20:16:30',
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'ComfortPlus',
                'slug' => 'comfortplus',
                'base_fare' => 17.0,
                'distance_fare' => 5.0,
                'time_fare' => 1.0,
                'min_fare' => 42.0,
                'in_order' => 1,
                'is_active' => 1,
                'created_at' => '2025-05-02 20:13:45',
                'updated_at' => '2025-05-02 20:15:42',
            ),
            2 =>
            array(
                'id' => 3,
                'name' => 'LuxElite',
                'slug' => 'luxelite',
                'base_fare' => 35.0,
                'distance_fare' => 8.0,
                'time_fare' => 1.7,
                'min_fare' => 90.0,
                'in_order' => 1,
                'is_active' => 1,
                'created_at' => '2025-05-02 20:15:24',
                'updated_at' => '2025-05-02 20:15:24',
            ),
        ));
    }
}