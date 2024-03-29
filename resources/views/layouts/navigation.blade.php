<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
	<!-- Primary Navigation Menu -->
	<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
		<div class="flex justify-between h-16">
			<div class="flex">
				<!-- Logo -->
				<div class="flex items-center shrink-0">
					<a href="{{ route('dashboard') }}" class="flex items-center gap-3">
						@if (!is_null(auth()->user()->role) && auth()->user()->sekolah->logo &&
						Storage::disk('public')->exists('uploads/'.userFolder().'/'.auth()->user()->sekolah->logo))
						<div class="block w-10 h-full text-gray-600 fill-current">
							<img src="{{ getUrl(auth()->user()->sekolah->logo) }}" class="w-full" alt="">
						</div>
						@else
						<x-application-logo class="block w-auto h-10 text-gray-600 fill-current" />
						@endif
						<div class="flex flex-col items-center justify-start">
							<span class="self-start text-sm">{{ env('APP_NAME','Aplikasi Ujian') }}</span>
							<span class="self-start -mt-2 text-lg font-bold">{{ !is_null(auth()->user()->role) ?
								auth()->user()->sekolah->name : 'Super Administrator' }}</span>
						</div>
					</a>
				</div>

				<!-- Navigation Links -->
				<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
					<x-anav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
						{{ __('Beranda') }}
					</x-anav-link>
					@if (!is_null(auth()->user()->role))
					<x-anav-link :href="route('media')" :active="request()->routeIs('media')">
						{{ __('Media') }}
					</x-anav-link>
					@if (auth()->user()->role==0)
					<x-anav-link :href="route('mapel')" :active="request()->routeIs('mapel')">
						{{ __('Mata Pelajaran') }}
					</x-anav-link>
					<x-anav-link :href="route('penilai')" :active="request()->routeIs('penilai')">
						{{ __('Penilai') }}
					</x-anav-link>
					@endif
					<x-anav-link :href="route('peserta')" :active="request()->routeIs('peserta')">
						{{ __('Peserta Ujian') }}
					</x-anav-link>
					<x-anav-link :href="route('soal')" :active="request()->routeIs('soal')">
						{{ __('Daftar Soal') }}
					</x-anav-link>
					<x-anav-link :href="route('jadwal')" :active="request()->routeIs('jadwal')">
						{{ __('Jadwal Ujian') }}
					</x-anav-link>
					@else
					<x-anav-link :href="route('sekolah')" :active="request()->routeIs('sekolah')">
						{{ __('Daftar Sekolah') }}
					</x-anav-link>
					@endif
				</div>
			</div>

			<!-- Settings Dropdown -->
			<div class="hidden sm:flex sm:items-center sm:ml-6">
				<x-adropdown align="right" width="48">
					<x-slot name="trigger">
						<button
							class="flex items-center text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300">
							<div>{{ Auth::user()->name }}</div>

							<div class="ml-1">
								<svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
									<path fill-rule="evenodd"
										d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
										clip-rule="evenodd" />
								</svg>
							</div>
						</button>
					</x-slot>

					<x-slot name="content">
						<!-- Authentication -->
						<form method="POST" action="{{ route('logout') }}">
							@csrf

							<x-adropdown-link :href="route('logout')" onclick="event.preventDefault();
							this.closest('form').submit();">
								{{ __('Keluar') }}
							</x-adropdown-link>
						</form>
					</x-slot>
				</x-adropdown>
			</div>

			<!-- Hamburger -->
			<div class="flex items-center -mr-2 sm:hidden">
				<button @click="open = ! open"
					class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
					<svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
						<path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round"
							stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
						<path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
							stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
		</div>
	</div>

	<!-- Responsive Navigation Menu -->
	<div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
		<div class="pt-2 pb-3 space-y-1">
			<x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
				{{ __('Beranda') }}
			</x-responsive-nav-link>
			@if (!is_null(auth()->user()->role))
			<x-responsive-nav-link :href="route('media')" :active="request()->routeIs('media')">
				{{ __('Media') }}
			</x-responsive-nav-link>
			@if (auth()->user()->role==0)
			<x-responsive-nav-link :href="route('mapel')" :active="request()->routeIs('mapel')">
				{{ __('Mata Pelajaran') }}
			</x-responsive-nav-link>
			<x-responsive-nav-link :href="route('penilai')" :active="request()->routeIs('penilai')">
				{{ __('Penilai') }}
			</x-responsive-nav-link>
			@endif
			<x-responsive-nav-link :href="route('peserta')" :active="request()->routeIs('peserta')">
				{{ __('Peserta Ujian') }}
			</x-responsive-nav-link>
			<x-responsive-nav-link :href="route('soal')" :active="request()->routeIs('soal')">
				{{ __('Daftar Soal') }}
			</x-responsive-nav-link>
			<x-responsive-nav-link :href="route('jadwal')" :active="request()->routeIs('jadwal')">
				{{ __('Jadwal Ujian') }}
			</x-responsive-nav-link>
			@else
			<x-responsive-nav-link :href="route('sekolah')" :active="request()->routeIs('sekolah')">
				{{ __('Daftar Sekolah') }}
			</x-responsive-nav-link>
			@endif
		</div>

		<!-- Responsive Settings Options -->
		<div class="pt-4 pb-1 border-t border-gray-200">
			<div class="px-4">
				<div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
				<div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
			</div>

			<div class="mt-3 space-y-1">
				<!-- Authentication -->
				<form method="POST" action="{{ route('logout') }}">
					@csrf

					<x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
					this.closest('form').submit();">
						{{ __('Keluar') }}
					</x-responsive-nav-link>
				</form>
			</div>
		</div>
	</div>
</nav>