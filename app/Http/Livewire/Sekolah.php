<?php

namespace App\Http\Livewire;

use App\Models\Mapel;
use App\Models\Peserta;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use WireUi\Traits\Actions;

class Sekolah extends Component
{
	use Actions;
	use WithFileUploads;

	public $nama_sekolah;
	public $logo_sekolah;
	public $excel;
	public $kop_sekolah;
	public $nama_admin;
	public $username;
	public $password;
	public $newpassword;
	public $renewpassword;
	public $limitlogin = false;
	public $restricttest = false;

	public function mount()
	{
		$this->nama_sekolah = auth()->user()->sekolah->name;
		$this->logo_sekolah = auth()->user()->sekolah->logo ?? '[g]kop_sekolah.png[/g]';
		$this->kop_sekolah = auth()->user()->sekolah->kop;
		$this->limitlogin = auth()->user()->sekolah->limit_login;
		$this->restricttest = auth()->user()->sekolah->restrict_test;
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
			'restrict_test' => $this->restricttest,
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

	public function downloadExcel()
	{
		return response()->download(resource_path('master_data.xlsx'), 'Master Data' . ($this->nama_sekolah ? ' ' . $this->nama_sekolah : '') . ' - ' . env('APP_NAME', 'Aplikasi Ujian') . '.xlsx');
	}

	public function updatedExcel()
	{
		$this->validate([
			'excel' => 'required|mimes:xls,xlsx,ods,bin'
		], [
			'excel.required' => 'File excel tidak boleh kosong',
			'excel.mimes' => 'Format file yang diimport tidak dikenali',
		]);

		$reader = IOFactory::load($this->excel->path());
		$mapel = $reader->getSheetByName('Mapel');
		$penilai = $reader->getSheetByName('Penilai');
		$peserta = $reader->getSheetByName('Peserta');

		if (!$mapel || !$penilai || !$peserta) {
			return $this->notification()->warning('Format master data tidak sesuai! Silahkan download template master data terlebih dahulu');
		}

		$sekolah_id = auth()->user()->sekolah->id;

		if ($mapel) {
			$mapel = $mapel->toArray();
			if (count($mapel) <= 1) {
				$this->notification()->warning('Data mata pelajaran tidak tersedia');
			} else {
				$i = 0;
				foreach ($mapel as $key => $row) {
					if ($key == 0) {
						continue;
					}
					if (!$row[0] || !is_numeric($row[0]) || !$row[1]) {
						continue;
					}
					$mapel = trim($row[1]);
					$check = Mapel::where('name', $mapel)
						->where('sekolah_id', $sekolah_id)
						->first();
					if (!$check) {
						$new = new Mapel();
						$new->name = $mapel;
						$new->sekolah_id = $sekolah_id;
						$new->save();
						$i++;
					}
				}
				$this->notification()->success($i . " data mata pelajaran berhasil ditambahkan");
			}
		}
		if ($penilai) {
			$penilai = $penilai->toArray();
			if (count($penilai) <= 1) {
				$this->notification()->warning('Data penilai tidak tersedia');
			} else {
				$i = 0;
				$j = 0;
				foreach ($penilai as $key => $row) {
					if ($key == 0) {
						continue;
					}
					if (!$row[0] || !is_numeric($row[0]) || !$row[2]) {
						continue;
					}
					$email = trim($row[2]);
					$check = User::where('email', $email)
						->where('sekolah_id', '!=', $sekolah_id)
						->first();
					if ($check) {
						$this->notification()->error('Email ' . $email . ' telah digunakan');
					} else {
						$new = User::where('email', $email)
							->where('sekolah_id', $sekolah_id)
							->where('role', 1)
							->first();
						if (!$new) {
							$new = new User();
							$new->email = $email;
							$new->sekolah_id = $sekolah_id;
							$new->role = 1;
							$new->email_verified_at = now();
							$i++;
						}
						$new->name = trim($row[1]);
						$new->password = bcrypt(trim($row[3]));
						if ($new->save()) {
							$mapels = array_map(function ($v) {
								return trim($v);
							}, explode(',', trim($row[4])));

							if (count($mapels)) {
								$mid = [];
								foreach ($mapels as $key => $v) {
									$mp = Mapel::where('name', $v)
										->where('sekolah_id', $sekolah_id)
										->first();
									if (!$mp) {
										$newm = new Mapel();
										$newm->name = $v;
										$newm->sekolah_id = $sekolah_id;
										$newm->save();
										if (!in_array($newm->id, $mid)) {
											array_push($mid, $newm->id);
										}
										$j++;
									} else {
										if (!in_array($mp->id, $mid)) {
											array_push($mid, $mp->id);
										}
									}
								}
								$new->mapels()->sync($mid);
							}
						}
					}
				}
				$this->notification()->success($i . " data penilai berhasil ditambahkan");
				if ($j > 0) {
					$this->notification()->success($j . " data mata pelajaran berhasil ditambahkan");
				}
			}
		}
		if ($peserta) {
			$peserta = $peserta->toArray();
			if (count($peserta) <= 1) {
				$this->notification()->warning('Data peserta tidak tersedia');
			} else {
				$i = 0;
				foreach ($peserta as $key => $row) {
					if ($key == 0) {
						continue;
					}
					if (!$row[0] || !is_numeric($row[0]) || !$row[1]) {
						continue;
					}
					$uid = trim($row[1]);
					$check = Peserta::where('uid', $uid)
						->where('sekolah_id', '!=', $sekolah_id)
						->first();
					if ($check) {
						$this->notification()->error('ID Peserta ' . $uid . ' telah digunakan');
					} else {
						$check = Peserta::where('uid', $uid)
							->where('sekolah_id', $sekolah_id)
							->first();
						if (!$check) {
							$check = new Peserta();
							$check->uid = $uid;
							$check->sekolah_id = $sekolah_id;
							$check->token = sha1($key . time());
							$i++;
						}
						$check->name = trim($row[2]);
						$check->jk = trim($row[3]);
						$check->password = bcrypt(trim($row[4]));
						$check->password_string = trim($row[4]);
						$check->ruang = trim($row[5]);
						$check->save();
					}
				}
				$this->notification()->success($i . " data peserta berhasil ditambahkan");
			}
		}
	}
}
