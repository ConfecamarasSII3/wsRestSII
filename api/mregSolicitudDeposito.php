<?php

namespace api;

use api\API;

trait mregSolicitudDeposito {

    public function mregSolicitudDepositoBorrarAnexo(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idanexo", true);

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregSolicitudDepositoBorrarAnexo', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesBorrarAnexoSolicitudBloque ';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . base64_decode($_SESSION["entrada"]["idanexo"]));
        if ($temx && !empty($temx)) {
            unlink(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $temx["path"] . base64_decode($_SESSION["entrada"]["idanexo"]) . '.pdf');
            borrarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idanexo=" . base64_decode($_SESSION["entrada"]["idanexo"]));
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

    public function mregSolicitudDepositoValidarAnexos(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("numerorecuperacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregSolicitudDepositoValidarAnexos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesBorrarAnexoSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Recupera la liquidación
        // ********************************************************************** // 
        $temx = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numerorecuperacion='" . $_SESSION["entrada"]["numerorecuperacion"] . "'");
        if ($temx === false || empty($temx)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada en el sistema';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $cuantosbalances = 0;
        $cuantosanexos = 0;
        $temx1 = retornarRegistrosMysqliApi($mysqli, 'mreg_publicacion_balances', "idliquidacion=" . $temx["idliquidacion"], "id");
        if ($temx1 && !empty($temx1)) {
            foreach ($temx1 as $tx1) {
                $cuantosbalances++;
                $temx2 = retornarRegistroMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $temx["idliquidacion"] . " and identificador='" . $tx1["identificador"] . "'");
                if ($temx2 && !empty($temx2)) {
                    $cuantosanexos++;
                }
            }
        }

        if ($cuantosbalances == 0) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se han indicado los informes a depositar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        if ($cuantosbalances > $cuantosanexos) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Faltan adocumentos (pdfs) por anexar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $mysqli->close();
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

    public function mregSolicitudDepositoGrabar(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';

        //
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("_numerorecuperacion", true);
        $api->validarParametro("_anodeposito", false);
        $api->validarParametro("_fechabalgen", false);
        $api->validarParametro("_foliosbalgen", false);
        $api->validarParametro("_fechaestres", false);
        $api->validarParametro("_foliosestres", false);
        $api->validarParametro("_fechafluefe", false);
        $api->validarParametro("_foliosfluefe", false);
        // $api->validarParametro("_fechasitfin", false);
        // $api->validarParametro("_foliossitfin", false);
        $api->validarParametro("_fechacampat", false);
        $api->validarParametro("_folioscampat", false);
        $api->validarParametro("_fechaotros", false);
        $api->validarParametro("_foliosotros", false);
        $api->validarParametro("_fechanotas", false);
        $api->validarParametro("_foliosnotas", false);
        $api->validarParametro("_fechadictamen", false);
        $api->validarParametro("_foliosdictamen", false);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregSolicitudDepositoGrabar', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método ruesGrabarSolicitudBloque ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $cuantosbalances = 0;
        $cuantosanexos = 0;

        $temx = retornarRegistroMysqliApi(null, 'mreg_liquidacion', "numerorecuperacion='" . base64_decode($_SESSION["entrada"]["_numerorecuperacion"]) . "'");
        if ($temx && !empty($temx)) {

            //
            borrarRegistrosMysqliApi(null, 'mreg_publicacion_balances', "idliquidacion=" . $temx["idliquidacion"]);

            //
            $arrCampos = array(
                'idliquidacion',
                'matricula',
                'ano',
                'identificador',
                'folios',
                'fechainforme',
                'codigobarras',
                'recibo',
                'fecharadicacion',
                'horaradicacion'
            );
            $arrValores = array();
            if (intval(base64_decode($_SESSION["entrada"]["_foliosbalgen"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-balgen'",
                    intval(base64_decode($_SESSION["entrada"]["_foliosbalgen"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechabalgen"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            if (intval(base64_decode($_SESSION["entrada"]["_foliosestres"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-estres'",
                    intval(base64_decode($_SESSION["entrada"]["_foliosestres"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechaestres"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            if (intval(base64_decode($_SESSION["entrada"]["_foliosfluefe"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-fluefe'",
                    intval(base64_decode($_SESSION["entrada"]["_foliosfluefe"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechafluefe"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            if (intval(base64_decode($_SESSION["entrada"]["_folioscampat"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-campat'",
                    intval(base64_decode($_SESSION["entrada"]["_folioscampat"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechacampat"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            /*
              if (intval(base64_decode($_SESSION["entrada"]["_foliossitfin"])) != 0) {
              $arrValores[] = array(
              $temx["idliquidacion"],
              "'" . $temx["idexpedientebase"] . "'",
              "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
              "'pubbal-sitfin'",
              intval(base64_decode($_SESSION["entrada"]["_foliossitfin"])),
              "'" . base64_decode($_SESSION["entrada"]["_fechasitfin"]) . "'",
              "''",
              "''",
              "''",
              "''"
              );
              }
             */
            if (intval(base64_decode($_SESSION["entrada"]["_foliosotros"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-otros'",
                    intval(base64_decode($_SESSION["entrada"]["_foliosotros"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechaotros"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            if (intval(base64_decode($_SESSION["entrada"]["_foliosnotas"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-notas'",
                    intval(base64_decode($_SESSION["entrada"]["_foliosnotas"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechanotas"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            if (intval(base64_decode($_SESSION["entrada"]["_foliosdictamen"])) != 0) {
                $arrValores[] = array(
                    $temx["idliquidacion"],
                    "'" . $temx["idexpedientebase"] . "'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'",
                    "'pubbal-dictamen'",
                    intval(base64_decode($_SESSION["entrada"]["_foliosdictamen"])),
                    "'" . base64_decode($_SESSION["entrada"]["_fechadictamen"]) . "'",
                    "''",
                    "''",
                    "''",
                    "''"
                );
                $cuantosbalances++;
            }
            if (!empty($arrValores)) {
                insertarRegistrosBloqueMysqliApi(null, 'mreg_publicacion_balances', $arrCampos, $arrValores);
            }

            //
            borrarRegistrosMysqliApi(null, 'mreg_liquidacion_campos', "idliquidacion=" . $temx["idliquidacion"] . " and campo='anodeposito'");

            //
            if (base64_decode($_SESSION["entrada"]["_anodeposito"]) != '') {
                $arrCampos = array(
                    'idliquidacion',
                    'campo',
                    'contenido'
                );
                $arrValores = array(
                    $temx["idliquidacion"],
                    "'anodeposito'",
                    "'" . base64_decode($_SESSION["entrada"]["_anodeposito"]) . "'"
                );
                insertarRegistrosMysqliApi(null, 'mreg_liquidacion_campos', $arrCampos, $arrValores);
            }

            $sops = retornarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', "idliquidacion=" . $temx["idliquidacion"], "idanexo");
            if ($sops && !empty($sops)) {
                foreach ($sops as $s) {
                    if ($s["identificador"] == 'pubbal-balgen') {
                        $txt = 'BALANCE GENERAL AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                    if ($s["identificador"] == 'pubbal-estres') {
                        $txt = 'ESTADO DE RESULTADOS AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                    if ($s["identificador"] == 'pubbal-fluefe') {
                        $txt = 'FLUJO DE EFECTIVO AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                    /*
                      if ($s["identificador"] == 'pubbal-sitfin') {
                      $txt = 'SITUACIÓN FINANCIERA AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                      $arrCampos = array('observaciones');
                      $arrValores = array("'" . addslashes($txt) . "'");
                      regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                      }
                     */
                    if ($s["identificador"] == 'pubbal-campat') {
                        $txt = 'CAMBIOS EN EL PATRIMONIO AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                    if ($s["identificador"] == 'pubbal-otros') {
                        $txt = 'OTROS INFORMES COMPLEMENTARIOS DE LOS ESTADOS FINANCIEROS AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                    if ($s["identificador"] == 'pubbal-notas') {
                        $txt = 'NOTAS A LOS ESTADOS FINANCIEROS AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                    if ($s["identificador"] == 'pubbal-dictamen') {
                        $txt = 'DICTAMEN DE ESTADOS FINANCIEROS AÑO ' . base64_decode($_SESSION["entrada"]["_anodeposito"]);
                        $arrCampos = array('observaciones');
                        $arrValores = array("'" . addslashes($txt) . "'");
                        regrabarRegistrosMysqliApi(null, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, "idanexo=" . $s["idanexo"]);
                        $cuantosanexos++;
                    }
                }
            }
        }

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
