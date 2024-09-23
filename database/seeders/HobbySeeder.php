<?php

namespace Database\Seeders;

use App\Models\Hobby;
use Illuminate\Database\Seeder;

class HobbySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hobbies = ['Programming', 'Reading', 'Games'];

        foreach ($hobbies as $hobby) {
            Hobby::create(['title' => $hobby]);
        }
    }
}
