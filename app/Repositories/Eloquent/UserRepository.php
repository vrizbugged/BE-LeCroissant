<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Mengambil semua users.
     *
     * @return mixed
     */
    public function getAllUsers()
    {
        return $this->model->with('roles')->get();
    }

    /**
     * Mengambil user berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getUserById($id)
    {
        try {
            // Mengambil user berdasarkan ID, handle jika tidak ditemukan
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("User with ID {$id} not found.");
            return null;
        }
    }

    /**
     * Mengambil user berdasarkan nama.
     *
     * @param string $name
     * @return mixed
     */
    public function getUserByName($name)
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Mengambil user berdasarkan status.
     *
     * @param string $status
     * @return mixed
     */
    public function getUserByStatus($status)
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Membuat user baru.
     *
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data)
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            Log::error("Failed to create user: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Memperbarui user berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateUser($id, array $data)
    {
        $user = $this->findUser($id);

        if ($user) {
            try {
                $user->update($data);
                return $user;
            } catch (\Exception $e) {
                Log::error("Failed to update user with ID {$id}: {$e->getMessage()}");
                return null;
            }
        }
        return null;
    }

    /**
     * Menghapus user berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function deleteUser($id)
    {
        $user = $this->findUser($id);

        if ($user) {
            try {
                $user->delete();
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to delete user with ID {$id}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }

    /**
     * Helper method untuk menemukan user berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    protected function findUser($id)
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("User with ID {$id} not found.");
            return null;
        }
    }

    /**
     * Mengupdate user status.
     *
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function updateUserStatus($id, $status)
    {
        $user = $this->findUser($id);

        if ($user) {
            $user->status = $status;
            $user->save();
            return $user;
        }
        return null;
    }
}
