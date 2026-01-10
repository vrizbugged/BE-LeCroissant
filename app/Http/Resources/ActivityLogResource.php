<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'event' => $this->event,
            'causer' => $this->getCauserData(),
            'subject' => $this->getSubjectData(),
            'properties' => $this->properties,
            'changes' => $this->getFormattedChanges(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get causer (user who performed the action) data.
     *
     * @return array<string, mixed>|null
     */
    protected function getCauserData(): ?array
    {
        if (!$this->causer) {
            return [
                'id' => null,
                'name' => 'System',
                'email' => null,
            ];
        }

        return [
            'id' => $this->causer->id,
            'name' => $this->causer->name,
            'email' => $this->causer->email,
        ];
    }

    /**
     * Get subject (model that was affected) data.
     *
     * @return array<string, mixed>|null
     */
    protected function getSubjectData(): ?array
    {
        if (!$this->subject_type || !$this->subject_id) {
            return null;
        }

        return [
            'type' => $this->subject_type,
            'id' => $this->subject_id,
            'name' => $this->getSubjectName(),
        ];
    }

    /**
     * Get subject name based on subject type.
     *
     * @return string
     */
    protected function getSubjectName(): string
    {
        if (!$this->subject_type || !$this->subject_id) {
            return 'N/A';
        }

        if (!$this->subject) {
            // Extract model name from full class name (e.g., App\Models\Product -> Product)
            $modelName = class_basename($this->subject_type);
            return "{$modelName} #{$this->subject_id}";
        }

        // Get name based on model type
        $subject = $this->subject;

        return match ($this->subject_type) {
            \App\Models\Product::class => $subject->name ?? "Product #{$this->subject_id}",
            \App\Models\Order::class => "Order #{$this->subject_id}",
            \App\Models\Client::class => $subject->company_name ?? $subject->name ?? "Client #{$this->subject_id}",
            default => class_basename($this->subject_type) . " #{$this->subject_id}",
        };
    }

    /**
     * Get formatted changes (old -> new values).
     *
     * @return array<int, array{field: string, old: string|null, new: string|null}>
     */
    protected function getFormattedChanges(): array
    {
        if (!$this->properties) {
            return [];
        }

        // Convert properties to array (handles Collection, array, or JSON string)
        $properties = $this->normalizeProperties($this->properties);
        
        if (!is_array($properties)) {
            return [];
        }

        $changes = [];

        // Handle different property structures
        if (isset($properties['old']) && isset($properties['attributes'])) {
            // Standard structure: {old: {...}, attributes: {...}} (updated event)
            $oldValues = is_array($properties['old']) ? $properties['old'] : (is_object($properties['old']) ? (array) $properties['old'] : []);
            $newValues = is_array($properties['attributes']) ? $properties['attributes'] : (is_object($properties['attributes']) ? (array) $properties['attributes'] : []);

            // Get all changed fields
            $changedFields = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

            foreach ($changedFields as $field) {
                $oldValue = $oldValues[$field] ?? null;
                $newValue = $newValues[$field] ?? null;

                // Only include if there's actually a change
                if ($oldValue !== $newValue) {
                    $changes[] = [
                        'field' => $this->getFieldLabel($field),
                        'old' => $this->formatValue($oldValue, $field),
                        'new' => $this->formatValue($newValue, $field),
                    ];
                }
            }
        } elseif (isset($properties['attributes'])) {
            // If only attributes (created event)
            $attributes = is_array($properties['attributes']) ? $properties['attributes'] : (is_object($properties['attributes']) ? (array) $properties['attributes'] : []);
            $trackedFields = ['name', 'price_b2b', 'stock', 'status', 'total_price', 'delivery_date', 'company_name', 'business_sector', 'role'];
            foreach ($attributes as $field => $value) {
                // Only show fields that we're tracking
                if (in_array($field, $trackedFields)) {
                    $changes[] = [
                        'field' => $this->getFieldLabel($field),
                        'old' => null,
                        'new' => $this->formatValue($value, $field),
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Normalize properties to array format.
     * Handles Collection, array, JSON string, or object.
     *
     * @param mixed $properties
     * @return array
     */
    protected function normalizeProperties($properties): array
    {
        if (is_array($properties)) {
            return $properties;
        }

        if (is_string($properties)) {
            $decoded = json_decode($properties, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_object($properties)) {
            // Handle Collection
            if (method_exists($properties, 'toArray')) {
                return $properties->toArray();
            }
            
            // Handle stdClass or other objects
            return (array) $properties;
        }

        return [];
    }

    /**
     * Get human-readable field label.
     *
     * @param string $field
     * @return string
     */
    protected function getFieldLabel(string $field): string
    {
        $labels = [
            'name' => 'Nama',
            'nama_produk' => 'Nama Produk',
            'price_b2b' => 'Harga B2B',
            'harga_grosir' => 'Harga Grosir',
            'stock' => 'Stok',
            'ketersediaan_stok' => 'Ketersediaan Stok',
            'status' => 'Status',
            'total_price' => 'Total Harga',
            'delivery_date' => 'Tanggal Pengiriman',
            'company_name' => 'Nama Perusahaan',
            'business_sector' => 'Sektor Bisnis',
            'email' => 'Email',
            'phone_number' => 'Nomor Telepon',
            'address' => 'Alamat',
            'role' => 'Role',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display.
     *
     * @param mixed $value
     * @param string $field
     * @return string|null
     */
    protected function formatValue($value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        // Format based on field type
        if (in_array($field, ['price_b2b', 'harga_grosir', 'total_price'])) {
            // Convert to float first, then format
            $numericValue = is_numeric($value) ? (float) $value : 0;
            return 'Rp ' . number_format($numericValue, 0, ',', '.');
        }

        if ($field === 'delivery_date' && $value) {
            try {
                if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
                    return $value->format('d M Y');
                }
                return \Carbon\Carbon::parse($value)->format('d M Y');
            } catch (\Exception $e) {
                return (string) $value;
            }
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        return (string) $value;
    }
}

