<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarInformacionSello {

    public function consultarInformacionSello(API $api) {
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
        $api->validarParametro("radicado", true);

        if (!$api->validarToken('consultarInformacionSello', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
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
            while ($trdtemp = $resQueryTD->fetch_array(MYSQL_ASSOC)) {
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
        $arrCB = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras like '" . $_SESSION["entrada"]["radicado"] . "'");
        if (!empty($arrCB)) {
            if ($arrCB["actoreparto"] == '09' || $arrCB["actoreparto"] == '53') {

                $queryInscripcionesSII = " SELECT DISTINCT "
                        . "cbl.libro, cbl.registro,cbl.acto, ins.fecharegistro as fecha, ins.horaregistro as hora, ins.operador as usuario, ins.tipoidentificacion, "
                        . "ins.identificacion, ins.nombre,'' as matricula, ins.proponente,ins.texto as noticia,'' as descripcionlibro, '' as paginainicial, '' as numeropaginas "
                        . "FROM mreg_est_codigosbarras_libros as cbl "
                        . "INNER JOIN mreg_est_inscripciones_proponentes ins on cbl.libro=ins.libro and REPLACE(cbl.registro,'\n', '')=ins.registro and cbl.acto=ins.acto "
                        . "WHERE cbl.codigobarras='" . $_SESSION["entrada"]["radicado"] . "'";
            } else {

                $queryInscripcionesSII = " SELECT DISTINCT "
                        . "cbl.libro, cbl.registro,cbl.acto, ins.fecharegistro as fecha, ins.horaregistro as hora, ins.operador as usuario, ins.tipoidentificacion, "
                        . "ins.identificacion, ins.nombre, ins.matricula, ins.proponente, ins.noticia, ins.descripcionlibro, ins.paginainicial, ins.numeropaginas "
                        . "FROM mreg_est_codigosbarras_libros as cbl "
                        . "INNER JOIN mreg_est_inscripciones ins on cbl.libro=ins.libro and REPLACE(cbl.registro,'\n', '')=ins.registro and cbl.acto=ins.acto "
                        . "WHERE cbl.codigobarras='" . $_SESSION["entrada"]["radicado"] . "'";
            }
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Radicado no encontrado en la BD del sistema de información';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Radicado no encontrado en la BD del sistema de información.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli->set_charset("utf8");
        $resQueryInscripcionesSII = $mysqli->query($queryInscripcionesSII);

        if (!empty($resQueryInscripcionesSII)) {
            while ($inscTemp = $resQueryInscripcionesSII->fetch_array(MYSQL_ASSOC)) {

                $insc['libro'] = trim((string)$inscTemp['libro']);
                $insc['registro'] = trim((string)$inscTemp['registro']);
                $insc['fecha'] = trim((string)$inscTemp['fecha']);
                $insc['hora'] = trim((string)$inscTemp['hora']);
                $insc['usuario'] = $inscTemp['usuario'];
                $insc['usuariosii'] = $insc['usuario'];
                $insc['tipoidentificacion'] = trim((string)$inscTemp['tipoidentificacion']);
                $insc['identificacion'] = trim((string)$inscTemp['identificacion']);
                $insc['nombre'] = trim((string)$inscTemp['nombre']);
                $insc['matricula'] = trim((string)$inscTemp['matricula']);
                $insc['proponente'] = trim((string)$inscTemp['proponente']);
                $insc['acto'] = trim((string)$inscTemp['acto']);

                if ($arrCB["actoreparto"] == '09' || $arrCB["actoreparto"] == '53') {
                    $arrActos = retornarRegistroMysqliApi($mysqli, 'mreg_actosproponente', "id='" . $insc['acto'] . "'");
                    $nombreActo = isset($arrActos["descripcion"]) ? $arrActos["descripcion"] : "";
                } else {
                    $arrActos = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $insc['libro'] . "' and idacto='" . $insc['acto'] . "'");
                    $nombreActo = isset($arrActos["nombre"]) ? $arrActos["nombre"] : "";
                    $tipoMatricula = isset($arrActos["creamatriculatipo"]) ? $arrActos["creamatriculatipo"] : "";
                }

                $identificador = '';
                $idservicio = '';
                /*
                  switch ($tipoMatricula) {
                  case "P" : $identificador = 'NUEVANAT';
                  break;
                  case "J" : $identificador = 'NUEVAJUR';
                  break;
                  case "E" : $identificador = 'NUEVAEST';
                  break;
                  case "S" : $identificador = 'NUEVASUC';
                  break;
                  case "A" :$identificador = 'NUEVAAGE';
                  break;
                  case "L" : $identificador = 'NUEVAESA';
                  break;
                  }

                  switch ($identificador) {
                  case 'NUEVANAT':
                  $idservicio = '01020101';
                  break;
                  case 'NUEVAJUR':
                  if ($insc['libro'] == 'RM09') {
                  $idservicio = '01030901';
                  }
                  if ($insc['libro'] == 'RM15') {
                  $idservicio = '01020108';
                  }
                  break;
                  case 'NUEVAEST':
                  $idservicio = '01020102';
                  break;
                  case 'NUEVASUC':
                  $idservicio = '01030601';
                  break;
                  case 'NUEVAAGE':
                  $idservicio = '01030601';
                  break;
                  case 'NUEVAESA':
                  $idservicio = '';
                  break;
                  }
                 */


                $insc['nacto'] = trim((string)$nombreActo);
                $insc['noticia'] = trim((string)$inscTemp['noticia']);

                // 2017-08-29: JINT: Se adiciona por solicitud de DocXflow
                $insc['tipolibro'] = '';
                $insc['paginainicial'] = '';
                $insc['numeropaginas'] = '';

                if ($arrCB["actoreparto"] != '09' && $arrCB["actoreparto"] != '53') {
                    if ($inscTemp["descripcionlibro"] != '') {
                        $insc['tipolibro'] = $inscTemp["descripcionlibro"];
                    }
                    if (intval($inscTemp["paginainicial"]) != 0) {
                        $insc['paginainicial'] = $inscTemp["paginainicial"];
                    }
                    if (intval($inscTemp["numeropaginas"]) != 0) {
                        $insc['numeropaginas'] = $inscTemp["numeropaginas"];
                    }
                }


                $insc['idservicio'] = $idservicio;

                $insc['imagenes'] = array();

                // ********************************************************************** //
                // Retornar imágenes Sellos (505) y notificación sipref (518-519) 
                // ********************************************************************** // 

               

                $queryImagenes = "SELECT * FROM mreg_radicacionesanexos WHERE eliminado<>'SI' and libro='" . $insc['libro'] . "' and registro='" . $insc['registro'] . "' and tipoanexo IN('505','518','519')";

                //WSIERRA : 2018-11-22  - A partir del nombre del acto concatena el texto que es impreso en el campo observaciones de la tabla mreg_radicacionesanexos
                $txtActo='ACTO: '.$insc['nacto'];
                $queryImagenes.=" and observaciones LIKE '%".$txtActo."'"; 

                $mysqli->set_charset("utf8");
                $resQueryImagenes = $mysqli->query($queryImagenes);

                // $insc['numeropaginas']  = $resQueryImagenes->num_rows;

                if (!empty($resQueryImagenes)) {
                    while ($imagent = $resQueryImagenes->fetch_array(MYSQL_ASSOC)) {
                        $tiposirep = '';
                        $tipodigitalizacion = '';
                        if (isset($trd[$imagent["idtipodoc"]])) {
                            $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                            $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                        }
                        $imagen = array();
                        //$imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
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
            }
            $_SESSION['jsonsalida']['inscripciones'] = $inscripciones;
            $resQueryInscripcionesSII->free();
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron resultados para el radicado solicitado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();
        unset($resQueryInscripcionesSII);
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
