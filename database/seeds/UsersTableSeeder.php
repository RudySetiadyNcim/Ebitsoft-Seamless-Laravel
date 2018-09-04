<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
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
                'is_global_admin' => false,
                'current_role' => 'User',
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
