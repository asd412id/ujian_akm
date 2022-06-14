<?php

namespace App\Http\Livewire\Peserta;

use App\Models\PesertaTest;
use Livewire\Component;
use WireUi\Traits\Actions;

class Ujian extends Component
{
	use Actions;

	public $user;
	public $login = null;
	public $timer = null;
	public $sid = 0;
	public $soals = [];
	public $soal;
	public $type;
	public $options = [];
	public $choices = [];
	public $schoices = [];
	public $correct;
	public $relation = [];
	public $srelation = [];
	public $answer;

	public function mount()
	{
		$this->login = $this->user->logins()
			->whereNotNull('start')
			->whereNull('end')
			->orderBy('id', 'asc')
			->first();
		if ($this->login->reset == 2) {
			$duration = now()->subSeconds($this->login->created_at->addMinutes($this->login->jadwal->duration)->diffInSeconds($this->login->start->addMinutes($this->login->jadwal->duration)));
			$dataUpdate['created_at'] = $duration;
			$dataUpdate['reset'] = 0;
			$this->login->update($dataUpdate);
		}
		$this->timer = $this->login->created_at->addMinutes($this->login->jadwal->duration)->getPreciseTimestamp(3);
	}

	public function checkLogin()
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

		if (!$this->login) {
			return redirect()->route('ujian.index')->with('msg', 'Ujian Selesai');
		} else {
			if ($this->login->reset == 3) {
				$this->login->delete();
				return redirect()->route('ujian.index');
			}
			if (now()->greaterThan($this->login->created_at->addMinutes($this->login->jadwal->duration))) {
				return $this->stop();
			}
			$this->timer = $this->login->created_at->addMinutes($this->login->jadwal->duration)->getPreciseTimestamp(3);
		}
	}

	public function checkTimer()
	{
		$this->skipRender();
		$this->checkLogin();
	}

	public function stop()
	{
		if ($this->login && is_null($this->login->end)) {
			$this->login->end = now()->lessThanOrEqualTo($this->login->created_at->addMinutes($this->login->jadwal->duration)) ? now() : $this->login->created_at->addMinutes($this->login->jadwal->duration);
			$this->login->created_at = now()->lessThanOrEqualTo($this->login->created_at->addMinutes($this->login->jadwal->duration)) ? now() : $this->login->created_at->addMinutes($this->login->jadwal->duration);
			$this->login->save();
		}
		$this->reset('login');
		return redirect()->route('ujian.index')->with('msg', 'Ujian Selesai');
	}

	public function getSoal()
	{
		$this->soals = $this->login->soals();
	}

	public function setSoal()
	{
		$this->checkLogin();
		$this->soal = $this->login->tests()->where('item_soal_id', $this->soals[$this->login->current_number]->id)->first();
		if (!$this->soal) {
			$opts = null;
			$this->soal = new PesertaTest();
			$this->soal->jadwal_id = $this->login->jadwal_id;
			$this->soal->peserta_id = $this->user->id;
			$this->soal->soal_id = $this->soals[$this->login->current_number]->soal_id;
			$this->soal->login_id = $this->login->id;
			$this->soal->item_soal_id = $this->soals[$this->login->current_number]->id;
			$this->soal->type = $this->soals[$this->login->current_number]->type;
			$this->soal->text = $this->soals[$this->login->current_number]->text;
			$this->soal->score = $this->soals[$this->login->current_number]->score;
			$this->soal->label = $this->soals[$this->login->current_number]->labels;

			if ($this->soals[$this->login->current_number]->shuffle) {
				$keys = array_keys($this->soals[$this->login->current_number]->options);
				shuffle($keys);
				foreach ($keys as $k) {
					$opts[$k] = $this->soals[$this->login->current_number]->options[$k];
				}
			} else {
				$opts = $this->soals[$this->login->current_number]->options;
			}


			if ($opts) {
				$this->soal->option = $opts;
			}
		}
		$this->sid++;
	}

	public function saveAnswer()
	{
		$this->setSoal();

		$soalOri = $this->soals[$this->login->current_number];
		switch ($this->soal->type) {
			case 'pg':
				if (count($this->schoices)) {
					$correct = [];
					foreach ($this->soal->option as $key => $o) {
						$correct[$key] = false;
						if ($this->schoices[0] == $key) {
							$correct[$key] = true;
							if ($soalOri->corrects[$key] == $correct[$key]) {
								$this->soal->pscore = $soalOri->score;
							} else {
								$this->soal->pscore = 0;
							}
						}
					}
					$this->soal->correct = $correct;
				} else {
					$this->soal->pscore = 0;
					$this->soal->correct = [];
				}
				break;
			case 'pgk':
				$ccount = 0;
				foreach ($soalOri->corrects as $key => $v) {
					$ccount += $v ? 1 : 0;
				}

				if (count($this->schoices)) {
					$correct = [];
					$i = 0;
					$j = 0;
					foreach ($this->soal->option as $key => $o) {
						$correct[$key] = false;
						if (in_array($key, $this->schoices[0])) {
							$correct[$key] = true;
							$j++;
							if ($soalOri->corrects[$key] == $correct[$key]) {
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
					$this->soal->pscore = $ccount > 0 ? $i / $ccount * $soalOri->score : $soalOri->score;
					$this->soal->correct = $correct;
				} else {
					$this->soal->pscore = 0;
					$this->soal->correct = [];
				}
				break;
			case 'jd':
				$ccount = 0;
				foreach ($soalOri->relations as $key => $v) {
					$ccount += is_array($v) ? 1 : 0;
				}
				if (is_array($this->srelation) && count($this->srelation)) {
					$r = [];
					$i = 0;
					foreach ($this->srelation as $key => $v) {
						$dkey = str_replace(['start', '_' . $this->soal->id], '', $key);
						$r[$dkey] = null;
						if ($v != null) {
							$dv = str_replace(['end', '_' . $this->soal->id], '', $v);
							$r[$dkey] = [$dv];
							if (in_array($dv, $soalOri->relations[$dkey])) {
								$i++;
							}
						}
					}
					$this->soal->pscore = $ccount > 0 ? $i / $ccount * $soalOri->score : $soalOri->score;
					$this->soal->relation = $r;
				} else {
					$this->soal->pscore = 0;
					$this->soal->relation = [];
				}
				break;
			case 'bs':
				$r = [];
				$i = 0;
				foreach ($this->choices as $key => $v) {
					$r[$key] = boolval($v);
					if ($r[$key] == $soalOri->corrects[$key]) {
						$i++;
					}
				}
				$this->soal->pscore = $i / count($soalOri->options) * $soalOri->score;
				$this->soal->correct = $r;
				break;
			case 'is':
				similar_text(strtolower(str_replace("\n", '', trim($this->answer))), strtolower(str_replace("\n", '', trim($soalOri->answer))), $percent);
				$this->soal->pscore = round($percent) < 50 ? round($percent) / 100 * $soalOri->score : $soalOri->score;
				$this->soal->answer = $this->answer;
				break;
			case 'u':
				similar_text(strtolower(str_replace("\n", '', trim($this->answer))), strtolower(str_replace("\n", '', trim($soalOri->answer))), $percent);
				$this->soal->pscore = round($percent) < 50 ? round($percent) / 100 * $soalOri->score : $soalOri->score;
				$this->soal->answer = $this->answer;
				break;
		}

		$this->soal->save();
	}

	public function getAnswer()
	{
		$this->setSoal();
		$this->reset(['choices', 'relation', 'answer', 'srelation']);

		switch ($this->soal->type) {
			case 'pg':
				$this->type = 'Pilihan Ganda';
				if (is_array($this->soal->correct) && count($this->soal->correct)) {
					foreach ($this->soal->correct as $key => $value) {
						if ($value) {
							$this->choices[0] = $key;
							$this->schoices[0] = $key;
							break;
						}
					}
				}
				break;
			case 'pgk':
				$this->type = 'Pilihan Ganda Kompleks';
				if (is_array($this->soal->correct) && count($this->soal->correct)) {
					$this->schoices[0] = [];
					foreach ($this->soal->correct as $key => $value) {
						if ($value) {
							$this->choices[] = $key;
							$this->schoices[0][] = $key;
						} else {
							$this->choices[] = null;
							$this->schoices[0][] = null;
						}
					}
				}
				break;
			case 'jd':
				$this->type = 'Menjodohkan';
				if (is_array($this->soal->relation) && count($this->soal->relation)) {
					foreach ($this->soal->relation as $key => $r) {
						if ($r) {
							$this->relation['start' . $key . '_' . $this->soal->id] = 'end' . $r[0] . '_' . $this->soal->id;
							$this->srelation['start' . $key . '_' . $this->soal->id] = 'end' . $r[0] . '_' . $this->soal->id;
						}
					}
				}
				break;
			case 'bs':
				$this->type = 'Benar/Salah';
				if (is_array($this->soal->correct)) {
					foreach ($this->soal->correct as $key => $c) {
						$this->choices[$key] = boolval($c);
					}
				}
				break;
			case 'is':
				$this->type = 'Isian Singkat';
				$this->answer = $this->soal->answer;
				break;
			case 'u':
				$this->type = 'Uraian';
				$this->answer = $this->soal->answer;
				break;
		}

		$this->soal->save();
	}

	public function prevSoal()
	{
		$this->login->current_number = $this->login->current_number == 0 ? count($this->login->soal) - 1 : $this->login->current_number - 1;
		$this->login->save();
		$this->getAnswer();
	}

	public function nextSoal()
	{
		$this->login->current_number = $this->login->current_number == count($this->login->soal) - 1 ? 0 : $this->login->current_number + 1;
		$this->login->save();
		$this->getAnswer();
	}

	public function toSoal($number)
	{
		if ($number >= count($this->login->soal)) {
			$number = count($this->login->soal) - 1;
		} elseif ($number < 0) {
			$number = 0;
		}
		$this->login->current_number = $number;
		$this->login->save();
		$this->getAnswer();
	}

	public function updatedChoices(...$value)
	{
		$this->schoices = $value;
	}

	public function updatedRelation($value)
	{
		$this->skipRender();
		$this->srelation = $value;
	}

	public function stopUjian()
	{
		$this->dialog()->confirm([
			'title' => 'Anda telah mengerjakan <b>' . $this->login->tests()
				->where('peserta_id', $this->login->peserta->id)
				->where(function ($q) {
					$q->whereNotNull('correct')->orWhereNotNull('relation')->orWhereNotNull('answer');
				})->count() . '</b> dari <b>' . $this->login->jadwal->soal_count . '</b> soal<br>Yakin Ingin Menyelesaikan Ujian?',
			'acceptLabel' => 'Ya, Saya Yakin',
			'rejectLabel' => 'Tidak, Lanjutkan Kerja Soal',
			'method' => 'stop'
		]);
	}

	public function render()
	{
		$this->getSoal();
		$this->getAnswer();
		return view('livewire.peserta.ujian');
	}
}
