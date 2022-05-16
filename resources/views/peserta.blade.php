<x-guest-layout>
	<x-slot name="title">{{ $title }}</x-slot>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __($title) }}
		</h2>
	</x-slot>

	<div class="py-6">
		{{ auth()->user()->with('jadwals')->first() }}
	</div>
</x-guest-layout>