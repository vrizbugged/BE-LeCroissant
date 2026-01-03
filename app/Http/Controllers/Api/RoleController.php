<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\RoleResource;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Services\Contracts\RoleServiceInterface; // Pastikan Interface ini ada
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * @var RoleServiceInterface
     */
    protected $roleService;

    /**
     * RoleController Constructor.
     * Menggunakan Dependency Injection untuk memisahkan logic.
     */
    public function __construct(RoleServiceInterface $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Menampilkan semua role.
     * [Ref Shine: Logika index dengan resource collection]
     */
    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();

        if ($roles->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data role masih kosong',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => RoleResource::collection($roles)
        ]);
    }

    /**
     * Membuat role baru.
     * [Ref Shine: Menggunakan StoreRequest untuk validasi]
     */
    public function store(RoleStoreRequest $request): JsonResponse
    {
        // Data sudah divalidasi oleh RoleStoreRequest sebelum masuk sini
        $role = $this->roleService->createRole($request->validated());

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat role baru'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dibuat',
            'data' => new RoleResource($role)
        ], 201);
    }

    /**
     * Menampilkan detail role spesifik.
     */
    public function show(string $id): JsonResponse
    {
        $role = $this->roleService->getRoleById($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RoleResource($role)
        ]);
    }

    /**
     * Memperbarui data role.
     * [Ref Shine: Menggunakan UpdateRequest]
     */
    public function update(RoleUpdateRequest $request, string $id): JsonResponse
    {
        $role = $this->roleService->updateRole($id, $request->validated());

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui role atau role tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil diperbarui',
            'data' => new RoleResource($role)
        ]);
    }

    /**
     * Menghapus role.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->roleService->deleteRole($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan atau tidak bisa dihapus'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus'
        ]);
    }

    /**
     * Menambahkan permission ke role tertentu.
     * Fitur tambahan untuk manajemen hak akses Admin Le Croissant.
     */
    public function addPermission(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name'
        ]);

        $role = $this->roleService->addPermissionToRole($id, $request->permission);

        if (!$role) {
            return response()->json(['message' => 'Gagal menambahkan izin'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Izin berhasil ditambahkan ke Role',
            'data' => new RoleResource($role)
        ]);
    }
}

