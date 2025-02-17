<?php

/**
 * Clase para el consumo de servicio web RR30N (radicarRegistroProponente) implementado el patron singleton
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2014/04/03 Actualizacion 02/02/2015 Actualizacion 03/07/2019
 *
 */
class RR30N {
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
            self::$instancia = new RR30N($url);
        }
        return self::$instancia;
    }

    /**
     * Metodo que controla la clonacion de la instancia unica singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function radicarRegistroProponente($parametros) {

        $this->parametros = $parametros;

        $parametrosValidados = self::validarParametros($parametros);

        $respWS = self::consumoWebService($parametrosValidados);

        unset($parametrosValidados);

        return $respWS;
    }

    public function consumoWebService($arregloEnvio) {

        ini_set('memory_limit', '3048M');
        ini_set('display_errors', '0');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $codigoError = '7000';
            $mensajeError = 'La url del servicio web RR30N no esta parametrizada';
            \logApi::general2('clienteRR30N_' . date('Ymd'), $this->parametros['numero_interno'], $codigoError . ' - ' . $mensajeError);
            return array(
                'codigo_error' => $codigoError,
                'mensaje_error' => $mensajeError
            );
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");

            if ($arregloEnvio['codigo_error'] == '0000') {

                try {

                    $opciones = array(
                        'trace' => true,
                        'exceptions' => false,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'encoding' => 'UTF-8'
                    );

                    $cliente = new SoapClient($this->wsdl, $opciones);

                    $respWS = objectToArrayRR30N($cliente->radicarRegistroProponente(array('radicarRegistroProponente' => $arregloEnvio)));

                    $logxml = 'XML PETICION: ' . $cliente->__getLastRequest() . chr(13) . chr(10) . 'XML RESPUESTA: ' . $cliente->__getLastResponse() . chr(13) . chr(10);

                    \logApi::general2('clienteRR30N_' . date('Ymd'), $this->parametros['numero_interno'], $logxml);
                } catch (SoapFault $fault) {
                    $codigoError = '7000';
                    $mensajeError = $fault->faultstring;
                    \logApi::general2('clienteRR30N_' . date('Ymd'), $this->parametros['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                } catch (Exception $e) {
                    $codigoError = '8000';
                    $mensajeError = $e->getMessage();
                    \logApi::general2('clienteRR30N_' . date('Ymd'), $this->parametros['numero_interno'], $codigoError . ' - ' . $mensajeError);
                    return array(
                        'codigo_error' => $codigoError,
                        'mensaje_error' => $mensajeError
                    );
                }


                if (isset($respWS['RUE_RadicarRegistroProponente_BC'])) {
                    return $respWS['RUE_RadicarRegistroProponente_BC'];
                }
            } else {
                return $arregloEnvio;
            }
        }
    }

    function validarParametros($param) {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $logxml = 'ARREGLO PREVIO: ' . var_export($param, true) . chr(13) . chr(10);
        \logApi::general2('clienteRR30N_' . date('Ymd'), '', $logxml);

        foreach ($param as $clave => $valor) {

            switch ($clave) {
                case 'numero_interno':
                    $param[$clave] = self::completarCeros($valor, 29, 'DER');
                    break;
                case 'codigo_clase_identificacion':
                    switch ($valor) {
                        case '0':
                            $param[$clave] = '06';
                            break;
                        case 'E':
                            $param[$clave] = '08';
                            break;
                        case 'R':
                            $param[$clave] = '07';
                            break;
                        default:
                            $param[$clave] = self::completarCeros($valor, 2, 'IZQ');
                            break;
                    }
                    break;
                case 'camara_comercio_proponente':
                case 'codigo_estado_proponente':
                case 'codigo_tamano_empresa':
                    $valor = !empty($valor) ? $valor : '00';
                    $param[$clave] = self::completarCeros($valor, 2, 'IZQ');
                    break;
                case 'inscripcion_proponente':
                    $param[$clave] = self::completarCeros($valor, 12, 'IZQ');
                    break;
                case 'matricula':
                    $param[$clave] = self::completarCeros($valor, 10, 'IZQ');
                    break;
                case 'numero_identificacion':
                    $param[$clave] = self::validarIdentificacion($valor);
                    break;
                case 'activo_corriente':
                case 'fijo_neto':
                case 'otros_Activos':
                case 'valorizaciones':
                case 'activo_total':
                case 'pasivo_corriente':
                case 'largo_plazo':
                case 'pasivo_total':
                case 'patrimonio':
                case 'ingresos_operacionales':
                case 'ingresos_no_operacionales':
                case 'gastos_operacionales':
                case 'gastos_no_operacionales':
                case 'costo_ventas':
                case 'gastos_intereses':
                case 'indice_liquidez':
                case 'indice_endeudamiento':
                case 'razon_cobertura_intereses':
                case 'utilidad_perdida_operacional': //
                case 'utilidad_perdida_neta': //
                case 'rentabilidad_patrimonio': //
                case 'rentabilidad_activo': //
                    $param[$clave] = $valor;
                    break;
                case 'autorizacion_datos':
                    if (($valor == 'SI') || ($valor == 'S')) {
                        $param[$clave] = 'S';
                    } else {
                        $param[$clave] = 'N';
                    }
                    break;
                case 'clasificacion_unspsc':
                    $arrUnspsc = $valor;
                    foreach ($arrUnspsc as $key => $value) {
                        $param[$clave][$key]['codigo_unspsc'] = self::completarCeros($value['codigo_unspsc'], 8, 'DER');
                    }
                    break;
                case 'grupo_empresarial_situaciones_control':
                    $arrGrupos = $valor;
                    foreach ($arrGrupos as $key => $value) {
                        $param[$clave][$key]['id_grupo'] = self::completarCeros($value['id_grupo'], 1, 'IZQ');
                        $param[$clave][$key]['nit'] = self::validarIdentificacion($value['nit']);
                        /*
                          $param[$clave][$key]['nombre'] = self::completarCeros($value['nombre'], 1,'IZQ');
                          $param[$clave][$key]['domicilio'] = self::completarCeros($value['domicilio'], 1,'IZQ');
                          $param[$clave][$key]['ge_matriz'] = self::completarCeros($value['ge_matriz'], 1,'IZQ');
                          $param[$clave][$key]['sc_controlante'] = self::completarCeros($value['sc_controlante'], 1,'IZQ');
                         */
                    }
                    break;
            }
        }
        return $param;
    }

    function validarIdentificacion($valor1) {

        $valor = str_replace(' ', '', $valor1);

        if (!empty($valor)) {
            if (is_numeric($valor)) {
                if (strlen($valor) <= 14) {
                    if (strlen($valor) != substr_count($valor, $valor[0])) {
                        return str_pad($valor, 14, "0", STR_PAD_LEFT);
                    }
                } else {
                    if (strlen($valor) == 15) {
                        if (strlen($valor) != substr_count($valor, $valor[0])) {
                            return str_pad($valor, 15, "0", STR_PAD_LEFT);
                        }
                    }
                }
            }
        } else {
            return '00000000000000';
        }
    }

    function completarCeros($dato1, $longitud, $sentido = NULL) {
        $dato = str_replace(' ', '', $dato1);

        if (is_numeric($dato)) {
            if (strlen($dato) <= $longitud) {
                switch ($sentido) {
                    case 'DER':
                        return str_pad($dato, $longitud, "0", STR_PAD_RIGHT);
                        break;
                    case 'IZQ':
                        return str_pad($dato, $longitud, "0", STR_PAD_LEFT);
                        break;
                    default:
                        return '';
                        break;
                }
            }
        } else {
            return 0;
        }
    }

}

function formatXml($Xml) {

    /*
      $Xml=str_replace("ns1:","urn:", $Xml);
      $Xml=str_replace("SOAP-ENV:","soapenv:", $Xml);
     */

    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($Xml);
    return htmlentities($dom->saveXML());
}

function objectToArrayRR30N($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayRR30N', $object);
}
