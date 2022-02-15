<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Category;
use \App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //Crea 5 categorias y 5 usuarios con 1 post cada uno con categorias distintas
        Post::factory(5)->create();

        /*         //crea 1 usuario con el nombre John Doe
        $user = User::factory()->create([
            'name' => 'John Doe'
        ]);

        //Crea 5 post que pertenecen al usuario John Doe
        Post::factory(5)->create([
            'user_id' => $user->id
        ]); */
    }
}
