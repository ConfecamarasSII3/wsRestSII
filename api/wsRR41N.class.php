<?php

/**
 * NUEVO WEB SERVICE DE ACTUALIZACIÓN DE MATRICULADOS
 * Clase para el consumo de servicio web RR41N (radicarRegistroMercantil) implementado el patron singleton
 * 
 * @package funciones
 * @author Weymer Sierra Ibarra
 * @since 2016-DIC-28
 */
class RR41N {
    /*
     * Propiedad estatica privada para almacenar la instancia del singletón
     */

    private static $instancia = null;

    private function __construct($url) {
        $this->wsdl = $url;
    }

    function __destruct() {
        
    }

    /*
     * Método de clase que crea punto de acceso global (patrón singleton)
     */

    public static function singleton($url) {
        if (!isset(self::$instancia)) {
            self::$instancia = new RR41N($url);
        }
        return self::$instancia;
    }

    /**
     * Método que controla la clonación de la instancia única singleton
     */
    public function __clone() {
        trigger_error('No se permite Clonar', E_USER_ERROR);
    }

    public function radicarRegistroMercantil($expediente) {
        $parametros = self::construirParametros($expediente);
        $this->parametros = $parametros;
        $respWS = self::consumoWebService($parametros);
        unset($parametros);
        return $respWS;
    }

    public function consumoWebService($arregloEnvio) {

        $mysqli = conexionMysqliApi();

        // Verifica si la url del servicio web proveedor esta definida
        if (empty($this->wsdl)) {
            $arregloEnvio["codigo_error"] = '7000';
            $arregloEnvio["mensaje_error"] = 'La url del servicio web RR41N no esta parametrizada';
            \logApi::general2('clienteRR41N_' . date('Ymd'), '', $arregloEnvio["codigo_error"] . ' - ' . $arregloEnvio["mensaje_error"]);
            $mysqli->close();
            return $arregloEnvio;
        } else {

            ini_set("soap.wsdl_cache_enabled", "0");
            ini_set('soap.wsdl_cache_ttl', '0');

            try {

                $opciones = array(
                    'trace' => true,
                    'exceptions' => true,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'encoding' => 'UTF-8'
                );

                $cliente = new SoapClient($this->wsdl, $opciones);

                $respWS = objectToArrayRR41N($cliente->radicarRegistroMercantil(array('registroMercantil' => $arregloEnvio)));
                /*
                if (((TIPO_AMBIENTE == 'PRUEBAS') && ($arregloEnvio['usuario'] != 'BATCH')) || substr($arregloEnvio['usuario'], 0, 6) == 'ADMGEN') {
                    if (!isset($_SESSION["generales"]["batch"]) || $_SESSION["generales"]["batch"] == 'no') {
                        echo '<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js?lang=xml&amp;skin=sons-of-obsidian"></script>';
                        echo "XML ENVIADO:<br/><pre class=\"prettyprint linenums\" >" . formatXml($cliente->__getLastRequest()) . "</pre>";
                        echo "XML RETORNADO:<br/><pre class=\"prettyprint linenums\" >" . formatXml($cliente->__getLastResponse()) . "</pre>";
                    }
                }
                 */
            } catch (SoapFault $fault) {
                $arregloEnvio["codigo_error"] = '7000';
                $arregloEnvio["mensaje_error"] = $fault->faultstring;
                $logTxt = '--------------------------------------SoapFault----------------------------------------------------------' . chr(13) . chr(10);
                $logTxt .= $arregloEnvio["codigo_error"] . ' - ' . $arregloEnvio["mensaje_error"];
                \logApi::general2('clienteRR41N_' . date('Ymd'), $this->parametros ['matricula'], $logTxt);
                actualizarLogMysqliApi($mysqli, '410', $_SESSION["generales"]["codigousuario"], 'wsRR41N.class.php', '', '', '', $logTxt, $this->parametros ['matricula'], '', '');
                unset($this->parametros);
                $mysqli->close();
                return $arregloEnvio;
            } catch (Exception $e) {
                $arregloEnvio["codigo_error"] = '8000';
                $arregloEnvio["mensaje_error"] = $e->getMessage();
                $logTxt = '---------------------------------------Exception---------------------------------------------------------' . chr(13) . chr(10);
                $logTxt .= $arregloEnvio["codigo_error"] . ' - ' . $arregloEnvio["mensaje_error"];
                logApi::general2('clienteRR41N_' . date('Ymd'), $this->parametros ['matricula'], $logTxt);
                actualizarLogMysqliApi($mysqli, '410', $_SESSION["generales"]["codigousuario"], 'wsRR41N.class.php', '', '', '', $logTxt, $this->parametros ['matricula'], '', '');
                unset($this->parametros);
                $mysqli->close();
                return $arregloEnvio;
            }

            if (isset($respWS['RUES_RegistroMercantil_41N_Response'])) {
                $respWS['RUES_RegistroMercantil_41N_Response']['hash_control'] = $this->parametros['hash_control'];
                $logTxt = "XML ENVIADO:<br>" . formatXml($cliente->__getLastRequest()) . "<br><br>";
                $logTxt .= "XML RETORNADO:<br>" . formatXml($cliente->__getLastResponse());
                if ($respWS['RUES_RegistroMercantil_41N_Response']['codigo_error'] == '0000') {
                    actualizarLogMysqliApi($mysqli, '403', $_SESSION["generales"]["codigousuario"], 'wsRR41N.class.php', '', '', '', $logTxt, $this->parametros ['matricula'], '', '');
                } else {
                    actualizarLogMysqliApi($mysqli, '410', $_SESSION["generales"]["codigousuario"], 'wsRR41N.class.php', '', '', '', $logTxt, $this->parametros ['matricula'], '', '');
                }
                $mysqli->close();
                return $respWS['RUES_RegistroMercantil_41N_Response'];
            }
        }
    }

