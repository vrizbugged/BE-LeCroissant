<?php

namespace App\Services\Contracts;

use App\Models\Client; // Menggunakan model Client terpisah
use Illuminate\Database\Eloquent\Collection;

interface ClientServiceInterface
{
    /**
     * Get all B2B clients
     * [Ref Proposal: Mengelola data klien B2B] [cite: 109]
     * * @return Collection
     */
    public function getAllClients();

    /**
     * Get client by id
     * * @param int $id
     * * @return Client|null
     */
    public function getClientById($id);

    /**
     * Get clients by business sector
     * [Ref Proposal: Klasifikasi berdasarkan sektor bisnis: Hotel, Restoran, EO]
     * * @param string $sector
     * * @return Collection
     */
    public function getClientsByBusinessSector($sector);

    /**
     * Get clients by citizenship
     * [Ref Proposal: Membedakan klien lokal (WNI) dan asing (WNA)]
     * * @param string $citizenship
     * * @return Collection
     */
    public function getClientsByCitizenship($citizenship);

    /**
     * Get client by email and business status
     * * @param string $email
     * * @param string $status
     * * @return Client|null
     */
    public function getClientByEmailAndStatus($email, $status);

    /**
     * Create a new B2B client record
     * * @param array $data
     * * @return Client
     */
    public function createClient(array $data);

    /**
     * Update client information
     * * @param int $id
     * * @param array $data
     * * @return Client|null
     */
    public function updateClient($id, array $data);

    /**
     * Verify client account (Admin Action)
     * [Ref Proposal: Admin memverifikasi pesanan/klien B2B]
     * * @param int $id
     * * @param string|null $verifiedAt Optional timestamp
     * * @return Client|null
     */
    public function verifyClientAccount($id, $verifiedAt = null);

    /**
     * Suspend client account
     * * @param int $id
     * * @param string|null $reason
     * * @return Client|null
     */
    public function suspendClientAccount($id, $reason = null);

    /**
     * Get client order and transaction summary
     * [Ref Proposal: Laporan ringkasan pesanan atau transaksi B2B]
     * * @param int $id
     * * @param string $startDate
     * * @param string $endDate
     * * @return array
     */
    public function getClientTransactionReport($id, $startDate, $endDate);

    /**
     * Get overall B2B market segment report
     * [Ref Proposal: Mendukung potensi pertumbuhan bisnis di pasar grosir] [cite: 87, 89]
     * * @param string $startDate
     * * @param string $endDate
     * * @return array
     */
    public function getMarketSegmentReport($startDate, $endDate);

    /**
     * Delete client record
     * * @param int $id
     * * @return bool
     */
    public function deleteClient($id);
}
