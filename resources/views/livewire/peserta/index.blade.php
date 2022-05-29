<div class="flex flex-col md:flex-row gap-3 md:gap-5">
	@if (session()->has('msg'))
	<div
		class="absolute top-14 shadow-md w-full max-w-sm right-5 z-50 py-3 px-5 rounded-md bg-amber-100 text-amber-600 border border-amber-100"
		x-data x-init="setTimeout(()=>{$el.classList.add('hidden')},5000)">
		<div class="flex gap-2 items-center">
			<x-icon name="information-circle" class="w-5 h-5" />
			{{ session()->get('msg') }}
		</div>
	</div>
	@endif
	<div class="w-full">
		<div class="w-full shadow-md bg-primary-50 border border-primary-100 text-primary-600 rounded-lg p-5">
			<div class="italic -mb-1">Selamat Datang,</div>
			<div class="font-bold text-3xl">{{ $user->name }}</div>
			<table class="mt-3 w-full">
				<tr>
					<td class="pr-2 py-1 align-top">ID Peserta</td>
					<td class="pr-2 py-1 align-top">:</td>
					<th class="text-left pl-2 py-1 align-top">{{ $user->uid }}</th>
				</tr>
				<tr>
					<td class="pr-2 py-1 align-top">Nama Lengkap</td>
					<td class="pr-2 py-1 align-top">:</td>
					<th class="text-left pl-2 py-1 align-top">{{ $user->name }}</th>
				</tr>
				<tr>
					<td class="pr-2 py-1 align-top">Jenis Kelamin</td>
					<td class="pr-2 py-1 align-top">:</td>
					<th class="text-left pl-2 py-1 align-top">{{ $user->jk == 'L'? 'Laki-Laki' : 'Perempuan' }}</th>
				</tr>
				<tr>
					<td class="pr-2 py-1 align-top">Ruang</td>
					<td class="pr-2 py-1 align-top">:</td>
					<th class="text-left pl-2 py-1 align-top">{{ $user->ruang }}</th>
				</tr>
				<tr>
					<td class="pr-2 py-1 align-top">Banyak ujian yang telah diikuti</td>
					<td class="pr-2 py-1 align-top">:</td>
					<th class="text-left pl-2 py-1 align-top">{{
						$user->logins()->whereNotNull('start')->whereNotNull('end')->count()
						}}</th>
				</tr>
			</table>
		</div>
	</div>
	<div class="w-full">
		<div class="shadow-md bg-white border-b border-gray-200 rounded-lg p-5">
			<div class="font-bold text-2xl underline underline-offset-8">Daftar Ujian</div>
			<div class="mt-5 flex flex-col gap-3" wire:poll.keep-alive='checkJadwal'>
				@if (is_null($this->login))
				@forelse ($jadwal as $j)
				<a href="#" wire:click.prevent='join({{ $j->id }})'>
					<x-card cardClasses="bg-purple-50 border-purple-100 cursor-pointer hover:bg-purple-100"
						wire:loading.class='cursor-not-allowed opacity-50' wire:target='join'>
						<div class="flex flex-col gap-1">
							<div class="flex flex-col mb-2">
								<span class="font-bold text-lg">{{ $j->name }}</span>
								<em class="-mt-1 text-sm align-top text-gray-500">{{ $j->opt['desc']??null }}</em>
							</div>
							<table>
								<tr>
									<td class="text-sm align-top">Durasi</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<span
											class="text-sm align-top bg-sky-50 border border-sky-200 shadow-md text-sky-700 px-1 rounded-md">{{
											$j->duration.' Menit' }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Jumlah Soal</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<div class="flex flex-wrap gap-1">
											<span
												class="text-sm align-top bg-rose-50 border border-rose-200 shadow-md text-rose-700 px-1 rounded-md">{{
												$j->soal_count.' Nomor' }}</span>
											@php
											$type =
											$j->item_soals($j->soals->pluck('id')->toArray())->select('type')->distinct('type')->get()->pluck('type')->toArray();
											@endphp
											@if (count($type))
											<span
												class="text-sm align-top bg-fuchsia-50 border border-fuchsia-200 shadow-md text-fuchsia-700 px-1 rounded-lg">{!!
												implode('</span> <span
												class="text-sm align-top bg-fuchsia-50 border border-fuchsia-200 shadow-md text-fuchsia-700 px-1 rounded-md">',array_map(function($v){return
												strtoupper($v);},$type))
												!!}</span>
											@endif
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Waktu Pelaksanaan</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<span
											class="text-sm align-top bg-amber-50 border border-amber-200 shadow-md text-amber-700 px-1 rounded-md">{{
											$j->start->format('d/m/Y H:i')
											}} - {{
											$j->end->format('d/m/Y H:i') }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Ruangan</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<span
											class="text-sm align-top bg-lime-50 border border-lime-200 shadow-md text-lime-700 px-1 rounded-md">{!!
											implode('</span> <span
											class="text-sm align-top bg-lime-50 border border-lime-200 shadow-md text-lime-700 px-1 rounded-md">
											',array_unique($j->pesertas()->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray()))
											!!}</span>
									</td>
								</tr>
							</table>
						</div>
					</x-card>
				</a>
				@empty
				<x-card cardClasses="bg-sky-50 border-sky-100">
					<div class="flex gap-2 items-center text-sky-600">
						<x-icon name="information-circle" class="w-5 h-5" /> Kamu tidak memiliki jadwal ujian!
					</div>
				</x-card>
				@endforelse
				@foreach ($logins as $j)
				<x-card cardClasses="bg-gray-50 border-gray-100 cursor-pointer hover:bg-gray-100 opacity-50"
					wire:loading.class='cursor-not-allowed'>
					<div class="flex flex-col gap-1">
						<h1
							class="font-bold bg-positive-50 border border-positive-100 text-positive-600 py-1 px-3 rounded-lg justify-between items-center flex gap-1">
							@if ($j->jadwal->show_score)
							SKOR: {{ round($j->tests()->select(DB::raw('SUM(pscore) as nilai'))->get()[0]->nilai,2)??0 }}
							@endif
							<div class="flex gap-1 items-center">
								<x-icon name="check-circle" class="w-5 h-5" />
								UJIAN SELESAI
							</div>
						</h1>
						<div class="flex flex-col mb-2">
							<span class="font-bold text-lg">{{ $j->jadwal->name }}</span>
							<em class="-mt-1 text-sm align-top text-gray-500">{{ $j->jadwal->opt['desc']??null }}</em>
						</div>
						<table>
							<tr>
								<td class="text-sm align-top">Durasi</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top">
									<span
										class="text-sm align-top bg-sky-50 border border-sky-200 shadow-md text-sky-700 px-1 rounded-md">{{
										$j->jadwal->duration.' Menit' }}</span>
								</td>
							</tr>
							<tr>
								<td class="text-sm align-top">Soal Dikerjakan</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top">
									<span
										class="text-sm align-top bg-rose-50 border border-rose-200 shadow-md text-rose-700 px-1 rounded-md">{{
										$j->tests()
										->where('peserta_id',$j->peserta->id)
										->where(function($q){
										$q->whereNotNull('correct')->orWhereNotNull('relation')->orWhereNotNull('answer');
										})
										->count().'
										/
										'.$j->jadwal->soal_count.' Nomor' }}</span>
								</td>
							</tr>
							<tr>
								<td class="text-sm align-top">Waktu Pengerjaan</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top">
									<span
										class="text-sm align-top bg-amber-50 border border-amber-200 shadow-md text-amber-700 px-1 rounded-md">{{
										$j->start->format('d/m/Y H:i')
										}} - {{
										$j->end->format('d/m/Y H:i') }}</span>
								</td>
							</tr>
							<tr>
								<td class="text-sm align-top">Ruangan</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top">
									<span
										class="text-sm align-top bg-lime-50 border border-lime-200 shadow-md text-lime-700 px-1 rounded-md">{!!
										implode('</span> <span
										class="text-sm align-top bg-lime-50 border border-lime-200 shadow-md text-lime-700 px-1 rounded-md">
										',array_unique($j->jadwal->pesertas()->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray()))
										!!}</span>
								</td>
							</tr>
						</table>
					</div>
				</x-card>
				@endforeach
				@else
				@php
				$loginJadwal = $login->jadwal;
				@endphp
				<a href="#" wire:click.prevent='join({{ $loginJadwal->id }})'>
					<x-card cardClasses="bg-negative-50 border-negative-100 cursor-pointer hover:bg-negative-100"
						wire:loading.class='cursor-not-allowed opacity-50' wire:target='join'>
						<div class="flex flex-col gap-1">
							<div class="flex flex-col md:flex-row gap-1 items-center" x-data="{countdown: null}" x-init="$nextTick(()=>{
								countdown = timer({{ $login->reset == 2 ?
								now()->addMinutes($loginJadwal->duration)->subSeconds($login->start->diffInSeconds($login->created_at))->getPreciseTimestamp(3) :
								$login->created_at->addMinutes($loginJadwal->duration)->getPreciseTimestamp(3) }});
								countdown.init();
								if(@js($login->reset) == 2){
									countdown.stop();
								}
							})">
								<div class="hidden" x-text="
									if(countdown!=null){
										if(countdown.time().seconds == '00' && countdown.time().minutes == '00' && countdown.time().hours == '00' && countdown.time().days == '00'){
											$wire.checkJadwal();
											countdown = null;
										}
									}
								"></div>
								<h1 class="font-bold text-lg italic pr-2 text-amber-600">
									@if ($login->reset == 2)
									Sisa Waktu Ujian
									@else
									Ujian Sedang Berlangsung
									@endif
								</h1>
								<div
									class="flex gap-1 py-1 px-3 bg-amber-50 border border-amber-100 text-amber-600 rounded-lg shadow-md mb-2 md:mb-0">
									<h1 x-text="countdown!=null?countdown.time().days:'00'">00</h1>:
									<h1 x-text="countdown!=null?countdown.time().hours:'00'">00</h1>:
									<h1 x-text="countdown!=null?countdown.time().minutes:'00'">00</h1>:
									<h1 x-text="countdown!=null?countdown.time().seconds:'00'">00</h1>
								</div>
							</div>
							<div class="flex flex-col mb-2">
								<span class="font-bold text-lg">{{ $loginJadwal->name }}</span>
								<em class="-mt-1 text-sm align-top text-gray-500">{{ $loginJadwal->opt['desc']??null }}</em>
							</div>
							<table>
								<tr>
									<td class="text-sm align-top">Durasi</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<span
											class="text-sm align-top bg-sky-50 border border-sky-200 shadow-md text-sky-700 px-1 rounded-md">{{
											$loginJadwal->duration.' Menit' }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Jumlah Soal</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<div class="flex flex-wrap gap-1">
											<span
												class="text-sm align-top bg-rose-50 border border-rose-200 shadow-md text-rose-700 px-1 rounded-md">{{
												$login->tests()->whereNotNull('correct')->orWhereNotNull('relation')->orWhereNotNull('answer')->count().'
												/
												'.$loginJadwal->soal_count.' Nomor' }}</span>
											@php
											$type =
											$loginJadwal->item_soals($loginJadwal->soals->pluck('id')->toArray())->select('type')->distinct('type')->get()->pluck('type')->toArray();
											@endphp
											@if (count($type))
											<span
												class="text-sm align-top bg-fuchsia-50 border border-fuchsia-200 shadow-md text-fuchsia-700 px-1 rounded-lg">{!!
												implode('</span> <span
												class="text-sm align-top bg-fuchsia-50 border border-fuchsia-200 shadow-md text-fuchsia-700 px-1 rounded-md">',array_map(function($v){return
												strtoupper($v);},$type))
												!!}</span>
											@endif
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Waktu Pengerjaan</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<span
											class="text-sm align-top bg-amber-50 border border-amber-200 shadow-md text-amber-700 px-1 rounded-md">{{
											$loginJadwal->start->format('d/m/Y H:i')
											}} - {{
											$loginJadwal->end->format('d/m/Y H:i') }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Ruangan</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<span
											class="text-sm align-top bg-lime-50 border border-lime-200 shadow-md text-lime-700 px-1 rounded-md">{!!
											implode('</span> <span
											class="text-sm align-top bg-lime-50 border border-lime-200 shadow-md text-lime-700 px-1 rounded-md">
											',array_unique($loginJadwal->pesertas()->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray()))
											!!}</span>
									</td>
								</tr>
							</table>
						</div>
					</x-card>
				</a>
				@endif
			</div>
		</div>
	</div>
</div>