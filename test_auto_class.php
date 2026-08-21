<?php

/**
 * Prueba rápida para verificar la clase Auto
 */

try {
    echo "<h1>Prueba de Clases LibreDTE</h1>";

    // Incluir archivos necesarios
    require_once __DIR__ . '/app/servicios_sii/ClsSii.php';
    require_once __DIR__ . '/app/servicios_sii/Auto.php';
    require_once __DIR__ . '/app/servicios_sii/Sii.php';
    require_once __DIR__ . '/app/servicios_sii/FirmaElectronica.php';
    require_once __DIR__ . '/app/servicios_sii/FormatosDocSii.php';

    echo "<h2>1. Verificando ClsSii</h2>";
    $test = \App\servicios_sii\ClsSii::prueba("test");
    echo "✅ ClsSii::prueba() funciona: " . $test . "<br>";

    echo "<h2>2. Verificando clases disponibles</h2>";

    $clases = [
        'Auto' => 'App\servicios_sii\Auto',
        'Sii' => 'App\servicios_sii\Sii',
        'FirmaElectronica' => 'App\servicios_sii\FirmaElectronica',
        'FormatosDocSii' => 'App\servicios_sii\FormatosDocSii'
    ];

    foreach ($clases as $nombre => $clase) {
        if (class_exists($clase)) {
            echo "✅ $nombre: Disponible<br>";
        } else {
            echo "❌ $nombre: NO disponible<br>";

            // Verificar si el archivo existe
            $archivo = __DIR__ . "/app/servicios_sii/$nombre.php";
            if (file_exists($archivo)) {
                echo "&nbsp;&nbsp;&nbsp;📁 Archivo existe: $archivo<br>";
                try {
                    include_once $archivo;
                    if (class_exists($clase)) {
                        echo "&nbsp;&nbsp;&nbsp;✅ Clase cargada después de include<br>";
                    } else {
                        echo "&nbsp;&nbsp;&nbsp;❌ Clase no se cargó después de include<br>";
                    }
                } catch (Exception $e) {
                    echo "&nbsp;&nbsp;&nbsp;❌ Error al incluir: " . $e->getMessage() . "<br>";
                }
            } else {
                echo "&nbsp;&nbsp;&nbsp;❌ Archivo no existe: $archivo<br>";
            }
        }
    }

    echo "<h2>3. Probando dame_firma()</h2>";
    try {
        $firma = \App\servicios_sii\ClsSii::dame_firma();
        if ($firma === false) {
            echo "⚠️ dame_firma() retornó false (posiblemente archivo cert no encontrado)<br>";
        } elseif (is_string($firma)) {
            echo "⚠️ dame_firma() retornó error: " . $firma . "<br>";
        } else {
            echo "✅ dame_firma() retornó un objeto correctamente<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error en dame_firma(): " . $e->getMessage() . "<br>";
    }

    echo "<h2>4. Estado de extensiones</h2>";
    echo "SOAP: " . (extension_loaded('soap') ? '✅' : '❌') . "<br>";
    echo "OpenSSL: " . (extension_loaded('openssl') ? '✅' : '❌') . "<br>";
    echo "cURL: " . (extension_loaded('curl') ? '✅' : '❌') . "<br>";

} catch (Error $e) {
    echo "<div style='color: red; border: 2px solid red; padding: 10px;'>";
    echo "<h3>❌ Error Fatal</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: orange; border: 2px solid orange; padding: 10px;'>";
    echo "<h3>⚠️ Excepción</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "</div>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #2c3e50; }
</style>
