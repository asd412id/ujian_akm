<?php

namespace App\Http\Livewire;

use App\Models\PesertaLogin;
use Livewire\Component;
use WireUi\Traits\Actions;

class Nilai extends Component
{
	use Actions;

	public $params;
	public $jadwal;
	public $modal = false;
	public $modalTitle;
	public $search = '';
	public $login;
	public $sid = 0;
	public $sekolah;
	public $score;
	public $totalnilai;

	public function mount()
	{
		$this->jadwal = $this->params;
		$this->sekolah = auth()->user()->sekolah;
	}

	public function render()
	{
		$notLogin = collect([]);
		$hasLogin = collect([]);

		$hasLogin = $this->jadwal->pesertas()
			->whereHas('logins', function ($q) {
				$q->where('jadwal_id', $this->jadwal->id);
			})
			->where(function ($q) {
				$q->where('name', 'like', "%$this->search%")
					->orWhere('uid', 'like', "%$this->search%")
					->orWhere('ruang', 'like', "%$this->search%");
			})
			->orderBy('uid', 'asc')
			->orderBy('name', 'asc')
			->orderBy('created_at', 'asc')
			->get();

		$notLogin = $this->jadwal->pesertas()
			->whereDoesntHave('logins')
			->where(function ($q) {
				$q->where('name', 'like', "%$this->search%")
					->orWhere('uid', 'like', "%$this->search%")
					->orWhere('ruang', 'like', "%$this->search%");
			})
			->orderBy('uid', 'asc')
			->orderBy('name', 'asc')
			->orderBy('created_at', 'asc')
			->get();


		$query = $hasLogin->merge($notLogin);
		return view('livewire.nilai', ['data' => $query]);
	}

	public function inputNilai(PesertaLogin $login)
	{
		$this->login = $login;
		$this->modalTitle = 'Input Nilai (' . $login->peserta->name . ') (' . $this->jadwal->name . ')';

		$this->score = $login->tests()->select('id', 'pscore')->get()->pluck('pscore', 'id')->toArray();
		$this->totalnilai = array_sum($this->score);

		$this->modal = true;
	}

	public function updatedScore()
	{
		$this->totalnilai = array_sum($this->score);
	}

	public function updateNilai()
	{
		foreach ($this->score as $id => $n) {
			$this->login->tests()->where('id', $id)->update(['pscore' => $n]);
		}
		$this->modal = false;
		$this->notification()->success('Nilai berhasil disimpan');
	}
}
