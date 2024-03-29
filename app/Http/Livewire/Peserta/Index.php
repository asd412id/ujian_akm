<?php

namespace App\Http\Livewire\Peserta;

use App\Models\ItemSoal;
use App\Models\Jadwal;
use App\Models\PesertaLogin;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class Index extends Component
{
	use Actions;
	use WithPagination;

	public $user;
	public $jadwal;
	public $login = null;
	protected $logins;
	public $jid;

	public function getJadwal()
	{
		if (!$this->user->is_login || session()->getId() != $this->user->session_id) {
			auth()->logout();
			return redirect()->route('index')->with('error', 'Anda telah login di perangkat lain!');
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

		if ($this->login && $this->login->reset != 2 && now()->greaterThan($this->login->created_at->addMinutes($this->login->jadwal->duration))) {
			$this->stop();
		}

		if (!$this->login) {
			$this->logins = $this->user->logins()
				->orderBy('end', 'desc')
				->paginate(10);
			$this->jadwal = $this->user->jadwals()
				->where('active', true)
				->where('start', '<=', now())
				->where('end', '>=', now())
				->whereDoesntHave('logins', function ($q) {
					$q->where('peserta_id', $this->user->id);
				})
				->orderBy('start', 'asc')
				->orderBy('created_at', 'asc')
				->orderBy('name', 'asc')
				->limit(1)
				->get();
		}
	}

	public function stop()
	{
		$this->login->end = now()->lessThanOrEqualTo($this->login->created_at->addMinutes($this->login->jadwal->duration)) ? now() : $this->login->created_at->addMinutes($this->login->jadwal->duration);
		$this->login->created_at = now()->lessThanOrEqualTo($this->login->created_at->addMinutes($this->login->jadwal->duration)) ? now() : $this->login->created_at->addMinutes($this->login->jadwal->duration);
		$this->login->save();
		$this->reset('login');
	}

	public function checkJadwal()
	{
		return $this->getJadwal();
	}

	public function render()
	{
		$this->getJadwal();
		return view('livewire.peserta.index', ['dataLogins' => $this->logins]);
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
			'title' => '<div class="text-xl font-bold">' . $jadwal->name . '</div><div class="-mt-1 text-sm italic">' . nl2br($jadwal->desc) . '</div><br>Ikut Ujian Sekarang?',
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
