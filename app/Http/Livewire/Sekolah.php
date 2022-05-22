<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use WireUi\Traits\Actions;

class Sekolah extends Component
{
	use Actions;

	public $nama_sekolah;
	public $logo_sekolah;
	public $kop_sekolah;
	public $nama_admin;
	public $username;
	public $password;
	public $newpassword;
	public $renewpassword;
	public $limitlogin = false;

	public function mount()
	{
		$this->nama_sekolah = auth()->user()->sekolah->name;
		$this->logo_sekolah = auth()->user()->sekolah->logo;
		$this->kop_sekolah = auth()->user()->sekolah->kop;
		$this->limitlogin = auth()->user()->sekolah->limit_login;
		$this->nama_admin = auth()->user()->name;
		$this->email = auth()->user()->email;
	}

	public function render()
	{
		return view('livewire.sekolah');
	}

	public function update()
	{
		$this->validate([
			'nama_sekolah' => 'required|unique:sekolahs,name,' . auth()->user()->sekolah_id
		], [
			'nama_sekolah.required' => 'Nama sekolah tidak boleh kosong',
			'nama_sekolah.unique' => 'Nama sekolah sudah digunakan'
		]);
		$update = auth()->user()->sekolah;
		$update->name = $this->nama_sekolah;
		$update->opt = [
			'kop' => $this->kop_sekolah,
			'logo' => $this->logo_sekolah,
			'limit_login' => $this->limitlogin,
		];
		if ($update->save()) {
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function updateUser()
	{
		$rules = [
			'nama_admin' => 'required',
			'email' => 'required|unique:users,email,' . auth()->user()->id,
			'password' => 'required',
		];
		$msgs = [
			'nama_admin.required' => 'Nama admin tidak boleh kosong',
			'email.required' => 'Alamat email tidak boleh kosong',
			'password.required' => 'Password tidak boleh kosong',
			'email.unique' => 'Alamat email sudah digunakan'
		];

		if ($this->newpassword) {
			$rules['renewpassword'] = 'same:newpassword';
			$msgs['renewpassword.same'] = 'Perulangan password tidak benar';
		}

		$this->validate($rules, $msgs);

		if (!Hash::check($this->password, auth()->user()->password)) {
			return $this->addError('password', 'Password tidak benar');
		}

		$update = auth()->user();
		$update->name = $this->nama_admin;
		$update->email = $this->email;
		if ($this->newpassword) {
			$update->password = bcrypt($this->newpassword);
		}
		if ($update->save()) {
			$this->resetValidation();
			$this->reset(['password', 'newpassword', 'renewpassword']);
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function fixFolder()
	{
		$uploads = public_path('uploads');
		$thumbs = public_path('thumbs');
		if (is_dir($uploads)) {
			rmdir($uploads);
		}
		if (is_dir($thumbs)) {
			rmdir($thumbs);
		}
		try {
			if (!Storage::disk('public')->exists('uploads')) {
				Storage::disk('public')->makeDirectory('uploads');
				Storage::disk('public')->makeDirectory('thumbs');
			}
			Artisan::call('storage:link');
			return $this->notification()->success('Struktur folder berhasil diperbaiki!');
		} catch (\Throwable $th) {
			return $this->notification()->error('Tidak dapat melakukan perbaikan!');
		}
	}
}
