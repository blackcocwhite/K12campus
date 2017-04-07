<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

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
        $this->call('OrderTableSeeder');
    }
}
class OrderTableSeeder extends Seeder
{
    public function run()
    {
        App\Model\Eequipment\Order::truncate();
        factory(App\Model\Eequipment\Order::class, 20)->create();
    }
}