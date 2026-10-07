<?php

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use Symfony\Component\HttpFoundation\Response;

// class IsStaff
// {
//     public function handle(Request $request, Closure $next): Response
//     {
//         if ($request->user() && in_array($request->user()->role, ['admin', 'petugas'], true)) {
//             return $next($request);
//         }
//         return response()->json(['message' => 'Akses ditolak. Anda Bukan Admin/Petugas.'], 403);
//     }
// }