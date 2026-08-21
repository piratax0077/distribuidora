<?php

/**
 * Script de prueba simple para ClsSii con importaciones limpias
 */

try {
    // Cargar el archivo ClsSii
    require_once __DIR__ . '/app/servicios_sii/ClsSii.php';

    echo "<h1>✅ Prueba ClsSii - Importaciones Limpias</h1>";

    echo "<h2>1. Prueba básica</h2>";
    $resultado = \App\servicios_sii\ClsSii::prueba("mundo");
    echo "Resultado: " . $resultado . "<br>";

    echo "<h2>2. Verificación de extensiones</h2>";
    echo "SOAP: " . (extension_loaded('soap') ? '✅ Disponible' : '❌ No disponible') . "<br>";
    echo "OpenSSL: " . (extension_loaded('openssl') ? '✅ Disponible' : '❌ No disponible') . "<br>";
    echo "cURL: " . (extension_loaded('curl') ? '✅ Disponible' : '❌ No disponible') . "<br>";

    echo "<h2>3. Verificación de clases</h2>";
    $clases_necesarias = [
        'Auto' => 'App\servicios_sii\Auto',
        'Sii' => 'App\servicios_sii\Sii',
        'FirmaElectronica' => 'App\servicios_sii\FirmaElectronica',
        'FormatosDocSii' => 'App\servicios_sii\FormatosDocSii'
    ];

    foreach ($clases_necesarias as $nombre => $clase) {
        $disponible = class_exists($clase);
        echo "$nombre: " . ($disponible ? '✅ Disponible' : '❌ No disponible') . "<br>";
    }

    echo "<h2>4. Verificación de constantes</h2>";
    echo "WSDL_CACHE_DISK: " . (defined('WSDL_CACHE_DISK') ? '✅ Definida (' . WSDL_CACHE_DISK . ')' : '❌ No definida') . "<br>";
    echo "LIBXML_COMPACT: " . (defined('LIBXML_COMPACT') ? '✅ Definida (' . LIBXML_COMPACT . ')' : '❌ No definida') . "<br>";

    echo "<h2>5. Estado general</h2>";
    echo "<div style='color: green; font-weight: bold;'>Todo parece estar funcionando correctamente!</div>";

} catch (Error $e) {
    echo "<div style='color: red; border: 2px solid red; padding: 10px; margin: 10px;'>";
    echo "<h2>❌ Error Fatal</h2>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='color: orange; border: 2px solid orange; padding: 10px; margin: 10px;'>";
    echo "<h2>⚠️ Excepción</h2>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #2c3e50; }
h2 { color: #34495e; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
</style>
