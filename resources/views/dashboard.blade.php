<x-app-layout>
	<x-slot name="title">{{ $title }}</x-slot>
	<x-slot name="header">
		<h2 class="text-xl font-semibold leading-tight text-gray-800">
			{{ __($title) }}
		</h2>
	</x-slot>

	<div class="py-6">
		@livewire('sekolah')
	</div>
</x-app-layout>