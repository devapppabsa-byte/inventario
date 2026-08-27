<?php

namespace App\Http\Controllers;

use App\Models\PostUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class postUserController extends Controller
{


    public function postUser(){

        $post_user = new PostUser();

        $post_user->post_body = request('post_body');
        $post_user->user_id = Auth::user()->id;

        $post_user->save();

        return redirect()->back();
    }



    public function reaction(Request $request, Post $post){

        

    }




}
