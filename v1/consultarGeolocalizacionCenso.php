<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait consultarGeolocalizacionCenso {

    public function consultarGeolocalizacionCenso(API $api) {
        require_once ('myErrorHandler.php');
        require_once ('generales.php');
        require_once ('LogSii2.class.php');
        $resError = set_error_handler('myErrorHandler');

        //cantidad de registros
        $limit = 100;
        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $arrCensados = array();
        $_SESSION["jsonsalida"]["censados"] = $arrCensados;
        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("matricula", false);
        $api->validarParametro("palabraclave", false);
        $api->validarParametro("semilla", true, true);

        if (!is_numeric($_SESSION["entrada"]["semilla"])) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Semilla no es un número entero';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        if (trim($_SESSION["entrada"]["matricula"]) == '' && trim($_SESSION["entrada"]["palabraclave"]) == '') {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se indicó un parámetro de búsqueda';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        if (!$api->validarToken('consultarGeolocalizacionCenso', $_SESSION["entrada"]["token"], $_SESSION["entrada"]["usuariows"])) {
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME);

        if ($mysqli->connect_error) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        $matricula = trim($_SESSION["entrada"]["matricula"]);
        $palabraclave = trim($_SESSION["entrada"]["palabraclave"]);

        if ($matricula != '') {
            $query = "SELECT id,id_censo, id_encuesta, fecha, matricula_establecimiento, "
                    . "nombre_establecimiento, municipio_establecimiento,latitud, longitud "
                    . "FROM mreg_censo_base_dinamico "
                    . "WHERE matricula_establecimiento='" . $matricula . "' "
                    . "AND legales_tiene_matricula='SI' ORDER BY fecha LIMIT " . $limit . " OFFSET " . $_SESSION["entrada"]["semilla"];
        }
        if ($palabraclave != '') {

            $query = "SELECT id,id_censo, id_encuesta, fecha, matricula_establecimiento, "
                    . "nombre_establecimiento, municipio_establecimiento,latitud, longitud "
                    . "FROM mreg_censo_base_dinamico "
                    . "WHERE nombre_establecimiento like '%" . $palabraclave . "%' ORDER BY fecha LIMIT " . $limit . " OFFSET " . $_SESSION["entrada"]["semilla"];
        }

        $mysqli->set_charset("utf8");
        $resQueryCenso = $mysqli->query($query);


        if (!empty($resQueryCenso)) {
            while ($encuestaTemp = $resQueryCenso->fetch_array(MYSQLI_ASSOC)) {
                $encuesta = array();
                $encuesta['id'] = $encuestaTemp['id'];
                $encuesta['id_censo'] = $encuestaTemp['id_censo'];
                $encuesta['id_encuesta'] = $encuestaTemp['id_encuesta'];
                $encuesta['fecha'] = $encuestaTemp['fecha'];
                $encuesta['matricula_establecimiento'] = $encuestaTemp['matricula_establecimiento'];
                //str_rot13
                $encuesta['nombre_establecimiento'] = ($encuestaTemp['nombre_establecimiento']);
                $encuesta['municipio_establecimiento'] = $encuestaTemp['municipio_establecimiento'];
                $encuesta['latitud'] = doubleval($encuestaTemp['latitud']);
                $encuesta['longitud'] = doubleval($encuestaTemp['longitud']);


                $urlInfografia = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . '/' .
                        $_SESSION["generales"]["codigoempresa"] . '/mreg/´/' .
                        $encuestaTemp['id_censo'] . '/' .
                        sprintf("%09s", $encuestaTemp['id_encuesta']) . '/';

                if (file_get_contents($urlInfografia . 'f1.jpg')) {
                    $encuesta['infografia1'] = $urlInfografia . 'f1.jpg';
                } else {
                    $encuesta['infografia1'] = '';
                }

                if (file_get_contents($urlInfografia . 'f2.jpg')) {
                    $encuesta['infografia2'] = $urlInfografia . 'f2.jpg';
                } else {
                    $encuesta['infografia2'] = '';
                }

                $arrCensados[] = $encuesta;
            }
            $resQueryCenso->free();
        } else {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'No se encontraron resultados para los datos solicitados';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $mysqli->close();
        unset($resQueryCenso);

        $_SESSION["jsonsalida"]["censados"] = $arrCensados;

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logSii2::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
