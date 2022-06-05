<div class="bg-white rounded shadow-md">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Nama</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Alamat Email</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Mata Pelajaran</th>
        <th class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      <tr class="hover:bg-gray-100">
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->name }}</td>
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->email }}</td>
        <td class="px-6 py-4 border-b border-gray-100">
          @php($mapels = $v->mapels->pluck('name')->toArray())
          @if (count($mapels))
          <span class="px-3 py-1 text-sm border rounded-lg shadow-md bg-cyan-50 border-cyan-200 text-cyan-700">{!!
            implode('</span> <span
            class="px-3 py-1 text-sm border rounded-lg shadow-md bg-cyan-50 border-cyan-200 text-cyan-700">',$mapels)
            !!}</span>
          @endif
        </td>
        <td class="px-6 py-4 border-b border-gray-100">
          <div class="flex justify-end">
            <div class="flex justify-end gap-1">
              <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
              <x-button red icon="trash" xs label="Hapus" wire:click="delete('{{ $v->id }}')" />
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="4" class="px-6 py-4 text-center border-b border-gray-100">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>