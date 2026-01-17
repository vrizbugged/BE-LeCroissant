<?php

namespace App\Repositories\Eloquent;

use App\Models\Client; // Menggunakan model Client terpisah
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var Client
     */
    protected $model;

    /**
     * ClientRepository constructor.
     *
     * @param Client $model
     */
    public function __construct(Client $model)
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
        return $this->model->all();
    }

    /**
     * Mengambil klien berdasarkan ID.
     * * @param int $id
     * * @return Client|null
     */
    public function getClientById($id): ?Client
    {
        return $this->model->find($id);
    }

    /**
     * Mengambil klien berdasarkan sektor bisnis (Hotel, Restoran, EO).
     * [Ref Proposal: Mengklasifikasikan responden berdasarkan sektor bisnis]
     * * @param string $sector
     * * @return Collection
     */
    public function getClientsBySector($sector): Collection
    {
        return $this->model->where('business_sector', $sector)->get();
    }

    /**
     * Mengambil klien berdasarkan status (Pending, Aktif, Non-Aktif).
     * [Ref Proposal: Verifikasi klien B2B]
     * * @param string $status
     * * @return Collection
     */
    public function getClientsByStatus($status): Collection
    {
        return $this->model->where('status', $status)->get();
    }


    /**
     * Mengambil klien berdasarkan email dan status.
     * * @param string $email
     * * @param string $status
     * * @return Client|null
     */
    public function getClientByEmailAndStatus($email, $status): ?Client
    {
        return $this->model->where('email', $email)
                           ->where('status', $status)
                           ->first();
    }

    /**
     * Membuat data klien B2B baru.
     * * @param array $data
     * * @return Client
     */
    public function createClient(array $data): Client
    {
        $client = $this->model->create($data);

        // Jika ada user_id, assign Spatie role 'Anggota' untuk permission management
        if ($client->user_id) {
            $user = $client->user;
            if ($user) {
                $anggotaRole = \Spatie\Permission\Models\Role::where('name', 'Anggota')->first();
                if ($anggotaRole) {
                    $user->assignRole($anggotaRole);
                }
            }
        }

        return $client;
    }

    /**
     * Memperbarui informasi klien.
     * * @param int $id
     * * @param array $data
     * * @return Client|null
     */
    public function updateClient($id, array $data): ?Client
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
     * * @return Client|null
     */
    public function verifyClient($id): ?Client
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
     * * @return bool
     */
    public function deleteClient($id): bool
    {
        $client = $this->getClientById($id);

        if (!$client) {
            return false;
        }

        // Cek apakah klien memiliki pesanan aktif
        if ($client->orders()->whereIn('status', ['menunggu_konfirmasi', 'diproses', 'dikirim'])->exists()) {
            return false; // Tidak bisa hapus jika ada pesanan aktif
        }

        return $client->delete();
    }
}
