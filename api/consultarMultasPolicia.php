<?php

function consultarMultasPoliciaSii($tid, $id, $idliq = 0) {
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
    require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
    $resError = set_error_handler('myErrorHandler');

    // ********************************************************************** //
    // Crea la conexión con la BD
    // ********************************************************************** // 
    $mysqli = conexionMysqliApi();

    if ($mysqli === false) {
        $_SESSION["jsonsalida"]["mensajeerror"] = 'Error de conexión a BD';
        return false;
    }

    //
    $reintentar = 3;
    $multadovencido = 'NO';
    while ($reintentar > 0) {
        $buscartoken = true;
        $name = PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt';
        if (file_exists($name)) {
            $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt');
            list ($token, $expira) = explode("|", $x);
            $act = date("Y-m-d H:i:s");
            if ($act <= $expira) {
                $buscartoken = false;
            }
        }

        if ($buscartoken) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://srvpqrs.policia.gov.co/ws_ponal/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "username=fvera@confecamaras.org.co&password=fveraPolicia2017*2018&grant_type=password");
            $result = curl_exec($ch);
            curl_close($ch);
            $resultado = json_decode($result, true);
            $access_token = $resultado['access_token'];
            $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
            $f = fopen($name, "w");
            fwrite($f, $access_token . '|' . $fecha);
            fclose($f);
        }

        //
        if (file_exists($name)) {
            $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt');
            list ($access_token, $expira) = explode("|", $x);
        }

        //
        $data = array(
            'codigoCamara' => CODIGO_EMPRESA,
            'tipoConsulta' => 'CC',
            'numeroIdentificacion' => $id
        );

        //
        $nameLog = 'api_validacionMultasPonal_' . date("Ymd");
        $fields = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://srvpqrs.policia.gov.co/ws_ponal/api/MultaVencida/ConsultaCedulaNit');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);

        //\logSii2::
        \logApi::general2($nameLog, $tid . '-' . $id, $result);

        //
        if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
            mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"]);
        }
        if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/")) {
            mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/", 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/");
        }
        if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/")) {
            mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/", 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/");
        }

        //
        $name1 = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/ponal/validaciones-' . date("Ym") . '.log';
        $f1 = fopen($name1, "a");
        fwrite($f1, date("Y-m-d") . '|' . date("His") . '|' . $tid . '|' . $id . '|' . $result . chr(13) . chr(10));
        fclose($f1);

        //
        if (isJson($result)) {
            $resultado = json_decode($result, true);
            if (isset($resultado["Message"])) {
                if ($resultado["Message"] == 'Authorization has been denied for this request.') {
                    unlink($name);
                    $reintentar--;
                } else {
                    $reintentar = 0;
                }
            } else {
                $fecx = date("Ymd");
                $horx = date("His");
                foreach ($resultado as $multa) {
                    if (TIPO_AMBIENTE == 'PRUEBAS') {
                        if ($id == '1004726620') {
                            $multa["MULTA_VENCIDA"] = 'SI';
                        }
                    }

                    $arrCampos = array(
                        'fecha',
                        'hora',
                        'tipoidentificacion',
                        'identificacion',
                        'nombres',
                        'apellidos',
                        'nit',
                        'razonsocial',
                        'estado',
                        'fechaimposicion',
                        'multavencida',
                        'direccionhechos',
                        'codigomunicipio',
                        'nombremunicipio',
                        'codigodpto',
                        'nombredpto',
                        'codigobarrio',
                        'nombrebarrio',
                        'numeralinfringido',
                        'articuloinfringido',
                        'idliquidacion'
                    );
                    $arrValores = array(
                        "'" . $fecx . "'",
                        "'" . $horx . "'",
                        "'" . $tid . "'",
                        "'" . $id . "'",
                        "'" . addslashes($multa["NOMBRES"]) . "'",
                        "'" . addslashes($multa["APELLIDOS"]) . "'",
                        "'" . $multa["NIT"] . "'",
                        "'" . addslashes($multa["RAZON_SOCIAL"]) . "'",
                        "'" . $multa["ESTADO"] . "'",
                        "'" . $multa["FECHA_IMPOSICION"] . "'",
                        "'" . $multa["MULTA_VENCIDA"] . "'",
                        "'" . addslashes($multa["DIRECCION_HECHOS"]) . "'",
                        "'" . $multa["COD_MUNICIPIO"] . "'",
                        "'" . addslashes($multa["MUNICIPIO"]) . "'",
                        "'" . $multa["COD_DEPARTAMENTO"] . "'",
                        "'" . addslashes($multa["DEPARTAMENTO"]) . "'",
                        "'" . $multa["COD_BARRIO"] . "'",
                        "'" . addslashes($multa["BARRIO"]) . "'",
                        "'" . addslashes($multa["ARTICULO_INFRINGIDO"]) . "'",
                        "'" . addslashes($multa["NUMERAL_INFRINGIDO"]) . "'",
                        $idliq
                    );
                    insertarRegistrosMysqliApi($mysqli, 'mreg_multas_ponal', $arrCampos, $arrValores);
                    //
                    if ($multa["MULTA_VENCIDA"] == 'SI') {
                        $multadovencido = 'SI';
                    }
                }
                $arrCampos = array(
                    'sincronizomultasponal',
                    'fechasincronizomultasponal',
                    'resultadosincronizomultasponal'
                );
                $arrValores = array(
                    "'SI'",
                    "'" . date("Ymd") . "'",
                    "'" . $multadovencido . "'"
                );
                regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                $reintentar = 0;
            }
        } else {
            $arrCampos = array(
                'sincronizomultasponal',
                'fechasincronizomultasponal',
                'resultadosincronizomultasponal'
            );
            $arrValores = array(
                "'SI'",
                "'" . date("Ymd") . "'",
                "'" . $multadovencido . "'"
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
            $reintentar = 0;
        }
    }

    //
    $mysqli->close();

    // return $return;
    return $multadovencido;
}

?>
