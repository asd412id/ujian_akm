<div class="bg-white rounded shadow-md">
  <table class="w-full text-left border-collapse">
    <thead>
      <tr>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Nama</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Ruangan</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Waktu</th>
        <th class="px-6 py-4 text-sm font-bold text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Status</th>
        <th class="px-6 py-4 text-sm font-bold text-right text-gray-700 uppercase bg-gray-100 border-b border-gray-100">
          Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $key => $v)
      <tr class="{{ !$v->active && $v->logins()->count() ? 'bg-primary-100' : 'hover:bg-gray-100' }}">
        <td class="px-6 py-4 border-b border-gray-100 whitespace-nowrap">
          <div class="flex flex-col">
            <span>{{ $v->name }}</span>
            <em class="-mt-1 text-xs text-gray-500">{!! nl2br($v->desc) !!}</em>
          </div>
        </td>
        <td class="px-6 py-4 border-b border-gray-100">
          <div class="flex flex-wrap gap-1">
            <span class="px-3 py-1 text-sm border rounded-lg shadow-md bg-lime-50 border-lime-200 text-lime-700">{!!
              implode('</span> <span
              class="px-3 py-1 text-sm border rounded-lg shadow-md bg-lime-50 border-lime-200 text-lime-700">
              ',array_unique($v->pesertas()->select('ruang')->distinct('ruang')->get()->pluck('ruang')->toArray()))
              !!}</span>
          </div>
        </td>
        <td class="px-6 py-4 border-b border-gray-100">
          <div class="flex flex-wrap gap-1">
            <span
              class="px-3 py-1 text-sm border rounded-lg shadow-md whitespace-nowrap bg-amber-50 border-amber-200 text-amber-700">{{
              $v->start->format('d/m/Y H:i')
              }} - {{
              $v->end->format('d/m/Y H:i') }}</span>
            <span
              class="px-3 py-1 text-sm border rounded-lg shadow-md whitespace-nowrap bg-sky-50 border-sky-200 text-sky-700">{{
              $v->duration.' Menit' }}</span>
          </div>
        </td>
        <td class="px-6 py-4 border-b border-gray-100 whitespace-nowrap">{!! $v->active?'<span
            class="px-3 py-1 text-sm border rounded-lg shadow-md bg-positive-50 text-positive-700 border-positive-200">Aktif</span>':'<span
            class="px-3 py-1 text-sm border rounded-lg shadow-md bg-negative-50 text-negative-700 border-negative-200">Tidak
            Aktif</span>' !!}</td>
        <td class="px-6 py-4 border-b border-gray-100 whitespace-nowrap">
          <div class="flex justify-end">
            <div class="flex flex-wrap justify-end gap-1">
              @if ((!$v->active && !$v->logins()->count()) || $v->active)
              <x-button sm fuchsia wire:click="activate('{{ $v->id }}')" icon="{{
                $v->active?'ban':'check-circle' }}" label="{{ $v->active?'Non-Aktifkan':'Aktifkan' }}" />
              @endif
              @if (!$v->active)
              @if ($v->logins()->count())
              <x-button positive sm :href="route('nilai',['uuid'=>$v->uuid])" icon="pencil-alt" title="Penilaian" />
              <x-button amber sm wire:click="resetUjian('{{ $v->id }}')" icon="refresh" title="Reset Ujian" />
              <x-button purple sm wire:click="daftarNilai('{{ $v->id }}')" icon="clipboard-list" title="Daftar Nilai" />
              @endif
              <x-button info sm wire:click="daftarHadir('{{ $v->id }}')" icon="view-list" title="Daftar Hadir" />
              <x-button warning sm wire:click="edit('{{ $v->id }}')" icon="pencil" title="Edit" />
              <x-button negative sm wire:click="delete('{{ $v->id }}')" icon="trash" title="Hapus" />
              @else
              <x-button info sm wire:click="daftarHadir('{{ $v->id }}')" icon="view-list" title="Daftar Hadir" />
              <x-button primary sm :href="route('statuspeserta',['uuid'=>$v->uuid])" icon="desktop-computer"
                title="Status Peserta" />
              <x-button warning sm wire:click="edit('{{ $v->id }}')" icon="pencil" title="Edit" />
              @endif
            </div>
          </div>
        </td>
      </tr>
      @empty
      <tr class="hover:bg-gray-100">
        <td colspan="6" class="px-6 py-4 text-center border-b border-gray-100">Data tidak tersedia!</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>