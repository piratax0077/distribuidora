<?php

/**
 * Script de diagnóstico para verificar el estado del servidor SII
 * Ejecutar este archivo para ver qué extensiones están disponibles
 */

require_once 'SiiSinSoap.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico SII - Estado del Servidor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #b8daff;
        }
        .extension-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .extension-card {
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .available {
            background-color: #d4edda;
            border: 2px solid #28a745;
        }
        .unavailable {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
        }
        .instructions {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .steps {
            list-style: none;
            counter-reset: step-counter;
        }
        .steps li {
            counter-increment: step-counter;
            margin: 10px 0;
            padding-left: 30px;
            position: relative;
        }
        .steps li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background: #007bff;
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico del Servidor SII</h1>
        
        <?php
        $config = SiiSinSoap::verificar_configuracion();
        $mensaje_estado = SiiSinSoap::crear_mensaje_estado();
        $instrucciones = SiiSinSoap::generar_instrucciones_hosting();
        ?>
        
        <h2>Estado General</h2>
        <?php if ($config['soap_disponible']): ?>
            <div class="status success">
                ✅ <strong>Perfecto!</strong> Todas las extensiones necesarias están disponibles.
            </div>
        <?php else: ?>
            <div class="status error">
                ❌ <strong>Atención:</strong> La extensión SOAP no está disponible. Se requiere contactar al proveedor de hosting.
            </div>
        <?php endif; ?>
        
        <h2>Estado de Extensiones PHP</h2>
        <div class="extension-grid">
            <div class="extension-card <?php echo $config['soap_disponible'] ? 'available' : 'unavailable'; ?>">
                <h3>SOAP</h3>
                <p><?php echo $config['soap_disponible'] ? '✅ Disponible' : '❌ No disponible'; ?></p>
                <small>Requerida para SII</small>
            </div>
            <div class="extension-card <?php echo $config['curl_disponible'] ? 'available' : 'unavailable'; ?>">
                <h3>cURL</h3>
                <p><?php echo $config['curl_disponible'] ? '✅ Disponible' : '❌ No disponible'; ?></p>
                <small>Para métodos alternativos</small>
            </div>
            <div class="extension-card <?php echo $config['openssl_disponible'] ? 'available' : 'unavailable'; ?>">
                <h3>OpenSSL</h3>
                <p><?php echo $config['openssl_disponible'] ? '✅ Disponible' : '❌ No disponible'; ?></p>
                <small>Para certificados</small>
            </div>
        </div>
        
        <h2>Información Detallada del Sistema</h2>
        <div class="info">
            <ul>
                <li><strong>Versión de PHP:</strong> <?php echo PHP_VERSION; ?></li>
                <li><strong>Sistema Operativo:</strong> <?php echo PHP_OS; ?></li>
                <li><strong>Extensiones cargadas:</strong> <?php echo count(get_loaded_extensions()); ?> extensiones</li>
                <li><strong>Memoria disponible:</strong> <?php echo ini_get('memory_limit'); ?></li>
            </ul>
        </div>
        
        <?php if (!$config['soap_disponible']): ?>
            <h2>🔧 Instrucciones para Habilitar SOAP</h2>
            
            <?php foreach ($instrucciones as $tipo => $instruccion): ?>
                <div class="instructions">
                    <h3><?php echo $instruccion['titulo']; ?></h3>
                    <ol class="steps">
                        <?php foreach ($instruccion['pasos'] as $paso): ?>
                            <li><?php echo $paso; ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endforeach; ?>
            
            <div class="status warning">
                <strong>Mensaje para el proveedor de hosting:</strong><br>
                "Necesito habilitar la extensión SOAP de PHP para mi sitio web. Es necesaria para la integración con el SII (Servicio de Impuestos Internos de Chile). Por favor, habiliten php_soap para la versión <?php echo PHP_VERSION; ?> de PHP."
            </div>
        <?php endif; ?>
        
        <h2>Extensiones Disponibles en el Servidor</h2>
        <div class="info">
            <details>
                <summary>Ver todas las extensiones (<?php echo count(get_loaded_extensions()); ?> total)</summary>
                <div style="columns: 3; margin-top: 10px;">
                    <?php 
                    $extensions = get_loaded_extensions();
                    sort($extensions);
                    foreach ($extensions as $ext): 
                    ?>
                        <div><?php echo $ext; ?></div>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
        
        <div class="status info">
            <strong>Próximos pasos:</strong><br>
            1. Si SOAP no está disponible, contactar al proveedor de hosting<br>
            2. Una vez habilitada la extensión, ejecutar las pruebas de integración SII<br>
            3. Verificar que los documentos se envíen correctamente
        </div>
        
        <p style="text-align: center; margin-top: 30px; color: #666; font-size: 0.9em;">
            Diagnóstico generado el <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>