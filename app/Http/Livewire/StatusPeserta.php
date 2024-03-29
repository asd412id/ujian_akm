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
		$dlogin->update(['reset' => 2, 'end' => null, 'created_at' => now()->lessThanOrEqualTo($dlogin->created_at->addMinutes($dlogin->jadwal->duration)) ? now() : $dlogin->created_at->addMinutes($dlogin->jadwal->duration)]);
		$dlogin->peserta()->update(['is_login' => false, 'session_id' => null]);
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
		$dlogin->update(['reset' => 3]);
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
		if ($dlogin && is_null($dlogin->end)) {
			$dlogin->update([
				'end' => now()->lessThanOrEqualTo($dlogin->created_at->addMinutes($dlogin->jadwal->duration)) ? now() : $dlogin->created_at->addMinutes($dlogin->jadwal->duration),
				'created_at' => now()->lessThanOrEqualTo($dlogin->created_at->addMinutes($dlogin->jadwal->duration)) ? now() : $dlogin->created_at->addMinutes($dlogin->jadwal->duration),
			]);
		}
		return $this->notification()->success('Ujian peserta berhasil dihentikan (' . $dlogin->peserta->name . ')');
	}

	public function stopPeserta(PesertaLogin $dlogin)
	{
		if ($dlogin && is_null($dlogin->end)) {
			$dlogin->update([
				'end' => now()->lessThanOrEqualTo($dlogin->created_at->addMinutes($dlogin->jadwal->duration)) ? now() : $dlogin->created_at->addMinutes($dlogin->jadwal->duration),
				'created_at' => now()->lessThanOrEqualTo($dlogin->created_at->addMinutes($dlogin->jadwal->duration)) ? now() : $dlogin->created_at->addMinutes($dlogin->jadwal->duration),
			]);
		}
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
						->orWhere('uid', 'like', "%$this->search%")
						->orWhere('ruang', 'like', "%$this->search%");
				})
				->select('pesertas.*')
				->join('peserta_logins', 'peserta_logins.peserta_id', '=', 'pesertas.id')
				->orderBy('peserta_logins.created_at', 'desc')
				->get();
		}

		if ($this->login == 'all' || $this->login == '!login') {
			$notLogin = $this->jadwal->pesertas()
				->whereDoesntHave('logins', function ($q) {
					$q->where('jadwal_id', $this->jadwal->id);
				})
				->where(function ($q) {
					$q->where('name', 'like', "%$this->search%")
						->orWhere('uid', 'like', "%$this->search%")
						->orWhere('ruang', 'like', "%$this->search%");
				})
				->orderBy('uid', 'asc')
				->orderBy('name', 'asc')
				->get();
		}


		$query = $hasLogin->merge($notLogin);
		return view('livewire.status-peserta', ['data' => $query]);
	}
}
