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
        User::truncate();
        Category::truncate();
        Post::truncate();

        $user = User::factory()->create();

        $personal = Category::create([
            'name' => 'Personal',
            'slug' => 'personal',
        ]);

        $family = Category::create([
            'name' => 'Family',
            'slug' => 'family',
        ]);

        $work = Category::create([
            'name' => 'Work',
            'slug' => 'work',
        ]);

        Post::create([
            'user_id' => $user->id,
            'category_id' => $family->id,
            'title' => 'My Family Post',
            'slug' => 'my-first-post',
            'excerpt' => 'Excerpt for my post',
            'body' => '<p>Sit dolore est minim non nostrud aliquip. Id aliquip eiusmod nulla excepteur laboris dolore elit culpa ipsum sunt ea Lorem cillum do. Sint magna veniam minim laboris. Sint laboris dolor do nisi excepteur enim minim et consectetur ex anim elit ut veniam.

Non officia anim deserunt irure exercitation est enim anim officia. Ut aliqua enim nostrud nisi nisi quis velit elit do id in est fugiat. Ex voluptate proident ex cupidatat ad nulla nisi laboris elit ut. Elit sunt irure deserunt cillum sint. Eiusmod occaecat deserunt Lorem aliquip aute officia sint voluptate. Proident sint anim id occaecat anim ea fugiat.</p>',
        ]);

        Post::create([
            'user_id' => $user->id,
            'category_id' => $work->id,
            'title' => 'My Work Post',
            'slug' => 'my-second-post',
            'excerpt' => 'Excerpt for my post',
            'body' => '<p>Sit dolore est minim non nostrud aliquip. Id aliquip eiusmod nulla excepteur laboris dolore elit culpa ipsum sunt ea Lorem cillum do. Sint magna veniam minim laboris. Sint laboris dolor do nisi excepteur enim minim et consectetur ex anim elit ut veniam.

Non officia anim deserunt irure exercitation est enim anim officia. Ut aliqua enim nostrud nisi nisi quis velit elit do id in est fugiat. Ex voluptate proident ex cupidatat ad nulla nisi laboris elit ut. Elit sunt irure deserunt cillum sint. Eiusmod occaecat deserunt Lorem aliquip aute officia sint voluptate. Proident sint anim id occaecat anim ea fugiat.</p>',
        ]);
    }
}
