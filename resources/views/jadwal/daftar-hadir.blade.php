<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title>Daftar Hadir {{ $jadwal->name }}</title>
  <link rel="stylesheet" href="{{ asset('css/app.css').'?v='.time() }}">
  <style>
    html,
    body {
      font-family: 'Arial' !important;
    }

    @page {
      margin: 40px;
    }
  </style>
</head>

<body>
  {!! shortcode(auth()->user()->sekolah->kop) !!}
  <div class="mt-2">
    <h4 class="text-center" style="font-weight:bold;padding:0;margin: 0">DAFTAR HADIR</h4>
    <h4 class="text-center" style="font-weight:bold;padding:0;margin: 0;text-transform: uppercase">{!!
      nl2br($jadwal->desc) !!}
    </h4>
    <table class="w-full">
      <tr>
        <td style="vertical-align: top;padding: 15px">
          <table>
            <tr>
              <td>Mata Pelajaran</td>
              <td align="center" style="width: 15px">:</td>
              <td class="text-left">{{ implode(', ',$mapels) }}
              </td>
            </tr>
            <tr>
              <td>Kelas/Ruang</td>
              <td align="center">:</td>
              <td class="text-left">{{ implode(',
                ',array_unique($jadwal->pesertas()->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray()))
                }}</td>
            </tr>
            <tr>
              <td>Jumlah Peserta</td>
              <td align="center">:</td>
              <td class="text-left">{{ $jadwal->pesertas()->count().' Orang' }}</td>
            </tr>
          </table>
        </td>
        <td style="vertical-align: top;padding: 15px" align="right">
          <table style="width: auto">
            <tr>
              <td align="left">Jenis Soal</td>
              <td align="center">:</td>
              <td align="left">{{ implode(', ', array_map(function($v){return strtoupper($v);},$types)) }}
              </td>
            </tr>
            <tr>
              <td align="left">Jumlah Soal</td>
              <td align="center">:</td>
              <td align="left">{{ $jadwal->soal_count }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table class="w-full">
      <thead>
        <tr>
          <th style="border: solid 1px #000" class="p-2 border-b-2 text-center">No.</th>
          <th style="border: solid 1px #000" class="p-2 border-b-2 text-center">ID Peserta</th>
          <th style="border: solid 1px #000" class="p-2 border-b-2 text-center">Nama</th>
          <th style="border: solid 1px #000" class="p-2 border-b-2 text-center">Kelas/Ruang</th>
          <th style="border: solid 1px #000" class="p-2 border-b-2 text-center">Tanda Tangan</th>
        </tr>
      </thead>
      <tbody>
        @php
        $datapeserta = $jadwal->pesertas()
        ->orderBy('uid', 'asc')
        ->orderBy('name', 'asc')
        ->orderBy('created_at', 'asc')
        ->get();
        @endphp
        @foreach ($datapeserta as $key => $p)
        <tr>
          <td style="border: solid 1px #000" class="px-2 py-3 text-center">{{ ($key+1).'.' }}</td>
          <td style="border: solid 1px #000" class="px-2 py-3">{{ $p->uid }}</td>
          <td style="border: solid 1px #000" class="px-2 py-3">{{ $p->name }}</td>
          <td style="border: solid 1px #000" class="px-2 py-3">{{ $p->ruang }}</td>
          <td style="border: solid 1px #000;{{ $key!=0&&($key+1)%2==0?'padding-left: 75px':'' }}" class="px-2 py-3">
            {{ $key+1 }}.
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <table class="w-full" style="page-break-inside: avoid">
      <tr>
        <td>
          <table>
            <tr>
              <th align="left" colspan="3">Keterangan:</th>
            </tr>
            <tr>
              <td>PG</td>
              <td>:</td>
              <td>Pilihan Ganda</td>
            </tr>
            <tr>
              <td>PGK</td>
              <td>:</td>
              <td>Pilihan Ganda Kompleks</td>
            </tr>
            <tr>
              <td>IS</td>
              <td>:</td>
              <td>Isian Singkat</td>
            </tr>
            <tr>
              <td>U</td>
              <td>:</td>
              <td>Uraian</td>
            </tr>
            <tr>
              <td>BS</td>
              <td>:</td>
              <td>Benar/Salah</td>
            </tr>
            <tr>
              <td>JD</td>
              <td>:</td>
              <td>Menjodohkan</td>
            </tr>
          </table>
        </td>
        <td align="right">
          <table>
            <tr>
              <td class="pt-10">......................, ................................... {{
                $jadwal->start->format('Y') }}</td>
            </tr>
            <tr>
              <td align="left">Pengawas Ujian,</td>
            </tr>
            <tr>
              <td height="100"></td>
            </tr>
            <tr>
              <td align="left">(..............................................)</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</body>

</html>