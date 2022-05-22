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
		$this->timer = $this->login->start->addMinutes($this->login->jadwal->duration)->getPreciseTimestamp(3);
	}

	public function checkLogin()
	{
		$this->login = $this->user->logins()
			->whereNotNull('start')
			->whereNull('end')
			->orderBy('id', 'asc')
			->first();

		if (!$this->login) {
			return redirect()->route('ujian.index')->with('msg', 'Ujian Selesai');
		} else {
			if (now()->greaterThan($this->login->start->addMinutes($this->login->jadwal->duration))) {
				return $this->stop();
			}
			$this->timer = $this->login->start->addMinutes($this->login->jadwal->duration)->getPreciseTimestamp(3);
		}
	}

	public function checkTimer()
	{
		$this->skipRender();
		$this->checkLogin();
	}

	public function stop()
	{
		$this->login->end = now();
		$this->login->save();
		$this->reset('login');
		return redirect()->route('ujian.index')->with('msg', 'Ujian Selesai');
	}

	public function getSoal()
	{
		$this->soals = $this->login->soals();
	}

	public function saveAnswer()
	{
		$this->checkLogin();
		$opts = null;
		$this->soal = PesertaTest::where('item_soal_id', $this->soals[$this->login->current_number]->id)->first() ?? new PesertaTest();
		$this->soal->jadwal_id = $this->login->jadwal_id;
		$this->soal->peserta_id = $this->user->id;
		$this->soal->soal_id = $this->soals[$this->login->current_number]->soal_id;
		$this->soal->login_id = $this->login->id;
		$this->soal->item_soal_id = $this->soals[$this->login->current_number]->id;
		$this->soal->type = $this->soals[$this->login->current_number]->type;
		$this->soal->text = $this->soals[$this->login->current_number]->text;
		$this->soal->score = $this->soals[$this->login->current_number]->score;
		$this->soal->label = $this->soals[$this->login->current_number]->labels;

		if (!PesertaTest::where('item_soal_id', $this->soals[$this->login->current_number]->id)->first()) {
			if ($this->soals[$this->login->current_number]->shuffle) {
				$keys = array_keys($this->soals[$this->login->current_number]->options);
				shuffle($keys);
				foreach ($keys as $k) {
					$opts[$k] = $this->soals[$this->login->current_number]->options[$k];
				}
			} else {
				$opts = $this->soals[$this->login->current_number]->options;
			}
		}

		if ($opts) {
			$this->soal->option = $opts;
		}

		switch ($this->soal->type) {
			case 'pg':
				if (count($this->schoices)) {
					$correct = [];
					foreach ($this->soal->option as $key => $o) {
						$correct[$key] = false;
						if ($this->schoices[0] == $key) {
							$correct[$key] = true;
						}
					}
					$this->soal->correct = $correct;
				} else {
					$this->soal->correct = [];
				}
				break;
			case 'pgk':
				if (count($this->schoices)) {
					$correct = [];
					foreach ($this->soal->option as $key => $o) {
						$correct[$key] = false;
						if (in_array($key, $this->schoices[0])) {
							$correct[$key] = true;
						}
					}
					$this->soal->correct = $correct;
				} else {
					$this->soal->correct = [];
				}
				break;
			case 'jd':
				if (is_array($this->srelation) && count($this->srelation)) {
					$r = [];
					foreach ($this->srelation as $key => $v) {
						if ($v != null) {
							$r[str_replace(['start', '_' . $this->soal->id], '', $key)] = [str_replace(['end', '_' . $this->soal->id], '', $v)];
						}
					}
					$this->soal->relation = $r;
				} else {
					$this->soal->relation = [];
				}
				break;
			case 'bs':
				$r = [];
				foreach ($this->choices as $key => $v) {
					$r[$key] = boolval($v);
				}
				$this->soal->correct = $r;
				break;
			case 'is':
				$this->soal->answer = $this->answer;
				break;
			case 'u':
				$this->soal->answer = $this->answer;
				break;
		}

		$this->soal->save();
	}

	public function getAnswer()
	{
		$this->checkLogin();
		$opts = null;
		$this->soal = PesertaTest::where('item_soal_id', $this->soals[$this->login->current_number]->id)->first() ?? new PesertaTest();

		$this->soal->jadwal_id = $this->login->jadwal_id;
		$this->soal->peserta_id = $this->user->id;
		$this->soal->soal_id = $this->soals[$this->login->current_number]->soal_id;
		$this->soal->login_id = $this->login->id;
		$this->soal->item_soal_id = $this->soals[$this->login->current_number]->id;
		$this->soal->type = $this->soals[$this->login->current_number]->type;
		$this->soal->text = $this->soals[$this->login->current_number]->text;
		$this->soal->score = $this->soals[$this->login->current_number]->score;
		$this->soal->label = $this->soals[$this->login->current_number]->labels;

		$this->reset(['choices', 'relation', 'answer', 'srelation']);

		if (!PesertaTest::where('item_soal_id', $this->soals[$this->login->current_number]->id)->first()) {
			if ($this->soals[$this->login->current_number]->shuffle) {
				$keys = array_keys($this->soals[$this->login->current_number]->options);
				shuffle($keys);
				foreach ($keys as $k) {
					$opts[$k] = $this->soals[$this->login->current_number]->options[$k];
				}
			} else {
				$opts = $this->soals[$this->login->current_number]->options;
			}
		}

		if ($opts) {
			$this->soal->option = $opts;
		}

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

	public function updatingChoices(...$value)
	{
		$this->schoices = $value;
	}

	public function updatingRelation($value)
	{
		$this->srelation = $value;
	}

	public function stopUjian()
	{
		$this->dialog()->confirm([
			'title' => 'Yakin Ingin Menyelesaikan Ujian?',
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
