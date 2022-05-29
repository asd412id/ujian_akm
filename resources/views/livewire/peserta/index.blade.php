<div class="flex flex-col gap-3 md:flex-row md:gap-5" x-init="
if(@js($user->sekolah->restrict_test && (!is_null($login) && !is_null($login->start) && is_null($login->end)))){
	window.onblur=function(){$wire.stop()};
}
">
	@if (session()->has('msg'))
	<div
		class="absolute z-50 w-full px-5 py-3 border rounded-md shadow-md md:max-w-sm top-14 md:right-5 bg-amber-100 text-amber-600 border-amber-100"
		x-data x-init="setTimeout(()=>{$el.classList.add('hidden')},5000)">
		<div class="flex items-center gap-2">
			<x-icon name="information-circle" class="w-5 h-5" />
			{{ session()->get('msg') }}
		</div>
	</div>
	@endif
	<div class="w-full">
		<div class="w-full p-5 border rounded-lg shadow-md bg-primary-50 border-primary-100 text-primary-600">
			<div class="-mb-1 italic">Selamat Datang,</div>
			<div class="text-3xl font-bold">{{ $user->name }}</div>
			<table class="w-full mt-3">
				<tr>
					<td class="py-1 pr-2 align-top">ID Peserta</td>
					<td class="py-1 pr-2 align-top">:</td>
					<th class="py-1 pl-2 text-left align-top">{{ $user->uid }}</th>
				</tr>
				<tr>
					<td class="py-1 pr-2 align-top">Nama Lengkap</td>
					<td class="py-1 pr-2 align-top">:</td>
					<th class="py-1 pl-2 text-left align-top">{{ $user->name }}</th>
				</tr>
				<tr>
					<td class="py-1 pr-2 align-top">Jenis Kelamin</td>
					<td class="py-1 pr-2 align-top">:</td>
					<th class="py-1 pl-2 text-left align-top">{{ $user->jk == 'L'? 'Laki-Laki' : 'Perempuan' }}</th>
				</tr>
				<tr>
					<td class="py-1 pr-2 align-top">Ruang</td>
					<td class="py-1 pr-2 align-top">:</td>
					<th class="py-1 pl-2 text-left align-top">{{ $user->ruang }}</th>
				</tr>
				<tr>
					<td class="py-1 pr-2 align-top">Banyak ujian yang telah diikuti</td>
					<td class="py-1 pr-2 align-top">:</td>
					<th class="py-1 pl-2 text-left align-top">{{
						$user->logins()->whereNotNull('start')->whereNotNull('end')->count()
						}}</th>
				</tr>
			</table>
		</div>
		@if ($user->sekolah->restrict_test && count($jadwal))
		<div class="w-full p-5 mt-3 border rounded-lg shadow-md bg-amber-50 border-amber-100 text-amber-600">
			<div class="flex items-center gap-2 text-xl font-bold text-red-600">
				<x-icon name="exclamation" solid class="w-9 h-9" />Peringatan!
			</div>
			<div class="mt-2 text-red-600">
				Saat mengikuti ujian, peserta dilarang untuk meninggalkan halaman ujian, membuka aplikasi lain, membuka
				halaman lain, dan atau mematikan layar perangkat!
			</div>
			<div class="mt-2 text-red-600">
				Apabila peserta meninggalkan halaman ujian, maka ujian akan selesai secara otomatis dan semua jawaban terakhir
				akan disimpan.
			</div>
		</div>
		@endif
	</div>
	<div class="w-full ">
		<div class="p-5 bg-white border-b border-gray-200 rounded-lg shadow-md">
			<div class="text-2xl font-bold underline underline-offset-8">Daftar Ujian</div>
			<div class="flex flex-col gap-3 mt-5" wire:poll.keep-alive='checkJadwal'>
				@if (is_null($login))
				@forelse ($jadwal as $j)
				<a href="#" wire:click.prevent='join({{ $j->id }})'>
					<x-card cardClasses="bg-purple-50 border-purple-100 cursor-pointer hover:bg-purple-100"
						wire:loading.class='opacity-50 cursor-not-allowed' wire:target='join'>
						<div class="flex flex-col gap-1">
							<div class="flex flex-col mb-2">
								<span class="text-lg font-bold">{{ $j->name }}</span>
								<em class="-mt-1 text-sm text-gray-500 align-top">{!! nl2br($j->desc) !!}</em>
							</div>
							<table>
								<tr>
									<td class="text-sm align-top">Durasi</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top whitespace-nowrap">
										<span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-sky-50 border-sky-200 text-sky-700">{{
											$j->duration.' Menit' }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Soal</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<div class="flex flex-wrap gap-1">
											<span
												class="px-1 text-sm align-top border rounded-md shadow-md bg-rose-50 border-rose-200 text-rose-700">{{
												$j->soal_count.' Nomor' }}</span>
											@php
											$type =
											$j->item_soals($j->soals->pluck('id')->toArray())->select('type')->distinct('type')->get()->pluck('type')->toArray();
											@endphp
											@if (count($type))
											<span
												class="px-1 text-sm align-top border rounded-lg shadow-md bg-fuchsia-50 border-fuchsia-200 text-fuchsia-700">{!!
												implode('</span> <span
												class="px-1 text-sm align-top border rounded-md shadow-md bg-fuchsia-50 border-fuchsia-200 text-fuchsia-700">',array_map(function($v){return
												strtoupper($v);},$type))
												!!}</span>
											@endif
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Waktu</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top whitespace-nowrap">
										<span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-amber-50 border-amber-200 text-amber-700">{{
											$j->start->format('d/m/Y H:i')
											}} - {{
											$j->end->format('d/m/Y H:i') }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Ruangan</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top whitespace-nowrap">
										<span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-lime-50 border-lime-200 text-lime-700">{!!
											implode('</span> <span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-lime-50 border-lime-200 text-lime-700">
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
					<div class="flex items-center gap-2 text-sky-600">
						<x-icon name="information-circle" class="w-5 h-5" /> Kamu tidak memiliki jadwal ujian!
					</div>
				</x-card>
				@endforelse
				@foreach ($logins as $j)
				<x-card cardClasses="bg-gray-50 border-gray-100 cursor-pointer hover:bg-gray-100 opacity-50"
					wire:loading.class='cursor-not-allowed'>
					<div class="flex flex-col gap-1">
						<h1
							class="flex items-center justify-between gap-1 px-3 py-1 font-bold border rounded-lg bg-positive-50 border-positive-100 text-positive-600">
							@if ($j->jadwal->show_score)
							SKOR: {{ round($j->tests()->select(DB::raw('SUM(pscore) as nilai'))->get()[0]->nilai,2)??0 }}
							@endif
							<div class="flex items-center gap-1">
								<x-icon name="check-circle" class="w-5 h-5" />
								UJIAN SELESAI
							</div>
						</h1>
						<div class="flex flex-col mb-2">
							<span class="text-lg font-bold">{{ $j->jadwal->name }}</span>
							<em class="-mt-1 text-sm text-gray-500 align-top">{!! nl2br($j->jadwal->desc) !!}</em>
						</div>
						<table>
							<tr>
								<td class="text-sm align-top">Durasi</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top whitespace-nowrap">
									<span
										class="px-1 text-sm align-top border rounded-md shadow-md bg-sky-50 border-sky-200 text-sky-700">{{
										$j->jadwal->duration.' Menit' }}</span>
								</td>
							</tr>
							<tr>
								<td class="text-sm align-top">Soal</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top whitespace-nowrap">
									<span
										class="px-1 text-sm align-top border rounded-md shadow-md bg-rose-50 border-rose-200 text-rose-700">{{
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
								<td class="text-sm align-top">Waktu</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top whitespace-nowrap">
									<span
										class="px-1 text-sm align-top border rounded-md shadow-md bg-amber-50 border-amber-200 text-amber-700">{{
										$j->start->format('d/m/Y H:i')
										}} - {{
										$j->end->format('d/m/Y H:i') }}</span>
								</td>
							</tr>
							<tr>
								<td class="text-sm align-top">Ruangan</td>
								<td class="text-sm align-top">:</td>
								<td class="pt-1 text-sm align-top whitespace-nowrap">
									<span
										class="px-1 text-sm align-top border rounded-md shadow-md bg-lime-50 border-lime-200 text-lime-700">{!!
										implode('</span> <span
										class="px-1 text-sm align-top border rounded-md shadow-md bg-lime-50 border-lime-200 text-lime-700">
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
						wire:loading.class='opacity-50 cursor-not-allowed' wire:target='join'>
						<div class="flex flex-col gap-1">
							<div class="flex flex-col items-center gap-1 md:flex-row" x-data="{countdown: null}" x-init="$nextTick(()=>{
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
								<h1 class="pr-2 text-lg italic font-bold text-amber-600">
									@if ($login->reset == 2)
									Sisa Waktu Ujian
									@else
									Ujian Sedang Berlangsung
									@endif
								</h1>
								<div
									class="flex gap-1 px-3 py-1 mb-2 border rounded-lg shadow-md bg-amber-50 border-amber-100 text-amber-600 md:mb-0">
									<h1 x-text="countdown!=null?countdown.time().days:'00'">00</h1>:
									<h1 x-text="countdown!=null?countdown.time().hours:'00'">00</h1>:
									<h1 x-text="countdown!=null?countdown.time().minutes:'00'">00</h1>:
									<h1 x-text="countdown!=null?countdown.time().seconds:'00'">00</h1>
								</div>
							</div>
							<div class="flex flex-col mb-2">
								<span class="text-lg font-bold">{{ $loginJadwal->name }}</span>
								<em class="-mt-1 text-sm text-gray-500 align-top">{!! nl2br($loginJadwal->desc) !!}</em>
							</div>
							<table>
								<tr>
									<td class="text-sm align-top">Durasi</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top whitespace-nowrap">
										<span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-sky-50 border-sky-200 text-sky-700">{{
											$loginJadwal->duration.' Menit' }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Soal</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top">
										<div class="flex flex-wrap gap-1">
											<span
												class="px-1 text-sm align-top border rounded-md shadow-md bg-rose-50 border-rose-200 text-rose-700">{{
												$login->tests()->whereNotNull('correct')->orWhereNotNull('relation')->orWhereNotNull('answer')->count().'
												/
												'.$loginJadwal->soal_count.' Nomor' }}</span>
											@php
											$type =
											$loginJadwal->item_soals($loginJadwal->soals->pluck('id')->toArray())->select('type')->distinct('type')->get()->pluck('type')->toArray();
											@endphp
											@if (count($type))
											<span
												class="px-1 text-sm align-top border rounded-lg shadow-md bg-fuchsia-50 border-fuchsia-200 text-fuchsia-700">{!!
												implode('</span> <span
												class="px-1 text-sm align-top border rounded-md shadow-md bg-fuchsia-50 border-fuchsia-200 text-fuchsia-700">',array_map(function($v){return
												strtoupper($v);},$type))
												!!}</span>
											@endif
										</div>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Waktu</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top whitespace-nowrap">
										<span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-amber-50 border-amber-200 text-amber-700">{{
											$loginJadwal->start->format('d/m/Y H:i')
											}} - {{
											$loginJadwal->end->format('d/m/Y H:i') }}</span>
									</td>
								</tr>
								<tr>
									<td class="text-sm align-top">Ruangan</td>
									<td class="text-sm align-top">:</td>
									<td class="pt-1 text-sm align-top whitespace-nowrap">
										<span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-lime-50 border-lime-200 text-lime-700">{!!
											implode('</span> <span
											class="px-1 text-sm align-top border rounded-md shadow-md bg-lime-50 border-lime-200 text-lime-700">
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