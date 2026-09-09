<?php

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use Symfony\Component\HttpFoundation\Response;

// class CheckRole
// {
//     public function handle(
//         Request $request,
//         Closure $next,
//         string ...$roles
//     ): Response {
//         // Cek apakah user sudah login
//         $user = $request->user();

//         // Cek apakah role user diizinkan
//         if (!$user || !in_array($user->role, $roles, true)) {
//             abort(403, 'Unauthorized action.');
//         }

//         return $next($request);
//     }
// }