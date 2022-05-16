<x-modal.card blur maxWidth="3xl" title="{{ $modalTitle }}" wire:model='modal' staticbackdrop>
  <form wire:submit.prevent='store' class="flex flex-col gap-3">
    <x-input wire:model.defer='name' label="Nama Soal" placeholder="Masukkan nama soal" />
    <x-select label="Pilih Mata Pelajaran" placeholder="Pilih Mata Pelajaran" searchable wire:model.defer='mapel'
      :options="$listMapel" option-label="name" option-value="id" searchmodel="select_search" />
    <div class="flex flex-col gap-1">
      <x-alabel>Butir Soal (Gunakan tombol di bawah untuk memasukkan kode)</x-alabel>
      <div class="flex justify-between flex-wrap">
        <x-button xs outline rose
          x-on:click="$refs.editor.focus();insertTag($refs.editor,'[soal no=? jenis={PG|PGK|JD|IS|U} skor=?]\n\t\n[/soal]',18)"
          label="soal" class="uppercase" />
        <x-button xs outline rose x-on:click="$refs.editor.focus();insertTag($refs.editor,'[teks][/teks]')" label="teks"
          class="uppercase" />
        <x-button xs outline rose
          x-on:click="$refs.editor.focus();insertTag($refs.editor,'[opsi {A} {benar} relasi=?][/opsi]',10)" label="opsi"
          class="uppercase" />
        <x-button xs outline rose x-on:click="$refs.editor.focus();insertTag($refs.editor,'[jawaban][/jawaban]')"
          label="jawaban" class="uppercase" />
        <x-button xs outline primary x-on:click="$refs.editor.focus();insertTag($refs.editor,'[p][/p]')"
          label="paragraf" class="uppercase" />
        <x-button xs outline primary
          x-on:click="$refs.editor.focus();insertTag($refs.editor,'[g {lebar} {tinggi} {posisi}][/g]',13)"
          label="gambar" class="uppercase" />
        <x-button xs outline primary x-on:click="$refs.editor.focus();insertTag($refs.editor,'[pangkat][/pangkat]')"
          label="pangkat" class="uppercase" />
        <x-button xs outline primary x-on:click="$refs.editor.focus();insertTag($refs.editor,'[sub][/sub]')" label="sub"
          class="uppercase" />
        <x-button xs outline info
          x-on:click="$refs.editor.focus();insertTag($refs.editor,'[tabel {lebar}]\n\t\n[/tabel]',4)" label="tabel"
          class="uppercase" />
        <x-button xs outline info x-on:click="$refs.editor.focus();insertTag($refs.editor,'[baris]\n\t\n[/baris]')"
          label="baris" class="uppercase" />
        <x-button xs outline info
          x-on:click="$refs.editor.focus();insertTag($refs.editor,'[kolom {lebar} {tinggi} {posisi_h} {posisi_v}][/kolom]',19)"
          label="kolom" class="uppercase" />
      </div>
      <x-textarea x-ref="editor" style="min-height: 525px" placeholder='Masukkan kode butir soal disini'
        wire:model.defer='item_soals' x-on:keydown.ctrl.space="insertTag($el,'\t',1);return false;" />
      {{-- @error('item_soals')
      <span class="text-sm text-negative-600">{{ $message }}</span>
      @enderror
      <x-editor :er="$ID" height='400' placeholder='Masukkan kode butir soal disini' wire:model.defer='item_soals' />
      --}}
    </div>
    <div class="flex justify-end gap-2">
      <x-button secondary label="Batal" x-on:click="close" />
      <x-button type="submit" primary label="Simpan" />
    </div>
  </form>
</x-modal.card>