    function construirParametros($datos = '') {
        require_once ('retornarHomologaciones.php');
        $mysqli = conexionMysqliApi();

        $arrDatosInfoFinanciera = array();
        $arrDatosInfoCapitales = array();
        $arrDatosInfoAdicional = array();
        $arrDatosPropietarios = array();
        $arrDatosVinculos = array();
        $arrDatosHistoricoPagos = array();

        /*
         * Validaciones Campos Identificación
         */
        if (trim($datos['tipoidentificacion']) == '2') {

            //Juridicas principales
            if (($datos["organizacion"] != '01') &&
                    ($datos["organizacion"] != '02') &&
                    ($datos["categoria"] == '1')) {

                $Nit = trim($datos["nit"]);
                $longitudNit = strlen($Nit);

                switch ($longitudNit) {
                    case 10:
                        $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                        $numIdentificacion = substr(trim($datos["nit"]), 0, - 1);
                        $dv = substr($datos["nit"], - 1, 1);
                        break;
                    case 9:
                        $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                        $numIdentificacion = trim($datos["nit"]);
                        $dv = \funcionesGenerales::calcularDv($datos["nit"]);
                        break;
                    default:
                        $tipoIdentificacion = '';
                        $numIdentificacion = '';
                        $dv = '';
                        break;
                }
            } else {
                if ($datos["nit"] != '') {
                    $tipoIdentificacion = trim($datos["tipoidentificacion"]);
                    $numIdentificacion = substr(trim($datos["nit"]), 0, - 1);
                    $dv = substr($datos["nit"], - 1, 1);
                } else {
                    $tipoIdentificacion = '';
                    $numIdentificacion = '';
                    $dv = '';
                }
            }
        } else {
            if (trim($datos['tipoidentificacion']) != 'V') {
                $tipoIdentificacion = trim($datos['tipoidentificacion']);
                $numIdentificacion = trim($datos["identificacion"]);
                $dv = '';
            } else {
                $tipoIdentificacion = '09';
                if (strlen($datos["identificacion"]) == 15) {
                    $numIdentificacion = substr(trim($datos["identificacion"]), 1);
                } else {
                    $numIdentificacion = trim($datos["identificacion"]);
                }
                $dv = '';
            }
        }

        /*
         * Forzar en el caso de establecimientos, sucursales o agencias que se envie vacio los datos de identificación sin importar la data que tenga la cámara.
         */

        //Sucursales y agencias
        $valorEstSucAg = '';
        if (($datos["categoria"] == '2') || ($datos["categoria"] == '3')) {
            $tipoIdentificacion = '';
            $numIdentificacion = '';
            $dv = '';
            $valorEstSucAg = $datos["actvin"];
        }
        //Establecimientos 
        if ($datos["organizacion"] == '02') {
            $tipoIdentificacion = '';
            $numIdentificacion = '';
            $dv = '';
            $valorEstSucAg = $datos["valest"];
        }


        $fechaMatricula = $datos["fechamatricula"];
        $fechaRenovacion = $datos["fecharenovacion"];
        $ultAnoRenovado = $datos["ultanoren"];
        $anoMatricula = substr($fechaMatricula, 0, 4);
        $anoRenovacion = substr($fechaRenovacion, 0, 4);

        /*
        if ($anoMatricula == $anoRenovacion) {
            $fechaRenovacion = '';
            $ultAnoRenovado = 0;
        }

        if ($anoMatricula == $ultAnoRenovado) {
            $fechaRenovacion = '';
            $ultAnoRenovado = 0;
        }
        */
        
        switch ($datos["estadomatricula"]) {
            case 'MC':
            case 'MF':
            case 'IC':

                if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                    if (trim($datos["fechaliquidacion"]) != '') {
                        $fechaCancelacion = $datos["fechaliquidacion"];
                    } else {
                        $fechaCancelacion = $datos["fechacancelacion"];
                    }
                } else {
                    if (trim($datos["fechacancelacion"]) != '') {
                        $fechaCancelacion = $datos["fechacancelacion"];
                    } else {
                        $fechaCancelacion = '';
                    }
                }

                $indicadorMotivoCancelacion = '0';
                if (trim($datos["motivocancelacion"]) != '') {
                    $indicadorMotivoCancelacion = $datos["motivocancelacion"];
                }

                break;
            default:
                $fechaCancelacion = '';
                $indicadorMotivoCancelacion = '';
                break;
        }

        if (trim($datos["fechavencimiento"]) == '99999999') {
            $fechaVigencia = '99991231';
        } else {
            if (trim($datos["fechavencimiento"]) != '') {
                $fechaVigencia = $datos["fechavencimiento"];
            } else {
                $fechaVigencia = '99991231';
            }
        }

        if (trim($datos["fechaconstitucion"]) != '') {
            $fechaConstitucion = $datos["fechaconstitucion"];
        } else {
            $fechaConstitucion = '';
        }

        /*
         * Validaciones Campo clasificación Importador Exportador
         */
        if (($datos["impexp"] == '1') || ($datos["impexp"] == '3')) {
            $indicadorImpExp = '1';
        }
        if (($datos["impexp"] == '2') || ($datos["impexp"] == '3')) {
            $indicadorImpExp = '2';
        } else {
            $indicadorImpExp = '0';
        }

        /*
         * Validaciones Campo Afiliado
         */
        switch ($datos["afiliado"]) {
            case '':
            case '0':
            case '2':
                $indicadorAfiliado = 'N';
                break;
            case '1':
            case '3':
            case '5':
                $indicadorAfiliado = 'S';
                break;
        }

        /*
         * Validaciones Campo Cantidad Establecimientos
         */

        /*
          if (isset($datos["establecimientos"])) {
          if (is_array($datos["establecimientos"])) {
          $CantEstablecimientos = count($datos["establecimientos"]);
          } else {
          $CantEstablecimientos = $datos["establecimientos"];
          }
          }
         */
        //2017-07-25 - WSI - Ajustado para que tome el valor de la captura en Cantidad de establecimientos 
        $CantEstablecimientos = '';
        if (isset($datos["cantest"])) {
            $CantEstablecimientos = trim($datos["cantest"]);
        }

