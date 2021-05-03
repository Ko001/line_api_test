<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Post $post)
    {
        # viewのメソッドであるwithを用いて、値を渡す。[]内は連想配列。
        return view('index')->with(['posts' => $post->getPaginateByLimit()]);
    }
}
