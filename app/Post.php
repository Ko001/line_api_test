<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
   public function getPaginateByLimit(int $max_post = 10){
       # $thisはインスタンス自身を示すから、Postデータを示す
       return $this->orderBy('id', 'DESC')->paginate($max_post);
   }
}