<?php

namespace App\Http\Livewire\Peserta;

use Livewire\Component;

class Ujian extends Component
{
	public $user;
	public $login = null;

	public function checkTimer()
	{
		$this->login = $this->user->logins()
			->whereNotNull('start')
			->whereNull('end')
			->orderBy('id', 'asc')
			->first();

		if ($this->login && now()->greaterThan($this->login->start->addMinutes($this->login->jadwal->duration))) {
			$this->login->end = now();
			$this->login->save();
			$this->reset('login');
			return redirect()->route('ujian.index')->with('msg', 'Ujian telah berakhir!');
		}
	}

	public function render()
	{
		$this->checkTimer();
		return view('livewire.peserta.ujian');
	}
}
