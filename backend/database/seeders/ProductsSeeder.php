<?php

namespace Database\Seeders;

use App\Modules\ProductCatalog\Domain\Models\Product;
use App\Modules\UserManagement\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds 10 sample products with placeholder images from picsum.photos.
 * Idempotent via firstOrCreate on the unique slug column.
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $products = [
            ['name' => 'Wireless Bluetooth Headphones', 'price' => 79.99,  'stock' => 50,  'description' => 'Premium sound quality with active noise cancellation. 30-hour battery life.'],
            ['name' => 'Mechanical Gaming Keyboard',     'price' => 129.99, 'stock' => 30,  'description' => 'RGB backlit mechanical keyboard with Cherry MX switches.'],
            ['name' => 'USB-C Hub 7-in-1',               'price' => 49.99,  'stock' => 100, 'description' => '7 ports: 4K HDMI, 3x USB-A, SD card, microSD, PD charging.'],
            ['name' => 'Ergonomic Office Chair',          'price' => 299.00, 'stock' => 15,  'description' => 'Lumbar support, adjustable armrests, breathable mesh back.'],
            ['name' => 'Standing Desk Converter',         'price' => 199.99, 'stock' => 20,  'description' => 'Height-adjustable platform for any desk. Fits dual monitors.'],
            ['name' => '4K Webcam 60fps',                 'price' => 89.99,  'stock' => 45,  'description' => 'Ultra HD video conferencing camera with built-in ring light.'],
            ['name' => 'Portable SSD 1TB',                'price' => 109.99, 'stock' => 60,  'description' => 'NVMe speeds up to 1050 MB/s. Rugged and pocket-sized.'],
            ['name' => 'LED Desk Lamp with Wireless Charging', 'price' => 59.99, 'stock' => 40, 'description' => 'Touch dimming, USB-C port, 10W Qi wireless charging pad.'],
            ['name' => 'Smart Wi-Fi Power Strip',         'price' => 39.99,  'stock' => 75,  'description' => '4 outlets + 4 USB ports, voice control compatible.'],
            ['name' => 'Noise-Isolating Earbuds',         'price' => 34.99,  'stock' => 90,  'description' => 'Silicone tips for passive isolation. 8-hour battery + charging case.'],
        ];

        foreach ($products as $index => $data) {
            Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'price'       => $data['price'],
                    'stock'       => $data['stock'],
                    // Using picsum.photos for deterministic placeholder images
                    'image'       => "https://picsum.photos/seed/{$index}/640/480",
                    'is_active'   => true,
                    'created_by'  => $admin->id,
                ]
            );
        }
    }
}
