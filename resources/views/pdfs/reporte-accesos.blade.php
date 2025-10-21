<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Accesos - CTAccess</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
        }
        
        .header {
            background: linear-gradient(135deg, #39A900 0%, #FDC300 100%);
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .header h1 {
            font-size: 24pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            font-size: 11pt;
            opacity: 0.95;
        }
        
        .info-section {
            background: #f8fafc;
            padding: 15px;
            border-left: 4px solid #39A900;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-section h2 {
            font-size: 13pt;
            color: #00304D;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #64748b;
            padding: 4px 10px 4px 0;
            width: 30%;
        }
        
        .info-value {
            display: table-cell;
            color: #1e293b;
            padding: 4px 0;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stat-row {
            display: table-row;
        }
        
        .stat-box {
            display: table-cell;
            background: #f1f5f9;
            padding: 12px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            width: 25%;
        }
        
        .stat-box + .stat-box {
            border-left: none;
        }
        
        .stat-label {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 20pt;
            font-weight: bold;
            color: #39A900;
        }
        
        .stat-value.secondary {
            color: #FDC300;
        }
        
        .stat-value.tertiary {
            color: #FDC300;
        }
        
        .stat-value.quaternary {
            color: #9333EA;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        
        thead {
            background: #00304D;
            color: white;
        }
        
        thead th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        
        tbody td {
            padding: 8px;
            color: #334155;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: 600;
        }
        
        .badge-primary {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f1f5f9;
            padding: 10px 20px;
            border-top: 2px solid #e2e8f0;
            font-size: 8pt;
            color: #64748b;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-mono {
            font-family: 'Courier New', monospace;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-style: italic;
        }
        
        .signature-section {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            border-top: 2px solid #1e293b;
            padding-top: 10px;
            text-align: center;
            margin-top: 40px;
        }
        
        .signature-label {
            font-size: 9pt;
            color: #64748b;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE ACCESOS</h1>
        <p>Sistema de Control de Accesos - CTAccess v2.0</p>
    </div>

    <!-- Información del Reporte -->
    <div class="info-section">
        <h2>Información del Reporte</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Período:</div>
                <div class="info-value">{{ $periodo['descripcion'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Generado por:</div>
                <div class="info-value">{{ $usuario->nombre ?? 'Sistema' }} ({{ $usuario->usuario ?? 'N/A' }})</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de generación:</div>
                <div class="info-value">{{ $fecha_generacion }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total de registros:</div>
                <div class="info-value"><strong>{{ $estadisticas['total'] }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <h2 style="color: #00304D; margin-bottom: 15px; font-size: 14pt; border-bottom: 2px solid #39A900; padding-bottom: 5px;">
        Estadísticas Generales
    </h2>
    
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-row">
            <div class="stat-box">
                <div class="stat-label">Total Accesos</div>
                <div class="stat-value">{{ $estadisticas['total'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Con Portátil</div>
                <div class="stat-value secondary">{{ $estadisticas['con_portatil'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Con Vehículo</div>
                <div class="stat-value tertiary">{{ $estadisticas['con_vehiculo'] }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Personas Únicas</div>
                <div class="stat-value quaternary">{{ $estadisticas['personas_unicas'] }}</div>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-row">
            <div class="stat-box" style="width: 50%;">
                <div class="stat-label">Duración Total</div>
                <div class="stat-value" style="font-size: 16pt;">{{ $estadisticas['duracion_total'] }}</div>
            </div>
            <div class="stat-box" style="width: 50%;">
                <div class="stat-label">Duración Promedio</div>
                <div class="stat-value" style="font-size: 16pt;">{{ $estadisticas['duracion_promedio'] }}</div>
            </div>
        </div>
    </div>

    <!-- Detalle de Accesos -->
    <h2 style="color: #00304D; margin-bottom: 15px; font-size: 14pt; border-bottom: 2px solid #39A900; padding-bottom: 5px;">
        Detalle de Accesos
    </h2>

    @if($accesos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Persona</th>
                    <th style="width: 12%;">Documento</th>
                    <th style="width: 10%;">Tipo</th>
                    <th style="width: 15%;">Entrada</th>
                    <th style="width: 15%;">Salida</th>
                    <th style="width: 10%;">Duración</th>
                    <th style="width: 13%;">Recursos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accesos as $index => $acceso)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $acceso->persona->Nombre ?? 'N/A' }}</strong></td>
                        <td class="font-mono">{{ $acceso->persona->numero_documento ?? '—' }}</td>
                        <td>
                            <span class="badge badge-primary">
                                {{ $acceso->persona->tipo_persona ?? 'N/A' }}
                            </span>
                        </td>
                        <td style="font-size: 8pt;">
                            {{ $acceso->fecha_entrada ? \Carbon\Carbon::parse($acceso->fecha_entrada)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="font-size: 8pt;">
                            {{ $acceso->fecha_salida ? \Carbon\Carbon::parse($acceso->fecha_salida)->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="font-mono">
                            @if($acceso->fecha_entrada && $acceso->fecha_salida)
                                @php
                                    $entrada = \Carbon\Carbon::parse($acceso->fecha_entrada);
                                    $salida = \Carbon\Carbon::parse($acceso->fecha_salida);
                                    $diff = $entrada->diffInMinutes($salida);
                                    $horas = floor($diff / 60);
                                    $minutos = $diff % 60;
                                @endphp
                                {{ $horas }}h {{ $minutos }}m
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($acceso->portatil)
                                <span class="badge badge-success">💻 Portátil</span>
                            @endif
                            @if($acceso->vehiculo)
                                <span class="badge badge-warning">🚗 Vehículo</span>
                            @endif
                            @if(!$acceso->portatil && !$acceso->vehiculo)
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No se encontraron registros de accesos para el período seleccionado.</p>
        </div>
    @endif

    <!-- Sección de Firmas -->
    @if($accesos->count() > 0)
        <div class="signature-section">
            <div style="display: table; width: 100%; margin-top: 40px;">
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 50%; padding-right: 20px;">
                        <div class="signature-box">
                            <div class="signature-label">ELABORADO POR</div>
                            <div style="margin-top: 5px;">{{ $usuario->nombre ?? 'Sistema' }}</div>
                        </div>
                    </div>
                    <div style="display: table-cell; width: 50%; padding-left: 20px;">
                        <div class="signature-box">
                            <div class="signature-label">REVISADO POR</div>
                            <div style="margin-top: 5px;">_____________________</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <table style="width: 100%; border: none; margin: 0;">
            <tr style="border: none;">
                <td style="text-align: left; border: none; padding: 0;">
                    <strong>CTAccess</strong> - Sistema de Control de Accesos
                </td>
                <td style="text-align: right; border: none; padding: 0;">
                    Página <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
