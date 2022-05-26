<?php

namespace App\Http\Livewire\Peserta;

use App\Models\ItemSoal;
use App\Models\Jadwal;
use App\Models\PesertaLogin;
use Livewire\Component;
use WireUi\Traits\Actions;

class Index extends Component
{
	use Actions;

	public $user;
	public $jadwal;
	public $login = null;
	public $logins;
	public $jid;

	public function getJadwal()
	{
		if (!$this->user->is_login) {
			auth()->logout();
			return redirect()->route('index');
		}

		$this->login = $this->user->logins()
			->whereNotNull('start')
			->whereNull('end')
			->orderBy('id', 'asc')
			->first();

		$ulogin = $this->user->logins()
			->where('reset', 3);
		if ($ulogin->count()) {
			foreach ($ulogin->get() as $l) {
				$l->delete();
			}
		}

		if ($this->login && now()->greaterThan($this->login->start->addMinutes($this->login->jadwal->duration))) {
			$this->login->end = now();
			$this->login->save();
			$this->reset('login');
		}

		if (!$this->login) {
			$this->logins = $this->user->logins()
				->orderBy('end', 'desc')
				->get();
			$this->jadwal = $this->user->jadwals()
				->where('active', true)
				->where('start', '<=', now())
				->where('end', '>=', now())
				->whereDoesntHave('logins', function ($q) {
					$q->where('peserta_id', $this->user->id);
				})
				->orderBy('start', 'asc')
				->get();
		}
	}

	public function checkJadwal()
	{
		return $this->getJadwal();
	}

	public function render()
	{
		$this->getJadwal();
		return view('livewire.peserta.index');
	}

	public function join(Jadwal $jadwal)
	{
		if ($this->user->sekolah_id != $jadwal->sekolah_id) {
			return $this->notification()->error('Jadwal yang dipilih tidak tesedia');
		}

		$checkLogin = $jadwal->logins()
			->where('peserta_id', $this->user->id)
			->whereNotNull('start')
			->whereNull('end')
			->first();

		if ($checkLogin) {
			if ($checkLogin->reset == 2) {
				$duration = now()->subSeconds($checkLogin->created_at->addMinutes($jadwal->duration)->diffInSeconds($checkLogin->start->addMinutes($jadwal->duration)));
				$dataUpdate['created_at'] = $duration;
			}
			$dataUpdate['reset'] = 0;
			$checkLogin->update($dataUpdate);
			return redirect()->route('ujian.tes');
		}

		$this->jid = $jadwal->id;
		return $this->dialog()->confirm([
			'title' => '<div class="font-bold text-xl">' . $jadwal->name . '</div><div class="text-sm -mt-1 italic">' . $jadwal->opt['desc'] . '</div><br>Ikut Ujian Sekarang?',
			'description' => 'Timer ujian <span class="italic font-bold">' . $jadwal->duration . ' Menit</span> akan mulai berjalan jika anda memilih untuk mengikuti!',
			'acceptLabel' => 'Ya, Ikuti Sekarang',
			'rejectLabel' => 'Tidak',
			'method' => 'joinNow',
		]);
	}

	public function joinNow()
	{
		$jadwal = Jadwal::find($this->jid);
		if ($jadwal) {
			$login = new PesertaLogin();
			$login->peserta_id = auth()->user()->id;
			$login->jadwal_id = $jadwal->id;
			$login->soal = $jadwal->shuffle ? ItemSoal::whereIn('soal_id', $jadwal->soals->pluck('id')->toArray())->inRandomOrder()->limit($jadwal->soal_count)->select('id')->get()->pluck('id')->toArray() : ItemSoal::whereIn('soal_id', $jadwal->soals->pluck('id')->toArray())->orderBy('num', 'asc')->limit($jadwal->soal_count)->select('id')->get()->pluck('id')->toArray();
			$login->start = now();
			$login->end = null;
			$login->current_number = 0;
			$login->reset = false;
			$login->save();
			return redirect()->route('ujian.tes');
		}
		return $this->notification()->error('Jadwal tidak tesedia');
	}
}
