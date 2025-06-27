# Sistem 3 Persetujuan untuk Mahasiswa

## Ringkasan
Sistem ini telah dimodifikasi untuk memastikan bahwa **setiap mahasiswa memerlukan persetujuan dari 3 (tiga) penilai yang berbeda** sebelum proposal skripsi mereka dapat dinyatakan lulus secara penuh.

## ⚠️ **MEKANISME RESET BARU**
**Jika ada satu penilai yang menolak, semua persetujuan akan direset dan proses evaluasi dimulai dari awal.**

### Cara Kerja Reset:
- ❌ **Satu penolakan = Reset semua**: Ketika satu evaluator menolak, sistem akan:
  - Reset semua `status_approval_penilaiX` menjadi `null`
  - Kecuali evaluator yang menolak tetap `0` (rejected)
  - Set `status_akhir` menjadi `null` (kembali ke status evaluasi)
  - Hapus komentar penolakan evaluator lain
- 🔄 **Evaluasi ulang**: Semua evaluator harus mengevaluasi ulang dari awal
- ✅ **Persetujuan 3/3**: Hanya jika SEMUA 3 evaluator menyetujui, proposal dinyatakan lulus

## Perubahan yang Dilakukan

### 1. Modifikasi Controller Pengumpulan (`ProposalSkripsiController`)
- **Perubahan**: Proposal yang baru disubmit tidak lagi otomatis mendapat penilai1 dan penilai2
- **Alasan**: Memastikan admin/kaprodi menentukan 3 penilai yang independen
- **File**: `app/Http/Controllers/Pengumpulan/ProposalSkripsiController.php`

### 2. Penguatan Logika Persetujuan (`PeriksaProposalController`)
- **Perubahan Utama**: 
  - **Reset Mechanism**: Jika ada penolakan, reset semua approval ke `null`
  - **3-Approval Requirement**: Status proposal hanya menjadi "lulus" jika SEMUA 3 penilai memberikan persetujuan
  - **No Shortcuts**: Tidak ada toleransi untuk penolakan - semua harus menyetujui
- **File**: `app/Http/Controllers/Periksa/PeriksaProposalController.php`

### 3. Validasi Penugasan Evaluator (`PenilaiProposalController`)
- **Perubahan**: Validasi memastikan ketiga evaluator berbeda dan terisi semua
- **Pesan Error**: Pesan validasi yang jelas jika evaluator tidak lengkap/sama
- **File**: `app/Http/Controllers/PenilaiProposalController.php`

### 4. Antarmuka Pengguna Enhanced
- **Progress Bars**: Menunjukkan progress penugasan evaluator (x/3) dan persetujuan (x/3)
- **Reset Indicator**: Progress bar merah "DIRESET" ketika ada penolakan
- **Status Messaging**: 
  - ✅ "Disetujui" - evaluator menyetujui
  - ❌ "Ditolak - Reset Semua" - evaluator yang menolak
  - 🔄 "Direset (Penilai lain menolak)" - evaluator lain yang terkena reset
  - ⏳ "Menunggu Evaluasi" - belum dievaluasi
- **Warning**: Peringatan jelas tentang mekanisme reset
- **File**: `resources/views/pages/mahasiswa/hasil-proposal/hasil-proposal.blade.php`

### 5. Data Testing Enhanced (`DatabaseSeeder`)
- **Test Case 1**: Proposal dengan 3/3 persetujuan (lulus)
- **Test Case 2**: Proposal dengan 2/3 persetujuan (menunggu)
- **Test Case 3**: Proposal dengan 1 penolakan (direset)
- **Test Case 4**: Proposal dengan evaluator belum lengkap
- **File**: `database/seeders/DatabaseSeeder.php`

## Status Proposal

### 🟢 **Lulus (status_akhir = 1)**
- Semua 3 evaluator memberikan persetujuan (`status_approval_penilaiX = 1`)
- Tidak ada penolakan sama sekali

