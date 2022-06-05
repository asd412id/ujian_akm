<div class="bg-white rounded shadow-md">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          ID Peserta</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Nama</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Jenis Kelamin</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Ruangan</th>
        <th class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      @php
      $hasTests = $v->logins()->has('tests')->count();
      @endphp
      <tr class="hover:bg-gray-100">
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->uid }}</td>
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->name }}</td>
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->jk }}</td>
        <td class="px-6 py-4 border-b border-gray-100"><span
            class="px-3 py-1 text-sm border rounded-lg shadow-md bg-emerald-50 border-emerald-200 text-emerald-700">{{
            $v->ruang
            }}</span></td>
        <td class="px-6 py-4 border-b border-gray-100">
          <div class="flex justify-end">
            <div class="flex justify-end gap-1">
              @if ($v->sekolah->limit_login && $v->is_login)
              <x-button blue icon="reply" xs label="Reset Login" wire:click="resetLogin('{{ $v->id }}')" />
              @endif
              <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
              @if (!$hasTests)
              <x-button red icon="trash" xs label="Hapus" wire:click="delete('{{ $v->id }}')" />
              @endif
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="5" class="px-6 py-4 text-center border-b border-gray-100">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>