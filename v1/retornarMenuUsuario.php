<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait retornarMenuUsuario  {

    public function retornarMenuUsuario(API $api) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');
        
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["menu"] = '';

        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);

        //
        if (trim($_SESSION["entrada"]["idusuario"]) == "") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Código del usuario no debe ser vacío';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken(__FUNCTION__, $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Si el usuaruio es USUPUBXX no lee la tabla de usuarios pues se considera
        // que es un usuario publico
        // ********************************************************************** // 
        $usuariopublico = 'no';
        if ($_SESSION["entrada"]["idusuario"] == 'USUPUBXX') {
            $usuariopublico = 'si';
            $tipousuario = '00';
        } else {
            if (substr($_SESSION["entrada"]["idusuario"],0,6) == 'ADMGEN') {
                $usuariopublico = 'si';
                $tipousuario = '01';
            } else {

                $arrUsu = retornarRegistroMysqliApi($mysqli, 'usuarios', "idusuario='" . $_SESSION["entrada"]["idusuario"] . "'");
                if ($arrUsu === false || empty($arrUsu)) {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no encontrado en la BD';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if (ltrim($arrUsu["fechaactivacion"], "0") == '') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario no activado';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                if (ltrim($arrUsu["fechainactivacion"], "0") != '') {
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "9996";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'Usuario inactivado';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
                $tipousuario = $arrUsu["idtipousuario"];
            }
        }

        //
        if (!defined('TIPO_EMPRESA')) {
            define('TIPO_EMPRESA', '');
        }
        if (!defined('TIPO_EMPRESA1')) {
            define('TIPO_EMPRESA1', '');
        }
        if (!defined('TIPO_EMPRESA2')) {
            define('TIPO_EMPRESA2', '');
        }

        // Carga la lista de opciones
        $arreglo = array();
        if ($tipousuario == '00') {
            $query = "sii2_mostrar = 'S' and  tipousuariopublico='X' and estado='1'";
        }
        if ($tipousuario == '01') {
            $query = "sii2_mostrar = 'S' and estado='1'";
        }
        if ($tipousuario == '02') {
            $query = "sii2_mostrar = 'S' and  tipousuarioadministrativo='X' and estado='1'";
        }
        if ($tipousuario == '03') {
            $query = "sii2_mostrar = 'S' and  tipousuarioproduccion='X' and estado='1'";
        }
        if ($tipousuario == '04') {
            $query = "sii2_mostrar = 'S' and  tipousuarioventas='X' and estado='1'";
        }
        if ($tipousuario == '05') {
            $query = "sii2_mostrar = 'S' and  tipousuarioregistro='X' and estado='1'";
        }
        if ($tipousuario == '06') {
            $query = "sii2_mostrar = 'S' and  tipousuarioexterno='X' and estado='1'";
        }

        //
        $result = retornarRegistrosMysqliApi($mysqli, "bas_opciones", $query, "idopcion");
        $i = 0;
        $k = 0;
        $j = 0;
        if ($result) {
            foreach ($result as $res) {
                if ($res["idtipoopcion"] == 'G') {
                    $i++;
                    $arreglo [$i] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["cantsub"] = 0;
                    $arreglo [$i] ["subs"] = array();
                    $j = 0;
                    $k = 0;
                }
                if ($res["idtipoopcion"] == 'S') {
                    $j++;
                    $arreglo [$i] ["subs"][$j] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["subs"][$j] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["subs"][$j] ["cantaccs"] = 0;
                    $arreglo [$i] ["subs"][$j] ["accs"] = array();
                    $k = 0;
                }
                if ($res["idtipoopcion"] == 'A') {
                    $k++;
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["idopcion"] = $res["idopcion"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["nombre"] = str_replace("<br>", " ", $res["nombre"]);
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_controlador"] = $res["sii2_controlador"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_metodo"] = $res["sii2_metodo"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_parametros"] = $res["sii2_parametros"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["sii2_ajax"] = $res["sii2_ajax"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["enlace"] = $res["enlace"];
                    $arreglo [$i] ["subs"][$j] ["accs"][$k] ["script"] = $res["script"];
                    if (isset($arreglo [$i] ["subs"][$j] ["cantaccs"])) {
                        $arreglo [$i] ["subs"][$j] ["cantaccs"] ++;
                        $arreglo [$i] ["cantsub"] ++;
                    }
                }
            }
        }

        $json = '[';
        $i1 = 0;
        foreach ($arreglo as $ar) {
            if ($ar["cantsub"] != 0) {
                $i1++;
                if ($i1 > 1) {
                    $json .= ',';
                }
                $json .= '{"parent":"1" ,"titulo":"' . $ar["nombre"] . '","child":"2","opc":[';
                $i2 = 0;
                foreach ($ar["subs"] as $arsub) {
                    if (isset($arsub["cantaccs"]) && $arsub["cantaccs"] != 0) {
                        $i2++;
                        if ($i2 > 1) {
                            $json .= ',';
                        }
                        $json .= '{"parent":"2" ,"titulo":"' . $arsub["nombre"] . '","child":"3","opc":[';
                        $i3 = 0;
                        foreach ($arsub["accs"] as $aracc) {
                            $i3++;
                            if ($i3 > 1) {
                                $json .= ',';
                            }
                            if ($aracc["sii2_controlador"] != '') {
                                $json .= '{"parent":"3" ,"titulo":"' . $aracc["nombre"] . '","x":"' . $aracc["sii2_controlador"] . '","y":"' . $aracc["sii2_metodo"] . '","z":"' . $aracc["sii2_ajax"] . '"}';
                            } else {
                                $arr = array();
                                $arr["codigoempresa"] = $_SESSION["generales"]["codigoempresa"];
                                $arr["idusuario"] = $_SESSION["entrada"]["idusuario"];
                                $arr["emailcontrol"] = $_SESSION["entrada"]["emailcontrol"];
                                $arr["identificacioncontrol"] = $_SESSION["entrada"]["identificacioncontrol"];
                                $arr["celularcontrol"] = $_SESSION["entrada"]["celularcontrol"];
                                $arr["fechainvocacion"] = date("Ymd");
                                $arr["horainvocacion"] = date("His");
                                $arr["script"] = $aracc["script"];
                                $arr["accion"] = 'seleccion';
                                $arr["parametros"] = array();
                                $jsonx = json_encode($arr);
                                $jsonencrypt = base64_encode(\funcionesGenerales::encriptar($jsonx, $claveEncriptacion));
                                $enlx = TIPO_HTTP . HTTP_HOST . '/lanzadorGeneral.php?parametros=' . $jsonencrypt;
                                $json .= '{"parent":"3" ,"titulo":"' . $aracc["nombre"] . '","href":"' . $enlx . '"}';
                            }
                        }
                        $json .= ']}';
                    }
                }
                $json .= ']}';
            }
        }
        $json .= ']';

        $_SESSION["jsonsalida"]["menu"] = $json;

        $salida = '{ ';
        $salida .= '"codigoerror":"0000",';
        $salida .= '"mensajeerror":"",';
        $salida .= '"menu":' . $json;
        $salida .= ' }';


        //
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        // $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $salida), 200);
    }

}
