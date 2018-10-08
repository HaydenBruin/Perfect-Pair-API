<?php

use Illuminate\Database\Seeder;
use App\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::truncate();

        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $title = rtrim($faker->text(18), '.');
            Product::create([
                'title' => $title,
                'slug' => $this->create_slug($title),
                'image' => $faker->imageUrl(),
                'description' => $faker->text(),
                'price' => 10.99,
                'saleprice' => 6.99,
                'inventory' => rand(1,2),
                'enabled' => 1
            ]);
        }
    }
    private function create_slug($string) {
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
        return $slug;
     }
}
