<?php

/**
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2015/09/16
 *
 */

/**
 * Clase para consumo del ws de MUISCAConsultar DIAN
 */
class MUISCAConsultar {
    /*
     * Propiedad estatica privada para almacenar la instancia del singleton
     */

    private static $instancia = null;

    private function __construct($url) {
        $this->wsdl = $url;
    }

    function __destruct() {
        
    }

    /**
     * Mtodo de clase que crea punto de acceso global (patron singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new MUISCAConsultar($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function ConsultarNIT($parametros) {

        $this->parametros = $parametros;

        $respWS = self::consumoWebService($parametros);

        unset($parametros);

        return $respWS;
    }

    public function consumoWebService($arregloEnvio) {

        ini_set('memory_limit', '3048M');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $fallo["codigo_error"] = '7000';
            $fallo["mensaje_error"] = 'La url del servicio web MUISCAConsultar no esta parametrizada';
            \logApi::general2('clienteMuiscaConsultar', $this->parametros ['numero_interno'], $fallo["codigo_error"] . ' - ' . $fallo["mensaje_error"]);
            return $fallo;
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");


            try {

                $opciones = array(
                    'trace' => true,
                    'exceptions' => false,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'encoding' => 'UTF-8',
                    'connection_timeout' => 1
                );

                $cliente = new SoapClient($this->wsdl, $opciones);


                $respWS = objectToArray($cliente->ConsultarNIT($arregloEnvio));
            } catch (SoapFault $fault) {

                $fallo["cod_error"] = '7000';
                $fallo["mensaje_error"] = $fault->faultstring;
                \logApi::general2('clienteMuiscaConsultar', $this->parametros ['numero_interno'], $fallo["codigo_error"] . ' - ' . $fallo["mensaje_error"]);
                return $fallo;
            }

            if (isset($respWS['ConsultarNITResult'])) {

                if (isset($respWS['ConsultarNITResult']['mensaje_error'])) {
                    $mensajeError = $respWS['ConsultarNITResult']['mensaje_error'];
                } else {
                    $mensajeError = 'Consulta OK';
                }
                return $respWS['ConsultarNITResult'];
            }
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