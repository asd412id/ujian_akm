<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'sekolah' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => 'required|captcha'
        ], [
            'sekolah.required' => 'Nama sekolah tidak boleh kosong',
            'name.required' => 'Nama lengkap tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.unique' => 'Email telah digunakan',
            'password.required' => 'Password tidak boleh kosong',
            'password.confirmed' => 'Perulangan password tidak sesuai',
            'password.min' => 'Panjang password minimal 8 karakter',
            'g-recaptcha-response.required' => 'Pastikan Anda bukan robot dengan mencentang reCAPTCHA di bawah',
            'g-recaptcha-response.captcha' => 'Verifkasi captcha bermasalah! Silahkan hubungi developer',
        ]);

        $configs = [];
        $configs['must_verified'] = false;
        if (Storage::exists('configs.json')) {
            $configs = file_get_contents(Storage::path('configs.json'));
            if (isValidJSON($configs)) {
                $configs = json_decode($configs, true);
            }
        }

        $sekolah = Sekolah::create([
            'name' => $request->sekolah
        ]);

        $userdata = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 0,
        ];

        if (!isset($configs['must_verified']) || (isset($configs['must_verified']) && !$configs['must_verified'])) {
            $userdata['email_verified_at'] = now();
        }

        $user = $sekolah->users()->create($userdata);

        event(new Registered($user));

        Auth::login($user);

        if (auth()->user()->sekolah) {
            setUserFolder(auth()->user()->sekolah->id);
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
