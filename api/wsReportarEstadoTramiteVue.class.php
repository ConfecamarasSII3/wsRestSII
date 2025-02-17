<?php

/**
 * Clase para consumo del servicio web wsMR02N (RADICACIONES)
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2013/08/02 Actualizacion 02/02/2015
 *
 */
class wsReportarEstadoTramiteVue {

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
            self::$instancia = new wsReportarEstadoTramiteVue($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function registroEstadoTramite($parametros) {


        $this->parametros = $parametros;

        ini_set("memory_limit", "1024M");
        require_once ('log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web registroEstadoTramite no pudo ser le&iacute;da';
            \logApi::general2('wsReportarEstadoTramiteVue_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {


            ini_set("soap.wsdl_cache_enabled", "0");
            $respFormatoRegistroEstadoTramite = self::validarParametros($parametros);
            $logxml = '-----------------------------------------------------------------------' . chr(13) . chr(10);
            try {
                $opciones = array(
                    'trace' => true,
                    'exceptions' => true,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'encoding' => 'UTF-8'
                );

                $cliente = new SoapClient($this->wsdl, $opciones);
                $respWS = objectToArrayWsReportarEstadoTramiteVue($cliente->registroEstadoTramite($respFormatoRegistroEstadoTramite));
                $logxml .= chr(13) . chr(10) . 'XML PETICION: ' . $cliente->__getLastRequest() . chr(13) . chr(10) . 'XML RESPUESTA: ' . $cliente->__getLastResponse() . chr(13) . chr(10);
                \logApi::general2('wsReportarEstadoTramiteVue_' . date('Ymd'), $parametros["TRAMITE"]['NUMERO_RADICADO'], $logxml);
            } catch (SoapFault $fault) {
                $codigoError = '7000';
                $mensajeError = $fault->faultstring;
                \logApi::general2('wsReportarEstadoTramiteVue_' . date('Ymd'), $parametros["TRAMITE"]['NUMERO_RADICADO'], $codigoError . ' - ' . $mensajeError);
                return array(
                    'codigo_error' => $codigoError,
                    'mensaje_error' => $mensajeError
                );
            } catch (Exception $e) {
                $codigoError = '8000';
                $mensajeError = $e->getMessage();
                \logApi::general2('wsReportarEstadoTramiteVue_' . date('Ymd'), $parametros["TRAMITE"]['NUMERO_RADICADO'], $codigoError . ' - ' . $mensajeError);
                return array(
                    'codigo_error' => $codigoError,
                    'mensaje_error' => $mensajeError
                );
            }

            if (isset($respWS["registroEstadoTramiteResult"])) {
                return array(
                    'codigo_error' => $respWS["registroEstadoTramiteResult"]["CODIGO_ERROR"],
                    'mensaje_error' => $respWS["registroEstadoTramiteResult"]["MENSAJE_ERROR"]
                );
            } else {
                return array(
                    'codigo_error' => '9000',
                    'mensaje_error' => 'SIN RESPUESTA VALIDA'
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
        return $param;
    }

}

function objectToArrayWsReportarEstadoTramiteVue($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayWsReportarEstadoTramiteVue', $object);
}

?>