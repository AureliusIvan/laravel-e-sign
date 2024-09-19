<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $program = ProgramStudi::all();
        return view('auth.register', [
            'program' => $program
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Fix the nama to proper format
        $request->validate([
            'nim' => ['required', 'unique:' . Mahasiswa::class],
            'nama' => ['required', 'string', 'max:255'],
            'program_studi' => ['required'],
            'angkatan' => ['required'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, 'ends_with:student.umn.ac.id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults(), 'min:8'],
        ]);

        try {
            $user = null;
            DB::transaction(function () use ($request, &$user) {
                DB::table('users')->lockForUpdate()->get();

                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'mahasiswa',
                ]);

                $lastUserId = $user->id;
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstorFail();

                Mahasiswa::create([
                    'user_id' => $lastUserId,
                    'nim' => $request->nim,
                    'nama' => $request->nama,
                    'program_studi_id' => $program->id,
                    'angkatan' => $request->angkatan,
                ]);

                // event(new Registered($user));

                // Auth::login($user);
            });

            // return redirect(route('dashboard', absolute: false));

            return redirect()->route('login');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan registrasi. Silahkan coba kembali');
        }
    }
}