<?php

class funcionesRegistrales_powerFileReportarReingreso {

    public static function powerFileReportarReingreso($mysqli, $cb, $rec, $usu, $fec, $hor, $sed) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nameLog = 'powerfile_' . date("Ymd");
        $post = "http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/serviceLogin.php";
        $headers = [
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        ];
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

        $resultado = json_decode($result, true);
        $access_token = $resultado['access_token'];
        $expires_in = $resultado['expires_in'];
        $token_type = $resultado['token_type'];
        $scope = $resultado['scope'];
        $refresh_token = $resultado['refresh_token'];

// echo $access_token;

        if ($access_token != '') {
            $header = [
                'function: newCaseReingreso',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'taskId: 3393249515a05ce576ca8a2068415149',
                'userId: 00000000000000000000000000000001',
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
            $case_id = $resultado['app_uid'];
        }

// ********************************************************************** //
// Retornar imágenes del radicado
// ********************************************************************** // 
        $imagenes = array();
        if (trim($rec) == '') {
            $arrImg = retornarRegistrosMysqliApi($mysqli, "mreg_radicacionesanexos", "idradicacion='" . ltrim(trim($cb), "0") . "'", "idanexo");
        } else {
            $arrImg = retornarRegistrosMysqliApi($mysqli, "mreg_radicacionesanexos", "idradicacion='" . ltrim(trim($cb), "0") . "' or numerorecibo='" . $rec . "'", "idanexo");
        }

        if (!empty($arrImg)) {
            foreach ($arrImg as $imagent) {
                $tiposirep = '';
                $tipodigitalizacion = '';
                if (isset($trd[$imagent["idtipodoc"]])) {
                    $tiposirep = $trd[$imagent["idtipodoc"]]["tiposirep"];
                    $tipodigitalizacion = $trd[$imagent["idtipodoc"]]["tipodigitalizacion"];
                }
                $imagen = array();
                $imagen['url'] = TIPO_HTTP . HTTP_HOST . "/" . PATH_RELATIVO_IMAGES . $_SESSION["entrada"]["codigoempresa"] . "/" . $imagent['path'];
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
                $imagen['observaciones'] = trim($imagent['observaciones']);
                $imagenes[] = $imagen;
            }
            unset($arrImg);
        }


//envio de variables
//enviar las variables
// para el envio de las variables este es un ejemplo se debe poner las variables que envia el SII

        $aVars = array(
            "fecha" => $fec,
            "hora" => $hor,
            "usuario" => $usu,
            "radicado" => $cb,
            "sede" => $sed,
            "UrlImagePdf" => $imagenes //arrar en como los ejemmplos enviados anterior mente
        );

        $misvar = json_encode($aVars);

        if ($access_token != '') {
            $header = [
                'function: sendVariableReingreso',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'Vars:' . $misvar,
                'taskId: 3393249515a05ce576ca8a2068415149',
                'userId: 00000000000000000000000000000001',
                'caseId:' . $case_id,
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/powerfileService.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $resultss = curl_exec($ch);
            curl_close($ch);

            $resultado = json_decode($results, true);
            \logApi::general2($nameLog, '', 'Consumo powerfile sendVariableReingreso reportarReingresoPowerFile ...');
            \logApi::general2($nameLog, '', '$results (respuesta del curl) ... ' . $results);
            foreach ($resultado as $key => $valor) {
                \logApi::general2($nameLog, '', $key . ' => ' . $valor);
            }
            \logApi::general2($nameLog, '', '');
        }
    }

}

?>
