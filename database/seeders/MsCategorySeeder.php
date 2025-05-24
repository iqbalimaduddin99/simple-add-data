<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('categories')->whereIn('id', [1, 2])->delete();

        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Income',
            ],
            [
                'id' => 2,
                'name' => 'Expense',
            ],
        ]);
    }
}
