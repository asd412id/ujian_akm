<?php

namespace App\Http\Livewire;

use App\Models\PesertaLogin;
use Livewire\Component;
use WireUi\Traits\Actions;

class StatusPeserta extends Component
{
	use Actions;

	public $params;
	public $jadwal;
	public $search = '';
	public $login = 'all';
	public $sekolah;

	public function mount()
	{
		$this->jadwal = $this->params;
		$this->sekolah = auth()->user()->sekolah;
	}

	public function resetLogin(PesertaLogin $dlogin)
	{
		return $this->dialog()->confirm([
			'title' => 'Reset Login Peserta?',
			'description' => $dlogin->peserta->name,
			'acceptLabel' => 'Reset',
			'rejecttLabel' => 'Tidak',
			'method' => 'doResetLogin',
			'params' => $dlogin->id
		]);
	}

	public function doResetLogin(PesertaLogin $dlogin)
	{
		$dlogin->update(['reset' => 2, 'end' => null, 'created_at' => now()]);
		$dlogin->peserta()->update(['is_login' => false]);
		return $this->notification()->success('Login peserta berhasil direset (' . $dlogin->peserta->name . ')');
	}

	public function resetUjian(PesertaLogin $dlogin)
	{
		return $this->dialog()->confirm([
			'title' => 'Reset Ujian Peserta ' . $dlogin->peserta->name . '?',
			'description' => 'Semua jawaban peserta ini akan terhapus!',
			'acceptLabel' => 'Reset',
			'rejecttLabel' => 'Tidak',
			'method' => 'doResetUjian',
			'params' => $dlogin->id
		]);
	}

	public function doResetUjian(PesertaLogin $dlogin)
	{
		$dlogin->delete();
		return $this->notification()->success('Ujian peserta berhasil direset (' . $dlogin->peserta->name . ')');
	}

	public function stopUjian(PesertaLogin $dlogin)
	{
		return $this->dialog()->confirm([
			'title' => 'Stop Ujian Peserta ' . $dlogin->peserta->name . '?',
			'description' => 'Ujian peserta ini akan dihentikan',
			'acceptLabel' => 'Stop Ujian',
			'rejecttLabel' => 'Tidak',
			'method' => 'doStopUjian',
			'params' => $dlogin->id
		]);
	}

	public function doStopUjian(PesertaLogin $dlogin)
	{
		$dlogin->update([
			'end' => now()
		]);
		return $this->notification()->success('Ujian peserta berhasil dihentikan (' . $dlogin->peserta->name . ')');
	}

	public function render()
	{
		$notLogin = collect([]);
		$hasLogin = collect([]);

		if ($this->login == 'all' || $this->login == 'login') {
			$hasLogin = $this->jadwal->pesertas()
				->whereHas('logins', function ($q) {
					$q->where('jadwal_id', $this->jadwal->id);
				})
				->where(function ($q) {
					$q->where('name', 'like', "%$this->search%")
						->orWhere('ruang', 'like', "%$this->search%");
				})
				->orderBy('created_at', 'desc')
				->get();
		}

		if ($this->login == 'all' || $this->login == '!login') {
			$notLogin = $this->jadwal->pesertas()
				->whereDoesntHave('logins')
				->where(function ($q) {
					$q->where('name', 'like', "%$this->search%")
						->orWhere('ruang', 'like', "%$this->search%");
				})
				->orderBy('created_at', 'desc')
				->get();
		}


		$query = $hasLogin->merge($notLogin);
		return view('livewire.status-peserta', ['data' => $query]);
	}
}
