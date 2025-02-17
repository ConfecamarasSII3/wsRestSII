<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarKardexExpediente {

    public function consultarKardexExpediente(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php');
        $resError = set_error_handler('myErrorHandler');

        $nameLog = 'consultarExpedienteMercantil_' . date("Ymd");

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["matricula"] = '';
        $_SESSION["jsonsalida"]["nombre"] = '';
        $_SESSION["jsonsalida"]["sigla"] = '';
        $_SESSION["jsonsalida"]["tipoidentificacion"] = '';
        $_SESSION["jsonsalida"]["identificacion"] = '';
        $_SESSION["jsonsalida"]["nit"] = '';
        $_SESSION["jsonsalida"]["organizacion"] = '';
        $_SESSION["jsonsalida"]["categoria"] = '';
        $_SESSION["jsonsalida"]["fechamatricula"] = '';
        $_SESSION["jsonsalida"]["fecharenovacion"] = '';
        $_SESSION["jsonsalida"]["ultanorenovado"] = '';
        $_SESSION["jsonsalida"]["fechacancelacion"] = '';
        $_SESSION["jsonsalida"]["controlafiliacion"] = '';
        $_SESSION["jsonsalida"]["estado"] = '';        
        $_SESSION["jsonsalida"]["inscripciones"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("ambiente", false);

        if (trim($_SESSION["entrada"]["matricula"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó matrícula a consultar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarKardexExpediente', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Abre conexión con la BD
        // ********************************************************************** // 
        if (!isset($_SESSION["entrada"]["ambiente"]) || $_SESSION["entrada"]["ambiente"] == '' || $_SESSION["entrada"]["ambiente"] == 'A') {
            $mysqli = conexionMysqliApi();
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'P') {
            $mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
        }
        if (isset($_SESSION["entrada"]["ambiente"]) && $_SESSION["entrada"]["ambiente"] == 'D') {
            $mysqli = conexionMysqliApi('D-' . $_SESSION["generales"]["codigoempresa"]);
        }

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Busca el expediente indicado
        // ********************************************************************** // 
        $arrTem = false;
        if ($_SESSION["entrada"]["matricula"] != '') {
            $arrTem = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $_SESSION["entrada"]["matricula"], '', '', '', 'N');
        }
        if ($arrTem === false || $arrTem == 0) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Expediente no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Retornar el expediente recuperado
        // ********************************************************************** // 
        $_SESSION["jsonsalida"]["matricula"] = ltrim((string) $arrTem["matricula"], "0");
        $_SESSION["jsonsalida"]["nombre"] = trim((string) $arrTem["nombre"]);
        $_SESSION["jsonsalida"]["sigla"] = trim((string) $arrTem["sigla"]);
        $_SESSION["jsonsalida"]["idclase"] = $arrTem["tipoidentificacion"];
        $_SESSION["jsonsalida"]["identificacion"] = ltrim((string) $arrTem["identificacion"], "0");
        $_SESSION["jsonsalida"]["nit"] = ltrim((string) $arrTem["nit"], "0");
        $_SESSION["jsonsalida"]["organizacion"] = $arrTem["organizacion"];
        $_SESSION["jsonsalida"]["categoria"] = $arrTem["categoria"];
        $_SESSION["jsonsalida"]["estado"] = $arrTem["estadomatricula"];
        $_SESSION["jsonsalida"]["fechamatricula"] = $arrTem["fechamatricula"];
        $_SESSION["jsonsalida"]["fecharenovacion"] = $arrTem["fecharenovacion"];
        $_SESSION["jsonsalida"]["ultanorenovado"] = $arrTem["ultanoren"];
        $_SESSION["jsonsalida"]["fechacancelacion"] = $arrTem["fechacancelacion"];
        $_SESSION["jsonsalida"]["controlafiliacion"] = $arrTem["afiliado"];

        //
        $_SESSION["jsonsalida"]["inscripciones"] = array();
        foreach ($arrTem["inscripciones"] as $insc) {
            $eslibro = '';
            $descripcionlibro = '';
            $tipolibro = '';
            if ($insc["acto"] == '0003' || $insc["acto"] == '0004') {
                $eslibro = 'si';
                $tipolibro = $insc["tipolibro"];
                $descripcionlibro = $insc["deslib"];
            }
            $row = array(
                'fechahora' => $insc["freg"] . ' ' . $insc["hreg"],
                'libro' => $insc["lib"],
                'registro' => $insc["nreg"],
                'dupli' => $insc["dupli"],
                'acto' => $insc["acto"],
                'nrodoc' => $insc["ndoc"],
                'fecdoc' => $insc["fdoc"],
                'origen' => $insc["txoridoc"],
                'noticia' => $insc["not"]
            );
            if ($eslibro == 'si') {
                $row["eslibrocomercio"] = $eslibro;
                $row["tipolibro"] = $tipolibro;
                $row["descripcionlibro"] = $descripcionlibro;
                if ($tipolibro == '' || $tipolibro == 'F') {
                    $row["paginainicial"] = $insc["paginainicial"];
                    $row["numerohojas"] = $insc["numhojas"];
                }
                if ($tipolibro == 'E') {
                    $ffin = str_replace("-","",\funcionesGenerales::calcularFechaInicial($insc["freg"], 365, '+'));
                    $row["vigencia"] = 'Vigente desde el ' . $insc["freg"] . ' al ' . $ffin;
                }
            }
            $_SESSION["jsonsalida"]["inscripciones"][] = $row;
        }

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
