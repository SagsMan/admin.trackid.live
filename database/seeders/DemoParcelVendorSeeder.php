<?php

namespace Database\Seeders;

use App\Models\CountryVendor;
use App\Models\Country;
use App\Models\PackageType;
use App\Models\PackageTypePricing;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Traits\ImageGeneratorTrait;
use Illuminate\Database\Seeder;

class DemoParcelVendorSeeder extends Seeder
{

    use ImageGeneratorTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //create package types
        $names = [
            "Small Package (<5kg)",
            "Medium Package (<20kg)",
            "Large Package (20-50kg)",
            "XLarge Package (50kg-1ton)",
        ];
        $descriptions = [
            "Package from 0 - 5kg in weight. Example: Food, Medication, Documents etc.",
            "Package of 20kg max in weight.",
            "Package from 20 - 50kg in weight.",
            "For order involing packages weighing up to 1 ton. Example: House moving, Delivery Warehouse, Bulk Delivery etc.",
        ];

        foreach ($names as $key => $name) {
            $packageType = new PackageType();
            $packageType->name = $name;
            $packageType->description = $descriptions[$key];
            $packageType->save();
            $packageType->clearMediaCollection();
            //
            $index = $key + 1;
            $packageTypeImage = public_path("images/vendor/modules/parcel/types/{$index}.png");
            $packageType->addMedia($packageTypeImage)
                ->preservingOriginal()
                ->toMediaCollection();
        }
        //
        $parcelVendorTypeId = VendorType::where('slug', 'parcel')->first()->id;
        $vendorIds = Vendor::where('vendor_type_id', $parcelVendorTypeId)->pluck('id')->toArray();
        Vendor::where('vendor_type_id', $vendorIds)->delete();
        //
        //create 4 popular food vendors: KFC, McDonalds, Burger King, Subway
        $vendorNames = ['Arrowline Couriers', 'SwiftParcel Express', 'GoPack Logistics', 'PrimeRoute Solutions'];
        //short descriptions
        $vendorDecriptions = [
            "Precision and professionalism define Arrowline Couriers, specializing in seamless last-mile delivery with real-time tracking and exceptional customer service.",
            "Delivering speed and reliability, SwiftParcel Express ensures your packages reach their destination on time, every time, with a focus on express shipping and same-day deliveries.",
            "GoPack Logistics offers flexible, affordable, and secure shipping solutions tailored for businesses and individuals, with a global network to meet your delivery needs.",
            "PrimeRoute Solutions simplifies shipping with innovative logistics technology, eco-friendly options, and customizable delivery plans to suit all your requirements."
        ];
        //
        $countries = Country::get()->pluck("id")->toArray();
        $faker = \Faker\Factory::create();
        //Loop through the vendor names
        foreach ($vendorNames as $key => $vendorName) {
            $model = new Vendor();
            $model->name = $vendorName;
            $model->description = $vendorDecriptions[$key];
            $model->delivery_fee = $faker->randomNumber(2, false);
            $model->delivery_range = $faker->randomNumber(3, false);
            $model->tax = $faker->randomNumber(2, false);
            $model->phone = $faker->phoneNumber;
            $model->email = $faker->email;
            $model->address = $faker->address;
            $model->latitude = $faker->latitude();
            $model->longitude = $faker->longitude();
            $model->tax = rand(0, 1);
            $model->pickup = rand(0, 1);
            $model->delivery = rand(0, 1);
            $model->is_active = 1;
            $model->vendor_type_id = $parcelVendorTypeId;
            $model->saveQuietly();
            //logo image
            try {
                //logo
                $model->clearMediaCollection();
                $index = $key + 1;
                $logoImage = public_path("images/vendor/modules/parcel/{$index}.jpg");
                $model->addMedia($logoImage)
                    ->preservingOriginal()
                    ->toMediaCollection("logo");

                //keep the original image
                $featureImage = public_path('images/vendor/modules/parcel/feature-img.jpg');
                $model->addMedia($featureImage)
                    ->preservingOriginal()
                    ->toMediaCollection("feature_image");
            } catch (\Exception $ex) {
                logger("Error", [$ex->getMessage()]);
            }
            //add pricing
            $packageTypes = PackageType::get();
            foreach ($packageTypes as $key => $packageType) {
                $packageTypePricing = new PackageTypePricing();
                $packageTypePricing->vendor_id = $model->id;
                $packageTypePricing->package_type_id = $packageType->id;
                $packageTypePricing->max_booking_days = rand(1, 90);
                $packageTypePricing->size_price = rand(20, 500);
                $packageTypePricing->price_per_kg = rand(0, 1);
                $packageTypePricing->distance_price = rand(20, 500);
                $packageTypePricing->base_price = rand(10, 200);
                $packageTypePricing->multiple_stop_fee = rand(1, 50);
                $packageTypePricing->price_per_km = rand(0, 1);
                $packageTypePricing->is_active = 1;
                $packageTypePricing->auto_assignment = rand(0, 1);
                $packageTypePricing->field_required = rand(0, 1);
                $packageTypePricing->save();
            }

            //add all countries of operations
            foreach ($countries as $countryId) {
                $countryVendor = new CountryVendor();
                $countryVendor->country_id = $countryId;
                $countryVendor->vendor_id = $model->id;
                $countryVendor->is_active = 1;
                $countryVendor->save();
            }
        }
    }
}