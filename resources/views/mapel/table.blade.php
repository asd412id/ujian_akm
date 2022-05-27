<div class="bg-white shadow-md rounded">
  <table class="text-left w-full border-collapse">
    <thead>
      <tr>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Nama Mata Pelajaran</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Nama Penilai</th>
        <th class="text-right py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      @php
      $hasTests = $v->soals()
      ->has('jadwals.tests')->count();
      @endphp
      <tr class="hover:bg-gray-100">
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->name }}</td>
        <td class="py-4 px-6 border-b border-gray-100">
          @php($penilai = $v->users->pluck('name')->toArray())
          @if (count($penilai))
          <span class="text-sm bg-rose-50 border border-rose-200 shadow-md text-rose-700 py-1 px-3 rounded-lg">{!!
            implode('</span> <span
            class="text-sm bg-rose-50 border border-rose-200 shadow-md text-rose-700 py-1 px-3 rounded-lg">',$penilai)
            !!}</span>
          @endif
        </td>
        <td class="py-4 px-6 border-b border-gray-100 flex justify-end gap-1">
          <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
          @if (!$hasTests)
          <x-button red icon="trash" xs label="Hapus" wire:click="delete('{{ $v->id }}')" />
          @endif
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="3" class="py-4 px-6 border-b border-gray-100 text-center">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>