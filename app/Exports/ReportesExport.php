<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $tipo;
    protected $data;

    public function __construct($tipo, $data)
    {
        $this->tipo = $tipo;
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        
        switch($this->tipo) {
            case 'inventario':
                foreach($this->data['productos'] as $producto) {
                    $rows[] = [
                        $producto['codigo'],
                        $producto['nombre'],
                        $producto['stock_actual'],
                        $producto['stock_minimo'],
                        $producto['lotes_vigentes'],
                        $producto['valor_inventario']
                    ];
                }
                break;
            
            case 'vencimientos':
                foreach($this->data['proximos_vencer'] as $lote) {
                    $rows[] = [
                        $lote['producto'],
                        $lote['lote'],
                        $lote['fecha_vencimiento'],
                        $lote['dias_restantes'],
                        $lote['cantidad']
                    ];
                }
                break;
            
            case 'autorizacionesInvima':
                foreach($this->data['detalle'] as $auth) {
                    $rows[] = [
                        $auth['paciente'],
                        $auth['producto'],
                        $auth['cantidad_autorizada'],
                        $auth['saldo_pendiente'],
                        $auth['fecha_vencimiento']
                    ];
                }
                break;
            
            case 'ventas':
                foreach($this->data['por_mes'] as $mes) {
                    $rows[] = [
                        $mes['mes'],
                        $mes['pedidos'],
                        $mes['monto']
                    ];
                }
                break;
            
            case 'despachos':
                foreach($this->data['por_zona'] as $zona) {
                    $rows[] = [
                        $zona['zona'],
                        $zona['cantidad']
                    ];
                }
                break;
            
            case 'importaciones':
                foreach($this->data['por_laboratorio'] as $lab) {
                    $rows[] = [
                        $lab['laboratorio'],
                        $lab['cantidad'],
                        $lab['valor']
                    ];
                }
                break;
        }
        
        return $rows;
    }

    public function headings(): array
    {
        switch($this->tipo) {
            case 'inventario':
                return ['Código', 'Nombre', 'Stock Actual', 'Stock Mínimo', 'Lotes', 'Valor'];
            
            case 'vencimientos':
                return ['Producto', 'Lote', 'Vencimiento', 'Días Restantes', 'Cantidad'];
            
            case 'autorizacionesInvima':
                return ['Paciente', 'Producto', 'Autorizada', 'Pendiente', 'Vencimiento'];
            
            case 'ventas':
                return ['Mes', 'Pedidos', 'Monto'];
            
            case 'despachos':
                return ['Zona', 'Cantidad'];
            
            case 'importaciones':
                return ['Laboratorio', 'Cantidad', 'Valor'];
            
            default:
                return [];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return ucfirst($this->tipo);
    }
}
