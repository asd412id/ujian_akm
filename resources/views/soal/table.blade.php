<div class="bg-white shadow-md rounded">
  <table class="text-left w-full border-collapse">
    <thead>
      <tr>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Nama Soal</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Mata Pelajaran</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Jumlah Soal</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Jenis Soal</th>
        <th class="text-right py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      @php
      $items = $v->item_soals->pluck('id')->toArray();
      $hasTests = $v->jadwals()
      ->whereHas('tests',function($q) use($items){
      $q->whereIn('item_soal_id',$items);
      })->count();
      @endphp
      <tr class="hover:bg-gray-100">
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->name }}</td>
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->mapel->name }}</td>
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->item_soals()->count() }}</td>
        <td class="py-4 px-6 border-b border-gray-100">
          @php($type = $v->item_soals()->select('type')->distinct('type')->get()->pluck('type')->toArray())
          @if (count($type))
          <span class="text-sm bg-amber-50 border border-amber-200 shadow-md text-amber-700 py-1 px-3 rounded-lg">{!!
            implode('</span> <span
            class="text-sm bg-amber-50 border border-amber-200 shadow-md text-amber-700 py-1 px-3 rounded-lg">',array_map(function($v){return
            strtoupper($v);},$type))
            !!}</span>
          @endif
        </td>
        <td class="py-4 px-6 border-b border-gray-100 flex justify-end gap-1">
          <x-button info icon="search" xs label="Lihat Soal" wire:click="show('{{ $v->id }}')" />
          @if ($v->excel && Storage::exists($v->excel))
          <x-button green icon="download" xs label="Excel" title="Download Soal Excel"
            wire:click="download('{{ $v->id }}')" />
          @endif
          <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
          @if (!$hasTests)
          <x-button red icon="trash" xs label="Hapus" wire:click="delete('{{ $v->id }}')" />
          @endif
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="5" class="py-4 px-6 border-b border-gray-100 text-center">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>