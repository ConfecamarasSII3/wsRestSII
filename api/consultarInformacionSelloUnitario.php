<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarInformacionSelloUnitario {

    public function consultarInformacionSelloUnitario(API $api) {
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
        $inscripciones = array();
        $_SESSION['jsonsalida']['inscripciones'] = $inscripciones;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("libro", true);
        $api->validarParametro("registro", true);
        $api->validarParametro("dupli", true, false);

        if (!$api->validarToken('consultarInformacionSelloUnitario', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Construye arreglo de tipos documentales
        // ********************************************************************** // 
        $trd = array();
        $query = "SELECT * from bas_tipodoc where 1=1 order by idtipodoc";
        $mysqli->set_charset("utf8");
        $resQueryTD = $mysqli->query($query);
        if (!empty($resQueryTD)) {
            while ($trdtemp = $resQueryTD->fetch_array(MYSQLI_ASSOC)) {
                $trd[$trdtemp['idtipodoc']] = array(
                    'tiposirep' => $trdtemp['homologasirep'],
                    'tipodigitalizacion' => $trdtemp['homologadigitalizacion']
                );
            }
        }
        $resQueryTD->free();

        // ********************************************************************** //
        // Consulta el código de barras
        // ********************************************************************** // 
        $arrIns = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $_SESSION["entrada"]["libro"] . "' and registro='" . $_SESSION["entrada"]["registro"] . "' and dupli='" . $_SESSION["entrada"]["dupli"] . "'");
        if ($arrIns === false || empty($arrIns)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'inscripción no encontrada en la BD del sistema de información';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Inscripción no encontrada en la BD del sistema de información.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $inscripciones = array();

        $insc = array();

        $insc['libro'] = trim((string)$arrIns['libro']);
        $insc['registro'] = trim((string)$arrIns['registro']);
        $insc['fecha'] = trim((string)$arrIns['fecharegistro']);
        $insc['hora'] = trim((string)$arrIns['horaregistro']);
        $insc['usuario'] = $arrIns['operador'];
        $insc['usuariosii'] = $arrIns['usuarioinscribe'];
        $insc['tipoidentificacion'] = trim((string)$arrIns['tipoidentificacion']);
        $insc['identificacion'] = trim((string)$arrIns['identificacion']);
        $insc['nombre'] = trim((string)$arrIns['nombre']);
        $insc['matricula'] = trim((string)$arrIns['matricula']);
        $insc['proponente'] = trim((string)$arrIns['proponente']);
        $insc['acto'] = trim((string)$arrIns['acto']);
        $arrActos = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $insc['libro'] . "' and idacto='" . $insc['acto'] . "'");
        $nombreActo = isset($arrActos["nombre"]) ? $arrActos["nombre"] : "";

        $identificador = '';
        $idservicio = '';

        $insc['nacto'] = trim((string)$nombreActo);
        $insc['noticia'] = trim((string)$arrIns['noticia']);

        // 2017-08-29: JINT: Se adiciona por solicitud de DocXflow
        $insc['tipolibro'] = '';
        $insc['paginainicial'] = '';
        $insc['numeropaginas'] = '';

        if ($arrIns["descripcionlibro"] != '') {
            $insc['tipolibro'] = $arrIns["descripcionlibro"];
        }
        if (intval($arrIns["paginainicial"]) != 0) {
            $insc['paginainicial'] = $arrIns["paginainicial"];
        }
        if (intval($arrIns["numeropaginas"]) != 0) {
            $insc['numeropaginas'] = $arrIns["numeropaginas"];
        }

        $insc['idservicio'] = $idservicio;
        $insc['imagenes'] = array();

        // ********************************************************************** //
        // Retornar imágenes Sellos (505) y notificación sipref (518-519) 
        // ********************************************************************** // 

        $queryImagenes = "SELECT * FROM mreg_radicacionesanexos WHERE  eliminado<>'SI' and libro='" . $insc['libro'] . "' and registro='" . $insc['registro'] . "' and tipoanexo IN('505','518','519')";

        //WSIERRA : 2018-11-22  - A partir del nombre del acto concatena el texto que es impreso en el campo observaciones de la tabla mreg_radicacionesanexos
        $txtActo='ACTO: '.$insc['nacto'];
        $queryImagenes.=" and observaciones LIKE '%".$txtActo."'"; 

        $mysqli->set_charset("utf8");
        $resQueryImagenes = $mysqli->query($queryImagenes);

        // $insc['numeropaginas']  = $resQueryImagenes->num_rows;

        if (!empty($resQueryImagenes)) {
            while ($imagent = $resQueryImagenes->fetch_array(MYSQLI_ASSOC)) {
                $tiposirep = '';
                $tipodigitalizacion = '';
                if (isset($trd[$imagent["idtipodoc"]])) {
                    $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                    $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                }
                $imagen = array();
                // $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                    $imagen['url'] = obtenerUrlRepositorioS3Api($imagent['path']);
                } else {
                    $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                }
                $imagen['idanexo'] = ($imagent['idanexo']);
                $imagen['tipo'] = trim((string)$imagent['idtipodoc']);

                 //WSIERRA : 2018-11-22  - Incluye campo tipoanexo
                 $imagen['tipoanexo'] = trim((string)$imagent['tipoanexo']);

                $imagen['tiposirep'] = $tiposirep;
                $imagen['tipodigitalizacion'] = $tipodigitalizacion;
                $imagen['identificador'] = trim((string)$imagent['identificador']);
                $strings = explode(".", $imagent['path']);
                $imagen['formato'] = $strings[count($strings) - 1];
                $imagen['identificacion'] = trim((string)$imagent['identificacion']);
                $imagen['nombre'] = trim((string)$imagent['nombre']);
                $imagen['matricula'] = trim((string)$imagent['matricula']);
                $imagen['proponente'] = trim((string)$imagent['proponente']);
                $imagen['fechadocumento'] = trim((string)$imagent['fechadoc']);
                $imagen['origen'] = trim((string)$imagent['txtorigendoc']);
                $imagen['observaciones'] = mb_strtoupper(trim((string)$imagent['observaciones']), 'utf-8');
                $insc['imagenes'][] = $imagen;
            }
            $resQueryImagenes->free();
        }
        $inscripciones[] = $insc;

        //
        $_SESSION['jsonsalida']['inscripciones'] = $inscripciones;

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
