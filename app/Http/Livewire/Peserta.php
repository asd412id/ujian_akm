<?php

namespace App\Http\Livewire;

use App\Models\Peserta as ModelPeserta;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class Peserta extends Component
{

	use Actions;
	use WithPagination;

	public $limit = 10;
	public $search = null;
	public $modal = false;
	public $modalTitle = 'Data Baru';
	protected $data;
	public $ID;
	public $IDS;
	public $uid;
	public $name;
	public $password;
	public $repassword;
	public $ruang = '';
	public $dataattrlist = '';
	public $attrplaceholder = 'Pilih Ruang';
	public $listRuang = [];
	public $select_search = null;
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function render()
	{
		$this->data = auth()->user()->sekolah->pesertas()->when($this->search, function ($q, $r) {
			$q->where('name', 'like', "%$r%")
				->orWhere('ruang', 'like', "%$r%");
		})
			->when($this->dataattrlist, function ($q, $r) {
				$q->where('ruang', $r);
			})
			->paginate($this->limit);

		$dta = $this->data;
		$this->IDS = $dta->pluck('id')->toArray();

		$attrlists = auth()->user()->sekolah->pesertas()
			->where('ruang', '!=', '')
			->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray();

		return view('livewire.peserta', ['data' => $this->data, 'attrlists' => $attrlists]);
	}

	public function boot()
	{
		$this->loadRuang();
	}

	public function loadRuang()
	{
		$list = auth()->user()->sekolah->pesertas()
			->select('ruang as sid', 'ruang as text')
			->where('ruang', '!=', '')
			->distinct('ruang')
			->get()
			->toArray();

		if (count($list)) {
			$this->listRuang = $list;
		} else {
			$this->listRuang = [
				[
					'sid' => null,
					'text' => 'Ketik nama ruang untuk menambahkan',
				]
			];
		}
	}

	public function updatingSelectSearch($value)
	{
		$list = auth()->user()->sekolah->pesertas()
			->where('ruang', 'like', "%$value%")
			->select('ruang as sid', 'ruang as text')
			->where('ruang', '!=', '')
			->distinct('ruang')
			->get()
			->toArray();

		if (count($list)) {
			$this->listRuang = $list;
		} else {
			$this->reset('listRuang');
			if ($this->ruang) {
				$this->listRuang[0] = [
					'sid' => $this->ruang,
					'text' => $this->ruang
				];
			} else {
				if (isset($this->listRuang[0])) {
					unset($this->listRuang[0]);
				}
			}
			$this->listRuang[] = [
				'sid' => $value ?? null,
				'text' => $value ?? 'Ketik nama ruang untuk menambahkan',
			];
		}
	}

	public function default()
	{
		$this->resetValidation();
		$this->reset([
			'uid',
			'name',
			'password',
			'repassword',
			'ruang',
			'modal',
			'modalTitle',
		]);
		$this->loadRuang();
	}

	public function create()
	{
		$this->default();
		$this->modal = true;
	}

	public function store()
	{
		if (!$this->ID) {
			$this->validate([
				'name' => 'required',
				'uid' => 'required|unique:pesertas,uid',
				'repassword' => 'same:password',
			], [
				'name.required' => 'Nama lengkap tidak boleh kosong',
				'uid.required' => 'ID Peserta tidak boleh kosong',
				'uid.unique' => 'ID Peserta telah digunakan',
				'repassword.same' => 'Perulangan password tidak benar',
			]);
		} else {
			$this->validate([
				'name' => 'required',
				'uid' => 'required|unique:pesertas,uid,' . $this->ID . ',id',
				'repassword' => 'same:password',
			], [
				'name.required' => 'Nama mata pelajaran tidak boleh kosong',
				'uid.required' => 'ID Peserta tidak boleh kosong',
				'uid.unique' => 'ID Peserta telah digunakan',
				'repassword.same' => 'Perulangan password tidak benar',
			]);
		}

		$update = $this->ID ? ModelPeserta::find($this->ID) : new ModelPeserta();
		$update->name = $this->name;
		$update->uid = $this->uid;
		$update->sekolah_id = auth()->user()->sekolah_id;
		$update->ruang = $this->ruang;
		if (!$this->ID) {
			$update->token = sha1(time());
			if (!$this->password) {
				$update->password = bcrypt($this->uid);
			}
		}
		if ($this->ID && $this->password) {
			$update->password = bcrypt($this->password);
		}
		if ($update->save()) {
			$this->default();
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function edit(ModelPeserta $peserta)
	{
		$this->default();
		if ($peserta->sekolah_id != auth()->user()->sekolah_id) {
			return $this->notification()->error('Data tidak tersedia!');
		}

		$this->ID = $peserta->id;
		$this->uid = $peserta->uid;
		$this->name = $peserta->name;
		$this->ruang = $peserta->ruang;

		$this->modalTitle = 'Ubah Data (' . $peserta->name . ')';
		$this->modal = true;
	}

	public function delete(ModelPeserta $peserta)
	{
		if ($peserta->sekolah_id != auth()->user()->sekolah_id) {
			$this->default();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $peserta->id;
		$this->dialog()->confirm([
			'title' => 'Yakin ingin menghapus?',
			'description' => $peserta->name,
			'acceptLabel' => 'Hapus',
			'rejectLabel' => 'Batal',
			'method' => 'destroy',
		]);
	}

	public function destroy()
	{
		$data = auth()->user()->sekolah->pesertas()->find($this->ID);
		$this->default();
		$this->resetExcept('listRuang');
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
		$delete = auth()->user()->sekolah->pesertas()
			->whereIn('id', $this->IDS)->delete();
		if ($delete) {
			$this->resetValidation();
			$this->resetExcept('listRuang');
			return $this->notification()->success('Berhasil menghapus ' . $count . ' data');
		}
		return $this->notification()->error('Data gagal dihapus');
	}
}
