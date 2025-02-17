<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarVinculosIdentificacion {

    public function consultarVinculosIdentificacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["nombre1"] = '';
        $_SESSION["jsonsalida"]["nombre2"] = '';
        $_SESSION["jsonsalida"]["apellido1"] = '';
        $_SESSION["jsonsalida"]["apellido2"] = '';


        $_SESSION["jsonsalida"]["registros"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("identificacion", true);

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarVinculosIdentificacion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["jsonsalida"]["identificacion"] = $_SESSION["entrada"]["identificacion"];

        //
        $mysqli = conexionMysqliApi();
        
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busqueda comerciantes con la identificacion
        // ********************************************************************** //
        $res = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "numid like '" . $_SESSION["entrada"]["identificacion"] . "%' or nit like '" . $_SESSION["entrada"]["identificacion"] . "%'", "id");
        if ($res && !empty($res)) {
            foreach ($res as $r) {
                $renglon = array(
                    'tipoVinculo' => 'COM',
                    'descripcionVinculo' => 'COMERCIANTE',
                    'estadoVinculo' => 'V',
                    'matricula' => $r["matricula"],
                    'razonSocial' => $r["razonsocial"],
                    'estadoMatricula' => $r["ctrestmatricula"],
                    'estadoAfiliacion' => $r["ctrafiliacion"],
                    'organizacionMatricula' => $r["organizacion"],
                    'categoriaMatricula' => $r["categoria"],
                    'ultAnoRenMatricula' => $r["ultanoren"]
                );
                $_SESSION["jsonsalida"]["registros"][] = $renglon;
                if ($_SESSION["jsonsalida"]["nombre"] == '') {
                    $_SESSION["jsonsalida"]["nombre"] = $r["razonsocial"];
                    $_SESSION["jsonsalida"]["nombre1"] = $r["nombre1"];
                    $_SESSION["jsonsalida"]["nombre2"] = $r["nombre2"];
                    $_SESSION["jsonsalida"]["apellido1"] = $r["apellido1"];
                    $_SESSION["jsonsalida"]["apellido2"] = $r["apellido2"];
                }
            }
        }

        // ********************************************************************** //
        // Busqueda de vínculos
        // ********************************************************************** //
        $res = retornarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', "numid like '" . $_SESSION["entrada"]["identificacion"] . "%'", "id");
        if ($res && !empty($res)) {
            foreach ($res as $r) {
                $tv = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $r["vinculo"] . "'");
                if ($tv && !empty($tv)) {
                    if ($tv["tipovinculo"] != '') {
                        $exp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $r["matricula"] . "'");
                        if ($exp && !empty($exp)) {
                            $renglon = array(
                                'tipoVinculo' => $tv["tipovinculo"],
                                'descripcionVinculo' => $tv["descripcion"],
                                'estadoVinculo' => $r["estado"],
                                'matricula' => $exp["matricula"],
                                'razonSocial' => $exp["razonsocial"],
                                'estadoMatricula' => $exp["ctrestmatricula"],
                                'estadoAfiliacion' => $exp["ctrafiliacion"],
                                'organizacionMatricula' => $exp["organizacion"],
                                'categoriaMatricula' => $exp["categoria"],
                                'ultAnoRenMatricula' => $exp["ultanoren"]
                            );
                            $_SESSION["jsonsalida"]["registros"][] = $renglon;
                            if ($_SESSION["jsonsalida"]["nombre"] == '') {
                                $_SESSION["jsonsalida"]["nombre"] = $r["nombre"];
                                $_SESSION["jsonsalida"]["nombre1"] = $r["nom1"];
                                $_SESSION["jsonsalida"]["nombre2"] = $r["nom2"];
                                $_SESSION["jsonsalida"]["apellido1"] = $r["ape1"];
                                $_SESSION["jsonsalida"]["apellido2"] = $r["ape2"];
                            }
                        }
                    }
                }
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
