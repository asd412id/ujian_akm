<div class="bg-white shadow-md rounded">
  <table class="text-left w-full border-collapse">
    <thead>
      <tr>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          ID Peserta</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Nama</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Jenis Kelamin</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Ruangan</th>
        <th class="text-right py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      @php
      $hasTests = $v->logins()->has('tests')->count();
      @endphp
      <tr class="hover:bg-gray-100">
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->uid }}</td>
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->name }}</td>
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->jk }}</td>
        <td class="py-4 px-6 border-b border-gray-100"><span
            class="text-sm bg-emerald-50 border border-emerald-200 shadow-md text-emerald-700 py-1 px-3 rounded-lg">{{
            $v->ruang
            }}</span></td>
        <td class="py-4 px-6 border-b border-gray-100 flex justify-end gap-1">
          @if ($v->sekolah->limit_login && $v->is_login)
          <x-button blue icon="reply" xs label="Reset Login" wire:click="resetLogin('{{ $v->id }}')" />
          @endif
          <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
          @if (!$hasTests)
          <x-button red icon="trash" xs label="Hapus" wire:click="delete('{{ $v->id }}')" />
          @endif
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="4" class="py-4 px-6 border-b border-gray-100 text-center">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>