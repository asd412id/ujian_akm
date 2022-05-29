<div class="flex flex-col gap-5 mx-auto max-w-7xl sm:px-6 lg:px-8 md:flex-row">
	@if (auth()->user()->role==0)
	<div class="w-full md:w-6/12">
		<div class="p-6 bg-white border-b border-gray-200 rounded-lg shadow-sm">
			<h3 class="text-2xl">Data Sekolah</h3>
			<form wire:submit.prevent='update' class="flex flex-col gap-3 mt-2" x-data>
				<x-input label="Nama Sekolah" wire:model.defer="nama_sekolah" placeholder="Masukkan nama sekolah" />
				<div>
					<x-input x-ref="logo_input" label="Nama File Logo Sekolah" wire:model.defer="logo_sekolah"
						placeholder="Masukkan kode logo sekolah" corner-hint="Contoh: nama_file.png, logo/nama_file.png"
						hint="Nama file berdasarkan lokasi file pada media" />
					@if ($logo_sekolah)
					<div class="w-24 p-1 mt-1 border border-gray-300 border-solid rounded-md shadow-md"
						x-on:click="$refs.logo_input.select()">
						{!! shortcode('[g]'.$logo_sekolah.'[/g]') !!}
					</div>
					@endif
				</div>
				<div class="flex gap-2" x-data>
					<x-button blue sm label="Download Template Master Data" icon="download" wire:click='downloadExcel' />
					<x-button green sm label="Impor Master Data" icon="upload" x-on:click="$refs.excel.click()" />
					<input type="file" wire:model='excel' class="hidden" accept=".xls,.xlsx,.ods,.bin" x-ref="excel">
					<x-error name="excel" />
				</div>
				<x-toggle lg wire:model.defer='limitlogin' label="Batasi login peserta hanya 1 (satu) kali." />
				<x-toggle lg wire:model.defer='restricttest'
					label="Batasi peserta dari keluar halaman ujian (ujian akan selesai)." />
				<div class="flex flex-col gap-1" x-data>
					<x-alabel>KOP Sekolah (Gunakan tombol di bawah untuk memasukkan kode)</x-alabel>
					<div class="flex flex-wrap gap-1">
						<x-button xs outline primary x-on:click="$refs.editor.focus();insertTag($refs.editor,'[p][/p]')"
							label="paragraf" class="uppercase" />
						<x-button xs outline primary
							x-on:click="$refs.editor.focus();insertTag($refs.editor,'[g {lebar} {tinggi} {posisi}][/g]',13)"
							label="gambar" class="uppercase" />
						<x-button xs outline info
							x-on:click="$refs.editor.focus();insertTag($refs.editor,'[tabel {lebar}]\n\t\n[/tabel]',4)" label="tabel"
							class="uppercase" />
						<x-button xs outline info x-on:click="$refs.editor.focus();insertTag($refs.editor,'[baris]\n\t\n[/baris]')"
							label="baris" class="uppercase" />
						<x-button xs outline info
							x-on:click="$refs.editor.focus();insertTag($refs.editor,'[kolom {lebar} {tinggi} {posisi_h} {posisi_v}][/kolom]',19)"
							label="kolom" class="uppercase" />
					</div>
					<x-textarea x-ref="editor" wire:model.defer='kop_sekolah'
						plugins="align,table,fontAwesome,fontFamily,fontSize,lists,fullscreen,codeView"
						placeholder="Masukkan kop sekolah atau kode gambar kop" />
				</div>
				<x-button label="SIMPAN" primary type='submit' />
			</form>
		</div>
	</div>
	@endif
	<div class="w-full md:w-6/12">
		@if (auth()->user()->role==0)
		@if (!is_dir(public_path('uploads'))||!Storage::disk('public')->exists('uploads'))
		<div class="w-full p-6 mb-3 bg-white border-b border-gray-200 rounded-lg shadow-sm">
			<p>Folder upload tidak terbaca oleh sistem! Anda tidak dapat melakukan unggahan/upload file ke aplikasi. Jika
				aplikasi baru pertama kali diinstall, klik tombol di bawah untuk
				memperbaiki struktur folder!</p>
			<x-button icon="adjustments" rose label="FIX FOLDER" class="block w-full mt-3" wire:click='fixFolder' />
		</div>
		@endif
		@if ($kop_sekolah)
		<div class="w-full p-6 bg-white border-b border-gray-200 rounded-lg shadow-sm">
			{!! shortcode($kop_sekolah) !!}
		</div>
		@endif
		@endif
		<div class="w-full p-6 mt-3 bg-white border-b border-gray-200 rounded-lg shadow-sm">
			<h3 class="text-2xl">Data Pengguna</h3>
			<form wire:submit.prevent='updateUser' class="flex flex-col gap-3 mt-2">
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