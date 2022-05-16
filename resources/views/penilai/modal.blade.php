<x-modal.card maxWidth="sm" blur title="{{ $modalTitle }}" wire:model='modal' staticbackdrop>
  <form wire:submit.prevent='store' class="flex flex-col gap-3">
    <x-input wire:model.defer='name' label="Nama Lengkap" placeholder="Masukkan nama lengkap" />
    <x-input type="email" wire:model.defer='email' label="Alamat Email" placeholder="Masukkan alamat email" />
    <x-input type="password" wire:model.defer='password' label="Password" placeholder="Masukkan password" />
    <x-input type="password" wire:model.defer='repassword' label="Ulang Password"
      placeholder="Masukkan ulang password" />
    <x-select label="Pilih Mata Pelajaran" placeholder="Pilih Mata Pelajaran" multiselect searchable
      wire:model.defer='mapel' :options="$listMapel" option-label="name" option-value="id"
      searchmodel="select_search" />
    <div class="flex justify-end gap-2">
      <x-button secondary label="Batal" x-on:click="close" />
      <x-button type="submit" primary label="Simpan" />
    </div>
  </form>
</x-modal.card>