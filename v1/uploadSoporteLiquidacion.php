<?php

/*
 * Se recibe json con la siguiente información
 * 
 */

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait uploadSoporteLiquidacion {

    public function uploadSoporteLiquidacion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');

        //
        if (!defined('CLAVE_ENCRIPTACION') || CLAVE_ENCRIPTACION == '') {
            $claveEncriptacion = 'c0nf3c@m@r@s2017';
        }

        // ********************************************************************** //
        // array de respuesta
        // ********************************************************************** //
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["link"] = '';

        // ********************************************************************** //
        // Verifica que  método de recepcion de parámetros sea POST
        // ********************************************************************** //
        if ($api->get_request_method() != "POST" && $api->get_request_method() != "GET") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST/GET';
        }

        //
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("idusuario", true);
        $api->validarParametro("tipousuario", true, true);
        $api->validarParametro("emailcontrol", false);
        $api->validarParametro("identificacioncontrol", false);
        $api->validarParametro("celularcontrol", false);

        //
        $api->validarParametro("idliquidacion", true, true); // obligatorio
        $api->validarParametro("sectransaccion", false); // para renovacion es vacio
        $api->validarParametro("expediente", false); // para renovación es obligatorio
        $api->validarParametro("identificacion", false); // Para renovación puede ser vacio
        $api->validarParametro("nombre", false); // Para renovación puede ser vacio
        $api->validarParametro("identificador", false); // obligatorio
        $api->validarParametro("descripcion", true); // obligatorio
        $api->validarParametro("fechadoc", true); // obligatorio (AAAAMMDD)
        $api->validarParametro("idtipodoc", true); // obligatorio
        $api->validarParametro("numdoc", false); // Para renovación puede ser vacio
        $api->validarParametro("origendoc", true); // obligatorio
        $api->validarParametro("base64", true);
        $api->validarParametro("extension", true);



        if (trim($_SESSION["entrada"]["base64"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se recibe el contenido del archivo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (trim($_SESSION["entrada"]["extension"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se definió la extensión del archivo';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        if (!$api->validarToken('uploadSoporteLiquidacion ', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $nametmp = PATH_ABSOLUTO_SITIO . '/tmp/' . $_SESSION["generales"]["codigoempresa"] . '-' . rand(1000000, 3000000) . '.' . $_SESSION["entrada"]["extension"];
        $f = fopen($nametmp, "wb");
        fwrite($f, base64_decode($_SESSION["entrada"]["base64"]));
        fclose($f);



        // ********************************************************************** //
        // Crea la conexión con la BD
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx;
        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        //
        $_SESSION["tramite"] = \funcionesRegistrales::retornarMregLiquidacion($mysqli, $_SESSION["entrada"]["idliquidacion"]);
        if ($_SESSION["tramite"] === false || empty($_SESSION["tramite"])) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Liquidación no localizada.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $secX = 0;
        $condicion = "idliquidacion=" . $_SESSION["entrada"]["idliquidacion"] . " and identificador='" . $_SESSION["entrada"]["identificador"] . "' and sectransaccion = '" . sprintf("%03s", $_SESSION["entrada"]["sectransaccion"]) . "'";
        $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $condicion, "secuenciaanexo");
        if ($arrTems && empty($arrTems)) {
            $secX = 0;
        } else {
            foreach ($arrTems as $s) {
                $secX = $s["secuenciaanexo"];
            }
        }
        unset($arrTems);

        //
        $secX++;

        //
        $arrCampos = array(
            'idliquidacion',
            'sectransaccion',
            'identificador',
            'secuenciaanexo',
            'expediente',
            'tipoanexo',
            'idradicacion',
            'numerorecibo',
            'numerooperacion',
            'identificacion',
            'nombre',
            'idtipodoc',
            'numdoc',
            'fechadoc',
            'txtorigendoc',
            'path',
            'tipoarchivo',
            'observaciones',
            'bandeja',
            'eliminado'
        );

        //
        $sectra = sprintf("%03s", $_SESSION["entrada"]["sectransaccion"]);

        // Por defecto signa los datos de la matricula afectada
        $expediente = $_SESSION["entrada"]["expediente"];
        $identificacion = $_SESSION["entrada"]["identificacion"];
        $nombre = $_SESSION["entrada"]["nombre"];
        $tipodoc = $_SESSION["entrada"]["idtipodoc"];
        $numdoc = $_SESSION["entrada"]["numdoc"];
        $fechadoc = str_replace(array("/", "-"), "", $_SESSION["entrada"]["fechadoc"]);
        $origendoc = $_SESSION["entrada"]["origendoc"];

        //         
        $bandeja = '4.-REGMER';

        // Almacena el registro en la BD
        $arrValores = array(
            $_SESSION["entrada"]["idliquidacion"],
            "'" . $sectra . "'",
            "'" . $_SESSION["entrada"]["identificador"] . "'",
            $secX,
            "'" . $expediente . "'",
            "'501'",
            0,
            "''",
            "''",
            "'" . $identificacion . "'",
            "'" . $nombre . "'",
            "'" . $tipodoc . "'",
            "'" . $numdoc . "'",
            "'" . $fechadoc . "'",
            "'" . $origendoc . "'",
            "'liquidacionmreg/" . $dirx . "/'",
            "'" . $_SESSION["entrada"]["extension"] . "'",
            "'" . addslashes(strtoupper($_SESSION["entrada"]["descripcion"])) . "'",
            "'" . $bandeja . "'",
            "'NO'"
        );

        $res = insertarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', $arrCampos, $arrValores, 'si');
        if ($res === false) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $_SESSION["generales"]["mensajeerror"];
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $idAnexo = $_SESSION["generales"]["lastId"];

        $pathSoporteUpload = $_SESSION["generales"]["codigoempresa"] . '/liquidacionmreg/' . $dirx . '/' . $idAnexo . '.' . $_SESSION["entrada"]["extension"];

        $pathAbsolutoRepositorio = PATH_ABSOLUTO_IMAGES . '/' . $pathSoporteUpload;

        copy($nametmp, $pathAbsolutoRepositorio);

        if (!file_exists($pathAbsolutoRepositorio)) {
            $mysqli->close();
            $_SESSION["jsonsalida"]["codigoerror"] = '9999';
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Anexo (fisico) no localizado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        } else {
            $pathRelativoRepositorio = PATH_RELATIVO_IMAGES . '/' . $pathSoporteUpload;
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Creado anexo #' . $idAnexo;
            $_SESSION["jsonsalida"]["link"] = TIPO_HTTP . HTTP_HOST . '/' . $pathRelativoRepositorio;
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
