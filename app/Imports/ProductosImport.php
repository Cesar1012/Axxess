<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductosImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Buscar categoría por nombre
        $categoria = null;
        if (!empty($row['categoria'])) {
            $categoria = Categoria::where('nombre', $row['categoria'])->first();
        }

        return new Producto([
            'codigo_producto' => $row['codigo'],
            'codigo_barras' => $row['codigo_barras'] ?? null,
            'nombre' => $row['nombre'],
            'molecula' => $row['molecula'] ?? null,
            'categoria_id' => $categoria?->id,
            'modulo' => strtolower($row['modulo'] ?? 'market'),
            'precio_compra' => $row['precio_compra'] ?? 0,
            'precio_venta' => $row['precio_venta'] ?? 0,
            'precio_mayorista' => $row['precio_mayorista'] ?? null,
            'stock_minimo' => $row['stock_minimo'] ?? 0,
            'stock_actual' => $row['stock_actual'] ?? 0,
            'unidad_medida' => $row['unidad_medida'] ?? 'unidad',
            'requiere_cadena_frio' => $this->parseBoolean($row['requiere_cadena_frio'] ?? 'no'),
            'registro_sanitario' => $row['registro_sanitario'] ?? null,
            'impuesto_iva' => $row['iva'] ?? 19,
            'activo' => $this->parseBoolean($row['activo'] ?? 'si'),
        ]);
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:200',
            'precio_venta' => 'required|numeric|min:0',
        ];
    }

    private function parseBoolean($value): bool
    {
        return in_array(strtolower($value), ['si', 'sí', 'yes', '1', 'true']);
    }
}
