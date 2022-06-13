<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-2">
	<div class="flex gap-2 w-full items-center">
		<div class="w-full p-2 md:p-0">
			<x-input type="search" wire:model.debounce.500ms='search' placeholder="Cari Data" right-icon="search" />
		</div>
		<x-button primary label="Kalkulasi Ulang Nilai" class="whitespace-nowrap" wire:click='calculateScore' />
	</div>
	<div class="w-full shadow-sm bg-white border-b border-gray-200 rounded-lg">
		<table class="text-left w-full border-collapse">
			<thead>
				<tr>
					<th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
						Nama</th>
					<th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
						Lama Pengerjaan</th>
					<th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
						Sisa Waktu</th>
					<th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
						Status</th>
					<th
						class="text-right py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
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
				<tr class="{{ !$dl || ($dl && $dl->reset == 3) ? 'bg-gray-50 opacity-30 italic' : 'hover:bg-gray-100' }}"
					wire:ignore wire:key='{{ $totalscore }}' x-data="{cdown: @js($cdown), countdown: null}">
					<td class="py-4 px-6 border-b border-gray-100">
						<div class="flex flex-col">
							<span>{{ $v->name }}</span>
							<div class="-mt-2">
								<em class="text-xs italic rounded-md px-1 border border-limcyane-100 bg-cyan-50 text-cyan-600">{{
									$v->uid }}</em>
							</div>
							<div class="-mt-1">
								<em class="text-xs rounded-md px-1 border border-lime-100 bg-lime-50 text-lime-600">{{ $v->ruang }}</em>
							</div>
						</div>
					</td>
					<td class="py-4 px-6 border-b border-gray-100">
						<div class="flex gap-1 items-center">
							@if ($dl && $dl->reset != 3)
							<span
								class="text-sm bg-positive-50 border border-positive-200 shadow-md text-positive-700 px-1 rounded-lg">{{
								$dl->start->diffInMinutes($dl->end) }} Menit</span>
							@endif
						</div>
					</td>
					<td class="py-4 px-6 border-b border-gray-100">
						@if ($dl && $dl->reset != 3)
						<div class="flex items-center">
							<div class="flex gap-1 !font-bold border border-sky-100 bg-sky-50 text-sky-600 px-2 rounded-md shadow-md"
								x-ref="timer{{ $dl->id }}" x-init="$nextTick(()=>{
									countdown = timer(cdown);
									countdown.init();
									if(@js($dl->reset) == 2 || @js($dl->end)){
										$refs.timer{{ $dl->id }}.classList.add('border-orange-100');
										$refs.timer{{ $dl->id }}.classList.add('bg-orange-50');
										$refs.timer{{ $dl->id }}.classList.add('text-orange-500');
										countdown.stop();
									}
								})">
								<div class="hidden" x-text="
									if(countdown!=null){
										if(Number(countdown.time().minutes) < 10 && countdown.time().hours == '00' && countdown.time().days == '00'){
											$refs.timer{{ $dl->id }}.classList.remove('border-sky-100');
											$refs.timer{{ $dl->id }}.classList.remove('bg-sky-50');
											$refs.timer{{ $dl->id }}.classList.remove('text-sky-600');
											$refs.timer{{ $dl->id }}.classList.add('border-red-100');
											$refs.timer{{ $dl->id }}.classList.add('bg-red-50');
											$refs.timer{{ $dl->id }}.classList.add('text-red-600');
										}
										
										if(countdown.time().seconds == '00' && countdown.time().minutes == '00' && countdown.time().hours == '00' && countdown.time().days == '00'){
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
					<td class="py-4 px-6 border-b border-gray-100">
						@if ($dl && $dl->reset != 3)
						<div class="flex flex-col gap-1">
							<div class="flex">
								<div class="font-bold bg-rose-50 text-rose-600 border border-rose-100 px-2 rounded-lg shadow-md">
									Skor: {{ round($totalscore,2)??0 }}
								</div>
							</div>
							<div class="flex gap-1">
								<div
									class="text-xs font-bold bg-fuchsia-50 text-fuchsia-600 border border-fuchsia-100 px-1 rounded-md shadow-md">
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
					<td class="py-4 px-6 border-b border-gray-100">
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