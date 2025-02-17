<?php

/**
 * Clase para consumo del servicio web MR03N (Cambio de estado)
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2015-07-27
 *
 */
class MR03N {

    private $wsdl;
    private $parametros;

    /*
     * Propiedad estatica privada para almacenar la instancia del singleton
     */
    private static $instancia = null;

    private function __construct($url) {
        $this->wsdl = $url;
    }

    function __destruct() {
        unset($this->parametros);
    }

    /**
     * M�todo de clase que crea punto de acceso global (patron singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new MR03N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function solicitudActualizacionEstado($parametros) {

        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');
        
        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web MR03N no pudo ser le&iacute;da';
            \logApi::general2('clienteMR03N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);

            return array (
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");

            $respFormatoMR03N = self::validarParametros($parametros);


            if ($respFormatoMR03N['codigo_error'] == '0000') {

                try {

                    $opciones = array (
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    /*
                      $cached_wsdl_file = sys_get_temp_dir() . '/cached_wsdl' . __CLASS__ . '.xml';
                      $wsdl_file = file_get_contents($this->wsdl);
                      file_put_contents($cached_wsdl_file, $wsdl_file);
                      $cliente = new SoapClient($cached_wsdl_file, $opciones);
                     */

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = convertirObjetoEnArray($cliente->solicitudActualizacionEstado(array ('actualizacionEstado' => $respFormatoMR03N)));

                    $logxml = '-----------------------------------------------------------------------' . chr(13) . chr(10);
                    $logxml .= 'XML PETICION: ' . $cliente->__getLastRequest() . chr(13) . chr(10) . 'XML RESPUESTA: ' . $cliente->__getLastResponse() . chr(13) . chr(10);
                    \logApi::general2('clienteMR03N_' . date('Ymd'), $this->parametros ['numero_interno'], $logxml);
                } catch (SoapFault $fault) {

                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteMR03N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array (
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteMR03N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array (
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUE_ActualizacionEstado_BC'])) {
                    return $respWS['RUE_ActualizacionEstado_BC'];
                } else {
                    return array (
                        'codigo_error' => '9000',
                        'mensaje_error' => $respWS['faultstring']
                    );
                }
            } else {
                return $respFormatoMR03N;
            }
        }
    }

    /**
     * Metodo privado que construye el array en el formato requerido por el servicio web MR03N
     *
     * @param $param
     * @return $formatoMR03N
     */
    private function validarParametros($param) {

        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'estado_transaccion' :
                    if (strlen($valor) != 2) {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoMR03N = array (
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;

                case 'numero_interno' :
                    if (strlen($valor) <= 29) {
                        $param [$clave] = self::completarCeros($valor, 29, 'DER');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' que no cumple la longitud para el env&iacute;o. long (29)';
                        return $formatoMR03N = array (
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                default :
            }
        }

        // retorna el array final para el servicio web
        $param['codigo_error'] = '0000';
        return $param;
    }

    /**
     * Metodo privado que completa rellena de ceros a contenido de determinado parametro.
     *
     * @param $dato $longitud $sentido
     * @return str_pad 0
     */
    private static function completarCeros($dato, $longitud, $sentido) {
        if (is_numeric($dato)) {
            if (strlen($dato) <= $longitud) {
                if ($sentido == 'IZQ') {
                    return str_pad($dato, $longitud, "0", STR_PAD_LEFT);
                } else {
                    return str_pad($dato, $longitud, "0");
                }
            }
        } else {
            return 0;
        }
    }

}

function convertirObjetoEnArray($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('convertirObjetoEnArray', $object);
}

?>