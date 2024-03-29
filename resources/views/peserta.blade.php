@php
$sekolah = auth()->user()->sekolah;
@endphp
<x-guest-layout>
	<x-slot name="title">{{ $title }}</x-slot>
	<div class="min-h-screen bg-gray-100">

		<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
			<!-- Primary Navigation Menu -->
			<div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
				<div class="flex justify-between h-16">
					<div class="flex">
						<!-- Logo -->
						<div class="flex items-center shrink-0">
							<a href="{{ route('ujian.index') }}" class="flex items-center gap-3">
								@if ($sekolah->logo && Storage::disk('public')->exists('uploads/'.userFolder().'/'.$sekolah->logo))
								<div class="block w-10 h-full text-gray-600 fill-current">
									<img src="{{ getUrl($sekolah->logo) }}" class="w-full" alt="">
								</div>
								@else
								<x-application-logo class="block w-auto h-10 text-gray-600 fill-current" />
								@endif
								<div class="flex flex-col items-center justify-start">
									<span class="self-start text-sm">{{ env('APP_NAME','Aplikasi Ujian') }}</span>
									<span class="self-start -mt-2 text-lg font-bold">{{ $sekolah->name }}</span>
								</div>
							</a>
						</div>

						<!-- Navigation Links -->
						<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
							<x-anav-link :active="true">
								{{ __($title) }}
							</x-anav-link>
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
								<form method="POST" action="{{ route('peserta.logout') }}">
									@csrf

									<x-adropdown-link :href="route('peserta.logout')" onclick="event.preventDefault();
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
						{{ __($title) }}
					</x-responsive-nav-link>
				</div>

				<!-- Responsive Settings Options -->
				<div class="pt-4 pb-1 border-t border-gray-200">
					<div class="px-4">
						<div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
					</div>

					<div class="mt-3 space-y-1">
						<!-- Authentication -->
						<form method="POST" action="{{ route('peserta.logout') }}">
							@csrf

							<x-responsive-nav-link :href="route('peserta.logout')" onclick="event.preventDefault();
							this.closest('form').submit();">
								{{ __('Keluar') }}
							</x-responsive-nav-link>
						</form>
					</div>
				</div>
			</div>
		</nav>

		<div class="py-6">
			<div class="flex flex-row gap-2 mx-auto max-w-7xl sm:px-6 lg:px-8 md:flex-col">
				@livewire($wire,['user' => auth()->user()])
			</div>
		</div>
	</div>
</x-guest-layout>