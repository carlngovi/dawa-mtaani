<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'ulid'                  => strtolower(Str::ulid()),
            'sku_code'              => 'SKU-' . fake()->unique()->numerify('######'),
            'generic_name'          => 'Paracetamol ' . fake()->numerify('###') . 'mg',
            'brand_name'            => fake()->word() . ' Tablets',
            'therapeutic_category'  => 'ANALGESIC',
            'unit_size'             => '100 tablets',
            'is_active'             => true,
            'created_by'            => 1,
        ];
    }
}
