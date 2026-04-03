<?php

namespace App\Http\Controllers;

use App\Rules\MatchOldPassword;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountSecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showChangePassword()
    {
        return view('account.change-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        Toastr::success('Password updated successfully.', 'Success');

        return redirect()->route('change/password');
    }
}

