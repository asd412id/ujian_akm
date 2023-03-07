<?php

namespace App\Http\Livewire;

use App\Models\Peserta as ModelPeserta;
use Livewire\Component;
use Livewire\WithPagination;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
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
	public $jk = 'L';
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
			->orderBy('uid', 'asc')
			->orderBy('name', 'asc')
			->orderBy('created_at', 'asc')
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
		$update->jk = $this->jk;
		$update->sekolah_id = auth()->user()->sekolah_id;
		$update->ruang = $this->ruang;
		if (!$this->ID) {
			$update->token = sha1(time());
			if (!$this->password) {
				$update->password = bcrypt($this->uid);
				$update->password_string = $this->uid;
			}else{
				$update->password = bcrypt($this->password);
				$update->password_string = $this->password;
			}
		}
		if ($this->ID && $this->password) {
			$update->password = bcrypt($this->password);
			$update->password_string = $this->password;
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
		$this->jk = $peserta->jk;
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
		$delete = auth()->user()->sekolah->pesertas()
			->whereIn('id', $this->IDS)
			->whereDoesntHave('tests', function ($q) {
				$q->whereIn('peserta_id', $this->IDS);
			});
		$count = $delete->count();
		if ($delete->delete()) {
			$this->resetValidation();
			$this->resetExcept('listRuang');
			return $this->notification()->success('Berhasil menghapus ' . $count . ' data');
		}
		return $this->notification()->error('Data gagal dihapus');
	}

	public function resetLogin(ModelPeserta $peserta)
	{
		if ($peserta->sekolah_id != auth()->user()->sekolah_id) {
			$this->default();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->dialog()->confirm([
			'title' => 'Yakin ingin reset login ' . $peserta->name . ' ?',
			'description' => 'Peserta akan ter-logout dari ujian',
			'acceptLabel' => 'Ya, Reset',
			'rejectLabel' => 'Tidak',
			'method' => 'doResetLogin',
			'params' => $peserta->id
		]);
	}

	public function doResetLogin(ModelPeserta $peserta)
	{
		if ($peserta->sekolah_id != auth()->user()->sekolah_id) {
			$this->default();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$peserta->is_login = false;
		$peserta->save();
		return $this->notification()->success('Login ' . $peserta->name . ' berhasil direset');
	}

	public function printCard()
	{
		$pesertas = ModelPeserta::whereIn('id', $this->IDS)
			->orderBy('uid', 'asc')
			->orderBy('name', 'asc')
			->orderBy('created_at', 'asc')
			->get();
		$ruangs = array_unique($pesertas->pluck('ruang')->toArray());

		if (!count($pesertas)) {
			return $this->notification()->error('Peserta tidak ditemukan');
		}

		$pdf = Pdf::loadView('peserta.card', [
			'pesertas' => $pesertas,
			'ruangs' => $ruangs,
		]);

		return response()->streamDownload(function () use ($pdf, $ruangs) {
			$pdf->stream('Kartu Peserta (' . implode('-', $ruangs) . ').pdf');
		}, 'Kartu Peserta (' . implode('-', $ruangs) . ').pdf');
	}
}
