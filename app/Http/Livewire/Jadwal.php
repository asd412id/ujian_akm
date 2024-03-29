<?php

namespace App\Http\Livewire;

use App\Models\ItemSoal;
use App\Models\Jadwal as ModelsJadwal;
use App\Models\Mapel;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use WireUi\Traits\Actions;

class Jadwal extends Component
{

	use Actions;
	use WithPagination;

	public $limit = 10;
	public $search = null;
	public $modal = false;
	public $showSoal = false;
	public $modalTitle = 'Jadwal Baru';
	protected $data = [];
	public $sid;
	public $ID;
	public $IDS;
	public $jlogin = 0;
	public $name;
	public $desc;
	public $start;
	public $end;
	public $duration;
	public $soals = [];
	public $ruangs = [];
	public $soal_count;
	public $shuffle = false;
	public $show_score = false;
	public $active = false;
	public $listSoal = ['value' => null, 'label' => 'Tidak ada data'];
	public $listRuang = ['value' => null, 'label' => 'Tidak ada data'];
	public $dataattrlist = '';
	public $attrplaceholder = 'Pilih Ruang';
	public $select_ruang;
	public $select_soal;
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function render()
	{
		$this->data = auth()->user()->sekolah->jadwals()->when($this->search, function ($q, $r) {
			$q->where('name', 'like', "%$r%")
				->orWhereHas('pesertas', function ($q) use ($r) {
					$q->where('name', 'like', "%$r%")
						->orWhere('uid', 'like', "%$r%")
						->orWhere('ruang', 'like', "%$r%");
				})
				->orWhereHas('soals', function ($q) use ($r) {
					$q->where('name', 'like', "%$r%");
				});
		})
			->when($this->dataattrlist, function ($q, $r) {
				$q->whereHas('pesertas', function ($q) use ($r) {
					$q->where('ruang', $r);
				});
			})
			->when(auth()->user()->role != 0, function ($q) {
				$q->whereHas('soals', function ($q) {
					$q->whereIn('mapel_id', auth()->user()->mapels->pluck('id')->toArray());
				});
			})
			->orderBy('active', 'desc')
			->orderBy('created_at', 'desc')
			->orderBy('start', 'asc')
			->orderBy('name', 'asc')
			->paginate($this->limit);

		$dta = $this->data;
		$this->IDS = $dta->pluck('id')->toArray();
		$attrlists = auth()->user()->sekolah->pesertas()
			->whereHas('jadwals')
			->select('ruang as label', 'ruang as value')
			->distinct('ruang')
			->get()
			->toArray();

		return view('livewire.jadwal', ['data' => $this->data, 'attrlists' => $attrlists]);
	}

	public function updatingSelectRuang($value)
	{
		$this->listRuang = auth()->user()->sekolah->pesertas()
			->where('ruang', 'like', "%$value%")
			->orWhereIn('ruang', $this->ruangs)
			->select('ruang as label', 'ruang as value')
			->distinct('ruang')
			->get()
			->toArray();
	}

	public function updatingSelectSoal($value)
	{
		$this->listSoal = auth()->user()->sekolah->soals()
			->where('name', 'like', "%$value%")
			->orWhereIn('id', $this->soals)
			->orWhereHas('mapel', function ($q) use ($value) {
				$q->where('name', 'like', "%$value%");
			})
			->select('name as label', 'id as value')
			->get()
			->toArray();
	}

	public function updatedSoals($value)
	{
		$sc = 0;
		$jadwals = auth()->user()->sekolah->soals()
			->whereIn('id', $value)->withCount('item_soals')->get();
		if (count($jadwals)) {
			foreach ($jadwals as $key => $s) {
				$sc += $s->item_soals_count;
			}
		}
		$this->soal_count = $sc;
	}

	public function create()
	{
		$this->reset();
		$this->resetValidation();
		$this->duration = 60;
		$this->start = (string) now()->addMinutes(10);
		$this->end = (string) now()->addMinutes($this->duration);
		$this->listRuang = auth()->user()->sekolah->pesertas()
			->select('ruang as label', 'ruang as value')
			->distinct('ruang')
			->limit(10)
			->get()
			->toArray();

		$this->listSoal = auth()->user()->sekolah->soals()
			->select('name as label', 'id as value')
			->limit(10)
			->get()
			->toArray();

		$this->modal = true;
	}

