<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait verificarAfiliado {

    public function verificarAfiliado(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["cupoAfiliado"] = 0;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matriculaAfiliado", true);
        $api->validarParametro("claveAfiliado", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('verificarAfiliado', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $mysqli = conexionMysqliApi();

        // ********************************************************************** //
        // Busqueda comerciantes con la identificacion
        // ********************************************************************** //
        $res = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matriculaAfiliado"] . "'");
        if ($res === false || empty($res)) {
            $_SESSION["jsonsalida"]["codigoerror"] = '0001';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Matricula no encontrada';
            $mysqli->close();
            \logApi::peticionRest('api_' . __FUNCTION__);
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        // ********************************************************************** //
        // En caso que la matrícula no esté activa y vigente
        // ********************************************************************** //
        if ($res["ctrestmatricula"] != 'MA') {
            $_SESSION["jsonsalida"]["codigoerror"] = '0001';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Matricula no está activa (MA)';
            $mysqli->close();
            \logApi::peticionRest('api_' . __FUNCTION__);
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        // ********************************************************************** //
        // En caso que no sea un afiliado activo
        // ********************************************************************** //
        if ($res["ctrafiliacion"] != '1') {
            $_SESSION["jsonsalida"]["codigoerror"] = '0002';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Matricula no corresponde con un afiliado activo';
            $mysqli->close();
            \logApi::peticionRest('api_' . __FUNCTION__);
            $json = $api->json($_SESSION["jsonsalida"]);
            $api->response(str_replace("\\/", "/", $json), 200);
        }

        // ********************************************************************** //
        // Valida la clave recibida y la compara
        // ********************************************************************** //
        $afil = retornarRegistroMysqliApi($mysqli, 'mreg_claves_afiliados', "matricula='" . $_SESSION["entrada1"]["matriculaAfiliado"] . "'");
        if ($afil === false || empty($afil)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '0003';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El afiliado no tiene una clave asignada';
            \logApi::peticionRest('api_' . __FUNCTION__);
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $clavelimpia = \funcionesGenerales::encrypt_decrypt('decrypt', 'c0nf3c4m4r4s', 'c0nf3c4m4r4s', trim($_SESSION["entrada"]["claveAfiliado"]));
        if (
                $afil["clave"] != md5($clavelimpia) &&                
                $afil["clave"] != sha1($clavelimpia) &&
                !password_verify($clavelimpia, $afil["clave"]) &&
                $afil["clave"] != md5($_SESSION["entrada"]["claveAfiliado"]) &&
                $afil["clave"] != sha1($_SESSION["entrada"]["claveAfiliado"]) &&
                $afil["clave"] != $_SESSION["entrada"]["claveAfiliado"] &&
                !password_verify($_SESSION["entrada"]["claveAfiliado"], $afil["clave"])
        ) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '0003';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Clave incorrecta';
            \logApi::peticionRest('api_' . __FUNCTION__);
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Encuentra el cupo del afiliado
        // ********************************************************************** //
        $resx1 = \funcionesRegistrales::consultarSaldoAfiliadoMysqliApi($mysqli, $_SESSION["entrada1"]["matriculaAfiliado"]);
        if ($resx1 && !empty($resx1)) {
            foreach ($resx1 as $rx1) {
                $_SESSION["jsonsalida"]["cupoAfiliado"] = $rx1["cupo"];
            }
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
