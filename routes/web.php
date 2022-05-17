<?php

use App\Http\Controllers\Controller;
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

Route::middleware('guest:peserta')->group(function () {
	Route::get('/', function () {
		return view('welcome');
	})->name('index');
	Route::post('/', [Controller::class, 'pesertaLogin'])->name('peserta.login');
});

Route::middleware('auth:peserta')->prefix('ujian')->group(function () {
	Route::post('/keluar', [Controller::class, 'pesertaLogout'])->name('peserta.logout');
	Route::get('/', function () {
		return view('peserta', ['title' => 'Data Peserta', 'wire' => 'peserta.index']);
	})->name('ujian.index');
	Route::get('/tes', function () {
		return view('peserta', ['title' => 'Mengerjakan Ujian', 'wire' => 'peserta.ujian']);
	})->name('ujian.tes');
});

Route::middleware(['auth', 'verified', 'role:null,0,1'])->group(function () {
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
	Route::get('/jadwal', function () {
		return view('pages', ['title' => 'Jadwal Ujian', 'wire' => 'jadwal']);
	})->name('jadwal');
});

require __DIR__ . '/auth.php';