### 🔄 **Dalam Evaluasi (status_akhir = null)**
- Ada evaluator yang belum dievaluasi
- Atau ada penolakan yang menyebabkan reset (kembali ke evaluasi)

### 🔴 **Perlu Evaluasi Ulang**
- Ada evaluator yang menolak (`status_approval_penilaiX = 0`)
- Semua evaluator lain direset ke `null`
- Proses evaluasi dimulai dari awal

## Akun Testing

**Mahasiswa:**
- `mahasiswa@umn.ac.id` - Fully approved (3/3) ✅
- `mahasiswa2@umn.ac.id` - Partial approval (2/3) ⏳  
- `mahasiswa3@umn.ac.id` - Reset scenario (1 rejection) 🔄
- `mahasiswa4@umn.ac.id` - Unassigned evaluators 👥

**Evaluator:**
- `dosen1@umn.ac.id`, `dosenpenguji@umn.ac.id`, `dosenketua@umn.ac.id`

**Admin:**
- `admin2@umn.ac.id`, `kaprodi@umn.ac.id`

🔑 **Password semua akun:** `password`

## Fitur Utama

1. **✅ 3 Persetujuan Wajib**: Tidak ada toleransi - harus 3/3
2. **🔄 Reset Mechanism**: Satu penolakan = reset semua
3. **👥 3 Evaluator Berbeda**: Validasi evaluator tidak boleh sama
4. **📊 Visual Progress**: Progress bar dan status yang jelas
5. **⚠️ Clear Warnings**: Peringatan tentang mekanisme reset

**Sistem ini memastikan kualitas proposal yang tinggi dengan tidak memberikan toleransi terhadap penolakan.**

## Alur Kerja Baru

### Untuk Mahasiswa:
1. Submit proposal skripsi
2. Tunggu admin/kaprodi menentukan 3 penilai
3. Proposal baru bisa dilihat statusnya setelah 3 penilai ditentukan
4. **Lulus hanya jika SEMUA 3 penilai menyetujui**

### Untuk Admin/Kaprodi:
1. Lihat proposal yang masuk
2. **WAJIB** menentukan 3 penilai yang berbeda untuk setiap proposal
3. Sistem akan memvalidasi bahwa ketiga penilai berbeda

### Untuk Dosen Penilai:
1. Evaluasi proposal sesuai tugasnya (sebagai penilai1, penilai2, atau penilai3)
2. Berikan persetujuan atau penolakan
3. Status akhir proposal akan otomatis terhitung berdasarkan 3 persetujuan

## Dampak Perubahan

### Keuntungan:
- ✅ Memastikan evaluasi yang lebih objektif dengan 3 perspektif berbeda
- ✅ Mengurangi bias karena melibatkan lebih banyak evaluator
- ✅ Standar yang lebih tinggi untuk kelulusan mahasiswa
- ✅ Transparansi proses evaluasi

### Konsekuensi:
- ⚠️ Proses evaluasi menjadi lebih panjang (perlu 3 persetujuan)
- ⚠️ Admin/kaprodi harus lebih selektif dalam menentukan penilai
- ⚠️ Mahasiswa perlu memastikan proposal berkualitas tinggi

## Testing
Gunakan route test untuk memverifikasi sistem:
```
GET /test-approval-system
```

Route ini akan menampilkan:
- Daftar semua proposal
- Status penilai yang ditentukan
- Status persetujuan dari masing-masing penilai
- Apakah proposal sudah memenuhi syarat 3 persetujuan

## Catatan Penting
- Sistem ini memastikan kualitas evaluasi yang lebih tinggi
- Mahasiswa harus mempersiapkan proposal dengan lebih baik
- Admin perlu mengalokasikan 3 dosen penilai untuk setiap proposal
- Tidak ada "jalan pintas" - semua 3 penilai harus menyetujui untuk lulus 