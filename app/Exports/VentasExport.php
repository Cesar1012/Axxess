<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Venta::with(['clienteMarket', 'vendedor', 'detalles.producto'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Número Factura',
            'Fecha',
            'Tipo Venta',
            'Cliente',
            'NIT',
            'Vendedor',
            'Subtotal',
            'Impuesto',
            'Descuento',
            'Total',
            'Forma Pago',
            'Estado',
            'Productos'
        ];
    }

    public function map($venta): array
    {
        $productos = $venta->detalles->map(fn($d) => 
            $d->producto?->nombre . ' (x' . $d->cantidad . ')'
        )->implode(', ');

        return [
            $venta->id,
            $venta->numero_factura,
            $venta->fecha_venta,
            $venta->tipo_venta,
            $venta->clienteMarket?->razon_social,
            $venta->clienteMarket?->nit,
            $venta->vendedor?->nombre_completo,
            $venta->subtotal,
            $venta->impuesto,
            $venta->descuento,
            $venta->total,
            $venta->forma_pago,
            $venta->estado,
            $productos
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
