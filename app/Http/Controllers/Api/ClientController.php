<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ClientResource;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Services\Contracts\ClientServiceInterface; // Pastikan Interface ini ada
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * @var ClientServiceInterface
     */
    protected $clientService;

    /**
     * ClientController Constructor.
     * Dependency Injection untuk memisahkan logika bisnis B2B.
     */
    public function __construct(ClientServiceInterface $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Menampilkan daftar klien B2B.
     * [Ref Proposal: Mengelola data klien B2B secara terpusat]
     */
    public function index(Request $request): JsonResponse
    {
        // Fitur Filter Lanjutan untuk Analisis Pasar
        // Contoh: /api/clients?sector=Hotel
        $sector = $request->query('sector');

        if ($sector) {
            $clients = $this->clientService->getClientsByBusinessSector($sector);
        } else {
            $clients = $this->clientService->getAllClients();
        }

        if ($clients->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data klien B2B',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => ClientResource::collection($clients)
        ]);
    }

    /**
     * Menambahkan data klien baru (Manual oleh Admin).
     */
    public function store(ClientStoreRequest $request): JsonResponse
    {
        // Validasi data bisnis (Nama Perusahaan, Sektor) ditangani Request
        $client = $this->clientService->createClient($request->validated());

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data klien'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mitra bisnis berhasil ditambahkan',
            'data' => new ClientResource($client)
        ], 201);
    }

    /**
     * Menampilkan detail profil bisnis klien.
     */
    public function show(string $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Data klien tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ClientResource($client)
        ]);
    }

    /**
     * Memperbarui data profil bisnis klien.
     */
    public function update(ClientUpdateRequest $request, string $id): JsonResponse
    {
        $client = $this->clientService->updateClient($id, $request->validated());

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data klien'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil bisnis berhasil diperbarui',
            'data' => new ClientResource($client)
        ]);
    }

    /**
     * Menghapus data klien.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->clientService->deleteClient($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Klien tidak ditemukan atau memiliki pesanan aktif'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data klien berhasil dihapus'
        ]);
    }

    /**
     * Verifikasi Akun Klien (Fitur Kunci Proposal).
     * [Ref Proposal: Admin memverifikasi pesanan/klien B2B]
     */
    public function verify(string $id): JsonResponse
    {
        // Memanggil service khusus untuk mengubah status Pending -> Aktif
        $client = $this->clientService->verifyClientAccount($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi klien'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Akun klien berhasil diverifikasi. Klien kini dapat memesan.',
            'data' => new ClientResource($client)
        ]);
    }

    /**
     * Laporan Segmentasi Pasar (Fitur Analisis Proposal).
     * [Ref Proposal: Mendukung potensi pertumbuhan bisnis di pasar grosir]
     */
    public function marketAnalysis(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        $report = $this->clientService->getMarketSegmentReport($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }
}
