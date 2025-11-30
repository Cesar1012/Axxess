<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Autorizaciones INVIMA - GLAZ GROUP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2196F3; padding-bottom: 15px; }
        .header h1 { color: #2196F3; margin: 0 0 10px 0; }
        .info { margin-bottom: 20px; background: #e3f2fd; padding: 15px; border-radius: 5px; }
        .info-row { display: inline-block; width: 32%; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #2196F3; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GLAZ GROUP S.A.S.</h1>
        <h2>Reporte de Autorizaciones INVIMA</h2>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row"><strong>Vigentes:</strong> {{ $vigentes }}</div>
        <div class="info-row"><strong>Próximas Vencer:</strong> {{ $proximas_vencer }}</div>
        <div class="info-row"><strong>Vencidas:</strong> {{ $vencidas }}</div>
        <div class="info-row"><strong>Saldo Pendiente Total:</strong> {{ $saldo_pendiente_total }}</div>
    </div>

    <h3>Detalle de Autorizaciones</h3>
    <table>
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Producto</th>
                <th>Autorizada</th>
                <th>Pendiente</th>
                <th>Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle as $auth)
            <tr>
                <td>{{ $auth['paciente'] }}</td>
                <td>{{ $auth['producto'] }}</td>
                <td>{{ $auth['cantidad_autorizada'] }}</td>
                <td>{{ $auth['saldo_pendiente'] }}</td>
                <td>{{ $auth['fecha_vencimiento'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema AXXESS - GLAZ GROUP S.A.S.</p>
    </div>
</body>
</html>
