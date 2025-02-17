<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarImagenesExpedienteProponentes {

    public function consultarImagenesExpedienteProponentes(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/s3_v4_api.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["proponente"] = '';
        $arrImagenes = array();
        $_SESSION["jsonsalida"]["imagenes"] = $arrImagenes;
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("proponente", false);
        $api->validarParametro("identificacion", false);

        if (trim($_SESSION["entrada"]["proponente"]) == '' && trim($_SESSION["entrada"]["identificacion"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indico un parametro de busqueda';
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarImagenesExpedienteProponentes', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Buscar imagenes
        // ********************************************************************** // 
        $arrTem = array();

        if (trim($_SESSION["entrada"]["proponente"]) == '' || $_SESSION["entrada"]["proponente"] == null) {
            $arrTemId = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "numid='" . ltrim($_SESSION["entrada"]["identificacion"], "0") . "'AND proponente !='' AND proponente is not null LIMIT 1");
            if ($arrTemId !== null && $arrTemId !== false && $arrTemId !== 0 && count($arrTemId) > 0) {
                $_SESSION["entrada"]["proponente"] = $arrTemId['proponente'];
            } else {
                $_SESSION["jsonsalida"]["codigoerror"] = "9999";
                $_SESSION["jsonsalida"]["mensajeerror"] = "No se encuentra registro de proponente asociado a la identificación";
                $api->response($api->json($_SESSION["jsonsalida"]), 200);
            }
        }



        $queryAnexos = "SELECT an.idanexo as idanexo, an.tipoanexo as tipoanexo, td.homologadigitalizacion as tipodoc, an.identificador as identificador, an.identificacion as identificacion,"
                . "an.nombre as nombre, an.matricula as matricula, an.proponente as proponente, an.fechadoc as fechadocumento, an.txtorigendoc as origendoc, "
                . "an.observaciones as observaciones, an.idradicacion as radicado,an.path as url "
                . "FROM mreg_radicacionesanexos an LEFT JOIN bas_tipodoc td on an.idtipodoc=td.idtipodoc "
                . "WHERE an.eliminado<>'SI' and an.proponente='" . $_SESSION["entrada"]["proponente"] . "'";

        $mysqli->set_charset("utf8");
        $resQueryAnexosRUP = $mysqli->query($queryAnexos);


        if (!empty($resQueryAnexosRUP)) {
            while ($imagenInfo = $resQueryAnexosRUP->fetch_array(MYSQLI_ASSOC)) {
                $arrayImgResp = array();
                if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                    $imagenx = obtenerUrlRepositorioS3Api($imagenInfo['url']);
                } else {
                    $imagenx = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagenInfo['url'];
                }

                $arrayImgResp['url'] = $imagenx;
                $arrayImgResp['idanexo'] = doubleval($imagenInfo['idanexo']);
                 
                //WSIERRA : 2018-11-22  - Incluye campo tipoanexo
                $arrayImgResp['tipoanexo'] = trim((string)$imagenInfo['tipoanexo']);

                $arrayImgResp['radicado'] = trim((string)$imagenInfo['radicado']);
                $arrayImgResp['tipodoc'] = trim((string)$imagenInfo['tipodoc']);
                $arrayImgResp['identificador'] = trim((string)$imagenInfo['identificador']);
                $strings = explode(".", $imagenInfo['url']);
                $arrayImgResp['formato'] = $strings[count($strings) - 1];
                $arrayImgResp['identificacion'] = trim((string)$imagenInfo['identificacion']);
                $arrayImgResp['nombre'] = trim((string)$imagenInfo['nombre']);
                $arrayImgResp['matricula'] = trim((string)$imagenInfo['matricula']);
                $arrayImgResp['fechadocumento'] = trim((string)$imagenInfo['fechadocumento']);
                $arrayImgResp['origendoc'] = trim((string)$imagenInfo['origendoc']);
                $arrayImgResp['observaciones'] = trim((string)$imagenInfo['observaciones']);
                $arrImagenes[] = $arrayImgResp;
            }
            $resQueryAnexosRUP->free();
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron radicaciones de anexos para los datos solicitados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();
        unset($resQueryAnexosRUP);

        $_SESSION["jsonsalida"]["proponente"] = $_SESSION["entrada"]["proponente"];
        $_SESSION["jsonsalida"]["imagenes"] = $arrImagenes;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
