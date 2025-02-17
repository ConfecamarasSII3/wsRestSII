<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarExpedienteMercantil {

    public function consultarExpedienteMercantil(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

        $nameLog = 'consultarExpedienteMercantil_' . date ("Ymd");
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["nombre1"] = '';
        $_SESSION["jsonsalida"]["nombre2"] = '';
        $_SESSION["jsonsalida"]["apellido1"] = '';
        $_SESSION["jsonsalida"]["apellido2"] = '';
        $_SESSION["jsonsalida"]["sigla"] = '';
        $_SESSION["jsonsalida"]["idclase"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["genero"] = '';
        $_SESSION["jsonsalida"]["nit"] = '';
        $_SESSION["jsonsalida"]["genero"] = '';
        $_SESSION["jsonsalida"]["emprendimientosocial"] = '';
        $_SESSION["jsonsalida"]["organizacion"] = '';
        $_SESSION["jsonsalida"]["categoria"] = '';
        $_SESSION["jsonsalida"]["estado"] = '';
        $_SESSION["jsonsalida"]["fechamatricula"] = '';
        $_SESSION["jsonsalida"]["fecharenovacion"] = '';
        $_SESSION["jsonsalida"]["ultanorenovado"] = '';
        $_SESSION["jsonsalida"]["fechacancelacion"] = '';
        $_SESSION["jsonsalida"]["dircom"] = '';
        $_SESSION["jsonsalida"]["idbarriocom"] = '';
        $_SESSION["jsonsalida"]["barriocom"] = '';
        $_SESSION["jsonsalida"]["muncom"] = '';
        $_SESSION["jsonsalida"]["telcom1"] = '';
        $_SESSION["jsonsalida"]["telcom2"] = '';
        $_SESSION["jsonsalida"]["telcom3"] = '';
        $_SESSION["jsonsalida"]["emailcom"] = '';
        $_SESSION["jsonsalida"]["urlcom"] = '';
        $_SESSION["jsonsalida"]["dirnot"] = '';
        $_SESSION["jsonsalida"]["idbarrionot"] = '';
        $_SESSION["jsonsalida"]["barrionot"] = '';
        $_SESSION["jsonsalida"]["munnot"] = '';
        $_SESSION["jsonsalida"]["telnot1"] = '';
        $_SESSION["jsonsalida"]["telnot2"] = '';
        $_SESSION["jsonsalida"]["telnot3"] = '';
        $_SESSION["jsonsalida"]["emailnot"] = '';
        $_SESSION["jsonsalida"]["autorizacionemailsms"] = '';
        $_SESSION["jsonsalida"]["ciiu1"] = '';
        $_SESSION["jsonsalida"]["ciiu2"] = '';
        $_SESSION["jsonsalida"]["ciiu3"] = '';
        $_SESSION["jsonsalida"]["ciiu4"] = '';
        $_SESSION["jsonsalida"]["afiliado"] = '';
        $_SESSION["jsonsalida"]["saldoafiliado"] = '';
        $_SESSION["jsonsalida"]["anodatos"] = '';
        $_SESSION["jsonsalida"]["fechadatos"] = '';
        $_SESSION["jsonsalida"]["activos"] = '';
        $_SESSION["jsonsalida"]["pasivos"] = '';
        $_SESSION["jsonsalida"]["patrimonio"] = '';
        $_SESSION["jsonsalida"]["ingresos"] = '';
        $_SESSION["jsonsalida"]["gastos"] = '';
        $_SESSION["jsonsalida"]["utilidad"] = '';
        $_SESSION["jsonsalida"]["personal"] = '';
        $_SESSION["jsonsalida"]["beneficio1429"] = '';
        $_SESSION["jsonsalida"]["beneficio1780"] = '';        
        $_SESSION["jsonsalida"]["fechainicioactividades"] = '';
        $_SESSION["jsonsalida"]["regimentributario"] = '';
        $_SESSION["jsonsalida"]["idclaserl"] = '';
        $_SESSION["jsonsalida"]["identificacionrl"] = '';
        $_SESSION["jsonsalida"]["nombrerl"] = '';
        $_SESSION["jsonsalida"]["idclasepro"] = '';
        $_SESSION["jsonsalida"]["identificacionpro"] = '';
        $_SESSION["jsonsalida"]["nombrepro"] = '';
        $_SESSION["jsonsalida"]["matriculapro"] = '';
        $_SESSION["jsonsalida"]["camarapro"] = '';
        $_SESSION["jsonsalida"]["renovacionappaltoimpacto"] = '';
        $_SESSION["jsonsalida"]["renovacionappnocomercial"] = '';
        $_SESSION["jsonsalida"]["renovacionapp1780"] = '';
        $_SESSION["jsonsalida"]["renovacionappmultavencida"] = '';
        $_SESSION["jsonsalida"]["cantidadmujeres"] = 0;
        $_SESSION["jsonsalida"]["cantidadmujerescargosdirectivos"] = 0;
        $_SESSION["jsonsalida"]["participacionmujeres"] = 0;
        $_SESSION["jsonsalida"]["tamanoempresa"] = '';
        $_SESSION["jsonsalida"]["ciiutamanoempresarial"] = '';
        $_SESSION["jsonsalida"]["ingresostamanoempresarial"] = 0;
        $_SESSION["jsonsalida"]["anodatostamanoempresarial"] = '';
        $_SESSION["jsonsalida"]["fechadatostamanoempresarial"] = '';

        $_SESSION["jsonsalida"]["establecimientos"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("identificacion", false);
        $api->validarParametro("tipo", false);
        $api->validarParametro("ambiente", false);


        if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó matrícula ni identificación a consultar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarExpedienteMercantil', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // 2018-07-18: JINT: Se incluye validación campo "tipo"
        // - T. Valida activos y cancelados
        // - C.- Solo cancelados
        // - A.- Solo activos
        // Por defecto asume T
        // ********************************************************************** // 
        if ($_SESSION["entrada"]["tipo"] != 'C' && $_SESSION["entrada"]["tipo"] != 'A') {
            $_SESSION["entrada"]["tipo"] = 'T';
        }

        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** // 
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'DEF') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'PRD') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'DES') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
    
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        // ********************************************************************** //
        // Busca el expediente indicado
        // ********************************************************************** // 
        $arrTem = false;
        if ($_SESSION["entrada"]["matricula"] != '') {
            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"], '', '', '', 'N');
        } else {
            if ($_SESSION["entrada"]["identificacion"] != '') {
                $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, '', '', $_SESSION["entrada"]["identificacion"], '', 'N', $_SESSION["entrada"]["tipo"]);
            }
        }
        if ($arrTem === false || $arrTem == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Retornar el expediente recuperado
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["matricula"] = ltrim((string)$arrTem["matricula"], "0");
        $_SESSION["jsonsalida"]["nombre"] = trim((string)$arrTem["nombre"]);
        $_SESSION["jsonsalida"]["nombre1"] = trim((string)$arrTem["nom1"]);
        $_SESSION["jsonsalida"]["nombre2"] = trim((string)$arrTem["nom2"]);
        $_SESSION["jsonsalida"]["apellido1"] = trim((string)$arrTem["ape1"]);
        $_SESSION["jsonsalida"]["apellido2"] = trim((string)$arrTem["ape2"]);
        $_SESSION["jsonsalida"]["sigla"] = trim((string)$arrTem["sigla"]);
        $_SESSION["jsonsalida"]["idclase"] = $arrTem["tipoidentificacion"];
        $_SESSION["jsonsalida"]["identificacion"] = ltrim((string)$arrTem["identificacion"], "0");
        $_SESSION["jsonsalida"]["genero"] = $arrTem["sexo"];
        $_SESSION["jsonsalida"]["emprendimientosocial"] = $arrTem["emprendimientosocial"];
        $_SESSION["jsonsalida"]["nit"] = ltrim((string)$arrTem["nit"], "0");
        $_SESSION["jsonsalida"]["organizacion"] = $arrTem["organizacion"];
        $_SESSION["jsonsalida"]["categoria"] = $arrTem["categoria"];
        $_SESSION["jsonsalida"]["estado"] = $arrTem["estadomatricula"];
        $_SESSION["jsonsalida"]["fechamatricula"] = $arrTem["fechamatricula"];
        $_SESSION["jsonsalida"]["fecharenovacion"] = $arrTem["fecharenovacion"];
        $_SESSION["jsonsalida"]["ultanorenovado"] = $arrTem["ultanoren"];
        $_SESSION["jsonsalida"]["fechacancelacion"] = $arrTem["fechacancelacion"];
        $_SESSION["jsonsalida"]["dircom"] = trim($arrTem["dircom"]);
        $_SESSION["jsonsalida"]["idbarriocom"] = trim((string)$arrTem["barriocom"]);
        $_SESSION["jsonsalida"]["barriocom"] = trim((string)$arrTem["nombrebarriocom"]);
        $_SESSION["jsonsalida"]["muncom"] = $arrTem["muncom"];
        $_SESSION["jsonsalida"]["telcom1"] = trim((string)$arrTem["telcom1"]);
        $_SESSION["jsonsalida"]["telcom2"] = trim((string)$arrTem["telcom2"]);
        $_SESSION["jsonsalida"]["telcom3"] = trim((string)$arrTem["celcom"]);
        $_SESSION["jsonsalida"]["emailcom"] = trim((string)$arrTem["emailcom"]);
        $_SESSION["jsonsalida"]["urlcom"] = trim((string)$arrTem["urlcom"]);
        $_SESSION["jsonsalida"]["dirnot"] = trim((string)$arrTem["dirnot"]);
        $_SESSION["jsonsalida"]["idbarrionot"] = trim((string)$arrTem["barrionot"]);
        $_SESSION["jsonsalida"]["barrionot"] = trim((string)$arrTem["nombrebarrionot"]);
        $_SESSION["jsonsalida"]["munnot"] = $arrTem["munnot"];
        $_SESSION["jsonsalida"]["telnot1"] = trim((string)$arrTem["telnot"]);
        $_SESSION["jsonsalida"]["telnot2"] = trim((string)$arrTem["telnot2"]);
        $_SESSION["jsonsalida"]["telnot3"] = trim((string)$arrTem["celnot"]);
        $_SESSION["jsonsalida"]["emailnot"] = trim((string)$arrTem["emailnot"]);
        $_SESSION["jsonsalida"]["autorizacionemailsms"] = $arrTem["ctrmennot"];
        $_SESSION["jsonsalida"]["ciiu1"] = $arrTem["ciius"][1];
        $_SESSION["jsonsalida"]["ciiu2"] = $arrTem["ciius"][2];
        $_SESSION["jsonsalida"]["ciiu3"] = $arrTem["ciius"][3];
        $_SESSION["jsonsalida"]["ciiu4"] = $arrTem["ciius"][4];
        $_SESSION["jsonsalida"]["afiliado"] = $arrTem["afiliado"];
        $_SESSION["jsonsalida"]["saldoafiliado"] = intval($arrTem["saldoafiliado"]);

        $_SESSION["jsonsalida"]["activos"] = '';
        $_SESSION["jsonsalida"]["pasivos"] = '';
        $_SESSION["jsonsalida"]["patrimonio"] = '';
        $_SESSION["jsonsalida"]["ingresos"] = '';
        $_SESSION["jsonsalida"]["gastos"] = '';
        $_SESSION["jsonsalida"]["utilidad"] = '';

        $_SESSION["jsonsalida"]["anodatos"] = $arrTem["anodatos"];
        $_SESSION["jsonsalida"]["fechadatos"] = $arrTem["fechadatos"];

        //
        if ($arrTem["organizacion"] == '01' || $arrTem["categoria"] == '1') {
            $_SESSION["jsonsalida"]["activos"] = doubleval($arrTem["acttot"]);
            $_SESSION["jsonsalida"]["pasivos"] = doubleval($arrTem["pastot"]);
            $_SESSION["jsonsalida"]["patrimonio"] = doubleval($arrTem["pattot"]);
            $_SESSION["jsonsalida"]["ingresos"] = doubleval($arrTem["ingope"]) + doubleval($arrTem["ingnoope"]);
            $_SESSION["jsonsalida"]["gastos"] = doubleval($arrTem["gtoven"]) + doubleval($arrTem["gtoadm"]) + doubleval($arrTem["cosven"]);
            $_SESSION["jsonsalida"]["utilidad"] = doubleval($arrTem["utinet"]);
        } else {
            $_SESSION["jsonsalida"]["activos"] = doubleval($arrTem["actvin"]);
        }

        // 2017-11-10: JINT: Se adicionan nuevos cmampos.
        $_SESSION["jsonsalida"]["actcte"] = $arrTem["actcte"];
        $_SESSION["jsonsalida"]["actnocte"] = $arrTem["actnocte"];
        $_SESSION["jsonsalida"]["acttot"] = $arrTem["acttot"];
        $_SESSION["jsonsalida"]["pascte"] = $arrTem["pascte"];
        $_SESSION["jsonsalida"]["paslar"] = $arrTem["paslar"];
        $_SESSION["jsonsalida"]["pastot"] = $arrTem["pastot"];
        $_SESSION["jsonsalida"]["pattot"] = $arrTem["pattot"];
        $_SESSION["jsonsalida"]["paspat"] = $arrTem["paspat"];
        $_SESSION["jsonsalida"]["balsoc"] = $arrTem["balsoc"];
        $_SESSION["jsonsalida"]["ingope"] = $arrTem["ingope"];
        $_SESSION["jsonsalida"]["ingnoope"] = $arrTem["ingnoope"];
        $_SESSION["jsonsalida"]["gtoven"] = $arrTem["gtoven"];
        $_SESSION["jsonsalida"]["gtoadm"] = $arrTem["gtoadm"];
        $_SESSION["jsonsalida"]["cosven"] = $arrTem["cosven"];
        $_SESSION["jsonsalida"]["gasint"] = $arrTem["gasint"];
        $_SESSION["jsonsalida"]["gasimp"] = $arrTem["gasimp"];
        $_SESSION["jsonsalida"]["utiope"] = $arrTem["utiope"];
        $_SESSION["jsonsalida"]["utinet"] = $arrTem["utinet"];
        $_SESSION["jsonsalida"]["actvin"] = $arrTem["actvin"];

        //
        $_SESSION["jsonsalida"]["personal"] = intval($arrTem["personal"]);
        $_SESSION["jsonsalida"]["beneficio1429"] = $arrTem["art7"];
        $_SESSION["jsonsalida"]["beneficio1780"] = $arrTem["benley1780"];
        $_SESSION["jsonsalida"]["fechainicioactividades"] = $arrTem["codigoscae"]["caealc_fec_inicio_actividades"];
        $_SESSION["jsonsalida"]["regimentributario"] = $arrTem["codigoscae"]["caealc_regimen_tributario1"];
        if ($arrTem["categoria"] == '1') {
            $_SESSION["jsonsalida"]["idclaserl"] = $arrTem["idtipoidentificacionreplegal"];
            $_SESSION["jsonsalida"]["identificacionrl"] = $arrTem["identificacionreplegal"];
            $_SESSION["jsonsalida"]["nombrerl"] = $arrTem["nombrereplegal"];
        }
        if ($arrTem["organizacion"] == '02') {
            if (!empty($arrTem["propietarios"])) {
                $_SESSION["jsonsalida"]["idclasepro"] = $arrTem["propietarios"][1]["idtipoidentificacionpropietario"];
                $_SESSION["jsonsalida"]["identificacionpro"] = $arrTem["propietarios"][1]["identificacionpropietario"];
                $_SESSION["jsonsalida"]["nombrepro"] = $arrTem["propietarios"][1]["nombrepropietario"];
                $_SESSION["jsonsalida"]["matriculapro"] = $arrTem["propietarios"][1]["matriculapropietario"];
                $_SESSION["jsonsalida"]["camarapro"] = $arrTem["propietarios"][1]["camarapropietario"];
            }
        }
        if ($arrTem["categoria"] == '2' || $arrTem["categoria"] == '3') {
            $_SESSION["jsonsalida"]["idclasepro"] = '2';
            $_SESSION["jsonsalida"]["identificacionpro"] = $arrTem["cpnumnit"];
            $_SESSION["jsonsalida"]["nombrepro"] = $arrTem["cprazsoc"];
            $_SESSION["jsonsalida"]["matriculapro"] = $arrTem["cpnummat"];
            $_SESSION["jsonsalida"]["camarapro"] = $arrTem["cpcodcam"];
        }

        //
        $cx = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $_SESSION["jsonsalida"]["ciiu1"] . "'");
        if ($cx["restriccionponal"] == 'S') {
            $_SESSION["jsonsalida"]["renovacionappaltoimpacto"] = 'si';
        }
        if ($cx["actividadcomercial"] == 'NO') {
            $_SESSION["jsonsalida"]["renovacionappnocomercial"] = 'si';
        }
        if (trim($_SESSION["jsonsalida"]["ciiu2"]) != '') {
            $cx = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $_SESSION["jsonsalida"]["ciiu2"] . "'");
            if ($cx["restriccionponal"] == 'S') {
                $_SESSION["jsonsalida"]["renovacionappaltoimpacto"] = 'si';
            }
            if ($cx["actividadcomercial"] == 'NO') {
                $_SESSION["jsonsalida"]["renovacionappnocomercial"] = 'si';
            }
        }
        if (trim($_SESSION["jsonsalida"]["ciiu3"]) != '') {
            $cx = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $_SESSION["jsonsalida"]["ciiu3"] . "'");
            if ($cx["restriccionponal"] == 'S') {
                $_SESSION["jsonsalida"]["renovacionappaltoimpacto"] = 'si';
            }
            if ($cx["actividadcomercial"] == 'NO') {
                $_SESSION["jsonsalida"]["renovacionappnocomercial"] = 'si';
            }
        }
        if (trim($_SESSION["jsonsalida"]["ciiu4"]) != '') {
            $cx = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $_SESSION["jsonsalida"]["ciiu4"] . "'");
            if ($cx["restriccionponal"] == 'S') {
                $_SESSION["jsonsalida"]["renovacionappaltoimpacto"] = 'si';
            }
            if ($cx["actividadcomercial"] == 'NO') {
                $_SESSION["jsonsalida"]["renovacionappnocomercial"] = 'si';
            }
        }
        if ($arrTem["benley1780"] == 'S') {
            $anom = date("Y") - 1;
            if ($arrTem["fechamatricula"] >= $anom . '0101' && $arrTem["fechamatricula"] <= $anom . '1231') {
                $_SESSION["jsonsalida"]["renovacionapp1780"] = 'si';
            }
        }
        if ($arrTem["organizacion"] == '01') {  
            if (substr(ACTIVAR_CONTROL_MULTAS_PONAL,0,2) == 'SI') {
                $multado = \funcionesGenerales::consultarMultasPolicia($mysqli, $arrTem["tipoidentificacion"], $arrTem["identificacion"]);
                if ($multado == 'SI') {
                    $_SESSION["jsonsalida"]["renovacionappmultavencida"] = 'si';
                }
            } else {
                $_SESSION["jsonsalida"]["renovacionappmultavencida"] = '';
            }
        }

        //
        $_SESSION["jsonsalida"]["cantidadmujeres"] = $arrTem["cantidadmujeres"];
        $_SESSION["jsonsalida"]["cantidadmujerescargosdirectivos"] = $arrTem["cantidadmujerescargosdirectivos"];
        $_SESSION["jsonsalida"]["participacionmujeres"] = $arrTem["participacionmujeres"];
        if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
            $_SESSION["jsonsalida"]["tamanoempresa"] = $arrTem["tamanoempresarial957codigo"];
            $_SESSION["jsonsalida"]["ciiutamanoempresarial"] = $arrTem["ciiutamanoempresarial"];
            $_SESSION["jsonsalida"]["ingresostamanoempresarial"] = $arrTem["ingresostamanoempresarial"];
            $_SESSION["jsonsalida"]["anodatostamanoempresarial"] = $arrTem["anodatostamanoempresarial"];
            $_SESSION["jsonsalida"]["fechadatostamanoempresarial"] = $arrTem["fechadatostamanoempresarial"];
        }
        
        //
        $_SESSION["jsonsalida"]["establecimientos"] = array();
        if ($arrTem["organizacion"] == '01' || ($arrTem["organizacion"] > '02' && $arrTem["categoria"] == '1')) {
            foreach ($arrTem["establecimientos"] as $est) {
                if ($est["estadomatricula"] != 'MC' && $est["estadomatricula"] != 'MF') {
                    $iEst = array();
                    $iEst["categoria"] = 'E';
                    $iEst["matricula"] = $est["matriculaestablecimiento"];
                    $iEst["nombre"] = $est["nombreestablecimiento"];
                    $iEst["ultanorenovado"] = $est["ultanoren"];
                    $iEst["fechamatricula"] = $est["fechamatricula"];
                    $iEst["fecharenovacion"] = $est["fecharenovacion"];
                    $iEst["valorestablecimiento"] = $est["actvin"];
                    $iEst["latitud"] = 0;
                    $iEst["longitud"] = 0;
                    $iEst["fechacenso"] = '';
                    $iEst["censo"] = '';
                    $iEst["infografia1"] = '';
                    $iEst["infografia2"] = '';

                    // 2017-08-29: JINT: Se adiciona por solicitud de la CC de Valledupar
                    if ($est["matriculaestablecimiento"] != '') {
                        $query = "SELECT id,id_censo, id_encuesta, fecha, matricula_establecimiento, "
                                . "nombre_establecimiento, municipio_establecimiento,latitud, longitud "
                                . "FROM mreg_censo_base_dinamico "
                                . "WHERE matricula_establecimiento='" . $est["matriculaestablecimiento"] . "' "
                                . "AND legales_tiene_matricula='SI' ORDER BY fecha desc LIMIT 1";

                        $mysqli->set_charset("utf8");
                        $resQueryCenso = $mysqli->query($query);

                        if (!empty($resQueryCenso)) {
                            while ($encuestaTemp = $resQueryCenso->fetch_array(MYSQLI_ASSOC)) {
                                $encuesta = array();
                                $encuesta['id'] = $encuestaTemp['id'];
                                $iEst['censo'] = $encuestaTemp['id_censo'];
                                $iEst['fechacenso'] = $encuestaTemp['fecha'];
                                $iEst['latitud'] = doubleval($encuestaTemp['latitud']);
                                $iEst['longitud'] = doubleval($encuestaTemp['longitud']);
                                $urlInfografia = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . '/' .
                                        $_SESSION["generales"]["codigoempresa"] . '/mreg/´/' .
                                        $encuestaTemp['id_censo'] . '/' .
                                        sprintf("%09s", $encuestaTemp['id_encuesta']) . '/';

                                if (file_get_contents($urlInfografia . 'f1.jpg')) {
                                    $iEst['infografia1'] = $urlInfografia . 'f1.jpg';
                                }

                                if (file_get_contents($urlInfografia . 'f2.jpg')) {
                                    $iEst['infografia2'] = $urlInfografia . 'f2.jpg';
                                }
                            }
                            $resQueryCenso->free();
                        }
                    }
                    $_SESSION["jsonsalida"]["establecimientos"][] = $iEst;
                }
            }

            foreach ($arrTem["sucursalesagencias"] as $est) {
                if ($est["estado"] != 'MC' && $est["estado"] != 'MF') {
                    $iEst = array();
                    $iEst["categoria"] = 'E';
                    if ($est["categoria"] == '2') {
                        $iEst["categoria"] = 'S';
                    }
                    if ($est["categoria"] == '3') {
                        $iEst["categoria"] = 'A';
                    }
                    $iEst["matricula"] = $est["matriculasucage"];
                    $iEst["nombre"] = $est["nombresucage"];
                    $iEst["ultanorenovado"] = $est["ultanoren"];
                    $iEst["fechamatricula"] = $est["fechamatricula"];
                    $iEst["fecharenovacion"] = $est["fecharenovacion"];
                    $iEst["valorestablecimiento"] = $est["actvin"];
                    $iEst["latitud"] = 0;
                    $iEst["longitud"] = 0;
                    $iEst["fechacenso"] = '';
                    $iEst["censo"] = '';
                    $iEst["infografia1"] = '';
                    $iEst["infografia2"] = '';

                    // 2017-08-29: JINT: Se adiciona por solicitud de la CC de Valledupar
                    if ($est["matriculasucage"] != '') {
                        $query = "SELECT id,id_censo, id_encuesta, fecha, matricula_establecimiento, "
                                . "nombre_establecimiento, municipio_establecimiento,latitud, longitud "
                                . "FROM mreg_censo_base_dinamico "
                                . "WHERE matricula_establecimiento='" . $est["matriculasucage"] . "' "
                                . "AND legales_tiene_matricula='SI' ORDER BY fecha desc LIMIT 1";

                        $mysqli->set_charset("utf8");
                        $resQueryCenso = $mysqli->query($query);

                        if (!empty($resQueryCenso)) {
                            while ($encuestaTemp = $resQueryCenso->fetch_array(MYSQLI_ASSOC)) {
                                $encuesta = array();
                                $encuesta['id'] = $encuestaTemp['id'];
                                $iEst['censo'] = $encuestaTemp['id_censo'];
                                $iEst['fechacenso'] = $encuestaTemp['fecha'];
                                $iEst['latitud'] = doubleval($encuestaTemp['latitud']);
                                $iEst['longitud'] = doubleval($encuestaTemp['longitud']);
                                $urlInfografia = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . '/' .
                                        $_SESSION["generales"]["codigoempresa"] . '/mreg/´/' .
                                        $encuestaTemp['id_censo'] . '/' .
                                        sprintf("%09s", $encuestaTemp['id_encuesta']) . '/';

                                if (file_get_contents($urlInfografia . 'f1.jpg')) {
                                    $iEst['infografia1'] = $urlInfografia . 'f1.jpg';
                                }

                                if (file_get_contents($urlInfografia . 'f2.jpg')) {
                                    $iEst['infografia2'] = $urlInfografia . 'f2.jpg';
                                }
                            }
                            $resQueryCenso->free();
                        }
                    }
                    $_SESSION["jsonsalida"]["establecimientos"][] = $iEst;
                }
            }
        }

        // **************************************************************************** //
        // Cerrar conexión a la BD
        // **************************************************************************** //        
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function consultarExpedienteMercantilResumido(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["nombre1"] = '';
        $_SESSION["jsonsalida"]["nombre2"] = '';
        $_SESSION["jsonsalida"]["apellido1"] = '';
        $_SESSION["jsonsalida"]["apellido2"] = '';
        $_SESSION["jsonsalida"]["sigla"] = '';
        $_SESSION["jsonsalida"]["idclase"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["genero"] = '';
        $_SESSION["jsonsalida"]["nit"] = '';
        $_SESSION["jsonsalida"]["organizacion"] = '';
        $_SESSION["jsonsalida"]["categoria"] = '';
        $_SESSION["jsonsalida"]["estado"] = '';
        $_SESSION["jsonsalida"]["fechamatricula"] = '';
        $_SESSION["jsonsalida"]["fecharenovacion"] = '';
        $_SESSION["jsonsalida"]["ultanorenovado"] = '';
        $_SESSION["jsonsalida"]["fechacancelacion"] = '';
        $_SESSION["jsonsalida"]["dircom"] = '';
        $_SESSION["jsonsalida"]["idbarriocom"] = '';
        $_SESSION["jsonsalida"]["barriocom"] = '';
        $_SESSION["jsonsalida"]["muncom"] = '';
        $_SESSION["jsonsalida"]["telcom1"] = '';
        $_SESSION["jsonsalida"]["telcom2"] = '';
        $_SESSION["jsonsalida"]["telcom3"] = '';
        $_SESSION["jsonsalida"]["emailcom"] = '';
        $_SESSION["jsonsalida"]["urlcom"] = '';
        $_SESSION["jsonsalida"]["dirnot"] = '';
        $_SESSION["jsonsalida"]["idbarrionot"] = '';
        $_SESSION["jsonsalida"]["barrionot"] = '';
        $_SESSION["jsonsalida"]["munnot"] = '';
        $_SESSION["jsonsalida"]["telnot1"] = '';
        $_SESSION["jsonsalida"]["telnot2"] = '';
        $_SESSION["jsonsalida"]["telnot3"] = '';
        $_SESSION["jsonsalida"]["emailnot"] = '';
        $_SESSION["jsonsalida"]["autorizacionemailsms"] = '';
        $_SESSION["jsonsalida"]["ciiu1"] = '';
        $_SESSION["jsonsalida"]["ciiu2"] = '';
        $_SESSION["jsonsalida"]["ciiu3"] = '';
        $_SESSION["jsonsalida"]["ciiu4"] = '';
        $_SESSION["jsonsalida"]["afiliado"] = '';
        $_SESSION["jsonsalida"]["saldoafiliado"] = '';
        $_SESSION["jsonsalida"]["anodatos"] = '';
        $_SESSION["jsonsalida"]["fechadatos"] = '';
        $_SESSION["jsonsalida"]["activos"] = '';
        $_SESSION["jsonsalida"]["pasivos"] = '';
        $_SESSION["jsonsalida"]["patrimonio"] = '';
        $_SESSION["jsonsalida"]["ingresos"] = '';
        $_SESSION["jsonsalida"]["gastos"] = '';
        $_SESSION["jsonsalida"]["utilidad"] = '';
        $_SESSION["jsonsalida"]["personal"] = '';
        $_SESSION["jsonsalida"]["beneficio1429"] = '';
        $_SESSION["jsonsalida"]["beneficio1780"] = '';
        $_SESSION["jsonsalida"]["tamanoempresa"] = '';
        $_SESSION["jsonsalida"]["fechainicioactividades"] = '';
        $_SESSION["jsonsalida"]["regimentributario"] = '';
        $_SESSION["jsonsalida"]["idclaserl"] = '';
        $_SESSION["jsonsalida"]["identificacionrl"] = '';
        $_SESSION["jsonsalida"]["nombrerl"] = '';
        $_SESSION["jsonsalida"]["idclasepro"] = '';
        $_SESSION["jsonsalida"]["identificacionpro"] = '';
        $_SESSION["jsonsalida"]["nombrepro"] = '';
        $_SESSION["jsonsalida"]["matriculapro"] = '';
        $_SESSION["jsonsalida"]["camarapro"] = '';
        $_SESSION["jsonsalida"]["renovacionappaltoimpacto"] = '';
        $_SESSION["jsonsalida"]["renovacionappnocomercial"] = '';
        $_SESSION["jsonsalida"]["renovacionapp1780"] = '';
        $_SESSION["jsonsalida"]["renovacionappmultavencida"] = '';
        $_SESSION["jsonsalida"]["cantidadmujeres"] = 0;
        $_SESSION["jsonsalida"]["cantidadmujerescargosdirectivos"] = 0;
        $_SESSION["jsonsalida"]["participacionmujeres"] = 0;
        $_SESSION["jsonsalida"]["ciiutamanoempresarial"] = '';
        $_SESSION["jsonsalida"]["ingresostamanoempresarial"] = 0;
        $_SESSION["jsonsalida"]["anodatostamanoempresarial"] = '';
        $_SESSION["jsonsalida"]["fechadatostamanoempresarial"] = '';

        $_SESSION["jsonsalida"]["establecimientos"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("identificacion", false);

        if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó matrícula ni identificación a consultar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarExpedienteMercantilResumido', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Busca el expediente indicado
        // ********************************************************************** // 
        $arrTem = false;
        if ($_SESSION["entrada"]["matricula"] != '') {
            $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'");
        } else {
            if ($_SESSION["entrada"]["identificacion"] != '') {
                $arrTem = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacion"] . "'", "*", "U");
            }
        }
        if ($arrTem === false || $arrTem == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Retornar el expediente recuperado
        // ********************************************************************** // 
        $tabcam = array();
        $tcam = retornarRegistrosMysqliApi($mysqli, 'tablas', "estructura-mreg-est-inscritos", "id");
        foreach ($tcam as $tx) {
            $tabcam[$tx["idcodigo"]] = $tx;
        }
        foreach ($arrTem as $k => $v) {
            if (isset($tabcam[$k]) && $tabcam[$k]["campo3"] == 'S') {
                $_SESSION["jsonsalida"][$k] = $v;
            }
        }

        // **************************************************************************** //
        // Cerrar conexión a la BD
        // **************************************************************************** //        
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
