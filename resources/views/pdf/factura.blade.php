<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header-container { border: 1px solid #000; padding: 10px; position: relative; }
        .type-box { 
            position: absolute; left: 47%; top: -1px; 
            border: 1px solid #000; background: #fff; 
            width: 35px; height: 35px; text-align: center; 
            font-size: 22px; font-weight: bold; line-height: 35px;
        }
        .text-right { text-align: right; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { background: #f0f0f0; border: 1px solid #000; padding: 5px; text-transform: uppercase; }
        .items-table td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 5px; }
        .items-table tr:last-child td { border-bottom: 1px solid #000; }
        .disclaimer { 
            margin-top: 20px; text-align: center; font-weight: bold; 
            color: #d12; border: 2px solid #d12; padding: 5px; font-size: 12px;
        }
        .totals-table { width: 250px; margin-left: auto; margin-top: 10px; border-collapse: collapse; }
        .totals-table td { padding: 3px 5px; border: 1px solid #000; }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="type-box">
            {{-- Extraemos la letra: FACTURA-A -> A, FACTURA-B -> B --}}
            {{ str_contains($factura->tipo_comprobante, 'FACTURA-A') ? 'A' : 'B' }}
        </div>
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <h1 style="margin: 0; color: #1e3a8a;">FarmaCorp</h1>
                    <p><strong>De:</strong> Famea Damián & Vigliani Mariano <br>
                    <strong>Domicilio:</strong> Calle Falsa 123 - Entre Ríos<br>
                    <strong>Condición IVA:</strong> Responsable Inscripto</p>
                </td>
                <td style="width: 50%;" class="text-right">
                    <h2 style="margin: 0;">{{ $factura->tipo_comprobante }}</h2>
                    <p><strong>Nro:</strong> 0001-{{ str_pad($factura->id, 8, '0', STR_PAD_LEFT) }}<br>
                    <strong>Fecha:</strong> {{ $factura->fecha_emision->format('d/m/Y H:i') }}<br>
                    <strong>CUIT:</strong> 30-71234567-8</p>
                </td>
            </tr>
        </table>
    </div>

    <div style="border: 1px solid #000; padding: 8px; margin-top: 5px;">
        <strong>CLIENTE:</strong> {{ $factura->cliente ? ($factura->cliente->first_name . ' ' . $factura->cliente->last_name) : 'CONSUMIDOR FINAL' }} [cite: 114, 118]<br>
        <strong>DOMICILIO:</strong> {{ $factura->cliente->address ?? 'N/D' }} | <strong>TEL:</strong> {{ $factura->cliente->phone ?? 'N/D' }}
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="text-align: left;">Descripción</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->details as $item)
                <tr>
                    <td style="width: 50%;">{{ $item->product->name }}</td>
                    <td style="text-align: center;">{{ $item->cantidad }}</td>
                    <td style="text-align: right;">$ {{ number_format($item->precio_unitario, 2) }}</td>
                    <td style="text-align: right;">$ {{ number_format($item->cantidad * $item->precio_unitario, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td><strong>SUBTOTAL:</strong></td>
            <td style="text-align: right;">$ {{ number_format($factura->total - $factura->ajuste_global, 2) }}</td>
        </tr>
        @if($factura->ajuste_global != 0)
            <tr>
                <td><strong>AJUSTE (DESC/REC):</strong></td>
                <td style="text-align: right;">$ {{ number_format($factura->ajuste_global, 2) }} </td>
            </tr>
        @endif
        <tr style="background: #eee; font-size: 12px;">
            <td><strong>TOTAL:</strong></td>
            <td style="text-align: right;"><strong>$ {{ number_format($factura->total, 2) }}</strong></td>
        </tr>
    </table>

    <div class="disclaimer">
        *** DOCUMENTO NO VÁLIDO COMO FACTURA ***<br>
        PROYECTO ACADÉMICO - TALLER DE INTEGRACIÓN (UADER) [cite: 4]
    </div>

    <div style="margin-top: 15px; font-size: 9px;">
        <strong>Estado del Pago:</strong> {{ $factura->estado }} | <strong>Vendedor:</strong> {{ $factura->user->name }}
    </div>
</body>
</html>