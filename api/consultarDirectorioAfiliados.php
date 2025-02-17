<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarDirectorioAfiliados {

    public function consultarDirectorioAfiliados(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');


        //cantidad de registros
        $limit = 50;

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $expedientes = array();
        $_SESSION["jsonsalida"]["total"] = '';
        $_SESSION["jsonsalida"]["expedientes"] = $expedientes;
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("semilla", true, false);
        $api->validarParametro("limite", false);
        $api->validarParametro("ciuucodigo", false);
        $api->validarParametro("ciuuletra", false);
        $limit = isset($_SESSION["entrada"]["limite"]) && $_SESSION["entrada"]["limite"] > 0 ? $_SESSION["entrada"]["limite"] : $limit;


        //
        $_SESSION["entrada"]["semilla"] = intval($_SESSION["entrada"]["semilla"]);

        if (!is_numeric($_SESSION["entrada"]["semilla"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Semilla no es un número entero';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarDirectorioAfiliados', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $tipoOrganizacion = array();
        $query = "SELECT * from bas_organizacionjuridica where 1=1 order by id";
        $mysqli->set_charset("utf8");
        $resQueryOrg = $mysqli->query($query);
        if (!empty($resQueryOrg)) {
            while ($orgTemp = $resQueryOrg->fetch_array(MYSQLI_ASSOC)) {
                $tipoOrganizacion[$orgTemp['id']] = mb_strtoupper($orgTemp['descripcion'], 'utf-8');
            }
        }
        $resQueryOrg->free();


        $tipoAfiliado = array();
        $tipoAfiliado[0] = "NO AFILIADO";
        $tipoAfiliado[1] = "AFILIACIÓN ACTIVA";
        $tipoAfiliado[2] = "DES-AFILIADO";
        $tipoAfiliado[3] = "ACEPTADO";
        $tipoAfiliado[5] = "DESAFILIACIÓN TEMPORAL";

        // ********************************************************************** //
        // Buscar expedientes
        // ********************************************************************** //  

        $consulta["retornar"] = $limit;
        $consulta["offset"] = 0;
        $filtroEstablecimiento = '';
        $filtroCodigoCiuu = "";
        $filtroLetraCiuu = "";
        $filtroEstados = "";
        $mostrarcancelados = "";
        $soloestablecimientos = 'N';

        if (isset($_SESSION["entrada"]["ciuucodigo"])) {
            $filtroCodigoCiuu = " and( ciiu1 like '%" . $_SESSION["entrada"]["ciuucodigo"] . "' "
                    . "or ciiu2 like '%" . $_SESSION["entrada"]["ciuucodigo"] . "' or ciiu3 like '%" . $_SESSION["entrada"]["ciuucodigo"] . "' "
                    . "or ciiu4 like '%" . $_SESSION["entrada"]["ciuucodigo"] . "' or ciiu5 like '%" . $_SESSION["entrada"]["ciuucodigo"] . "')";
        }

        if (isset($_SESSION["entrada"]["ciuuletra"])) {
            $filtroLetraCiuu = " and( ciiu1 like '" . $_SESSION["entrada"]["ciuuletra"] . "%' "
                    . "or ciiu2 like '" . $_SESSION["entrada"]["ciuuletra"] . "%' or ciiu3 like '" . $_SESSION["entrada"]["ciuuletra"] . "%' "
                    . "or ciiu4 like '" . $_SESSION["entrada"]["ciuuletra"] . "%' or ciiu5 like '" . $_SESSION["entrada"]["ciuuletra"] . "%')";
        }

        if ($mostrarcancelados != 'S') {
            $filtroEstados = " and (ctrestmatricula IN ('MA','MI','IA','II'))";
        }
        if ($_SESSION["entrada"]["semilla"] != '0') {
            $consulta["offset"] = $_SESSION["entrada"]["semilla"] * $limit;
        }
        $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "ctrafiliacion=1 " . $filtroEstablecimiento . $filtroCodigoCiuu . $filtroLetraCiuu, "razonsocial", "matricula,razonsocial,emailcom,muncom,dircom,telcom1,emailcom2,urlcom,ctrafiliacion", $consulta["offset"], $consulta["retornar"]);

        $arrTem = array();

        if ($reg === false || empty($reg)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No encontraron registros que cumplan con el criterio indicado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            return array();
        } else {
            $i = -1;
            foreach ($reg as $rg) {
                $i++;
                $arrTem[$i]["matricula"] = $rg["matricula"];
                $arrTem[$i]["razonsocial"] = $rg["razonsocial"];
                $arrTem[$i]["emailcom"] = $rg["emailcom"];
                $arrTem[$i]["muncom"] = $rg["muncom"];
                $arrTem[$i]["dircom"] = $rg["dircom"];
                $arrTem[$i]["telcom1"] = $rg["telcom1"];
                $arrTem[$i]["emailcom2"] = $rg["emailcom2"];
                $arrTem[$i]["urlcom"] = $rg["urlcom"];
                $arrTem[$i]["afiliacion"] = $rg["ctrafiliacion"];
            }
        }

        // **************************************************************************** //
        // Construye salida API
        // **************************************************************************** //


        foreach ($arrTem as $expedienteInfo) {


            $arrayExpresp = array();

            $arrayExpresp["matricula"] = trim($expedienteInfo["matricula"]);
            $arrayExpresp["razonsocial"] = trim($expedienteInfo["razonsocial"]);
            $arrayExpresp["correoelectronico"] = trim($expedienteInfo["emailcom"]);
            $arrayExpresp["municipiocomercial"] = trim($expedienteInfo["muncom"]); //
            $arrayExpresp["municipiotextual"] = retornarNombreMunicipioMysqliApi($mysqli, trim($expedienteInfo["muncom"]));
            $arrayExpresp["direccioncomercial"] = trim($expedienteInfo["dircom"]);
            $arrayExpresp["telefonocomercial"] = trim($expedienteInfo["telcom1"]);
            $arrayExpresp["correo"] = trim($expedienteInfo["emailcom2"]);
            $arrayExpresp["urlweb"] = trim($expedienteInfo["urlcom"]);
            $arrayExpresp["afiliado"] = trim($expedienteInfo["afiliacion"]);
            $arrayExpresp["afiliadotextual"] = isset($tipoAfiliado[$arrayExpresp["afiliado"]]) ? $tipoAfiliado[$arrayExpresp["afiliado"]] : "";
            $arrExpedientes[] = $arrayExpresp;
        }
        $_SESSION["jsonsalida"]["total"] = count($arrExpedientes);
        $_SESSION["jsonsalida"]["expedientes"] = $arrExpedientes;

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
