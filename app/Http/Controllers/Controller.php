<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

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

		$check = Auth::guard('peserta')->attempt(['uid' => $r->peserta_id, 'password' => $r->password], true);
		if (!$check) {
			return redirect()->back()->with('error', 'ID Peserta dan password tidak sesuai')->withInput($r->only('peserta_id'));
		}

		return redirect()->route('ujian.index');
	}
}
