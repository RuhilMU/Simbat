<?php

namespace Database\Seeders;

use App\Models\Master\Category;
use App\Models\Master\Drug;
use App\Models\Master\Manufacture;
use App\Models\Master\Variant;
use App\Models\Master\Vendor;
use App\Models\Profile;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    //definisi data yang perlu dimasukkan kedalam database
    private $categories = [
        [
            "name" => "Paracetamol",
            "code" => "PC"
        ],
        [
            "name" => "Analgesik",
            "code" => "AG"
        ],
        [
            "name" => "Anthelmetika",
            "code" => "AH"
        ],
        [
            "name" => "Anti Parasit",
            "code" => "AP"
        ],
        [
            "name" => "Roboransia",
            "code" => "RB"
        ],
        [
            "name" => "Vitamin",
            "code" => "VT"
        ],
    ];
    private $variants = [["name" => "Cair"], ["name" => "Padat"], ["name" => "Butir"], ["name" => "Bubuk"], ["name" => "Kapsul"], ["name" => "Pil"]];
    private $manufactures = [["name" => "Kimia Farma"], ["name" => "Darya"], ["name" => "Kalbe"], ["name" => "Sidomuncul"], ["name" => "Aipeak"], ["name" => "Sanbe"]];
    private $vendors = [
        [
            "name" => "PT Farmasi Sejahtera",
            "phone" => "081234567890",
            "address" => "Jl. Kesehatan No.10, Jakarta"
        ],
        [
            "name" => "CV Apotek Sumber Waras",
            "phone" => "081223344556",
            "address" => "Jl. Merdeka No.20, Bandung"
        ],
        [
            "name" => "PT Obat Maju Jaya",
            "phone" => "081298765432",
            "address" => "Jl. Sudirman No.15, Surabaya"
        ],
        [
            "name" => "PT Kesehatan Mandiri",
            "phone" => "081311223344",
            "address" => "Jl. Diponegoro No.5, Medan"
        ],
        [
            "name" => "CV Sumber Sehat",
            "phone" => "081377889900",
            "address" => "Jl. Pahlawan No.8, Yogyakarta"
        ],
        [
            "name" => "PT Pharma Indo",
            "phone" => "081344556677",
            "address" => "Jl. Ahmad Yani No.12, Makassar"
        ],
    ];
    private $drugs = [
        [
            "name" => "Paracetamol 500mg",
            "code" => "PC0001",
            "category_id" => 1,
            "variant_id" => 1,
            "last_price" => 5000,
            "last_discount" => 0,
            "maximum_capacity" => 100,
            "minimum_capacity" => 10,
            "pack_quantity" => 10,
            "pack_margin" => 5,
            "piece_quantity" => 5,
            "piece_margin" => 2,
            "piece_netto" => 500,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Paracetamol Syrup",
            "code" => "PC0002",
            "category_id" => 1,
            "variant_id" => 2,
            "last_price" => 7000,
            "last_discount" => 10,
            "maximum_capacity" => 50,
            "minimum_capacity" => 5,
            "pack_quantity" => 5,
            "pack_margin" => 3,
            "piece_quantity" => 3,
            "piece_margin" => 1,
            "piece_netto" => 60,
            "piece_unit" => "ml",
        ],
        [
            "name" => "Ibuprofen",
            "code" => "AG0001",
            "category_id" => 2,
            "variant_id" => 3,
            "last_price" => 8000,
            "last_discount" => 5,
            "maximum_capacity" => 150,
            "minimum_capacity" => 10,
            "pack_quantity" => 10,
            "pack_margin" => 4,
            "piece_quantity" => 7,
            "piece_margin" => 3,
            "piece_netto" => 400,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Aspirin",
            "code" => "AG0002",
            "category_id" => 2,
            "variant_id" => 4,
            "last_price" => 6000,
            "last_discount" => 2,
            "maximum_capacity" => 80,
            "minimum_capacity" => 10,
            "pack_quantity" => 8,
            "pack_margin" => 3,
            "piece_quantity" => 5,
            "piece_margin" => 2,
            "piece_netto" => 300,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Albendazole",
            "code" => "AH0001",
            "category_id" => 3,
            "variant_id" => 5,
            "last_price" => 12000,
            "last_discount" => 3,
            "maximum_capacity" => 70,
            "minimum_capacity" => 5,
            "pack_quantity" => 7,
            "pack_margin" => 5,
            "piece_quantity" => 10,
            "piece_margin" => 2,
            "piece_netto" => 200,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Mebendazole",
            "code" => "AH0002",
            "category_id" => 3,
            "variant_id" => 6,
            "last_price" => 15000,
            "last_discount" => 4,
            "maximum_capacity" => 60,
            "minimum_capacity" => 6,
            "pack_quantity" => 6,
            "pack_margin" => 4,
            "piece_quantity" => 8,
            "piece_margin" => 3,
            "piece_netto" => 500,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Metronidazole",
            "code" => "AP0001",
            "category_id" => 4,
            "variant_id" => 1,
            "last_price" => 9000,
            "last_discount" => 0,
            "maximum_capacity" => 90,
            "minimum_capacity" => 10,
            "pack_quantity" => 9,
            "pack_margin" => 3,
            "piece_quantity" => 20,
            "piece_margin" => 2,
            "piece_netto" => 250,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Ivermectin",
            "code" => "AP0002",
            "category_id" => 4,
            "variant_id" => 2,
            "last_price" => 11000,
            "last_discount" => 0,
            "maximum_capacity" => 80,
            "minimum_capacity" => 8,
            "pack_quantity" => 8,
            "pack_margin" => 3,
            "piece_quantity" => 4,
            "piece_margin" => 2,
            "piece_netto" => 3,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Vitamin C",
            "code" => "VT0001",
            "category_id" => 6,
            "variant_id" => 3,
            "last_price" => 3000,
            "last_discount" => 0,
            "maximum_capacity" => 100,
            "minimum_capacity" => 10,
            "pack_quantity" => 10,
            "pack_margin" => 2,
            "piece_quantity" => 4,
            "piece_margin" => 1,
            "piece_netto" => 500,
            "piece_unit" => "mg",
        ],
        [
            "name" => "Vitamin D",
            "code" => "VT0002",
            "category_id" => 6,
            "variant_id" => 4,
            "last_price" => 4000,
            "last_discount" => 0,
            "maximum_capacity" => 100,
            "minimum_capacity" => 10,
            "pack_quantity" => 8,
            "pack_margin" => 2,
            "piece_quantity" => 15,
            "piece_margin" => 1,
            "piece_netto" => 1000,
            "piece_unit" => "mg",
        ],
    ];
    private $users = [
        [
            "name" => "Super Admin",
            "role" => "super",
            "avatar" => "avatar/Avatar.jpg",
            "email" => null,
            "password" => null
        ],
        [
            "name" => "Simklinik",
            "role" => "clinic",
            "avatar" => "avatar/Avatar.jpg",
            "email" => "simklinik@simbat.disyfa.site",
            "password" => "simklinik"
        ],
        [
            "name" => "Dokter",
            "role" => "doctor",
            "avatar" => "avatar/Avatar.jpg",
            "email" => "dokter@simbat.disyfa.site",
            "password" => "dokter"
        ],
        [
            "name" => "Apoteker",
            "role" => "admin",
            "avatar" => "avatar/Avatar.jpg",
            "email" => "apoteker@simbat.disyfa.site",
            "password" => "apoteker"
        ]
    ];
    public function run(): void
    {
        foreach ($this->users as $item) {
            if ($item["role"] === "super") {
                $item["password"] = Hash::make(env("SUPER_PASSWORD"));
                $item["email"] = env("SUPER_EMAIL");
            } else {
                $item["password"] = Hash::make($item["password"]);
            }
            User::create($item);
        }
        foreach ($this->categories as $item) {
            Category::create($item);
        }
        foreach ($this->variants as $item) {
            Variant::create($item);
        }
        foreach ($this->manufactures as $item) {
            Manufacture::create($item);
        }
        foreach ($this->vendors as $item) {
            Vendor::create($item);
        }
        foreach ($this->drugs as $item) {
            $item["manufacture_id"] = $item["variant_id"];
            $drug = Drug::create($item);
            $drug->default_repacks();
            $drug->default_stock();
        }
        Profile::create([
            "name" => "Klinik Dokter Hendrik",
            "logo" => "profile/logo simbat.png",
            "address" => "Surabaya",
            "phone" => "08987654321"
        ]);
    }
}
