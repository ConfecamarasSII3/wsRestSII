<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarDevolucion {

    public function consultarDevolucion(API $api) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesS3V4.php');
        $resError = set_error_handler('myErrorHandler');

        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]['devolucion'] = '';
        $_SESSION["jsonsalida"]['fecha'] = '';
        $_SESSION["jsonsalida"]['hora'] = '';
        $_SESSION["jsonsalida"]['radicado'] = '';
        $_SESSION["jsonsalida"]['usuario'] = '';
        $_SESSION["jsonsalida"]['matricula'] = '';
        $_SESSION["jsonsalida"]['proponente'] = '';
        $_SESSION["jsonsalida"]['identificacion'] = '';
        $_SESSION["jsonsalida"]['nombre'] = '';
        $_SESSION["jsonsalida"]['email'] = '';
        $_SESSION["jsonsalida"]['tipotramite'] = '';
        $_SESSION["jsonsalida"]['tipodoc'] = '';
        $_SESSION["jsonsalida"]['observaciones'] = '';
        $_SESSION["jsonsalida"]['fechanotificacion'] = '';
        $_SESSION["jsonsalida"]['horanotificacion'] = '';
        $_SESSION["jsonsalida"]['tipodevolucion'] = '';
        $_SESSION["jsonsalida"]['devolucionparcial'] = '';
        $_SESSION["jsonsalida"]['estado'] = '';
        $imagenes = array();
        $_SESSION['jsonsalida']['imagenes'] = $imagenes;

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("tiporegistro", true);
        $api->validarParametro("devolucion", false); //numdoc
        $api->validarParametro("radicado", false); //numdoc

        $devolucion = trim($_SESSION["entrada"]["devolucion"]);
        $radicado = trim($_SESSION["entrada"]["radicado"]);

        if ($devolucion == '' && $radicado == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó devolucion o radicado a consultar';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarDevolucion', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = conexionMysqliApi();

        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexion a la BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Construye arreglo de tipos documentales
        // ********************************************************************** // 
        $trd = array();
        $temp = retornarRegistrosMysqliApi($mysqli, 'bas_tipodoc', "1=1", "idtipodoc");
        foreach ($temp as $t) {
            $trd[$t['idtipodoc']] = array(
                'tiposirep' => $t['homologasirep'],
                'tipodigitalizacion' => $t['homologadigitalizacion']
            );
        }

        // ********************************************************************** //
        // Consulta la devolución
        // ********************************************************************** // 
        $devolTemp = retornarRegistroMysqliApi($mysqli, 'mreg_devoluciones_nueva', "numdoc='" . $devolucion . "'");
        if ($devolTemp === false || empty($devolTemp)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Devolutivo no localizado en la BD.';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }


        $tipoTramite = trim($devolTemp['tipotramite']);
        $arrTramite = retornarRegistroMysqliApi($mysqli, 'mreg_tipotramite', "idtramite='" . $tipoTramite . "'");

        // if (($_SESSION["entrada"]["tiporegistro"] == '1' && $arrTramite["bandeja"] == '4.-REGMER') ||
        //        ($_SESSION["entrada"]["tiporegistro"] == '2' && $arrTramite["bandeja"] == '5.-REGESADL') ||
        //        ($_SESSION["entrada"]["tiporegistro"] == '3' && $arrTramite["bandeja"] == '6.-REGPRO')) {

            $_SESSION["jsonsalida"]['devolucion'] = trim($devolTemp['numdoc']);
            $_SESSION["jsonsalida"]['fecha'] = trim($devolTemp['fechadevolucion']);
            $_SESSION["jsonsalida"]['hora'] = trim($devolTemp['horadevolucion']);
            $_SESSION["jsonsalida"]['radicado'] = trim($devolTemp['idradicacion']);
            $_SESSION["jsonsalida"]['usuario'] = trim($devolTemp['idusuario']);
            $_SESSION["jsonsalida"]['matricula'] = trim($devolTemp['matricula']);
            $_SESSION["jsonsalida"]['proponente'] = trim($devolTemp['proponente']);
            $_SESSION["jsonsalida"]['identificacion'] = trim($devolTemp['identificacion']);
            $_SESSION["jsonsalida"]['nombre'] = trim($devolTemp['nombre']);
            $_SESSION["jsonsalida"]['email'] = trim($devolTemp['email']);
            $_SESSION["jsonsalida"]['tipotramite'] = $tipoTramite;
            $_SESSION["jsonsalida"]['tipodoc'] = '33'; // Devolutivo
            $_SESSION["jsonsalida"]['observaciones'] = \funcionesGenerales::restaurarEspeciales(trim(str_replace(array("\n", "\t", "?"), " ", $devolTemp['observaciones'])));
            $_SESSION["jsonsalida"]['fechanotificacion'] = trim($devolTemp['fechanotificacion']);
            $_SESSION["jsonsalida"]['horanotificacion'] = trim($devolTemp['horanotificacion']);
            $_SESSION["jsonsalida"]['tipodevolucion'] = trim($devolTemp['tipodevolucion']);
            $_SESSION["jsonsalida"]['devolucionparcial'] = trim($devolTemp['devolucionparcial']);
            $_SESSION["jsonsalida"]['estado'] = trim($devolTemp['estado']);

            // ********************************************************************** //
            // Retornar imágenes Devolución (507) y notificación sipref (518-519) 
            // ********************************************************************** // 
            $imagenes = array ();
            $imagents = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "idradicacion='" . $_SESSION["jsonsalida"]['radicado'] . "' and tipoanexo IN('507','518','519') and eliminado<>'SI'", "idanexo");
            if ($imagents && !empty ($imagents)) {
                foreach ($imagents as $imagent) {

                    $buscar = array('DEVOLUCIÓN', 'DEVOLUTIVO');
                    $observaciones = mb_strtoupper(trim($imagent['observaciones']), 'utf-8');

                    $encontroPalabra = 'no';
                    foreach ($buscar as $v) {
                        if (strpos($observaciones, $v) !== false) {
                            $encontroPalabra = 'si';
                            break;
                        }
                    }

                    if ($encontroPalabra == 'si') {
                        $tiposirep = '';
                        $tipodigitalizacion = '';
                        if (isset($trd[$imagent["idtipodoc"]])) {
                            $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                            $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                        }
                        $imagen = array();
                        if (TIPO_REPOSITORIO_NUEVAS_IMAGENES == 'EFS_S3') {
                            $imagen['url'] = \funcionesS3V4::obtenerUrlRepositorioS3($imagent['path']);
                        } else {
                            $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . "/" . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
                        }
                        $imagen['idanexo'] = ($imagent['idanexo']);
                        $imagen['tipo'] = trim($imagent['idtipodoc']);
                        $imagen['tipoanexo'] = trim($imagent['tipoanexo']);
                        $imagen['tiposirep'] = $tiposirep;
                        $imagen['tipodigitalizacion'] = $tipodigitalizacion;
                        $imagen['identificador'] = trim($imagent['identificador']);
                        $strings = explode(".", $imagent['path']);
                        $imagen['formato'] = $strings[count($strings) - 1];
                        $imagen['identificacion'] = trim($imagent['identificacion']);
                        $imagen['nombre'] = trim($imagent['nombre']);
                        $imagen['matricula'] = trim($imagent['matricula']);
                        $imagen['proponente'] = trim($imagent['proponente']);
                        $imagen['fechadocumento'] = trim($imagent['fechadoc']);
                        $imagen['origen'] = trim($imagent['txtorigendoc']);
                        $imagen['observaciones'] = $observaciones;
                        $imagenes[] = $imagen;
                    }
                }
            }
        // }
        $_SESSION['jsonsalida']['imagenes'] = $imagenes;

        $mysqli->close();

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
