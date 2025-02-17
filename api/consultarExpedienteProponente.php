<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

trait consultarExpedienteProponente
{

    public function consultarExpedienteProponente(API $api)
    {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR18N.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION['jsonsalida']['proponente'] = '';
        $_SESSION['jsonsalida']['matricula'] = '';
        $_SESSION['jsonsalida']['nombre'] = '';
        $_SESSION['jsonsalida']['sigla'] = '';
        $_SESSION['jsonsalida']['idclase'] = '';
        $_SESSION['jsonsalida']['identificacion'] = '';
        $_SESSION['jsonsalida']['nit'] = '';
        $_SESSION['jsonsalida']['organizacion'] = '';
        $_SESSION['jsonsalida']['estado'] = '';
        $_SESSION['jsonsalida']['fechainscripcion'] = '';
        $_SESSION['jsonsalida']['fecharenovacion'] = '';
        $_SESSION['jsonsalida']['fechacancelacion'] = '';
        $_SESSION['jsonsalida']['dircom'] = '';
        $_SESSION['jsonsalida']['muncom'] = '';
        $_SESSION['jsonsalida']['telcom1'] = '';
        $_SESSION['jsonsalida']['telcom2'] = '';
        $_SESSION['jsonsalida']['telcom3'] = '';
        $_SESSION['jsonsalida']['emailcom'] = '';
        $_SESSION['jsonsalida']['urlcom'] = '';
        $_SESSION['jsonsalida']['dirnot'] = '';
        $_SESSION['jsonsalida']['munnot'] = '';
        $_SESSION['jsonsalida']['telnot1'] = '';
        $_SESSION['jsonsalida']['telnot2'] = '';
        $_SESSION['jsonsalida']['telnot3'] = '';
        $_SESSION['jsonsalida']['emailnot'] = '';
        $_SESSION['jsonsalida']['idclaserl'] = '';
        $_SESSION['jsonsalida']['identificacionrl'] = '';
        $_SESSION['jsonsalida']['nombrerl'] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("proponente", false);
        $api->validarParametro("identificacion", false);

        if (trim($_SESSION["entrada"]["proponente"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indico proponente ni identificación a consultar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarExpedienteProponente', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el expediente indicado
        // ********************************************************************** // 
        $prop = trim((string)$_SESSION["entrada"]["proponente"]);
        $ideProp = trim((string)$_SESSION["entrada"]["identificacion"]);
        
        //
        $arrTemIns = array();
        $arrTemRup = array();
        
        //Valida la existencia del proponente en tablas maestras de inscritos y proponentes
        if ($prop != '') {
            $arrTemIns = retornarRegistroMysqliApi($mysqli, "mreg_est_inscritos", "proponente='" . $prop . "'");
            $arrTemRup = retornarRegistroMysqliApi($mysqli, "mreg_est_proponentes", "proponente='" . $prop . "'");
        } else {
            $arrTemIns = retornarRegistroMysqliApi($mysqli, "mreg_est_inscritos", "numid='" . $ideProp . "' or nit='" . $ideProp . "'");
            if ($arrTemIns && !empty ($arrTemIns)) {
                $prop = $arrTemIns["proponente"];
                $arrTemRup = retornarRegistroMysqliApi($mysqli, "mreg_est_proponentes", "proponente='" . $prop . "'");
            }
        }
        if ($arrTemIns === false || empty ($arrTemIns)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Proponente no localizado en la BD.(mreg_est_inscritos)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if ($arrTemRup === false || empty ($arrTemRup)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Proponente no localizado en la BD.(mreg_est_proponentes)';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $arrRepLegal = array();

        if ($arrTemIns["matricula"] != '' && 
        ($arrTemIns["ctrestmatricula"] == 'MA' || 
        $arrTemIns["ctrestmatricula"] == 'IA' || 
        $arrTemIns["ctrestmatricula"] == 'MR' || 
        $arrTemIns["ctrestmatricula"] == 'MI' || 
        $arrTemIns["ctrestmatricula"] == 'II' ||
        $arrTemIns["ctrestmatricula"] == 'IR')) {

            $arrTemx = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $arrTemIns["matricula"]);

            // Localiza vínculos de representación legal
            $i = 0;
            if (!empty($arrTemx["vinculos"])) {
                foreach ($arrTemx["vinculos"] as $v) {
                    if (
                        $v["tipovinculo"] == 'RLP' ||
                        $v["tipovinculo"] == 'RLS' ||
                        $v["tipovinculo"] == 'RLS1' ||
                        $v["tipovinculo"] == 'RLS2' ||
                        $v["tipovinculo"] == 'RLS3' ||
                        $v["tipovinculo"] == 'RLS4'
                    ) {
                        $i++;
                        $arrRepLegal["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $v["idtipoidentificacionotros"];
                        $arrRepLegal["representanteslegales"][$i]["identificacionrepleg"] = $v["identificacionotros"];
                        $arrRepLegal["representanteslegales"][$i]["paisrepleg"] = $v["paisotros"];
                        $arrRepLegal["representanteslegales"][$i]["nombrerepleg"] = $v["nombreotros"];
                        $arrRepLegal["representanteslegales"][$i]["cargorepleg"] = $v["cargootros"];
                        $arrRepLegal["representanteslegales"][$i]["nom1"] = $v["nombre1otros"];
                        $arrRepLegal["representanteslegales"][$i]["nom2"] = $v["nombre2otros"];
                        $arrRepLegal["representanteslegales"][$i]["ape1"] = $v["apellido1otros"];
                        $arrRepLegal["representanteslegales"][$i]["ape2"] = $v["apellido2otros"];
                    }
                }
            }
        } else {
            $arrTemVin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_representacion', "proponente='" . $arrTemRup["proponente"] . "'", "id");
            foreach ($arrTemVin as $v) {
                $i++;
                $arrRepLegal["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $v["tipoidentificacion"];
                $arrRepLegal["representanteslegales"][$i]["identificacionrepleg"] = $v["identificacion"];
                $arrRepLegal["representanteslegales"][$i]["paisrepleg"] = ''; // nace con circular 002
                $arrRepLegal["representanteslegales"][$i]["nombrerepleg"] = $v["nombre"];
                $arrRepLegal["representanteslegales"][$i]["cargorepleg"] = $v["cargo"];
                $arrRepLegal["representanteslegales"][$i]["nom1"] = '';
                $arrRepLegal["representanteslegales"][$i]["nom2"] = '';
                $arrRepLegal["representanteslegales"][$i]["ape1"] = '';
                $arrRepLegal["representanteslegales"][$i]["ape2"] = '';
            }
        }

        // ********************************************************************** //
        // Retornar el expediente recuperado
        // ********************************************************************** //


        $_SESSION['jsonsalida']['matricula'] = trim($arrTemIns["matricula"]);
        $_SESSION['jsonsalida']['proponente'] = trim($arrTemIns["proponente"]);
        $_SESSION['jsonsalida']['nombre'] = trim($arrTemIns["razonsocial"]);
        $_SESSION['jsonsalida']['sigla'] = trim($arrTemIns["sigla"]);
        $_SESSION['jsonsalida']['idclase'] = trim($arrTemIns["idclase"]);
        $_SESSION['jsonsalida']['identificacion'] = trim($arrTemIns["numid"]);
        $_SESSION['jsonsalida']['nit'] = trim($arrTemIns["nit"]);
        $_SESSION['jsonsalida']['organizacion'] = trim($arrTemIns["organizacion"]);
        $_SESSION['jsonsalida']['estado'] = trim($arrTemIns["ctrestproponente"]);
        $_SESSION['jsonsalida']['fechainscripcion'] = trim($arrTemRup["fechaultimainscripcion"]);
        $_SESSION['jsonsalida']['fecharenovacion'] = trim($arrTemRup["fechaultimarenovacion"]);
        $_SESSION['jsonsalida']['fechacancelacion'] = trim($arrTemRup["fechacancelacion"]);


        if ($arrTemIns["matricula"] != '' && 
        ($arrTemIns["ctrestmatricula"] == 'MA' || 
        $arrTemIns["ctrestmatricula"] == 'IA' || 
        $arrTemIns["ctrestmatricula"] == 'MR' || 
        $arrTemIns["ctrestmatricula"] == 'MI' || 
        $arrTemIns["ctrestmatricula"] == 'II' ||
        $arrTemIns["ctrestmatricula"] == 'IR')) {
            $_SESSION['jsonsalida']['dircom'] = trim((string)$arrTemIns["dircom"]);
            $_SESSION['jsonsalida']['muncom'] = trim((string)$arrTemIns["muncom"]);
            $_SESSION['jsonsalida']['telcom1'] = trim((string)$arrTemIns["telcom1"]);
            $_SESSION['jsonsalida']['telcom2'] = trim((string)$arrTemIns["telcom2"]);
            $_SESSION['jsonsalida']['telcom3'] = trim((string)$arrTemIns["celcom"]);
            $_SESSION['jsonsalida']['emailcom'] = trim((string)$arrTemIns["emailcom"]);
            $_SESSION['jsonsalida']['urlcom'] = "";
            $_SESSION['jsonsalida']['dirnot'] = trim((string)$arrTemIns["dirnot"]);
            $_SESSION['jsonsalida']['munnot'] = trim((string)$arrTemIns["munnot"]);
            $_SESSION['jsonsalida']['telnot1'] = trim((string)$arrTemIns["telnot"]);
            $_SESSION['jsonsalida']['telnot2'] = trim((string)$arrTemIns["telnot2"]);
            $_SESSION['jsonsalida']['telnot3'] = trim((string)$arrTemIns["celnot"]);
            $_SESSION['jsonsalida']['emailnot'] = trim((string)$arrTemIns["emailnot"]);
        } else {
            $_SESSION['jsonsalida']['dircom'] = trim((string)$arrTemRup["dircom"]);
            $_SESSION['jsonsalida']['muncom'] = trim((string)$arrTemRup["muncom"]);
            $_SESSION['jsonsalida']['telcom1'] = trim((string)$arrTemRup["telcom1"]);
            $_SESSION['jsonsalida']['telcom2'] = trim((string)$arrTemRup["telcom2"]);
            $_SESSION['jsonsalida']['telcom3'] = trim((string)$arrTemRup["celcom"]);
            $_SESSION['jsonsalida']['emailcom'] = trim((string)$arrTemRup["emailcom"]);
            $_SESSION['jsonsalida']['urlcom'] = "";
            $_SESSION['jsonsalida']['dirnot'] = trim((string)$arrTemRup["dirnot"]);
            $_SESSION['jsonsalida']['munnot'] = trim((string)$arrTemRup["munnot"]);
            $_SESSION['jsonsalida']['telnot1'] = trim((string)$arrTemRup["telnot"]);
            $_SESSION['jsonsalida']['telnot2'] = trim((string)$arrTemRup["telnot2"]);
            $_SESSION['jsonsalida']['telnot3'] = trim((string)$arrTemRup["celnot"]);
            $_SESSION['jsonsalida']['emailnot'] = trim((string)$arrTemRup["emailnot"]);
        }


        if ($arrTemRup) {

            $_SESSION['jsonsalida']["inffin1510_fechacorte"] = trim((string)$arrTemRup["inffin1510_fechacorte"]);
            $_SESSION['jsonsalida']["inffin1510_actcte"] = trim((string)$arrTemRup["inffin1510_actcte"]);
            $_SESSION['jsonsalida']["inffin1510_actnocte"] = trim((string)$arrTemRup["inffin1510_actnocte"]);
            $_SESSION['jsonsalida']["inffin1510_acttot"] = trim((string)$arrTemRup["inffin1510_acttot"]);
            $_SESSION['jsonsalida']["inffin1510_pascte"] = trim((string)$arrTemRup["inffin1510_pascte"]);
            $_SESSION['jsonsalida']["inffin1510_paslar"] = trim((string)$arrTemRup["inffin1510_paslar"]);
            $_SESSION['jsonsalida']["inffin1510_pastot"] = trim((string)$arrTemRup["inffin1510_pastot"]);
            $_SESSION['jsonsalida']["inffin1510_patnet"] = trim((string)$arrTemRup["inffin1510_patnet"]);
            $_SESSION['jsonsalida']["inffin1510_paspat"] = trim((string)$arrTemRup["inffin1510_paspat"]);
            $_SESSION['jsonsalida']["inffin1510_balsoc"] = trim((string)$arrTemRup["inffin1510_balsoc"]);
            $_SESSION['jsonsalida']["inffin1510_ingope"] = trim((string)$arrTemRup["inffin1510_ingope"]);
            $_SESSION['jsonsalida']["inffin1510_ingnoope"] = trim((string)$arrTemRup["inffin1510_ingnoope"]);
            $_SESSION['jsonsalida']["inffin1510_gasope"] = trim((string)$arrTemRup["inffin1510_gasope"]);
            $_SESSION['jsonsalida']["inffin1510_gasnoope"] = trim((string)$arrTemRup["inffin1510_gasnoope"]);
            $_SESSION['jsonsalida']["inffin1510_cosven"] = trim((string)$arrTemRup["inffin1510_cosven"]);
            $_SESSION['jsonsalida']["inffin1510_utinet"] = trim((string)$arrTemRup["inffin1510_utinet"]);
            $_SESSION['jsonsalida']["inffin1510_utiope"] = trim((string)$arrTemRup["inffin1510_utiope"]);
            $_SESSION['jsonsalida']["inffin1510_gasint"] = trim((string)$arrTemRup["inffin1510_gasint"]);
            $_SESSION['jsonsalida']["inffin1510_gasimp"] = trim((string)$arrTemRup["inffin1510_gasimp"]);
            $_SESSION['jsonsalida']["inffin1510_indliq"] = trim((string)$arrTemRup["inffin1510_indliq"]);
            $_SESSION['jsonsalida']["inffin1510_nivend"] = trim((string)$arrTemRup["inffin1510_nivend"]);
            $_SESSION['jsonsalida']["inffin1510_razcob"] = trim((string)$arrTemRup["inffin1510_razcob"]);
            $_SESSION['jsonsalida']["inffin1510_renpat"] = trim((string)$arrTemRup["inffin1510_renpat"]);
            $_SESSION['jsonsalida']["inffin1510_renact"] = trim((string)$arrTemRup["inffin1510_renact"]);


            $temx = retornarRegistroMysqli2($mysqli, 'bas_gruponiif', "id='" . $arrTemRup["gruponiif"] . "'");
            if ($temx && !empty($temx)) {
                $_SESSION['jsonsalida']["inffin1510_gruponiif"] = trim($temx["idformulario"]);
            } else {
                $_SESSION['jsonsalida']["inffin1510_gruponiif"] = '';
            }
            unset($temx);

            if (count($arrRepLegal["representanteslegales"]) > 0) {
                foreach ($arrRepLegal["representanteslegales"] as $rl) {
                    $_SESSION['jsonsalida']['idclaserl'] = trim((string)$rl['idtipoidentificacionrepleg']);
                    $_SESSION['jsonsalida']['identificacionrl'] = trim((string)$rl['identificacionrepleg']);
                    $_SESSION['jsonsalida']['nombrerl'] = trim((string)$rl['nombrerepleg']);
                    break;
                }
            }
        }

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }
}
