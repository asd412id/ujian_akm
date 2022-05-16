<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-row md:flex-col gap-2">
	@include('pageattribute')
	<div class="w-full shadow-sm bg-white border-b border-gray-200 rounded-lg">
		@include('jadwal.table')
	</div>
	@include('jadwal.modal')
	<div class="flex justify-end">
		{!! $data->links() !!}
	</div>
</div>