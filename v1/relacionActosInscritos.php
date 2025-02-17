<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait relacionActosInscritos {

    public function relacionActosInscritos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');
        
        // $nameLog = 'api_relacionExpedientesModificados_' . date ("Ymd");
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["fechainicial"] = '';
        $_SESSION["jsonsalida"]["fechafinal"] = '';
        $_SESSION["jsonsalida"]["horainicial"] = '';
        $_SESSION["jsonsalida"]["horafinal"] = '';
        $_SESSION["jsonsalida"]["control"] = '';
        $_SESSION["jsonsalida"]["actos"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", true);
        $api->validarParametro("fechafinal", true);
        $api->validarParametro("horainicial", false);
        $api->validarParametro("horafinal", false);
        $api->validarParametro("control", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('relacionActosInscritos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // Validar fechas
        if ($_SESSION["entrada"]["fechainicial"] > $_SESSION["entrada"]["fechafinal"]) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de fechas incorrecto';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (!\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechainicial"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha inicial incorrecta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (!\funcionesGenerales::validarFecha($_SESSION["entrada"]["fechafinal"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Fecha final incorrecta';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // validar horas
        if ($_SESSION["entrada"]["fechainicial"] == $_SESSION["entrada"]["fechafinal"]) {
            if ($_SESSION["entrada"]["horainicial"] != '' || $_SESSION["entrada"]["horafinal"] != '') {
                if ($_SESSION["entrada"]["horainicial"] == '' || $_SESSION["entrada"]["horafinal"] == '') {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar una hora innicial y una final';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if ($_SESSION["entrada"]["horainicial"] > $_SESSION["entrada"]["horafinal"]) {
                    $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Rango de horas/minutos incorrecto';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }

        //
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $condicion = "(fecharegistro between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "') ";
        if ($_SESSION["entrada"]["horainicial"] != '') {
            $condicion .= "and (horaregistro between '" . $_SESSION["entrada"]["horainicial"] . "' and '" . $_SESSION["entrada"]["horafinal"] . "') ";
        }
        if ($_SESSION["entrada"]["control"] != '*') {
            $tactos = '';
            $arActos = explode(",", $_SESSION["entrada"]["control"]);
            foreach ($arActos as $cnt) {
                $actos = retornarRegistrosMysqliApi($mysqli, 'mreg_actos', "idgrupoacto='" . sprintf("%03s",trim($cnt)) . "'", "idlibro,idacto");
                if ($actos && !empty($actos)) {
                    foreach ($actos as $a) {
                        if ($tactos != '') {
                            $tactos .= ' or ';
                        }
                        $tactos .= "(libro='" . $a["idlibro"] . "' and acto='" . $a["idacto"] . "')";
                    }
                }
            }
            if ($tactos != '') {
                $condicion .= ' and (' . $tactos . ')';
            }
        }

        //        
        $regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $condicion, "libro,registro,dupli");
        if ($regs && !empty($regs)) {
            foreach ($regs as $r) {
                $arr = array();
                $arr["libro"] = $r["libro"];
                $arr["registro"] = $r["registro"];
                $arr["dupli"] = $r["dupli"];
                $arr["fecharegistro"] = $r["fecharegistro"];
                $arr["horaregistro"] = $r["horaregistro"];
                $arr["acto"] = $r["acto"];
                $arr["matricula"] = $r["matricula"];
                $arr["tipoidentificacion"] = $r["tipoidentificacion"];
                $arr["identificacion"] = $r["identificacion"];
                $arr["nombre"] = $r["nombre"];
                $arr["fechadocumento"] = $r["fechadocumento"];
                $arr["idorigendoc"] = $r["idorigendoc"];
                $arr["origendocumento"] = $r["origendocumento"];
                $arr["municipiodocumento"] = $r["municipiodocumento"];
                $arr["numerodocumento"] = $r["numerodocumento"];
                $arr["tipodocumento"] = $r["tipodocumento"];
                $arr["tipolibro"] = $r["tipolibro"];
                $arr["idcodlibro"] = $r["idcodlibro"];
                $arr["codigolibro"] = $r["codigolibro"];
                $arr["descripcionlibro"] = $r["descripcionlibro"];
                $arr["paginainicial"] = $r["paginainicial"];
                $arr["numeropaginas"] = $r["numeropaginas"];
                $arr["noticia"] = $r["noticia"];
                $arr["fechalimite"] = $r["fechalimite"];
                $arr["recibo"] = $r["recibo"];
                $arr["codigobarras"] = $r["idradicacion"];
                $arr["operador"] = $r["operador"];
                $arr["camaraanterior"] = $r["camaraanterior"];
                $arr["libroanterior"] = $r["libroanterior"];
                $arr["registroanterior"] = $r["registroanterior"];
                $arr["fecharegistroanterior"] = $r["fecharegistroanterior"];
                $arr["vinculoafectado"] = $r["vinculoafectado"];
                $arr["tipoidentificacionafectada"] = $r["tipoidentificacionafectada"];
                $arr["identificacionafectada"] = $r["identificacionafectada"];
                $arr["estado"] = $r["estado"];
                $_SESSION["jsonsalida"]["actos"][] = $arr;
            }
        }


        // ********************************************************************** //
        // Retornar registros
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["fechainicial"] = $_SESSION["entrada"]["fechainicial"];
        $_SESSION["jsonsalida"]["fechafinal"] = $_SESSION["entrada"]["fechafinal"];
        $_SESSION["jsonsalida"]["horainicial"] = $_SESSION["entrada"]["horainicial"];
        $_SESSION["jsonsalida"]["horafinal"] = $_SESSION["entrada"]["horafinal"];
        $_SESSION["jsonsalida"]["control"] = $_SESSION["entrada"]["control"];

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
