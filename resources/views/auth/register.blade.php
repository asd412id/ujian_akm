<x-guest-layout>
	<x-slot name="title">Buat Akun Sekolah</x-slot>
	@push('scripts')
	{!! NoCaptcha::renderJs('id') !!}
	@endpush
	<x-auth-card class="py-6">
		<x-slot name="logo">
			<a href="/" class="flex flex-col justify-center gap-2">
				<x-application-logo class="self-center w-16 h-16 text-gray-500 fill-current" />
				<h1 class="text-xl font-bold text-center">Buat Akun Sekolah</h1>
			</a>
		</x-slot>

		<x-auth-validation-errors class="mb-4 text-center" :errors="$errors" />

		<form method="POST" action="{{ route('register') }}">
			@csrf

			<div>
				<x-alabel for="sekolah" :value="__('Nama Sekolah')" />

				<x-ainput id="sekolah" class="block w-full mt-1" type="text" name="sekolah" :value="old('sekolah')" required
					autofocus />
			</div>

			<div class="mt-4">
				<x-alabel for="name" :value="__('Nama Lengkap')" />

				<x-ainput id="name" class="block w-full mt-1" type="text" name="name" :value="old('name')" required autofocus />
			</div>

			<div class="mt-4">
				<x-alabel for="email" :value="__('Alamat Email')" />

				<x-ainput id="email" class="block w-full mt-1" type="email" name="email" :value="old('email')" required />
			</div>

			<div class="mt-4">
				<x-alabel for="password" :value="__('Password')" />

				<x-ainput id="password" class="block w-full mt-1" type="password" name="password" required
					autocomplete="new-password" />
			</div>

			<div class="mt-4">
				<x-alabel for="password_confirmation" :value="__('Ulang Password')" />

				<x-ainput id="password_confirmation" class="block w-full mt-1" type="password" name="password_confirmation"
					required />
			</div>

			<div class="flex justify-center mt-4">
				{!! NoCaptcha::display() !!}
			</div>

			<div class="flex items-center justify-end mt-4">
				<a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">
					{{ __('Sudah mendaftar?') }}
				</a>

				<x-abutton class="ml-4">
					{{ __('Daftar') }}
				</x-abutton>
			</div>
		</form>
	</x-auth-card>
</x-guest-layout>