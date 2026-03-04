<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    public function __construct(
        private readonly Collection $orders
    ) {
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Client Name',
            'Company Name',
            'Phone Number',
            'Address',
            'Delivery Date',
            'Status',
            'Total Price',
            'Ordered Items',
            'Client Note',
            'Created At',
            'Updated At',
        ];
    }

    public function map($order): array
    {
        $items = $order->products
            ->map(function ($product) {
                $quantity = (int) ($product->pivot->quantity ?? 0);
                $price = (float) ($product->pivot->price_at_purchase ?? 0);
                $subtotal = $quantity * $price;

                return sprintf(
                    '%s (x%d) @ Rp %s = Rp %s',
                    $product->nama_produk ?? 'Produk',
                    $quantity,
                    number_format($price, 0, ',', '.'),
                    number_format($subtotal, 0, ',', '.')
                );
            })
            ->implode("\n");

        return [
            '#' . $order->id,
            $order->client?->name ?? $order->user?->name ?? 'N/A',
            $order->client?->company_name ?? $order->user?->company_name ?? '-',
            $order->client?->phone_number ?? $order->user?->phone_number ?? '-',
            $order->client?->address ?? $order->user?->address ?? '-',
            optional($order->delivery_date)->format('d M Y'),
            $this->statusLabel($order->status),
            (float) $order->total_price,
            $items ?: '-',
            $order->special_notes ?: '-',
            optional($order->created_at)->format('d M Y H:i'),
            optional($order->updated_at)->format('d M Y H:i'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:L1');

                $sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF111827'],
                    ],
                ]);

                $sheet->getStyle("A2:L{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("I2:J{$lastRow}")->getAlignment()->setWrapText(true);

                // Keep note/items columns readable on long text.
                $sheet->getColumnDimension('I')->setWidth(55);
                $sheet->getColumnDimension('J')->setWidth(45);
            },
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'menunggu_konfirmasi' => 'Pending',
            'diproses' => 'Processing',
            'siap_di_pickup' => 'Ready to Pick-Up',
            'selesai' => 'Done',
            'dibatalkan' => 'Cancelled',
            default => $status,
        };
    }
}

