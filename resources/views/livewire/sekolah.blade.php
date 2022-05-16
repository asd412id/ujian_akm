<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-5">
	<div class="w-full md:w-6/12 shadow-sm p-6 bg-white border-b border-gray-200 rounded-lg">
		<h3 class="text-2xl">Data Sekolah</h3>
		<form wire:submit.prevent='update' class="mt-2 flex flex-col gap-3">
			<x-input label="Nama Sekolah" wire:model.defer="nama_sekolah" placeholder="Masukkan nama sekolah" />
			<div>
				<x-input label="Nama File Logo Sekolah" wire:model.defer="logo_sekolah" placeholder="Masukkan kode logo sekolah"
					corner-hint="Contoh: nama_file.png, logo/nama_file.png" hint="Nama file berdasarkan lokasi file pada media" />
				<div class="w-24 mt-1 p-1 border border-solid border-gray-300 rounded-md shadow-md">
					{!! shortcode('[g]'.$logo_sekolah.'[/g]') !!}
				</div>
			</div>
			<div class="flex flex-col">
				<x-alabel>KOP Sekolah Editor</x-alable>
					<x-editor wire:model.defer='kop_sekolah'
						plugins="align,table,fontAwesome,fontFamily,fontSize,lists,fullscreen,codeView"
						placeholder="Masukkan kop sekolah" />
			</div>
			<x-button label="SIMPAN" primary type='submit' />
		</form>
	</div>
	<div class="w-full md:w-6/12">
		@if (!is_dir(public_path('uploads'))||!Storage::disk('public')->exists('uploads'))
		<div class="w-full shadow-sm p-6 bg-white border-b border-gray-200 rounded-lg mb-3">
			<p>Folder upload tidak terbaca oleh sistem! Anda tidak dapat melakukan unggahan/upload file ke aplikasi. Jika
				aplikasi baru pertama kali diinstall, klik tombol di bawah untuk
				memperbaiki struktur folder!</p>
			<x-button icon="adjustments" rose label="FIX FOLDER" class="block w-full mt-3" wire:click='fixFolder' />
		</div>
		@endif
		<div class="w-full shadow-sm p-6 bg-white border-b border-gray-200 rounded-lg">
			{!! shortcode($kop_sekolah) !!}
		</div>
		<div class="w-full shadow-sm p-6 bg-white border-b border-gray-200 rounded-lg mt-3">
			<h3 class="text-2xl">Data Admin</h3>
			<form wire:submit.prevent='updateUser' class="mt-2 flex flex-col gap-3">
				<x-input label="Nama Admin" wire:model.defer="nama_admin" placeholder="Masukkan nama admin" required />
				<x-input label="Alamat Email" type="email" wire:model.defer="email" placeholder="Masukkan alamat email"
					required />
				<x-input label="Password" type="password" wire:model.defer="password" placeholder="Masukkan password"
					required />
				<x-input label="Password Baru" type="password" wire:model.defer="newpassword"
					placeholder="Masukkan password baru (Jika ingin diubah)" />
				<x-input label="Ulang Password Baru" type="password" wire:model.defer="renewpassword"
					placeholder="Masukkan ulang password baru (Jika ingin diubah)" />
				<x-button label="SIMPAN" primary type='submit' />
			</form>
		</div>
	</div>
</div>