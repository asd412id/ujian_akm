<x-guest-layout>
	<x-slot name="title">Masuk Halaman Admin</x-slot>
	<x-auth-card class="py-6">
		<x-slot name="logo">
			<a href="/" class="flex flex-col gap-2 justify-center">
				<x-application-logo class="w-16 h-16 fill-current text-gray-500 self-center" />
				<h1 class="text-lg font-bold text-center">Masuk Halaman Admin</h1>
			</a>
		</x-slot>

		<!-- Session Status -->
		<x-auth-session-status class="mb-4 text-center" :status="session('status')" />

		<!-- Validation Errors -->
		<x-auth-validation-errors class="mb-4 text-center" :errors="$errors" />

		<form method="POST" action="{{ route('login') }}">
			@csrf

			<!-- Email Address -->
			<div>
				<x-alabel for="email" :value="__('Alamat Email')" />

				<x-ainput id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
					autofocus />
			</div>

			<!-- Password -->
			<div class="mt-4">
				<x-alabel for="password" :value="__('Password')" />

				<x-ainput id="password" class="block mt-1 w-full" type="password" name="password" required
					autocomplete="current-password" />
			</div>

			<!-- Remember Me -->
			<div class="block mt-4">
				<label for="remember_me" class="inline-flex items-center">
					<input id="remember_me" type="checkbox"
						class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
						name="remember">
					<span class="ml-2 text-sm text-gray-600">{{ __('Ingat Saya') }}</span>
				</label>
			</div>

			<div class="flex items-center justify-end mt-4">
				@if (Route::has('password.request'))
				<a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
					{{ __('Lupa password?') }}
				</a>
				@endif

				<x-abutton class="ml-3">
					{{ __('Masuk') }}
				</x-abutton>
			</div>
		</form>
		@if (Route::has('register'))
		<div class="text-right mt-3 text-sm">
			<a class="underline" href="{{ route('register') }}">Belum punya akun? Daftar sekarang!</a>
		</div>
		@endif
		<div class="text-right mt-3 text-sm">
			<a class="underline" href="{{ route('index') }}">Anda peserta ujian? Masuk Di Sini!</a>
		</div>
	</x-auth-card>
</x-guest-layout>