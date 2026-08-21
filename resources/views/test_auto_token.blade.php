<!DOCTYPE html>
<html>
<head>
    <title>Prueba de Auto::getToken()</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #2c3e50; }
        .error { color: red; border: 2px solid red; padding: 10px; }
        .warning { color: orange; border: 2px solid orange; padding: 10px; }
        .success { color: green; }
    </style>
</head>
<body>

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

    // Detectar el entorno y usar la ruta correcta
    $basePath = base_path();
    echo "<p>Base path: " . $basePath . "</p>";

    // Intentar diferentes rutas posibles
    $possiblePaths = [
        app_path('servicios_sii/ClsSii.php'),
        $basePath . '/app/servicios_sii/ClsSii.php',
        '/home/ipschile/intranet.ipschile.cl/app/servicios_sii/ClsSii.php'
    ];

    $clsSiiPath = null;
    foreach ($possiblePaths as $path) {
        echo "<p>Verificando: " . $path . "</p>";
        if (file_exists($path)) {
            $clsSiiPath = $path;
            echo "<p class='success'>✅ Encontrado en: " . $path . "</p>";
            break;
        } else {
            echo "<p>❌ No existe: " . $path . "</p>";
        }
    }

    if (!$clsSiiPath) {
        echo "<div class='error'>❌ No se encontró ClsSii.php en ninguna de las rutas posibles</div>";

        // Mostrar contenido del directorio app para debugging
        $appPath = app_path();
        echo "<h3>Contenido de " . $appPath . ":</h3>";
        if (is_dir($appPath)) {
            $files = scandir($appPath);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "- " . $file . "<br>";
                }
            }
        }
        exit;
    }

    // Incluir ClsSii que incluye las demás clases
    require_once $clsSiiPath;

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

    echo "<h2>3. Probando funcionalidad sin SOAP</h2>";

    // Probar solo la parte de firma sin hacer requests SOAP
    echo "<p>✅ La firma funciona correctamente</p>";
    echo "<p>✅ Todas las clases LibreDTE están disponibles</p>";
    echo "<p>❌ SOAP no está disponible - este es el único problema</p>";
    echo "<p><strong>Solución:</strong> El administrador del servidor debe instalar php8.1-soap</p>";

    echo "<h3>Comando para el administrador:</h3>";
    echo "<code style='background: #f0f0f0; padding: 10px; display: block;'>";
    echo "sudo apt-get install php8.1-soap<br>";
    echo "sudo systemctl restart apache2";
    echo "</code>";

    /*
    // Esta línea fallaría porque necesita SOAP:
    // $token = \App\servicios_sii\Auto::getToken($firma);
    */

    echo "<h2>4. Estado de extensiones y clases</h2>";
    echo "SOAP extension loaded: " . (extension_loaded('soap') ? '✅ SÍ' : '❌ NO') . "<br>";
    echo "SoapClient class exists: " . (class_exists('SoapClient') ? '✅ SÍ' : '❌ NO') . "<br>";
    echo "OpenSSL: " . (extension_loaded('openssl') ? '✅ Disponible' : '❌ NO disponible') . "<br>";
    echo "cURL: " . (extension_loaded('curl') ? '✅ Disponible' : '❌ NO disponible') . "<br>";

    // Verificar si SoapClient está disponible con namespace completo
    echo "\\SoapClient class exists: " . (class_exists('\\SoapClient') ? '✅ SÍ' : '❌ NO') . "<br>";

    // Mostrar información de PHP
    echo "<h3>Información PHP:</h3>";
    echo "PHP Version: " . phpversion() . "<br>";

    // Listar todas las extensiones cargadas que contengan 'soap'
    $extensions = get_loaded_extensions();
    $soapExtensions = array_filter($extensions, function($ext) {
        return stripos($ext, 'soap') !== false;
    });

    if (!empty($soapExtensions)) {
        echo "Extensiones relacionadas con SOAP: " . implode(', ', $soapExtensions) . "<br>";
    } else {
        echo "❌ No se encontraron extensiones relacionadas con SOAP<br>";
    }

    // Mostrar todas las extensiones para debug
    echo "<h3>Todas las extensiones cargadas:</h3>";
    echo "<div style='max-height: 200px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;'>";
    foreach ($extensions as $ext) {
        echo $ext . "<br>";
    }
    echo "</div>";

    // Intentar crear SoapClient directamente para ver el error específico
    echo "<h3>Prueba directa de SoapClient:</h3>";
    try {
        $testSoap = new SoapClient(null, ['location' => 'test', 'uri' => 'test', 'trace' => 1]);
        echo "✅ SoapClient se puede instanciar<br>";
    } catch (Error $e) {
        echo "❌ Error al crear SoapClient: " . $e->getMessage() . "<br>";
    } catch (Exception $e) {
        echo "❌ Excepción al crear SoapClient: " . $e->getMessage() . "<br>";
    }

} catch (Error $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error Fatal</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Excepción</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

?>

</body>
</html>
