<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ (isset($title)?$title.' | ':'').config('app.name', 'Aplikasi Ujian AKM') }}</title>
	<link rel="shortcut icon" href="{{ url('favicon.png') }}" type="image/png">

	<link rel="stylesheet" href="{{ url('css/app.css') }}">
	<script src="{{ url('js/app.js') }}" defer></script>

</head>

<body class="antialiased" x-data="{modal:false,tloading: 'Mohon Tunggu', html5QrCode: null}">
	<div class="flex flex-col items-center justify-center min-h-screen gap-3 p-5 bg-gray-100">
		<a href="/">
			<x-application-logo class="w-20 h-20 text-gray-500 fill-current" />
		</a>
		<div class="text-center text-primary-600">
			<h1 class="text-3xl font-bold text-center">{{ strtoupper((isset($title)?$title.' | ':'').config('app.name',
				'Aplikasi Ujian
				AKM')) }}</h1>
			<em>Silahkan Masuk untuk Mengikuti Ujian</em>
		</div>
		<div class="w-full max-w-sm" @keyup.esc="$dispatch('closed')">
			<x-card cardClasses="px-3 md:px-0" x-data="{submit: false}">
				@if (session()->has('error'))
				<div class="px-3 py-1 mb-3 text-center text-red-600 border border-red-200 rounded-lg bg-red-50">{{
					session()->get('error') }}
				</div>
				@endif
				<form class="flex flex-col gap-3" method="POST" action="{{ route('peserta.login') }}" x-on:submit="submit=true">
					@csrf
					<x-input lg primary type="text" placeholder="Masukkan ID Peserta" label="ID Peserta" autofocus
						name="peserta_id" x-ref="id" x-bind:readonly='submit' value="{{ old('peserta_id') }}" />
					<x-input lg name="password" type="password" placeholder="******************" label="Password"
						x-bind:readonly='submit' />
					<div class="flex items-center {{ request()->secure()?'justify-between':'justify-end' }} gap-2">
						@if (request()->secure())
						<x-button info label="Scan Kode QR" icon="qrcode" x-on:click="$dispatch('showed')"
							x-bind:disabled='submit' />
						@endif
						<x-button type="submit" x-bind:disabled='submit' primary label="Masuk" icon="login" />
					</div>
				</form>
				<div class="text-right mt-6 text-primary-700 text-sm">
					<a class="underline" href="{{ route('login') }}">Masuk Sebagai Administrator</a>
				</div>
			</x-card>
			<div class="mt-3 text-sm italic text-center text-primary-700">Copyright &copy; 2022 by
				<a class="underline" href="https://www.facebook.com/aezdar">asd412id</a>
			</div>
		</div>
	</div>
	@if (request()->secure())
	<div x-show='modal' x-transition @showed.window="modal=true;$nextTick(()=>{
		Html5Qrcode.getCameras().then(devices => {
			if (devices && devices.length) {
				tloading='Silahkan scan Kode QR pada kartu ujian!';
				html5QrCode = new Html5Qrcode('qrscan');
				html5QrCode.start(
					{ facingMode: 'environment' },
					{
						fps: 1000,
						qrbox: { width: 250, height: 250 },
						aspectRatio: 1
					},
					(decodedText, decodedResult) => {
						html5QrCode.stop();
						tloading = 'Mengecek Informasi Login ...'
						axios.post(@js(route('peserta.login.qr')),{_token: @js(csrf_token()),qrcode: decodedText})
						.then(res=>{
							tloading = res.msg;
							location.reload();
						}).catch(err => {
							tloading = err.response.data.msg;
							setTimeout(()=>{modal=false;},3000);
						})
					}
				).catch((err) => {
					tloading = 'Tidak dapat mengakses kamera!';
				});
			}
		}).catch(err => {
			tloading = 'Anda harus mengizinkan akses kamera pada perangkat!';
		});
	})"
		class="absolute inset-0 z-10 flex items-center justify-center min-h-screen p-5 backdrop-blur-md backdrop-grayscale">
		<x-card @closed.window="modal=false;$nextTick(()=>{
		if(html5QrCode!=null){
			try{
				html5QrCode.stop();
			}catch(err){
				console.log('Camera not running!');
			}
			tloading = 'Mohon Tunggu';
		}
	})" cardClasses="w-full max-w-md bg-white text-center">
			<span x-text="tloading" class="font-bold text-primary-600">Mohon Tunggu</span>
			<div id="qrscan"></div>
			<x-button x-on:click="$dispatch('closed')" label="TUTUP SCANNER" sm outline primary class="mt-3" />
		</x-card>
	</div>
	@endif
</body>

</html>