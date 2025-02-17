<?php

/**
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2013/07/18 Actualización 02/02/2015
 *
 */

/**
 * Clase para consumo del ws de Consulta de registro mercantil el patrón de diseño Singleton
 */
class RR19N {
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
            self::$instancia = new RR19N($url);
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
     * Metodo publico que realiza el consumo de servicio web para consulta por Matricula
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
        require_once ('../funciones/Log.class.php');
         require_once ('../funciones/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR19N no pudo ser le&iacute;da';
            logApi::general2('clienteRR19N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoRR19N = self::validarParametros($parametros);

            if ($respFormatoRR19N ['codigo_error'] == '0000') {

                $formatoEnvio = array(
                    'ConsultaMatricula' => array(
                        'numero_interno' => $respFormatoRR19N ['ConsultaMatriculaNuevo'] ['numero_interno'],
                        'usuario' => $respFormatoRR19N ['ConsultaMatriculaNuevo'] ['usuario'],
                        'codigo_camara' => $respFormatoRR19N ['ConsultaMatriculaNuevo'] ['codigo_camara'],
                        'matricula' => $respFormatoRR19N ['ConsultaMatriculaNuevo'] ['matricula'],
                        'codigo_error' => $respFormatoRR19N ['codigo_error']
                    )
                );

                try {

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
                    logApi::general2('clienteRR19N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    Api::general2('clienteRR19N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUES_ConsultaRegistrosMatricula_BC'])) {
                    return $respWS['RUES_ConsultaRegistrosMatricula_BC'];
                }
            } else {
                return $respFormatoRR19N;
            }
        }
    }

    /**
     * Metodo privado que construye el array en el formato requerido por el servicio web RR19N
     *
     * @param $param 
     * @return $formatoRR19N
     */
    private function validarParametros($param) {

        // Define array con los paramatros validados (No vacios)
        $parametrosNoVacio = array();

        // Carga del array con los parametros definidos en el constructor de la clase
        foreach ($this->parametros as $clave => $valor) {
            if (!empty($this->parametros [$clave])) {
                $param [$clave] = $this->parametros [$clave];
            }
        }

        // Recorre el array de parametros definidos formateando su contenido (Algunos parametros)
        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'matricula' :
                    if (strlen($valor) <= 10) {
                        // Observacion: para la consulta recibe el numero de matricula son ceros. long (10)
                        $param [$clave] = self::completarCeros($valor, 10, 'IZQ');
                    } else {
                        $codigoError = '8000';
                        $mensajeError = 'El parametro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (10)';
                        return $formatoRR19N = array(
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
                        return $formatoRR19N = array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }
                    break;
                default :
            }
        }

        // Verifica que los parametros validados no esten vacios y construye un nuevo array
        foreach ($param as $clave => $valor) {
            if (!empty($param [$clave])) {
                $parametrosNoVacio [$clave] = $param [$clave];
            }
        }

        // Construye el array con los par�metros validados para el servicio web
        $formatoRR19N = array(
            'codigo_error' => '0000',
            'ConsultaMatriculaNuevo' => $parametrosNoVacio
        );

        // Destruye las variables para array
        unset($param);
        unset($parametrosNoVacio);

        // retorna el array final para el servicio web
        return $formatoRR19N;
    }

    /**
     * Metodo privado que completa rellena de ceros a contenido de determinado parametro.
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