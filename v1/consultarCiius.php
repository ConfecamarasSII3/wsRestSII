<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarCiius {

    public function consultarCiius(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');
        
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["palabras"] = '';
        $_SESSION["jsonsalida"]["renglones"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("palabras", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 

        if (!$api->validarToken('consultarCiius', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busqueda de ciius
        // ********************************************************************** //
        $busqueda = "";
        $cantidad_palabras = 0;
        $palabras_busqueda = explode(" ", $_SESSION["entrada"]["palabras"]);
        $arregloCiiu = array();

        if (is_numeric($_SESSION["entrada"]["palabras"])) {
            $busqueda = "idciiu like '%" . $_SESSION["entrada"]["palabras"] . "'";
        } else {
            foreach ($palabras_busqueda as $palabra) {
                $cantidad_palabras++;
                if ($cantidad_palabras == 1) {
                    $busqueda .= "(descripcion like '%" . $palabra . "%' or detalle like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                } else {
                    $busqueda .= " and (descripcion like '%" . $palabra . "%'  or detalle like '%" . $palabra . "%' or incluye like '%" . $palabra . "%')";
                }
            }
        }

        //
        $mysqli = conexionMysqliApi();
        $res = retornarRegistrosMysqliApi($mysqli, 'bas_ciius', $busqueda, "idciiu");
        $mysqli->close();
        
        //
        if ($res && !empty($res)) {
            foreach ($res as $x) {
                foreach ($palabras_busqueda as $p) {
                    $x["descripcion"] = str_replace($p, "<span style='background-color: #88AAEE'>$p</span>", $x["descripcion"]);
                }
                foreach ($palabras_busqueda as $p) {
                    $x["detalle"] = str_replace($p, "<span style='background-color: #88AAEE'>$p</span>", $x["detalle"]);
                }
                foreach ($palabras_busqueda as $p) {
                    $x["incluye"] = str_replace($p, "<span style='background-color: #88AAEE'>$p</span>", $x["incluye"]);
                }
                $x["descripcion"] = str_replace("\n?",chr(13).chr(10), $x["descripcion"]);
                $x["detalle"] = str_replace("\n?",chr(13).chr(10), $x["detalle"]);
                $x["incluye"] = str_replace("\n?",chr(13).chr(10), $x["incluye"]);
                $x["excluye"] = str_replace("\n?",chr(13).chr(10), $x["excluye"]);
                
                $arregloCiiu[$x["idciiu"]]["ciiu"] = $x["idciiu"];
                $arregloCiiu[$x["idciiu"]]["descripcion"] = $x["descripcion"];
                $arregloCiiu[$x["idciiu"]]["detalle"] = $x["detalle"];
                $arregloCiiu[$x["idciiu"]]["incluye"] = $x["incluye"];
                $arregloCiiu[$x["idciiu"]]["excluye"] = $x["excluye"];
                $arregloCiiu[$x["idciiu"]]["restriccionponal"] = $x["restriccionponal"];
                $arregloCiiu[$x["idciiu"]]["actividadcomercial"] = $x["actividadcomercial"];
            }
        }

        //
        if (!empty($arregloCiiu)) {
            foreach ($arregloCiiu as $dats) {
                $_SESSION["jsonsalida"]['renglones'][] = $dats;
            }
        }

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        $_SESSION["jsonsalida"]["palabras"] = $_SESSION["entrada"]["palabras"];
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
