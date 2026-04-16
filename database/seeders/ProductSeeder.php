<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name'        => 'Wireless Headphones',
                'description' => 'High quality wireless headphones with noise cancellation.',
                'price'       => 199.99,
                'stock'       => 50,
                'image'       => 'https://picsum.photos/seed/headphones/400/300',
            ],
            [
                'name'        => 'Mechanical Keyboard',
                'description' => 'RGB mechanical keyboard with cherry MX switches.',
                'price'       => 149.99,
                'stock'       => 30,
                'image'       => 'https://picsum.photos/seed/keyboard/400/300',
            ],
            [
                'name'        => 'Gaming Mouse',
                'description' => 'Precision gaming mouse with 12000 DPI sensor.',
                'price'       => 79.99,
                'stock'       => 100,
                'image'       => 'https://picsum.photos/seed/mouse/400/300',
            ],
            [
                'name'        => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI, USB 3.0 and SD card reader.',
                'price'       => 49.99,
                'stock'       => 75,
                'image'       => 'https://picsum.photos/seed/usbhub/400/300',
            ],
            [
                'name'        => 'Webcam HD',
                'description' => '1080p HD webcam with built-in microphone.',
                'price'       => 89.99,
                'stock'       => 40,
                'image'       => 'https://picsum.photos/seed/webcam/400/300',
            ],
            [
                'name'        => 'Monitor 24"',
                'description' => '24 inch IPS monitor with 144Hz refresh rate.',
                'price'       => 399.99,
                'stock'       => 20,
                'image'       => 'https://picsum.photos/seed/monitor/400/300',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}