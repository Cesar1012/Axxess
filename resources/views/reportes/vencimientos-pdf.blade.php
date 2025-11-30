<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Vencimientos - GLAZ GROUP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #FF9800; padding-bottom: 15px; }
        .header h1 { color: #FF9800; margin: 0 0 10px 0; }
        .info { margin-bottom: 20px; background: #fff3e0; padding: 15px; border-radius: 5px; }
        .info-row { display: inline-block; width: 48%; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #FF9800; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .urgente { color: #d32f2f; font-weight: bold; }
        .medio { color: #f57c00; }
        .bajo { color: #388e3c; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GLAZ GROUP S.A.S.</h1>
        <h2>Reporte de Productos Próximos a Vencer</h2>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row"><strong>Total Lotes:</strong> {{ $total_lotes }}</div>
        <div class="info-row"><strong>Valor en Riesgo:</strong> ${{ number_format($valor_riesgo, 2) }}</div>
    </div>

    <h3>Detalle de Lotes Próximos a Vencer</h3>
    <table>
        <thead>
            <tr>
                <th>Urgencia</th>
                <th>Producto</th>
                <th>Lote</th>
                <th>Vencimiento</th>
                <th>Días</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proximos_vencer as $lote)
            @php
                $dias = $lote['dias_restantes'];
                $class = $dias <= 30 ? 'urgente' : ($dias <= 60 ? 'medio' : 'bajo');
                $label = $dias <= 30 ? 'URGENTE' : ($dias <= 60 ? 'MEDIO' : 'BAJO');
            @endphp
            <tr>
                <td class="{{ $class }}">{{ $label }}</td>
                <td>{{ $lote['producto'] }}</td>
                <td>{{ $lote['lote'] }}</td>
                <td>{{ $lote['fecha_vencimiento'] }}</td>
                <td class="{{ $class }}">{{ $dias }} días</td>
                <td>{{ $lote['cantidad'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema AXXESS - GLAZ GROUP S.A.S.</p>
    </div>
</body>
</html>
