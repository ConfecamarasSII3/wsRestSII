<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarExpedienteMercantilClientesExternos {

    public function consultarExpedienteMercantilClientesExternos(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/wsRR18N.class.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["codigocamara"] = '';
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["razonsocial"] = '';
        $_SESSION["jsonsalida"]["nombre1"] = '';
        $_SESSION["jsonsalida"]["nombre2"] = '';
        $_SESSION["jsonsalida"]["apellido1"] = '';
        $_SESSION["jsonsalida"]["apellido2"] = '';
        $_SESSION["jsonsalida"]["sigla"] = '';
        $_SESSION["jsonsalida"]["idclase"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["genero"] = '';
        $_SESSION["jsonsalida"]["nit"] = '';
        $_SESSION["jsonsalida"]["organizacion"] = '';
        $_SESSION["jsonsalida"]["categoria"] = '';
        $_SESSION["jsonsalida"]["estado"] = '';
        $_SESSION["jsonsalida"]["fechamatricula"] = '';
        $_SESSION["jsonsalida"]["fecharenovacion"] = '';
        $_SESSION["jsonsalida"]["ultanorenovado"] = '';
        $_SESSION["jsonsalida"]["fechacancelacion"] = '';
        $_SESSION["jsonsalida"]["dircom"] = '';
        $_SESSION["jsonsalida"]["muncom"] = '';
        $_SESSION["jsonsalida"]["telcom1"] = '';
        $_SESSION["jsonsalida"]["telcom2"] = '';
        $_SESSION["jsonsalida"]["telcom3"] = '';
        $_SESSION["jsonsalida"]["emailcom"] = '';
        $_SESSION["jsonsalida"]["dirnot"] = '';
        $_SESSION["jsonsalida"]["munnot"] = '';
        $_SESSION["jsonsalida"]["telnot1"] = '';
        $_SESSION["jsonsalida"]["telnot2"] = '';
        $_SESSION["jsonsalida"]["telnot3"] = '';
        $_SESSION["jsonsalida"]["emailnot"] = '';
        $_SESSION["jsonsalida"]["ciiu1"] = '';
        $_SESSION["jsonsalida"]["ciiu2"] = '';
        $_SESSION["jsonsalida"]["ciiu3"] = '';
        $_SESSION["jsonsalida"]["ciiu4"] = '';
        $_SESSION["jsonsalida"]["afiliado"] = '';
        $_SESSION["jsonsalida"]["anodatos"] = '';
        $_SESSION["jsonsalida"]["fechadatos"] = '';
        $_SESSION["jsonsalida"]["activos"] = '';
        $_SESSION["jsonsalida"]["pasivos"] = '';
        $_SESSION["jsonsalida"]["patrimonio"] = '';
        $_SESSION["jsonsalida"]["ingope"] = '';
        $_SESSION["jsonsalida"]["ingnoope"] = '';
        $_SESSION["jsonsalida"]["utiope"] = '';
        $_SESSION["jsonsalida"]["utinet"] = '';
        $_SESSION["jsonsalida"]["personal"] = '';
        $_SESSION["jsonsalida"]["capitalsocial"] = '';
        $_SESSION["jsonsalida"]["capitalautorizado"] = '';
        $_SESSION["jsonsalida"]["capitalsuscrito"] = '';
        $_SESSION["jsonsalida"]["capitalpagado"] = '';
        $_SESSION["jsonsalida"]["replegal"] = array();
        $_SESSION["jsonsalida"]["socios"] = array();
        $_SESSION["jsonsalida"]["objetosocial"] = '';
        $_SESSION["jsonsalida"]["facultadesylimitaciones"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idsolicitud", true);
        $api->validarParametro("idclase", true);
        $api->validarParametro("identificacion", true);


        if (trim($_SESSION["entrada"]["idsolicitud"]) == '' && trim($_SESSION["entrada"]["idclase"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indico alguno de los datos obligatorios para la consulta (idsolicitud, idclase, identifcacion)';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarExpedienteMercantilClientesExternos', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $_SESSION["entrada"]["identificacion"] = str_replace(array(",",".","-"," "),"",$_SESSION["entrada"]["identificacion"]);
        
        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();

        //
        $solprev = retornarRegistroMysqliApi($mysqli, 'mreg_log_consultarExpedienteMercantilClientesExternos', "usuariows='" . $_SESSION["entrada"]["usuariows"] . "' and idsolicitud='" . $_SESSION["entrada"]["idsolicitud"] . "'");
        if ($solprev && !empty($solprev)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9997";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Solicitud previamente realizada (' . $solprev["fecha"] . ' - ' . $solprev["hora"] . ')';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el expediente indicado
        // ********************************************************************** // 
        $reg = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . $_SESSION["entrada"]["identificacion"] . "' and idclase='" . $_SESSION["entrada"]["idclase"] . "' and matricula > ''", "*", 'U');
        if ($reg === false || empty($reg)) {
            $cam = '';
            $mat = '';
            $est = '';
            $ins = \RR18N::singleton(wsRUE_RR18N);
            $camOrigen = CODIGO_EMPRESA;
            $camDestino = CODIGO_EMPRESA;
            if ($_SESSION["entrada"]["idclase"] == '2') {
                $x = \funcionesGenerales::separarDv($_SESSION["entrada"]["identificacion"]);
                $idex = $x["identificacion"];
                $dvx = $x["dv"];
            } else {
                $idex = $_SESSION["entrada"]["identificacion"];
                $dvx = '';
            }
            $parametros = array(
                'numero_interno' => date("Ymd") . date("His") . $camOrigen . $camDestino,
                'usuario' => CODIGO_EMPRESA,
                'numero_identificacion' => $idex
            );
            try {
                $res = $ins->consultarNumeroIdentificacion($parametros);
            } catch (Exception $e) {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible conectarse al RUES';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
            $t = (array) $res;
            if ($t["codigo_error"] != '0000') {
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'Error en respuesta RUES : ' . str_replace(array("'", '"'), "", $t["mensaje_error"]);
            }
            if (isset($t["datos_respuesta"])) {
                if (!isset($t["datos_respuesta"][0])) {
                    if (ltrim($t["datos_respuesta"]["matricula"], "0") != "") {
                        if (($_SESSION["entrada"]["idclase"] == '2' && $t["codigo_clase_identificacion"] == '02') || $t["codigo_clase_identificacion"] !== '02') {
                            if ($t["codigo_categoria_matricula"] != '01') {
                                $cam = $t["datos_respuesta"]["codigo_camara"];
                                $mat = ltrim($t["datos_respuesta"]["matricula"], "0");
                                $est = '';
                                if ($t["datos_respuesta"]["codigo_estado_matricula"] == "01") {
                                    $est = 'MC';
                                } else {
                                    $est = 'MA';
                                }
                            }
                        }
                    }
                } else {
                    foreach ($t["datos_respuesta"] as $r) {
                        if (ltrim($r["matricula"], "0") != "") {
                            if (($_SESSION["entrada"]["idclase"] == '2' && $r["codigo_clase_identificacion"] == '02') || $r["codigo_clase_identificacion"] !== '02') {
                                if ($r["codigo_categoria_matricula"] == '01') {
                                    $cam = $r["codigo_camara"];
                                    $mat = ltrim($r["matricula"], "0");
                                    $est = '';
                                    if ($r["codigo_estado_matricula"] != "01") {
                                        $est = 'MC';
                                    } else {
                                        $est = 'MA';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($cam == '') {
                $arrCampos = array(
                    'usuariows',
                    'idsolicitud',
                    'ip',
                    'fecha',
                    'hora',
                    'idclase',
                    'numid',
                    'respuesta',
                    'codigocamara',
                    'matricula',
                    'proponente',
                    'codigoerror'
                );
                $arrValores = array(
                    "'" . $_SESSION["entrada"]["usuariows"] . "'",
                    "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                    "'" . \funcionesGenerales::localizarIP() . "'",
                    "'" . date("Ymd") . "'",
                    "'" . date("His") . "'",
                    "'" . $_SESSION["entrada"]["idclase"] . "'",
                    "'" . $_SESSION["entrada"]["identificacion"] . "'",
                    "''",
                    "''",
                    "''",
                    "''",
                    "'0001'"
                );
                insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarExpedienteMercantilClientesExternos', $arrCampos, $arrValores);
                $mysqli->close();
                $_SESSION["jsonsalida"]["codigoerror"] = "0001";
                $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron registros asociados a la identificación';
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            } else {
                $_SESSION["jsonsalida"]["codigocamara"] = $cam;
                $_SESSION["jsonsalida"]["matricula"] = $mat;
                if ($est == 'MA') {
                    $arrCampos = array(
                        'usuariows',
                        'idsolicitud',
                        'ip',
                        'fecha',
                        'hora',
                        'idclase',
                        'numid',
                        'respuesta',
                        'codigocamara',
                        'matricula',
                        'proponente',
                        'codigoerror'
                    );
                    $arrValores = array(
                        "'" . $_SESSION["entrada"]["usuariows"] . "'",
                        "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                        "'" . \funcionesGenerales::localizarIP() . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . $_SESSION["entrada"]["idclase"] . "'",
                        "'" . $_SESSION["entrada"]["identificacion"] . "'",
                        "''",
                        "'" . $cam . "'",
                        "'" . $mat . "'",
                        "''",
                        "'0002'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarExpedienteMercantilClientesExternos', $arrCampos, $arrValores);
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0002";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La identificacion se encuentra asociada a un comerciante matriculado (activo) en otra cámara de comercio';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                } else {
                    $arrCampos = array(
                        'usuariows',
                        'idsolicitud',
                        'ip',
                        'fecha',
                        'hora',
                        'idclase',
                        'numid',
                        'respuesta',
                        'codigocamara',
                        'matricula',
                        'proponente',
                        'codigoerror'
                    );
                    $arrValores = array(
                        "'" . $_SESSION["entrada"]["usuariows"] . "'",
                        "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
                        "'" . \funcionesGenerales::localizarIP() . "'",
                        "'" . date("Ymd") . "'",
                        "'" . date("His") . "'",
                        "'" . $_SESSION["entrada"]["idclase"] . "'",
                        "'" . $_SESSION["entrada"]["identificacion"] . "'",
                        "''",
                        "'" . $cam . "'",
                        "'" . $mat . "'",
                        "''",
                        "'0003'"
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarExpedienteMercantilClientesExternos', $arrCampos, $arrValores);
                    $mysqli->close();
                    $_SESSION["jsonsalida"]["codigoerror"] = "0003";
                    $_SESSION["jsonsalida"]["mensajeerror"] = 'La identificacion se encuentra asociada a un comerciante matriculado (cancelado) en otra cámara de comercio';
                    $api->response($api->json($_SESSION["jsonsalida"]), 200);
                }
            }
        }

        //
        $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $reg["matricula"]);

        // ********************************************************************** //
        // Retornar el expediente recuperado
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["matricula"] = ltrim($arrTem["matricula"], "0");
        $_SESSION["jsonsalida"]["razonsocial"] = trim($arrTem["nombre"]);
        $_SESSION["jsonsalida"]["nombre1"] = trim($arrTem["nom1"]);
        $_SESSION["jsonsalida"]["nombre2"] = trim($arrTem["nom2"]);
        $_SESSION["jsonsalida"]["apellido1"] = trim($arrTem["ape1"]);
        $_SESSION["jsonsalida"]["apellido2"] = trim($arrTem["ape2"]);
        $_SESSION["jsonsalida"]["sigla"] = trim($arrTem["sigla"]);
        $_SESSION["jsonsalida"]["idclase"] = $arrTem["tipoidentificacion"];
        $_SESSION["jsonsalida"]["identificacion"] = ltrim($arrTem["identificacion"], "0");
        $_SESSION["jsonsalida"]["genero"] = $arrTem["sexo"];
        $_SESSION["jsonsalida"]["nit"] = ltrim($arrTem["nit"], "0");
        $_SESSION["jsonsalida"]["organizacion"] = $arrTem["organizacion"];
        $_SESSION["jsonsalida"]["categoria"] = $arrTem["categoria"];
        $_SESSION["jsonsalida"]["estado"] = $arrTem["estadomatricula"];
        $_SESSION["jsonsalida"]["fechamatricula"] = $arrTem["fechamatricula"];
        $_SESSION["jsonsalida"]["fecharenovacion"] = $arrTem["fecharenovacion"];
        $_SESSION["jsonsalida"]["ultanorenovado"] = $arrTem["ultanoren"];
        $_SESSION["jsonsalida"]["fechacancelacion"] = $arrTem["fechacancelacion"];
        $_SESSION["jsonsalida"]["dircom"] = trim($arrTem["dircom"]);
        $_SESSION["jsonsalida"]["muncom"] = $arrTem["muncom"];
        $_SESSION["jsonsalida"]["telcom1"] = trim($arrTem["telcom1"]);
        $_SESSION["jsonsalida"]["telcom2"] = trim($arrTem["telcom2"]);
        $_SESSION["jsonsalida"]["telcom3"] = trim($arrTem["celcom"]);
        $_SESSION["jsonsalida"]["emailcom"] = trim($arrTem["emailcom"]);
        $_SESSION["jsonsalida"]["dirnot"] = trim($arrTem["dirnot"]);
        $_SESSION["jsonsalida"]["munnot"] = $arrTem["munnot"];
        $_SESSION["jsonsalida"]["telnot1"] = trim($arrTem["telnot"]);
        $_SESSION["jsonsalida"]["telnot2"] = trim($arrTem["telnot2"]);
        $_SESSION["jsonsalida"]["telnot3"] = trim($arrTem["celnot"]);
        $_SESSION["jsonsalida"]["emailnot"] = trim($arrTem["emailnot"]);
        $_SESSION["jsonsalida"]["ciiu1"] = $arrTem["ciius"][1];
        $_SESSION["jsonsalida"]["ciiu2"] = $arrTem["ciius"][2];
        $_SESSION["jsonsalida"]["ciiu3"] = $arrTem["ciius"][3];
        $_SESSION["jsonsalida"]["ciiu4"] = $arrTem["ciius"][4];
        $_SESSION["jsonsalida"]["afiliado"] = $arrTem["afiliado"];
        $_SESSION["jsonsalida"]["anodatos"] = $arrTem["anodatos"];
        $_SESSION["jsonsalida"]["fechadatos"] = $arrTem["fechadatos"];
        if ($arrTem["organizacion"] == '01' || $arrTem["categoria"] == '1') {
            $_SESSION["jsonsalida"]["activos"] = doubleval($arrTem["acttot"]);
            $_SESSION["jsonsalida"]["pasivos"] = doubleval($arrTem["pastot"]);
            $_SESSION["jsonsalida"]["patrimonio"] = doubleval($arrTem["pattot"]);
            $_SESSION["jsonsalida"]["ingope"] = $arrTem["ingope"];
            $_SESSION["jsonsalida"]["ingnoope"] = $arrTem["ingnoope"];
            $_SESSION["jsonsalida"]["utiope"] = $arrTem["utiope"];
            $_SESSION["jsonsalida"]["utinet"] = $arrTem["utinet"];
        }
        $_SESSION["jsonsalida"]["personal"] = $arrTem["personal"];
        $_SESSION["jsonsalida"]["capitalsocial"] = $arrTem["capsoc"];
        $_SESSION["jsonsalida"]["capitalautorizado"] = $arrTem["capaut"];
        $_SESSION["jsonsalida"]["capitalsuscrito"] = $arrTem["capsus"];
        $_SESSION["jsonsalida"]["capitalpagado"] = $arrTem["cappag"];

        //
        foreach ($arrTem["vinculos"] as $v) {
            if ($v["tipovinculo"] == 'RLP' || $v["tipovinculo"] == 'RLS') {
                $arrl = array();
                $arrl["idclase"] = $v["idtipoidentificacionotros"];
                $arrl["identificacion"] = $v["identificacionotros"];
                $arrl["nombre"] = $v["nombreotros"];
                $arrl["tipo"] = 'P';
                if ($v["tipovinculo"] == 'RLS') {
                    $arrl["tipo"] = 'S';
                }
                $_SESSION["jsonsalida"]["replegal"][] = $arrl;
            }
            if ($v["tipovinculo"] == 'SOC') {
                $arrl = array();
                $arrl["idclase"] = $v["idtipoidentificacionotros"];
                $arrl["identificacion"] = $v["identificacionotros"];
                $arrl["nombre"] = $v["nombreotros"];
                $arrl["tipo"] = '';
                $_SESSION["jsonsalida"]["socios"][] = $arrl;
            }
        }

        // Objeto social
        if ($arrTem["organizacion"] == '01') {
            $_SESSION["jsonsalida"]["objetosocial"] = $arrTem["desactiv"];
        } else {
            if (isset($arrTem["crtsii"]["0740"]) && $arrTem["crtsii"]["0740"] != '') {
                $_SESSION["jsonsalida"]["objetosocial"] = $arrTem["crtsii"]["0740"];
            } else {
                if (isset($arrTem["crt"]["0740"]) && $arrTem["crt"]["0740"] != '') {
                    $_SESSION["jsonsalida"]["objetosocial"] = $arrTem["crt"]["0740"];
                }
            }
        }

        // Facultades y limitaciones
        if ($arrTem["organizacion"] == '01') {
            $_SESSION["jsonsalida"]["facultadesylimitaciones"] = 'Las personas naturales no tiene facultades ni limitaciones';
        } else {
            if (isset($arrTem["crtsii"]["1300"]) && $arrTem["crtsii"]["1300"] != '') {
                $_SESSION["jsonsalida"]["facultadesylimitaciones"] = $arrTem["crtsii"]["1300"];
            } else {
                if (isset($arrTem["crt"]["1300"]) && $arrTem["crt"]["1300"] != '') {
                    $_SESSION["jsonsalida"]["facultadesylimitaciones"] = $arrTem["crt"]["1300"];
                }
            }
        }

        // **************************************************************************** //
        // Almacena log
        // **************************************************************************** //        
        $arrCampos = array(
            'usuariows',
            'idsolicitud',
            'ip',
            'fecha',
            'hora',
            'idclase',
            'numid',
            'respuesta',
            'codigocamara',
            'matricula',
            'proponente',
            'codigoerror'
        );
        $arrValores = array(
            "'" . $_SESSION["entrada"]["usuariows"] . "'",
            "'" . $_SESSION["entrada"]["idsolicitud"] . "'",
            "'" . \funcionesGenerales::localizarIP() . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $_SESSION["entrada"]["idclase"] . "'",
            "'" . $_SESSION["entrada"]["identificacion"] . "'",
            "'" . addslashes($api->json($_SESSION["jsonsalida"])) . "'",
            "'" . CODIGO_EMPRESA . "'",
            "'" . $arrTem["matricula"] . "'",
            "''",
            "'0000'"
        );
        insertarRegistrosMysqliApi($mysqli, 'mreg_log_consultarExpedienteMercantilClientesExternos', $arrCampos, $arrValores);

        // **************************************************************************** //
        // Cerrar conexión a la BD
        // **************************************************************************** //        
        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
