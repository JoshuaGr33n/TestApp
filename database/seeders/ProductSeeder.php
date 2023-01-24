<?php

namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_tests')->insert([
            'product_name' => Str::random(6),
            'quantity' => 1,
            'price' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
