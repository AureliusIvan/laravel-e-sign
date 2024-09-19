<x-guest-layout>
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-ban"></i>Failed!</h5>
        {{ session('error') }}
    </div>
    @endif

    <div class="text-center p-2">
        <h1 style="font-size: 2em; font-weight: bold;">Register</h1>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nim -->
        <div>
            <x-input-label for="nim" :value="__('NIM Lengkap')" />
            <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim" :value="old('nim')" required autofocus
                autocomplete="nim" />
            <x-input-error :messages="$errors->get('nim')" class="mt-2" />
        </div>

        <!-- Nama -->
        <div class="mt-1">
            <x-input-label for="nama" :value="__('Nama Lengkap')" />
            <x-text-input id="nama" class="block mt-1 w-full" type="text" name="nama" :value="old('nama')" required autofocus
                autocomplete="nama" />
            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
        </div>

        <!-- Program Studi -->
        <div class="mt-1">
            <x-input-label for="program_studi" :value="__('Program Studi')" />
            <select name="program_studi" id="program_studi" class="block mt-1 w-full rounded-md" required>
                <option value="">Pilih ...</option>
                @foreach ($program as $p)
                <option value="{{ $p->uuid }}" {{ old('program_studi') == $p->uuid ? 'selected' : '' }}>
                    {{ $p->program_studi }}
                </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('program_studi')" class="mt-2" />
        </div>

        <!-- Angkatan -->
        <div class="mt-1">
            <x-input-label for="angkatan" :value="__('Angkatan')" />
            <x-text-input id="angkatan" class="block mt-1 w-full" type="number" min="2000" name="angkatan"
                :value="old('angkatan')" required autocomplete="angkatan" />
            <x-input-error :messages="$errors->get('angkatan')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-1">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-1">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-1">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation"
                required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>



</x-guest-layout>