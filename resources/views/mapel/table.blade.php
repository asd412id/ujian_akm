<div class="bg-white rounded shadow-md">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Nama Mata Pelajaran</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Nama Penilai</th>
        <th class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      <tr class="hover:bg-gray-100">
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->name }}</td>
        <td class="px-6 py-4 border-b border-gray-100">
          @php($penilai = $v->users->pluck('name')->toArray())
          @if (count($penilai))
          <span class="px-3 py-1 text-sm border rounded-lg shadow-md bg-rose-50 border-rose-200 text-rose-700">{!!
            implode('</span> <span
            class="px-3 py-1 text-sm border rounded-lg shadow-md bg-rose-50 border-rose-200 text-rose-700">',$penilai)
            !!}</span>
          @endif
        </td>
        <td class="px-6 py-4 border-b border-gray-100">
          <div class="flex justify-end">
            <div class="flex justify-end gap-1">
              <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
              @if (!$v->soals()->count())
              <x-button red icon="trash" xs label="Hapus" wire:click="delete('{{ $v->id }}')" />
              @endif
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="3" class="px-6 py-4 text-center border-b border-gray-100">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>