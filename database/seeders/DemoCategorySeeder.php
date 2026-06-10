<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\VendorType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create categories by vendor type and assign to random products and services
        $foodCategories = [
            [
                "name" => "Fast Food",
                "color" => "#FF5733"
            ],
            [
                "name" => "Organic Food",
                "color" => "#33FF57"
            ],
            [
                "name" => "Snacks",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Beverages",
                "color" => "#33C4FF"
            ],
            [
                "name" => "Dairy Products",
                "color" => "#FFC433"
            ],
            [
                "name" => "Fruits",
                "color" => "#FF5733"
            ],
            [
                "name" => "Vegetables",
                "color" => "#33FFCA"
            ],
            [
                "name" => "Bakery Items",
                "color" => "#FFA833"
            ],
            [
                "name" => "Meat & Poultry",
                "color" => "#33FFB4"
            ],
            [
                "name" => "Seafood",
                "color" => "#FF3333"
            ],
            [
                "name" => "Frozen Food",
                "color" => "#33A4FF"
            ],
            [
                "name" => "Desserts",
                "color" => "#FF33FF"
            ],
            [
                "name" => "Canned Food",
                "color" => "#5733FF"
            ],
            [
                "name" => "Spices & Seasonings",
                "color" => "#FF3387"
            ],
            [
                "name" => "Confectionery",
                "color" => "#33FF8A"
            ],
            [
                "name" => "Soups",
                "color" => "#FF8A33"
            ],
            [
                "name" => "Grains & Rice",
                "color" => "#33B5FF"
            ],
            [
                "name" => "Pasta & Noodles",
                "color" => "#FFB533"
            ],
            [
                "name" => "Sauces & Condiments",
                "color" => "#3387FF"
            ],
            [
                "name" => "Prepared Meals",
                "color" => "#FF333B"
            ]
        ];

        $groceryCategories = [
            [
                "name" => "Fresh Vegetables",
                "color" => "#A1FF33"
            ],
            [
                "name" => "Fresh Fruits",
                "color" => "#33FF57"
            ],
            [
                "name" => "Bread & Bakery",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Canned Goods",
                "color" => "#FF5733"
            ],
            [
                "name" => "Dry Goods",
                "color" => "#33C4FF"
            ],
            [
                "name" => "Dairy",
                "color" => "#FFC433"
            ],
            [
                "name" => "Meat",
                "color" => "#33FFB4"
            ],
            [
                "name" => "Seafood",
                "color" => "#FF3333"
            ],
            [
                "name" => "Frozen Foods",
                "color" => "#FF8A33"
            ],
            [
                "name" => "Snacks",
                "color" => "#5733FF"
            ],
            [
                "name" => "Beverages",
                "color" => "#FF3387"
            ],
            [
                "name" => "Spices",
                "color" => "#33FF8A"
            ],
            [
                "name" => "Oils & Vinegars",
                "color" => "#FF33FF"
            ],
            [
                "name" => "Condiments",
                "color" => "#33A4FF"
            ],
            [
                "name" => "Pasta & Grains",
                "color" => "#33FF57"
            ],
            [
                "name" => "Baby Care",
                "color" => "#33FFA4"
            ],
            [
                "name" => "Household Supplies",
                "color" => "#FFB533"
            ],
            [
                "name" => "Pet Supplies",
                "color" => "#3387FF"
            ],
            [
                "name" => "Personal Care",
                "color" => "#FF333B"
            ],
            [
                "name" => "Breakfast Foods",
                "color" => "#33B5FF"
            ]
        ];

        $commerceCategories = [
            [
                "name" => "Electronics",
                "color" => "#FF5733"
            ],
            [
                "name" => "Mobile Phones",
                "color" => "#33FF57"
            ],
            [
                "name" => "Home Appliances",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Clothing",
                "color" => "#FF5733"
            ],
            [
                "name" => "Shoes",
                "color" => "#33C4FF"
            ],
            [
                "name" => "Bags & Accessories",
                "color" => "#FFC433"
            ],
            [
                "name" => "Books",
                "color" => "#33FFB4"
            ],
            [
                "name" => "Beauty & Personal Care",
                "color" => "#FF3333"
            ],
            [
                "name" => "Toys",
                "color" => "#FF8A33"
            ],
            [
                "name" => "Home & Furniture",
                "color" => "#33FFA4"
            ],
            [
                "name" => "Sports & Fitness",
                "color" => "#FF3387"
            ],
            [
                "name" => "Watches",
                "color" => "#5733FF"
            ],
            [
                "name" => "Jewelry",
                "color" => "#FF33FF"
            ],
            [
                "name" => "Health & Wellness",
                "color" => "#33A4FF"
            ],
            [
                "name" => "Pet Supplies",
                "color" => "#33FF8A"
            ],
            [
                "name" => "Baby Products",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Office Supplies",
                "color" => "#FFB533"
            ],
            [
                "name" => "Automotive",
                "color" => "#3387FF"
            ],
            [
                "name" => "Gardening",
                "color" => "#FF333B"
            ],
            [
                "name" => "Music Instruments",
                "color" => "#33B5FF"
            ]
        ];

        $pharmacyCategories = [
            [
                "name" => "Prescription Medicine",
                "color" => "#FF5733"
            ],
            [
                "name" => "Vitamins & Supplements",
                "color" => "#33FF57"
            ],
            [
                "name" => "Personal Care",
                "color" => "#FF33B5"
            ],
            [
                "name" => "First Aid",
                "color" => "#FF5733"
            ],
            [
                "name" => "Cold & Flu",
                "color" => "#33C4FF"
            ],
            [
                "name" => "Pain Relief",
                "color" => "#FFC433"
            ],
            [
                "name" => "Allergies",
                "color" => "#33FFB4"
            ],
            [
                "name" => "Skin Care",
                "color" => "#FF3333"
            ],
            [
                "name" => "Dental Care",
                "color" => "#FF8A33"
            ],
            [
                "name" => "Eye Care",
                "color" => "#5733FF"
            ],
            [
                "name" => "Baby Care",
                "color" => "#FF3387"
            ],
            [
                "name" => "Women’s Health",
                "color" => "#FF33FF"
            ],
            [
                "name" => "Men’s Health",
                "color" => "#33A4FF"
            ],
            [
                "name" => "Home Diagnostics",
                "color" => "#33FF8A"
            ],
            [
                "name" => "Antiseptics",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Medical Devices",
                "color" => "#FFB533"
            ],
            [
                "name" => "Supplements",
                "color" => "#3387FF"
            ],
            [
                "name" => "Weight Management",
                "color" => "#FF333B"
            ],
            [
                "name" => "Incontinence",
                "color" => "#33B5FF"
            ],
            [
                "name" => "Elder Care",
                "color" => "#FFA833"
            ]
        ];

        $serviceCategories = [
            [
                "name" => "Cleaning Services",
                "color" => "#FF5733"
            ],
            [
                "name" => "Plumbing",
                "color" => "#33FF57"
            ],
            [
                "name" => "Electrical Services",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Painting",
                "color" => "#FF5733"
            ],
            [
                "name" => "Pest Control",
                "color" => "#33C4FF"
            ],
            [
                "name" => "Lawn Care",
                "color" => "#FFC433"
            ],
            [
                "name" => "Home Repairs",
                "color" => "#33FFB4"
            ],
            [
                "name" => "Carpentry",
                "color" => "#FF3333"
            ],
            [
                "name" => "Appliance Repairs",
                "color" => "#FF8A33"
            ],
            [
                "name" => "Automotive Services",
                "color" => "#5733FF"
            ],
            [
                "name" => "Tutoring",
                "color" => "#FF3387"
            ],
            [
                "name" => "Health & Wellness",
                "color" => "#FF33FF"
            ],
            [
                "name" => "Fitness Trainers",
                "color" => "#33A4FF"
            ],
            [
                "name" => "Event Planning",
                "color" => "#33FF8A"
            ],
            [
                "name" => "Photography",
                "color" => "#FF33B5"
            ],
            [
                "name" => "Delivery Services",
                "color" => "#FFB533"
            ],
            [
                "name" => "Courier Services",
                "color" => "#3387FF"
            ],
            [
                "name" => "IT Support",
                "color" => "#FF333B"
            ],
            [
                "name" => "Pet Care",
                "color" => "#33B5FF"
            ],
            [
                "name" => "Moving Services",
                "color" => "#FFA833"
            ]
        ];


        //
        $groupCategories = [
            "food" => $foodCategories,
            "grocery" => $groceryCategories,
            "pharmacy" => $pharmacyCategories,
            "service" => $serviceCategories,
            "commerce" => $commerceCategories,
        ];


        foreach ($groupCategories as $slug => $categories) {

            foreach ($categories as $categoryObject) {
                //
                $vendorType = VendorType::where('slug', $slug)->inRandomOrder()->first();
                $category = new Category();
                $category->name = $categoryObject['name'];
                $category->color = $categoryObject['color'];
                $category->vendor_type_id = $vendorType->id;
                $category->save();
            }
        }
    }
}