<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait construirRelacionInscritos {

    public function construirRelacionInscritos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
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
        //$api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("ciuucodigos", false);
        $api->validarParametro("ciuuletra", false);
        $api->validarParametro("organizacion", false);
        $api->validarParametro("categoria", false);
        $api->validarParametro("afiliado", false);
        $api->validarParametro("estadomatricula", false);
        $api->validarParametro("fechasmatricula", false);
        $api->validarParametro("fechasrenovacion", false);
        $api->validarParametro("fechascancelacion", false);
        $api->validarParametro("anorenovacion", false);
        $api->validarParametro("empleados", false);
        $api->validarParametro("exporta", false);
        $api->validarParametro("generaarchivo", false);
        $api->validarParametro("aniorenovacion", false);
        $api->validarParametro("activos", false);
        $api->validarParametro("municipio", false);
        $api->validarParametro("barrio", false);
        $limit = isset($_SESSION["entrada"]["limite"]) && $_SESSION["entrada"]["limite"] > 0 && $_SESSION["entrada"]["limite"] <= 50 ? $_SESSION["entrada"]["limite"] : $limit;

        //Si confirmo generar el archivo solo muestro 5 registros de muestra
        if ($_SESSION["entrada"]["generaarchivo"] == "si")
            $limit = 5;

        $_SESSION["entrada"]["semilla"] = intval($_SESSION["entrada"]["semilla"]);
        if (!is_numeric($_SESSION["entrada"]["semilla"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Semilla no es un número entero';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('construirRelacionInscritos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
        $tipoAfiliado[9] = "POTENCIAL AFILIADO";

        // ********************************************************************** //
        // Buscar expedientes
        // ********************************************************************** //  

        $consulta["retornar"] = $limit;
        $consulta["offset"] = 0;
        $filtroCodigoCiuu = "";
        $filtroLetraCiuu = "";
        $filtroEstados = "";
        $mostrarcancelados = "";

        if ($_SESSION["entrada"]["organizacion"] != '') {
            $arr["Organizaciones"] = explode(",", $_SESSION["entrada"]["organizacion"]);
        } else {
            $arr["Organizaciones"] = array();
        }
        if ($_SESSION["entrada"]["categoria"] != '') {
            $arr["Categorias"] = explode(",", $_SESSION["entrada"]["categoria"]);
        } else {
            $arr["Categorias"] = array();
        }

        if ($_SESSION["entrada"]["estadomatricula"] != '') {
            $arr["EstadoMatriculas"] = explode(",", $_SESSION["entrada"]["estadomatricula"]);
        } else {
            $arr["EstadoMatriculas"] = array();
        }
        if ($_SESSION["entrada"]["activos"] != '') {
            $arr["Activos"] = explode(",", $_SESSION["entrada"]["activos"]);
        } else {
            $arr["Activos"] = array();
        }
        if ($_SESSION["entrada"]["fechasmatricula"] != '') {
            $arr["FecMatricula"] = explode(",", $_SESSION["entrada"]["fechasmatricula"]);
        } else {
            $arr["FecMatricula"] = array();
        }
        if ($_SESSION["entrada"]["fechasrenovacion"] != '') {
            $arr["FecRenovacion"] = explode(",", $_SESSION["entrada"]["fechasrenovacion"]);
        } else {
            $arr["FecRenovacion"] = array();
        }
        if ($_SESSION["entrada"]["fechascancelacion"] != '') {
            $arr["FecCancelacion"] = explode(",", $_SESSION["entrada"]["fechascancelacion"]);
        } else {
            $arr["FecCancelacion"] = array();
        }
        if ($_SESSION["entrada"]["aniorenovacion"] != '') {
            $arr["AnosRenovacion"] = explode(",", $_SESSION["entrada"]["aniorenovacion"]);
        } else {
            $arr["AnosRenovacion"] = array();
        }

        if ($_SESSION["entrada"]["ciuucodigo"] != '') {
            $arr["Ciiucodigos"] = explode(",", $_SESSION["entrada"]["ciuucodigo"]);
        } else {
            $arr["Ciiucodigos"] = array();
        }
        if ($_SESSION["entrada"]["ciuuletra"] != '') {
            $arr["Ciiuletras"] = explode(",", $_SESSION["entrada"]["ciuuletra"]);
        } else {
            $arr["Ciiuletras"] = array();
        }

        if ($_SESSION["entrada"]["empleados"] != '') {
            $arr["Empleados"] = explode(",", $_SESSION["entrada"]["empleados"]);
        } else {
            $arr["Empleados"] = array();
        }

        if ($_SESSION["entrada"]["municipio"] != '') {
            $arr["Municipios"] = explode(",", $_SESSION["entrada"]["municipio"]);
        } else {
            $arr["Municipios"] = array();
        }

        if ($_SESSION["entrada"]["barrio"] != '') {
            $arr["Barrios"] = explode(",", $_SESSION["entrada"]["barrio"]);
        } else {
            $arr["Barrios"] = array();
        }

        if ($_SESSION["entrada"]["semilla"] != '0') {
            $consulta["offset"] = $_SESSION["entrada"]["semilla"] * $limit;
        }

        //
        $where = "(matricula <>'')";

        if (strtoupper($_SESSION["entrada"]["afiliado"]) == 'SI') {
            $where .= " and (ctrafiliacion = '1')";
        }

        //
        if (!empty($arr["EstadoMatriculas"])) {
            $atxt = '';
            foreach ($arr["EstadoMatriculas"] as $atx) {
                if ($atxt != '') {
                    $atxt .= ",";
                }
                $atxt .= "'" . $atx . "'";
            }
            $where .= " and (ctrestmatricula IN (" . $atxt . "))";
        }

        //
        if (!empty($arr["Categorias"])) {
            $atxt = '';
            foreach ($arr["Categorias"] as $atx) {
                if ($atxt != '') {
                    $atxt .= ",";
                }
                $atxt .= "'" . $atx . "'";
            }
            $where .= " and (categoria IN (" . $atxt . "))";
        }

        if (!empty($arr["Organizaciones"])) {
            $atxt = '';
            foreach ($arr["Organizaciones"] as $atx) {
                if ($atxt != '') {
                    $atxt .= ",";
                }
                $atxt .= "'" . sprintf("%02s", $atx) . "'";
            }
            $where .= " and (organizacion IN (" . $atxt . "))";
        }

        //
        if (!empty($arr["FecMatricula"])) {
            if (trim($arr["FecMatricula"][0]) != '') {
                $where .= " and (fecmatricula >= '" . $arr["FecMatricula"][0] . "')";
            }
            if (isset($arr["FecMatricula"][1]) && trim($arr["FecMatricula"][1]) != '') {
                $where .= " and (fecmatricula <= '" . $arr["FecMatricula"][1] . "')";
            }
        }

        //
        if (!empty($arr["FecRenovacion"])) {
            if (trim($arr["FecRenovacion"][0]) != '') {
                $where .= " and (fecrenovacion >= '" . $arr["FecRenovacion"][0] . "')";
            }
            if (isset($arr["FecRenovacion"][1]) && trim($arr["FecRenovacion"][1]) != '') {
                $where .= " and (fecrenovacion <= '" . $arr["FecRenovacion"][1] . "')";
            }
        }

        //
        if (!empty($arr["FecCancelacion"])) {
            if (trim($arr["FecCancelacion"][0]) != '') {
                $where .= " and (feccancelacion >= '" . $arr["FecCancelacion"][0] . "')";
            }
            if (isset($arr["FecCancelacion"][1]) && trim($arr["FecCancelacion"][1]) != '') {
                $where .= " and (feccancelacion <= '" . $arr["FecCancelacion"][1] . "')";
            }
        }

        //
        if (!empty($arr["AnosRenovacion"])) {
            if (trim($arr["AnosRenovacion"][0]) != '' && !isset($arr["AnosRenovacion"][1])) {
                $where .= " and (ultanoren = '" . $arr["AnosRenovacion"][0] . "')";
            }
            if (trim($arr["AnosRenovacion"][0]) != '' && isset($arr["AnosRenovacion"][1])) {
                $where .= " and (ultanoren >= '" . $arr["AnosRenovacion"][0] . "')";
            }
            if (isset($arr["AnosRenovacion"][1]) && trim($arr["AnosRenovacion"][1]) != '') {
                $where .= " and (ultanoren <= '" . $arr["AnosRenovacion"][1] . "')";
            }
        }

        //
        if (!empty($arr["Empleados"])) {
            if (trim($arr["Empleados"][0]) != '') {
                $where .= " and (personal >= " . $arr["Empleados"][0] . ")";
            }
            if (isset($arr["Empleados"][1]) && trim($arr["Empleados"][1]) != '') {
                $where .= " and (personal <= " . $arr["Empleados"][1] . ")";
            }
        }

        //
        if (!empty($arr["Municipios"])) {
            if (trim($arr["Municipios"][0]) != '' && !isset($arr["Municipios"][1])) {
                $where .= " and (muncom = '" . $arr["Municipios"][0] . "')";
            }
            if (trim($arr["Municipios"][0]) != '' && isset($arr["Municipios"][1])) {
                $where .= " and (muncom >= '" . $arr["Municipios"][0] . "')";
            }
            if (isset($arr["Municipios"][1]) && trim($arr["Municipios"][1]) != '') {
                $where .= " and (muncom <= '" . $arr["Municipios"][1] . "')";
            }
        }

        //
        if (!empty($arr["Barrios"])) {
            if (trim($arr["Barrios"][0]) != '' && !isset($arr["Barrios"][1])) {
                $where .= " and (barriocom = '" . sprintf("%05s", $arr["Barrios"][0]) . "')";
            }
            if (trim($arr["Barrios"][0]) != '' && isset($arr["Barrios"][1])) {
                $where .= " and (barriocom >= '" . sprintf("%05s", $arr["Barrios"][0]) . "')";
            }
            if (isset($arr["Barrios"][1]) && trim($arr["Barrios"][1]) != '') {
                $where .= " and (barriocom <= '" . sprintf("%05s", $arr["Barrios"][1]) . "')";
            }
        }

        if (strtoupper($_SESSION["entrada"]["exporta"]) == 'SI') {
            $where .= " and (ctrimpexp > '0')";
        }

        //
        if (!empty($arr["Activos"])) {
            $where .= " and (";
            $where .= "((organizacion = '01' or organizacion > '02') ";
            if (trim($arr["Activos"][0]) != '') {
                $where .= " and (acttot >= " . $arr["Activos"][0] . ")";
            }
            if (isset($arr["Activos"][1]) && trim($arr["Activos"][1]) != '') {
                $where .= " and (acttot <= " . $arr["Activos"][1] . ")";
            }
            $where .= ") or (";
            $where .= "((organizacion = '02' or categoria In ('2','3')) ";
            if (trim($arr["Activos"][0]) != '') {
                $where .= " and (actvin >= " . $arr["Activos"][0] . ")";
            }
            if (isset($arr["Activos"][1]) && trim($arr["Activos"][1]) != '') {
                $where .= " and (actvin <= " . $arr["Activos"][1] . ")";
            }
            $where .= ")";
            $where .= ")";
            $where .= ")";
        }

        //
        if (!empty($arr["Ciuuletras"])) {
            $where .= " and (";
            $ctosciius = 0;
            foreach ($arr["Ciuuletras"] as $cl) {
                $ctosciius++;
                if ($ctosciius > 1) {
                    $where .= " or ";
                }
                $where .= "(ciiu1 like '" . $cl . "%' or ciiu2 like '" . $cl . "%' or ciiu3 like '" . $cl . "%' or ciiu4 like '" . $cl . "%')";
            }
            $where .= ")";
        }

        //
        if (!empty($arr["Ciuucodigos"])) {
            $where .= " and (";
            $ctosciius = 0;
            foreach ($arr["Ciuucodigos"] as $cl) {
                $ctosciius++;
                if ($ctosciius > 1) {
                    $where .= " or ";
                }
                $where .= "(ciiu1 like '" . $cl . "%' or ciiu2 like '" . $cl . "%' or ciiu3 like '" . $cl . "%' or ciiu4 like '" . $cl . "%')";
            }
            $where .= ")";
        }


        //
        $reg = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos ins', $where, "razonsocial",
                "matricula, ctrestmatricula, organizacion,categoria, razonsocial,nombre1, nombre2, apellido1, apellido2, nit,fecmatricula,acttot,actvin,personal,fecconstitucion,personal, ctrclaseespeesadl, ctrimpexp,fecrenovacion, ultanoren, feccancelacion, dircom,muncom,barriocom,telcom1,emailcom,"
                . "ciiu1,actividad,(select nombre from mreg_est_vinculos vin where ins.matricula=vin.matricula and vinculo in (select id from mreg_codvinculos where tipovinculo='RLP') and estado='V'  order by id desc limit 1) as rlp"
                . " ", $consulta["offset"], $consulta["retornar"]);

        $count = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $where);

        if ($_SESSION["entrada"]["generaarchivo"] == "si") {
            $var1 = escapeshellcmd($_SESSION["generales"]["codigoempresa"]);
            $var2 = escapeshellcmd($_SESSION ["generales"] ["pathabsoluto"]);
            $var3 = escapeshellcmd(base64_encode($where));
            $var4 = escapeshellcmd("Inscritos-" . date("Ymd") . '-' . date("His") . '.csv');
            $orden1 = "";
            $uploaddir = $_SESSION ["generales"] ["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '/';
            if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                echo "error creando directorio.";
            }
            $orden = system("php " . $var2 . "librerias/proceso/mregArchivoRelacionInscritos.php $var1 $var2 $var3 $var4 >>" . $_SESSION ["generales"] ["pathabsoluto"] . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "/archivoInscritos.txt 2>>" . $_SESSION ["generales"] ["pathabsoluto"] . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "/errorarchivoInscritos.txt&", $orden1);
            $orden .= "-" . $orden1; //$_SESSION["jsonsalida"]["res"] = $orden;
            //$_SESSION["jsonsalida"]["contrr"] = $var3;
            //$_SESSION["jsonsalida"]["path"] = $_SESSION ["generales"] ["pathabsoluto"];
            $_SESSION["jsonsalida"]["empresa"] = $_SESSION["generales"]["codigoempresa"];
            $siexporto = "si";
        } else {
            $siexporto = "no";
        }
        $arrTem = array();
        if ($reg === false || empty($reg)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "0000";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No encontraron registros que cumplan con el criterio indicado';
            $_SESSION["jsonsalida"]["control"] = $where;
            $_SESSION["jsonsalida"]["total"] = $count;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
            return array();
        } else {
            $i = -1;
            foreach ($reg as $rg) {
                $i++;
                $arrTem[$i]["matricula"] = $rg["matricula"];
                $arrTem[$i]["estado"] = $rg["ctrestmatricula"];
                $arrTem[$i]["organizacion"] = $rg["organizacion"];
                $arrTem[$i]["categoria"] = $rg["categoria"];
                $arrTem[$i]["razonsocial"] = $rg["razonsocial"];
                $arrTem[$i]["nombre1"] = $rg["nombre1"];
                $arrTem[$i]["nombre2"] = $rg["nombre2"];
                $arrTem[$i]["apellido1"] = $rg["apellido1"];
                $arrTem[$i]["apellido2"] = $rg["apellido2"];
                $arrTem[$i]["nit"] = $rg["nit"];
                $arrTem[$i]["fecmatricula"] = $rg["fecmatricula"];
                $arrTem[$i]["fecconstitucion"] = $rg["fecconstitucion"];
                $arrTem[$i]["fecrenovacion"] = $rg["fecrenovacion"];
                $arrTem[$i]["ultanoren"] = $rg["ultanoren"];
                $arrTem[$i]["feccancelacion"] = $rg["feccancelacion"];
                $arrTem[$i]["dircom"] = $rg["dircom"];
                $arrTem[$i]["muncom"] = $rg["muncom"];
                $arrTem[$i]["barriocom"] = $rg["barriocom"];
                $arrTem[$i]["telcom1"] = $rg["telcom1"];
                $arrTem[$i]["emailcom"] = $rg["emailcom"];
                $arrTem[$i]["ciiu1"] = $rg["ciiu1"];
                $arrTem[$i]["actividad"] = $rg["actividad"];
                $arrTem[$i]["numempleados"] = $rg["personal"];
                $arrTem[$i]["ctrclaseespeesadl"] = $rg["ctrclaseespeesadl"];
                $arrTem[$i]["representantelegal"] = $rg["rlp"];
                $arrTem[$i]["ctrimpexp"] = $rg["ctrimpexp"];
                $arrTem[$i]["acttot"] = $rg["acttot"];
                $arrTem[$i]["actvin"] = $rg["actvin"];
                $arrTem[$i]["personal"] = $rg["personal"];
            }
        }

        // **************************************************************************** //
        // Construye salida API
        // **************************************************************************** //


        foreach ($arrTem as $expedienteInfo) {


            $arrayExpresp = array();
            $arrayExpresp["matricula"] = trim($expedienteInfo["matricula"]);
            $arrayExpresp["estadomatricula"] = trim($expedienteInfo["estado"]);
            $arrayExpresp["organizacionjuridica"] = trim($expedienteInfo["organizacion"]);
            $arrayExpresp["categoria"] = trim($expedienteInfo["categoria"]);
            $arrayExpresp["razonsocial"] = trim($expedienteInfo["razonsocial"]);
            $arrayExpresp["nombre1"] = trim($expedienteInfo["nombre1"]);
            $arrayExpresp["nombre2"] = trim($expedienteInfo["nombre2"]);
            $arrayExpresp["apellido1"] = trim($expedienteInfo["apellido1"]);
            $arrayExpresp["apellido2"] = trim($expedienteInfo["apellido2"]);
            $arrayExpresp["nit"] = trim($expedienteInfo["nit"]);
            $arrayExpresp["fecmatricula"] = trim($expedienteInfo["fecmatricula"]);
            $arrayExpresp["fecconstitucion"] = trim($expedienteInfo["fecconstitucion"]);
            $arrayExpresp["fecrenovacion"] = trim($expedienteInfo["fecrenovacion"]);
            $arrayExpresp["anorenovacion"] = trim($expedienteInfo["ultanoren"]);
            $arrayExpresp["feccancelacion"] = trim($expedienteInfo["feccancelacion"]);
            $arrayExpresp["direccioncomercial"] = trim($expedienteInfo["dircom"]);
            $arrayExpresp["municipiocomercial"] = trim($expedienteInfo["muncom"]); //
            $arrayExpresp["municipiotextual"] = retornarNombreMunicipioMysqli2($mysqli, trim($expedienteInfo["muncom"]));
            $arrayExpresp["barriocomercial"] = trim($expedienteInfo["barriocom"]); //
            $arrayExpresp["barriotextual"] = retornarNombreBarrioMysqli2($mysqli, trim($expedienteInfo["muncom"]), trim($expedienteInfo["barriocom"]));
            $arrayExpresp["telefonocomercial"] = trim($expedienteInfo["telcom1"]);
            $arrayExpresp["correoelectronico"] = trim($expedienteInfo["emailcom"]);
            $arrayExpresp["codigociiu"] = trim($expedienteInfo["ciiu1"]);
            $arrayExpresp["descripcionciiu"] = trim($expedienteInfo["actividad"]);
            $arrayExpresp["claseespecialesadl"] = trim($expedienteInfo["ctrclaseespeesadl"]);
            $arrayExpresp["numeroempleados"] = trim($expedienteInfo["personal"]);
            $arrayExpresp["representantelegal"] = trim($expedienteInfo["representantelegal"]);
            $arrayExpresp["importaexporta"] = trim($expedienteInfo["ctrimpexp"]);
            $arrayExpresp["personal"] = trim($expedienteInfo["personal"]);
            if ($expedienteInfo["organizacion"] == '02' || $expedienteInfo["categoria"] == '2' || $expedienteInfo["categoria"] == '3') {
                $arrayExpresp["activos"] = trim($expedienteInfo["actvin"]);
            } else {
                $arrayExpresp["activos"] = trim($expedienteInfo["acttot"]);
            }
            $arrExpedientes[] = $arrayExpresp;
        }
        $_SESSION["jsonsalida"]["control"] = $where . "|Export:" . $siexporto;
        $_SESSION["jsonsalida"]["total"] = $count;
        $_SESSION["jsonsalida"]["archivo"] = HTTP_HOST . "/tmp/" . $_SESSION["generales"]["codigoempresa"] . "/" . $var4;
        $_SESSION["jsonsalida"]["expedientes"] = $arrExpedientes;
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
