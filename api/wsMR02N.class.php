<?php

/**
 * Clase para consumo del servicio web wsMR02N (RADICACIONES)
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2013/08/02 Actualizacion 02/02/2015
 *
 */
class MR02N {

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
     * método de clase que crea punto de acceso global (patron singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new MR02N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function solicitudRadicacion($parametros) {


        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web MR02N no pudo ser le&iacute;da';
            \logApi::general2('clienteMR02N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {


            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoMR02N = self::validarParametros($parametros);

            $logxml = '-----------------------------------------------------------------------' . chr(13) . chr(10);

            try {

                $opciones = array(
                    'trace' => true,
                    'exceptions' => true,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'encoding' => 'UTF-8'
                );

                $cliente = new SoapClient($this->wsdl, $opciones);

                $respWS = objectToArrayMR02N($cliente->solicitudRadicacion(array('radicacion' => $respFormatoMR02N)));

                $logxml .= chr(13) . chr(10) . 'XML PETICION: ' . $cliente->__getLastRequest() . chr(13) . chr(10) . 'XML RESPUESTA: ' . $cliente->__getLastResponse() . chr(13) . chr(10);
                \logApi::general2('clienteMR02N_' . date('Ymd'), $this->parametros ['numero_interno'], $logxml);
            } catch (SoapFault $fault) {
                $codigoError = '7000';
                $mensajeError = $fault->faultstring;
                \logApi::general2('clienteMR02N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                return array(
                    'codigo_error' => $codigoError,
                    'mensaje_error' => $mensajeError
                );
            } catch (Exception $e) {
                $codigoError = '8000';
                $mensajeError = $e->getMessage();
                \logApi::general2('clienteMR02N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                return array(
                    'codigo_error' => $codigoError,
                    'mensaje_error' => $mensajeError
                );
            }

            if (isset($respWS['RUE_Radicacion_BC'])) {
                return $respWS['RUE_Radicacion_BC'];
            } else {
                return array(
                    'codigo_error' => '9000',
                    'mensaje_error' => $respWS['faultstring']
                );
            }
        }
    }

    /**
     * Metodo privado que construye el array en el formato requerido por el servicio web MR02N
     *
     * @param $param
     * @return $formatoMR02N
     */
    private function validarParametros($param) {

        // Recorre el array de parámetros definidos formateando su contenido (Algunos parámetros)
        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'estado_transaccion' :
                    if (strlen($valor) == 2) {
                        switch ($valor) {
                            case '01' :
                                break;
                            default :
                                $codigoError = '8000';
                                $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no es v&aacute;lido';
                                return $formatoMR02N = array(
                                    'codigo_error' => $codigoError,
                                    'mensaje_error' => $mensajeError
                                );
                        }
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoMR02N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'matricula' :
                    if (strlen($valor) <= 10) {
                        $param [$clave] = str_pad($valor, 10, "0", STR_PAD_LEFT);
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
                        $param [$clave] = str_pad($valor, 12, "0", STR_PAD_LEFT);
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (12)';
                        return $formatoMR02N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;

                case 'codigo_servicio_radicar' :
                    if (strlen($valor) <= 8) {
                        $param [$clave] = self::completarCeros($valor, 8, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoMR02N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'clase_identificacion' :
                    if (strlen($valor) <= 2) {
                        $param [$clave] = self::completarCeros($valor, 2, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (2)';
                        return $formatoMR02N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'numero_identificacion' :
                    if (strlen($valor) <= 14) {
                        $param [$clave] = self::validarIdentificacion($valor);
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' que no cumple la longitud para el env&iacute;o. long (14)';
                        return $formatoMR02N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                default :
            }
        }

        $param['codigo_error'] = '0000';
        return $param;
    }

    /**
     * Metodo privado para validar de Numero de Identificacion (Longitud - Digitos repetidos)
     *
     * @param $valor
     * @return str_pad
     */
    private static function validarIdentificacion($valor) {
        if (is_numeric($valor)) {
            if (strlen($valor) <= 14) {
                if ($valor == '222222222222') {
                    return $valor;
                } else {
                    if (strlen($valor) != substr_count($valor, $valor [0])) {
                        return str_pad($valor, 14, "0", STR_PAD_LEFT);
                    }
                }
            } else {
                if (strlen($valor) == 14) {
                    if (strlen($valor) != substr_count($valor, $valor [0])) {
                        return str_pad($valor, 15, "0", STR_PAD_LEFT);
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
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

function objectToArrayMR02N($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayMR02N', $object);
}

?>