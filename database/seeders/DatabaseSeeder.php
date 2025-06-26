<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PembimbingMahasiswa;
use App\Models\ProposalSkripsiForm;
use App\Models\ProposalSkripsi;
use App\Models\ResearchList;
use App\Models\TopikPenelitianProposal;
use Illuminate\Support\Str;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ProgramStudi;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen1@umn.ac.id',
            //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000001',
        //         'nama' => 'Dosen 1',
        //         'gelar' => 'S.Kom., M.Kom',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen2@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000002',
        //         'nama' => 'Dosen 2',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen3@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000003',
        //         'nama' => 'Dosen 3',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen4@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000004',
        //         'nama' => 'Dosen 4',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen5@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000005',
        //         'nama' => 'Dosen 5',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen6@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000006',
        //         'nama' => 'Dosen 6',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen7@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000007',
        //         'nama' => 'Dosen 7',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen8@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000008',
        //         'nama' => 'Dosen 8',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'dosen9@umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'dosen',
        //     ]);

        //     $lastUserId = $user->id;

        //     Dosen::create([
        //         'user_id' => $lastUserId,
        //         'nid' => '000009',
        //         'nama' => 'Dosen 9',
        //         'program_studi_id' => 1,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'mahasiswa5@student.umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'mahasiswa',
        //     ]);

        //     $lastUserId = $user->id;

        //     Mahasiswa::create([
        //         'user_id' => $lastUserId,
        //         'nim' => '00000000005',
        //         'nama' => 'Mahasiswa 5',
        //         'program_studi_id' => 1,
        //         'angkatan' => 2020,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'mahasiswa6@student.umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'mahasiswa',
        //     ]);

        //     $lastUserId = $user->id;

        //     Mahasiswa::create([
        //         'user_id' => $lastUserId,
        //         'nim' => '00000000006',
        //         'nama' => 'Mahasiswa 6',
        //         'program_studi_id' => 1,
        //         'angkatan' => 2020,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'mahasiswa7@student.umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'mahasiswa',
        //     ]);

        //     $lastUserId = $user->id;

        //     Mahasiswa::create([
        //         'user_id' => $lastUserId,
        //         'nim' => '00000000007',
        //         'nama' => 'Mahasiswa 7',
        //         'program_studi_id' => 1,
        //         'angkatan' => 2020,
        //     ]);
        // });

        // DB::transaction(function () {
        //     DB::table('users')->lockForUpdate()->get();

        //     $user = User::create([
        //         'email' => 'mahasiswa8@student.umn.ac.id',
        //         'password' => Hash::make('password'),
        //         'role' => 'mahasiswa',
        //     ]);

        //     $lastUserId = $user->id;

        //     Mahasiswa::create([
        //         'user_id' => $lastUserId,
        //         'nim' => '00000000008',
        //         'nama' => 'Mahasiswa 8',
        //         'program_studi_id' => 1,
        //         'angkatan' => 2020,
        //     ]);
        // });

        // create admin
        DB::transaction(function () {
            DB::table('users')->lockForUpdate()->get();

            $user = User::updateOrCreate(
                ['email' => 'admin2@umn.ac.id'],
                [
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                ]
            );

            $lastUserId = $user->id;

            Admin::updateOrCreate(
                ['user_id' => $user->id],
                ['nama' => 'Admin 2']
            );

            // Create kaprodi account (for thesis purpose)
            $userKaprodi = User::updateOrCreate(
                ['email' => 'kaprodi@umn.ac.id'],
                [
                    'password' => Hash::make('password'),
                    'role' => 'kaprodi',
                ]
            );

            $kaprodi = Dosen::updateOrCreate(
                ['nid' => '000000'],
                [
                    'user_id' => $userKaprodi->id,
                    'nama' => 'Dr. Kepala Program Studi',
                    'gelar' => 'S.Kom., M.Kom., Ph.D',
                    'program_studi_id' => 1,
                ]
            );

            // Create dosen account (for thesis purpose)
            $userDosen = User::updateOrCreate(
                ['email' => 'dosen1@umn.ac.id'],
                [
                    'password' => Hash::make('password'),
                    'role' => 'dosen',
                ]
            );

            $dosen = Dosen::updateOrCreate(
                ['nid' => '000001'],
                [
                    'user_id' => $userDosen->id,
                    'nama' => 'Dosen 1',
                    'gelar' => 'S.Kom., M.Kom',
                    'program_studi_id' => 1,
                ]
            );

            // Create mahasiswa account (for thesis purpose)
            $userMahasiswa = User::updateOrCreate(
                ['email' => 'mahasiswa@umn.ac.id'],
                [
                    'password' => Hash::make('password'),
                    'role' => 'mahasiswa',
                ]
            );

            $mahasiswa = Mahasiswa::updateOrCreate(
                ['nim' => '00000000001'],
                [
                    'user_id' => $userMahasiswa->id,
                    'nama' => 'Mahasiswa 1',
                    'program_studi_id' => 1,
                    'angkatan' => 2020,
                    'status_aktif_skripsi' => 1,
                ]
            );

            // crete new tahun ajaran (for thesis purpose)
            $tahunAjaran = TahunAjaran::updateOrCreate(
                ['tahun' => 2021, 'semester' => 'Ganjil'],
                ['status_aktif' => 1]
            );

            // Assign dosen as pembimbing to mahasiswa
            PembimbingMahasiswa::updateOrCreate(
                [
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'mahasiswa' => $mahasiswa->id,
                ],
                [
                    'program_studi_id' => 1,
                    'pembimbing1' => $dosen->id,
                ]
            );

            // Create Skripsi form (for thesis purpose)
            $proposalForm = ProposalSkripsiForm::updateOrCreate(
                ['judul_form' => 'Form Skripsi 2021'],
                [
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'program_studi_id' => 1,
                    'keterangan' => 'Form untuk mengumpulkan Skripsi tahun ajaran 2021 semester ganjil',
                    'dibuka' => now()->subDays(30),
                    'ditutup' => now()->addDays(30),
                    'deadline_penilaian' => now()->addDays(60),
                    'publish_dosen' => 1,
                ]
            );

            // Create research topics (for thesis purpose)
            $researchTopic1 = ResearchList::updateOrCreate(
                ['kode_penelitian' => 'WEB001'],
                [
                    'program_studi_id' => 1,
                    'topik_penelitian' => 'Pengembangan Sistem Informasi Berbasis Web',
                    'deskripsi' => 'Penelitian tentang pengembangan aplikasi web menggunakan teknologi modern seperti Laravel, React, dan database management.',
                ]
            );

            $researchTopic2 = ResearchList::updateOrCreate(
                ['kode_penelitian' => 'SEC001'],
                [
                    'program_studi_id' => 1,
                    'topik_penelitian' => 'Keamanan Sistem Informasi',
                    'deskripsi' => 'Penelitian tentang implementasi keamanan dalam sistem informasi, termasuk enkripsi, digital signature, dan authentication.',
                ]
            );

            $researchTopic3 = ResearchList::updateOrCreate(
                ['kode_penelitian' => 'AI001'],
                [
                    'program_studi_id' => 1,
                    'topik_penelitian' => 'Artificial Intelligence dan Machine Learning',
                    'deskripsi' => 'Penelitian tentang penerapan AI dan ML dalam berbagai domain aplikasi.',
                ]
            );

            // Create sample Skripsi (for thesis purpose)
            $proposalSkripsi = ProposalSkripsi::updateOrCreate(
                ['mahasiswa_id' => $mahasiswa->id],
                [
                    'proposal_skripsi_form_id' => $proposalForm->id,
                    'judul_proposal' => 'Sistem Informasi Manajemen Tugas Akhir Berbasis Web dengan Implementasi Digital Signature',
                    'file_proposal' => '00000000001_Mahasiswa1_SistemInformasiManajemen.pdf',
                    'file_proposal_random' => '20241210_sample_proposal_random.pdf',
                    'status' => 1,
                    'available_at' => '2021-Ganjil',
                    'available_until' => '2021-Ganjil',
                    'is_expired' => 0,
                ]
            );

            // Link research topics to proposal (for thesis purpose)
            TopikPenelitianProposal::updateOrCreate(
                [
                    'proposal_skripsi_id' => $proposalSkripsi->id,
                    'research_list_id' => $researchTopic1->id,
                ]
            );

            TopikPenelitianProposal::updateOrCreate(
                [
                    'proposal_skripsi_id' => $proposalSkripsi->id,
                    'research_list_id' => $researchTopic2->id,
                ]
            );
        });
    }
}
