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
    public function viewAllUser(){
        $users=User::orderBy('id')->get();
        return view('showUser')->with('users',$users);
    }

    public function showUser(){
        return view('showProfile')->with('user',Auth::user());
    }

    public function edit($id){

        //ignore the id in the URL; you may only edit your own profile
        return view('editProfile')->with('user',Auth::user());
    }

    public function update(Request $request){
        $user=Auth::user();
        $data=$request->validate([
            'userName'=>'required|string|max:255',
            'email'=>'required|email|max:255|unique:users,email,'.$user->id,
            'phone_number'=>'required|string|max:20|unique:users,phone_number,'.$user->id,
        ]);

        $user->name=$data['userName'];
        $user->email=$data['email'];
        $user->phone_number=$data['phone_number'];
        $user->save();
        Session::flash('success',"Profile updated successful!");

        return redirect()->route('viewProfile');
    }

    public function editPassword($id){

        return view('changePassword');
    }

    public function updatePassword(Request $request) {
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            return redirect()->back()->withErrors(['current-password'=>'Your current password is incorrect.']);
        }
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            return redirect()->back()->withErrors(['new-password'=>'New password cannot be the same as your current password.']);
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

    /**
     * Was deleteUser($id) on a GET route — any id, no owner check, fired on a
     * prefetch. Deletes the signed-in account only.
     */
    public function destroyAccount(){
        $user=Auth::user();
        Auth::logout();
        $user->delete();

        return redirect()->route('login');
    }
}