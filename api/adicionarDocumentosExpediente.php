<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait adicionarDocumentosExpediente {

    public function adicionarDocumentosExpediente(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        $resError = set_error_handler('myErrorHandler');

        //cantidad de registros
        $limit = 100;
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["identificadorfoto"] = '';

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);

        //
        $api->validarParametro("codigobarras", false);
        $api->validarParametro("recibo", false);
        $api->validarParametro("operacion", false);
        $api->validarParametro("identificacion", false);
        $api->validarParametro("nombre", false);
        $api->validarParametro("matricula", false);
        $api->validarParametro("proponente", false);
        $api->validarParametro("idtipodoc", false);
        $api->validarParametro("numdoc", false);
        $api->validarParametro("fechadoc", false);
        $api->validarParametro("txtorigendoc", false);
        $api->validarParametro("observaciones", false);
        $api->validarParametro("libro", false);
        $api->validarParametro("registro", false);
        $api->validarParametro("dupli", false);
        $api->validarParametro("bandeja", false);
        $api->validarParametro("tipoanexo", false);
        $api->validarParametro("usuario", false);
        $api->validarParametro("URL", false);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('adicionarDocumentosExpediente', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim($_SESSION["entrada"]["idtipodoc"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicarse el tipo de documento';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim($_SESSION["entrada"]["usuario"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicarse el usuario que carga el documento';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        
        //
        if (trim($_SESSION["entrada"]["bandeja"]) != '4.-REGMER' &&
                trim($_SESSION["entrada"]["bandeja"]) != '5.-REGESADL' &&
                trim($_SESSION["entrada"]["bandeja"]) != '6.-REGPRO') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicarse la bandeja adecuada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["proponente"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicar el nro de la matrícula o proponente';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (trim($_SESSION["entrada"]["tipoanexo"]) != '501' &&
                trim($_SESSION["entrada"]["tipoanexo"]) != '503' &&
                trim($_SESSION["entrada"]["tipoanexo"]) != '505' &&
                trim($_SESSION["entrada"]["tipoanexo"]) != '507' &&
                trim($_SESSION["entrada"]["tipoanexo"]) != '509' &&
                trim($_SESSION["entrada"]["tipoanexo"]) != '511' &&
                trim($_SESSION["entrada"]["tipoanexo"]) != '512') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Debe indicarse un tipo anexo permitido';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $rutatmp = $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . \funcionesGenerales::generarAleatorioAlfanumerico20() . '.pdf';
        \funcionesGenerales::descargaPdf($_SESSION["entrada"]["url"], $rutatmp);
        if (!file_exists($rutatmp)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No fue posible cargar al servidor SII el archivo desde la url indicada';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        /*
        if  (!\funcionesGenerales::validarPdf($rutatmp,'%PDF-1')) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'El archivo a incluir no es un pdf válido';
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        */
        
        // Si la identificación es vacía, lo busca en inscritos
        if (trim($_SESSION["entrada"]["identificacion"]) == '') {
            if (trim($_SESSION["entrada"]["matricula"]) != '') {
                $_SESSION["entrada"]["identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'","numid");
            } else {
                if (trim($_SESSION["entrada"]["proponente"]) != '') {
                    $_SESSION["entrada"]["identificacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $_SESSION["entrada"]["proponente"] . "'","numid");
                }
            }
        }
        
        // Si el nombre es vacío, lo busca en inscritos
        if (trim($_SESSION["entrada"]["nombre"]) == '') {
            if (trim($_SESSION["entrada"]["matricula"]) != '') {
                $_SESSION["entrada"]["nombre"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $_SESSION["entrada"]["matricula"] . "'","razonsocial");
            } else {
                if (trim($_SESSION["entrada"]["proponente"]) != '') {
                    $_SESSION["entrada"]["nombre"] = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "proponente='" . $_SESSION["entrada"]["proponente"] . "'","razonsocial");
                }
            }
        }
        
        // SI el tipo de documento se envía a 2 dígitos, se busca el correspondiente en SII -registro para homologarlo
        if (strlen(trim($_SESSION["entrada"]["idtipodoc"])) == 1) {
            $_SESSION["entrada"]["idtipodoc"] = sprintf("%02s",$_SESSION["entrada"]["idtipodoc"]);
        }
        if (strlen(trim($_SESSION["entrada"]["idtipodoc"])) == 2) {
            $tdoc = retornarRegistroMysqliApi($mysqli, 'bas_tipodoc', "homologasirep='" . trim($_SESSION["entrada"]["idtipodoc"]) . "'");
            if ($tdoc === false || empty ($tdoc)) {
                $idtipodoc = trim($_SESSION["entrada"]["idtipodoc"]);
            } else {
                $idtipodoc = trim($tdoc["idtipodoc"]);
            }
        } else {
            $idtipodoc = trim($_SESSION["entrada"]["idtipodoc"]);
        }
        
        // Si el recibo llega vacío se busca a partir del código de bafrras
        if (trim($_SESSION["entrada"]["recibo"]) == '') {
            $_SESSION["entrada"]["recibo"] = retornarRegistroMysqliApi($mysqli, 'mreg_liquidacion', "numeroradicacion='" . trim($_SESSION["entrada"]["codigobarras"]) . "'", "numerorecibo");
        }
        
        //
        $id = \funcionesRegistrales::grabarAnexoRadicacion(
                        $mysqli, // Conexion BD
                        $_SESSION["entrada"]["codigobarras"], // Código de barras
                        $_SESSION["entrada"]["recibo"], // Número del recibo
                        $_SESSION["entrada"]["operacion"], // Operacion
                        $_SESSION["entrada"]["identificacion"], // Identificacion
                        $_SESSION["entrada"]["nombre"], // Nombre
                        '', // Acreedor
                        '', // Nombre acreedor
                        $_SESSION["entrada"]["matricula"], // matrícula
                        $_SESSION["entrada"]["proponente"], // proponente
                        $idtipodoc, // Tipo de documento para el sello de mercantil
                        $_SESSION["entrada"]["numdoc"], // Numero del documento
                        $_SESSION["entrada"]["fechadoc"],
                        '', // Codigo de origen
                        $_SESSION["entrada"]["txtorigendoc"], // origen del documento
                        '', // Clasificacion
                        '', // Numero del contrato
                        '', // Idfuente
                        1, // version
                        '', // Path
                        '1', // Estado
                        date("Ymd"), // fecha de escaneo o generacion
                        $_SESSION["entrada"]["usuario"], // Usuario que genera el registro
                        '', // Caja de archivo
                        '', // Libro de archivo
                        $_SESSION["entrada"]["observaciones"], // Observaciones
                        $_SESSION["entrada"]["libro"], // Libro
                        $_SESSION["entrada"]["registro"], // Numero del registro en libros
                        $_SESSION["entrada"]["dupli"], // Dupli
                        $_SESSION["entrada"]["bandeja"], // Bandeja de registro
                        'N', // Soporte recibo
                        '', // Identificador
                        $_SESSION["entrada"]["tipoanexo"], 	
                        'API' // Proceso especial
        );

        //
        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
        copy($rutatmp, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
        \funcionesRegistrales::grabarPathAnexoRadicacion($mysqli, $id, $pathsalida);

        //
        $mysqli->close();

        //
        $_SESSION["jsonsalida"]["identificadorimagen"] = $id;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}