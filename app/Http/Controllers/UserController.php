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

        //ignore the id in the URL; you may only edit your own profile
        return view('editProfile')->with('users',[Auth::user()]);
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

        return view('changePassword')->with('users',[Auth::user()]);
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