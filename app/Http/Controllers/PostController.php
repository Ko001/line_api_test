<?php

namespace App\Http\Controllers;

// クラス使用のための宣言
use App\Post;
use Illuminate\Http\Request; //ユーザーからの入力を使用するときに使うクラス

class PostController extends Controller
{
    public function index(Post $post)
    {
        # viewのメソッドであるwithを用いて、値を渡す。[]内は連想配列。
        return view('index')->with(['posts' => $post->getPaginateByLimit()]);
    }
    public function show(Post $post)
    {
        return view('show')->with(['post' => $post]);
    }
    public function create()
    {
        return view('create');
    }
    public function edit(Post $post)
    {
        return view('edit')->with(['post' => $post]);
    }
    public function store(Post $post, Request $request)
    {
        // dd($request->all());
        $input = $request['post'];
        $post->fill($input)->save();
        return redirect('/posts/' . $post->id);
    }
}
