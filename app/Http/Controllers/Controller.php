<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function pesertaLogin(Request $r)
	{
		$r->validate([
			'peserta_id' => 'required',
			'password' => 'required',
		], [
			'peserta_id.required' => 'ID Peserta tidak boleh kosong',
			'password.required' => 'Password tidak boleh kosong',
		]);

		$user = Peserta::where('uid', $r->peserta_id)->first();

		if (!$user) {
			return redirect()->back()->with('error', 'ID Peserta dan password tidak sesuai')->withInput($r->only('peserta_id'));
		}

		$checkPassword = Hash::check($r->password, $user->password);

		if (!$checkPassword) {
			return redirect()->back()->with('error', 'ID Peserta dan password tidak sesuai')->withInput($r->only('peserta_id'));
		}

		if ($user->is_login) {
			return redirect()->back()->with('error', 'Anda telah login di tempat lain')->withInput($r->only('peserta_id'));
		}

		Auth::guard('peserta')->login($user, true);

		$user->is_login = true;
		$user->save();

		setUserFolder($user->sekolah->id);

		return redirect()->back();
	}

	public function pesertaLogout()
	{
		$user = auth()->user();
		$user->is_login = false;
		if ($user->save()) {
			auth()->logout();
		}
		return redirect()->back();
	}
}
