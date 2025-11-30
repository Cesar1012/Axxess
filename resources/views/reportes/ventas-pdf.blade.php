<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas - GLAZ GROUP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4CAF50; padding-bottom: 15px; }
        .header h1 { color: #4CAF50; margin: 0 0 10px 0; }
        .info { margin-bottom: 20px; background: #e8f5e9; padding: 15px; border-radius: 5px; }
        .info-row { display: inline-block; width: 48%; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #4CAF50; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GLAZ GROUP S.A.S.</h1>
        <h2>Reporte de Ventas</h2>
        <p>Período: {{ $periodo }}</p>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row"><strong>Total Ventas:</strong> ${{ number_format($total_ventas, 2) }}</div>
        <div class="info-row"><strong>Total Pedidos:</strong> {{ $total_pedidos }}</div>
        <div class="info-row"><strong>Ventas THERAPIES:</strong> ${{ number_format($ventas_therapies, 2) }}</div>
        <div class="info-row"><strong>Ventas MARKET:</strong> ${{ number_format($ventas_market, 2) }}</div>
    </div>

    <h3>Ventas por Mes</h3>
    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Pedidos</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($por_mes as $mes)
            <tr>
                <td>{{ $mes['mes'] }}</td>
                <td>{{ $mes['pedidos'] }}</td>
                <td>${{ number_format($mes['monto'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema AXXESS - GLAZ GROUP S.A.S.</p>
    </div>
</body>
</html>
