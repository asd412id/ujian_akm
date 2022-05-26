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

	public function download(ModelsSoal $soal)
	{
		return response()->download(storage_path('app/' . $soal->excel), 'Soal ' . $soal->name . ' - ' . env('APP_NAME', 'Aplikasi Ujian') . '.xlsx');
	}

	public function downloadFormat()
	{
		return response()->download(resource_path('format_soal.xlsx'), 'Format Soal - ' . env('APP_NAME', 'Aplikasi Ujian') . '.xlsx');
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

	public function updatedExcel()
	{
		$this->validate([
			'excel' => 'required|mimes:xls,xlsx,ods,bin'
		], [
			'excel.required' => 'File excel tidak boleh kosong',
			'excel.mimes' => 'Format file yang diimport tidak dikenali',
		]);

		$reader = IOFactory::load($this->excel->path());
		$elements = $reader->getSheetByName('Soal')->toArray();

		if (count($elements)) {
			$cols = range('A', 'Z');
			$soals = '';
			foreach ($elements as $key => $row) {
				if ($key == 0) {
					continue;
				}
				if (!$row[0] || !is_numeric($row[0])) {
					continue;
				}
				$jenis = $row[2];
				if (!in_array(strtolower($jenis), ['pg', 'pgk', 'is', 'u', 'bs', 'jd'])) {
					continue;
				}
				$opsi_count = $row[6];
				if (intval($opsi_count) <= 0) {
					$opsi_count = 1;
				}
				$score = $row[5];
				if (!$score) {
					continue;
				}
				$shuffle = $row[4] == 'Ya' ? ' acak' : null;

				if ($key > 1) {
					$soals .= "\n";
				}

				$soals .= sprintf("[soal no=%s jenis=%s skor=%s%s]", $key, $jenis, $score, $shuffle);

				$soal = $reader->getSheetByName('Soal')->getCell($cols[1] . ($key + 1))->getValue();

				if (!$soal || $soal == '') {
					continue;
				}

				$soal = $this->getRichText($soal);
				$soals .= sprintf("\n\t[teks]\n\t\t%s\n\t[/teks]", $soal);

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

					if (!$val || $val == '') {
						continue;
					}

					$val = $this->getRichText($val);

					if (strtolower($jenis) == 'is' || strtolower($jenis) == 'u') {
						$answer = trim($val);
						$soals .= sprintf("\n\t[jawaban]%s[/jawaban]", $answer);
					} else {
						$options[$cols[$i]] = trim($val);
						if (in_array(strtolower($jenis), ['pg', 'pgk', 'bs'])) {
							$corrects[$cols[$i]] = $opstyle->getFill()->getColorsChanged();
							if (strtolower($jenis) == 'bs') {
								$soals .= sprintf("\n\t[opsi %s%s%s]%s[/opsi]", $cols[$i], $corrects[$cols[$i]] ? ' benar' : null, (is_array($labels) && isset($labels[$i]) ? ' label="' . $labels[$i] . '"' : null), trim($val));
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
							$soals .= sprintf("\n\t[opsi %s%s%s]%s[/opsi]", $cols[$i], (is_array($relations[$cols[$i]]) && count($relations[$cols[$i]]) ? ' relasi=' . implode(',', $relations[$cols[$i]]) : null), (is_array($labels) && isset($labels[$i]) ? ' label="' . $labels[$i] . '"' : null), trim($val));
						}
					}
				}
				$soals .= "\n[/soal]";
			}
			$this->item_soals = $soals;
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
