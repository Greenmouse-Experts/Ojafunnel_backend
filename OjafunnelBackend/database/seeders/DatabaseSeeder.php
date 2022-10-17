<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            AdminAccountSeeder::class,
            AccountTypeSeeder::class,
            AdminCryptoAccountSeeder::class,
            AirtimeToCashNetworkSeeder::class,
            BillerCategorySeeder::class,
            AccountTypeSeeder::class,
            BankSeeder::class,
            AirtimeBillersSeeder::class,
            CableTvBillersSeeder::class,
            DataBillersSeeder::class,
            // ElectricityBillersSeeder::class
        ]);
    }
}
