<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
   use SoftDeletes;
   
   protected $fillable = [
      'title',
      'body',
      ];
      
   public function getPaginateByLimit(int $max_post = 10){
       # $thisはインスタンス自身を示すから、Postデータを示す
       return $this->orderBy('id', 'DESC')->paginate($max_post);
   }
}