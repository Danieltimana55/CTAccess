<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portátil Asignado</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #39A900 0%, #007832 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #39A900;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info-row {
            display: flex;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 100px;
        }
        .info-value {
            color: #333;
            font-weight: 500;
        }
        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border: 2px dashed #39A900;
        }
        .qr-title {
            font-weight: 600;
            color: #39A900;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .qr-image {
            background-color: white;
            padding: 15px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .qr-image img {
            width: 250px;
            height: 250px;
            border-radius: 8px;
        }
        .instructions {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .instructions strong {
            color: #2196F3;
            display: block;
            margin-bottom: 10px;
        }
        .instructions ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
            color: #555;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        .footer strong {
            color: #39A900;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .alert-box strong {
            display: block;
            margin-bottom: 5px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="icon"></div>
            <h1>Portátil Asignado</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Sistema de Control de Acceso</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hola <strong>{{ $persona->Nombre }}</strong>,
            </div>
            
            <p>
                Se te ha asignado un nuevo portátil en el sistema <strong>CTAccess</strong>. 
                A continuación encontrarás toda la información relevante y tu código QR único para este equipo.
            </p>

            <!-- Información del Portátil -->
            <div class="info-box">
                <h3 style="margin: 0 0 15px 0; color: #39A900;">📋 Información del Portátil</h3>
                
                <div class="info-row">
                    <span class="info-label"> Serial:</span>
                    <span class="info-value">{{ $portatil->serial }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"> Marca:</span>
                    <span class="info-value">{{ $portatil->marca }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"> Modelo:</span>
                    <span class="info-value">{{ $portatil->modelo }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label"> Asignado el:</span>
                    <span class="info-value">{{ $portatil->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Código QR -->
            <div class="qr-container">
                <div class="qr-title">Tu Código QR Único</div>
                <p style="color: #666; margin-bottom: 15px;">
                    Utiliza este código para registrar el ingreso y salida del portátil
                </p>
                
                @if($portatil->qrCode)
                    <div class="qr-image">
                        <img src="{{ asset($portatil->qrCode) }}" alt="QR Portátil {{ $portatil->serial }}" />
                    </div>
                    <p style="margin-top: 15px; color: #666; font-size: 14px;">
                        <em>El código QR también está adjunto como archivo PNG</em>
                    </p>
                @else
                    <p style="color: #dc3545;">No se pudo generar el código QR</p>
                @endif
            </div>

            <!-- Instrucciones -->
            <div class="instructions">
                <strong>Instrucciones de Uso:</strong>
                <ul>
                    <li><strong>Descarga el código QR</strong> adjunto o guarda esta imagen</li>
                    <li>Puedes <strong>imprimirlo</strong> y pegarlo en tu portátil</li>
                    <li>O guarda la imagen en tu <strong>teléfono móvil</strong></li>
                    <li>Presenta este código al <strong>celador</strong> cuando ingreses o salgas con el equipo</li>
                    <li>El sistema <strong>verificará automáticamente</strong> que el portátil está asignado a ti</li>
                </ul>
            </div>

            <!-- Alerta de Seguridad -->
            <div class="alert-box">
                <strong> Importante:</strong>
                Este portátil está registrado a tu nombre. Eres responsable de su uso y custodia. 
                Reporta cualquier pérdida o daño inmediatamente al área de sistemas.
            </div>

            <p style="margin-top: 30px; color: #666;">
                Si tienes alguna pregunta o no reconoces esta asignación, contacta al área de sistemas de inmediato.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>CTAccess v2.0</strong> • SENA<br>
            Sistema de Control de Acceso<br>
            <small style="color: #adb5bd;">Este es un mensaje automático, por favor no respondas a este correo</small>
        </div>
    </div>
</body>
</html>
