<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use Hash;

use App\Models\User; //import

use Session;

class UserController extends Controller
{
    public function store(){
        $r=request(); //request means received the form data by method get or post
        $addUser=User::create([
            'id'=>$r->userID,  //'id' is  database field name, categoryID is HTML input name
            'name'=>$r->userName,
            'phone_number'=>$r->userPhoneNumber,
            'password'=>$r->password,
        ]);
        return redirect()->route('showUser'); //after insert redirect to view category
    }

    public function viewAllUser(){
        $user=User::all(); //apply SQL select * from categories
        return view('showUser')->with('user',$user);
    }

    public function showUser(){
        $users=DB::table('users')

        ->select('users.id','users.name','users.email','users.phone_number')
        ->where('users.id','=',Auth::id())
        //->get();
        ->paginate(9);
        return view('showProfile')->with('users',$users);
    }

    public function edit($id){

        $user=User::all()->where('id',$id);
        //select * from categories where id='$id'
        return view('editProfile')->with('users',$user);
    }

    public function update(){
        $r=request();
        $user=User::find($r->hid); //retrive the record based on id
        $user->name=$r->userName;      
        $user->email=$r->email;
        $user->phone_number=$r->phone_number;
        $user->save();
        Session::flash('success',"Profile updated successful!");
        
        return redirect()->route('viewProfile');
    }

    public function editPassword($id){

        $user=User::all()->where('id',$id);
        //select * from categories where id='$id'
        return view('changePassword')->with('users',$user);
    }

    public function updatePassword(Request $request) {
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Your current password does not matches with the password.");
        }
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            // Current password and new password same
            return redirect()->back()->with("error","New Password cannot be same as your current password.");
        }
        $validatedData = $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:8|confirmed',
        ]);
        //Change Password
        $user = Auth::user();
        $user->password = bcrypt($request->get('new-password'));
        $user->save();
        return redirect()->back()->with("success","Password successfully changed!");
    }

    public function deleteAccount(){
        $users=DB::table('users')

        ->select('users.id','users.name','users.email','users.phone_number')
        ->where('users.id','=',Auth::id())
        //->get();
        ->paginate(9);
        return view('delete')->with('users',$users);
    }

    public function deleteUser($id){
        $delete=User::find($id);
        $delete->delete();  //run SQL delete from my_Cart where id=$id
        return redirect()->route('login');
    }
}