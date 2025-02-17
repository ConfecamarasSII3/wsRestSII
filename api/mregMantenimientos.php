<?php

namespace api;

use api\API;

trait mregMantenimientos {

    public function mantenimientoLiquidacionesGrabar(API $api) {
        require_once ('mysqli.php');
        require_once ('log.php');
        require_once ('funcionesGenerales.php');
        require_once ('funcionesRegistrales.php');
        require_once ('funcionesRegistralesCalculos.php');

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

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('mantenimientoLiquidacionesGrabar', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Sin permisos para ejecutar el método mantenimientoLiquidacionesGrabar ';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();
        
        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        $liqini = json_encode($_SESSION["tramite"]);

        //
        foreach ($_SESSION["entrada"] as $k => $v) {
            if ($k != 'session_parameters' && $k != '_acceso' && $k != '_hhtphost' && $k != '_tipohhtp' && $k != 'idliquidacion') {
                if (isset($_SESSION["tramite"][$k])) {
                    if ($k != 'matriculabase' && $k != 'proponentebase') {
                        $_SESSION["tramite"][$k] = base64_decode($v);
                    }
                }
            }
        }
        $_SESSION["tramite"]["matriculabase"] = $_SESSION["entrada"]["idmatriculabase"];
        $_SESSION["tramite"]["proponentebase"] = $_SESSION["entrada"]["idproponentebase"];

        //
        if ($_SESSION["tramite"]["tipocliente"] == 'PN') {
            $_SESSION["tramite"]["razonsocialcliente"] = $_SESSION["tramite"]["apellido1cliente"];
            if (trim($_SESSION["tramite"]["apellido2cliente"])) {
                $_SESSION["tramite"]["razonsocialcliente"] .= ' ' . $_SESSION["tramite"]["apellido2cliente"];
            }
            if (trim($_SESSION["tramite"]["nombre1cliente"])) {
                $_SESSION["tramite"]["razonsocialcliente"] .= ' ' . $_SESSION["tramite"]["nombre1cliente"];
            }
            if (trim($_SESSION["tramite"]["nombre2cliente"])) {
                $_SESSION["tramite"]["razonsocialcliente"] .= ' ' . $_SESSION["tramite"]["nombre2cliente"];
            }
            $_SESSION["tramite"]["nombrecliente"] = trim($_SESSION["tramite"]["nombre1cliente"] . ' ' . $_SESSION["tramite"]["nombre2cliente"]);
            $_SESSION["tramite"]["apellidocliente"] = trim($_SESSION["tramite"]["apellido1cliente"] . ' ' . $_SESSION["tramite"]["apellido2cliente"]);
        } else {
            $_SESSION["tramite"]["apellido1cliente"] = '';
            $_SESSION["tramite"]["apellido2cliente"] = '';
            $_SESSION["tramite"]["nombre1cliente"] = '';
            $_SESSION["tramite"]["nombre2cliente"] = '';
            $_SESSION["tramite"]["nombrecliente"] = '';
            $_SESSION["tramite"]["apellidocliente"] = '';
        }

        //
        if ($_SESSION["tramite"]["tipopagador"] == 'PN') {
            $_SESSION["tramite"]["razonsocialpagador"] = $_SESSION["tramite"]["apellido1pagador"];
            if (trim($_SESSION["tramite"]["apellido2pagador"])) {
                $_SESSION["tramite"]["razonsocialpagador"] .= ' ' . $_SESSION["tramite"]["apellido2pagador"];
            }
            if (trim($_SESSION["tramite"]["nombre1pagador"])) {
                $_SESSION["tramite"]["razonsocialpagador"] .= ' ' . $_SESSION["tramite"]["nombre1pagador"];
            }
            if (trim($_SESSION["tramite"]["nombre2pagador"])) {
                $_SESSION["tramite"]["razonsocialpagador"] .= ' ' . $_SESSION["tramite"]["nombre2pagador"];
            }
            $_SESSION["tramite"]["nombrepagador"] = trim($_SESSION["tramite"]["nombre1pagador"] . ' ' . $_SESSION["tramite"]["nombre2pagador"]);
            $_SESSION["tramite"]["apellidopagador"] = trim($_SESSION["tramite"]["apellido1pagador"] . ' ' . $_SESSION["tramite"]["apellido2pagador"]);
        } else {
            $_SESSION["tramite"]["apellido1pagador"] = '';
            $_SESSION["tramite"]["apellido2pagador"] = '';
            $_SESSION["tramite"]["nombre1pagador"] = '';
            $_SESSION["tramite"]["nombre2pagador"] = '';
            $_SESSION["tramite"]["nombrepagador"] = '';
            $_SESSION["tramite"]["apellidopagador"] = '';
        }

        //
        $_SESSION["tramite"]["valorbruto"] = doubleval(base64_decode($_SESSION["entrada"]["valorbruto"]));
        $_SESSION["tramite"]["valorbaseiva"] = doubleval(base64_decode($_SESSION["entrada"]["valorbaseiva"]));
        $_SESSION["tramite"]["valoriva"] = doubleval(base64_decode($_SESSION["entrada"]["valoriva"]));
        $_SESSION["tramite"]["valortotal"] = doubleval(base64_decode($_SESSION["entrada"]["valortotal"]));
        $_SESSION["tramite"]["vueltas"] = doubleval(base64_decode($_SESSION["entrada"]["vueltas"]));
        $_SESSION["tramite"]["pagoefectivo"] = doubleval(base64_decode($_SESSION["entrada"]["pagoefectivo"]));
        $_SESSION["tramite"]["pagocheque"] = doubleval(base64_decode($_SESSION["entrada"]["pagocheque"]));
        $_SESSION["tramite"]["pagoconsignacion"] = doubleval(base64_decode($_SESSION["entrada"]["pagoconsignacion"]));
        $_SESSION["tramite"]["pagovisa"] = doubleval(base64_decode($_SESSION["entrada"]["pagovisa"]));
        $_SESSION["tramite"]["pagoach"] = doubleval(base64_decode($_SESSION["entrada"]["pagoach"]));
        $_SESSION["tramite"]["pagomastercard"] = doubleval(base64_decode($_SESSION["entrada"]["pagomastercard"]));
        $_SESSION["tramite"]["pagoamerican"] = doubleval(base64_decode($_SESSION["entrada"]["pagoamerican"]));
        $_SESSION["tramite"]["pagocredencial"] = doubleval(base64_decode($_SESSION["entrada"]["pagocredencial"]));
        $_SESSION["tramite"]["pagodiners"] = doubleval(base64_decode($_SESSION["entrada"]["pagodiners"]));
        $_SESSION["tramite"]["pagotdebito"] = doubleval(base64_decode($_SESSION["entrada"]["pagotdebito"]));
        $_SESSION["tramite"]["pagoprepago"] = doubleval(base64_decode($_SESSION["entrada"]["pagoprepago"]));
        $_SESSION["tramite"]["pagoafiliado"] = doubleval(base64_decode($_SESSION["entrada"]["pagoafiliado"]));
        $_SESSION["tramite"]["alertaid"] = intval(base64_decode($_SESSION["entrada"]["alertaid"]));
        $_SESSION["tramite"]["alertavalor"] = doubleval(base64_decode($_SESSION["entrada"]["alertavalor"]));


        //
        $liqfin = json_encode($_SESSION["tramite"]);
        \funcionesRegistrales::grabarLiquidacionMreg($mysqli);
        actualizarLogMysqliApi($mysqli, '003', $_SESSION["generales"]["codigousuario"], 'mantenimientoLiquidaciones.php', '', '', '', 'Mantenimiento liquidacion No. ' . $_SESSION["tramite"]["idliquidacion"] . ' - Inicial : ' . $liqini . ' - Final : ' . $liqfin, '', '');
        $mysqli->close();

        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = 'LIQUIDACION GRABADA';
        \logApi::peticionRest('api_' . __FUNCTION__);
        $api->response($api->json($_SESSION["jsonsalida"]), 200);
    }

}
