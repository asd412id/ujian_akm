<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title>{{ 'Kartu Peserta ('.implode('-',$ruangs).')' }}</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <style>
    html,
    body {
      background: none;
      font-family: 'Arial';
    }

    .card {
      border-collapse: collapse;
      border: solid 1px #000;
      page-break-inside: avoid !important;
      font-size: 0.6em !important;
      width: 100%;
    }

    .no-border {
      border: none !important;
    }

    .detail {
      width: 100%;
    }

    .detail .info {
      text-transform: uppercase;
    }

    @page {
      margin: 20px;
    }
  </style>
</head>

<body>
  <table class="table w-full" cellpadding="0" cellspacing="0">
    @php ($i = 1)
    @foreach ($pesertas as $key => $p)
    @if ($i == 3)
    @php ($i = 1)
    </tr>
    @endif
    @if ($i == 1)
    <tr>
      @endif
      @php ($i++)
      <td style="padding: 5px">
        <table class="card w-full" border="1">
          <tr>
            <td colspan="3">
              <table class="detail">
                <tr>
                  <td colspan="3" class="text-center"
                    style="font-weight: bold;font-size: 1.3em;padding-top: 15px;padding-bottom: 10px">
                    KARTU
                    PESERTA</td>
                </tr>
                <tr class="info">
                  <td style="width: 100px;padding-left: 15px;padding-top: 5px">ID PESERTA</td>
                  <td style="width: 10px;padding-top: 5px">:</td>
                  <td style="font-weight:  bold;padding-top: 5px">{{ $p->uid }}</td>
                </tr>
                <tr class="info">
                  <td style="width: 100px;padding-left: 15px">NAMA</td>
                  <td style="width: 10px">:</td>
                  <td style="font-weight:  bold">{{ $p->name }}</td>
                </tr>
                <tr class="info">
                  <td style="width: 100px;padding-left: 15px">KELAS/RUANG</td>
                  <td style="width: 10px">:</td>
                  <td style="font-weight:  bold">{{ strtoupper($p->ruang) }}</td>
                </tr>
                <tr>
                  <td style="width: 100px;padding-left: 15px">PASSWORD</td>
                  <td style="width: 10px">:</td>
                  <td style="font-weight:  bold">{{ $p->password_string }}</td>
                </tr>
                <tr>
                  <td colspan="2" style="padding: 15px" align="center">
                    <img
                      src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(65)->generate($p->token)) !!} ">
                  </td>
                  <td class="text-right"
                    style="padding-bottom: 15px;padding-left: 100px;padding-top: 11px;padding-right: 30px">
                    {{ now()->translatedFormat('j F Y') }}
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    Panitia Ujian
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
      @endforeach
  </table>
</body>

</html>