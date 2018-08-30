<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
        $this->call([
            CountriesTableSeeder::class,
            CurrenciesTableSeeder::class,
            LanguagesTableSeeder::class,
            AdminTableSeeder::class,
            UsersTableSeeder::class,
        ]);

        Model::reguard();
    }
}
