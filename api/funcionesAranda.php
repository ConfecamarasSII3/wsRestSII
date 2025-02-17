<?php

class funcionesAranda {

    public static function peticionArandaAsignarPruebas($tokenAranda, $tck) {
        $casoAranda = implode("", $tck);
        $prefijoCaso = substr($casoAranda, 0, 2);

        switch ($prefijoCaso) {
            case 'RF':
                $tipoCaso = '4'; //Código del tipo caso : Requerimientos
                $estadoQA = '53'; //Código del estado : Pruebas y Calidad (Requerimientos)
                break;
            case 'IM':
                $tipoCaso = '1'; //Código del tipo caso : Incidentes
                $estadoQA = '56'; //Código del estado : Pruebas y Calidad (Incidentes)
                break;
            default:
                break;
        }

        $usrArandaOrigen = '456'; //Código del usuario : desarrollo
        $usrArandaDestino = '11'; //Código del usuario : frojas(QA)
        //$usrArandaDestino = '24'; //Código del usuario :Weymer
        $grupoId = '10'; //Código del grupo : Operaciones
        $razonId = '27'; //Código de razón : El desarrollo pasa a pruebas de calidad

        $arrAsign = array(
            array("Field" => 'StateId', "Value" => $estadoQA),
            array("Field" => 'GroupId', "Value" => $grupoId),
            array("Field" => 'ReasonId', "Value" => $razonId),
            array("Field" => 'SpecialistId', "Value" => $usrArandaDestino),
        );

        $jsonAsign = json_encode($arrAsign);
        $urlAsign = URL_API_ARANDA . "/item/update/" . $casoAranda . "/" . $tipoCaso . "/" . $usrArandaOrigen;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlAsign);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonAsign);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        $dataResp = json_decode($result, true);

        foreach ($dataResp as $registro) {
            if ($registro["Field"] == "result") {
                $dataRespSal = $registro["Value"];
            }
        }

        return $dataRespSal;
    }

    public static function peticionArandaAsignarEstadoFlujo($tokenAranda, $tck, $estadoFlujo = '') {

        $casoAranda = implode("", $tck);
        $prefijoCaso = substr($casoAranda, 0, 2);

        //En análisis técnico
        if ($estadoFlujo == 'AT') {
            switch ($prefijoCaso) {
                case 'RF':
                    $tipoCasoAranda = '4';
                    $estadoAranda = '52';
                    $razonAranda = '24';
                    break;
                case 'IM':
                    $tipoCasoAranda = '1';
                    $estadoAranda = '55';
                    $razonAranda = '24';
                    break;
                default:
                    break;
            }
        }

        //En proceso de solución
        if ($estadoFlujo == 'PS') {
            switch ($prefijoCaso) {
                case 'RF':
                    $tipoCasoAranda = '4';
                    $estadoAranda = '12';
                    $razonAranda = '25';
                    break;
                case 'IM':
                    $tipoCasoAranda = '1';
                    $estadoAranda = '5';
                    $razonAranda = '36';
                    break;
                default:
                    break;
            }
        }

        //En espera
        if ($estadoFlujo == 'EE') {
            switch ($prefijoCaso) {
                case 'RF':
                    $tipoCasoAranda = '4';
                    $estadoAranda = '61';
                    $razonAranda = '41';
                    break;
                case 'IM':
                    $tipoCasoAranda = '1';
                    $estadoAranda = '4';
                    $razonAranda = '41';
                    break;
                default:
                    break;
            }
        }

        $usrArandaOrigen = '456'; //Código del usuario : desarrollo
        $usrArandaDestino = '279'; //Código del usuario : fábrica
        $grupoIdArquitectura = '1';

        $arrAsign = array(
            array("Field" => 'StateId', "Value" => $estadoAranda),
            array("Field" => 'GroupId', "Value" => $grupoIdArquitectura),
            array("Field" => 'ReasonId', "Value" => $razonAranda),
            array("Field" => 'SpecialistId', "Value" => $usrArandaDestino),
        );

        $jsonAsign = json_encode($arrAsign);
        $urlAsign = URL_API_ARANDA . "/item/update/" . $casoAranda . "/" . $tipoCasoAranda . "/" . $usrArandaOrigen;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlAsign);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonAsign);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        $dataResp = json_decode($result, true);

        foreach ($dataResp as $registro) {
            if ($registro["Field"] == "result") {
                $dataRespSal = $registro["Value"];
            }
        }
        return $dataRespSal;
    }

    public static function peticionArandaNotas($tokenAranda, $tck, $leyendaVersion = '', $mostrarNotaCliente = 'no') {

        $casoAranda = implode("", $tck);
        $prefijoCaso = substr($casoAranda, 0, 2);

        switch ($prefijoCaso) {
            case 'RF':
                $tipoCaso = '4';
                break;
            case 'IM':
                $tipoCaso = '1';
                break;
            default:
                $tipoCaso = '';
                break;
        }

        /*
          IsPrivate es false la nota es pública para el cliente
          IsPrivate es true la nota es visible los para los especialistas
         */

        $ctrNota = true;
        if (trim($mostrarNotaCliente) == 'si') {
            $ctrNota = false;
        }

        $arrNotas = array("Description" => 'El caso:  ' . $casoAranda . '  hace parte de la versión titulada : ' . $leyendaVersion, "IsPrivate" => $ctrNota);
        $jsonNotas = json_encode($arrNotas);
        $urlNotas = URL_API_ARANDA . "/item/" . $casoAranda . "/" . $tipoCaso . "/note";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlNotas);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonNotas);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaReplicasActividades($tokenAranda, $tck, $textoNota, $mostrarNotaCliente = 'no') {

        $casoAranda = implode("", $tck);
        $prefijoCaso = substr($casoAranda, 0, 2);

        switch ($prefijoCaso) {
            case 'RF':
                $tipoCaso = '4';
                break;
            case 'IM':
                $tipoCaso = '1';
                break;
            default:
                break;
        }

        /*
          IsPrivate es false la nota es pública para el cliente
          IsPrivate es true la nota es visible los para los especialistas
         */

        $ctrNota = true;
        if (trim($mostrarNotaCliente) == 'si') {
            $ctrNota = false;
        }

        $notaReplica = $textoNota;
        $notaReplica .= '<strong>Nota:</strong> Los ajustes o mejoras a los desarrollos informados en esta nota no se reflejarán en producción ';
        $notaReplica .= 'hasta tanto se surta el proceso de QA y liberación.';
        $arrNotas = array("Description" => $notaReplica, "IsPrivate" => $ctrNota);
        $jsonNotas = json_encode($arrNotas);
        $urlNotas = URL_API_ARANDA . "/item/" . $casoAranda . "/" . $tipoCaso . "/note";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlNotas);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonNotas);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaConsultar($tokenAranda, $tck) {

        $casoAranda = implode("", $tck);

        $prefijoCaso = substr($casoAranda, 0, 2);

        switch ($prefijoCaso) {
            case 'RF':
                $tipoCaso = '4';
                break;
            case 'IM':
                $tipoCaso = '1';
                break;
            default:
                break;
        }

        $usrArandaOrigen = '456'; //Código del usuario : desarrollo
        $urlConsultar = URL_API_ARANDA . "/item/" . $casoAranda . "/" . $tipoCaso . "/" . $usrArandaOrigen . "?level=2";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlConsultar);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaListarCasosFab($tokenAranda) {

        $arrListar = array(
            'Paging' => array(
                'Start' => 1,
                'End' => 5000,
                'Size' => 0,
            ),
            'Criteria' => array(
                0 => array(
                    'Value' => 279,
                    'FieldName' => 'SpecialistId',
                    'LogicOperatorId' => 1,
                    'ComparisonOperatorId' => 5,
                ),
            ),
            'WhereCriteria' => array(),
            'Order' => array(
                'ColumnName' => 'RegistrationDate',
                'ModeId' => 2,
            ),
            'ViewId' => 5,
            'ProjectId' => 1,
        );

        $jsonListar = json_encode($arrListar);
        $urlListarCasos = URL_API_ARANDA . "/item/list";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlListarCasos);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonListar);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaListarCasosWeymer($tokenAranda) {

        $arrListar = array(
            'Paging' => array(
                'Start' => 1,
                'End' => 5000,
                'Size' => 0,
            ),
            'Criteria' => array(
                0 => array(
                    'Value' => 24,
                    'FieldName' => 'SpecialistId',
                    'LogicOperatorId' => 1,
                    'ComparisonOperatorId' => 5,
                ),
            ),
            'WhereCriteria' => array(),
            'Order' => array(
                'ColumnName' => 'RegistrationDate',
                'ModeId' => 2,
            ),
            'ViewId' => 5,
            'ProjectId' => 1,
        );

        $jsonListar = json_encode($arrListar);
        $urlListarCasos = URL_API_ARANDA . "/item/list";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlListarCasos);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonListar);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaListarCasosArq($tokenAranda) {

        $arrListar = array(
            'Paging' => array(
                'Start' => 1,
                'End' => 5000,
                'Size' => 0,
            ),
            'Criteria' => array(
                0 => array(
                    'Value' => 'ARQUITECTURA Y DESARROLLO',
                    'FieldName' => 'GroupName',
                    'LogicOperatorId' => 1,
                    'ComparisonOperatorId' => 5,
                ),
            ),
            'WhereCriteria' => array(),
            'Order' => array(
                'ColumnName' => 'RegistrationDate',
                'ModeId' => 2,
            ),
            'ViewId' => 5,
            'ProjectId' => 1,
        );

        $jsonListar = json_encode($arrListar);
        $urlListarCasos = URL_API_ARANDA . "/item/list";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlListarCasos);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonListar);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaListarCasosQA($tokenAranda) {

        $arrListar = array(
            'Paging' => array(
                'Start' => 1,
                'End' => 5000,
                'Size' => 0,
            ),
            'Criteria' => array(
                0 => array(
                    'Value' => "Pruebas y Calidad",
                    'FieldName' => 'StateName',
                    'LogicOperatorId' => 1,
                    'ComparisonOperatorId' => 5,
                ),
            ),
            'WhereCriteria' => array(),
            'Order' => array(
                'ColumnName' => 'RegistrationDate',
                'ModeId' => 2,
            ),
            'ViewId' => 5,
            'ProjectId' => 1,
        );

        $jsonListar = json_encode($arrListar);
        $urlListarCasos = URL_API_ARANDA . "/item/list";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlListarCasos);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonListar);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function peticionArandaListarCasosQAOK($tokenAranda) {
        $arrListar = array(
            'Paging' => array(
                'Start' => 1,
                'End' => 5000,
                'Size' => 0,
            ),
            'Criteria' => array(
                0 => array(
                    'Value' => "Pruebas finalizadas",
                    'FieldName' => 'StateName',
                    'LogicOperatorId' => 1,
                    'ComparisonOperatorId' => 5,
                ),
            ),
            'WhereCriteria' => array(),
            'Order' => array(
                'ColumnName' => 'RegistrationDate',
                'ModeId' => 2,
            ),
            'ViewId' => 5,
            'ProjectId' => 1,
        );

        $jsonListar = json_encode($arrListar);
        $urlListarCasos = URL_API_ARANDA . "/item/list";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlListarCasos);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonListar);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function solicitarTokenAranda() {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/commonAranda.php');

        $sessionAranda = '';
        $continuar = true;

        if (defined('URL_API_ARANDA') && URL_API_ARANDA == '') {
            $_SESSION["generales"]["mensajeerror"] = 'END POINT DEL SERVICIO WEB ARANDA NO ESTA DEFINIDO';
            $continuar = false;
        }

        if (defined('USR_API_ARANDA') && USR_API_ARANDA == '') {
            $_SESSION["generales"]["mensajeerror"] = 'USUARIO DEL SERVICIO WEB ARANDA NO ESTA DEFINIDO';
            $continuar = false;
        }

        if (defined('PWD_API_ARANDA') && PWD_API_ARANDA == '') {
            $_SESSION["generales"]["mensajeerror"] = 'PASSWORD DEL SERVICIO WEB ARANDA NO ESTA DEFINIDO';
            $continuar = false;
        }

        if ($continuar) {
            $data = array(
                array("Field" => "username", "Value" => USR_API_ARANDA),
                array("Field" => "password", "Value" => PWD_API_ARANDA),
            );

            $json_data = json_encode($data);
            $urlApiArandaLogin = URL_API_ARANDA . "/user/login";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlApiArandaLogin);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

            $result = curl_exec($ch);
            curl_close($ch);
            $dataResp = json_decode($result, true);

            if ($dataResp != "FailureOnLicense") {
                foreach ($dataResp as $registro) {
                    if ($registro["Field"] == "sessionId") {
                        $sessionAranda = $registro["Value"];
                    }
                }
            } else {
                $sessionAranda = $dataResp;
            }
            return $sessionAranda;
        } else {
            return $sessionAranda;
        }
    }

    public static function eliminarTokenAranda($tokenAranda) {
        $urlApiArandaLogout = URL_API_ARANDA . "/user/logout";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlApiArandaLogout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "authorization: " . $tokenAranda . ""));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public static function extraerTickets($cadenaTexto) {
        $re = '/[IM|RF]{2}+[-]{1}+[0-9]{1,8}+[-]{1}+[0-9]{1,8}+[-]{1}+[0-9]{1,8}/';
        preg_match_all($re, $cadenaTexto, $matches, PREG_SET_ORDER, 0);
        return \funcionesAranda::elementosUnicos($matches);
    }

    public static function extraerActividades($cadenaTexto) {
        $re = '/[AC]{2}+[-]{1}+[AE]{2}+[-]{1}+[0-9]{0,8}/';
        preg_match_all($re, $cadenaTexto, $matches, PREG_SET_ORDER, 0);
        return $matches;
    }

    public static function elementosUnicos($array) {
        $arraySinDuplicados = [];
        foreach ($array as $elemento) {
            if (!in_array($elemento, $arraySinDuplicados)) {
                $arraySinDuplicados[] = $elemento;
            }
        }
        return $arraySinDuplicados;
    }

}
