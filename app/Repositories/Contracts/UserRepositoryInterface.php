<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    /**
     * Mengambil semua users.
     *
     * @return mixed
     */
    public function getAllUsers();

    /**
     * Mengambil user berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getUserById($id);

    /**
     * Mengambil user berdasarkan nama.
     *
     * @param string $name
     * @return mixed
     */
    public function getUserByName($name);

    /**
     * Mengambil user berdasarkan status.
     *
     * @param string $status
     * @return mixed
     */
    public function getUserByStatus($status);

    /**
     * Membuat user baru.
     *
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data);

    /**
     * Memperbarui user berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateUser($id, array $data);

    /**
     * Menghapus user berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function deleteUser($id);

    /**
     * Mengupdate user status.
     *
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function updateUserStatus($id, $status);
}
