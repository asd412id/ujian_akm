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
			->orderBy('created_at', 'asc')
			->get();


		$query = $hasLogin->merge($notLogin);
		return view('livewire.nilai', ['data' => $query]);
	}

	public function calculateScore()
	{
		$this->dialog()->confirm([
			'title' => 'Lakukan kalkulasi ulang nilai?',
			'description' => 'Nilai yang sudah diinput akan dikalkulasi ulang!',
			'acceptLabel' => 'Ya, Kalkulasi Nilai',
			'rejectLabel' => 'Tidak, Batalkan',
			'method' => 'doCalculateScore'
		]);
	}

	public function doCalculateScore()
	{
		if ($this->jadwal->tests()->count()) {
			foreach ($this->jadwal->tests as $v) {
				$soalOri = $v->itemSoal;
				switch ($v->type) {
					case 'pg':
						if (is_array($v->correct) && count($v->correct)) {
							foreach ($v->option as $key => $o) {
								if ($v->correct[$key]) {
									if ($soalOri->corrects[$key] == $v->correct[$key]) {
										$v->pscore = $soalOri->score;
									} else {
										$v->pscore = 0;
									}
								}
							}
						} else {
							$v->pscore = 0;
						}
						break;
					case 'pgk':
						$ccount = 0;
						foreach ($soalOri->corrects as $key => $v1) {
							$ccount += $v1 ? 1 : 0;
						}

						if (is_array($v->correct) && count($v->correct)) {
							$i = 0;
							$j = 0;
							foreach ($v->option as $key => $o) {
								if ($v->correct[$key]) {
									$j++;
									if ($soalOri->corrects[$key] == $v->correct[$key]) {
										$i++;
									} else {
										$i--;
										$j++;
									}
								}
							}
							if ($i < 0) {
								$i = 0;
							}
							if ($i <= $ccount && $j > count($soalOri->corrects)) {
								$i = 0;
							}
							$v->pscore = $ccount > 0 ? $i / $ccount * $soalOri->score : $soalOri->score;
						} else {
							$v->pscore = 0;
						}
						break;
					case 'jd':
						$ccount = 0;
						foreach ($soalOri->relations as $key => $v1) {
							$ccount += is_array($v1) ? 1 : 0;
						}
						if (is_array($v->relation) && count($v->relation)) {
							$i = 0;
							foreach ($v->relation as $key => $v2) {
								if ($v2 != null) {
									foreach ($v2 as $vr) {
										if (in_array($vr, $soalOri->relations[$key])) {
											$i++;
										}
									}
								}
							}
							$v->pscore = $ccount > 0 ? $i / $ccount * $soalOri->score : $soalOri->score;
						} else {
							$v->pscore = 0;
						}
						break;
					case 'bs':
						$i = 0;
						$v->pscore = 0;
						if (is_array($v->correct) && count($v->correct)) {
							foreach ($v->correct as $key => $v) {
								if (boolval($v) == $soalOri->corrects[$key]) {
									$i++;
								}
							}
							$v->pscore = $i / count($soalOri->options) * $soalOri->score;
						}
						break;
					case 'is':
						similar_text(strtolower(str_replace("\n", '', trim($v->answer))), strtolower(str_replace("\n", '', trim($soalOri->answer))), $percent);
						$v->pscore = round($percent) < 50 ? round($percent) / 100 * $soalOri->score : $soalOri->score;
						break;
					case 'u':
						similar_text(strtolower(str_replace("\n", '', trim($v->answer))), strtolower(str_replace("\n", '', trim($soalOri->answer))), $percent);
						$v->pscore = round($percent) < 50 ? round($percent) / 100 * $soalOri->score : $soalOri->score;
						break;
				}
				$v->save();
			}
			$this->notification()->success('Nilai ujian selesai dikalkulasi!');
			$this->render();
		} else {
			$this->notification()->warning('Tidak ada peserta ujian yang mengerjakan soal!');
		}
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
