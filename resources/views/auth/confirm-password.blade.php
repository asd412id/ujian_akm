<x-guest-layout>
	<x-auth-card>
		<x-slot name="logo">
			<a href="/">
				<x-application-logo class="w-20 h-20 fill-current text-gray-500" />
			</a>
		</x-slot>

		<div class="mb-4 text-sm text-gray-600">
			{{ __('Silahkan konfirmasi password untuk melanjutkan.') }}
		</div>

		<!-- Validation Errors -->
		<x-auth-validation-errors class="mb-4" :errors="$errors" />

		<form method="POST" action="{{ route('password.confirm') }}">
			@csrf

			<!-- Password -->
			<div>
				<x-alabel for="password" :value="__('Password')" />

				<x-ainput id="password" class="block mt-1 w-full" type="password" name="password" required
					autocomplete="current-password" />
			</div>

			<div class="flex justify-end mt-4">
				<x-abutton>
					{{ __('Konfirmasi') }}
				</x-abutton>
			</div>
		</form>
	</x-auth-card>
</x-guest-layout>