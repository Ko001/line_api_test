<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('posts')->truncate();
         DB::table('posts')->insert([[
            'title' => 'title1',
            'body' => 'This is body1',],
            ['title' => 'title2',
            'body' => 'This is body2',]
            ]
        );
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }
}
