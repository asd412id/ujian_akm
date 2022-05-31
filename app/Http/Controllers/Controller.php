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
			return redirect()->route('index')->with('error', 'ID Peserta dan password tidak sesuai')->withInput($r->only('peserta_id'));
		}

		$checkPassword = Hash::check($r->password, $user->password);

		if (!$checkPassword) {
			return redirect()->route('index')->with('error', 'ID Peserta dan password tidak sesuai')->withInput($r->only('peserta_id'));
		}

		if ($user->sekolah->limit_login && $user->is_login) {
			return redirect()->route('index')->with('error', 'Anda telah login pada perangkat lain')->withInput($r->only('peserta_id'));
		}

		Auth::guard('peserta')->login($user, true);

		$user->is_login = true;
		$user->session_id = session()->getId();
		$user->save();

		setUserFolder($user->sekolah->id);

		return redirect()->route('index');
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

			$check->is_login = true;
			$check->session_id = session()->getId();
			$check->save();
			setUserFolder($check->sekolah->id);

			return response()->json(['status' => true, 'msg' => 'Login berhasil']);
		}
		return response()->json(['status' => false, 'msg' => 'Kode QR tidak terdaftar!'], 401);
	}

	public function pesertaLogout()
	{
		$user = auth()->user();
		$user->is_login = false;
		$user->session_id = null;
		if ($user->save()) {
			auth()->logout();
			removeCookie('_userfolder');
		}
		return redirect()->route('index');
	}
}
