<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ (isset($title)?$title.' | ':'').config('app.name', 'Aplikasi Ujian AKM') }}</title>

	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<script src="{{ asset('js/app.js') }}" defer></script>

</head>

<body class="antialiased" x-data="{modal:false,tloading: 'Mohon Tunggu', html5QrCode: null}">
	<div class="min-h-screen flex flex-col gap-3 bg-gray-100 justify-center items-center p-5">
		<div class="text-primary-600">
			<h1 class="text-center text-3xl font-bold">{{ strtoupper((isset($title)?$title.' | ':'').config('app.name',
				'Aplikasi Ujian
				AKM')) }}</h1>
			<em>Silahkan Masuk untuk Mengikuti Ujian</em>
		</div>
		<div class="w-full max-w-sm" @keyup.esc="$dispatch('closed')">
			<x-card cardClasses="px-3 md:px-0" x-data="{submit: false}">
				@if (session()->has('error'))
				<div class="mb-3 py-1 px-3 bg-red-50 text-red-600 border border-red-200 text-center rounded-lg">{{
					session()->get('error') }}
				</div>
				@endif
				<form class="flex flex-col gap-3" method="POST" action="{{ route('peserta.login') }}" x-on:submit="submit=true">
					@csrf
					<x-input lg primary type="text" placeholder="Masukkan ID Peserta" label="ID Peserta" autofocus
						name="peserta_id" x-ref="id" x-bind:readonly='submit' value="{{ old('peserta_id') }}" />
					<x-input lg name="password" type="password" placeholder="******************" label="Masukkan Password"
						x-bind:readonly='submit' />
					<div class="flex items-center {{ request()->secure()?'justify-between':'justify-end' }} gap-2">
						@if (request()->secure())
						<x-button info label="Scan Kode QR" icon="qrcode" x-on:click="$dispatch('showed')"
							x-bind:disabled='submit' />
						@endif
						<x-button type="submit" x-bind:disabled='submit' primary label="Masuk" icon="login" />
					</div>
				</form>
			</x-card>
			<div class="text-center text-sm italic mt-3 text-primary-700">Copyright &copy; 2022 by
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
						tloading = decodedText;
					}
				).catch((err) => {
					tloading = 'Tidak dapat mengakses kamera!';
				});
			}
		}).catch(err => {
			tloading = 'Anda harus mengizinkan akses kamera pada perangkat!';
		});
	})" x-on:click="$dispatch('closed')"
		class="min-h-screen backdrop-blur-md backdrop-grayscale absolute inset-0 z-10 flex justify-center items-center p-5">
		<x-card @closed.window="modal=false;$nextTick(()=>{
		if(html5QrCode!=null){
			try{
				html5QrCode.stop();
			}catch(err){
				console.log('Camera not running!');
			}
			tloading = 'Mohon Tunggu';
			$refs.id.focus();
		}
	})" cardClasses="w-full max-w-md bg-white text-center">
			<span x-text="tloading" class="font-bold text-primary-600">Mohon Tunggu</span>
			<div id="qrscan"></div>
		</x-card>
	</div>
	@endif
</body>

</html>