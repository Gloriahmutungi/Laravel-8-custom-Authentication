<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Hash;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB; 
use Carbon\Carbon; 
use Mail; 
use Illuminate\Support\Str;

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
    public function showForgetPasswordForm(){
        return view('auth.forgetPassword');
    }
    
    public function submitForgetPasswordForm(Request $request){
        $request->validate([
            'email' =>'required|exists:users|email'
        ]);
        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email'=>$request->email,
            'token'=>$token,
            'created_at'=>Carbon::now()
        ]);
        Mail::send('forgetPassword', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });
        Session::flash('success',"We have e-mailed your password reset link!");
        return redirect()->route('forget.password.get');
    }

    public function showResetPasswordForm($token){
        return view('auth.forgetPasswordLink',['token' =>$token]);
    }

    public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
  
          if(!$updatePassword){
              Session::flash('fail', 'Invalid token');
              return redirect()->route('reset.password.get');
          }
  
          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
          Session::flash('success',"Your password has been changed!");
          return redirect()->route('login');
      }
}
