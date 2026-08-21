<?php

/**
 * Configuración para SII sin SOAP
 * Este archivo maneja la configuración cuando la extensión SOAP no está disponible
 */

class SiiSinSoap {

    /**
     * Verificar si SOAP está disponible y configurar alternativas
     */
    public static function verificar_configuracion() {
        $config = [
            'soap_disponible' => extension_loaded('soap'),
            'curl_disponible' => extension_loaded('curl'),
            'openssl_disponible' => extension_loaded('openssl'),
            'puede_usar_alternativo' => false,
            'errores' => [],
            'advertencias' => []
        ];

        if (!$config['soap_disponible']) {
            $config['errores'][] = 'Extensión SOAP no disponible - Funcionalidad limitada';

            if ($config['curl_disponible'] && $config['openssl_disponible']) {
                $config['puede_usar_alternativo'] = true;
                $config['advertencias'][] = 'SOAP no disponible pero cURL y OpenSSL sí - Se pueden usar métodos alternativos limitados';
            } else {
                $config['errores'][] = 'Faltan extensiones críticas para métodos alternativos';
            }
        } else {
            $config['puede_usar_alternativo'] = true;
        }

        return $config;
    }

    /**
     * Crear mensaje informativo para el usuario sobre el estado del servidor
     */
    public static function crear_mensaje_estado() {
        $config = self::verificar_configuracion();

        $mensaje = "Estado del servidor para integración SII:\n\n";

        $mensaje .= "Extensiones PHP:\n";
        $mensaje .= "- SOAP: " . ($config['soap_disponible'] ? '✓ Disponible' : '✗ No disponible') . "\n";
        $mensaje .= "- cURL: " . ($config['curl_disponible'] ? '✓ Disponible' : '✗ No disponible') . "\n";
        $mensaje .= "- OpenSSL: " . ($config['openssl_disponible'] ? '✓ Disponible' : '✗ No disponible') . "\n\n";

        if (!$config['soap_disponible']) {
            $mensaje .= "⚠️ IMPORTANTE: La extensión SOAP es necesaria para la funcionalidad completa del SII.\n\n";
            $mensaje .= "Acciones recomendadas:\n";
            $mensaje .= "1. Contactar al proveedor de hosting para habilitar la extensión SOAP\n";
            $mensaje .= "2. Verificar que el servidor tenga PHP con soporte SOAP\n";
            $mensaje .= "3. Reiniciar el servidor web después de habilitar SOAP\n\n";

            if ($config['puede_usar_alternativo']) {
                $mensaje .= "Funcionalidad limitada disponible con cURL mientras se habilita SOAP.\n";
            } else {
                $mensaje .= "Sin SOAP, cURL o OpenSSL no se puede conectar al SII.\n";
            }
        } else {
            $mensaje .= "✅ Todas las extensiones necesarias están disponibles.\n";
        }

        return $mensaje;
    }

    /**
     * Generar instrucciones específicas para diferentes proveedores de hosting
     */
    public static function generar_instrucciones_hosting() {
        return [
            'cpanel' => [
                'titulo' => 'Para servidores con cPanel',
                'pasos' => [
                    'Acceder al cPanel de su hosting',
                    'Buscar "PHP Selector" o "Selector PHP"',
                    'Seleccionar la versión de PHP actual',
                    'En "Extensions", marcar la casilla "soap"',
                    'Guardar cambios y esperar unos minutos'
                ]
            ],
            'plesk' => [
                'titulo' => 'Para servidores con Plesk',
                'pasos' => [
                    'Acceder al panel Plesk',
                    'Ir a "Configuración PHP"',
                    'Seleccionar la versión de PHP',
                    'En extensiones, habilitar "soap"',
                    'Aplicar cambios'
                ]
            ],
            'general' => [
                'titulo' => 'Instrucciones generales',
                'pasos' => [
                    'Contactar soporte técnico del hosting',
                    'Solicitar habilitar extensión PHP SOAP',
                    'Especificar la versión de PHP del sitio',
                    'Pedir confirmación una vez habilitada',
                    'Reiniciar aplicación si es necesario'
                ]
            ]
        ];
    }
}

?>
