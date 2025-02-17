<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarRelacionTramites {

    public function consultarRelacionTramites(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        $resError = set_error_handler('myErrorHandler');


        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cantidad"] = 0;
        $_SESSION['jsonsalida']['registros'] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        $api->validarParametro("semilla", false);
        $api->validarParametro("cantidad", true);

        //
        if (!$api->validarToken('consultarRelacionTramites', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //WSIERRA 2018-01-28 Ajuste del paginador
        $_SESSION["entrada"]["semilla"] = intval($_SESSION["entrada"]["semilla"]);
        $consulta["retornar"] = intval($_SESSION["entrada"]["cantidad"]);
        $consulta["offset"] = 0;

        if ($_SESSION["entrada"]["semilla"] != '0') {
            $consulta["offset"] = $_SESSION["entrada"]["semilla"] * $consulta["retornar"];
        }

        // ********************************************************************** //
        // Si tipo de usuario es 00 (públicos) o 06 (Externos)
        // Busca la información en mreg_liquidacion
        // ********************************************************************** // 
        $_SESSION["entrada"]["tipousuario"] = sprintf("%02s", $_SESSION["entrada"]["tipousuario"]);
        if ($_SESSION["entrada"]["tipousuario"] == '00' || $_SESSION["entrada"]["tipousuario"] == '06') {
            if (trim((string)$_SESSION["entrada"]["emailcontrol"]) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Correo electrónico es obligatorio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', "emailcontrol='" . $_SESSION["entrada"]["emailcontrol"] . "'", "fecha desc", "idliquidacion,numerorecuperacion,tipotramite,fecha,idestado,identificacioncliente,nombrecliente,idproponentebase,idmatriculabase", $consulta["offset"], $consulta["retornar"]);

            if ($temx && !empty($temx)) {
                $_SESSION["jsonsalida"]["cantidad"] = count($temx);
                foreach ($temx as $t) {
                    $registro = array();
                    $registro["numerorecuperacion"] = $t["numerorecuperacion"];
                    $registro["fecha"] = $t["fecha"];
                    $registro["idliquidacion"] = $t["idliquidacion"];
                    // $registro["tipotramite"] = \funcionesSii2::mostrarFecha($t["fecha"]).' / '.strtoupper($t["numerorecuperacion"]) . ' - ' . $t["tipotramite"];
                    $registro["tipotramite"] = strtoupper($t["numerorecuperacion"]) . ' - ' . $t["tipotramite"];
                    $registro["idestado"] = $t["idestado"];
                    $registro["txtestado"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacionestados', "id='" . $t["idestado"] . "'", "descripcion");
                    $registro["identificacion"] = $t["identificacioncliente"];
                    $registro["nombre"] = $t["nombrecliente"];
                    $registro["matricula"] = isset($t["idmatriculabase"]) ? $t["idmatriculabase"] : '';
                    $registro["proponente"] = isset($t["idproponentebase"]) ? $t["idproponentebase"] : '';
                    $registro["links"] = array();

                    // Si está pagado
                    if ($t["idestado"] == '07' || $t["idestado"] == '09') {
                        $arr = array();
                        $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                        $arr["idusuario"] = 'USUPUBXX';
                        $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                        $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                        $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                        $arr["fechainvocacion"] = date("Ymd");
                        $arr["horainvocacion"] = date("His");
                        $arr["script"] = 'mregSoportesPago.php';
                        $arr["accion"] = 'mostrarsoportes';
                        $arr["parametros"] = array();
                        $arr["parametros"]["liquidacion"] = $t["idliquidacion"];
                        $json = json_encode($arr);
                        $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                        $link = array();
                        $link["aplicacion"] = 'sii1';
                        $link["titulo"] = 'Ver soportes';
                        $link["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                        $registro["links"][] = $link;
                        $link["aplicacion"] = 'sii1';
                        $link["titulo"] = 'Consultar estado trámite';
                        $link["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                        $registro["links"][] = $link;
                    } else {
                        // Si no está anulado
                        if ($t["idestado"] != '99') {
                            $tt = retornarRegistroMysqliApi($mysqli, "bas_tipotramites", "id='" . $t["tipotramite"] . "'");
                            if ($tt && !empty($tt)) {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = 'USUPUBXX';
                                $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                                $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                                $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = $tt["script"];
                                $arr["accion"] = 'validarseleccionrecuperada';
                                $arr["parametros"] = array();
                                $arr["parametros"]["_numrec"] = $t["numerorecuperacion"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Recuperar trámite';
                                $link["href"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }
                        }
                    }

                    //
                    $_SESSION['jsonsalida']['registros'][] = $registro;
                }
            }
        }

        // ********************************************************************** //
        // Si tipo de usuario es 00 (públicos) o 06 (Externos)
        // Busca la información en mreg_liquidacion
        // ********************************************************************** // 
        if ($_SESSION["entrada"]["tipousuario"] != '00' && $_SESSION["entrada"]["tipousuario"] != '06') {
            if (trim($_SESSION["entrada"]["idusuario"]) == '') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Código del usuario es obligatorio';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }

            //
            if ($_SESSION["entrada"]["tipousuario"] == '01') {
                $temy = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "estadofinal IN ('01','02','03','04','09','11','13','14','22','23','34','35','38') and fechaestadofinal>'20161231'", "fecharadicacion asc", "*", $consulta["offset"], $consulta["retornar"]);
            } else {
                $usu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
                $opefin = "'" . $_SESSION["entrada"]["idusuario"] . "'";
                if (trim((string)$usu["idcodigosirepcaja"]) != '') {
                    $opefin .= ",'" . $usu["idcodigosirepcaja"] . "'";
                }
                if (trim((string)$usu["idcodigosirepdigitacion"]) != '') {
                    $opefin .= ",'" . $usu["idcodigosirepdigitacion"] . "'";
                }
                if (trim((string)$usu["idcodigosirepregistro"]) != '') {
                    $opefin .= ",'" . $usu["idcodigosirepregistro"] . "'";
                }

                $temy = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "operadorfinal IN(" . $opefin . ") and estadofinal IN ('01','02','03','04','09','11','13','14','22','23','34','35','38') and fechaestadofinal>'20161231'", "fecharadicacion asc", "*", $consulta["offset"], $consulta["retornar"]);
            }

            //
            if ($temy && !empty($temy)) {
                $_SESSION["jsonsalida"]["cantidad"] = count($temy);
                foreach ($temy as $t) {
                    // Localizar ruta
                    $tr = retornarRegistroMysqliApi($mysqli, "mreg_codrutas", "id='" . $t["actoreparto"] . "'");

                    // Carga el registro
                    $registro = array();
                    $registro["codigobarras"] = trim((string)$t["codigobarras"]);
                    $registro["fecharadicacion"] = trim((string)$t["fecharadicacion"]);
                    $registro["operacion"] = trim((string)$t["operacion"]);
                    $registro["recibo"] = trim((string)$t["recibo"]);
                    $registro["operador"] = trim((string)$t["operadorfinal"]);
                    $registro["matricula"] = trim((string)$t["matricula"]);
                    $registro["proponente"] = trim((string)$t["proponente"]);
                    $registro["tipoidentificacion"] = trim((string)$t["idclase"]);
                    $registro["identificacion"] = trim((string)$t["numid"]);
                    $registro["nombre"] = trim((string)$t["nombre"]);
                    $registro["idestado"] = trim((string)$t["estadofinal"]);
                    $registro["txtestado"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "id='" . $t["estadofinal"] . "'", "descripcion");
                    if (trim($registro["txtestado"]) == '') {
                        $registro["txtestado"] = retornarRegistroMysqliApi($mysqli, 'mreg_codestados_rutaproponentes', "id='" . $t["estadofinal"] . "'", "descripcion");
                    }
                    $registro["fechaestadofinal"] = trim((string)$t["fechaestadofinal"]);
                    $registro["horaestadofinal"] = trim((string)$t["horaestadofinal"]);
                    $registro["actoreparto"] = trim((string)$t["actoreparto"]);
                    $registro["txtactoreparto"] = trim((string)$tr["descripcion"]);
                    $registro["links"] = [];

                    // Arma los enlaces
                    switch ($t["estadofinal"]) {
                        case "01" : // En caso de escaneo
                        case "02" : // En caso de escaneo
                        case "03" : // En caso de escaneo
                        case "09" : // Reingresado
                            //
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Escanear
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregExpedienteGrafico.php';
                            $arr["accion"] = 'buscarcodigobarras';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $arr["parametros"]["numerooperacion"] = $t["operacion"];
                            $arr["parametros"]["identificacion"] = $t["numid"];
                            $arr["parametros"]["matricula"] = $t["matricula"];
                            $arr["parametros"]["proponente"] = $t["proponente"];
                            $arr["parametros"]["nombre"] = $t["nombre"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Escanear o digitalizar';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Finalizar escaneo
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'finalizarescaneo';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finalizar digitalización y enrutar';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            //
                            break;

                        case "04" : // Estudio y registro - Mercantil / ESADL
                        case "11" : // Inscrito - Mercantil / ESADL
                        case "13" : // Enviado a registro - Proponentes
                        case "22" : // Inscrito - Proponentes
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Estudiar mercantil
                            if ($tr["tipo"] == 'ME' || $tr["tipo"] == 'ES') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregEstudiarMercantil.php';
                                $arr["accion"] = 'cargarcodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Estudiar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Estudiar proponente
                            if ($tr["tipo"] == 'PR') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregEstudiarProponentes.php';
                                $arr["accion"] = 'cargarcodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Estudiar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Devolver trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregDevolverTramite.php';
                            $arr["accion"] = 'cargarcodigobarras';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Devolver trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Finalizar estudio
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'finalizarestudio';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finaliar estudio';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Archivar trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'archivartramite';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Archivar trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;
                            break;


                        case "14" : // Digitación proponentes
                        case "23" : // Digitaciuón mercantil
                        case "24" : // Digitaciuón mercantil
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Digitar trámite mercantil
                            if ($tr["tipo"] == 'ME' || $tr["tipo"] == 'ES') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregMostrarBandejas.php';
                                $arr["accion"] = 'mostrardigitacion';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Digitar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Digitar trámite de proponentes
                            if ($tr["tipo"] == 'PR') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregMostrarBandejas.php';
                                $arr["accion"] = 'digitarformularioproponentes';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codbarras"] = $t["codigobarras"];
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Digitar trámite';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Finalizar digitación
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'finalizardigitacion';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finalizar digitación';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Archivar trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'archivartramite';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Archivar trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            //
                            break;

                        case "34" : // para firma
                        case "35" : // para firma
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Firmar inscripciones mercantil
                            if ($tr["tipo"] == 'ME' || $tr["tipo"] == 'ES') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregFirmaInscripciones.php';
                                $arr["accion"] = 'cargarrelacioncodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                                $arr["parametros"]["criterio"] = '1';
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Firmar inscripciones|';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Firmar inscripciones proponentes
                            if ($tr["tipo"] == 'PR') {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = '';
                                $arr["identificacioncontrol"] = '';
                                $arr["celularcontrol"] = '';
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = 'mregFirmaInscripciones.php';
                                $arr["accion"] = 'cargarrelacioncodigobarras';
                                $arr["parametros"] = array();
                                $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                                $arr["parametros"]["criterio"] = '2';
                                $json = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                                $link = array();
                                $link["aplicacion"] = 'sii1';
                                $link["titulo"] = 'Firmar inscripciones|';
                                $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $registro["links"][] = $link;
                            }

                            // Archivar trámite
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregMostrarBandejas.php';
                            $arr["accion"] = 'archivartramite';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codbarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Archivar trámite';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;
                            break;

                        case "38" : // en control de calidad
                            // Consultar ruta
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregConsultaRutaDocumentos.php';
                            $arr["accion"] = 'traerruta';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Ver ruta del documento';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;

                            // Finalizar control de calidad
                            $arr = array();
                            $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                            $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                            $arr["emailcontrol"] = '';
                            $arr["identificacioncontrol"] = '';
                            $arr["celularcontrol"] = '';
                            $arr["fechainvocacion"] = date("Ymd");
                            $arr["horainvocacion"] = date("His");
                            $arr["script"] = 'mregFinalizarControlCalidad.php';
                            $arr["accion"] = 'cargarcodigobarras';
                            $arr["parametros"] = array();
                            $arr["parametros"]["codigobarras"] = $t["codigobarras"];
                            $json = json_encode($arr);
                            $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($json, $claveEncriptacion));
                            $link = array();
                            $link["aplicacion"] = 'sii1';
                            $link["titulo"] = 'Finalizar control de calidad';
                            $link["enlace"] = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                            $registro["links"][] = $link;
                            break;
                    }

                    //
                    $_SESSION['jsonsalida']['registros'][] = $registro;
                }
            }
        }

        //
        $mysqli->close();


        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
