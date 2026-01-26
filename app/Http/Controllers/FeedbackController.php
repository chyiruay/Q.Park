<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Models\User; 
use App\Models\Feedback; //import

class FeedbackController extends Controller
{
    public function store(){
        $r=request(); //request means received the form data by method get or post
        $addFeedback=Feedback::create([
            'id'=>$r->feedbackID,  //'id' is  database field name, categoryID is HTML input name
            'content'=>$r->feedbackContent,
        ]);
        return view('welcome'); //after insert redirect to view category
    }


    public function view(){
        $feedback=Feedback::all(); //apply SQL select * from categories
        return view('showFeedback')->with('feedback',$feedback);
    }


    public function index(){
    
        return view('insertFeedback');
    }
}
