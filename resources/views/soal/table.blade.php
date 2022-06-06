<div class="bg-white rounded shadow-md">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Nama Soal</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Mata Pelajaran</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Jumlah Soal</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Jenis Soal</th>
        <th class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      <tr class="hover:bg-gray-100">
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->name }}</td>
        <td class="px-6 py-4 border-b border-gray-100">{{ isset($v->mapel)?$v->mapel->name:'-' }}</td>
        <td class="px-6 py-4 border-b border-gray-100">{{ $v->item_soals()->count() }}</td>
        <td class="px-6 py-4 border-b border-gray-100">
          @php($type =
          $v->item_soals()->select('type')->distinct('type')->orderBy('num','asc')->get()->pluck('type')->toArray())
          @if (count($type))
          <div class="flex flex-wrap gap-1">
            <span class="px-3 py-1 text-sm border rounded-lg shadow-md bg-amber-50 border-amber-200 text-amber-700">{!!
              implode('</span> <span
              class="px-3 py-1 text-sm border rounded-lg shadow-md bg-amber-50 border-amber-200 text-amber-700">',array_map(function($v){return
              strtoupper($v);},$type))
              !!}</span>
          </div>
          @endif
        </td>
        <td class="px-6 py-4 border-b border-gray-100">
          <div class="flex justify-end">
            <div class="flex flex-wrap justify-end gap-1">
              <x-button info icon="search" xs label="Lihat Soal" wire:click="show('{{ $v->id }}')" />
              @if ($v->excel && Storage::exists($v->excel))
              <x-button green icon="download" xs label="Excel" title="Download Soal Excel"
                wire:click="download('{{ $v->id }}')" />
              @endif
              <x-button warning icon="pencil" xs label="Edit" wire:click="edit('{{ $v->id }}')" />
              @if (!$v->jadwals()->count())
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