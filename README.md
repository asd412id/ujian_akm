# Aplikasi Ujian
Merupakan aplikasi ujian yang dikembangkan menggunakan framework Laravel dengan livewire untuk memudahkan dalam ujian online/offline di sekolah-sekolah.

## Aplikasi ujian ini memiliki fitur:
1. 1 Super Admin
2. Multi User/Sekolah
3. Menerapkan 6 Model Soal AKM: **Pilihan Ganda**, **Pilihan Ganda Kompleks**, **Isian Singkat**, **Menjodohkan**, **Benar/Salah**, **Uraian**
4. Penskoran Otomatis & Manual
5. Login menggunakan Kode QR *(HTTPS Only)*
6. Import data & soal menggunakan Excel
7. Cetak absensi dan nilai ujian
8. dll

## Cara Install
- Pastikan memiliki web server yang mendukung php *(xampp, wampp, laragon, dll)* dengan versi php minimal 8. [Download Xampp](https://www.apachefriends.org/download.html)
- Pastikan sudah menginstall [Composer](https://getcomposer.org/download/)
- Clone repo ini ke dalam folder root aplikasi web server
- Buka terminal/cmd dan arahkan ke folder hasil clone, kemudian jalankan perintah **_composer install_**
- Buat database mysql (gunakan phpmyadmin pada xampp atau aplikasi lain)
- Copy **.env.example** dan paste dengan nama **.env**
- Buka file **.env** dan masukkan data aplikasi di dalamnya seperti **DB_DATABASE** untuk nama database yang dibuat sebelumnya, **DB_USERNAME** untuk username database *(default: root untuk xampp)*, **DB_PASSWORD** untuk password database *(defalut: kosongkan untuk xampp)*. Simpan perubahan!
- Kembali ke terminal, jalankan perintah **_php artisan key:generate_**
- Selanjutnya jalankan **_php artisan migrate --seed_**
- Jalankan web server, dan buka *http://localhost/folder_ujian/public*
- Agar folder *public* tidak muncul pada alamat url, ubah root folder pada aplikasi web server agar mengarah ke folder **_public_** aplikasi ujian

## Petunjuk Awal Penggunaan
- Halaman awal adalah halaman login siswa yang dilengkapi dengan scan kode QR jika website menggunakan ssl/https
- Klik *Masuk Sebagai Administrator*. Masukkan email: *admin@ujianq.id* dan password: *passwordAdmin* untuk login sebagai Super Admin
- Klik tambah data, dan masukkan data sekolah termasuk email dan password login, simpan
- Logout dari akun Super Admin, dan masukkan email dan password sekolah yang sudah dibuat tadi
- Atur data sekolah seperti mata pelajaran, penilai, siswa, dan ujian

## Info Tambahan
Jika ada pertanyaan, silahkan mampir di laman [Facebook](https://www.facebook.com/aezdar) atau [Twitter](https://www.twitter.com/asd412id)