<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Hash;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CustomAuthController extends Controller
{
    public function index(){
        return view('auth.login');
    }
    public function customLogin(Request $request){
        $request->validate([
            'email' =>'required',
            'password'=>'required',
        ]);
        $credentials = $request->only('email','password');
        if(Auth::attempt($credentials)){
            Session::flash('success','You are signed in!');
            return redirect("dashboard");
        }else{
            Session::flash('fail','wrong email or password!');
            return redirect()->route('login');
            
        }
    }
    public function registration(){
        return view('auth.registration');
    }
    public function customRegistration(Request $request){
        $request->validate([
            'firstname' =>'required',
            'lastname' =>'required',
            'phonenumber'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6',
        ]);
        $data = $request->all();
        $check =$this->create($data);
        Session::flash('success','You have signed in');
        return redirect("dashboard");
    }
    public function create(array $data)
    {
      return User::create([
        'firstname' => $data['firstname'],
        'lastname' => $data['lastname'],
        'email' => $data['email'],
        'phonenumber' => $data['phonenumber'],
        'password' => Hash::make($data['password'])
      ]);
    }   


    public function dashboard(){
        if (Auth::check()){
            return view('dashboard');
        }
        Session::flash('fail','You are not allowed to access this page');
        return redirect()->route("login");
    }
    public function signOut(){
        Session::flush();
        Auth::logout();
        
        return redirect("login");
    }
}
