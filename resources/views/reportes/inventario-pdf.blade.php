<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Inventario - GLAZ GROUP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0 0 10px 0;
        }
        .info {
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .info-row {
            display: inline-block;
            width: 48%;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .alert {
            color: #d32f2f;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GLAZ GROUP S.A.S.</h1>
        <h2>Reporte de Inventario</h2>
        <p>Fecha de generación: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row">
            <strong>Total de Productos:</strong> {{ $total_productos }}
        </div>
        <div class="info-row">
            <strong>Valor Total Inventario:</strong> ${{ number_format($valor_total, 2) }}
        </div>
        <div class="info-row">
            <strong>Productos con Stock Bajo:</strong> <span class="alert">{{ $alertas_stock_bajo }}</span>
        </div>
    </div>

    <h3>Detalle de Productos</h3>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Lotes</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            <tr>
                <td>{{ $producto['codigo'] }}</td>
                <td>{{ $producto['nombre'] }}</td>
                <td class="{{ $producto['stock_actual'] <= $producto['stock_minimo'] ? 'alert' : '' }}">
                    {{ $producto['stock_actual'] }}
                </td>
                <td>{{ $producto['stock_minimo'] }}</td>
                <td>{{ $producto['lotes_vigentes'] }}</td>
                <td>${{ number_format($producto['valor_inventario'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema AXXESS - GLAZ GROUP S.A.S. | Bogotá, Colombia</p>
        <p>Documento generado automáticamente - {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
