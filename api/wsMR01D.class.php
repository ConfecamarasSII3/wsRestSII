<?php

/**
 * Clase para consumo del servicio web MR01D (LIQUIDACIONES)
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2013/07/25 Actualizacion 02/02/2015
 *
 */
class MR01D {

    private $wsdl;
    private $parametros;

    /*
     * Propiedad estatica privada para almacenar la instancia del singleton
     */
    private static $instancia = null;

    private function __construct($url) {
        $this->wsdl = $url;//.'/RUE_WebService_DL/MR01D.asmx?WSDL';
        
    }

    function __destruct() {
        unset($this->parametros);
    }

    /**
     * Metodo de clase que crea punto de acceso global (patron singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new MR01D($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function solicitarLiquidacion($parametros) {

        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');
        require_once ('myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web MR01D no pudo ser le&iacute;da';
            \logApi::general2('clienteMR01D_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {


            ini_set("soap.wsdl_cache_enabled", "0");

            $respFormatoMR01D = self::validarParametros($parametros);

            if ($respFormatoMR01D['codigo_error'] == '0000') {

                $logxml = '-----------------------------------------------------------------------' . chr(13) . chr(10);

                try {

                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = objectToArrayMR01D($cliente->solicitudLiquidacion(array('liquidacion' => $respFormatoMR01D)));

                    $logxml.= 'XML PETICION: ' . $cliente->__getLastRequest() . chr(13) . chr(10) . 'XML RESPUESTA: ' . $cliente->__getLastResponse() . chr(13) . chr(10);
                    \logApi::general2('clienteMR01D_' . date('Ymd'), $this->parametros ['numero_interno'], $logxml);
                    
                } catch (SoapFault $fault) {

                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteMR01D_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {

                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteMR01D_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUE_Liquidacion_BC'])) {
                    return $respWS['RUE_Liquidacion_BC'];
                } else {
                    return array(
                        'codigo_error' => '9000',
                        'mensaje_error' => $respWS['faultstring']
                    );
                }
            } else {
                return $respFormatoMR01D;
            }
        }
    }

    /**
     * Metodo privado que construye el array en el formato requerido por el servicio web MR01D
     *
     * @param $param
     * @return $formatoMR01D
     */
    private function validarParametros($param) {

        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'estado' :
                    if (strlen($valor) == 2) {
                        switch ($valor) {
                            case '01' :
                                $param ['estado'] = '01';
                                break;
                            case '03' :
                                $param ['estado'] = '03';
                                break;
                            default :
                                $codigoError = '8000';
                                $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no es v&aacute;lido';
                                return $formatoMR01D = array(
                                    'codigo_error' => $codigoError,
                                    'mensaje_error' => $mensajeError
                                );
                        }
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoMR01D = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'matricula' :
                    if (strlen($valor) <= 10) {
                        $param [$clave] = self::completarCeros($valor, 10, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (10)';
                        return $formatoMR01D = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'inscripcion' :
                    if (strlen($valor) <= 12) {
                        $param [$clave] = self::completarCeros($valor, 12, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (12)';
                        return $formatoMR01D = array(
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
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' que no cumple la longitud para el env&iacute;o. long (29)';
                        return $formatoMR01D = array(
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
     * @param $dato, $longitud, $sentido
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

function objectToArrayMR01D($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayMR01D', $object);
}

?>