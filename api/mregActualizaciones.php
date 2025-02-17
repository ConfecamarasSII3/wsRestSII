<?php

namespace api;

use api\API;

trait mregActualizaciones {

    public function mregAjustarVinculos(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');

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
        $api->validarParametro("_idclase", true);
        $api->validarParametro("_numid", true);
        $api->validarParametro("_idclasenew", true);
        $api->validarParametro("_numidnew", true);
        $api->validarParametro("_nombrenew", true);
        $api->validarParametro("_nombre1new", false);
        $api->validarParametro("_nombre2new", false);
        $api->validarParametro("_apellido1new", false);
        $api->validarParametro("_apellido2new", false);
        $api->validarParametro("_ids", true);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mregAjustarVinculos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mregAjustarVinculos ';            
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Pasa de base 64 a texto plano
        // ********************************************************************** // 
        $_SESSION["entrada"]["_idclase"] = base64_decode($_SESSION["entrada"]["_idclase"]);
        $_SESSION["entrada"]["_idclasenew"] = base64_decode($_SESSION["entrada"]["_idclasenew"]);
        $_SESSION["entrada"]["_numid"] = base64_decode($_SESSION["entrada"]["_numid"]);
        $_SESSION["entrada"]["_numidnew"] = base64_decode($_SESSION["entrada"]["_numidnew"]);
        $_SESSION["entrada"]["_nombrenew"] = base64_decode($_SESSION["entrada"]["_nombrenew"]);
        $_SESSION["entrada"]["_nombre1new"] = base64_decode($_SESSION["entrada"]["_nombre1new"]);
        $_SESSION["entrada"]["_nombre2new"] = base64_decode($_SESSION["entrada"]["_nombre2new"]);
        $_SESSION["entrada"]["_apellido1new"] = base64_decode($_SESSION["entrada"]["_apellido1new"]);
        $_SESSION["entrada"]["_apellido2new"] = base64_decode($_SESSION["entrada"]["_apellido2new"]);
        $_SESSION["entrada"]["_ids"] = base64_decode($_SESSION["entrada"]["_ids"]);


        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $arrVinculos = array ();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_codvinculos', "1=1","id");
        foreach ($temx as $tx) {
            $arrVinculos[$tx["id"]] = $tx;
        }
        unset ($temx);
        
        //
        $includes = '';
        $tx = explode("|", $_SESSION["entrada"]["_ids"]);
        foreach ($tx as $l) {
            if ($includes != '') {
                $includes .= ',';
            }
            $includes .= $l;
        }
        unset($tx);

        //
        $tems = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "id IN(" . $includes . ")", "matricula");

