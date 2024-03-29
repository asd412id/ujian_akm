<div class="flex flex-col md:flex-row justify-center md:justify-between gap-2 px-2 md:px-0">
  <div class="flex flex-col md:flex-row gap-1">
    <x-button primary label="Tambah Data" icon="plus" wire:click='create' />
    @if (request()->is('*peserta') || request()->is('*soal') || request()->is('*jadwal'))
    <x-button red label="Hapus Data" icon="trash" wire:click='destroyAll' :disabled="!count($this->data)" />
    @if (request()->is('*peserta'))
    <x-button violet label="Cetak Kartu Peserta" icon="credit-card" wire:click='printCard'
      :disabled="!count($this->data)" />
    @endif
    @endif
  </div>
  <div class="flex flex-col md:flex-row gap-2">
    <div class="flex gap-1 items-center justify-center">
      Tampilkan Data:
      <x-native-select wire:model='limit' :options=[10,15,30,50,100,500,1000] />
    </div>
    @if (request()->is('*peserta'))
    <x-native-select wire:model='dataattrlist' :options="$attrlists" placeholder="{{ $attrplaceholder }}" />
    @endif
    @if (request()->is('*soal') || request()->is('*jadwal'))
    <x-native-select wire:model='dataattrlist' :options="$attrlists" placeholder="{{ $attrplaceholder }}"
      option-label="label" option-value="value" />
    @endif
    <x-input type="search" wire:model.debounce.250ms='search' placeholder="Cari Data" right-icon="search" />
  </div>
</div>