<?php

namespace App\Services\Implementations;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Services\Contracts\ClientServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ClientService implements ClientServiceInterface
{
    /**
     * @var ClientRepositoryInterface
     */
    protected $clientRepository;

    /**
     * ClientService constructor.
     *
     * @param ClientRepositoryInterface $clientRepository
     */
    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Mengambil semua klien B2B.
     * [Ref Proposal: Menyediakan pengelolaan data pemesanan B2B yang terpusat] [cite: 93]
     * * @return Collection
     */
    public function getAllClients()
    {
        return $this->clientRepository->getAllClients();
    }

    /**
     * Mengambil klien berdasarkan ID.
     * * @param int $id
     * @return Client|null
     */
    public function getClientById($id)
    {
        return $this->clientRepository->getClientById($id);
    }

    /**
     * Mengambil klien berdasarkan sektor bisnis (Hotel, Restoran, EO).
     * [Ref Proposal: Mengklasifikasikan responden berdasarkan sektor bisnis] [cite: 330]
     * * @param string $sector
     * @return Collection
     */
    public function getClientsByBusinessSector($sector)
    {
        return $this->clientRepository->getClientsBySector($sector);
    }

    /**
     * Mengambil klien berdasarkan kewarganegaraan.
     * [Ref Proposal: Membedakan antara klien lokal dan asing] [cite: 339]
     * * @param string $citizenship
     * @return Collection
     */
    public function getClientsByCitizenship($citizenship)
    {
        return $this->clientRepository->getClientsByCitizenship($citizenship);
    }

    /**
     * Mengambil klien berdasarkan email dan status.
     * * @param string $email
     * @param string $status
     * @return Client|null
     */
    public function getClientByEmailAndStatus($email, $status)
    {
        return $this->clientRepository->getClientByEmailAndStatus($email, $status);
    }

    /**
     * Membuat data klien B2B baru.
     * * @param array $data
     * @return Client
     */
    public function createClient(array $data)
    {
        // Set default status ke 'Pending' jika tidak disediakan
        // Sesuai alur B2B yang membutuhkan verifikasi Admin
        if (!isset($data['status'])) {
            $data['status'] = 'Pending';
        }

        return $this->clientRepository->createClient($data);
    }

    /**
     * Memperbarui informasi klien.
     * * @param int $id
     * @param array $data
     * @return Client|null
     */
    public function updateClient($id, array $data)
    {
        return $this->clientRepository->updateClient($id, $data);
    }

    /**
     * Verifikasi akun klien B2B agar dapat mulai memesan.
     * [Ref Proposal: Dapat melihat dan memverifikasi pesanan/klien B2B] [cite: 107, 109]
     * * @param int $id
     * @param string|null $verifiedAt
     * @return Client|null
     */
    public function verifyClientAccount($id, $verifiedAt = null)
    {
        // Memanggil repository untuk memperbarui status
        return $this->clientRepository->updateClient($id, [
            'status' => 'Aktif'
        ]);
    }

    /**
     * Menangguhkan akun klien.
     * * @param int $id
     * @param string|null $reason
     * @return Client|null
     */
    public function suspendClientAccount($id, $reason = null)
    {
        $data = ['status' => 'Non Aktif'];
        
        // Jika ada alasan, bisa disimpan di field notes atau suspended_reason (jika ada)
        // Untuk saat ini, cukup update status saja

        return $this->clientRepository->updateClient($id, $data);
    }

    /**
     * Mengambil laporan ringkasan transaksi klien tertentu.
     * [Ref Proposal: Dapat mencetak laporan ringkasan pesanan atau transaksi B2B] [cite: 110]
     * * @param int $id
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getClientTransactionReport($id, $startDate, $endDate)
    {
        $client = $this->getClientById($id);
        if (!$client) return [];

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Mengambil data pesanan melalui relasi (Pastikan model Client punya relasi orders)
        $orders = $client->orders()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'client_name' => $client->name,
            'company_name' => $client->company_name, // Field tambahan sesuai B2B [cite: 109]
            'total_orders' => $orders->count(),
            'total_spent' => $orders->sum('total_price'),
            'orders' => $orders->toArray()
        ];
    }

    /**
     * Mengambil laporan segmentasi pasar (Analisis Pertumbuhan).
     * [Ref Proposal: Mendukung potensi pertumbuhan bisnis di pasar grosir] [cite: 89]
     * * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getMarketSegmentReport($startDate, $endDate)
    {
        $stats = $this->clientRepository->getClientStats();

        return [
            'report_period' => "$startDate to $endDate",
            'market_analysis' => [
                'total_registered' => $stats['total_clients'],
                'active_partners' => $stats['active_clients'],
                'by_sector' => $stats['sector_distribution'],
            ]
        ];
    }

    /**
     * Menghapus data klien.
     * * @param int $id
     * @return bool
     */
    public function deleteClient($id)
    {
        return $this->clientRepository->deleteClient($id);
    }
}
