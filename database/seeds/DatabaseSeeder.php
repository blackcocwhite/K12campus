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
//        $this->call('OrderTableSeeder');
        $this->call('TodoTableSeeder');
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

class TodoTableSeeder extends Seeder
{
    public function run()
    {
        App\Model\Temporary\Todo::truncate();
        factory(App\Model\Temporary\Todo::class, 5)->create();
    }
}