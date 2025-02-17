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
        // require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
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
            
            //
            if ($arrCB["actoreparto"] == '09' || $arrCB["actoreparto"] == '53') {
                $res = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes',"idradicacion='" . $_SESSION["entrada"]["radicado"] . "'","libro,registro,dupli");
            } else {
                $res = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones',"idradicacion='" . $_SESSION["entrada"]["radicado"] . "'","libro,registro,dupli");  
            }
           

        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Radicado no encontrado en la BD del sistema de información';
            \logApi::general2('api_' . __FUNCTION__, $_SESSION["entrada"]["usuariows"], 'Radicado no encontrado en la BD del sistema de información.');
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if ($res && !empty ($res)) {
           foreach ($res as $res1) {
                $insc['libro'] = trim((string)$res1['libro']);
                $insc['registro'] = trim((string)$res1['registro']);
                $insc['dupli'] = trim((string)$res1['dupli']);
                $insc['fecha'] = trim((string)$res1['fecharegistro']);
                $insc['hora'] = trim((string)$res1['horaregistro']);
                $insc['usuario'] = $res1['usuarioinscribe'];
                $insc['usuariosii'] = $res1['usuarioinscribe'];
                $insc['tipoidentificacion'] = trim((string)$res1['tipoidentificacion']);
                $insc['identificacion'] = trim((string)$res1['identificacion']);
                $insc['nombre'] = trim((string)$res1['nombre']);

                if ($arrCB["actoreparto"] == '09' || $arrCB["actoreparto"] == '53') {
                    $insc['matricula'] = '';
                } else {
                    $insc['matricula'] = trim((string)$res1['matricula']);
                }
                


                $insc['proponente'] = trim((string)$res1['proponente']);
                $insc['acto'] = trim((string)$res1['acto']);

                if ($arrCB["actoreparto"] == '09' || $arrCB["actoreparto"] == '53') {
                    $arrActos = retornarRegistroMysqliApi($mysqli, 'mreg_actosproponente', "id='" . $res1['acto'] . "'");
                    $nombreActo = isset($arrActos["descripcion"]) ? $arrActos["descripcion"] : "";
                } else {
                    $arrActos = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $res1['libro'] . "' and idacto='" . $res1['acto'] . "'");
                    $nombreActo = isset($arrActos["nombre"]) ? $arrActos["nombre"] : "";
                    $tipoMatricula = isset($arrActos["creamatriculatipo"]) ? $arrActos["creamatriculatipo"] : "";
                }

                $identificador = '';
                $idservicio = '';
                $insc['nacto'] = trim((string)$nombreActo);


                if ($arrCB["actoreparto"] == '09' || $arrCB["actoreparto"] == '53') {
                    $insc['noticia'] = trim((string)$res1['texto']);
                } else {
                    $insc['noticia'] = trim((string)$res1['noticia']);
                }

               

                // 2017-08-29: JINT: Se adiciona por solicitud de DocXflow
                $insc['tipolibro'] = '';
                $insc['paginainicial'] = '';
                $insc['numeropaginas'] = '';

                if ($arrCB["actoreparto"] != '09' && $arrCB["actoreparto"] != '53') {
                    if ($res1["descripcionlibro"] != '') {
                        $insc['tipolibro'] = $res1["descripcionlibro"];
                    }
                    if (intval($res1["paginainicial"]) != 0) {
                        $insc['paginainicial'] = $res1["paginainicial"];
                    }
                    if (intval($res1["numeropaginas"]) != 0) {
                        $insc['numeropaginas'] = $res1["numeropaginas"];
                    }
                }


                $insc['idservicio'] = $idservicio;

                $insc['imagenes'] = array();


                // ********************************************************************** //
                // Retornar imágenes Sellos (505) y notificación sipref (518-519) 
                // ********************************************************************** // 
                if ($arrCB["actoreparto"] != '09' && $arrCB["actoreparto"] != '53') {
                    $cantidadInscripciones = contarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $res1['libro'] . "' and registro='" . $res1['registro'] . "'");
                    $queryImagenes = "SELECT * FROM mreg_radicacionesanexos WHERE eliminado<>'SI' and ((libro='" . $res1['libro'] . "' and registro='" . $res1['registro'] . "' and dupli='" . $res1["dupli"] . "' and tipoanexo IN('505')) or ((libro='" . $res1['libro'] . "' and registro='" . $res1['registro'] . "' and tipoanexo IN('518','519'))))";
                } else {
                    $cantidadInscripciones = 1;
                    $queryImagenes = "SELECT * FROM mreg_radicacionesanexos WHERE eliminado<>'SI' and ((libro='" . $res1['libro'] . "' and registro='" . $res1['registro'] . "' and tipoanexo IN('505')) or ((libro='" . $res1['libro'] . "' and registro='" . $res1['registro'] . "' and tipoanexo IN('518','519'))))";
                }
                if ($cantidadInscripciones > 1) {
                    $txtActo = 'ACTO: ' . $insc['nacto'];
                    $queryImagenes .= " and observaciones LIKE '%" . $txtActo . "'";
                }

                $resQueryImagenes = ejecutarQueryMysqliApi($mysqli,$queryImagenes);
                if (!empty($resQueryImagenes)) {
                    foreach ($resQueryImagenes as $imagent) {
                        $tiposirep = '';
                        $tipodigitalizacion = '';
                        if (isset($trd[$imagent["idtipodoc"]])) {
                            $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                            $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                        }
                        $imagen = array();
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
                }
                $inscripciones[] = $insc;
            }
            $_SESSION['jsonsalida']['inscripciones'] = $inscripciones;
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron resultados para el radicado solicitado';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();
        unset ($res);
        
        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
