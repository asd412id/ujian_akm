<div class="flex gap-3" wire:poll.keep-alive.15s='checkTimer'>
	<div class="w-9/12">
		<div class="w-full shadow-md bg-white border border-gray-100 rounded-lg p-5">
			Lorem ipsum dolor sit amet, consectetur adipisicing elit. Necessitatibus quidem adipisci molestias, inventore,
			minus tempore cum harum, reiciendis quas quasi maiores. Quia, voluptate! Aut itaque iure corporis necessitatibus
			dolores autem!
		</div>
	</div>
	<div class="w-3/12 flex flex-col gap-3">
		<div x-data class="w-full shadow-md bg-amber-50 border border-amber-100 text-amber-600 rounded-lg py-1 px-5"
			x-ref="timer">
			<div class="flex flex-col items-center" x-data="{countdown: null}" x-init="$nextTick(()=>{
				countdown = timer(@js($login->start->addMinutes($login->jadwal->duration)->getPreciseTimestamp(3)));
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
		</div>
	</div>
</div>