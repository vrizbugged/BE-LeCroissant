<?php

namespace App\Services\Implementations;

use Illuminate\Support\Facades\Cache;
use App\Services\Contracts\UserServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    protected $repository;

    const USERS_ALL_CACHE_KEY = 'users.all';
    const USERS_ACTIVE_CACHE_KEY = 'users.active';
    const USERS_INACTIVE_CACHE_KEY = 'users.inactive';

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Mengambil semua users.
     *
     * @return mixed
     */
    public function getAllUsers()
    {
        return Cache::remember(self::USERS_ALL_CACHE_KEY, 3600, function () {
            return $this->repository->getAllUsers();
        });
    }

    /**
     * Mengambil user berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getUserById($id)
    {
        return $this->repository->getUserById($id);
    }

    /**
     * Mengambil user berdasarkan nama.
     *
     * @param string $name
     * @return mixed
     */
    public function getUserByName($name)
    {
        return $this->repository->getUserByName($name);
    }

    /**
     * Mengambil user berdasarkan status.
     *
     * @param string $status
     * @return mixed
     */
    public function getUserByStatus($status)
    {
        return $this->repository->getUserByStatus($status);
    }

    /**
     * Mengambil users dengan status aktif.
     *
     * @return mixed
     */
    public function getActiveUsers()
    {
        return Cache::remember(self::USERS_ACTIVE_CACHE_KEY, 3600, function () {
            return $this->repository->getUserByStatus('Aktif');
        });
    }

    /**
     * Mengambil users dengan status tidak aktif.
     *
     * @return mixed
     */
    public function getInactiveUsers()
    {
        return Cache::remember(self::USERS_INACTIVE_CACHE_KEY, 3600, function () {
            return $this->repository->getUserByStatus('Non Aktif');
        });
    }

    /**
     * Membuat user baru.
     *
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data)
    {
        $role = $data['role'] ?? null;

        // Membuat user baru
        $user = $this->repository->createUser($data);

        // Sinkronisasi permissions
        if ($user && $role) {
            $user->assignRole($role);
        }

        // Clear cache
        $this->clearUserCaches();

        return $user;
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
        $role = $data['role'] ?? null;

        // Memperbarui user
        $user = $this->repository->updateUser($id, $data);

        // Sinkronisasi permissions
        if ($role && $user) {
            $user->syncRoles([$role]);
        }

        // Clear cache
        $this->clearUserCaches($id);

        return $user;
    }

    /**
     * Menghapus user berdasarkan ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser($id)
    {
        // Menghapus user
        $result = $this->repository->deleteUser($id);

        // Clear cache
        $this->clearUserCaches($id);

        return $result;
    }

    public function updateUserStatus($id, $status)
    {
        $user = $this->getUserById($id);

        if ($user) {
            $result = $this->repository->updateUserStatus($id, $status);

            $this->clearUserCaches($id);

            return $result;
        }

        return null;
    }

    /**
     * Menghapus semua cache user
     *
     * @param int|null $id
     * @return void
     */
    public function clearUserCaches($id = null)
    {
        Cache::forget(self::USERS_ALL_CACHE_KEY);
        Cache::forget(self::USERS_ACTIVE_CACHE_KEY);
        Cache::forget(self::USERS_INACTIVE_CACHE_KEY);

        if ($id) {
            Cache::forget("user_{$id}_with_roles");
        }
    }
}
