<?php

/**
 * Clase para consumo del servicio web wsFR01N (CONSULTA TRANSACCIONES)
 * 
 * @package funciones
 * @author JINT
 * @since 2015/07/23
 *
 */
class FR01N {

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
            self::$instancia = new FR01N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia uUnica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function consultaTransaccionesRecaudoFecha($parametros) {

        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ($_SESSION["generales"]["pathabsoluto"] .'/api/log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web FR01N no pudo ser le&iacute;da';
            \logApi::general2('clienteFR01N', $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoFR01N = self::validarParametros($parametros);

            if ($respFormatoFR01N['codigo_error'] == '0000') {

                try {

                    $opciones = array(
                        'trace' => true,
                        'exceptions' => true, //
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );


                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = objectToArrayFR01N($cliente->consultaTransaccionesRecaudoFecha(array('consultaTransaccionesRecaudoFecha' => $respFormatoFR01N)));
                } catch (SoapFault $fault) {
                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteFR01N',$this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }

                if (isset($respWS['RUE_Recaudo_BC'])) {
                    return $respWS['RUE_Recaudo_BC'];
                }
            } else {
                return $respFormatoFR01N;
            }
        }
    }

    /*
     * Metodo privado que construye el array en el formato requerido por el servicio web FR01N
     *
     * @param $param
     * @return $formatoFR01N
     */

    private function validarParametros($param) {

        // Recorre el array de parametros definidos formateando su contenido (Algunos parametros)
        foreach ($param as $clave => $valor) {
            switch ($clave) {
                case 'usuario' :
                    if (strlen($valor) > 8) {
                        $codigoError = '8000';
                        $mensajeError = 'El par&aacute;metro ' . strtoupper($clave) . ' no cumple la longitud para el env&iacute;o. long (8)';
                        return $formatoFR01N = array(
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

}

function objectToArrayFR01N($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayFR01N', $object);
}

?>