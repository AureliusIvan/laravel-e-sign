<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Mahasiswa;
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

            $user = User::create([
                'email' => 'admin2@umn.ac.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);

            $lastUserId = $user->id;

            Admin::create([
                'user_id' => $lastUserId,
                'nama' => 'Admin 2',
            ]);
        });
    }
}
