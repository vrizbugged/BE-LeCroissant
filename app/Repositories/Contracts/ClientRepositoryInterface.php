<?php

namespace App\Repositories\Contracts;

use App\Models\Client; // Menggunakan model Client terpisah
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface
{
    /**
     * Get all B2B clients
     * * @return Collection
     */
    public function getAllClients();

    /**
     * Get client by id
     * * @param int $id
     * @return Client|null
     */
    public function getClientById($id);

    /**
     * Get clients by business sector (e.g., Hotel, Restaurant)
     * [Ref Proposal: Mengklasifikasikan responden berdasarkan sektor bisnis]
     * * @param string $sector
     * * @return Collection
     */
    public function getClientsBySector($sector);

    /**
     * Get clients by status (e.g., Aktif, Pending, Non-Aktif)
     * [Ref Proposal: Verifikasi klien B2B]
     * * @param string $status
     * * @return Collection
     */
    public function getClientsByStatus($status);

    /**
     * Get clients by citizenship (WNI/WNA)
     * [Ref Proposal: Membedakan klien lokal dan asing]
     * * @param string $citizenship
     * * @return Collection
     */
    public function getClientsByCitizenship($citizenship);

    /**
     * Get client by email and status
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
     * Verify client account (Change status to Active)
     * [Ref Proposal: Admin memverifikasi pesanan/klien B2B]
     * * @param int $id
     * * @return Client|null
     */
    public function verifyClient($id);

    /**
     * Get client statistics (Total, Active, Pending)
     * * @return array
     */
    public function getClientStats();

    /**
     * Delete client record
     * * @param int $id
     * * @return bool
     */
    public function deleteClient($id);
}
