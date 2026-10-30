<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogAktivitasResource;
use App\Models\LogAktivitas;
use Illuminate\Http\JsonResponse;

class LogAktivitasController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = LogAktivitas::with('user')->latest()->paginate(15);

        return LogAktivitasResource::collection($logs)
            ->additional(['message' => 'Seluruh catatan log aktivitas berhasil diambil.'])
            ->response();
    }
}
