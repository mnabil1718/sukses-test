<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('authors')->insert([
            'name' => 'Bruce Wayne',
            'bio' => 'an author by day, vigilante by night',
            'birth_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
