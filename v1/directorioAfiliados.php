<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait directorioAfiliados {

    public function directorioAfiliados(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $expedientes = array();
        $_SESSION["jsonsalida"]["total"] = '';
        $_SESSION["jsonsalida"]["expedientes"] = array();
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("registros_a_incluir", false);
        $api->validarParametro("ambiente", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('directorioAfiliados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = false;
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9904";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $tipoOrganizacion = array();
        $temx = retornarRegistrosMysqliApi($mysqli, 'bas_organizacionjuridica', "1=1");
        foreach ($temx as $tx) {
            $tipoOrganizacion[$tx['id']] = mb_strtoupper($tx['descripcion'], 'utf-8');
        }

        //
        $tipoVinculos = array();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_codvinculos', "1=1");
        foreach ($temx as $tx) {
            $tipoVinculos[$tx['id']] = $tx;
        }

        $tipoAfiliado = array();
        $tipoAfiliado[0] = "NO AFILIADO";
        $tipoAfiliado[1] = "AFILIACIÓN ACTIVA";
        $tipoAfiliado[2] = "DES-AFILIADO";
        $tipoAfiliado[3] = "ACEPTADO";
        $tipoAfiliado[5] = "DESAFILIACIÓN TEMPORAL";

        $tipoCategoria = array();
        $tipoCategoria[1] = "PRINCIPAL";
        $tipoCategoria[2] = "SUCURSAL";
        $tipoCategoria[3] = "AGENCIA";


        $estadoProponente = array();
        $estadoProponente["00"] = "ACTIVO";
        $estadoProponente["01"] = "CANCELADO";
        $estadoProponente["02"] = "EN ACTUALIZACIÓN";
        $estadoProponente["03"] = "NO RENOVADO";
        $estadoProponente["04"] = "NO ASIGNADO";


        $estadoDatos = array();
        $estadoDatos["0"] = "SIN DATOS";
        $estadoDatos["1"] = "NORMAL";
        $estadoDatos["2"] = "EN DIGITACIÓN";
        $estadoDatos["3"] = "POR VERIFICAR";
        $estadoDatos["4"] = "EN RENOVACIÓN";
        $estadoDatos["5"] = "EN CORRECCIÓN";
        $estadoDatos["6"] = "REVISADO";
        $estadoDatos["7"] = "DOC. TRÁMITE";
        $estadoDatos["8"] = "EN REVISIÓN";

        //
        if (!isset($_SESSION["entrada"]["registros_a_incluir"]) || $_SESSION["entrada"]["registros_a_incluir"] == '') {
            $_SESSION["entrada"]["registros_a_incluir"] = 'afi';
        }
        
        // ********************************************************************** //
        // Buscar expedientes
        // ********************************************************************** // 
        $arrExpedientes = array();
        if ($_SESSION["entrada"]["registros_a_incluir"] == 'afi') {
            $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrafiliacion='1' and ctrestmatricula IN ('MA','MI','MR','II','IA')", "razonsocial");
        }
        if ($_SESSION["entrada"]["registros_a_incluir"] == 'des') {
            $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrafiliacion='2'", "razonsocial");
        }
        if ($_SESSION["entrada"]["registros_a_incluir"] == 'afides') {
            $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrafiliacion IN ('1','2')", "razonsocial");
        }
        
        //
        $arrTem = array();

        //
        if ($reg === false || empty($reg)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No encontraron registros que cumplan con el criterio indicado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $i = -1;
            $tabMun = array ();
            foreach ($reg as $rg) {
                if (!isset($tabMun[$rg["muncom"]])) {
                    $tabMun[$rg["muncom"]] = retornarRegistroMysqliApi($mysqli, 'bas_municipios',"codigomunicipio='" . $rg["muncom"]. "'");
                }
                $i++;
                $arrTem[$i]["matricula"] = $rg["matricula"];
                $arrTem[$i]["proponente"] = ltrim(trim((string)$rg["proponente"]), "0");
                $arrTem[$i]["tipoidentificacion"] = $rg["idclase"];
                $arrTem[$i]["identificacion"] = $rg["numid"];
                $arrTem[$i]["nombre"] = $rg["razonsocial"];
                $arrTem[$i]["razonsocial"] = $rg["razonsocial"];
                $arrTem[$i]["ape1"] = $rg["apellido1"];
                $arrTem[$i]["ape2"] = $rg["apellido2"];
                $arrTem[$i]["nom1"] = $rg["nombre1"];
                $arrTem[$i]["nom2"] = $rg["nombre2"];
                $arrTem[$i]["organizacion"] = $rg["organizacion"]; //
                $arrTem[$i]["categoria"] = $rg["categoria"]; //
                $arrTem[$i]["fecmat"] = $rg["fecmatricula"];
                $arrTem[$i]["fecren"] = $rg["fecrenovacion"];
                $arrTem[$i]["ultanoren"] = $rg["ultanoren"];
                $arrTem[$i]["afiliacion"] = $rg["ctrafiliacion"];
                $arrTem[$i]["fecafiliacion"] = $rg["fecaflia"];
                $arrTem[$i]["actaafiliacion"] = $rg["numactaaflia"];
                $arrTem[$i]["fecactaafiliacion"] = $rg["fecactaaflia"];
                
                if ($rg["ctrafiliacion"] == '2') {
                    $arrTem[$i]["feccanafiliacion"] = $rg["fecactacanaflia"];
                    $arrTem[$i]["actadesafiliacion"] = $rg["numactacanaflia"];
                    $arrTem[$i]["fecactadesafiliacion"] = $rg["fecactacanaflia"];
                } else {
                    $arrTem[$i]["feccanafiliacion"] = '';
                    $arrTem[$i]["actadesafiliacion"] = '';
                    $arrTem[$i]["fecactadesafiliacion"] = '';                    
                }
                
                $arrTem[$i]["estadomatricula"] = $rg["ctrestmatricula"];
                $arrTem[$i]["estadoproponente"] = $rg["ctrestproponente"];
                $arrTem[$i]["estabs"] = array();
                $arrTem[$i]["embargos"] = '';
                $arrTem[$i]["estadodatosmatricula"] = $rg["ctrestdatos"];
                $arrTem[$i]["fecinsprop"] = '';
                $arrTem[$i]["fecrenprop"] = '';
                $arrTem[$i]["feccanprop"] = '';
                $arrTem[$i]["saldoafiliado"] = $rg["saldoaflia"];
                $arrTem[$i]["sigla"] = $rg["sigla"];
                $arrTem[$i]["nit"] = $rg["nit"];
                $arrTem[$i]["dircom"] = $rg["dircom"];
                $arrTem[$i]["telcom"] = $rg["telcom1"];
                $arrTem[$i]["muncom"] = $rg["muncom"]; //
                $arrTem[$i]["desmuncom"] = $tabMun[$rg["muncom"]]["ciudad"] . ' (' . $tabMun[$rg["muncom"]]["departamento"] . ')'; //
                $arrTem[$i]["emailcom"] = $rg["emailcom"]; //                
                $arrTem[$i]["ciiu1"] = $rg["ciiu1"];
                $arrTem[$i]["ciiu2"] = $rg["ciiu2"];
                $arrTem[$i]["ciiu3"] = $rg["ciiu3"];
                $arrTem[$i]["ciiu4"] = $rg["ciiu4"];
                
                

                if (ltrim(trim($rg["proponente"]), "0") != '') {
                    $arr1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . ltrim($rg["proponente"], "0") . "'");
                    if ($arr1 && !empty($arr1)) {
                        $arrTem[$i]["fecinsprop"] = $arr1["fechaultimainscripcion"];
                        $arrTem[$i]["fecrenprop"] = $arr1["fechaultimarenovacion"];
                        $arrTem[$i]["feccanprop"] = $arr1["fechacancelacion"];
                    }
                }

                /*
                if ($rg["categoria"] != '2' && $rg["categoria"] != '3' && $rg["organizacion"] != '02') {
                    $arr1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario='" . $rg["matricula"] . "'", "matricula");
                    if ($arr1 && !empty($arr1)) {
                        $j = 0;
                        foreach ($arr1 as $ar) {
                            $arr2 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $ar["matricula"] . "'");
                            $arrTem[$i]["estabs"][$j]["mat"] = $ar["matricula"];
                            $arrTem[$i]["estabs"][$j]["nom"] = $arr2["razonsocial"];
                            $arrTem[$i]["estabs"][$j]["est"] = $arr2["ctrestmatricula"];
                        }
                    }
                }
                */

                /*
                if (ltrim($rg["matricula"], "0") != 0) {
                    $arrTem[$i]["embargos"] = contarRegistrosMysqliApi($mysqli, 'mreg_est_embargos', "matricula='" . ltrim($rg["matricula"], "0") . "' and acto IN ('0900','0940','1000','1040') and ctrestadoembargo = '1'");
                }
                */
                
                $iVin = 0;
                $arrTem[$i]["rl"] = array();
                $temrl = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "matricula='" . $arrTem[$i]["matricula"] . "'");
                if ($temrl && !empty($temrl)) {
                    foreach ($temrl as $trl) {
                        if ($trl["estado"] == 'V') {
                            if (isset($tipoVinculos[$trl["vinculo"]])) {
                                if ($tipoVinculos[$trl["vinculo"]]["tipovinculo"] == 'RLP') {
                                    $iVin++;
                                    $arrTem[$i]["rl"][$iVin] = array(
                                        'identificacionrl' => $trl["numid"],
                                        'nombrerl' => $trl["nombre"],
                                        'vinculorl' => $trl["vinculo"],
                                        'descargorl' => $trl["descargo"]
                                    );
                                }
                            }
                        }
                    }
                }
                
                $arrTem[$i]["cpcodcam"] = $rg["cpcodcam"];
                $arrTem[$i]["cpnummat"] = $rg["cpnummat"];
                $arrTem[$i]["cpnumnit"] = $rg["cpnumnit"];
                $arrTem[$i]["cprazsoc"] = $rg["cprazsoc"];
            }
        }

        // **************************************************************************** //
        // Construye salida API
        // **************************************************************************** //


        foreach ($arrTem as $expedienteInfo) {
            $arrayExpresp = array();
            $arrayExpresp["matricula"] = trim((string)$expedienteInfo["matricula"]);
            $arrayExpresp["proponente"] = trim((string)$expedienteInfo["proponente"]);
            $arrayExpresp["nombre"] = trim((string)$expedienteInfo["razonsocial"]);
            $arrayExpresp["sigla"] = trim((string)$expedienteInfo["sigla"]);
            $arrayExpresp["idclase"] = trim((string)$expedienteInfo["tipoidentificacion"]);
            $arrayExpresp["identificacion"] = trim((string)$expedienteInfo["identificacion"]);
            $arrayExpresp["nit"] = trim((string)$expedienteInfo["nit"]);
            $arrayExpresp["organizacion"] = trim((string)$expedienteInfo["organizacion"]);
            $arrayExpresp["organizaciontextual"] = isset($tipoOrganizacion[$arrayExpresp["organizacion"]]) ? $tipoOrganizacion[$arrayExpresp["organizacion"]] : "";
            $arrayExpresp["categoria"] = trim((string)$expedienteInfo["categoria"]);
            $arrayExpresp["categoriatextual"] = isset($tipoCategoria[$arrayExpresp["categoria"]]) ? $tipoCategoria[$arrayExpresp["categoria"]] : "";
            $arrayExpresp["estadomatricula"] = trim((string)$expedienteInfo["estadomatricula"]);
            $arrayExpresp["estadoproponente"] = trim((string)$expedienteInfo["estadoproponente"]);
            $arrayExpresp["estadoproponentetextual"] = isset($estadoProponente[$arrayExpresp["estadoproponente"]]) ? $estadoProponente[$arrayExpresp["estadoproponente"]] : "";
            $arrayExpresp["fechamatricula"] = trim((string)$expedienteInfo["fecmat"]);
            $arrayExpresp["fecharenovacion"] = trim((string)$expedienteInfo["fecren"]);
            $arrayExpresp["ultanorenovado"] = trim((string)$expedienteInfo["ultanoren"]);
            $arrayExpresp["afiliado"] = trim((string)$expedienteInfo["afiliacion"]);
            $arrayExpresp["afiliadotextual"] = isset($tipoAfiliado[$arrayExpresp["afiliado"]]) ? $tipoAfiliado[$arrayExpresp["afiliado"]] : "";
            $arrayExpresp["fechaafiliacion"] = trim((string)$expedienteInfo["fecafiliacion"]);
            $arrayExpresp["numeroactaafiliacion"] = trim((string)$expedienteInfo["actaafiliacion"]);
            $arrayExpresp["fechaactaafiliacion"] = trim((string)$expedienteInfo["fecactaafiliacion"]);
            $arrayExpresp["fechadesafiliacion"] = trim((string)$expedienteInfo["feccanafiliacion"]);
            $arrayExpresp["numeroactadesafiliacion"] = trim((string)$expedienteInfo["actadesafiliacion"]);
            $arrayExpresp["fechaactadesafiliacion"] = trim((string)$expedienteInfo["fecactadesafiliacion"]);
            $arrayExpresp["estadodatosmatricula"] = $expedienteInfo["estadodatosmatricula"];
            $arrayExpresp["estadodatosmatriculatextual"] = isset($estadoDatos[$arrayExpresp["estadodatosmatricula"]]) ? $estadoDatos[$arrayExpresp["estadodatosmatricula"]] : "";
            $arrayExpresp["direccion"] = trim((string)$expedienteInfo["dircom"]);
            $arrayExpresp["telefono"] = trim((string)$expedienteInfo["telcom"]);
            $arrayExpresp["municipio"] = trim((string)$expedienteInfo["muncom"]); //
            $arrayExpresp["municipiotextual"] = trim((string)$expedienteInfo["desmuncom"]); //                        
            $arrayExpresp["emailcomercial"] = trim((string)$expedienteInfo["emailcom"]); //                        
            $arrayExpresp["representanteslegales"] = $expedienteInfo["rl"];
            $arrayExpresp["ciiu1"] = $expedienteInfo["ciiu1"];
            $arrayExpresp["ciiu2"] = $expedienteInfo["ciiu2"];
            $arrayExpresp["ciiu3"] = $expedienteInfo["ciiu3"];
            $arrayExpresp["ciiu4"] = $expedienteInfo["ciiu4"];            
            if ($arrayExpresp["categoria"] == '2') {
                $arrayExpresp["casaprincipalcamara"] = $expedienteInfo["cpcodcam"];
                $arrayExpresp["casaprincipalmatricula"] = $expedienteInfo["cpnummat"];
                $arrayExpresp["casaprincipalnit"] = $expedienteInfo["cpnumnit"];
                $arrayExpresp["casaprincipalrazonsocial"] = $expedienteInfo["cprazsoc"];
            }

            $arrExpedientes[] = $arrayExpresp;
        }
        $_SESSION["jsonsalida"]["total"] = count($arrExpedientes);
        $_SESSION["jsonsalida"]["expedientes"] = $arrExpedientes;

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
