<?php

namespace App\Http\Controllers;

// クラス使用のための宣言
use App\Post;
use App\Http\Requests\PostRequest; //ユーザーからの入力を使用するときに使うクラス

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
    public function store(Post $post, PostRequest $request)
    {
        // dd($request->all());
        $input = $request['post'];
        $post->fill($input)->save();
        return redirect('/posts/' . $post->id);
    }
    public function edit(Post $post)
    {
        return view('edit')->with(['post' => $post]);
    }
    // ユーザーからの入力を受け取る場合は、必ずRequestを使用する
    public function update(PostRequest $request, Post $post)
    {
        $input_post = $request['post'];
        $post->fill($input_post)->save();
        return redirect('/posts/' . $post->id);
    }
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect('/');
    }
}
