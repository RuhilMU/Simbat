<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $judul = "Manajemen Akun";
        $users = User::paginate(10);
        return view('pages.super.user', compact('judul', 'users'));
    }
    public function store(Request $request)
    {
        try {
            $avatar = 'avatar/Avatar.jpg';

            if ($request->hasFile('avatar')) {
                $avatar = Storage::disk('public')->put('avatar', $request->file('avatar'));
            }
            $user = new User();
            $user->name = $request->name;
            $user->role = $request->role;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->avatar = $avatar;
            $user->save();
            return back()->with('success', 'Berhasil membuat user');
        } catch (\Throwable $th) {
            return back()->with('error', 'Gagal membuat user');
        }
    }
    public function update(Request $request, User $user)
    {
        try {
            $user->update($request->except(['avatar']));
            if ($request->hasFile('avatar')) {
                Storage::disk('public')->delete($user->avatar);
                $new = Storage::disk('public')->put('avatar', $request->file('avatar'));
                $user->avatar = $new;
                $user->save();
            }
            return back()->with('success', 'Berhasil mengubah user');
        } catch (\Throwable $th) {
            return back()->with('error', 'Gagal mengubah user');
        }
    }
    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id() && $user->role === 'super') {
                return back()->with('error', 'Tidak dapat menghapus Super Admin');
            }
            
            $user->delete();
            return back()->with('success', 'User behasil dihapus');
        } catch (\Throwable $th) {
            return back()->with('error', 'Gagal menghapus user');
        }
    }
    public function login(Request $request)
    {
        if ($request->isMethod("get")) {
            return view('login', ["title" => "Login"]);
        } elseif ($request->isMethod("post")) {
            $credentials = $request->validate([
                "email" => ['required', 'email', 'lowercase'],
                "password" => ['required']
            ]);
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->intended('/');
            } else {
                return back()->with('error', 'GAGAL');
            }
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect("/");
    }
    public function forgot(Request $request)
    {
        if ($request->isMethod("get")) {
            return view('forgot', ["title" => "Forgot Password"]);
        } elseif ($request->isMethod("post")) {
            $request->validate(['email' => 'required|email']);
            $status = Password::sendResetLink($request->only('email'));
            return $status === Password::RESET_LINK_SENT
                ? back()->with('success',"Link reset password terkirim")
                : back()->with('error',"Link reset password gagal");
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'token' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->with('error',$status);
    }

    public function showResetForm($token)
    {
        return view('reset', ['token' => $token]);
    }

    public function settings(Request $request)
    {
        if ($request->isMethod("get")) {
            $judul = "Atur Profil Klinik";
            return view('pages.settings', compact('judul'));
        } elseif ($request->isMethod("put")) {
            $profile = Profile::first();
            try {
                if ($request->hasFile('logo')) {
                    Storage::disk('public')->delete($profile->logo);
                    $new = Storage::disk('public')->put('profile', $request->file('logo'));
                    $profile->update($request->all());
                    $profile->logo = $new;
                    $profile->save();
                } else {
                    $profile->update($request->all());
                }
                return back()->with('success', 'Profil klinik berhasil diubah');
            } catch (\Throwable $th) {
                return back()->with('error', 'Profil klinik gagal diubah');
            }
        }
    }
}
