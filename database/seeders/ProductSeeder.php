<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@ecom.test')->first();
        $ownerOne = User::where('email', 'owner@ecom.test')->first();
        $ownerTwo = User::where('email', 'owner2@ecom.test')->first();

        $shopOne = Shop::where('slug', 'rahim-super-shop')->first();
        $shopTwo = Shop::where('slug', 'karim-traders')->first();

        $catalog = [
            ['name' => 'A4 Notebook Set', 'sku' => 'NB-1001', 'price' => 350, 'by' => $admin, 'color' => '2d6a9f', 'description' => 'Pack of three ruled A4 notebooks with 120 pages each.'],
            ['name' => 'Wireless Mouse', 'sku' => 'WM-2001', 'price' => 1200, 'by' => $admin, 'color' => '1f6f5c', 'description' => '2.4GHz silent wireless mouse with adjustable DPI and USB receiver.'],
            ['name' => 'Cotton T-Shirt', 'sku' => 'TS-3001', 'price' => 650, 'by' => $admin, 'color' => 'b45309', 'description' => 'Premium 100% combed cotton crew neck t-shirt, available in all sizes.'],
            ['name' => 'Stainless Steel Water Bottle', 'sku' => 'WB-4001', 'price' => 850, 'by' => $admin, 'color' => '0e7490', 'description' => '1 litre double-wall insulated bottle that keeps drinks cold for 24 hours.'],
            ['name' => 'Bluetooth Earbuds', 'sku' => 'BE-5001', 'price' => 2500, 'by' => $admin, 'color' => '6d28d9', 'description' => 'True wireless earbuds with charging case, touch controls and 18h playtime.'],
            ['name' => 'LED Desk Lamp', 'sku' => 'DL-6001', 'price' => 1800, 'by' => $admin, 'color' => 'a16207', 'description' => 'Dimmable LED desk lamp with three colour temperatures and USB charging port.'],
            ['name' => 'Face Wash 100ml', 'sku' => 'FW-7001', 'price' => 420, 'by' => $admin, 'color' => 'be185d', 'description' => 'Gentle daily face wash with aloe vera and vitamin E for all skin types.'],
            ['name' => 'USB-C Charging Cable', 'sku' => 'UC-8001', 'price' => 500, 'by' => $admin, 'color' => '334155', 'description' => '1.5 metre braided USB-C fast charging and data transfer cable.'],
            ['name' => 'Running Shoes', 'sku' => 'RS-9001', 'price' => 3200, 'by' => $admin, 'color' => '166534', 'description' => 'Lightweight running shoes with breathable mesh and cushioned sole.'],
            ['name' => 'Backpack 25L', 'sku' => 'BP-1002', 'price' => 2100, 'by' => $admin, 'color' => '7c2d12', 'description' => 'Water resistant 25 litre backpack with laptop compartment and USB port.'],
            ['name' => 'Handmade Cotton Panjabi', 'sku' => 'PN-1101', 'price' => 2400, 'by' => $ownerOne, 'color' => '1e3a8a', 'description' => 'Handmade cotton panjabi with embroidery on the collar and placket.'],
            ['name' => 'Smart Watch Strap', 'sku' => 'SW-1201', 'price' => 550, 'by' => $ownerTwo, 'color' => '0f766e', 'description' => 'Soft silicone replacement strap compatible with 20mm smart watches.'],
        ];

        $products = [];

        foreach ($catalog as $item) {
            $fileName = strtolower($item['sku']).'.svg';
            $this->writePlaceholder($fileName, $item['name'], $item['color']);

            $products[$item['sku']] = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'created_by' => $item['by']?->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'thumbnail' => $fileName,
                    'base_price' => $item['price'],
                    'is_active' => true,
                ]
            );
        }

        $assignments = [
            // Rahim Super Shop
            ['shop' => $shopOne, 'sku' => 'NB-1001', 'price' => 330, 'details' => 'School and office notebook bundle. Free delivery inside Dhanmondi.', 'active' => true],
            ['shop' => $shopOne, 'sku' => 'TS-3001', 'price' => 620, 'details' => '100% cotton t-shirt. Sizes M, L and XL available in store.', 'active' => true],
            ['shop' => $shopOne, 'sku' => 'WB-4001', 'price' => 799, 'details' => 'Insulated steel bottle, ideal for daily commute.', 'active' => true],
            ['shop' => $shopOne, 'sku' => 'FW-7001', 'price' => 399, 'details' => 'Gentle face wash for daily use.', 'active' => false],
            ['shop' => $shopOne, 'sku' => 'UC-8001', 'price' => 480, 'details' => 'Braided fast-charging cable with 12 month warranty.', 'active' => true],
            ['shop' => $shopOne, 'sku' => 'PN-1101', 'price' => 2350, 'details' => 'Exclusive handmade panjabi, limited stock.', 'active' => true],

            // Karim Traders
            ['shop' => $shopTwo, 'sku' => 'WM-2001', 'price' => 1150, 'details' => 'Silent wireless mouse with 1 year service warranty.', 'active' => true],
            ['shop' => $shopTwo, 'sku' => 'BE-5001', 'price' => 2390, 'details' => 'Bluetooth 5.3 earbuds with touch controls.', 'active' => true],
            ['shop' => $shopTwo, 'sku' => 'DL-6001', 'price' => 1750, 'details' => 'Eye-care LED desk lamp with USB charging.', 'active' => true],
            ['shop' => $shopTwo, 'sku' => 'SW-1201', 'price' => 520, 'details' => 'Comfortable silicone strap, multiple colours available.', 'active' => true],
        ];

        foreach ($assignments as $row) {
            if (! $row['shop'] || ! isset($products[$row['sku']])) {
                continue;
            }

            ShopProduct::updateOrCreate(
                [
                    'shop_id' => $row['shop']->id,
                    'product_id' => $products[$row['sku']]->id,
                ],
                [
                    'price' => $row['price'],
                    'details' => $row['details'],
                    'is_active' => $row['active'],
                ]
            );
        }
    }

    private function writePlaceholder(string $fileName, string $name, string $color): void
    {
        $directory = public_path('uploads/products');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $path = $directory.'/'.$fileName;

        if (is_file($path)) {
            return;
        }

        $initials = collect(explode(' ', $name))
            ->filter()
            ->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480" role="img" aria-label="{$safeName}">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#{$color}"/>
      <stop offset="100%" stop-color="#0f172a"/>
    </linearGradient>
  </defs>
  <rect width="640" height="480" fill="url(#g)"/>
  <circle cx="540" cy="90" r="150" fill="#ffffff" opacity="0.07"/>
  <circle cx="90" cy="430" r="120" fill="#ffffff" opacity="0.05"/>
  <text x="50%" y="46%" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="150" font-weight="700" fill="#ffffff" opacity="0.92">{$initials}</text>
  <text x="50%" y="64%" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="28" fill="#ffffff" opacity="0.8">{$safeName}</text>
</svg>
SVG;

        file_put_contents($path, $svg);
    }
}
