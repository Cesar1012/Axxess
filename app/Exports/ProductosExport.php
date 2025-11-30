<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Producto::with('categoria')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Código Barras',
            'Nombre',
            'Molécula',
            'Categoría',
            'Módulo',
            'Precio Compra',
            'Precio Venta',
            'Precio Mayorista',
            'Stock Mínimo',
            'Stock Actual',
            'Unidad Medida',
            'Requiere Cadena Frío',
            'Registro Sanitario',
            'IVA %',
            'Activo'
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->codigo_producto,
            $producto->codigo_barras,
            $producto->nombre,
            $producto->molecula,
            $producto->categoria?->nombre,
            $producto->modulo,
            $producto->precio_compra,
            $producto->precio_venta,
            $producto->precio_mayorista,
            $producto->stock_minimo,
            $producto->stock_actual,
            $producto->unidad_medida,
            $producto->requiere_cadena_frio ? 'Sí' : 'No',
            $producto->registro_sanitario,
            $producto->impuesto_iva,
            $producto->activo ? 'Sí' : 'No'
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
