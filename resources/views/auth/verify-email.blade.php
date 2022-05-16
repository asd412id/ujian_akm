<x-guest-layout>
	<x-auth-card>
		<x-slot name="logo">
			<a href="/">
				<x-application-logo class="w-20 h-20 fill-current text-gray-500" />
			</a>
		</x-slot>

		<div class="mb-4 text-sm text-gray-600">
			{{ __('Terima kasih sudah mendaftar! Sebelum memulai, Anda harus melakukan verifikasi email dengan mengklik
			link yang dikirim ke email yang Anda gunakan saat mendaftar. Jika Anda tidak menerima email, silahkan klik
			tombol di bawah untuk mengirim email verifikasi terbaru!') }}
		</div>

		@if (session('status') == 'verification-link-sent')
		<div class="mb-4 font-medium text-sm text-green-600">
			{{ __('Link verifikasi baru telah dikirim ke email Anda.') }}
		</div>
		@endif

		<div class="mt-4 flex items-center justify-between">
			<form method="POST" action="{{ route('verification.send') }}">
				@csrf

				<div>
					<x-abutton>
						{{ __('Kirim Ulang Email Verifikasi') }}
					</x-abutton>
				</div>
			</form>

			<form method="POST" action="{{ route('logout') }}">
				@csrf

				<button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
					{{ __('Log Out') }}
				</button>
			</form>
		</div>
	</x-auth-card>
</x-guest-layout>