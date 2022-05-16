<?php

namespace App\Http\Livewire;

use App\Models\ItemSoal;
use App\Models\Soal as ModelsSoal;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class Soal extends Component
{

	use Actions;
	use WithPagination;

	public $limit = 10;
	public $search = null;
	public $modal = false;
	public $showSoal = false;
	public $modalTitle = 'Data Baru';
	protected $data = [];
	public $sid;
	public $ID;
	public $IDS;
	public $name;
	public $mapel;
	public $item_soals;
	public $listMapel;
	public $soal;
	public $dataattrlist = '';
	public $attrplaceholder = 'Pilih Mapel';
	public $select_search;
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function render()
	{
		$this->data = auth()->user()->sekolah->soals()->when($this->search, function ($q, $r) {
			$q->where(function ($q) use ($r) {
				$q->where('name', 'like', "%$r%")
					->orWhereHas('item_soals', function ($q) use ($r) {
						$q->where('text', 'like', "%$r%")
							->orWhere('options', 'like', "%$r%")
							->orWhere('answer', 'like', "%$r%");
					})
					->orWhereHas('mapels', function ($q) use ($r) {
						$q->where('name', 'like', "%$r%");
					});
			});
		})
			->when($this->dataattrlist, function ($q, $r) {
				$q->where('mapel_id', $r);
			})
			->paginate($this->limit);

		$dta = $this->data;
		$this->IDS = $dta->pluck('id')->toArray();
		$attrlists = auth()->user()->sekolah->mapels()
			->whereHas('soals')
			->select('name as label', 'id as value')
			->get()
			->toArray();

		return view('livewire.soal', ['data' => $this->data, 'attrlists' => $attrlists]);
	}

	public function boot()
	{
		$list = auth()->user()->sekolah->mapels()
			->select('id', 'name')
			->limit(10)
			->get()
			->toArray();

		if (count($list)) {
			$this->listMapel = $list;
		} else {
			$this->listMapel = [['id' => null, 'name' => 'Data tidak tersedia']];
		}
	}

	public function updatingSelectSearch($value)
	{
		$list = auth()->user()->sekolah->mapels()
			->where('name', 'like', "%$value%")
			->select('id', 'name')
			->get()
			->toArray();

		if (count($list)) {
			return $this->listMapel = $list;
		}
		return $this->listMapel = [['id' => null, 'name' => 'Data tidak ditemukan']];
	}

	public function create()
	{
		$this->resetValidation();
		$this->reset([
			'ID',
			'name',
			'mapel',
			'item_soals',
		]);
		$list = auth()->user()->sekolah->mapels()
			->select('id', 'name')
			->limit(10)
			->get()
			->toArray();

		if (count($list)) {
			$this->listMapel = $list;
		} else {
			$this->listMapel = [['id' => null, 'name' => 'Data tidak tersedia']];
		}

		$this->modal = true;
	}

	public function store()
	{
		$this->validate([
			'name' => 'required',
			'mapel' => 'required',
			'item_soals' => 'required',
		], [
			'name.required' => 'Nama lengkap tidak boleh kosong',
			'mapel.required' => 'Mata pelajaran tidak boleh kosong',
			'item_soals.required' => 'Butir soal tidak boleh kosong',
		]);

		$update = $this->ID ? ModelsSoal::find($this->ID) : new ModelsSoal();
		$update->name = $this->name;
		$update->sekolah_id = auth()->user()->sekolah_id;
		$update->mapel_id = $this->mapel;
		$update->soal_raw = $this->item_soals;
		if ($update->save()) {
			$item_soals = getJson(parseSoal(cleanCodeTags($this->item_soals)));
			if (count($item_soals)) {
				$update->item_soals()->delete();
				foreach ($item_soals as $v) {
					if (isValidJSON($v)) {
						$item = json_decode($v);
						if (!in_array(strtolower($item->type), ['pg', 'pgk', 'jd', 'is', 'u'])) {
							continue;
						}
						$isoal = new ItemSoal();
						$isoal->soal_id = $update->id;
						$isoal->type = strtolower($item->type);
						$isoal->num = $item->num;
						$isoal->text = cleanCodeTags(nl2br(trim($item->text)));
						$isoal->score = $item->score;
						$isoal->options = $item->options;
						$isoal->shuffle = $item->shuffle;
						$isoal->corrects = $item->corrects;
						$isoal->relations = $item->relations;
						$isoal->answer = $item->answer;
						$isoal->labels = $item->labels;
						$isoal->save();
					}
				}
			}
			$this->resetExcept('listMapel');
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function edit(ModelsSoal $soal)
	{
		if ($soal->sekolah_id != auth()->user()->sekolah_id) {
			$this->resetExcept('listMapel');
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->reset([
			'ID',
			'name',
			'mapel',
			'item_soals',
		]);
		$this->resetValidation();
		$list = auth()->user()->sekolah->mapels()
			->whereIn('id', [$soal->mapel_id])
			->orWhereNotIn('id', [$soal->mapel_id])
			->select('id', 'name')
			->get()
			->toArray();

		if (count($list)) {
			$this->listMapel = $list;
		} else {
			$this->listMapel = [['id' => null, 'name' => 'Data tidak tersedia']];
		}

		$this->ID = $soal->id;
		$this->name = $soal->name;
		$this->mapel = $soal->mapel_id;
		$this->item_soals = $soal->soal_raw;

		$this->modalTitle = 'Ubah Data (' . $soal->name . ')';
		$this->modal = true;
	}

	public function delete(ModelsSoal $soal)
	{
		if ($soal->sekolah_id != auth()->user()->sekolah_id) {
			$this->resetExcept('listMapel');
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $soal->id;
		$this->dialog()->confirm([
			'title' => 'Yakin ingin menghapus?',
			'description' => $soal->name,
			'acceptLabel' => 'Hapus',
			'rejectLabel' => 'Batal',
			'method' => 'destroy',
		]);
	}

	public function show(ModelsSoal $soal)
	{
		if ($soal->sekolah_id != auth()->user()->sekolah_id) {
			$this->resetExcept('listMapel');
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}

		$this->sid = time();
		$this->ID = $soal->id;
		$this->soal = $soal;

		$this->modalTitle = 'Daftar Soal (' . $soal->name . ')';
		$this->showSoal = true;
	}

	public function destroy()
	{
		$data = auth()->user()->sekolah->soals()->find($this->ID);
		$this->resetExcept('listMapel');
		$this->resetValidation();
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
		$soals = auth()->user()->sekolah->soals()
			->whereIn('id', $this->IDS)->get();
		if (count($soals)) {
			foreach ($soals as $key => $s) {
				$s->delete();
			}
			$this->resetValidation();
			$this->resetExcept('listMapel');
			return $this->notification()->success('Berhasil menghapus ' . $count . ' data');
		}
		return $this->notification()->error('Data gagal dihapus');
	}
}
