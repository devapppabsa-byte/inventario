<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostUser extends Model
{
    use HasFactory;


    protected $table = "post_users";
    protected $fillable = ["post_body", "user_id"];


    
    
    public function user(){

        return $this->belongsTo(User::class);
    
    }


    public function commentPostUser(){

        return $this->hasMany(CommentUserPost::class);

    }


    public function reactionPostUser(){

        return $this->hasMany(ReactionPostUser::class);
    
    }


    public function likeCount(){

        return $this->reactionPostUser()->where('reaction', 'like')->count();

    }



    public function dislikeCount(){
        return $this->reactionPostUser()->where('reaction', 'dislike')->count();
    }


    








}
