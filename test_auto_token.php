<?php

/**
 * Prueba específica para Auto::getToken()
 */

// Definir constantes necesarias para SOAP
if (!defined('WSDL_CACHE_DISK')) {
    define('WSDL_CACHE_DISK', 1);
}
if (!defined('LIBXML_COMPACT')) {
    define('LIBXML_COMPACT', 65536);
}

try {
    echo "<h1>Prueba de Auto::getToken()</h1>";

    // Incluir ClsSii que incluye las demás clases
    require_once __DIR__ . '/app/servicios_sii/ClsSii.php';

    echo "<h2>1. Verificando clases necesarias</h2>";

    $clases = [
        'ClsSii' => 'App\servicios_sii\ClsSii',
        'Auto' => 'App\servicios_sii\Auto',
        'Sii' => 'App\servicios_sii\Sii',
        'FirmaElectronica' => 'App\servicios_sii\FirmaElectronica'
    ];

    foreach ($clases as $nombre => $clase) {
        if (class_exists($clase)) {
            echo "✅ $nombre: Disponible<br>";
        } else {
            echo "❌ $nombre: NO disponible<br>";
        }
    }

    echo "<h2>2. Obteniendo firma</h2>";
    $firma = \App\servicios_sii\ClsSii::dame_firma();
    if ($firma === false) {
        echo "❌ Error: dame_firma() retornó false<br>";
        exit;
    } elseif (is_string($firma)) {
        echo "❌ Error en dame_firma(): " . $firma . "<br>";
        exit;
    } else {
        echo "✅ Firma obtenida correctamente (tipo: " . get_class($firma) . ")<br>";
    }

    echo "<h2>3. Probando Auto::getToken()</h2>";

    // Intentar obtener token
    $token = \App\servicios_sii\Auto::getToken($firma);

    if ($token === false) {
        echo "❌ Auto::getToken() retornó false<br>";
        echo "Esto puede ser normal si hay problemas de conexión con el SII<br>";
    } elseif (is_string($token) && strlen($token) > 10) {
        echo "✅ Token obtenido correctamente: " . substr($token, 0, 20) . "...<br>";
    } else {
        echo "⚠️ Resultado inesperado: " . var_export($token, true) . "<br>";
    }

    echo "<h2>4. Estado de extensiones</h2>";
    echo "SOAP: " . (extension_loaded('soap') ? '✅ Disponible' : '❌ NO disponible') . "<br>";
    echo "OpenSSL: " . (extension_loaded('openssl') ? '✅ Disponible' : '❌ NO disponible') . "<br>";
    echo "cURL: " . (extension_loaded('curl') ? '✅ Disponible' : '❌ NO disponible') . "<br>";

} catch (Error $e) {
    echo "<div style='color: red; border: 2px solid red; padding: 10px;'>";
    echo "<h3>❌ Error Fatal</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: orange; border: 2px solid orange; padding: 10px;'>";
    echo "<h3>⚠️ Excepción</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #2c3e50; }
</style>
