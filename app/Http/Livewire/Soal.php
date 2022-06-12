<?php

namespace App\Http\Livewire;

use App\Models\ItemSoal;
use App\Models\Soal as ModelsSoal;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use WireUi\Traits\Actions;
use Str;

class Soal extends Component
{

	use Actions;
	use WithPagination;
	use WithFileUploads;

	public $limit = 10;
	public $search = null;
	public $modal = false;
	public $showSoal = false;
	public $modalTitle = 'Data Baru';
	protected $data = [];
	public $sid;
	public $error = false;
	public $ID;
	public $IDS;
	public $name;
	public $mapel;
	public $item_soals;
	public $excel;
	public $listMapel;
	public $soal;
	public $dataattrlist = '';
	public $attrplaceholder = 'Pilih Mapel';
	public $select_search;
	protected $queryString = [
		'page' => ['except' => 1, 'as' => 'hal']
	];

	public function updatingName()
	{
		$this->error = false;
		$this->resetValidation('name');
		$check = auth()->user()->sekolah->soals()
			->where('name', $this->name)
			->where('id', '!=', $this->ID)
			->first();
		if ($check) {
			$this->error = true;
			return $this->addError('name', 'Nama soal sudah digunakan');
		}
	}

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
					->orWhereHas('mapel', function ($q) use ($r) {
						$q->where('name', 'like', "%$r%");
					});
			});
		})
			->when($this->dataattrlist, function ($q, $r) {
				$q->where('mapel_id', $r);
			});

		if (auth()->user()->role != 0) {
			$this->data->whereIn('mapel_id', auth()->user()->mapels->pluck('id')->toArray());
		}

		$this->data = $this->data
			->orderBy('name', 'asc')
			->orderBy('id', 'desc')
			->paginate($this->limit);

		$dta = $this->data;
		$this->IDS = $dta->pluck('id')->toArray();
		$attrlists = auth()->user()->sekolah->mapels()
			->when(auth()->user()->role != 0, function ($q) {
				$q->whereIn('id', auth()->user()->mapels->pluck('id')->toArray());
			})
			->whereHas('soals')
			->select('name as label', 'id as value')
			->get()
			->toArray();

		return view('livewire.soal', ['data' => $this->data, 'attrlists' => $attrlists]);
	}

	public function boot()
	{
		$list = auth()->user()->sekolah->mapels()
			->when(auth()->user()->role != 0, function ($q) {
				$q->whereIn('id', auth()->user()->mapels->pluck('id')->toArray());
			})
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
			->when(auth()->user()->role != 0, function ($q) {
				$q->whereIn('id', auth()->user()->mapels->pluck('id')->toArray());
			})
			->where('name', 'like', "%$value%")
			->orWhere('id', $this->mapel)
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
			'error',
		]);
		$list = auth()->user()->sekolah->mapels()
			->when(auth()->user()->role != 0, function ($q) {
				$q->whereIn('id', auth()->user()->mapels->pluck('id')->toArray());
			})
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

		$check = auth()->user()->sekolah->soals()
			->where('name', $this->name)
			->where('id', '!=', $this->ID)
			->first();
		if ($check) {
			$this->error = true;
			return $this->notification()->error('Nama soal sudah digunakan');
		}

		$update = $this->ID ? auth()->user()->sekolah->soals()->find($this->ID) : new ModelsSoal();
		$update->name = $this->name;
		$update->sekolah_id = auth()->user()->sekolah_id;
		$update->mapel_id = $this->mapel;
		$update->soal_raw = $this->item_soals;
		if ($this->excel) {
			Storage::delete($update->excel);
			$update->opt = [
				'excel' => $this->excel->store('soalassets')
			];
		}
		if ($update->save()) {
			$item_soals = getJson(parseSoal(cleanCodeTags($this->item_soals)));
			if (count($item_soals)) {
				foreach ($item_soals as $v) {
					if (isValidJSON($v)) {
						$item = json_decode($v);
						if (!$item->num || !is_numeric($item->num) || intval($item->num) == 0 || !in_array(strtolower($item->type), ['pg', 'pgk', 'jd', 'bs', 'is', 'u'])) {
							continue;
						}
						$isoal = $update->item_soals()->where('num', $item->num)->first() ?? new ItemSoal();
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
			'error',
		]);
		$this->resetValidation();
		if (auth()->user()->role == 0) {
			$list = auth()->user()->sekolah->mapels()
				->whereIn('id', [$soal->mapel_id])
				->orWhereNotIn('id', [$soal->mapel_id])
				->select('id', 'name')
				->get()
				->toArray();
		} else {
			$list = auth()->user()->sekolah->mapels()
				->whereIn('id', auth()->user()->mapels->pluck('id')->toArray())
				->select('id', 'name')
				->get()
				->toArray();
		}


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

	public function download(ModelsSoal $soal)
	{
		return response()->download(storage_path('app/' . $soal->excel), 'Soal ' . str_replace(['/', '\\'], '-', $soal->name) . ' - ' . env('APP_NAME', 'Aplikasi Ujian') . '.xlsx');
	}

	public function downloadFormat()
	{
		return response()->download(resource_path('format_soal.xlsx'), 'Format Soal' . ($this->name ? ' ' . str_replace(['/', '\\'], '-', $this->name) : '') . ' - ' . env('APP_NAME', 'Aplikasi Ujian') . '.xlsx');
	}

	public function getRichText($text)
	{
		if ($text instanceof RichText) {
			$newtext = '';
			foreach ($text->getRichTextElements() as $richTextElement) {
				if ($richTextElement->getFont()) {
					$st = 0;
					$styles = '';
					if ($richTextElement->getFont()->getBold() === true) {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<strong>%s</strong>', $styles);
					}
					if ($richTextElement->getFont()->getItalic() === true) {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<em>%s</em>', $styles);
					}
					if ($richTextElement->getFont()->getUnderline() !== 'none') {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<u>%s</u>', $styles);
					}
					if ($richTextElement->getFont()->getStrikethrough() === true) {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<strike>%s</strike>', $styles);
					}
					if ($richTextElement->getFont()->getColor()->getRGB() != '000000') {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<span style="color: #' . $richTextElement->getFont()->getColor()->getRGB() . '">%s</span>', $styles);
					}
					if ($richTextElement->getFont()->getSuperscript() === true) {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<sup>%s</sup>', $styles);
					}
					if ($richTextElement->getFont()->getSubscript() === true) {
						$st = 1;
						$styles = $styles == '' ? $richTextElement->getText() : $styles;
						$styles = sprintf('<sub>%s</sub>', $styles);
					}
					// if ($richTextElement->getFont()->getSize()) {
					// 	$st = 1;
					// 	$styles = $styles == '' ? $richTextElement->getText() : $styles;
					// 	$styles = sprintf('<span style="font-size: %spt">%s</span>', $richTextElement->getFont()->getSize(), $styles);
					// }

					if (!$st) {
						$newtext .= $richTextElement->getText();
					}
					$newtext .= $styles;
				} else {
					$newtext .= $richTextElement->getText();
				}
			}
			return $newtext;
		}
		return $text;
	}

	public function saveImage($image)
	{
		$zipReader = fopen($image->getPath(), 'r');
		$imageContents = '';
		while (!feof($zipReader)) {
			$imageContents .= fread($zipReader, 1024);
		}
		fclose($zipReader);
		$extension = $image->getExtension();

		$imageName = Str::slug($this->name) . '/' . sha1($image->getName()) . '.' . $extension;
		$imageSize = strlen($imageContents);

		$storage = file_exists(public_path('uploads/' . userFolder()) . '/config.php') ? include public_path('uploads/' . userFolder()) . '/config.php' : null;

		if (is_array($storage)) {
			if (($imageSize <= $storage['MaxSizeUpload'] * pow(1024, 2)) && (($imageSize + folderSize(public_path('uploads/' . userFolder()))) <= $storage['MaxSizeTotal'] * pow(1024, 2))) {
				Storage::disk('public')->put('uploads/' . userFolder() . '/' . $imageName, $imageContents);
				Storage::disk('public')->put('thumbs/' . userFolder() . '/' . $imageName, $imageContents);
				return $imageName;
			}
		}
		return false;
	}

	public function updatedExcel()
	{
		$this->validate([
			'name' => 'required',
			'excel' => 'required|mimes:xls,xlsx,ods,bin'
		], [
			'name.required' => 'Nama soal tidak boleh kosong',
			'excel.required' => 'File excel tidak boleh kosong',
			'excel.mimes' => 'Format file yang diimport tidak dikenali',
		]);

		$this->error = true;

		$check = auth()->user()->sekolah->soals()
			->where('name', $this->name)
			->where('id', '!=', $this->ID)
			->first();
		if ($check) {
			$this->error = true;
			return $this->notification()->error('Nama soal sudah digunakan');
		}

		$reader = IOFactory::load($this->excel->path());

		if (!$reader->getSheetByName('Soal')) {
			return $this->addError('excel', 'Format file excel tidak dikenali');
		}

		$elements = $reader->getSheetByName('Soal')->toArray();
		$drawings = $reader->getSheetByName('Soal')->getDrawingCollection();
		$images = [];

		foreach ($drawings as $key => $dr) {
			$images[$dr->getCoordinates()][$key] = $dr;
		}

		Storage::disk('public')->deleteDirectory('uploads/' . userFolder() . '/' . Str::slug($this->name));
		Storage::disk('public')->deleteDirectory('thumbs/' . userFolder() . '/' . Str::slug($this->name));

		if (count($elements)) {
			$cols = range('A', 'Z');
			$soals = '';
			foreach ($elements as $key => $row) {
				if ($key == 0) {
					continue;
				}
				if (!$row[0] || !is_numeric(trim($row[0]))) {
					continue;
				}
				$jenis = trim($row[2]);
				if (!in_array(strtolower($jenis), ['pg', 'pgk', 'is', 'u', 'bs', 'jd'])) {
					continue;
				}
				$opsi_count = trim($row[6]);
				if (intval($opsi_count) <= 0) {
					$opsi_count = 1;
				}
				$score = trim($row[5]);
				if (!$score) {
					continue;
				}
				$shuffle = strtolower(trim($row[4])) == 'ya' ? ' acak' : null;

				if ($key > 1) {
					$soals .= "\n";
				}

				$soal = $reader->getSheetByName('Soal')->getCell($cols[1] . ($key + 1))->getValue();
				$imgs = isset($images[$cols[1] . ($key + 1)]) ? $images[$cols[1] . ($key + 1)] : [];

				usort($imgs, function ($a, $b) {
					return $a->getOffsetY() <=> $b->getOffsetY();
				});

				if ((!$soal || $soal == '') && (!is_array($imgs) || !count($imgs))) {
					continue;
				}
				$soals .= sprintf("[soal no=%s jenis=%s skor=%s%s]", $key, $jenis, $score, $shuffle);
				$soal = $this->getRichText($soal);

				if (count($imgs)) {
					$inserted = [];
					foreach ($imgs as $ki => $image) {
						$ipath = $this->saveImage($image);
						if (!$ipath) {
							$img = '<p style="font-style: italic;font-weight: bold">[Gambar tidak dapat terupload]</p>';
						} else {
							$img = "[g" . ($image->getOffsetX() == 0 && $image->getOffsetX() != $image->getOffsetX2() ? ' kiri' : ($image->getOffsetX2() == 0 && $image->getOffsetX() != $image->getOffsetX2() ? ' kanan' : ($image->getOffsetX() == $image->getOffsetX2() || ($image->getOffsetX() > 0 && $image->getOffsetX2() / $image->getOffsetX() <= 2.1) ? ' tengah' : ''))) . "]" . $ipath . "[/g]";
						}
						if ($image->getOffsetY() == 0) {
							$soal = $img . $soal;
						} elseif ($image->getOffsetY2() == 0) {
							$soal = $soal . $img;
						} else {
							$break = explode("\n\n", $soal);
							if (count($break)) {
								$soal = '';
								foreach ($break as $kn => $n) {
									$soal .= $n;
									if (!in_array($ki, $inserted)) {
										array_push($inserted, $ki);
										$soal .= $img;
									} else {
										if ($kn < count($break) - 1) {
											$soal .= "\n\n";
										}
									}
								}
							}
						}
					}
				}

				$soals .= sprintf("\n\t[teks]\n\t\t%s\n\t[/teks]", trim($soal));

				$options = null;
				$labels = array_map(function ($d) {
					return trim($d);
				}, explode(",", $row[3]));
				$corrects = null;
				$relations = null;
				$answer = null;

				for ($i = 0; $i < $opsi_count; $i++) {
					$k = $i + 8;
					$opsi = $reader->getSheetByName('Soal')->getCellByColumnAndRow($k, $key + 1);
					$val = $opsi->getValue();
					$opstyle = $opsi->getAppliedStyle();
					$imgs = isset($images[$cols[$k - 1] . ($key + 1)]) ? $images[$cols[$k - 1] . ($key + 1)] : [];

					usort($imgs, function ($a, $b) {
						return $a->getOffsetY() <=> $b->getOffsetY();
					});

					if ((!$val || $val == '') && (!is_array($imgs) || !count($imgs))) {
						continue;
					}

					$val = $this->getRichText($val);

					if (count($imgs)) {
						$inserted = [];
						foreach ($imgs as $ki => $image) {
							$ipath = $this->saveImage($image);
							if (!$ipath) {
								$img = '<p style="font-style: italic;font-weight: bold">[Gambar tidak dapat terupload]</p>';
							} else {
								$img = "[g" . ($image->getOffsetX() == 0 && $image->getOffsetX() != $image->getOffsetX2() ? ' kiri' : ($image->getOffsetX2() == 0 && $image->getOffsetX() != $image->getOffsetX2() ? ' kanan' : ($image->getOffsetX() == $image->getOffsetX2() || ($image->getOffsetX() > 0 && $image->getOffsetX2() / $image->getOffsetX() <= 2.1) ? ' tengah' : ''))) . "]" . $ipath . "[/g]";
							}
							if ($image->getOffsetY() == 0) {
								$val = $img . $val;
							} elseif ($image->getOffsetY2() == 0) {
								$val = $val . $img;
							} else {
								$break = explode("\n\n", $val);
								if (count($break)) {
									$val = '';
									foreach ($break as $kn => $n) {
										$val .= $n;
										if (!in_array($ki, $inserted)) {
											array_push($inserted, $ki);
											$val .= $img;
										} else {
											if ($kn < count($break) - 1) {
												$val .= "\n\n";
											}
										}
									}
								}
							}
						}
					}

					if (strtolower($jenis) == 'is' || strtolower($jenis) == 'u') {
						$answer = trim($val);
						$soals .= sprintf("\n\t[jawaban]%s[/jawaban]", $answer);
					} else {
						$options[$cols[$i]] = trim($val);
						if (in_array(strtolower($jenis), ['pg', 'pgk', 'bs'])) {
							$corrects[$cols[$i]] = $opstyle->getFill()->getColorsChanged() && $opstyle->getFill()->getStartColor()->getRGB() != 'FFFFFF';
							if (strtolower($jenis) == 'bs') {
								$soals .= sprintf("\n\t[opsi %s%s%s]%s[/opsi]", $cols[$i], $corrects[$cols[$i]] ? ' benar' : null, (is_array($labels) && isset($labels[$i]) ? ' label="' . trim($labels[$i]) . '"' : null), trim($val));
							} else {
								$soals .= sprintf("\n\t[opsi %s%s]%s[/opsi]", $cols[$i], $corrects[$cols[$i]] ? ' benar' : null, trim($val));
							}
						} else {
							$color = $opstyle->getFill()->getStartColor()->getRGB();
							if ($color == 'FFFFFF') {
								$relations[$cols[$i]] = null;
							} else {
								$relations[$cols[$i]] = [];
								for ($j = $i + 1; $j < $opsi_count; $j++) {
									$color2 = $reader->getSheetByName('Soal')->getCellByColumnAndRow($j + 8, $key + 1)->getAppliedStyle()->getFill()->getStartColor()->getRGB();
									if ($color == $color2) {
										array_push($relations[$cols[$i]], $cols[$j]);
									}
								}
							}
							$soals .= sprintf("\n\t[opsi %s%s%s]%s[/opsi]", $cols[$i], (is_array($relations[$cols[$i]]) && count($relations[$cols[$i]]) ? ' relasi=' . trim(implode(',', $relations[$cols[$i]])) : null), (is_array($labels) && isset($labels[$i]) ? ' label="' . trim($labels[$i]) . '"' : null), trim($val));
						}
					}
				}
				$soals .= "\n[/soal]";
			}
			$this->item_soals = $soals;
			$this->error = false;
			$this->notification()->success('Soal berhasil diimpor');
		}
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
		$count = 0;
		$soals = auth()->user()->sekolah->soals()
			->whereIn('id', $this->IDS)->get();
		if (count($soals)) {
			foreach ($soals as $key => $s) {
				$items = $s->item_soals->pluck('id')->toArray();
				if (!$s->jadwals()
					->whereHas('tests', function ($q) use ($items) {
						$q->whereIn('item_soal_id', $items);
					})->count()) {
					$s->delete();
					$count++;
				}
			}
			$this->resetValidation();
			$this->resetExcept('listMapel');
			return $this->notification()->success('Berhasil menghapus ' . $count . ' data');
		}
		return $this->notification()->error('Data gagal dihapus');
	}
}
