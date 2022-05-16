<?php

namespace App\Http\Livewire;

use App\Models\Mapel as ModelsMapel;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class Mapel extends Component
{

	use Actions;
	use WithPagination;

	public $limit = 10;
	public $search = null;
	public $modal = false;
	public $modalTitle = 'Data Baru';
	protected $data = [];
	public $ID;
	public $name;
	public $penilai = [];
	public $listPenilai;
	public $select_search;
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function render()
	{
		$this->data = auth()->user()->sekolah->mapels()->when($this->search, function ($q, $r) {
			$q->where('name', 'like', "%$r%")
				->orWhereHas('users', function ($q) use ($r) {
					$q->where('name', 'like', "%$r%")
						->orWhere('email', 'like', "%$r%");
				});
		})
			->paginate($this->limit);
		return view('livewire.mapel', ['data' => $this->data]);
	}

	public function updatingSelectSearch($value)
	{
		$list = auth()->user()->sekolah->users()
			->where('role', 1)
			->where(function ($q) use ($value) {
				$q->where('name', 'like', "%$value%")
					->orWhere('email', 'like', "%$value%");
			})
			->select('id', 'name')
			->get()
			->toArray();

		if (count($list)) {
			return $this->listPenilai = $list;
		}
		return $this->listPenilai = [['id' => null, 'name' => 'Data tidak ditemukan']];
	}

	public function create()
	{
		$this->resetValidation();
		$this->reset(['name', 'penilai']);
		$list = auth()->user()->sekolah->users()
			->where('role', 1)
			->select('id', 'name')
			->limit(10)
			->get()
			->toArray();

		if (count($list)) {
			$this->listPenilai = $list;
		} else {
			$this->listPenilai = [['id' => null, 'name' => 'Data tidak tersedia']];
		}

		$this->modal = true;
	}

	public function store()
	{
		$this->validate([
			'name' => 'required'
		], [
			'name.required' => 'Nama mata pelajaran tidak boleh kosong'
		]);

		$update = $this->ID ? ModelsMapel::find($this->ID) : new ModelsMapel();
		$update->name = $this->name;
		$update->sekolah_id = auth()->user()->sekolah_id;
		if ($update->save()) {
			$update->users()->sync($this->penilai);
			$this->reset();
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function edit(ModelsMapel $mapel)
	{
		if ($mapel->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->resetValidation();
		$this->penilai = $mapel->users()
			->select('id')
			->get()
			->pluck('id')
			->toArray();
		$list = auth()->user()->sekolah->users()
			->where('role', 1)
			->whereIn('id', $this->penilai)
			->orWhereNotIn('id', $this->penilai)
			->select('id', 'name')
			->get()
			->toArray();

		if (count($list)) {
			$this->listPenilai = $list;
		} else {
			$this->listPenilai = [['id' => null, 'name' => 'Data tidak tersedia']];
		}

		$this->ID = $mapel->id;
		$this->name = $mapel->name;

		$this->modalTitle = 'Ubah Data (' . $mapel->name . ')';
		$this->modal = true;
	}

	public function delete(ModelsMapel $mapel)
	{
		if ($mapel->sekolah_id != auth()->user()->sekolah_id) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $mapel->id;
		$this->dialog()->confirm([
			'title' => 'Yakin ingin menghapus?',
			'description' => $mapel->name,
			'acceptLabel' => 'Hapus',
			'rejectLabel' => 'Batal',
			'method' => 'destroy',
		]);
	}

	public function destroy()
	{
		$data = auth()->user()->sekolah->mapels()->find($this->ID);
		$this->reset();
		$this->resetValidation();
		if ($data) {
			$data->users()->detach();
			$data->delete();
			return $this->notification()->success('Data berhasil dihapus');
		} else {
			return $this->notification()->error('Data tidak tersedia');
		}
	}
}
