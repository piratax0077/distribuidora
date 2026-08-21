<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Prueba ClsSii Simplificada</h1>";

try {
    // Incluir solo la clase ClsSii
    include_once __DIR__ . '/app/servicios_sii/ClsSii.php';

    echo "<h2>Probando método básico</h2>";
    $resultado = \App\servicios_sii\ClsSii::prueba("mundo");
    echo "✅ Método prueba: " . $resultado . "<br>";

    echo "<h2>Probando verificar_constantes</h2>";
    $reflection = new ReflectionClass('\App\servicios_sii\ClsSii');
    $method = $reflection->getMethod('verificar_constantes');
    $method->setAccessible(true);
    $method->invoke(null);
    echo "✅ verificar_constantes ejecutado<br>";

    echo "<h2>Probando test_soap</h2>";
    $soap_test = \App\servicios_sii\ClsSii::test_soap();
    echo "Resultado: <pre>" . print_r($soap_test, true) . "</pre>";

    echo "<h2>Probando test_clases_disponibles</h2>";
    $clases_test = \App\servicios_sii\ClsSii::test_clases_disponibles();
    echo "Resultado: <pre>" . print_r($clases_test, true) . "</pre>";

} catch (Error $e) {
    echo "<div style='color: red; border: 1px solid red; padding: 10px; margin: 10px;'>";
    echo "<h3>❌ Error Fatal:</h3>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: orange; border: 1px solid orange; padding: 10px; margin: 10px;'>";
    echo "<h3>⚠️ Excepción:</h3>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
