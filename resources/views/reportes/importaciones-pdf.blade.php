<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Importaciones - GLAZ GROUP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #00BCD4; padding-bottom: 15px; }
        .header h1 { color: #00BCD4; margin: 0 0 10px 0; }
        .info { margin-bottom: 20px; background: #e0f7fa; padding: 15px; border-radius: 5px; }
        .info-row { display: inline-block; width: 48%; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #00BCD4; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GLAZ GROUP S.A.S.</h1>
        <h2>Reporte de Importaciones</h2>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-row"><strong>Total:</strong> {{ $total_importaciones }}</div>
        <div class="info-row"><strong>Valor Total:</strong> ${{ number_format($valor_total, 2) }}</div>
        <div class="info-row"><strong>Licencias Activas:</strong> {{ $licencias_activas }}</div>
    </div>

    <h3>Importaciones por Laboratorio</h3>
    <table>
        <thead>
            <tr>
                <th>Laboratorio</th>
                <th>Cantidad</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($por_laboratorio as $lab)
            <tr>
                <td>{{ $lab['laboratorio'] }}</td>
                <td>{{ $lab['cantidad'] }}</td>
                <td>${{ number_format($lab['valor'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema AXXESS - GLAZ GROUP S.A.S.</p>
    </div>
</body>
</html>