        //
        $mats = array ();
        foreach ($tems as $tx) {
            //
            if (!isset($mats[$tx["matricula"]])) {
                $mats[$tx["matricula"]] = $tx["matricula"];
            }
            
            // Log general
            $detalle = 'Cambio de identificación ... ' . chr(13) . chr(10);
            $detalle .= 'id (mreg_est_vinculos) : ' . $tx["id"] . chr(13) . chr(10);
            $detalle .= 'idclase original : ' . $tx["idclase"] . chr(13) . chr(10);
            $detalle .= 'numid original : ' . $tx["numid"] . chr(13) . chr(10);
            $detalle .= 'nombre original : ' . $tx["nombre"] . chr(13) . chr(10);
            $detalle .= 'primer apellido original : ' . $tx["ape1"] . chr(13) . chr(10);
            $detalle .= 'segundo apellido original : ' . $tx["ape2"] . chr(13) . chr(10);
            $detalle .= 'primer nombre original : ' . $tx["nom1"] . chr(13) . chr(10);
            $detalle .= 'segundo nombre original : ' . $tx["nom2"] . chr(13) . chr(10);
            $detalle .= 'por ... ' . chr(13) . chr(10);
            $detalle .= 'idclase nueva : ' . $_SESSION["entrada"]["_idclasenew"] . chr(13) . chr(10);
            $detalle .= 'numid nuevo : ' . $_SESSION["entrada"]["_numidnew"] . chr(13) . chr(10);
            $detalle .= 'nombre nuevo : ' . $_SESSION["entrada"]["_nombrenew"] . chr(13) . chr(10);
            $detalle .= 'primer apellido nuevo : ' . $_SESSION["entrada"]["_apellido1new"] . chr(13) . chr(10);
            $detalle .= 'segundo apellido nuevo : ' . $_SESSION["entrada"]["_apellido2new"] . chr(13) . chr(10);
            $detalle .= 'primer nombre nuevo : ' . $_SESSION["entrada"]["_nombre1new"] . chr(13) . chr(10);
            $detalle .= 'segundo nombre nuevo : ' . $_SESSION["entrada"]["_nombre2new"] . chr(13) . chr(10);
            actualizarLogMysqliApi($mysqli, '067', $_SESSION["generales"]["codigousuario"], 'mregAjustarVinculos.php', '', '', '', $detalle, $tx["matricula"]);

            //
            $tipomovimiento = 'vinculo-modificado-otros';
            if (isset($arrVinculos[$tx["vinculo"]])) {
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'SOC' || $arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'SOC') {
                    $tipomovimiento = 'vinculo-modificado-socios';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'JDP') {
                    $tipomovimiento = 'vinculo-modificado-jdp';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'JDS') {
                    $tipomovimiento = 'vinculo-modificado-jds';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'OAP') {
                    $tipomovimiento = 'vinculo-modificado-oap';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'OAS') {
                    $tipomovimiento = 'vinculo-modificado-oas';
                }                
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RLP'  || $arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'RLP') {
                    $tipomovimiento = 'vinculo-modificado-rlp';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RLPE') {
                    $tipomovimiento = 'vinculo-modificado-rlp';
                }                
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RLS' || $arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'RLS') {
                    $tipomovimiento = 'vinculo-modificado-rls';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RFP' || $arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'RFP') {
                    $tipomovimiento = 'vinculo-modificado-rfp';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RFDP') {
                    $tipomovimiento = 'vinculo-modificado-rfp';
                }                
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RFS' || $arrVinculos[$tx["vinculo"]]["tipovinculoesadl"] == 'RFS') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RFDS') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }                
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RFS1') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }
                if ($arrVinculos[$tx["vinculo"]]["tipovinculo"] == 'RFS2') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }                
            }
            
            // campos históricos
            $arrCampos1 = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );
            $arrValores1 = array(
                "'" . ltrim($tx["matricula"], "0") . "'",
                "'" . $tipomovimiento . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''", // Codigo de barras
                "'" . addslashes($tx["idclase"] . ':' . $tx["numid"] . ':' . $tx["nombre"] . ':' . $tx["vinculo"] . ':' . $tx["idlibro"] . ':' . $tx["numreg"]) . "'", // Datos originales
                "'" . addslashes($_SESSION["entrada"]["_idclasenew"] . ':' . $_SESSION["entrada"]["_numidnew"] . ':' . $_SESSION["entrada"]["_nombrenew"] . ':' . $tx["vinculo"] . ':' . $tx["idlibro"] . ':' . $tx["numreg"]) . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesGenerales::localizarIP() . "'",
                "'mregAjustarVinculos'",
                "''" // recibo
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . date("Y"), $arrCampos1, $arrValores1);

            $arrCampos = array(
                'idclase',
                'numid',
                'nombre',
                'ape1',
                'ape2',
                'nom1',
                'nom2'
            );
            $arrValores = array(
                "'" . $_SESSION["entrada"]["_idclasenew"] . "'",
                "'" . $_SESSION["entrada"]["_numidnew"] . "'",
                "'" . addslashes($_SESSION["entrada"]["_nombrenew"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["_apellido1new"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["_apellido2new"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["_nombre1new"]) . "'",
                "'" . addslashes($_SESSION["entrada"]["_nombre2new"]) . "'"
            );
            $resx = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', $arrCampos, $arrValores, "id=" . $tx["id"]);
            if ($resx === false) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error regrabando : ' . $_SESSION["generales"]["mensajeerror"];
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }

        // Actualiza la tabla mreg_est_inscritos como pendiente de sincronizar.
        foreach ($mats as $mx) {
            $arrCampos = array (
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc'
            );
            $arrValores = array (
                "'" . date ("Ymd") . "'",
                "'NO'",
                "'NO'",
                "'NO'"
            );
            regrabarRegistrosMysqliApi($mysqli,'mreg_est_inscritos',$arrCampos,$arrValores,"matricula='" . $mx . "'");
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
