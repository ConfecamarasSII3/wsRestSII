<?php

class funcionesRegistrales_powerFileReportarDevolutivo {

    public static function powerFileReportarDevolutivo($mysqli, $pathsalida) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nameLog = 'powerfile_' . date("Ymd");

        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );

//body
        $data = array('pmhost' => 'http://bpm.cccucuta.org.co',
            'workspace' => 'cccucuta',
            'clientId' => 'NZPXVTXFOLFZTOGQVGGYUDXGCGLLRFQV',
            'clientSecret' => '13653564459a7194df19bb2021547438',
            'username' => 'confecamara',
            'password' => '123456789'
        );

        $fields = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/loginUserService.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        $resultado = json_decode($result, true);
        $access_token = $resultado['access_token'];
        $expires_in = $resultado['expires_in'];
        $token_type = $resultado['token_type'];
        $scope = $resultado['scope'];
        $refresh_token = $resultado['refresh_token'];

        if ($access_token != '') {
            $arrCod = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $_SESSION["devolucion"]["datos"]["idradicacion"] . "'");
            $arrAnexosDev = retornarRegistrosMysqliApi($mysqli, 'mreg_radicacionesanexos', "idradicacion='" . $_SESSION["devolucion"]["datos"]["idradicacion"] . "' and tipoanexo IN('507','518','519')");
            $arrTipoDoc = retornarRegistrosMysqliApi($mysqli, 'bas_tipodoc', "1=1");
            $trd = array();
            foreach ($arrTipoDoc as $tipo) {
                $trd[$tipo['idtipodoc']] = array(
                    'tiposirep' => $tipo['homologasirep'],
                    'tipodigitalizacion' => $tipo['homologadigitalizacion']
                );
            }

            $imagenesDevolucion = array();

            foreach ($arrAnexosDev as $imagent) {

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
                    $imagen['url'] = TIPO_HTTP . HTTP_HOST . '/' . PATH_RELATIVO_IMAGES . $_SESSION["generales"]["codigoempresa"] . '/' . $imagent["path"];
                    $imagen['idanexo'] = ($imagent['idanexo']);
                    $imagen['tipo'] = trim($imagent['idtipodoc']);
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
                    $imagenesDevolucion[] = $imagen;
                }
            }




            $aVars = array(
                "tipodocdevolucion" => $_SESSION["devolucion"]["datos"]["idtipodoc"],
                "numerodevolucion" => $_SESSION["devolucion"]["datos"]["numdoc"],
                "fecha" => $_SESSION["devolucion"]["datos"]["fechadevolcuion"],
                "hora" => $_SESSION["devolucion"]["datos"]["horadevolucion"],
                "usuario" => $_SESSION["devolucion"]["datos"]["idusuario"],
                "radicado" => $_SESSION["devolucion"]["datos"]["idradicacion"],
                "idclase" => $arrCod["idclase"],
                "identificacion" => $arrCod["numid"],
                "nombre" => $arrCod["nombre"],
                "matricula" => $_SESSION["devolucion"]["datos"]["matricula"],
                "proponente" => $_SESSION["devolucion"]["datos"]["proponente"],
                "tipodoc" => $arrCod["tipdoc"],
                "numerodoc" => $arrCod["numdoc"],
                "fechadoc" => $arrCod["fecdoc"],
                "origendoc" => $arrCod["oridoc"],
                "municipiodoc" => $arrCod["mundoc"],
                "UrlImagePdf" => $imagenesDevolucion
            );

//
            $misvar = json_encode($aVars);

            $header = [
                'function: sendVariableDevolucion',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'Vars:' . $misvar,
                'taskId: 495910644598ddd083756f5079058936',
                'userId: 00000000000000000000000000000001',
                // 'processId: 125488281598ddd07bbb5e4006297031',
// 'taskId: 130462646598ddd080ea780009585776',
// 'userId: 85714721659a72800144f89026507541',
                'caseId:' . $_SESSION["devolucion"]["datos"]["process_id"],
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/powerfileService.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $results = curl_exec($ch);
            curl_close($ch);

            $resultado = json_decode($results, true);
            \logApi::general2($nameLog, '', 'Consumo powerfile sendVariableDevolucion  reportarDevolutivoPowerFile ...');
            \logApi::general2($nameLog, '', '$results (respusta del curl) ... ' . $results);
            foreach ($resultado as $key => $valor) {
                \logApi::general2($nameLog, '', $key . ' => ' . $valor);
            }
            \logApi::general2($nameLog, '', '');
        }
    }

}

?>
