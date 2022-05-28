<?php

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['guest:peserta', 'guest'])->group(function () {
	Route::get('/', function () {
		return view('welcome');
	})->name('index');
	Route::post('/', [Controller::class, 'pesertaLogin'])->name('peserta.login');
	Route::post('/qrcode', [Controller::class, 'loginQR'])->name('peserta.login.qr');
});

Route::middleware('auth:peserta')->prefix('ujian')->group(function () {
	Route::post('/keluar', [Controller::class, 'pesertaLogout'])->name('peserta.logout');
	Route::get('/', function () {
		return view('peserta', ['title' => 'Data Peserta', 'wire' => 'peserta.index']);
	})->name('ujian.index');
	Route::get('/tes', function () {
		return view('peserta', ['title' => 'Mengerjakan Soal', 'wire' => 'peserta.ujian']);
	})->name('ujian.tes')->middleware('ujian');
});

Route::middleware(['auth', 'verified', 'role:null,0,1'])->prefix('/admin')->group(function () {
	Route::get('/beranda', function () {
		return view('dashboard', ['title' => 'Beranda']);
	})->name('dashboard');
	Route::get('/media', function () {
		return view('media', ['title' => 'Media']);
	})->name('media');

	Route::middleware('role:null,0')->group(function () {
		Route::get('/mapel', function () {
			return view('pages', ['title' => 'Mata Pelajaran', 'wire' => 'mapel']);
		})->name('mapel');
		Route::get('/penilai', function () {
			return view('pages', ['title' => 'Penilai', 'wire' => 'penilai']);
		})->name('penilai');
	});

	Route::get('/soal', function () {
		return view('pages', ['title' => 'Daftar Soal', 'wire' => 'soal']);
	})->name('soal');
	Route::get('/peserta', function () {
		return view('pages', ['title' => 'Peserta Ujian', 'wire' => 'peserta']);
	})->name('peserta');
	Route::get('/ruang', function () {
		return view('pages', ['title' => 'Ruang Ujian', 'wire' => 'ruang']);
	})->name('ruang');
	Route::get('/soal', function () {
		return view('pages', ['title' => 'Daftar Soal', 'wire' => 'soal']);
	})->name('soal');

	Route::prefix('/jadwal')->group(function () {
		Route::get('/', function () {
			return view('pages', ['title' => 'Jadwal Ujian', 'wire' => 'jadwal']);
		})->name('jadwal');
		Route::get('/{uuid}', function () {
			$jadwal = Jadwal::where('uuid', request()->uuid)->first();
			if (!$jadwal) {
				return redirect()->route('jadwal')->withErrors('Jadwal tidak tersedia');
			}
			return view('pages', ['title' => 'Status Peserta - ' . $jadwal->name, 'wire' => 'status-peserta', 'params' => $jadwal]);
		})->name('statuspeserta');
		Route::get('/{uuid}/nilai', function () {
			$jadwal = Jadwal::where('uuid', request()->uuid)->first();
			if (!$jadwal) {
				return redirect()->route('jadwal')->withErrors('Jadwal tidak tersedia');
			}
			return view('pages', ['title' => 'Penilaian - ' . $jadwal->name, 'wire' => 'nilai', 'params' => $jadwal]);
		})->name('nilai');
	});
});

require __DIR__ . '/auth.php';
