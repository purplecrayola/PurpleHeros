<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'locked',
            'unlock'
        ]);
    }

    /** index login page */
    public function login()
    {
        return view('auth.login');
    }

    /** login page to check database table users */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $username = $request->email;
            $password = $request->password;
            $todayDate = Carbon::now()->format('Y-m-d H:i:s');

            if (Auth::attempt(['email' => $username, 'password' => $password, 'status' => 'Active'])) {
                $request->session()->regenerate();

                $user = Auth::user();
                Session::put('name', $user->name);
                Session::put('email', $user->email);
                Session::put('user_id', $user->user_id);
                Session::put('join_date', $user->join_date);
                Session::put('phone_number', $user->phone_number);
                Session::put('status', $user->status);
                Session::put('role_name', $user->role_name);
                Session::put('avatar', $user->avatar);
                Session::put('position', $user->position);
                Session::put('department', $user->department);

                User::where('email', $username)->update(['last_login' => $todayDate]);

                Toastr::success('Login successfully :)', 'Success');

                $defaultRedirect = $user->isAdmin() ? url('/admin') : route('em/dashboard');

                return redirect()->intended($defaultRedirect);
            }

            Toastr::error('Fail, wrong email or password :)', 'Error');
            return redirect()->route('login');
        } catch (\Throwable $exception) {
            report($exception);
            Toastr::error('Login failed :)', 'Error');
            return redirect()->back()->withInput($request->only('email'));
        }
    }

    /** logout and forget session */
    public function logout(Request $request)
    {
        $todayDate = Carbon::now()->format('Y-m-d H:i:s');

        if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
            DB::table('activity_logs')->insert([
                'name' => Session::get('name'),
                'email' => Session::get('email'),
                'description' => 'Has log out',
                'date_time' => $todayDate,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Toastr::success('Logout successfully :)', 'Success');

        return redirect()->route('login');
    }
}
