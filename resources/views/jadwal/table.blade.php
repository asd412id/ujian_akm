<div class="bg-white shadow-md rounded">
  <table class="text-left w-full border-collapse">
    <thead>
      <tr>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Nama</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Ruangan</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Waktu</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Durasi</th>
        <th class="py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Status</th>
        <th class="text-right py-4 px-6 bg-gray-100 font-bold uppercase text-sm text-gray-700 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      <tr class="hover:bg-gray-100">
        <td class="py-4 px-6 border-b border-gray-100">{{ $v->name }}</td>
        <td class="py-4 px-6 border-b border-gray-100"><span
            class="text-sm bg-lime-50 border border-lime-200 shadow-md text-lime-700 py-1 px-3 rounded-lg">{!!
            implode('</span> <span
            class="text-sm bg-lime-50 border border-lime-200 shadow-md text-lime-700 py-1 px-3 rounded-lg">
            ',array_unique($v->pesertas()->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray()))
            !!}</span></td>
        <td class="py-4 px-6 border-b border-gray-100">
          <span class="text-sm bg-amber-50 border border-amber-200 shadow-md text-amber-700 py-1 px-3 rounded-lg">{{
            $v->start->format('d/m/Y H:i')
            }} - {{
            $v->end->format('d/m/Y H:i') }}</span>
        </td>
        <td class="py-4 px-6 border-b border-gray-100">
          <span class="text-sm bg-sky-50 border border-sky-200 shadow-md text-sky-700 py-1 px-3 rounded-lg">{{
            $v->duration.' Menit' }}</span>
        </td>
        <td class="py-4 px-6 border-b border-gray-100">{!! $v->active?'<span
            class="text-sm bg-positive-50 text-positive-700 border border-positive-200 shadow-md py-1 px-3 rounded-lg">Aktif</span>':'<span
            class="text-sm bg-negative-50 text-negative-700 border border-negative-200 shadow-md py-1 px-3 rounded-lg">Tidak
            Aktif</span>' !!}</td>
        <td class="py-5 px-6 border-b border-gray-100 flex justify-end">
          <x-dropdown>
            <x-dropdown.item wire:click="activate('{{ $v->id }}')" icon="{{ $v->active?'ban':'check-circle' }}"
              label="{{ $v->active?'Non-Aktifkan':'Aktifkan' }}" />
            <x-dropdown.item wire:click="edit('{{ $v->id }}')" icon="pencil" label="Edit" />
            <x-dropdown.item wire:click="delete('{{ $v->id }}')" icon="trash" label="Hapus" />
          </x-dropdown>
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