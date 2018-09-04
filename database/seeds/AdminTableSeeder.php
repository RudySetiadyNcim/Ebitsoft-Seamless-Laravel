<?php

use App\User;
use Illuminate\Database\Seeder;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $faker = Faker\Factory::create();
        $users = array(
            [
                'username' => $faker->userName,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'balance' => 0,
                'country' => 'ID',
                'currency' => 'IDR',
                'language' => 'id',
                'is_global_admin' => true,
                'current_role' => 'Owner',
                'password' => Hash::make('colorphotograph'),
                'email_address' => $faker->email,
                'mobile_number' => $faker->phoneNumber
            ],
            [
                'username' => $faker->userName,
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'balance' => 0,
                'country' => 'ID',
                'currency' => 'IDR',
                'language' => 'id',
                'is_global_admin' => true,
                'current_role' => 'Administrator',
                'password' => Hash::make('colorphotograph'),
                'email_address' => $faker->email,
                'mobile_number' => $faker->phoneNumber
            ],
        );

        // Loop through each user above and create the record for them in the database
        foreach ($users as $data)
        {
            $user = new User();
            DB::beginTransaction();
            $user->saveUser($data);
            DB::commit();
        }

    }
}
