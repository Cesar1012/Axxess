<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Despachos - GLAZ GROUP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #9C27B0; padding-bottom: 15px; }
        .header h1 { color: #9C27B0; margin: 0 0 10px 0; }
        .info { margin-bottom: 20px; background: #f3e5f5; padding: 15px; border-radius: 5px; }
        .info-row { display: inline-block; width: 32%; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #9C27B0; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GLAZ GROUP S.A.S.</h1>
        <h2>Reporte de Despachos</h2>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row"><strong>Total:</strong> {{ $total_despachos }}</div>
        <div class="info-row"><strong>Completados:</strong> {{ $completados }}</div>
        <div class="info-row"><strong>Pendientes:</strong> {{ $pendientes }}</div>
    </div>

    <h3>Despachos por Zona</h3>
    <table>
        <thead>
            <tr>
                <th>Zona</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($por_zona as $zona)
            <tr>
                <td>{{ $zona['zona'] }}</td>
                <td>{{ $zona['cantidad'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema AXXESS - GLAZ GROUP S.A.S.</p>
    </div>
</body>
</html>
