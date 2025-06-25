<?php

namespace App\Http\Middleware;

use App\Models\Mahasiswa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStatusMahasiswa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

        if ($mahasiswa->status_aktif_skripsi === 0) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}