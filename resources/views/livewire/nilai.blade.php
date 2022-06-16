<div class="flex flex-col gap-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
	<div class="flex items-center w-full gap-2">
		<div class="w-full p-2 md:p-0">
			<x-input type="search" wire:model.debounce.500ms='search' placeholder="Cari Data" right-icon="search" />
		</div>
		<x-button primary label="Kalkulasi Ulang Nilai" class="whitespace-nowrap" wire:click='calculateScore' />
	</div>
	<div class="w-full bg-white border-b border-gray-200 rounded-lg shadow-sm">
		<table class="w-full text-left border-collapse">
			<thead>
				<tr>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Nama</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Lama Pengerjaan</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Sisa Waktu</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Status</th>
					<th
						class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Aksi</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($data as $key => $v)
				@php
				$dl = $v->logins()->where('jadwal_id',$jadwal->id)->first();
				$cdown = null;
				$totalscore = 0;
				if ($dl) {
				$totalscore = $dl->tests()->select(DB::raw('SUM(pscore) as nilai'))->get()[0]->nilai;
				$cdown = $dl->reset == 2 || $dl->end ?
				now()->addMinutes($jadwal->duration)->subSeconds($dl->start->diffInSeconds($dl->created_at))->getPreciseTimestamp(3)
				:
				$dl->created_at->addMinutes($jadwal->duration)->getPreciseTimestamp(3);
				}
				@endphp
				<tr class="{{ $dl && $dl->changed_nilai ? 'bg-amber-100' : 'hover:bg-gray-100' }}"
					x-data="{cdown: @js($cdown), countdown: null}">
					<td class="px-6 py-4 border-b border-gray-100">
						<div class="flex flex-col">
							<span>{{ $v->name }}</span>
							<div class="-mt-2">
								<em class="px-1 text-xs italic border rounded-md border-limcyane-100 bg-cyan-50 text-cyan-600">{{
									$v->uid }}</em>
							</div>
							<div class="-mt-1">
								<em class="px-1 text-xs border rounded-md border-lime-100 bg-lime-50 text-lime-600">{{ $v->ruang }}</em>
							</div>
						</div>
					</td>
					<td class="px-6 py-4 border-b border-gray-100">
						<div class="flex items-center gap-1">
							@if ($dl && $dl->reset != 3)
							<span
								class="px-1 text-sm border rounded-lg shadow-md bg-positive-50 border-positive-200 text-positive-700">{{
								$dl->start->diffInMinutes($dl->end) }} Menit</span>
							@endif
						</div>
					</td>
					<td class="px-6 py-4 border-b border-gray-100">
						@if ($dl && $dl->reset != 3)
						<div class="flex items-center">
							<div class="flex gap-1 !font-bold border border-sky-100 bg-sky-50 text-sky-600 px-2 rounded-md shadow-md"
								x-ref="timer{{ $dl->id }}" x-init="$nextTick(()=>{
									countdown = timer(cdown);
									countdown.init();
									if(@js($dl->reset) == 2 && @js(is_null($dl->end))){
										$refs.timer{{ $dl->id }}.classList.remove('border-sky-100');
										$refs.timer{{ $dl->id }}.classList.remove('bg-sky-50');
										$refs.timer{{ $dl->id }}.classList.remove('text-sky-600');
										$refs.timer{{ $dl->id }}.classList.add('border-yellow-100');
										$refs.timer{{ $dl->id }}.classList.add('bg-yellow-50');
										$refs.timer{{ $dl->id }}.classList.add('text-yellow-500');
										countdown.stop();
									}else if(@js(!is_null($dl->end))){
										$refs.timer{{ $dl->id }}.classList.remove('border-sky-100');
										$refs.timer{{ $dl->id }}.classList.remove('bg-sky-50');
										$refs.timer{{ $dl->id }}.classList.remove('text-sky-600');
										$refs.timer{{ $dl->id }}.classList.add('border-gray-100');
										$refs.timer{{ $dl->id }}.classList.add('bg-gray-50');
										$refs.timer{{ $dl->id }}.classList.add('text-gray-500');
										countdown.stop();
									}
								})">
								<div class="hidden" x-text="
									if(countdown!=null){
										if(Number(countdown.time().seconds) <= 0 && Number(countdown.time().minutes) <= 0 && Number(countdown.time().hours) <= 0 && Number(countdown.time().days) <= 0){
											countdown = null;
										}
									}
								"></div>
								<h1 x-text="countdown!=null?countdown.time().days:'00'">00</h1>:
								<h1 x-text="countdown!=null?countdown.time().hours:'00'">00</h1>:
								<h1 x-text="countdown!=null?countdown.time().minutes:'00'">00</h1>:
								<h1 x-text="countdown!=null?countdown.time().seconds:'00'">00</h1>
							</div>
						</div>
						@endif
					</td>
					<td class="px-6 py-4 border-b border-gray-100">
						@if ($dl && $dl->reset != 3)
						<div class="flex flex-col gap-1">
							<div class="flex">
								<div class="px-2 font-bold border rounded-lg shadow-md bg-rose-50 text-rose-600 border-rose-100">
									Skor: {{ round($totalscore,2)??0 }}
								</div>
							</div>
							<div class="flex gap-1">
								<div
									class="px-1 text-xs font-bold border rounded-md shadow-md bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100">
									Dikerja: {{
									$dl->tests()
									->where('peserta_id',$dl->peserta->id)
									->where(function($q){
									$q->whereNotNull('correct')->orWhereNotNull('relation')->orWhereNotNull('answer');
									})
									->count().'/'.count($dl->soals())
									}}
								</div>
							</div>
						</div>
						@endif
					</td>
					<td class="px-6 py-4 border-b border-gray-100">
						@if ($dl)
						<div class="flex flex-wrap justify-end gap-1">
							<x-button.circle sm primary icon="pencil-alt" title="Input Nilai" wire:click='inputNilai({{ $dl->id }})'
								wire:target='inputNilai' />
						</div>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@include('jadwal.nilai')
</div>