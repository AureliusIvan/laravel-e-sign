<?php

namespace App\Http\Middleware;

use App\Models\Dosen;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStatusDosen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $dosen = Dosen::where('user_id', $user->id)->first();
        if ($dosen->status_aktif === 0) {
            abort(403, 'Access denied.');
        }
        return $next($request);
    }
}
