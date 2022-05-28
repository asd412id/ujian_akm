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

		if ($user->sekolah->limit_login && $user->is_login) {
			return redirect()->back()->with('error', 'Anda telah login di tempat lain')->withInput($r->only('peserta_id'));
		}

		Auth::guard('peserta')->login($user, true);
		try {
			Auth::guard('peserta')->logoutOtherDevices($user->password);
		} catch (\Throwable $th) {
		}

		$user->is_login = true;
		$user->save();

		setUserFolder($user->sekolah->id);

		return redirect()->back();
	}

	public function loginQR(Request $r)
	{
		$r->validate([
			'qrcode' => 'required'
		], [
			'qrcode.required' => 'Kode QR tidak terdeteksi'
		]);

		$check = Peserta::where('token', $r->qrcode)->first();
		if ($check) {
			Auth::guard('peserta')->login($check, true);
			try {
				Auth::guard('peserta')->logoutOtherDevices($check->password);
			} catch (\Throwable $th) {
			}
			$check->is_login = true;
			$check->save();
			setUserFolder($check->sekolah->id);

			return response()->json(['status' => true]);
		}
		return response()->json(['status' => false]);
	}

	public function pesertaLogout()
	{
		$user = auth()->user();
		$user->is_login = false;
		if ($user->save()) {
			auth()->logout();
			removeCookie('_userfolder');
		}
		return redirect()->back();
	}
}
