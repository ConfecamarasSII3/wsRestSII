<?php

/**
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2013/07/17 Actualización 02/02/2015
 *
 */

/**
 * Clase para consumo del ws de Consultas RUES, consultas sobre todos los registros
 */
class RR18N {
    /*
     * Propiedad estática privada para almacenar la instancia del singletón
     */

    private static $instancia = null;

    private function __construct($url) {
        $this->wsdl = $url;
    }

    function __destruct() {
        unset($this->parametros);
    }

    /**
     * método de clase que crea punto de acceso global (patrón singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new RR18N($url);
        }
        return self::$instancia;
    }

    /**
     * Método que controla la clonación de la instancia única singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    /**
     * Método público que realiza el consumo de servicio web para consulta por nombre
     *
     * @param $parametros
     * @return $respWS
     */
    public function consultarNombre($parametros) {
        /*
          <urn:numero_interno>?</urn:numero_interno>
          <urn:usuario>?</urn:usuario>
          <urn:razon_social>?</urn:razon_social>
         */

        $this->parametros = $parametros;

        ini_set("memory_limit", "2024M");
        require_once ('log.php');
        require_once ('myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR18N no pudo ser le&iacute;da';
            \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            set_time_limit(90);
            ini_set("soap.wsdl_cache_enabled", "0");

            $respFormatoRR18N = self::validarParametros($parametros);

            if ($respFormatoRR18N ['codigo_error'] == '0000') {

                $formatoEnvio = array(
                    'ConsultaRUESNombre' => array(
                        'numero_interno' => $respFormatoRR18N ['ConsultaEmpresas']['numero_interno'],
                        'usuario' => $respFormatoRR18N ['ConsultaEmpresas']['usuario'],
                        'razon_social' => $respFormatoRR18N ['ConsultaEmpresas']['razon_social'],
                        'codigo_error' => $respFormatoRR18N ['codigo_error']
                    )
                );

                try {

                    if (!defined('WSDL_CACHE_NONE')) {
                        define('WSDL_CACHE_NONE', '');
                    }
                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = convertirObjetoEnArray($cliente->consultarNombre($formatoEnvio));
                } catch (SoapFault $fault) {
                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);

                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUES_ConsultaEmpresasNombre_BC'])) {
                    return $respWS['RUES_ConsultaEmpresasNombre_BC'];
                }
            } else {
                return $respFormatoRR18N;
            }
        }
    }

    /**
     * Método público que realiza el consumo de servicio web para consulta por Matricula
     *
     * @param $parametros
     * @return $respWS
     */
    public function consultarMatricula($parametros) {
        /*
          <urn:numero_interno>?</urn:numero_interno>
          <urn:usuario>?</urn:usuario>
          <urn:codigo_camara>?</urn:codigo_camara>
          <urn:matricula>?</urn:matricula>
         */


        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');
        require_once ('myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR18N no pudo ser le&iacute;da';
            \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoRR18N = self::validarParametros($parametros);

            // Verifica si el array que retorna el método validarParametros no presenta errores de validación en parámetros
            if ($respFormatoRR18N ['codigo_error'] == '0000') {


                if ($respFormatoRR18N ['ConsultaEmpresas'] ['codigo_camara'] == 99) {
                    $camara = '';
                } else {
                    $camara = $respFormatoRR18N ['ConsultaEmpresas'] ['codigo_camara'];
                }

                $formatoEnvio = array(
                    'ConsultaRUESMatricula' => array(
                        'numero_interno' => $respFormatoRR18N ['ConsultaEmpresas'] ['numero_interno'],
                        'usuario' => $respFormatoRR18N ['ConsultaEmpresas'] ['usuario'],
                        'codigo_camara' => $camara,
                        'matricula' => $respFormatoRR18N ['ConsultaEmpresas'] ['matricula'],
                        'codigo_error' => $respFormatoRR18N ['codigo_error']
                    )
                );

                try {
                    if (!defined('WSDL_CACHE_NONE')) {
                        define('WSDL_CACHE_NONE', '');
                    }
                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = convertirObjetoEnArray($cliente->consultarMatricula($formatoEnvio));
                } catch (SoapFault $fault) {
                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);

                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUES_ConsultaEmpresasMatricula_BC'])) {
                    return $respWS['RUES_ConsultaEmpresasMatricula_BC'];
                }
            } else {
                return $respFormatoRR18N;
            }
        }
    }

    /**
     * Método público que realiza el consumo de servicio web para consulta por identificación
     *
     * @param $parametros
     * @return $respWS
     */
    public function consultarNumeroIdentificacion($parametros) {
        /*
          <urn:numero_interno>?</urn:numero_interno>
          <urn:usuario>?</urn:usuario>
          <urn:codigo_clase_identificacion>?</urn:codigo_clase_identificacion>
          <urn:numero_identificacion>?</urn:numero_identificacion>
          <urn:digito_verificacion>?</urn:digito_verificacion>
         */


        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');
        require_once ('myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR18N no pudo ser le&iacute;da';
            \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoRR18N = self::validarParametros($parametros);

            if ($respFormatoRR18N ['codigo_error'] == '0000') {
                $formatoEnvio = array(
                    'ConsultaRUESNumeroIdentificacion' => array(
                        'numero_interno' => $respFormatoRR18N ['ConsultaEmpresas']['numero_interno'],
                        'usuario' => $respFormatoRR18N ['ConsultaEmpresas']['usuario'],
                        'numero_identificacion' => $respFormatoRR18N ['ConsultaEmpresas']['numero_identificacion'],
                        'codigo_error' => $respFormatoRR18N ['codigo_error']
                    )
                );

                try {

                    if (!defined('WSDL_CACHE_NONE')) {
                        define('WSDL_CACHE_NONE', '');
                    }
                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = convertirObjetoEnArray($cliente->consultarNumeroIdentificacion($formatoEnvio));

                    $logxml = 'XML PETICION: ' . $cliente->__getLastRequest() . chr(13) . chr(10) . 'XML RESPUESTA: ' . $cliente->__getLastResponse() . chr(13) . chr(10);

                    \logApi::general2('clienteRR18N_' . __FUNCTION__ . '_' . date('Ymd'), $this->parametros ['numero_interno'], $logxml);
                } catch (SoapFault $fault) {
                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);

                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUES_ConsultaEmpresasIdentificacion_BC'])) {
                    return $respWS['RUES_ConsultaEmpresasIdentificacion_BC'];
                }
            } else {
                return $respFormatoRR18N;
            }
        }
    }

    /**
     * Método público que realiza el consumo de servicio web para consulta por palabra clave
     *
     * @param $parametros
     * @return $respWS
     */
    public function consultarPalabraClave($parametros) {
        /*
          <urn:numero_interno>?</urn:numero_interno>
          <urn:usuario>?</urn:usuario>
          <urn:razon_social>?</urn:razon_social>
         */


        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');
        require_once ('myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR18N no pudo ser le&iacute;da';
            \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoRR18N = self::validarParametros($parametros);

            if ($respFormatoRR18N ['codigo_error'] == '0000') {

                $formatoEnvio = array(
                    'ConsultaRUESPalabraClave' => array(
                        'numero_interno' => $respFormatoRR18N ['ConsultaEmpresas']['numero_interno'],
                        'usuario' => $respFormatoRR18N ['ConsultaEmpresas']['usuario'],
                        'razon_social' => $respFormatoRR18N['ConsultaEmpresas']['razon_social'],
                        'codigo_error' => $respFormatoRR18N['codigo_error']
                    )
                );

                try {
                    if (!defined('WSDL_CACHE_NONE')) {
                        define('WSDL_CACHE_NONE', '');
                    }

                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = convertirObjetoEnArray($cliente->consultarPalabraClave($formatoEnvio));
                } catch (SoapFault $fault) {
                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteRR18N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }


                if (isset($respWS['RUES_ConsultaEmpresasNombre_BC'])) {
                    return $respWS['RUES_ConsultaEmpresasNombre_BC'];
                }
            } else {
                return $respFormatoRR18N;
            }
        }
    }

    /**
     * Método privado que construye el array en el formato requerido por el servicio web RR18N
     *
     * @param $param
     * @return $formatoRR18N
     */
    private function validarParametros($param) {

        // Define array con los paramátros validados (No vacíos)
        $parametrosNoVacio = array();

        // Carga del array con los parámetros definidos en el constructor de la clase
        foreach ($this->parametros as $clave => $valor) {
            if (!empty($this->parametros [$clave])) {
                $param [$clave] = $this->parametros [$clave];
            }
        }

        // Recorre el array de parámetros definidos formateando su contenido (Algunos parametros)
        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'matricula' :
                    if (strlen($valor) <= 10) {
                        $param [$clave] = self::completarCeros($valor, 10, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (10)';
                        return $formatoRR18N = array(
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
                        return $formatoRR18N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                case 'numero_identificacion' :
                    if (strlen($valor) <= 15) {
                        $param [$clave] = self::validarIdentificacion($valor);
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' que no cumple la longitud para el env&iacute;o. long (14)';
                        return $formatoRR18N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                default :
            }
        }

        // Verifica que los parámetros validados no esten vacios y construye un nuevo array
        foreach ($param as $clave => $valor) {
            if (!empty($param [$clave])) {
                $parametrosNoVacio [$clave] = $param [$clave];
            }
        }

        // Construye el array con los parámetros validados para el servicio web
        $formatoRR18N = array(
            'codigo_error' => '0000',
            'ConsultaEmpresas' => $parametrosNoVacio
        );

        // Destruye las variables para array
        unset($param);
        unset($parametrosNoVacio);

        // Retorna el array final para el servicio web
        return $formatoRR18N;
    }

    /**
     * Método privado para validar de Número de Identificación (Longitud - Digitos repetidos)
     *
     * @param
     *        	$valor
     * @return str_pad
     */
    private static function validarIdentificacion($valor) {
        if (strlen($valor) < 15) {
            if (strlen($valor) != substr_count($valor, $valor [0])) {
                return str_pad($valor, 14, "0", STR_PAD_LEFT);
            }
        } else {
            if (strlen($valor) != substr_count($valor, $valor [0])) {
                return str_pad($valor, 15, "0", STR_PAD_LEFT);
            }
        }
    }

    /**
     * Método privado que completa rellena de ceros a contenido de determinado parámetro.
     *
     * @param $dato $longitud
     *        	$sentido
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