<?php

/**
 * Script rápido para probar ClsSii con las rutas corregidas
 */

// Incluir autoload de Laravel si es necesario
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

// Usar la clase ClsSii
use App\servicios_sii\ClsSii;

// Probar disponibilidad de clases
echo "<h1>Prueba de Clases ClsSii</h1>";

try {
    $resultado_clases = ClsSii::test_clases_disponibles();

    echo "<h2>Estado de las Clases:</h2>";
    echo "<pre>" . print_r($resultado_clases, true) . "</pre>";

    // Contar clases disponibles
    $disponibles = 0;
    $total = count($resultado_clases['clases']);

    foreach ($resultado_clases['clases'] as $nombre => $info) {
        if ($info['disponible']) {
            $disponibles++;
            echo "<div style='color: green;'>✅ {$nombre}: Disponible</div>";
        } else {
            echo "<div style='color: red;'>❌ {$nombre}: NO disponible</div>";
        }
    }

    echo "<h3>Resumen: {$disponibles}/{$total} clases disponibles</h3>";

    if ($disponibles === $total) {
        echo "<div style='color: green; font-weight: bold;'>🎉 ¡Todas las clases están disponibles! El envío al SII debería funcionar.</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>⚠️ Faltan algunas clases. Revisar la instalación de LibreDTE.</div>";
    }

} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}

?>