	public function store()
	{
		$this->resetValidation();
		$this->validate([
			'name' => 'required',
			'start' => 'required',
			'end' => 'required',
			'duration' => 'required|numeric|min:1',
			'ruangs' => 'required|array',
			'soals' => 'required|array',
			'soal_count' => 'required|numeric|min:1',
		], [
			'name.required' => 'Nama lengkap tidak boleh kosong',
			'start.required' => 'Waktu mulai tidak boleh kosong',
			'end.required' => 'Waktu selesai tidak boleh kosong',
			'duration.required' => 'Durasi ujian tidak boleh kosong',
			'duration.numeric' => 'Durasi ujian harus berupa angka',
			'duration.min' => 'Durasi ujian minimal 1 menit',
			'ruangs.required' => 'Ruang tidak boleh kosong',
			'ruangs.array' => 'Format Ruang tidak benar',
			'soals.required' => 'Soal ujian tidak boleh kosong',
			'soals.array' => 'Format soal ujian tidak boleh kosong',
			'soal_count.required' => 'Jumlah soal tidak boleh kosong',
			'soal_count.numeric' => 'Format jumlah soal harus berupa angka',
			'soal_count.min' => 'Jumlah soal minimal 1',
		]);

		$start = Carbon::parse($this->start);
		$end = Carbon::parse($this->end);

		if ($start->greaterThanOrEqualTo($end)) {
			return $this->addError('end', 'Waktu selesai ujian harus lebih besar dari waktu mulai ujian');
		}

		$update = $this->ID ? ModelsJadwal::find($this->ID) : new ModelsJadwal();
		$update->name = $this->name;
		$update->sekolah_id = auth()->user()->sekolah_id;
		$update->start = Carbon::parse($this->start)->startOfMinute();
		$update->end = Carbon::parse($this->end)->startOfMinute();
		$update->duration = $this->duration;
		if (!boolval($this->jlogin)) {
			$update->soal_count = $this->soal_count;
			$update->shuffle = boolval($this->shuffle);
		}
		$update->active = boolval($this->active);
		$update->show_score = boolval($this->show_score);
		$update->opt = [
			'desc' => $this->desc
		];
		if ($update->save()) {
			if (!boolval($this->jlogin)) {
				$pids = auth()->user()->sekolah->pesertas()
					->whereIn('ruang', $this->ruangs)->select('id')
					->get()
					->pluck('id')
					->toArray();
				$update->pesertas()->sync($pids);
				$update->soals()->sync($this->soals);
			}
			$this->reset('modal');
			$this->resetValidation();
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function edit(ModelsJadwal $jadwal)
	{
		if ($jadwal->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->resetValidation();

		$this->ID = $jadwal->id;
		$this->jlogin = $jadwal->logins()->count() || $jadwal->active;
		$this->name = $jadwal->name;
		$this->start = strval($jadwal->start);
		$this->end = strval($jadwal->end);
		$this->duration = intval($jadwal->duration);
		$this->soal_count = intval($jadwal->soal_count);
		$this->shuffle = boolval($jadwal->shuffle);
		$this->show_score = boolval($jadwal->show_score);
		$this->active = boolval($jadwal->active);
		$this->desc = isset($jadwal->opt['desc']) ? $jadwal->opt['desc'] : null;
		$this->ruangs = array_values(array_unique($jadwal->pesertas()->select('ruang')->get()->pluck('ruang')->toArray()));
		$this->soals = array_values($jadwal->soals()->select('id')->get()->pluck('id')->toArray());

		$this->listRuang = auth()->user()->sekolah->pesertas()
			->select('ruang as label', 'ruang as value')
			->distinct('ruang')
			->get()
			->toArray();

		$this->listSoal = auth()->user()->sekolah->soals()
			->select('name as label', 'id as value')
			->get()
			->toArray();

		$this->modalTitle = 'Ubah Jadwal (' . $jadwal->name . ')';
		$this->modal = true;
	}

	public function delete(ModelsJadwal $jadwal)
	{
		if ($jadwal->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $jadwal->id;
		$this->dialog()->confirm([
			'title' => 'Yakin ingin menghapus?',
			'description' => $jadwal->name,
			'acceptLabel' => 'Hapus',
			'rejectLabel' => 'Batal',
			'method' => 'destroy',
		]);
	}

	public function activate(ModelsJadwal $jadwal)
	{
		if ($jadwal->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $jadwal->id;
		$this->dialog()->confirm([
			'title' => ($jadwal->active ? 'Non-Aktifkan Jadwal' : 'Aktifkan Jadwal') . ' ' . $jadwal->name . '?',
			'description' => $jadwal->active ? 'Semua peserta akan diset selesai ujian' : 'Peserta akan dapat mengikuti ujian',
			'acceptLabel' => 'Ya',
			'rejectLabel' => 'Tidak',
			'method' => 'activated',
		]);
	}

	public function activated()
	{
		$jadwal = auth()->user()->sekolah->jadwals()->find($this->ID);
		if ($jadwal) {
			$jadwal->active = !$jadwal->active;
			if ($jadwal->save()) {
				if (!$jadwal->active) {
					foreach ($jadwal->logins as $l) {
						if (is_null($l->end)) {
							$l->end = now()->lessThanOrEqualTo($l->created_at->addMinutes($l->jadwal->duration)) ? now() : $l->created_at->addMinutes($l->jadwal->duration);
							$l->created_at = now()->lessThanOrEqualTo($l->created_at->addMinutes($l->jadwal->duration)) ? now() : $l->created_at->addMinutes($l->jadwal->duration);
							$l->save();
						}
					}
				}
				$this->notification()->success('Jadwal berhasil di ' . ($jadwal->active ? 'Aktifkan' : 'Non-Aktifkan'));
			} else {
				$this->notification()->error('Jadwal gagal di ' . ($jadwal->active ? 'Aktifkan' : 'Non-Aktifkan'));
			}
		} else {
			$this->notification()->error('Jadwal gagal di ' . ($jadwal->active ? 'Aktifkan' : 'Non-Aktifkan'));
		}
	}

	public function resetUjian(ModelsJadwal $jadwal)
	{
		if ($jadwal->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $jadwal->id;
		$this->dialog()->confirm([
			'title' => 'Ujian peserta pada jadwal ini akan terhapus!<br>Reset Ujian dan Nilai Peserta?',
			'description' => 'Ujian ini telah diikuti oleh ' . $jadwal->logins()->count() . ' Peserta',
			'acceptLabel' => 'Ya',
			'rejectLabel' => 'Tidak',
			'method' => 'doResetUjian',
		]);
	}

	public function doResetUjian()
	{
		$jadwal = auth()->user()->sekolah->jadwals()->find($this->ID);
		if ($jadwal) {
			$jadwal->logins()->delete();
			$jadwal->tests()->delete();
			return $this->notification()->success('Ujian berhasil di reset');
		}
		return $this->notification()->error('Ujian gagal di reset');
	}

	public function destroy()
	{
		$data = auth()->user()->sekolah->jadwals()->find($this->ID);
		if ($data) {
			$data->delete();
			return $this->notification()->success('Data berhasil dihapus');
		} else {
			return $this->notification()->error('Data tidak tersedia');
		}
	}

	public function destroyAll()
	{
		if (count($this->IDS)) {
			return $this->dialog()->confirm([
				'title' => 'Yakin ingin menghapus ' . count($this->IDS) . ' data?',
				'acceptLabel' => 'Ya, Hapus',
				'rejectLabel' => 'Batal',
				'method' => 'doDestroyAll',
			]);
		}
		return $this->notification()->error('Data tidak tersedia');
	}

	public function doDestroyAll()
	{
		$count = count($this->IDS);
		$jadwals = auth()->user()->sekolah->jadwals()
			->whereIn('id', $this->IDS)->get();
		if (count($jadwals)) {
			foreach ($jadwals as $s) {
				$s->delete();
			}
			return $this->notification()->success('Berhasil menghapus ' . $count . ' data');
		}
		return $this->notification()->error('Data gagal dihapus');
	}

	public function daftarHadir(ModelsJadwal $jadwal)
	{
		if ($jadwal->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}

		$mapels = Mapel::whereHas('soals.jadwals', function ($q) use ($jadwal) {
			$q->where('id', $jadwal->id);
		})->get()->pluck('name')->toArray();

		$types = ItemSoal::whereHas('soal.jadwals', function ($q) use ($jadwal) {
			$q->where('jadwal_id', $jadwal->id);
		})
			->select('type')->distinct('type')->get()->pluck('type')->toArray();

		$pdf = Pdf::loadView('jadwal.daftar-hadir', [
			'jadwal' => $jadwal,
			'mapels' => $mapels,
			'types' => $types,
		]);

		return response()->streamDownload(function () use ($pdf, $jadwal) {
			$pdf->stream('Daftar Hadir ' . str_replace(['/', '\\'], '-', $jadwal->name) . '.pdf');
		}, 'Daftar Hadir ' . str_replace(['/', '\\'], '-', $jadwal->name) . '.pdf');
	}

	public function daftarNilai(ModelsJadwal $jadwal)
	{
		if ($jadwal->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}

		$mapels = Mapel::whereHas('soals.jadwals', function ($q) use ($jadwal) {
			$q->where('id', $jadwal->id);
		})->get()->pluck('name')->toArray();

		$types = ItemSoal::whereHas('soal.jadwals', function ($q) use ($jadwal) {
			$q->where('jadwal_id', $jadwal->id);
		})
			->select('type')->distinct('type')->get()->pluck('type')->toArray();

		$pdf = Pdf::loadView('jadwal.daftar-nilai', [
			'jadwal' => $jadwal,
			'mapels' => $mapels,
			'types' => $types,
		]);

		return response()->streamDownload(function () use ($pdf, $jadwal) {
			$pdf->stream('Daftar Nilai ' . str_replace(['/', '\\'], '-', $jadwal->name) . '.pdf');
		}, 'Daftar Nilai ' . str_replace(['/', '\\'], '-', $jadwal->name) . '.pdf');
	}
}
