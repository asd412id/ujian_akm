<x-modal.card maxWidth="5xl" blur title="{{ $modalTitle }}" wire:model='modal' staticbackdrop>
  <form wire:submit.prevent='store' class="flex flex-col gap-3">
    <div class="flex gap-5">
      <div class="flex flex-col gap-2 w-6/12">
        <x-input wire:model.defer='name' label="Nama Jadwal" placeholder="Masukkan nama jadwal" />
        <x-textarea wire:model.defer='desc' label="Deskripsi" placeholder="Masukkan deskripsi jadwal" />
        <x-datetime-picker wire:model.defer='start' label="Waktu Mulai" placeholder="Masukkan waktu mulai ujian"
          display-format="DD/MM/YYYY HH:mm" time-format="24" />
        <x-datetime-picker wire:model.defer='end' label="Waktu Selesai" placeholder="Masukkan waktu selesai ujian"
          display-format="DD/MM/YYYY HH:mm" time-format="24" />
        <x-inputs.maskable wire:model.defer='duration' label="Masukkan Durasi Ujian (menit)"
          placeholder="Durasi dalam menit" mask="####" />
      </div>
      <div class="flex flex-col gap-2 w-6/12">
        <x-select label="Pilih Ruangan" multiselect placeholder="Pilih Ruangan Peserta" searchable
          wire:model.defer='ruangs' :options="$listRuang" option-label="label" option-value="value"
          searchmodel="select_ruang" />
        <x-select label="Pilih Soal" multiselect placeholder="Pilih Soal Ujian" searchable wire:model='soals'
          :options="$listSoal" option-label="label" option-value="value" searchmodel="select_soal" />
        <x-inputs.maskable wire:model.defer='soal_count' label="Jumlah Soal" placeholder="Masukkan Jumlah Soal"
          mask="####" />
        <x-toggle lg label="Acak Soal" wire:model.defer="shuffle" />
        <x-toggle lg label="Tampilkan Nilai Akhir" wire:model.defer="show_score" />
        <x-toggle lg label="Aktifkan Jadwal" wire:model.defer="active" />
      </div>
    </div>
    <div class="flex justify-end gap-2">
      <x-button secondary label="Batal" x-on:click="close" />
      <x-button type="submit" primary label="Simpan" />
    </div>
  </form>
</x-modal.card>