<?php

/**
 * Script de prueba para verificar ClsSii
 */

// Simular el autoload de Laravel si es necesario
require_once __DIR__ . '/../../vendor/autoload.php';

// Usar la clase ClsSii
use App\servicios_sii\ClsSii;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba ClsSii</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Prueba de Clase ClsSii</h1>

    <h2>1. Prueba básica</h2>
    <?php
    try {
        $resultado_basico = ClsSii::prueba("Mundo");
        echo '<div class="success">✅ Prueba básica exitosa: ' . $resultado_basico . '</div>';
    } catch (Exception $e) {
        echo '<div class="error">❌ Error en prueba básica: ' . $e->getMessage() . '</div>';
    }
    ?>

    <h2>2. Prueba SOAP</h2>
    <?php
    try {
        $resultado_soap = ClsSii::test_soap();
        if ($resultado_soap['estado'] === 'OK') {
            echo '<div class="success">✅ SOAP disponible</div>';
            echo '<pre>' . print_r($resultado_soap, true) . '</pre>';
        } else {
            echo '<div class="error">❌ Problema con SOAP</div>';
            echo '<pre>' . print_r($resultado_soap, true) . '</pre>';
        }
    } catch (Exception $e) {
        echo '<div class="error">❌ Error verificando SOAP: ' . $e->getMessage() . '</div>';
        echo '<div class="info">Archivo: ' . $e->getFile() . '</div>';
        echo '<div class="info">Línea: ' . $e->getLine() . '</div>';
    }
    ?>

    <h2>3. Verificación de Clases LibreDTE</h2>
    <?php
    $clases_necesarias = [
        '\sasco\LibreDTE\Sii\Auto',
        '\sasco\LibreDTE\Sii\Sii',
        '\sasco\LibreDTE\FirmaElectronica'
    ];

    foreach ($clases_necesarias as $clase) {
        if (class_exists($clase)) {
            echo '<div class="success">✅ ' . $clase . ' disponible</div>';
        } else {
            echo '<div class="error">❌ ' . $clase . ' NO disponible</div>';
        }
    }
    ?>

    <h2>4. Información del Sistema</h2>
    <ul>
        <li><strong>Versión PHP:</strong> <?php echo PHP_VERSION; ?></li>
        <li><strong>SOAP:</strong> <?php echo extension_loaded('soap') ? 'Disponible' : 'NO disponible'; ?></li>
        <li><strong>OpenSSL:</strong> <?php echo extension_loaded('openssl') ? 'Disponible' : 'NO disponible'; ?></li>
        <li><strong>cURL:</strong> <?php echo extension_loaded('curl') ? 'Disponible' : 'NO disponible'; ?></li>
    </ul>
</body>
</html>
