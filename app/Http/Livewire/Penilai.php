<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use WireUi\Traits\Actions;

class Penilai extends Component
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
	public $email;
	public $password;
	public $repassword;
	public $mapel = [];
	public $listMapel;
	public $select_search;
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function render()
	{
		$this->data = auth()->user()->sekolah->users()->when($this->search, function ($q, $r) {
			$q->where(function ($q) use ($r) {
				$q->where('name', 'like', "%$r%")
					->orWhere('email', 'like', "%$r%")
					->orWhereHas('mapels', function ($q) use ($r) {
						$q->where('name', 'like', "%$r%");
					});
			})->where('role', 1);
		})
			->where('role', 1)
			->orderBy('name', 'asc')
			->orderBy('id', 'desc')
			->paginate($this->limit);
		return view('livewire.penilai', ['data' => $this->data]);
	}

	public function updatingSelectSearch($value)
	{
		$list = auth()->user()->sekolah->mapels()
			->where('name', 'like', "%$value%")
			->orWhereIn('id', $this->mapel)
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
			'name',
			'email',
			'password',
			'repassword',
			'mapel',
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
		if (!$this->ID) {
			$this->validate([
				'name' => 'required',
				'email' => 'required|email|unique:users,email',
				'password' => 'required',
				'repassword' => 'required|same:password',
			], [
				'name.required' => 'Nama lengkap tidak boleh kosong',
				'email.required' => 'Alamat email tidak boleh kosong',
				'email.email' => 'Format alamat email tidak benar',
				'email.unique' => 'Alamat email telah digunakan',
				'password.required' => 'Password tidak boleh kosong',
				'repassword.required' => 'Perulangan password tidak boleh kosong',
				'repassword.same' => 'Perulangan password tidak benar',
			]);
		} else {
			$this->validate([
				'name' => 'required',
				'email' => 'required|email|unique:users,email,' . $this->ID . ',id',
				'repassword' => 'same:password',
			], [
				'name.required' => 'Nama mata pelajaran tidak boleh kosong',
				'email.required' => 'Alamat email tidak boleh kosong',
				'email.email' => 'Format alamat email tidak benar',
				'email.unique' => 'Alamat email telah digunakan',
				'repassword.same' => 'Perulangan password tidak benar',
			]);
		}

		$update = $this->ID ? User::find($this->ID) : new User();
		$update->name = $this->name;
		$update->email = $this->email;
		$update->sekolah_id = auth()->user()->sekolah_id;
		$update->role = 1;
		$update->email_verified_at = now();
		if (!$this->ID || ($this->ID && $this->password)) {
			$update->password = bcrypt($this->password);
		}
		if ($update->save()) {
			$update->mapels()->sync($this->mapel);
			$this->reset();
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function edit(User $user)
	{
		if ($user->sekolah_id != auth()->user()->sekolah_id || $user->role != 1) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->resetValidation();
		$this->mapel = $user->mapels()
			->select('id')
			->get()
			->pluck('id')
			->toArray();
		$list = auth()->user()->sekolah->mapels()
			->whereIn('id', $this->mapel)
			->orWhereNotIn('id', $this->mapel)
			->select('id', 'name')
			->get()
			->toArray();

		if (count($list)) {
			$this->listMapel = $list;
		} else {
			$this->listMapel = [['id' => null, 'name' => 'Data tidak tersedia']];
		}

		$this->ID = $user->id;
		$this->name = $user->name;
		$this->email = $user->email;

		$this->modalTitle = 'Ubah Data (' . $user->name . ')';
		$this->modal = true;
	}

	public function delete(User $user)
	{
		if ($user->sekolah_id != auth()->user()->sekolah_id || $user->role != 1) {
			$this->reset();
			$this->resetValidation();
			return $this->notification()->error('Data tidak tersedia!');
		}
		$this->ID = $user->id;
		$this->dialog()->confirm([
			'title' => 'Yakin ingin menghapus?',
			'description' => $user->name,
			'acceptLabel' => 'Hapus',
			'rejectLabel' => 'Batal',
			'method' => 'destroy',
		]);
	}

	public function destroy()
	{
		$data = auth()->user()->sekolah->users()->find($this->ID);
		$this->reset();
		$this->resetValidation();
		if ($data) {
			$data->mapels()->detach();
			$data->delete();
			return $this->notification()->success('Data berhasil dihapus');
		} else {
			return $this->notification()->error('Data tidak tersedia');
		}
	}
}
