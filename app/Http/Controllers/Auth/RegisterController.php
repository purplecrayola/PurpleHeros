<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /** register page */
    public function register()
    {
        if (! $this->canManageUsers()) {
            Toastr::error('Self-service registration is disabled. Contact your system administrator.', 'Error');
            return redirect()->route('login');
        }

        $role = DB::table('role_type_users')->orderBy('role_type')->get();

        return view('auth.register', compact('role'));
    }

    /** insert new users */
    public function storeUser(Request $request): RedirectResponse
    {
        if (! $this->canManageUsers()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role_name' => 'required|string|max:255|exists:role_type_users,role_type',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        try {
            $todayDate = Carbon::now()->format('Y-m-d H:i:s');

            User::create([
                'name' => $request->name,
                'avatar' => null,
                'email' => $request->email,
                'join_date' => $todayDate,
                'last_login' => $todayDate,
                'role_name' => $request->role_name,
                'status' => 'Active',
                'password' => Hash::make($request->password),
            ]);

            Toastr::success('Create new account successfully :)', 'Success');

            return redirect()->route('userManagement');
        } catch (\Throwable $exception) {
            report($exception);
            Toastr::error('Add new employee fail :)', 'Error');

            return redirect()->back()->withInput();
        }
    }

    private function canManageUsers(): bool
    {
        return Auth::check() && in_array(Auth::user()->role_name, ['Admin', 'Super Admin'], true);
    }
}
