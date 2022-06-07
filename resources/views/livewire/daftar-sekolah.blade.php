<div class="flex flex-col gap-2 mx-auto max-w-7xl sm:px-6 lg:px-8">
	<div class="flex flex-col items-center justify-center gap-2 p-2 px-2 md:flex-row md:justify-between md:px-0">
		<div class="flex items-center gap-2">
			<x-button primary label="Tambah Data" icon="plus" wire:click='create' />
			<x-toggle lg label="Verifikasi Email" wire:model='must_verified' />
			<x-toggle lg label="Buka Pendaftaran" wire:model='allow_register' />
		</div>
		<div class="flex flex-col gap-2 md:flex-row">
			<div class="flex items-center justify-center gap-1">
				Tampilkan Data:
				<x-native-select wire:model='limit' :options=[10,15,30,50,100,500,1000] />
			</div>
			<x-native-select :options="[
					['id'=>'all','text'=>'Semua Sekolah'],
					['id'=>'verified','text'=>'Terverifikasi'],
					['id'=>'!verified','text'=>'Belum Terverifikasi'],
				]" wire:model='verified' option-label='text' option-value='id' />
			<x-input type="search" wire:model.debounce.500ms='search' placeholder="Cari Data" right-icon="search"
				autofocus="true" />
		</div>
	</div>
	<div class="w-full bg-white border-b border-gray-200 rounded-lg shadow-sm">
		<table class="w-full text-left border-collapse">
			<thead>
				<tr>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Nama</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Operator</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Email</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Data</th>
					<th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Status</th>
					<th
						class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
						Aksi</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($data as $v)
				@php
				$storage = file_exists(public_path('uploads/'.generateUserFolder($v->id).'/config.php'))?include
				public_path('uploads/'.generateUserFolder($v->id).'/config.php'):null;
				@endphp
				<tr class="hover:bg-gray-100">
					<td class="px-6 py-4 border-b border-gray-100">
						{{ $v->name }}</td>
					<td class="px-6 py-4 border-b border-gray-100">
						{{ $v->operator?$v->operator->name:'-' }}</td>
					<td class="px-6 py-4 border-b border-gray-100">
						{{ $v->operator?$v->operator->email:'-' }}</td>
					<td class="px-6 py-4 border-b border-gray-100">
						<div class="flex flex-wrap gap-1">
							<span
								class="px-2 text-sm border rounded shadow whitespace-nowrap bg-primary-50 text-primary-600 border-primary-100">
								Mapel: {{ $v->mapels()->count() }}
							</span>
							<span class="px-2 text-sm text-red-600 border border-red-100 rounded shadow whitespace-nowrap bg-red-50">
								Penilai: {{ $v->users()->where('role',1)->count() }}
							</span>
							<span
								class="px-2 text-sm border rounded shadow whitespace-nowrap bg-info-50 text-info-600 border-info-100">
								Peserta: {{ $v->pesertas()->count() }}
							</span>
							<span
								class="px-2 text-sm border rounded shadow whitespace-nowrap bg-amber-50 text-amber-600 border-amber-100">
								Soal: {{ $v->soals()->count() }}
							</span>
							<span
								class="px-2 text-sm border rounded shadow whitespace-nowrap bg-violet-50 text-violet-600 border-violet-100">
								Jadwal: {{ $v->jadwals()->count() }}
							</span>
							<span
								class="px-2 text-sm text-gray-600 border border-gray-100 rounded shadow whitespace-nowrap bg-gray-50">
								Storage: {{ is_array($storage)?$storage['MaxSizeTotal'].' MB':'-' }}
							</span>
							<span
								class="px-2 text-sm text-gray-600 border border-gray-100 rounded shadow whitespace-nowrap bg-gray-50">
								Upload: {{ is_array($storage)?$storage['MaxSizeUpload'].' MB':'-' }}
							</span>
						</div>
					</td>
					<td class="px-6 py-4 border-b border-gray-100">
						@if ($v->operator && !is_null($v->operator->email_verified_at))
						<span
							class="px-2 text-sm border rounded shadow whitespace-nowrap bg-positive-50 text-positive-600 border-positive-100">Aktif</span>
						@else
						<span
							class="px-2 text-sm border rounded shadow whitespace-nowrap bg-negative-50 text-negative-600 border-negative-100">Tidak
							Aktif</span>
						@endif
					</td>
					<td class="px-6 py-4 border-b border-gray-100">
						<div class="flex justify-end">
							<div class="flex justify-end gap-1">
								<x-button warning icon="pencil" xs title="Edit" wire:click="edit('{{ $v->id }}')" />
								<x-button red icon="trash" xs title="Hapus" wire:click="delete('{{ $v->id }}')" />
							</div>
						</div>
					</td>
				</tr>
				@empty
				<tr>
					<td class="px-6 py-4 text-center border-b border-gray-100" colspan="6">Data tidak tersedia</td>
				</tr>
				@endforelse
			</tbody>
		</table>
	</div>
	<x-modal.card maxWidth="sm" blur title="{{ $modalTitle }}" wire:model='modal' staticbackdrop>
		<form wire:submit.prevent='store' class="flex flex-col gap-3">
			<x-input wire:model.defer='nama_sekolah' label="Nama Sekolah" placeholder="Masukkan nama sekolah" required />
			<x-input wire:model.defer='nama_operator' label="Nama Operator" placeholder="Masukkan nama operator" required />
			<x-input wire:model.defer='email' label="Alamat Email" placeholder="Masukkan alamat email" type="email"
				required />
			<x-input wire:model.defer='password' label="Password" placeholder="Masukkan password" type="password" />
			<x-input wire:model.defer='repassword' label="Ulang Password" placeholder="Masukkan ulang password"
				type="password" />
			<x-toggle lg wire:model.defer="is_verified" label="Verifikasi" />
			<x-input wire:model.defer='max_storage' label="Max Storage" type="number" suffix="MB" step="0.0001"
				class="pr-28" />
			<x-input wire:model.defer='max_upload' label="Max Upload" type="number" suffix="MB" step="0.0001" class="pr-28" />
			<div class="flex justify-end gap-2 pt-5">
				<x-button secondary label="Batal" x-on:click="close" />
				<x-button type="submit" primary label="Simpan" />
			</div>
		</form>
	</x-modal.card>
	<div class="flex justify-end">
		{!! $data->links() !!}
	</div>
</div>