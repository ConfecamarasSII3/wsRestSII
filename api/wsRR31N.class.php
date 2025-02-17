<?php

/**
 * Clase para el consumo de servicio web RR31N (Reporte contratos, multas y sanciones) implementado el patron singleton
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2014/04/02 Actualizacion 02/02/2015
 *
 */
class RR31N {
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
     * Metodo de clase que crea punto de acceso global (patron singleton)
     */
    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new RR31N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function consultaHistoriaProponente($parametros) {

        $this->parametros = $parametros;
        $arregloEnvio = array('consultaHistorialProponente' => $parametros);
        $respWS = self::consumoWebService($arregloEnvio, __FUNCTION__);
        unset($arregloEnvio);
        return $respWS;
    }

    public function reporteContratos($parametros) {

        $this->parametros = $parametros;
        $arregloEnvio = array('reporteContratos' => $parametros);
        $respWS = self::consumoWebService($arregloEnvio, __FUNCTION__);
        unset($arregloEnvio);
        return $respWS;
    }

    public function reporteMultas($parametros) {

        $this->parametros = $parametros;
        $arregloEnvio = array('reporteMultas' => $parametros);
        $respWS = self::consumoWebService($arregloEnvio, __FUNCTION__);
        unset($arregloEnvio);
        return $respWS;
    }

    public function reporteSanciones($parametros) {

        $this->parametros = $parametros;
        $arregloEnvio = array('reporteSanciones' => $parametros);
        $respWS = self::consumoWebService($arregloEnvio, __FUNCTION__);
        unset($arregloEnvio);
        return $respWS;
    }

    function consumoWebService($arregloEnvio = array(), $metodo = '') {
        ini_set("memory_limit", "1024M");
        require_once ('../funciones/Log.class.php');

        switch ($metodo) {
            case 'consultaHistoriaProponente':
                $paramEnvio = 'consultaHistorialProponente';
                $paramRetorno = 'RUE_ConsultaHistoriaProponenteRR31N_BC';
                break;
            case 'reporteContratos':
                $paramEnvio = $metodo;
                $paramRetorno = 'RUE_ReporteContratoRR31N_BC';
                break;
            case 'reporteMultas':
                $paramEnvio = $metodo;
                $paramRetorno = 'RUE_ReporteMultaRR31N_BC';
                break;
            case 'reporteSanciones':
                $paramEnvio = $metodo;
                $paramRetorno = 'RUE_ReporteSancionRR31N_BE';
                break;
            default:
                $paramEnvio = '';
                $paramRetorno = '';
                break;
        }


        if (trim($paramRetorno) != '') {

            if (empty($this->wsdl)) {
                $codigoError = '7001';
                $mensajeError = 'La url del servicio web RR31N no pudo ser le&iacute;da';
                logApi::general2('clienteRR31N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                return array('codigo_error' => $codigoError, 'mensaje_error' => $mensajeError);
            } else {

                ini_set("soap.wsdl_cache_enabled", "0");

                if (isset($arregloEnvio[$paramEnvio])) {

                    try {

                        $opciones = array(
                            'trace' => true,
                            'exceptions' => false,
                            'cache_wsdl' => WSDL_CACHE_NONE,
                            'encoding' => 'UTF-8'
                        );


                        $cliente = new SoapClient($this->wsdl, $opciones);

                        $respWS = objectToArrayRR31N($cliente->$metodo($arregloEnvio));
                      
                    } catch (SoapFault $fault) {

                        $codigoError = '7000';
                        $mensajeError = $fault->faultstring;
                        logApi::general2('clienteRR31N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
                        return array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    } catch (Exception $e) {
                        $codigoError = '8000';
                        $mensajeError = $e->getMessage();
                        logApi::general2('clienteRR31N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);

                        return array(
                            'codigo_error' => $codigoError,
                            'mensaje_error' => $mensajeError
                        );
                    }

                    if (isset($respWS[$paramRetorno])) {
                        return $respWS[$paramRetorno];
                    }
                } else {
                    return $arregloEnvio;
                }
            }
        } else {
            $codigoError = '7005';
            $mensajeError = 'Error de nombre del método a consumir';
            logApi::general2('clienteRR31N_' . date('Ymd'), $this->parametros ['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        }
    }

}

function objectToArrayRR31N($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayRR31N', $object);
}

?>