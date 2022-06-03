<x-guest-layout>
	<x-slot name="title">Buat Akun Sekolah</x-slot>
	@push('scripts')
	{!! NoCaptcha::renderJs('id') !!}
	@endpush
	<x-auth-card class="sm:py-6">
		<x-slot name="logo">
			<a href="/" class="flex flex-col gap-2 justify-center">
				<x-application-logo class="w-16 h-16 fill-current text-gray-500 self-center" />
				<h1 class="text-xl font-bold text-center">Buat Akun Sekolah</h1>
			</a>
		</x-slot>

		<x-auth-validation-errors class="mb-4 text-center" :errors="$errors" />

		<form method="POST" action="{{ route('register') }}">
			@csrf

			<div>
				<x-alabel for="sekolah" :value="__('Nama Sekolah')" />

				<x-ainput id="sekolah" class="block mt-1 w-full" type="text" name="sekolah" :value="old('sekolah')" required
					autofocus />
			</div>

			<div class="mt-4">
				<x-alabel for="name" :value="__('Nama Lengkap')" />

				<x-ainput id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
			</div>

			<div class="mt-4">
				<x-alabel for="email" :value="__('Alamat Email')" />

				<x-ainput id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
			</div>

			<div class="mt-4">
				<x-alabel for="password" :value="__('Password')" />

				<x-ainput id="password" class="block mt-1 w-full" type="password" name="password" required
					autocomplete="new-password" />
			</div>

			<div class="mt-4">
				<x-alabel for="password_confirmation" :value="__('Ulang Password')" />

				<x-ainput id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation"
					required />
			</div>

			<div class="mt-4 flex justify-center">
				{!! NoCaptcha::display() !!}
			</div>

			<div class="flex items-center justify-end mt-4">
				<a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
					{{ __('Sudah mendaftar?') }}
				</a>

				<x-abutton class="ml-4">
					{{ __('Daftar') }}
				</x-abutton>
			</div>
		</form>
	</x-auth-card>
</x-guest-layout>