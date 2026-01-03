<?php

namespace App\Repositories\Eloquent;

use App\Models\User; // Menggunakan model User dengan role 'klien_b2b'
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * ClientRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Mengambil semua klien B2B.
     * [Ref Proposal: Mengelola data klien B2B]
     * * @return Collection
     */
    public function getAllClients(): Collection
    {
        // Hanya mengambil user yang memiliki role klien_b2b (filter by database column)
        return $this->model->where('role', 'klien_b2b')->get();
    }

    /**
     * Mengambil klien berdasarkan ID.
     * * @param int $id
     * @return User|null
     */
    public function getClientById($id): ?User
    {
        $model = $this->model->where('role', 'klien_b2b')->find($id);
        return $model instanceof User ? $model : null;
    }

    /**
     * Mengambil klien berdasarkan sektor bisnis (Hotel, Restoran, EO).
     * [Ref Proposal: Mengklasifikasikan responden berdasarkan sektor bisnis]
     * * @param string $sector
     * @return Collection
     */
    public function getClientsBySector($sector): Collection
    {
        return $this->model->where('role', 'klien_b2b')
                           ->where('business_sector', $sector)
                           ->get();
    }

    /**
     * Mengambil klien berdasarkan status (Pending, Aktif, Non-Aktif).
     * [Ref Proposal: Verifikasi klien B2B]
     * * @param string $status
     * @return Collection
     */
    public function getClientsByStatus($status): Collection
    {
        return $this->model->where('role', 'klien_b2b')
                           ->where('status', $status)
                           ->get();
    }

    /**
     * Mengambil klien berdasarkan kewarganegaraan (WNI/WNA).
     * [Ref Proposal: Membedakan klien lokal dan asing]
     * * @param string $citizenship
     * @return Collection
     */
    public function getClientsByCitizenship($citizenship): Collection
    {
        return $this->model->where('role', 'klien_b2b')
                           ->where('citizenship', $citizenship)
                           ->get();
    }

    /**
     * Mengambil klien berdasarkan email dan status.
     * * @param string $email
     * * @param string $status
     * @return User|null
     */
    public function getClientByEmailAndStatus($email, $status): ?User
    {
        return $this->model->where('role', 'klien_b2b')
                           ->where('email', $email)
                           ->where('status', $status)
                           ->first();
    }

    /**
     * Membuat data klien B2B baru.
     * * @param array $data
     * @return User
     */
    public function createClient(array $data): User
    {
        // Pastikan role di database adalah 'klien_b2b'
        $data['role'] = 'klien_b2b';
        $client = $this->model->create($data);

        // Assign Spatie role 'Anggota' untuk permission management
        $anggotaRole = \Spatie\Permission\Models\Role::where('name', 'Anggota')->first();
        if ($anggotaRole) {
            $client->assignRole($anggotaRole);
        }

        return $client;
    }

    /**
     * Memperbarui informasi klien.
     * * @param int $id
     * @param array $data
     * @return User|null
     */
    public function updateClient($id, array $data): ?User
    {
        $client = $this->getClientById($id);

        if (!$client) {
            return null;
        }

        $client->update($data);
        return $client;
    }

    /**
     * Verifikasi akun klien (Mengubah status menjadi Aktif).
     * [Ref Proposal: Admin memverifikasi pesanan/klien B2B]
     * * @param int $id
     * @return User|null
     */
    public function verifyClient($id): ?User
    {
        $client = $this->getClientById($id);

        if (!$client) {
            return null;
        }

        $client->status = 'Aktif';
        $client->save();

        return $client;
    }

    /**
     * Mengambil statistik klien B2B.
     * [Ref Proposal: Evaluasi manajemen & laporan ringkasan]
     * * @return array
     */
    public function getClientStats(): array
    {
        $clients = $this->getAllClients();

        return [
            'total_clients' => $clients->count(),
            'active_clients' => $clients->where('status', 'Aktif')->count(),
            'pending_verification' => $clients->where('status', 'Pending')->count(),
            'sector_distribution' => $clients->groupBy('business_sector')->map->count(),
        ];
    }

    /**
     * Menghapus data klien.
     * * @param int $id
     * @return bool
     */
    public function deleteClient($id): bool
    {
        $client = $this->getClientById($id);

        if (!$client) {
            return false;
        }

        return $client->delete();
    }
}
