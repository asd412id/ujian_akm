<div class="flex flex-col-reverse md:flex-row gap-3 w-full" x-init="window.onblur=function(){console.log('cheated')}">
	<div class="w-full md:w-9/12">
		<div class="w-full shadow-md bg-white border border-gray-100 rounded-lg p-5 flex flex-col gap-3" wire:ignore
			wire:key='soal{{ $soal->id.$soal->updated_at->timestamp }}'>
			<div class="hidden" x-data x-init="
			$nextTick(()=>{
				if(Object.keys(lines).length > 0){
					for(let i in lines){
						if(!i.includes('_'+@js($soal->id))){
							lines[i] = removeLine(lines[i], i);
							delete lines[i];
						}
					};
				}
				window.scrollTo({ top: 0, behavior: 'smooth' });
			})
			"></div>
			<h2 class="font-bold text-lg">#Soal {{$login->current_number+1}} ({{ $type }})</h2>
			<div class="flex flex-col gap-3">
				<div class="w-full">{!! shortcode($soal->text) !!}</div>

				@if ((strtolower($soal->type)=='pg' || strtolower($soal->type)=='pgk') && is_array($soal->option))
				<div class="flex flex-col gap-2">
					@foreach ($soal->option as $key => $o)
					<label class="flex items-center gap-2">
						@if (strtolower($soal->type)=='pg')
						<input type="radio"
							class="form-radio rounded-full transition ease-in-out duration-100 border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600 dark:focus:ring-offset-secondary-800"
							wire:model.defer="choices" name='choice' value="{{ $key }}">
						@else
						<x-checkbox wire:model.defer="choices" value="{{ $key }}" />
						@endif
						<span>{!! shortcode($o) !!}</span>
					</label>
					@endforeach
				</div>
				@elseif (strtolower($soal->type)=='bs')
				<table class="w-full">
					<thead>
						<tr>
							<th class="py-2 px-3 border border-gray-400 border-b-2 bg-gray-200">{{
								isset($soal->label[0])?$soal->label[0]:'Pernyataan' }}</th>
							<th class="py-2 px-3 border border-gray-400 border-b-2 bg-gray-200" colspan="2">Jawaban</th>
						</tr>
					</thead>
					@forelse ($soal->option as $key => $s)
					<tr>
						<td class="py-2 px-3 border border-gray-400">{!! shortcode($s) !!}</td>
						<td class="py-2 px-3 border border-gray-400">
							<label class="flex gap-2 items-center justify-center">
								<input type="radio"
									class="form-radio rounded-full transition ease-in-out duration-100 border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600 dark:focus:ring-offset-secondary-800"
									wire:key='ch{{ $key }}' name="ch{{ $key }}" wire:model.defer="choices.{{ $key }}" value="1">Benar
							</label>
						</td>
						<td class="py-2 px-3 border border-gray-400">
							<label class="flex gap-2 items-center justify-center">
								<input type="radio"
									class="form-radio rounded-full transition ease-in-out duration-100 border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400 dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600 dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600 dark:focus:ring-offset-secondary-800"
									wire:key='ch{{ $key }}' name="ch{{ $key }}" wire:model.defer="choices.{{ $key }}" value="0">Salah
							</label>
						</td>
					</tr>
					@empty
					<tr>
						<td colspan="3" class="text-center">Pilihan jawaban tidak tersedia!</td>
					</tr>
					@endforelse
				</table>
				@elseif (strtolower($soal->type)=='is')
				<x-input wire:model.defer='answer' placeholder="Masukkan jawabanmu" />
				@elseif (strtolower($soal->type)=='u')
				<x-textarea wire:model.defer='answer' placeholder="Masukkan jawabanmu" />
				@elseif (strtolower($soal->type)=='jd' && is_array($soal->option))
				<div class="flex justify-between md:justify-start md:gap-48 relative mt-5"
					x-data="{relations: {}, key: null, keyb: null, paired: {}}">
					<div class="hidden" x-init="$nextTick(()=>{
						if(@js(count($srelation))){
							for(let i in @js($srelation)){
								if(@js($srelation)[i] != null){
									relations[i] = @js($srelation)[i];
									paired[i] = 2;
									lines[i] = generateLine($refs[i], $refs[relations[i]], i);
								}
							}
						}
					})
					"></div>
					<div class="flex flex-col gap-4 relative">
						@if (isset($soal->label[0]))
						<div class="font-bold border-b-2 border-b-gray-600 text-center">{{ $soal->label[0] }}</div>
						@endif
						@foreach ($soal->option as $key => $o)
						@if (is_array($soal->itemSoal->relations[$key]))
						<div
							class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300 hover:cursor-pointer hover:bg-gray-100"
							x-ref='start{{ $key }}_{{ $soal->id }}' x-on:click="
							key = 'start{{ $key }}_{{ $soal->id }}';
							if(paired[key] == undefined){
								paired[key] = 1;
							}

							if(keyb != null){
								$refs[keyb].classList.remove('bg-primary-300');
								$refs[keyb].classList.remove('hover:bg-primary-300');
							}

							if(paired[key] == 1){
								$refs[key].classList.add('bg-primary-300');
								$refs[key].classList.add('hover:bg-primary-300');
								keyb = key;
							}else{
								$refs[key].classList.remove('bg-primary-300');
								$refs[key].classList.remove('hover:bg-primary-300');
							}

							if(paired[key] == 2){
								delete paired[key];
								lines[key] = removeLine(lines[key], key);
								lines[key] = null;
								relations[key] = null;
							}
							
							rels = {...relations};
							$wire.relation = rels;
							">{!!
							shortcode($soal->option[$key]) !!}</div>
						@endif
						@endforeach
					</div>
					<div class="flex flex-col gap-4 relative" x-ref="contoh">
						@if (isset($soal->label[1]))
						<div class="font-bold border-b-2 border-b-gray-600 text-center">{{ $soal->label[1] }}</div>
						@endif
						@foreach ($soal->option as $key => $o)
						@if (!is_array($soal->itemSoal->relations[$key]))
						<div
							class="py-1 px-2 rounded-md text-center shadow-md border border-gray-300 hover:cursor-pointer hover:bg-gray-100"
							x-ref='end{{ $key }}_{{ $soal->id }}' x-on:click="
							if(paired[key] == 1){
								paired[key] = 2;
								relations[key] = 'end{{ $key }}_{{ $soal->id }}';
								lines[key] = generateLine($refs[key], $el, key);
								$refs[key].classList.remove('bg-primary-300');
								$refs[key].classList.remove('hover:bg-primary-300');
							}

							rels = {...relations};
							$wire.relation = rels;
							">{!!
							shortcode($soal->option[$key]) !!}</div>
						@endif
						@endforeach
					</div>
				</div>
				@endif
			</div>
			<div class="flex flex-col md:flex-row gap-2 justify-between mt-5 pt-5 items-center border-t border-t-gray-200">
				<span>
					<x-button negative icon="arrow-narrow-left" label="Soal Sebelumnya" wire:click='prevSoal'
						wire:target='prevSoal' class="rounded-3xl" />
				</span>
				<span>
					<x-button primary label="SIMPAN JAWABAN" lg x-on:click="$wire.saveAnswer();$wire.nextSoal()"
						wire:target='saveAnswer' class="rounded-3xl px-16 md:px-32" />
				</span>
				<span>
					<x-button positive label="Soal Selanjutnya" wire:click='nextSoal' right-icon="arrow-narrow-right"
						wire:target='nextSoal' class="rounded-3xl" />
				</span>
			</div>
		</div>
	</div>
	<div class="w-full md:w-3/12 flex flex-col gap-3" x-data="{opennum: false}" wire:ignore wire:key='attr{{ $soal->id }}'
		x-init="opennum=false">
		<div
			class="w-full shadow-md bg-amber-50 border border-amber-100 text-amber-600 rounded-lg p-3 text-xl md:text-md md:p-5 flex flex-col justify-center gap-3"
			x-ref="timer" wire:poll.keep-alive='checkTimer'>
			<div class="flex flex-col items-center" x-data="{cdown: @entangle('timer'), countdown: null}" x-init="$nextTick(()=>{
				countdown = timer(cdown);
				countdown.init();
			})">
				<div class="hidden" x-text="
					if(countdown!=null){
						if(Number(countdown.time().minutes) < 10 && countdown.time().hours == '00' && countdown.time().days == '00'){
							$refs.timer.classList.remove('bg-amber-50');
							$refs.timer.classList.remove('border-amber-100');
							$refs.timer.classList.remove('text-amber-600');
							$refs.timer.classList.add('bg-red-100');
							$refs.timer.classList.add('border-red-200');
							$refs.timer.classList.add('text-red-700');
						}
						if(countdown.time().seconds == '00' && countdown.time().minutes == '00' && countdown.time().hours == '00' && countdown.time().days == '00'){
							$wire.checkTimer();
							countdown = null;
						}
					}
				"></div>
				<h1 class="-mb-1">Waktu Ujian</h1>
				<div class="flex gap-1 !font-bold justify-center w-full">
					<h1 x-text="countdown!=null?countdown.time().days:'00'">00</h1>:
					<h1 x-text="countdown!=null?countdown.time().hours:'00'">00</h1>:
					<h1 x-text="countdown!=null?countdown.time().minutes:'00'">00</h1>:
					<h1 x-text="countdown!=null?countdown.time().seconds:'00'">00</h1>
				</div>
			</div>
			<x-button negative label="SELESAI" wire:click='stopUjian' />
			<x-button primary label="NOMOR SOAL" class="md:hidden inline-block" wire:target='none' x-on:click="
			$el.blur();
			opennum = !opennum;
			if(!opennum){
				$refs.number.classList.add('hidden');
			}else{
				$refs.number.classList.remove('hidden');
			}
			" />
		</div>
		<div x-ref="number" class="w-full hidden md:block shadow-md bg-white border border-gray-100 rounded-lg p-5">
			<div class="text-center text-lg font-bold underline underline-offset-2 text-gray-600">Nomor Soal</div>
			<div class="mt-5 grid grid-cols-5 gap-3 content-center text-center">
				@foreach ($login->soals() as $key => $bs)
				@if ($key == $login->current_number)
				<x-button amber label="{{ $key+1 }}" wire:click="toSoal('{{ $key }}')" class="shadow-md" />
				@else
				@php
				$ll = $login->tests()->where('item_soal_id',$bs->id)->first();
				@endphp
				@if ($ll && (is_array($ll->correct) && (count($ll->correct)) || is_array($ll->relation) &&
				(count($ll->relation)) ||
				$ll->answer))
				<x-button positive wire:click="toSoal('{{ $key }}')" wire:target='toSoal' label="{{ $key+1 }}"
					class="shadow-md" />
				@else
				<x-button wire:click="toSoal('{{ $key }}')" wire:target='toSoal' label="{{ $key+1 }}" class="shadow-md" />
				@endif
				@endif
				@endforeach
			</div>
		</div>
	</div>
</div>