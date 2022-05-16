<x-app-layout>
	<x-slot name="title">{{ $title }}</x-slot>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __($title) }}
		</h2>
	</x-slot>
	<div class="py-6">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-5">
			<div class="w-full shadow-sm bg-white border-b border-gray-200 rounded-lg overflow-hidden">
				<iframe src="/plugins/filemanager/dialog.php?type=0&fldr=" frameborder="0" width="100%" height="500"></iframe>
			</div>
		</div>
	</div>
</x-app-layout>