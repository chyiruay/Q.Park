<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;

use App\Models\User; 
use App\Models\Feedback; //import

class FeedbackController extends Controller
{
    public function store(Request $request){
        //no feedbackID input exists, so the old 'id' => null crashed on a NOT NULL column
        $data=$request->validate([
            'feedbackContent'=>'required|string|max:255',  //feedback.content is a varchar(255)
        ]);

        $addFeedback=Feedback::create([
            'content'=>$data['feedbackContent'],
            'userID'=>Auth::id()
        ]);

        return redirect()->route('insertFeedback')->with('success','Thanks for your feedback!');
    }


    public function view(){
        $feedback=Feedback::with('user')->orderByDesc('id')->get();
        return view('showFeedback')->with('feedback',$feedback);
    }


    public function index(){
    
        return view('insertFeedback');
    }
}
