<?php

/**
 * Clase para consumo del servicio web wsRR04N (CERTIFICADOS)
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2013/07/25 Actualizacion 02/02/2015
 *
 */
class RR04N {

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
     * metodo de clase que crea punto de acceso global (patron singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new RR04N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function solicitarCertificado($parametros) {

        $this->parametros = $parametros;

        ini_set("memory_limit", "2024M");
        require_once ('log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR04N no pudo ser le&iacute;da';
            \logApi::general2('clienteRR04N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoRR04N = self::validarParametros($parametros);

            if ($respFormatoRR04N['codigo_error'] == '0000') {

                try {

                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true, //
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

                    $respWS = objectToArray($cliente->solicitudCertificado(array('radicacion' => $respFormatoRR04N)));
                } catch (SoapFault $fault) {

                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteRR04N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteRR04N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUE_Certificados_BC'])) {
                    return $respWS['RUE_Certificados_BC'];
                }
            } else {
                return $respFormatoRR04N;
            }
        }
    }

    /*
     * Metodo privado que construye el array en el formato requerido por el servicio web RR04N
     *
     * @param $param
     * @return $formatoRR04N
     */

    private function validarParametros($param) {

        // Recorre el array de parametros definidos formateando su contenido (Algunos parametros)
        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'estado_transaccion' :
                    if (strlen($valor) == 2) {
                        switch ($valor) {
                            case '01' :
                                break;
                            default :
                                $codigoError = '8000';
                                $mensajeError = 'El parametro ' . strtoupper($clave) . ' no es v&aacute;lido';
                                return $formatoRR04N = array(
                                    'codigo_error' => $codigoError,
                                    'mensaje_error' => $mensajeError
                                );
                        }
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoRR04N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'usuario' :
                    if (strlen($valor) > 8) {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoRR04N = array(
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
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (10)';
                        return $formatoRR04N = array(
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
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (12)';
                        return $formatoRR04N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'numero_interno' :
                    if (strlen($valor) <= 29) {
                        $param [$clave] = self::completarCeros($valor, 29, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' que no cumple la longitud para el env&iacute;o. long (29)';
                        return $formatoRR04N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'codigo_servicio' :
                    if (strlen($valor) <= 8) {
                        $param [$clave] = self::completarCeros($valor, 8, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoRR04N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;

                case 'clase_identificacion' :
                    switch ($valor) {
                        case '7':
                            $param [$clave] = '06';
                            break;
                        case '0':
                            $param [$clave] = '06';
                            break;
                        case 'E':
                            $param [$clave] = '08';
                            break;
                        case 'R':
                            $param [$clave] = '07';
                            break;
                        default:
                            $param [$clave] = self::completarCeros($valor, 2, 'IZQ');
                            break;
                    }
                    break;

                case 'numero_identificacion' :
                    $param [$clave] = self::validarIdentificacion($valor);

                    if ($param [$clave] == false) {
                        $param [$clave] = '';
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' que no cumple con una la longitud requerida. (' . $valor . ')';
                        return $formatoRR04N = array(
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
     * @param
     *        	$valor
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
                    } else {
                        return false;
                    }
                }
            } else {
                if (strlen($valor) == 15) {
                    if (strlen($valor) != substr_count($valor, $valor [0])) {
                        return str_pad($valor, 15, "0", STR_PAD_LEFT);
                    } else {
                        return false;
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
     * @param $dato $longitud
     *        	$sentido
     * @return str_pad 0
     */
    function completarCeros($valor, $longitud, $sentido) {

        $dato = str_replace(' ', '', $valor);

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

function objectToArray($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArray', $object);
}

?>