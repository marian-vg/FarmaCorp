<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Caja #{{ $caja->id }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header img {
            max-height: 50px;
            width: auto;
            margin-bottom: 4px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .caja-id {
            font-size: 12px;
            margin: 2px 0;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 25%;
            color: #4b5563;
        }
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .financial-table th, .financial-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        .financial-table th {
            background-color: #f9fafb;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .amount {
            text-align: right;
            font-family: 'Courier', monospace;
            font-size: 12px;
        }
        .total-row td {
            font-weight: bold;
            border-top: 1.5px solid #374151;
            background-color: #f3f4f6;
        }
        .monto-final-row td {
            background-color: #eff6ff;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
        }
        .observaciones {
            border: 1px solid #9ca3af;
            padding: 8px;
            background-color: #f9fafb;
            margin-top: 10px;
        }
        .observaciones h4 {
            margin: 0 0 4px 0;
            font-size: 11px;
            color: #374151;
        }
        .signatures {
            margin-top: 35px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 50%;
        }
        .sign-line {
            display: inline-block;
            width: 70%;
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 4px;
            font-size: 10px;
        }
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 0.5px solid #e5e7eb;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        {{-- Logo FarmaCorp --}}
        <img src="{{ public_path('images/logo-farma-corp.png') }}" alt="FarmaCorp">
        <h1 class="title">Reporte de Cierre de Caja</h1>
        <div class="caja-id">Auditoría ID: #{{ str_pad($caja->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Responsable / Cajero:</td>
            <td>{{ $caja->user->name }}</td>
            <td class="label">Apertura:</td>
            <td>{{ $caja->fecha_apertura->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="label">Efectivo Inicial:</td>
            <td>$ {{ number_format($caja->monto_inicial, 2) }}</td>
            <td class="label">Cierre:</td>
            <td>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i:s') : 'PENDIENTE' }}</td>
        </tr>
        <tr>
            <td class="label">Duración del Turno:</td>
            <td colspan="3">
                @if($caja->fecha_cierre)
                    {{ $caja->fecha_apertura->diff($caja->fecha_cierre)->format('%H:%I:%S') }} hrs
                @else
                    EN CURSO
                @endif
            </td>
        </tr>
    </table>

    <table class="financial-table">
        <thead>
            <tr>
                <th>Medio de Pago (RF-05)</th>
                <th style="text-align: right;">Ingresos (+)</th>
                <th style="text-align: right;">Egresos (-)</th>
                <th style="text-align: right;">Neto</th>
            </tr>
        </thead>
        <tbody>
            @php $granNeto = 0; @endphp
            @foreach($totales as $medio => $datos)
                @php $granNeto += $datos['neto']; @endphp
                <tr>
                    <td>{{ $medio }}</td>
                    <td class="amount" style="color: #059669;">$ {{ number_format($datos['ingresos'], 2) }}</td>
                    <td class="amount" style="color: #dc2626;">$ {{ number_format($datos['egresos'], 2) }}</td>
                    <td class="amount"><strong>$ {{ number_format($datos['neto'], 2) }}</strong></td>
                </tr>
            @endforeach
            
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Resultado Operativo del Turno (Neto):</td>
                <td class="amount">$ {{ number_format($granNeto, 2) }}</td>
            </tr>
            
            <tr class="monto-final-row">
                <td colspan="3" style="text-align: right;">
                    <strong>MONTO FINAL AUDITADO EN CAJA:</strong><br>
                    <small>(Monto Inicial + Neto de todos los medios)</small>
                </td>
                <td class="amount" style="font-size: 15px;">
                    <strong>$ {{ number_format($caja->monto_final, 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    @if($caja->observaciones)
    <div class="observaciones">
        <h4>Observaciones de Auditoría:</h4>
        <p style="margin: 0;">{{ $caja->observaciones }}</p>
    </div>
    @endif

    <table class="signatures">
        <tr>
            <td><span class="sign-line">Firma Cajero: {{ $caja->user->name }}</span></td>
            <td><span class="sign-line">Firma Supervisor / Administrador</span></td>
        </tr>
    </table>

    <div class="footer">
        Generado por FarmaCorp el {{ now()->format('d/m/Y H:i:s') }} - Reporte Confidencial de Auditoría Interna (RF-07)
    </div>

</body>
</html>