        /*
         * Parámetros Información Financiera 
         */
        if (isset($datos["hf"])) {
            if (count($datos["hf"]) > 0) {
                $arrHistoriaFinanciera = $datos["hf"];
                //Obtiene año inicial de datos (últimos cinco años de información financiera)                
                $anoInicial = $datos["anodatos"] - 5;
                $anosReportados = array();
                $sec = 0;

                foreach ($arrHistoriaFinanciera as $value) {
                    if ($anoInicial < $value["anodatos"]) {

                        //Verifica que el año recorrido no fue reportado previamente y crear datos de control
                        if (!isset($anosReportados[$value["anodatos"]])) {
                            $anosReportados[$value["anodatos"]] = $sec;
                        }

                        //Si el año fue reportado previamente reutiliza la secuencia de años y actualiza valores
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $secBase = $sec;
                            $sec = $anosReportados[$value["anodatos"]];
                        }

                        $arrDatosInfoFinanciera[$sec]['ano_informacion_financiera'] = $value["anodatos"];
                        $arrDatosInfoFinanciera[$sec]['activo_corriente'] = $value["actcte"];
                        $arrDatosInfoFinanciera[$sec]['activo_no_corriente'] = $value["actnocte"];
                        $arrDatosInfoFinanciera[$sec]['activo_total'] = $value["acttot"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_corriente'] = $value["pascte"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_no_corriente'] = $value["paslar"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_total'] = $value["pastot"];
                        $arrDatosInfoFinanciera[$sec]['patrimonio_neto'] = $value["pattot"];
                        $arrDatosInfoFinanciera[$sec]['pasivo_mas_patrimonio'] = $value["paspat"];
                        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                            if ($datos["categoria"] == '1') {
                                $arrDatosInfoFinanciera[$sec]['balance_social'] = $value["balsoc"];
                            }
                        } else {
                            $arrDatosInfoFinanciera[$sec]['balance_social'] = '';
                        }
                        $arrDatosInfoFinanciera[$sec]['ingresos_actividad_ordinaria'] = $value["ingope"];
                        $arrDatosInfoFinanciera[$sec]['otros_ingresos'] = $value["ingnoope"];
                        $arrDatosInfoFinanciera[$sec]['costo_ventas'] = $value["cosven"];
                        $arrDatosInfoFinanciera[$sec]['gastos_operacionales'] = $value["gtoven"];
                        $arrDatosInfoFinanciera[$sec]['otros_gastos'] = $value["gtoadm"];
                        $arrDatosInfoFinanciera[$sec]['gastos_impuestos'] = $value["gasimp"];
                        $arrDatosInfoFinanciera[$sec]['utilidad_perdida_operacional'] = $value["utiope"];
                        $arrDatosInfoFinanciera[$sec]['resultado_del_periodo'] = $value["utinet"];
                        $arrDatosInfoFinanciera[$sec]['valor_est_suc_ag'] = $valorEstSucAg;

                        //Si el año de datos pertenece a una secuencia previa reasigna el valor de la secuencia base
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $sec = $secBase;
                        }
                        //Incrementa la secuencia
                        $sec++;
                    }
                }
                //Obtiene los valores recogidos en el arreglo e indexa el resultado 
                $arrDatosInfoFinanciera = array_values($arrDatosInfoFinanciera);
            }
        }


        /*
         * Parámetros Información Capitales 
         */

        if (isset($datos["capitales"])) {
            if (count($datos["capitales"]) > 0) {
                $arrHistoriaCapitales = $datos["capitales"];

                //Obtiene año inicial de datos (últimos cinco años de información Capitales)                
                $anoInicial = $datos["anodatos"] - 5;
                $anosReportados = array();
                $sec = 0;

                foreach ($arrHistoriaCapitales as $value) {
                    if ($anoInicial < $value["anodatos"]) {

                        //Verifica que el año recorrido no fue reportado previamente y crear datos de control
                        if (!isset($anosReportados[$value["anodatos"]])) {
                            $anosReportados[$value["anodatos"]] = $sec;
                        }

                        //Si el año fue reportado previamente reutiliza la secuencia de años y actualiza valores
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $secBase = $sec;
                            $sec = $anosReportados[$value["anodatos"]];
                        }

                        $arrDatosInfoCapitales[$sec]['fecha_modificacion_capital'] = $value["fechadatos"];

                        //REVISAR
                        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
                            if ($datos["categoria"] == '1') {
                                $arrDatosInfoCapitales[$sec]['patrimonio_esal'] = '';
                            }
                        } else {
                            $arrDatosInfoCapitales[$sec]['patrimonio_esal'] = '';
                        }

                        if ($datos["organizacion"] != '12' && $datos["organizacion"] != '14' && $datos["organizacion"] != '01' && $datos["organizacion"] != '02') {
                            $arrDatosInfoCapitales[$sec]['capital_social'] = $value["social"];
                            $arrDatosInfoCapitales[$sec]['capital_autorizado'] = $value["autorizado"];
                            $arrDatosInfoCapitales[$sec]['capital_suscrito'] = $value["suscrito"];
                            $arrDatosInfoCapitales[$sec]['capital_pagado'] = $value["pagado"];
                        }

                        if ($datos["organizacion"] == '09') {
                            $arrDatosInfoCapitales[$sec]['eat_aportes_laborales'] = $value["apolab"];
                            $arrDatosInfoCapitales[$sec]['eat_aportes_activos'] = $value["apoact"];
                            $arrDatosInfoCapitales[$sec]['eat_aportes_laborales_adicionales'] = $value["apolabadi"];
                            $arrDatosInfoCapitales[$sec]['eat_aportes_en_dinero'] = $value["apodin"];
                            $arrDatosInfoCapitales[$sec]['eat_total_aportes'] = $value["apolab"] + $value["apoact"] + $value["apolabadi"] + $value["apodin"];
                        }

                        //Si el año de datos pertenece a una secuencia previa reasigna el valor de la secuencia base
                        if (isset($anosReportados[$value["anodatos"]])) {
                            $sec = $secBase;
                        }
                        //Incrementa la secuencia
                        $sec++;
                    }
                }
                //Obtiene los valores recogidos en el arreglo e indexa el resultado 
                $arrDatosInfoCapitales = array_values($arrDatosInfoCapitales);
            }
        }


        /*
         * Parámetros Información Adicional  //REVISAR 
         */
        /*
          $regInformacionAdicional['codigo_dato'] = '';
          $regInformacionAdicional['valor_dato'] = '';
          $arrDatosInfoAdicional[] = $regInformacionAdicional;
         */


        /*
         * Parámetros Propietarios de Establecimientos
         */
        if ($datos['organizacion'] == '02') {
            if (isset($datos["propietarios"])) {
                if (count($datos["propietarios"]) > 0) {
                    $arrPropietarios = $datos["propietarios"];
                    foreach ($arrPropietarios as $key => $value) {
                        $arrDatosPropietarios[$key - 1]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi($value['idtipoidentificacionpropietario']);
                        //

                        $NitPropietario = trim($value['identificacionpropietario']);
                        $longNitPropietario = strlen(trim($NitPropietario));

                        // Si es persona natural comprobada
                        if (isset($value['organizacionpropietario']) && $value['organizacionpropietario'] == '01') {
                            if ($value['idtipoidentificacionpropietario'] == '2') {
                                $numIdePropietario = substr($NitPropietario, 0, - 1);
                                $dvPropietario = substr($NitPropietario, - 1, 1);
                            } else {
                                $numIdePropietario = $NitPropietario;
                                $dvPropietario = '';
                            }
                        }

                        // Si es persona juridica comprobada
                        if (isset($value['organizacionpropietario']) && $value['organizacionpropietario'] > '02') {
                            if ($value['idtipoidentificacionpropietario'] != '2') {
                                $numIdePropietario = '';
                                $dvPropietario = '';
                            } else {
                                if ($longNitPropietario >= 10) {
                                    $numIdePropietario = substr($NitPropietario, 0, - 1);
                                    $dvPropietario = substr($NitPropietario, - 1, 1);
                                } else {
                                    $numIdePropietario = $NitPropietario;
                                    $dvPropietario = calcularDv($NitPropietario);
                                }
                            }
                        }

                        // Si no sabemos que es
                        if (!isset($value['organizacionpropietario']) || $value['organizacionpropietario'] == '') {
                            switch ($longNitPropietario) {
                                case 10:
                                    if ($value['idtipoidentificacionpropietario'] == '2') {
                                        $numIdePropietario = substr($NitPropietario, 0, - 1);
                                        $dvPropietario = substr($NitPropietario, - 1, 1);
                                    } else {
                                        $numIdePropietario = $NitPropietario;
                                        $dvPropietario = '';
                                    }
                                    break;
                                case 9:
                                    if ($value['idtipoidentificacionpropietario'] == '2') {
                                        $numIdePropietario = $NitPropietario;
                                        $dvPropietario = \funcionesGenerales::calcularDv($NitPropietario);
                                    } else {
                                        $numIdePropietario = $NitPropietario;
                                        $dvPropietario = '';
                                    }
                                    break;
                                default:
                                    $numIdePropietario = $NitPropietario;
                                    $dvPropietario = '';
                                    break;
                            }
                        }


                        //WSIERRA 2018-09-11 se reemplaza Cero por $key - 1
                        $arrDatosPropietarios[$key - 1]['numero_identificacion_propietario'] = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
                        $arrDatosPropietarios[$key - 1]['digito_verificacion_propietario'] = $dvPropietario;

                        $arrDatosPropietarios[$key - 1]['codigo_camara_propietario'] = trim($value['camarapropietario']);
                        $arrDatosPropietarios[$key - 1]['matricula_propietario'] = homologacionMatriculaRUESApi($value['matriculapropietario']);
                    }
                }
            }
        }

        /*
         * Parámetros Propietarios de Sucursales y agencias  
         */
        if (($datos['categoria'] == '2') || ($datos['categoria'] == '3')) {
            $arrDatosPropietarios[0]['codigo_clase_identificacion_propietario'] = homologacionTipoIdentificacionRUESApi('2');

            $NitPropietario = trim($datos["cpnumnit"]);
            $longNitPropietario = strlen(trim($NitPropietario));


            switch ($longNitPropietario) {
                case 10:
                    $numIdePropietario = substr($NitPropietario, 0, - 1);
                    $dvPropietario = substr($NitPropietario, - 1, 1);
                    break;
                case 9:
                    $numIdePropietario = $NitPropietario;
                    $dvPropietario = \funcionesGenerales::calcularDv($NitPropietario);
                    break;
                default:
                    $numIdePropietario = '';
                    $dvPropietario = '';
                    break;
            }

            $arrDatosPropietarios[0]['numero_identificacion_propietario'] = str_pad(trim($numIdePropietario), 14, "0", STR_PAD_LEFT);
            $arrDatosPropietarios[0]['digito_verificacion_propietario'] = $dvPropietario;
            $arrDatosPropietarios[0]['codigo_camara_propietario'] = trim($datos['cpcodcam']);
            $arrDatosPropietarios[0]['matricula_propietario'] = homologacionMatriculaRUESApi($datos['cpnummat']);
        }

        /*
         * Parámetros Vinculos 
         */
        if (isset($datos["vinculos"])) {
            if (count($datos["vinculos"]) > 0) {
                $arrVinculos = $datos["vinculos"];
                foreach ($arrVinculos as $key => $value) {
                    $arrDatosVinculos[$key - 1]['tipo_identificacion'] = homologacionTipoIdentificacionRUESApi($value['idtipoidentificacionotros']);
                    $arrDatosVinculos[$key - 1]['numero_identificacion'] = str_pad(trim($value['identificacionotros']), 14, "0", STR_PAD_LEFT);
                    $arrDatosVinculos[$key - 1]['nombre'] = $value["nombreotros"];
                    $arrDatosVinculos[$key - 1]['detalle_vinculos'][0] = array('codigo_tipo_vinculo' => homologacionCodigoVinculoRUESApi($datos["organizacion"], $value['vinculootros']));
                }
            }
        }

        $servsmat = array();
        $servsren = array();
        $servsafi = array();
        $servsben = array();
        $consulta = "";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_servicios', "tipoingreso IN ('02','03','13','31','85')", "idservicio");
        foreach ($temx as $tx) {
            if ($tx["tipoingreso"] == '02') {
                $servsmat[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '03' || $tx["tipoingreso"] == '13') {
                $servsren[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '31') {
                $servsafi[$tx["idservicio"]] = $tx["idservicio"];
            }
            if ($tx["tipoingreso"] == '85') {
                $servsben[$tx["idservicio"]] = $tx["idservicio"];
            }

            if ($consulta != '') {
                $consulta .= ",";
            }
            $consulta .= "'" . $tx["idservicio"] . "'";
        }

        //
        $camposRequeridos = array('anorenovacion', 'servicio', 'fecoperacion', 'base', 'valor');

        $res = retornarRegistrosMysqliApi($mysqli, "mreg_est_recibos", "matricula='" . $datos["matricula"] . "' and servicio IN (" . $consulta . ")", "fecoperacion desc", '', '', $camposRequeridos);
        $valorPagadoMatricula = '';
        $valorPagadoRenovacion = '';

        if ($res && !empty($res)) {
            $sech = 0;

            foreach ($res as $rs) {
                $incluir = 'no';
                if ($rs["ctranulacion"] != '1' && $rs["ctranulacion"] != '2') {
                    if (substr($rs["numerorecibo"], 0, 1) == 'R' || substr($rs["numerorecibo"], 0, 1) == 'S') {
                        $incluir = 'si';
                    }
                }

                //
                if ($incluir == 'si') {
                    if ($rs["valor"] == 0) {
                        $incluir = 'no';
                    }
                }

                if ($incluir == 'si') {
                    $ctp = '';
                    $arp = '';
                    if (isset($servsmat[$rs["servicio"]])) {
                        $ctp = '01';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                        if ($valorPagadoMatricula == '') {
                            $valorPagadoMatricula = $rs["valor"];
                        }
                    }
                    if (isset($servsren[$rs["servicio"]])) {
                        if ($rs["fecharenovacionaplicable"] != '') {
                            $fren = $rs["fecharenovacionaplicable"];
                        } else {
                            $fren = $rs["fecoperacion"];
                        }
                        $ctp = '02';
                        $arp = $rs["anorenovacion"];
                        if ($rs["servicio"] == "00000510") {
                            $arp = substr($fren, 0, 4);
                        }
                        if ($rs["servicio"] == "00000710") {
                            $arp = '';
                        }
                        if ($valorPagadoRenovacion == '') {
                            $valorPagadoRenovacion = $rs["valor"];
                        }
                    }
                    if (isset($servsafi[$rs["servicio"]])) {
                        $ctp = '03';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                    }
                    if (isset($servsben[$rs["servicio"]])) {
                        $ctp = '04';
                        $arp = substr($rs["fecoperacion"], 0, 4);
                    }

                    //
                    if ($ctp != '') {
                        if ($rs["fecharenovacionaplicable"] != '') {
                            $fren = $rs["fecharenovacionaplicable"];
                        } else {
                            $fren = $rs["fecoperacion"];
                        }
                        $arrDatosHistoricoPagos[$sech]['codigo_tipo_pago'] = $ctp;
                        $arrDatosHistoricoPagos[$sech]['ano'] = $arp;
                        $arrDatosHistoricoPagos[$sech]['fecha'] = $fren;
                        $arrDatosHistoricoPagos[$sech]['valor_base'] = $rs["base"];
                        $arrDatosHistoricoPagos[$sech]['valor_pagado'] = $rs["valor"];
                        $sech++;
                    }
                }
            }
        }



        /*
         * Validaciones Grupo NIIF
         */
        $indicadorGrupoNiif = '';
        if (trim($datos["gruponiif"]) == '') {
            $indicadorGrupoNiif = '0';
        } else {
            $indicadorGrupoNiif = \funcionesGenerales::retornarGrupoNiifFormulario($mysqli, $datos["gruponiif"]);
        }


        /*
         * Validaciones Campo Objeto Social
         */
        if (isset($datos["crtsii"]["0740"])) {
            $objetoSocial = $datos["crtsii"]["0740"];
        } else {
            if (isset($datos["crt"]["0740"])) {
                $objetoSocial = $datos["crt"]["0740"];
            } else {
                $objetoSocial = '';
            }
        }

        /*
         * Validaciones Campo Juegos Suerte Azar (CIIU=R9200) 
         */
        $indicadorJuegosSuerteAzar = 'N';
        if (isset($datos["ciius"])) {
            if (in_array("R9200", $datos["ciius"])) {
                $indicadorJuegosSuerteAzar = 'S';
            }
        }

        /*
         * Validaciones Campo Transporte Carga
         */
        $indicadorTransporteCarga = 'N';
        if (isset($datos["inscripciones"])) {
            foreach ($datos["inscripciones"] as $key => $value) {
                if ($value['acto'] == '0800' || $value['acto'] == '0801') {
                    $indicadorTransporteCarga = 'S';
                }
            }
        }

        /*
         * Validaciones Campo Facultades 
         * Obtiene de tabla mreg_certificas_sii los certificas 1300 y 1500
         */
        $facultades = '';
        if (isset($datos["crtsii"]["1300"])) {
            $facultades = $datos["crtsii"]["1300"];
            if (isset($datos["crtsii"]["1500"])) {
                $facultades .= " | " . $datos["crtsii"]["1500"];
            }
        } else {
            if (isset($datos["crt"]["1300"])) {
                $facultades = $datos["crt"]["1300"];
                if (isset($datos["crt"]["1500"])) {
                    $facultades .= " | " . $datos["crt"]["1500"];
                }
            }
        }


        /*
         * Validaciones Zona Notificación Comercial
         */
        $indicadorZonaNotificacionComercial = '';
        if ($datos["codigozonacom"] == 'U') {
            $indicadorZonaNotificacionComercial = 1; //URBANA
        }
        if ($datos["codigozonacom"] == 'R') {
            $indicadorZonaNotificacionComercial = 2; //RURAL
        }

        /*
         * Validaciones Zona Notificación Judicial
         */
        $indicadorZonaNotificacionJudicial = '';
        if ($datos["codigozonanot"] == 'U') {
            $indicadorZonaNotificacionJudicial = 1; //URBANA
        }
        if ($datos["codigozonanot"] == 'R') {
            $indicadorZonaNotificacionJudicial = 2; //RURAL
        }

        /*
         * Validaciones Notificación Email
         */
        $valorCtrmennot = substr($datos["ctrmennot"], 0, 1);
        $indicadorNotificacionEmail = homologarBoleeano($valorCtrmennot);


        /*
         * Validaciones Etnia
         */
        $cualEtnia = '';
        $indicadorEtnia = homologarBoleeano($datos["ctresaetnia"]);
        if ($indicadorEtnia == 'S') {
            $cualEtnia = $datos["ctresacualetnia"];
        }

        /*
         * Validaciones Reinsertado
         */

        $cualReinsertado = '';
        $indicadorReinsertado = homologarBoleeano($datos["ctresadespvictreins"]);
        if ($indicadorReinsertado == 'S') {
            $cualReinsertado = $datos["ctresacualdespvictreins"];
        }

        /*
         * Validaciones Tipo Propiedad - Establecimientos
         */
        $indicadorTipoPropiedad = '';
        if ($datos['organizacion'] == '02') {
            if ($datos["tipolocal"] == '1') {
                $indicadorTipoPropiedad = 1; //PROPIO
            }
            if ($datos["tipolocal"] == '0') {
                $indicadorTipoPropiedad = 2; //AJENO
            }
        }


        /*
         * Parámetros Finales web service RR41N 
         */

        $parametros['numero_interno'] = '';
        $parametros['usuario'] = $_SESSION["generales"]["codigousuario"];
        $parametros['codigo_camara'] = $_SESSION["generales"]["codigoempresa"];
        $parametros['matricula'] = homologacionMatriculaRUESApi($datos["matricula"]);
        $parametros['inscripcion_proponente'] = str_pad($datos["proponente"], 12, "0", STR_PAD_LEFT);
        $parametros['razon_social'] = $datos["nombre"];

        $parametros['sigla'] = $datos["sigla"];
        //Solo Personas Naturales (incluido por Circular 004 del 2017)
        if ($datos["organizacion"] == '01') {
            $parametros['primer_apellido'] = $datos["ape1"];
            $parametros['segundo_apellido'] = $datos["ape2"];
            $parametros['primer_nombre'] = $datos["nom1"];
            $parametros['segundo_nombre'] = $datos["nom2"];
        }
        $parametros['codigo_clase_identificacion'] = homologacionTipoIdentificacionRUESApi($tipoIdentificacion);
        $parametros['numero_identificacion'] = str_pad($numIdentificacion, 14, "0", STR_PAD_LEFT);
        $parametros['digito_verificacion'] = $dv;

        //Solo Personas Naturales (incluido por Circular 004 del 2017)
        if ($datos["organizacion"] == '01') {
            $parametros['fecha_expedicion'] = $datos["fecexpdoc"];
            $parametros['lugar_expedicion'] = $datos["idmunidoc"];
            $parametros['pais_expedicion'] = $datos["paisexpdoc"];
        }

        //Solo para Tipo Identificación = Cedulas extranjería o Pasaporte
        if (ltrim($tipoIdentificacion, "0") == '3' || ltrim($tipoIdentificacion, "0") == '5') {
            $parametros['num_id_trib_ep'] = $datos["idetripaiori"];
            $parametros['pais_origen'] = $datos["paiori"];
            $parametros['num_id_trib_ep'] = $datos["idetriextep"];
        }

        $parametros['direccion_comercial'] = $datos["dircom"];
        $parametros['codigo_ubicacion_empresa'] = $datos["ctrubi"];
        $parametros['codigo_zona_comercial'] = $indicadorZonaNotificacionComercial; //1 o 2
        $parametros['codigo_postal_comercial'] = $datos["codigopostalcom"];
        $parametros['municipio_comercial'] = empty($datos["muncom"]) ? '99999' : $datos["muncom"];
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            //2017-11-09 WEYMER - Incluye campo barrio_comercial 
            $parametros['barrio_comercial'] = retornarNombreBarrioMysqliApi($mysqli, $datos["muncom"], $datos["barriocom"]);
        }
        $parametros['telefono_comercial_1'] = $datos["telcom1"];
        $parametros['telefono_comercial_2'] = $datos["telcom2"];
        $parametros['telefono_comercial_3'] = $datos["celcom"];
        $parametros['correo_electronico_comercial'] = $datos["emailcom"];
        $parametros['direccion_fiscal'] = $datos["dirnot"];
        $parametros['codigo_zona_fiscal'] = $indicadorZonaNotificacionJudicial; //1 o 2
        $parametros['codigo_postal_fiscal'] = $datos["codigopostalnot"];
        $parametros['municipio_fiscal'] = empty($datos["munnot"]) ? '99999' : $datos["munnot"];
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            //2017-11-09 WEYMER - Incluye campo barrio_fiscal 
            $parametros['barrio_fiscal'] = retornarNombreBarrioMysqliApi($mysqli, $datos["munnot"], $datos["barrionot"]);
        }
        $parametros['telefono_fiscal_1'] = $datos["telnot"];
        $parametros['telefono_fiscal_2'] = $datos["telnot2"];
        $parametros['telefono_fiscal_3'] = $datos["celnot"];
        $parametros['correo_electronico_fiscal'] = $datos["emailnot"];
        $parametros['autorizacion_envio_correo_electronico'] = $indicadorNotificacionEmail; //S o N
        $parametros['objeto_social'] = base64_encode($objetoSocial);
        $parametros['cod_ciiu_act_econ_pri'] = empty($datos["ciius"][1]) ? '9999' : substr($datos["ciius"][1], 1);
        $parametros['fecha_inicio_act_econ_pri'] = $datos["feciniact1"]; //YYYMMDD
        $parametros['cod_ciiu_act_econ_sec'] = empty($datos["ciius"][2]) ? '' : substr($datos["ciius"][2], 1);
        $parametros['fecha_inicio_act_econ_sec'] = $datos["feciniact2"]; //YYYMMDD
        $parametros['ciiu3'] = empty($datos["ciius"][3]) ? '' : substr($datos["ciius"][3], 1);
        $parametros['ciiu4'] = empty($datos["ciius"][4]) ? '' : substr($datos["ciius"][4], 1);
        $parametros['clasificacion_imp_exp'] = $indicadorImpExp;
        $parametros['empresa_familiar'] = homologarBoleeano($datos["empresafamiliar"]);
        $parametros['proceso_innovacion'] = homologarBoleeano($datos["procesosinnovacion"]);
        $parametros['fecha_matricula'] = $fechaMatricula; //YYYMMDD
        $parametros['fecha_constitucion'] = $fechaConstitucion; //YYYMMDD
        $parametros['fecha_renovacion'] = $fechaRenovacion; //YYYMMDD
        $parametros['ultimo_ano_renovado'] = $ultAnoRenovado; //YYYMMDD
        $parametros['valor_pagado_renovacion'] = $valorPagadoRenovacion;
        $parametros['valor_pagado_matricula'] = $valorPagadoMatricula;
        $parametros['fecha_vigencia'] = $fechaVigencia; //YYYMMDD
        $parametros['fecha_cancelacion'] = $fechaCancelacion; //YYYMMDD

        $parametros['codigo_motivo_cancelacion'] = homologacionMotivoCancelacionRUESApi($indicadorMotivoCancelacion); //HOMOLOGACION BAS_MOTIVOS_CANCELACION

        $parametros['codigo_tipo_sociedad'] = homologacionSociedadRUESApi($datos['organizacion'], $datos['claseespesadl'], $datos["ciius"]); //HOMOLOGACION BAS_TIPO_SOCIEDAD

        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            $parametros['codigo_organizacion_juridica'] = homologacionOrganizacionEsadlRUESApi($mysqli, $datos['claseespesadl']);
        } else {
            $parametros['codigo_organizacion_juridica'] = homologacionOrganizacionRUESApi($datos['organizacion'], $datos['clasegenesadl'], $datos['claseespesadl'], $datos['claseeconsoli']);
        }
        $parametros['codigo_categoria_matricula'] = homologacionCategoriaRUESApi($datos['organizacion'], $datos['categoria']); //HOMOLOGACION BAS_CATEGORIA_MATRICULA
        $parametros['indicador_vendedor_juegos_suerte_azar'] = $indicadorJuegosSuerteAzar;
        $parametros['indicador_transporte_de_carga'] = $indicadorTransporteCarga;
        $parametros['afiliado'] = $indicadorAfiliado;
        $parametros['url'] = filter_var($datos["urlcom"], FILTER_VALIDATE_URL) ? $datos["urlcom"] : '';
        $parametros['codigo_estado_matricula'] = homologacionEstadoMatriculaRUESApi($datos["estadomatricula"]); //HOMOLOGACION BAS_ESTADO_MATRICULA
        $parametros['codigo_estado_persona_juridica'] = $datos["estadocapturado"];
        $parametros['empleados'] = empty($datos["personal"]) ? '0' : $datos["personal"];
        $parametros['porcentaje_empleados_temporales'] = $datos["personaltemp"]; //%
        $parametros['codigo_tamano_empresa'] = str_pad($datos["tamanoempresa"], 2, "0", STR_PAD_LEFT); //HOMOLOGACION BAS_TAMANO_EMPRESA 
        $parametros['codigo_estado_liquidacion'] = homologacionEstadoLiquidacionRUESApi($datos["estadotipoliquidacion"]); //REVISAR HOMOLOGACION BAS_CODIGOS_LIQUIDACION
        $parametros['latitud'] = $datos['latitud'];
        $parametros['longitud'] = $datos['longitud'];
        $parametros['informacion_financiera'] = $arrDatosInfoFinanciera;
        $parametros['informacion_capitales'] = $arrDatosInfoCapitales;
        $parametros['grupo_niif'] = $indicadorGrupoNiif; // 0 - 7
        $parametros['codigo_partidas_conciliatorias'] = $datos["niifconciliacion"];
        $parametros['capital_social_nacional_publico'] = $datos["cap_porcnalpub"];
        $parametros['capital_social_nacional_privado'] = $datos["cap_porcnalpri"];
        $parametros['capital_social_extranjero_publico'] = $datos["cap_porcextpub"];
        $parametros['capital_social_extranjero_privado'] = $datos["cap_porcextpri"];
        $parametros['indicador_beneficio_ley1429'] = homologarBoleeano($datos["art7"]);
        $parametros['indicador_beneficio_ley1780'] = homologarBoleeano($datos["benley1780"]);
        $parametros['indicador_aportante_seguridad_social'] = $datos["aportantesegsocial"];

        if ($datos["aportantesegsocial"] == 'S') {
            $parametros['tipo_aportante_seguridad_social'] = $datos["tipoaportantesegsocial"]; //REVISAR HOMOLOGACION BAS_TIPO_APORTANTE
        }

        // 2019 12 27 - JINT - Se incluyen los datos nuevos del formulario
        // cantidadmujeres
        // cantidadmujerescargosdirectivos
        // ctrbic
        // participacionmujeres
        // ciiu de mas ingresos
        // valor de los ingresos
        // genero
        // revisar con weymer apenas se pueda
        /*
          if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
          if ($datos["ctrbic"] != '') {
          $adi = array();
          $adi[]["codigo_dato"] = '1101';
          $adi[]["codigo_dato"] = $datos["ctrbic"];
          $arrDatosInfoAdicional[] = $adi;
          }
          }
          if ($datos["organizacion"] == '01') {
          if ($datos["sexo"] != '') {
          $adi = array();
          $adi[]["codigo_dato"] = '1102';
          $adi[]["codigo_dato"] = $datos["sexo"];
          $arrDatosInfoAdicional[] = $adi;
          }
          }
          if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
          if ($datos["fecdatoscap"] != '') {
          $adi = array();
          $adi[]["codigo_dato"] = '1103';
          $adi[]["codigo_dato"] = $datos["fecdatoscap"];
          $arrDatosInfoAdicional[] = $adi;
          }
          }
          if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
          $adi = array();
          $adi[]["codigo_dato"] = '1104';
          $adi[]["codigo_dato"] = $datos["participacionmujeres"];
          $arrDatosInfoAdicional[] = $adi;
          }
          if ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14') {
          $adi = array();
          $adi[]["codigo_dato"] = '1105';
          $adi[]["codigo_dato"] = $datos["cantidadmujerescargosdirectivos"];
          $arrDatosInfoAdicional[] = $adi;
          }
          if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14')) {
          $adi = array();
          $adi[]["codigo_dato"] = '1106';
          $adi[]["codigo_dato"] = $datos["cantidadmujeres"];
          $arrDatosInfoAdicional[] = $adi;
          }
          if ($datos["organizacion"] == '01' || ($datos["organizacion"] > '02' && $datos["categoria"] == '1' && $datos["organizacion"] != '12' && $datos["organizacion"] != '14')) {
          if ($datos["ciiutamanoempresarial"] != '') {
          $adi = array();
          $adi[]["codigo_dato"] = '1107';
          $adi[]["codigo_dato"] = $datos["ciiutamanoempresarial"];
          $arrDatosInfoAdicional[] = $adi;
          }
          }
         */

        $parametros['informacion_adicional'] = $arrDatosInfoAdicional;

        //
        $parametros['cantidad_establecimientos'] = $CantEstablecimientos;
        //Solo Establecimientos de comercio
        if ($datos["organizacion"] == '02') {
            $parametros['tipo_propietario'] = homologacionTipoPropietarioRUESApi($datos["tipopropiedad"]);
            if (TIPO_AMBIENTE == 'PRUEBAS') {
                //2017-11-09 WEYMER - Incluye campo codigo_tipo_local 
                $parametros['codigo_tipo_local'] = $indicadorTipoPropiedad;
            }
        }

        $parametros['grupo_empresarial_tipo'] = $datos["tipogruemp"];
        $parametros['grupo_empresarial_nombre'] = $datos["nombregruemp"];
        $parametros['datos_propietarios'] = $arrDatosPropietarios;
        $parametros['facultades'] = base64_encode($facultades);
        $parametros['vinculos'] = $arrDatosVinculos;
        //Solo ESADL
        if ($datos["organizacion"] == '12' || $datos["organizacion"] == '14') {
            $parametros['esal_numero_asociados'] = $datos["ctresacntasociados"];
            $parametros['esal_numero_mujeres'] = $datos["ctresacntmujeres"];
            $parametros['esal_numero_hombres'] = $datos["ctresacnthombres"];
            $parametros['esal_indicador_pertenencia_gremio'] = homologarBoleeano($datos["ctresapertgremio"]);
            $parametros['esal_nombre_gremio'] = $datos["ctresagremio"];
            $parametros['esal_entidad_acreditada'] = $datos["ctresaacredita"];
            $parametros['esal_entidad_ivc'] = $datos["ctresaivc"];
            $parametros['esal_ha_remitido_info_ivc'] = homologarBoleeano($datos["ctresainfoivc"]);
            $parametros['esal_autorizacion_registro'] = homologarBoleeano($datos["ctresaautregistro"]);
            $parametros['esal_entidad_autoriza'] = $datos["ctresaentautoriza"];
            $parametros['esal_codigo_naturaleza'] = homologacionNaturalezaEsadlRUESApi($datos['organizacion'], $datos["ctresacodnat"]);
            $parametros['esal_codigo_tipo_entidad'] = homologacionOrganizacionRUESApi($datos['organizacion'], $datos['clasegenesadl'], $datos['claseespesadl'], $datos['claseeconsoli']);
            $parametros['esal_ed_discapacidad'] = $datos["ctresadiscap"];
            $parametros['esal_ed_etnia'] = $indicadorEtnia;
            $parametros['esal_ed_etnia_cual'] = $cualEtnia;
            $parametros['esal_ed_lgbti'] = $datos["ctresalgbti"];
            $parametros['esal_ed_desp_vict_reins'] = $indicadorReinsertado;
            $parametros['esal_ed_desp_vict_reins_cual'] = $cualReinsertado;
            $parametros['esal_indicador_gestion'] = homologarBoleeano($datos["ctresaindgest"]);
        }
        $parametros['historico_pagos'] = $arrDatosHistoricoPagos;

        /* Eliminar campos vacíos de los parámetros */
        foreach ($parametros as $claveRaiz => $valorRaiz) {

            if (!is_array($valorRaiz)) {
                if (trim($valorRaiz) == '') {
                    unset($parametros[$claveRaiz]);
                }
            } else {
                foreach ($valorRaiz as $clave1 => $valor1) {
                    foreach ($valor1 as $clave2 => $valor2) {
                        if (!is_array($valor2)) {
                            if (trim($valor2) == '') {
                                unset($parametros[$claveRaiz][$clave1][$clave2]);
                            }
                        }
                    }
                }
            }
        }

        unset($arrDatosInfoFinanciera);
        unset($arrDatosInfoCapitales);
        unset($arrDatosInfoAdicional);
        unset($arrDatosPropietarios);
        unset($arrDatosVinculos);
        unset($arrDatosHistoricoPagos);

        $cadCtr = '';
        if (TIPO_AMBIENTE == 'PRUEBAS') {
            $cadCtr = date("His");
        }

        $parametros['hash_control'] = md5(json_encode($parametros)) . $cadCtr;

        //Asignado el número interno al final de los parámetros, para evitar que el hash sea modificado. 
        $parametros['numero_interno'] = str_pad(date('Ymd') . date('His') . '000' . $_SESSION ["generales"] ["codigoempresa"] . $_SESSION ["generales"] ["codigoempresa"], 29, "0", STR_PAD_RIGHT);

        //
        $mysqli->close();

        //
        return $parametros;
    }

}

function homologarBoleeano($valor) {

    $valor = trim($valor);

    switch ($valor) {
        case 'S':
        case '1':
        case 'SI':
            $resultado = 'S';
            break;
        case 'NO':
        case 'N':
        case '0':
            $resultado = 'N';
            break;
        default:
            $resultado = '';
            break;
    }

    return $resultado;
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

function objectToArrayRR41N($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArrayRR41N', $object);
}

?>