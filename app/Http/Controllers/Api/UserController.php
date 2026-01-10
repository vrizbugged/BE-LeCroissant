<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->query('per_page', 15));
        $query = User::query()->orderByDesc('id');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function store(Request $request)
    {
        // Convert role ke lowercase sebelum validasi jika ada
        $requestData = $request->all();
        if (isset($requestData['role'])) {
            $requestData['role'] = strtolower($requestData['role']);
        }
        $request->merge($requestData);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['sometimes', 'string', 'same:password'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'role' => ['nullable', Rule::in(['admin', 'klien_b2b'])],
            'status' => ['nullable', Rule::in(['Aktif', 'Non Aktif'])],
        ]);

        // Set default status jika tidak ada
        if (!isset($validated['status'])) {
            $validated['status'] = 'Aktif';
        }

        // Hapus password_confirmation dari validated karena tidak perlu disimpan
        unset($validated['password_confirmation']);

        // Password akan otomatis di-hash oleh Laravel karena ada cast 'hashed' di model

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'User created',
            'data' => $user,
        ], 201);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            // Hapus password_confirmation dari validated karena tidak perlu disimpan
            unset($validated['password_confirmation']);

            // Jika password kosong atau null, jangan update password
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            // Extract role dari validated untuk di-handle terpisah
            $roleName = $validated['role'] ?? null;
            unset($validated['role']);

            // Wrap dalam transaction untuk memastikan konsistensi data
            $updatedUser = DB::transaction(function () use ($user, $validated, $roleName) {
                // Update user fields
                $user->update($validated);

                // Sync roles jika role ada di request
                if ($roleName) {
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $user->syncRoles([$role->name]);
                    }
                }

                // Clear permission cache setelah role update
                app()[PermissionRegistrar::class]->forgetCachedPermissions();

                return $user->fresh()->load('roles');
            });

            return response()->json([
                'success' => true,
                'message' => 'User updated',
                'data' => $updatedUser,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            // Cek apakah user mencoba menghapus dirinya sendiri
            if (auth()->check() && auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage(), [
                'user_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memperbarui Status User secara khusus (Aktif/Non Aktif).
     * Endpoint ini berguna untuk fitur "Blokir" atau "Aktivasi" user.
     */
    public function updateStatus(string $id, Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:Aktif,Non Aktif'],
        ]);

        $user = User::findOrFail($id);
        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status user berhasil diperbarui',
            'data' => $user,
        ]);
    }
}

