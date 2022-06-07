<?php

namespace App\Http\Livewire;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use stdClass;
use WireUi\Traits\Actions;

class DaftarSekolah extends Component
{
	use WithPagination;
	use Actions;

	public $search = '';
	public $modalTitle = 'Tambah Data';
	public $modal = false;
	public $limit = 10;
	public $verified = 'all';
	public $ID;
	public $nama_sekolah;
	public $nama_operator;
	public $email;
	public $password;
	public $repassword;
	public $max_storage = 0;
	public $max_upload = 0;
	public $is_verified = false;
	public $allow_register = false;
	public $must_verified = true;
	public $configs = [];
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function getConfigs()
	{
		$this->configs['allow_register'] = false;
		$this->configs['must_verified'] = true;
		if (Storage::exists('configs.json')) {
			$configs = file_get_contents(Storage::path('configs.json'));
			if (isValidJSON($configs)) {
				$this->configs = json_decode($configs, true);
			}
		}
	}

	public function mount()
	{
		$this->getConfigs();
		$this->allow_register = isset($this->configs['allow_register']) ? $this->configs['allow_register'] : false;
		$this->must_verified = isset($this->configs['must_verified']) ? $this->configs['must_verified'] : true;
	}

	public function updatedAllowRegister($value)
	{
		$this->getConfigs();
		$this->configs['allow_register'] = $this->allow_register;
		file_put_contents(Storage::path('configs.json'), json_encode($this->configs));
		$this->notification()->success('Berhasil ' . ($value ? 'membuka' : 'menutup') . ' pendaftaran');
	}
	public function updatedMustVerified($value)
	{
		$this->getConfigs();
		$this->configs['must_verified'] = $this->must_verified;
		file_put_contents(Storage::path('configs.json'), json_encode($this->configs));
		$this->notification()->success('Email ' . ($value ? 'harus diverifikasi' : 'tidak diverifikasi'));
	}

	public function create()
	{
		$this->resetValidation();
		$this->reset([
			'modalTitle',
			'nama_sekolah',
			'nama_operator',
			'email',
			'password',
			'repassword',
			'is_verified'
		]);
		$this->modal = true;
	}

	public function edit(Sekolah $sekolah)
	{
		$this->ID = $sekolah->id;
		$this->resetValidation();
		$this->reset([
			'modalTitle',
			'nama_sekolah',
			'nama_operator',
			'email',
			'password',
			'repassword',
			'is_verified'
		]);

		$this->nama_sekolah = $sekolah->name;
		if ($sekolah->operator) {
			$this->nama_operator = $sekolah->operator->name;
			$this->email = $sekolah->operator->email;
			$this->is_verified = boolval($sekolah->operator->email_verified_at);
		}

		$storage = file_exists(public_path('uploads/' . generateUserFolder($sekolah->id) . '/config.php')) ? include public_path('uploads/' . generateUserFolder($sekolah->id) . '/config.php') : null;

		if (is_array($storage)) {
			$this->max_storage = $storage['MaxSizeTotal'];
			$this->max_upload = $storage['MaxSizeUpload'];
		}

		$this->modalTitle = 'Ubah Data ' . $sekolah->name;
		$this->modal = true;
	}

	public function store()
	{
		$sekolah = Sekolah::find($this->ID);
		if ($this->ID && $sekolah->operator) {
			$this->validate([
				'nama_sekolah' => 'required',
				'nama_operator' => 'required',
				'email' => 'required|email|unique:users,email,' . $sekolah->operator->id . ',id',
			]);
		} else {
			$this->validate([
				'nama_sekolah' => 'required',
				'nama_operator' => 'required',
				'email' => 'required|unique:users,email',
				'password' => 'required',
				'repassword' => 'required|same:password',
			]);
		}

		if (!$sekolah) {
			$sekolah = new Sekolah();
		}
		$sekolah->name = $this->nama_sekolah;
		if ($sekolah->save()) {
			$configFile = createUserFolder($sekolah->id);
			$config = "
			<?php
				return [
					'MaxSizeTotal' => " . $this->max_storage . ",
					'MaxSizeUpload' => " . $this->max_upload . ",
					'default_language' => 'id',
					'show_total_size' => true,
					'show_folder_size' => true,
					'convert_spaces' => true,
					'replace_with' => '_',
					'lower_case' => true,
				];
			";

			file_put_contents($configFile, str_replace(["\t"], '', trim($config)));

			$operator = $sekolah->operator;
			if (!$operator) {
				$operator = new User();
				$operator->role = 0;
				$operator->sekolah_id = $sekolah->id;
			}
			$operator->name = $this->nama_operator;
			$operator->email = $this->email;
			if ($this->password) {
				$operator->password = bcrypt($this->password);
			}
			if ($this->is_verified && is_null($operator->email_verified_at)) {
				$operator->email_verified_at = now();
			} elseif (!$this->is_verified) {
				$operator->email_verified_at = null;
			}
			$operator->save();
			$this->reset([
				'modal',
				'modalTitle',
				'nama_sekolah',
				'nama_operator',
				'email',
				'password',
				'repassword',
				'is_verified'
			]);
			return $this->notification()->success('Data berhasil disimpan');
		}
		return $this->notification()->error('Data gagal disimpan');
	}

	public function delete(Sekolah $sekolah)
	{
		$this->ID = $sekolah->id;
		$this->dialog()->confirm([
			'title' => 'Yakin ingin menghapus?',
			'description' => $sekolah->name,
			'acceptLabel' => 'Hapus',
			'rejectLabel' => 'Batal',
			'method' => 'destroy',
		]);
	}

	public function destroy()
	{
		$data = Sekolah::find($this->ID);
		$this->reset();
		$this->resetValidation();
		if ($data) {
			$data->delete();
			return $this->notification()->success('Data berhasil dihapus');
		} else {
			return $this->notification()->error('Data tidak tersedia');
		}
	}

	public function render()
	{

		$query = Sekolah::when($this->verified != 'all', function ($q) {
			$q->whereHas('users', function ($q) {
				$q->where('role', 0);
				if ($this->verified == 'verified') {
					$q->whereNotNull('email_verified_at');
				} else {
					$q->whereNull('email_verified_at');
				}
			});
		})
			->where(function ($q) {
				$q->where('name', 'like', "%$this->search%")
					->orWhereHas('users', function ($q) {
						$q->where('name', 'like', "%$this->search%");
					});
			})
			->paginate($this->limit);

		return view('livewire.daftar-sekolah', ['data' => $query]);
	}
}
