<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Error 500</h1>";

try {
    echo "<h2>1. Verificando autoload</h2>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✅ Autoload encontrado y cargado<br>";
    } else {
        echo "❌ No se encontró vendor/autoload.php<br>";
    }

    echo "<h2>2. Verificando clase ClsSii</h2>";

    // Intentar cargar manualmente la clase
    $classFile = __DIR__ . '/app/servicios_sii/ClsSii.php';
    if (file_exists($classFile)) {
        echo "✅ Archivo ClsSii.php encontrado<br>";

        // Verificar si se puede incluir
        include_once $classFile;
        echo "✅ Archivo ClsSii.php incluido correctamente<br>";

        // Verificar si la clase existe
        if (class_exists('App\servicios_sii\ClsSii')) {
            echo "✅ Clase ClsSii disponible<br>";

            // Probar método básico
            $resultado = \App\servicios_sii\ClsSii::prueba("test");
            echo "✅ Método prueba() funciona: " . $resultado . "<br>";

        } else {
            echo "❌ Clase ClsSii no disponible después de incluir<br>";
        }

    } else {
        echo "❌ Archivo ClsSii.php no encontrado en: " . $classFile . "<br>";
    }

    echo "<h2>3. Verificando clases LibreDTE</h2>";
    $clases_libredte = [
        'Auto' => __DIR__ . '/app/servicios_sii/Auto.php',
        'Sii' => __DIR__ . '/app/servicios_sii/Sii.php',
        'FirmaElectronica' => __DIR__ . '/app/servicios_sii/FirmaElectronica.php'
    ];

    foreach ($clases_libredte as $nombre => $archivo) {
        if (file_exists($archivo)) {
            echo "✅ $nombre encontrado<br>";
            try {
                include_once $archivo;
                echo "✅ $nombre incluido correctamente<br>";
            } catch (Exception $e) {
                echo "❌ Error incluyendo $nombre: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ $nombre no encontrado en: $archivo<br>";
        }
    }

    echo "<h2>4. Probando método test_soap</h2>";
    if (class_exists('App\servicios_sii\ClsSii')) {
        $test_soap = \App\servicios_sii\ClsSii::test_soap();
        echo "Resultado test_soap: <pre>" . print_r($test_soap, true) . "</pre>";
    }

} catch (Error $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ Error Fatal:</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ Excepción:</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<h2>5. Información del Sistema</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "SOAP Extension: " . (extension_loaded('soap') ? 'Disponible' : 'No disponible') . "<br>";
echo "OpenSSL Extension: " . (extension_loaded('openssl') ? 'Disponible' : 'No disponible') . "<br>";
echo "cURL Extension: " . (extension_loaded('curl') ? 'Disponible' : 'No disponible') . "<br>";

phpinfo(INFO_MODULES);
?>
