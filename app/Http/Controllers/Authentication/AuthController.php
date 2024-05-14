<?php

namespace App\Http\Controllers\Authentication;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Admin\UserLog;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //show login page
    public function showLogin()
    {
        if(Auth::check()){
            return redirect()->back()->with('error', "Already Logged In.");
        }else{
            return view('authentication.login');
        }
    }

    //login check
    public function login(LoginRequest $request)
    {
        $user = User::where('user_name', $request->user_name)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid credentials. Please try again.');
        }

        if ($user->is_changed_password == 0) {
            return view('auth.passwords.index', compact('user'));
        }

        if (Auth::attempt($request->only('user_name', 'password'))) {
            // Check for unauthorized roles
            if ($request->user()->hasRole('Player')) {
                abort(403);
            }

            // Log user activity (assuming UserLog model)
            UserLog::create([
                'ip_address' => $request->ip(),
                'user_id' => Auth::id(), // Use Auth::id() for logged in user
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('home');
        }

        return redirect()->back()->with('error', 'Invalid credentials. Please try again.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect('/login');
    }
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'phone' => 'required',
    //         'password' => ['required', 'string', 'min:6'],
    //     ]);        

    //     $banUserCheck = User::where('user_name', $request->user_name)->first(); 
    //     if(!$banUserCheck){
    //         return redirect()->back()->with('error', "Your user_name does not exist.");
    //     }
    //     if($banUserCheck->status == 1){
    //         return redirect()->back()->with('error', "Your account has been banned.");
    //     }  

    //     $credentials = [
    //         'user_name' => $request->input('user_name'),
    //         'password' => $request->input('password'),
    //     ];

    //     if (Auth::attempt($credentials)) {
    //         // Authentication passed
    //         return redirect('/home')->with('success', 'Login Success!');
    //     } else {
    //         return redirect()->back()->with('error', 'Invalid credentials. Please try again.');
    //     }
    // }

    //show register
    public function showRegister()
    {
        if(Auth::check()){
            return redirect()->back()->with('error', "Already Logged In.");
        }else{
            return view('authentication.register');
        }
        // abort(404);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:11', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Create user based on provided credentials
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            Auth::login($user);
            return redirect('/home')->with('success', 'Logged In Successful.');
        } else {
            return redirect()->back()->with('error', 'Registration failed. Please try again.');
        }
    }
}
