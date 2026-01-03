<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Menampilkan semua permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Permission::query();

        // Filter berdasarkan status jika diperlukan (untuk konsistensi dengan Role)
        // Permission dari Spatie tidak punya status, jadi kita skip filter status

        // Search by name
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $permissions = $query->orderBy('name')->get();

        if ($permissions->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data permission masih kosong',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => PermissionResource::collection($permissions)
        ]);
    }

    /**
     * Menampilkan detail permission spesifik.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $permission = Permission::findById($id, 'web');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PermissionResource($permission)
        ]);
    }

    /**
     * Membuat permission baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'guard_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => $validated['guard_name'] ?? 'web',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dibuat',
                'data' => new PermissionResource($permission)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permission: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Memperbarui data permission.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $permission = Permission::findById($id, 'web');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:permissions,name,' . $id],
            'guard_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        try {
            $permission->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil diperbarui',
                'data' => new PermissionResource($permission->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui permission: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Menghapus permission.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $permission = Permission::findById($id, 'web');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission tidak ditemukan'
            ], 404);
        }

        try {
            $permission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus permission: ' . $e->getMessage()
            ], 400);
        }
    }
}

