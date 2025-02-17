<?php

namespace libreriaswsRestSII;

use libreriaswsRestSII\API;

trait retornarResumenMatriculadosCanceladosAno {

    public function retornarResumenMatriculadosCanceladosAno(API $api) {
        $nameLog = 'retornarResumenMatriculadosCanceladosAno_' . date("Ymd");
        ini_set('memory_limit', '2048M');

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $resError = set_error_handler('myErrorHandler');



        // array de respuesta
        $_SESSION["jsonsalida"] = array();
        $_SESSION["jsonsalida"]["codigoerror"] = '0000';
        $_SESSION["jsonsalida"]["mensajeerror"] = '';
        $_SESSION["jsonsalida"]["anos"] = array();

        // Verifica que  método de recepcion de parámetros sea POST
        if ($api->get_request_method() != "POST") {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'La petición debe ser POST';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        $api->validarParametro("usuariows", true);
        $api->validarParametro("token", true);
        $api->validarParametro("fechainicial", false);
        $api->validarParametro("fechafinal", false);


        // ********************************************************************** //
        // Valida que el usuario reportado tenga acceso a la BD y al metodo
        // ********************************************************************** // 
        $mysqli = conexionMysqliApi();
        if ($mysqli === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }

        //
        $inscritos = array();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "1=1", "matricula", "matricula,organizacion,categoria,ctrestmatricula");
        foreach ($temx as $x) {
            $inscritos[$x["matricula"]] = $x;
            $inscritos[$x["matricula"]]["encontrocancelacion"] = '';
        }
        unset($temx);
        \logApi::general2($nameLog, '', 'Cargo mreg_est_inscritos');

        //
        $actos = array();
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_actos', "1=1", "idlibro,idacto");
        foreach ($temx as $x) {
            $ind = $x["idlibro"] . '-' . $x["idacto"];
            $actos[$ind] = $x;
        }
        unset($temx);
        \logApi::general2($nameLog, '', 'Cargo mreg_actos');

        //
        $anos = array();

        // Matriculados
        $condicion = "fecmatricula between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "'";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $condicion, "fecmatricula", "matricula,fecmatricula,organizacion,categoria");
        if ($temx === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (empty($temx)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = "No encontro registros en mreg_est_inscritos";
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'Leyo matriculados en mreg_est_inscritos');


        foreach ($temx as $x) {
            $ano = substr($x["fecmatricula"], 0, 4);
            if (!isset($anos[$ano])) {
                $anos[$ano] = array();
                $anos[$ano]["ano"] = $ano;
            }
            if (!isset($anos[$ano]["matriculas"])) {
                $anos[$ano]["matriculas"] = array();
            }
            $org = $x["organizacion"];
            if ($x["categoria"] == '2') {
                $org = '97';
            }
            if ($x["categoria"] == '3') {
                $org = '98';
            }
            if (!isset($anos[$ano]["matriculas"][$org])) {
                $anos[$ano]["matriculas"][$org] = 0;
            }
            $anos[$ano]["matriculas"][$org] ++;
        }
        unset($temx);

        // cancelados
        $condicion = "feccancelacion between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "'";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $condicion, "fecmatricula", "matricula,feccancelacion,organizacion,categoria");
        if ($temx === false) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = $mysqli->connect_error;
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        if (empty($temx)) {
            $_SESSION["jsonsalida"]["codigoerror"] = "9999";
            $_SESSION["jsonsalida"]["mensajeerror"] = "No encontro registros en mreg_est_inscritos";
            $mysqli->close();
            $api->response($api->json($_SESSION["jsonsalida"]), 200);
        }
        \logApi::general2($nameLog, '', 'Leyo cancelados en mreg_est_inscritos');
        
        foreach ($temx as $x) {
            if (ltrim(trim($x["feccancelacion"]), "0") != '') {
                $ano = substr($x["feccancelacion"], 0, 4);
                if (!isset($anos[$ano])) {
                    $anos[$ano] = array();
                    $anos[$ano]["ano"] = $ano;
                }
                if (!isset($anos[$ano]["cancelaciones"])) {
                    $anos[$ano]["cancelaciones"] = array();
                }
                $org = $x["organizacion"];
                if ($x["categoria"] == '2') {
                    $org = '97';
                }
                if ($x["categoria"] == '3') {
                    $org = '98';
                }
                if (!isset($anos[$ano]["cancelaciones"][$org])) {
                    $anos[$ano]["cancelaciones"][$org] = 0;
                }
                $anos[$ano]["cancelaciones"][$org] ++;
                $inscritos[$x["matricula"]]["encontrocancelacion"] = 'si';
            }
        }
        unset($temx);

        //
        $condicion = "fecharegistro between '" . $_SESSION["entrada"]["fechainicial"] . "' and '" . $_SESSION["entrada"]["fechafinal"] . "'";
        $temx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', $condicion, "fecharegistro", "fecharegistro,libro,acto,matricula");
        foreach ($temx as $x) {

            $continuar = 'no';
            $ind = $x["libro"] . '-' . $x["acto"];
            if (isset($actos[$ind])) {
                if ($actos[$ind]["idgrupoacto"] == '002' || $actos[$ind]["idgrupoacto"] == '069' || $actos[$ind]["idgrupoacto"] == '010') {
                    $continuar = 'si';
                }
            }

            if ($continuar == 'si') {

                //
                $ano = substr($x["fecharegistro"], 0, 4);

                //
                if (!isset($anos[$ano])) {
                    $anos[$ano] = array();
                    $anos[$ano]["ano"] = $ano;
                }

                //
                if (!isset($anos[$ano]["cancelaciones"])) {
                    $anos[$ano]["cancelaciones"] = array();
                }

                //
                if (isset($inscritos[$x["matricula"]])) {
                    $org = $inscritos[$x["matricula"]]["organizacion"];
                    if ($inscritos[$x["matricula"]]["categoria"] == '2') {
                        $org = '97';
                    }
                    if ($inscritos[$x["matricula"]]["categoria"] == '3') {
                        $org = '98';
                    }


                    //
                    if (!isset($anos[$ano]["cancelaciones"][$org])) {
                        $anos[$ano]["cancelaciones"][$org] = 0;
                    }

                    //
                    if ($inscritos[$x["matricula"]]["encontrocancelacion"] == '') {
                        $anos[$ano]["cancelaciones"][$org] ++;
                        $inscritos[$x["matricula"]]["encontrocancelacion"] = 'si';
                    }
                }
            }
            unset($temx);
        }
        \logApi::general2($nameLog, '', 'Leyo cancelados en mreg_est_inscripciones');
        
        //
        $mysqli->close();

        //
        foreach ($anos as $cx) {
            $_SESSION["jsonsalida"]["anos"][] = $cx;
        }
        \logApi::general2($nameLog, '', 'genero resultado');

        // **************************************************************************** //
        // Resultado
        // **************************************************************************** //
        \logApi::peticionRest('api_' . __FUNCTION__);
        $json = $api->json($_SESSION["jsonsalida"]);
        $api->response(str_replace("\\/", "/", $json), 200);
    }

}
