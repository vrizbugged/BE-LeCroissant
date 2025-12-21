<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    /**
     * Mendapatkan semua data notification.
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Mendapatkan data notification berdasarkan ID.
     *
     * @param int $id
     * @return Notification|null
     */
    public function findById(int $id): ?Notification;

    /**
     * Mendapatkan data notification berdasarkan tipe.
     *
     * @param string $type
     * @return Collection
     */
    public function findByType(string $type): Collection;

    /**
     * Mendapatkan data notification berdasarkan status.
     *
     * @param string $status
     * @return Collection
     */
    public function findByStatus(string $status): Collection;

    /**
     * Membuat data notification baru.
     *
     * @param array $data
     * @return Notification
     */
    public function create(array $data): Notification;

    /**
     * Memperbarui data notification berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return Notification
     */
    public function update(int $id, array $data): Notification;

    /**
     * Menghapus data notification berdasarkan ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
 