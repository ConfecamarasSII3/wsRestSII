<?php

class funcionesRegistrales_powerFileGenerarSecuenciaDesistimiento {

    public static function powerFileGenerarSecuenciaDesistimiento($mysqli) {

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $nameLog = 'powerfile_' . date("Ymd");

//
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

        \logApi::general2($nameLog, '', 'Consumo powerfile authenticationUser generarSecuenciaDesistimientoPowerFile ...');
        foreach ($resultado as $key => $valor) {
            \logApi::general2($nameLog, '', $key . ' => ' . $valor);
        }
        \logApi::general2($nameLog, '', '');

        $access_token = $resultado['access_token'];
        $expires_in = $resultado['expires_in'];
        $token_type = $resultado['token_type'];
        $scope = $resultado['scope'];
        $refresh_token = $resultado['refresh_token'];

//echo $access_token;
//Validacion de Autenticacion
//fragmento de codigo para obtener el numero de radicado
        if ($access_token != '') {
            $header = [
                'function: newCaseDesestimiento',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'taskId: 61230468759bffa0ac8f188015486614',
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
            $case_id = $resultado['APPLICATION'];
            $proce_id = $resultado['PROCESS'];
            $task_id = $resultado['TASK'];
            $numer_case = $resultado['APP_NUMBER'];
            $numdev = $resultado['txt_NumRadDesistimiento'];
            if (strlen($numdev) == 15) {
                $numdev = ltrim(substr($resultado['txt_NumRadDesistimiento'], 4), "0");
            } else {
                $numdev = ltrim($resultado['txt_NumRadDesistimiento'], "0");
            }

            \logApi::general2($nameLog, '', 'Consumo powerfile newCaseDesestimiento generarSecuenciaDesistimientoPowerFile ...');
            foreach ($resultado as $key => $valor) {
                \logApi::general2($nameLog, '', $key . ' => ' . $valor);
            }
            log::general2($nameLog, '', '');

            return array($numdev, $case_id);
        } else {
            return false;
        }
    }
}

?>
