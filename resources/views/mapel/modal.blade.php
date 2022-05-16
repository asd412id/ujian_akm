<x-modal.card maxWidth="sm" blur title="{{ $modalTitle }}" wire:model='modal' staticbackdrop>
  <form wire:submit.prevent='store' class="flex flex-col gap-3">
    <x-input wire:model.defer='name' label="Nama Mata Pelajaran" placeholder="Masukkan nama mapel" />
    <x-select label="Pilih Penilai" placeholder="Pilih Penilai" multiselect searchable wire:model.defer='penilai'
      :options="$listPenilai" option-label="name" option-value="id" searchmodel="select_search" />
    <div class="flex justify-end gap-2">
      <x-button secondary label="Batal" x-on:click="close" />
      <x-button type="submit" primary label="Simpan" />
    </div>
  </form>
</x-modal.card>