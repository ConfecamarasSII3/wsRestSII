<?php

class funcionesPowerFile {

    public static function generarSecuenciaCodigoBarrasPowerFile($dbx) {
        $cb = false;
        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
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
            $header = array(
                'function: newCaseTrigger',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'taskId: 130462646598ddd080ea780009585776',
                'userId: 85714721659a72800144f89026507541',
                'Content-Type: application/json'
            );

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
            if (isset($resultado["txt_NumRad"])) {
                $cb = ltrim(trim($resultado["txt_NumRad"]), "0");
            }
        }
        if ($cb && $cb != '') {
            $arrCampos = array(
                'codigobarras',
                'operacion',
                'recibo',
                'fecharadicacion',
                'matricula',
                'proponente',
                'idclase',
                'numid',
                'numdocextenso',
                'nombre',
                'estadofinal',
                'operadorfinal',
                'fechaestadofinal',
                'horaestadofinal',
                'sucursalfinal',
                'activos',
                'liquidacion',
                'reliquidacion',
                'actoreparto',
                'tipdoc',
                'numdoc',
                'oridoc',
                'mundoc',
                'fecdoc',
                'detalle',
                'canins',
                'candoc',
                'canfor',
                'cananx1',
                'cananx2',
                'cananx3',
                'cananx4',
                'cananx5',
                'sucursalradicacion',
                'tiprut',
                'numcaja',
                'escaneocompleto',
                'clavefirmado'
            );

//
            $arrValores = array(
                "'" . $cb . "'",
                "''", // operacion
                "''", // recibo
                "'" . date("Ymd") . "'",
                "''", // matricula
                "''", // proponente
                "''", // idclase
                "''", // numid
                "''", // numdocextenso
                "''", // nombre
                "'" . '01' . "'",
                "''", // operadorfinal
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''", // sucursalfinal
                0, // activos
                0, // liquidacion
                "''", // reliquidacion
                "''", // actoreparto
                "''", // tipdoc
                "''", // numdoc
                "''", // oridoc
                "''", // mundoc
                "''", // fecdoc
                "''", // detalle
                0, // canins
                0, // candoc
                0, // canfor
                0, // cananx1
                0, // cananx2
                0, // cananx3
                0, // cananx4
                0, // cananx5
                "''", // sucursal radicacion
                "''", // tiprut
                "''", // numcaja
                "''", // escaneo completo
                "''" // clave firmado
            );
            $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras', $arrCampos, $arrValores);
            if ($res === false) {
                return false;
            }
            $detalle = 'Creo codigo de barras No. ' . $cb . ', estado final: 01';
            actualizarLogMysqliApi($dbx, '069', $_SESSION["generales"]["codigousuario"], 'powerFile', '', '', '', $detalle, '', '');

        }
        return $cb;
    }

    public static function generarSecuenciaDevolutivoPowerFile($dbx = null) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        
        $nameLog = 'powerfile_' . date("Ymd");
        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
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

        \logApi::general2($nameLog, '', 'Consumo powerfile authenticationUser generarSecuenciaDevolutivoPowerFile ...');
        foreach ($resultado as $key => $valor) {
            \logApi::general2($nameLog, '', $key . ' => ' . $valor);
        }
        \logApi::general2($nameLog, '', '');

        $access_token = $resultado['access_token'];
        $expires_in = $resultado['expires_in'];
        $token_type = $resultado['token_type'];
        $scope = $resultado['scope'];
        $refresh_token = $resultado['refresh_token'];
        if ($access_token != '') {
            $header = [
                'function: newCaseDevolucion',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'taskId: 495910644598ddd083756f5079058936',
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
            $numdev = $resultado['txt_NumRadDevolucion'];
            if (strlen($numdev) == 15) {
                $numdev = ltrim(substr($resultado['txt_NumRadDevolucion'], 4), "0");
            } else {
                $numdev = ltrim($resultado['txt_NumRadDevolucion'], "0");
            }

            \logApi::general2($nameLog, '', 'Consumo powerfile newCaseDevolucion generarSecuenciaDevolutivoPowerFile ...');
            foreach ($resultado as $key => $valor) {
                \logApi::general2($nameLog, '', $key . ' => ' . $valor);
            }
            \logApi::general2($nameLog, '', '');
            return array($numdev, $case_id);
        } else {
            return false;
        }
    }

    public static function generarSecuenciaDesistimientoPowerFile($dbx = null) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'powerfile_' . date("Ymd");
        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
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
            \logApi::general2($nameLog, '', '');
            return array($numdev, $case_id);
        } else {
            return false;
        }
    }

    public static function reportarDevolutivoPowerFile($dbx = null, $pathsalida = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'powerfile_' . date("Ymd");

        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
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

            $arrCod = retornarRegistroMysqliApi($dbx,'mreg_est_codigosbarras', "codigobarras='" . $_SESSION["devolucion"]["datos"]["idradicacion"] . "'");
            $arrAnexosDev = retornarRegistrosMysqliApi($dbx,'mreg_radicacionesanexos', "idradicacion='" . $_SESSION["devolucion"]["datos"]["idradicacion"] . "' and tipoanexo IN('507','518','519')");

            $arrTipoDoc = retornarRegistrosMysqliApi($dbx,'bas_tipodoc', "1=1");
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

    public static function reportarDesistimientoPowerFile($dbx = null, $pathsalida = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'powerfile_' . date("Ymd");
        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
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

            $arrCod = retornarRegistroMysqliApi($dbx,'mreg_est_codigosbarras', "codigobarras='" . $_SESSION["desistimiento"]["datos"]["codigobarras"] . "'");
            $arrAnexosDes = retornarRegistrosMysqliApi($dbx,'mreg_radicacionesanexos', "idradicacion='" . $_SESSION["desistimiento"]["datos"]["codigobarras"] . "' and tipoanexo IN('511','518','519')");
            $arrTipoDoc = retornarRegistrosMysqliApi($dbx,'bas_tipodoc', "1=1");
            $trd = array();
            foreach ($arrTipoDoc as $tipo) {
                $trd[$tipo['idtipodoc']] = array(
                    'tiposirep' => $tipo['homologasirep'],
                    'tipodigitalizacion' => $tipo['homologadigitalizacion']
                );
            }

            $imagenesDesistimiento = array();

            foreach ($arrAnexosDes as $imagent) {

                $buscar = array('DESISTIMIENTO');
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
                    $imagenesDesistimiento[] = $imagen;
                }
            }




            $aVars = array(
                "tipodocdesistimiento" => $_SESSION["desistimiento"]["datos"]["idtipodocdesistimiento"],
                "numerodesistimiento" => $_SESSION["desistimiento"]["datos"]["numdocdesistimiento"],
                "fecha" => $_SESSION["desistimiento"]["datos"]["fechadocdesistimiento"],
                "hora" => date("His"),
                "usuario" => $_SESSION["desistimiento"]["datos"]["usuariodeclaradesistimiento"],
                "radicado" => $_SESSION["desistimiento"]["datos"]["codigobarras"],
                "idclase" => $arrCod["idclase"],
                "identificacion" => $arrCod["numid"],
                "nombre" => $arrCod["nombre"],
                "matricula" => $_SESSION["desistimiento"]["datos"]["matricula"],
                "proponente" => $_SESSION["desistimiento"]["datos"]["proponente"],
                "tipodoc" => $arrCod["tipdoc"],
                "numerodoc" => $arrCod["numdoc"],
                "fechadoc" => $arrCod["fecdoc"],
                "origendoc" => $arrCod["oridoc"],
                "municipiodoc" => $arrCod["mundoc"],
                "UrlImagePdf" => $imagenesDesistimiento
            );

//
            $misvar = json_encode($aVars);

            $header = [
                'function: sendVariableDesestimiento',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'Vars:' . $misvar,
                'taskId: 61230468759bffa0ac8f188015486614',
                'userId: 00000000000000000000000000000001',
                'caseId:' . $_SESSION["desistimiento"]["datos"]["process_id"],
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
            \logApi::general2($nameLog, '', 'Consumo powerfile sendVariableDesistimiento reportarDesistimientoPowerFile ...');
            \logApi::general2($nameLog, '', '$results (respusta del curl) ... ' . $results);
            foreach ($resultado as $key => $valor) {
                \logApi::general2($nameLog, '', $key . ' => ' . $valor);
            }
            \logApi::general2($nameLog, '', '');
        }
    }

    public static function reportarReingresoPowerFile($dbx = null, $cb = '', $rec = '', $usu = '', $fec = '', $hor = '', $sed = '') {
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

        echo $access_token;

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
            $arrImg = retornarRegistrosMysqliApi($dbx,"mreg_radicacionesanexos", "idradicacion='" . ltrim(trim($cb), "0") . "'", "idanexo");
        } else {
            $arrImg = retornarRegistrosMysqliApi($dbx,"mreg_radicacionesanexos", "idradicacion='" . ltrim(trim($cb), "0") . "' or numerorecibo='" . $rec . "'", "idanexo");
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