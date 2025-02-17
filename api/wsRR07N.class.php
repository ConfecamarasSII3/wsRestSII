<?php

/**
 * Clase para consumo del servicio web wsRR07N(CONSULTA RUTA NACIONAL)
 * 
 * @package funciones
 * @author Weymer
 * @since 2016/08/24
 *
 */
class RR07N {

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
            self::$instancia = new RR07N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia uUnica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function consultaRutaNacional($parametros) {

        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR07N no pudo ser le&iacute;da';
            \logApi::general2('clienteRR07N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");

            try {

                $opciones = array(
                    'trace' => true,
                    'exceptions' => true, //
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'encoding' => 'UTF-8'
                );

                $cliente = new SoapClient($this->wsdl, $opciones);

                $respWS = objectToArrayRR07N($cliente->consultaRutaNacional(array('RutaNacional' => $parametros)));
            } catch (SoapFault $fault) {
                $codigoError = '7000';
                $mensajeError = $fault->faultstring;
                \logApi::general2('clienteRR07N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);

                return array(
                    'codigo_error' => $codigoError,
                    'mensaje_error' => $mensajeError
                );
            }



            if (isset($respWS['RUE_RutaNacional_BC'])) {
                return $respWS['RUE_RutaNacional_BC'];
            }
        }
    }

}

function objectToArrayRR07N($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayRR07N', $object);
}

?>