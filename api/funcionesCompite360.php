<?php

class funcionesCompite360 {

    public static function ReportarDatosMatricula($mysqli, $nameLog = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarDatos";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a reportar datos basicos matricula');

        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Ajusto matricula ' . $matricula);
        $_SESSION["compite360"]["matriculaajustada"] = $matricula;

        $minimo = 0;
        foreach ($_SESSION["compite360"]["smlv"] as $s) {
            if ($s["fecha"] <= $_SESSION["expediente"]["fechadatos"]) {
                $minimo = $s["salario"];
            }
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro minimo para ' . $_SESSION["expediente"]["fechadatos"] . ' en ' . $minimo);
        // Control de afiliacion
        $afiliado = '0';
        if ($_SESSION["expediente"]["afiliado"] == '1') {
            $afiliado = '1';
        } else {
            $_SESSION["expediente"]["fechaafiliacion"] = '';
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro afiliacion ' . $afiliado);
        $_SESSION["compite360"]["afiliado"] = $afiliado;

        // estado de la matricula
        $estado = '';
        if ($_SESSION["expediente"]["estadomatricula"] == 'MA' || $_SESSION["expediente"]["estadomatricula"] == 'MI' || $_SESSION["expediente"]["estadomatricula"] == 'IA') {
            $estado = '0';
        }
        if ($_SESSION["expediente"]["estadomatricula"] == 'MC' || $_SESSION["expediente"]["estadomatricula"] == 'IC') {
            $estado = '1';
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro estado ' . $estado);
        $_SESSION["compite360"]["estado"] = $estado;

        // Organizacion juridica
        $organizacion = '';
        $clase = '0';
        $tipodomicilio = '0';
        switch ($_SESSION["expediente"]["organizacion"]) {
            case "01":
                $organizacion = '1';
                break;
            case "03":
                $organizacion = '3';
                break;
            case "04":
                $organizacion = '4';
                break;
            case "05":
                $organizacion = '5';
                break;
            case "06":
                $organizacion = '6';
                break;
            case "07":
                $organizacion = '7';
                break;
            case "08":
                $organizacion = '8';
                break;
            case "09":
                $organizacion = '9';
                break;
            case "10":
                $organizacion = '12';
                $clase = '1';
                break; // Civiles
            case "11":
                $organizacion = '10';
                break;
            case "12": // ESADL
                $organizacion = '';
                switch ($_SESSION["expediente"]["claseespesadl"]) {
                    case "20":
                        $organizacion = '20';
                        break;
                    case "21":
                        $organizacion = '21';
                        break;
                    case "22":
                        $organizacion = '22';
                        break;
                    case "23":
                        $organizacion = '23';
                        break;
                    case "24":
                        $organizacion = '24';
                        break;
                    case "25":
                        $organizacion = '25';
                        break;
                    case "26":
                        $organizacion = '26';
                        break;
                    case "27":
                        $organizacion = '27';
                        break;
                    case "28":
                        $organizacion = '28';
                        break;
                    case "29":
                        $organizacion = '29';
                        break;
                    case "30":
                        $organizacion = '30';
                        break;
                    case "31":
                        $organizacion = '31';
                        break;
                    case "32":
                        $organizacion = '32';
                        break;
                    case "33":
                        $organizacion = '33';
                        break;
                    case "34":
                        $organizacion = '34';
                        break;
                    case "35":
                        $organizacion = '35';
                        break;
                    case "36":
                        $organizacion = '36';
                        break;
                    case "37":
                        $organizacion = '37';
                        break;
                    case "38":
                        $organizacion = '38';
                        break;
                    case "39":
                        $organizacion = '39';
                        break;
                    case "40":
                        $organizacion = '40';
                        break;
                    case "41":
                        $organizacion = '41';
                        break;
                    case "42":
                        $organizacion = '42';
                        break;
                    case "43":
                        $organizacion = '42';
                        break; // Federaciones de cooperativas
                    case "44":
                        $organizacion = '42';
                        break; // Confederaciones de cooperativas
                    case "45":
                        $organizacion = '42';
                        break; // Federaciones de fondos de empleados
                    case "46":
                        $organizacion = '42';
                        break; // Confederaciones de fondos de empleados
                    case "47":
                        $organizacion = '42';
                        break; // Empresas comunitarias
                    case "48":
                        $organizacion = '42';
                        break; // Empresas solidarias de salud
                    case "49":
                        $organizacion = '44';
                        break;
                    case "50":
                        $organizacion = '42';
                        break; // Federaciones de EAT
                    case "51":
                        $organizacion = '42';
                        break; // Precooperativas
                    case "52":
                        $organizacion = '42';
                        break; // Federaciones
                    case "53":
                        $organizacion = '21';
                        break;
                    case "61":
                        $organizacion = '45';
                        break;
                    case "62":
                        $organizacion = '42';
                        break; // VeedurIas no jurIdicas    					
                    default:
                        $organizacion = '42';
                        break; // En caso de otras    					 
                }
                break;
            case "13":
                $organizacion = '';
                break; // No matriculados
            case "14": // Economia solidaria
                $organizacion = '';
                switch ($_SESSION["expediente"]["claseeconsoli"]) {
                    case "01":
                        $organizacion = '21';
                        break;
                    case "02":
                        $organizacion = '21';
                        break;
                    case "03":
                        $organizacion = '24';
                        break;
                    case "04":
                        $organizacion = '42';
                        break; // Ojo revisar // Empresas de servicio en forma de administracion publica cooperativa
                    case "05":
                        $organizacion = '22';
                        break;
                    case "06":
                        $organizacion = '44';
                        break;
                    case "07":
                        $organizacion = '23';
                        break;
                    case "08":
                        $organizacion = '28';
                        break;
                    case "09":
                        $organizacion = '42';
                        break; // Ojo revisar // Empresa comunitaria
                    case "10":
                        $organizacion = '31';
                        break; // Federaciones y confederaciones enviadas como "gremiales
                    default:
                        $organizacion = '42';
                        break; // En caso de otras    					 
                }
                break;
            case "15":
                $organizacion = '13';
                break;
            case "16":
                $organizacion = '16';
                break;
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro organizacion ' . $organizacion);
        $_SESSION["compite360"]["organizacion"] = $organizacion;
        $_SESSION["compite360"]["clase"] = $clase;

        if ($_SESSION["expediente"]["categoria"] == '1') {
            $tipodomicilio = '1';
        }
        if ($_SESSION["expediente"]["categoria"] == '2') {
            $tipodomicilio = '2';
        }
        if ($_SESSION["expediente"]["categoria"] == '3') {
            $tipodomicilio = '3';
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro tipo domicilio ' . $tipodomicilio);
        $_SESSION["compite360"]["tipodomicilio"] = $tipodomicilio;

        // Tipo identificacion
        $tipoidentificacion = '';
        $nroidenticacion = '';
        switch ($_SESSION["expediente"]["tipoidentificacion"]) {
            case "1":
                $tipoidentificacion = '1';
                break;
            case "2":
                $tipoidentificacion = '0';
                break;
            case "3":
                $tipoidentificacion = '2';
                break;
            case "4":
                $tipoidentificacion = '3';
                break;
            case "5":
                $tipoidentificacion = '4';
                break;
            case "7":
                $tipoidentificacion = '';
                break;
            case "E":
                $tipoidentificacion = '7';
                break;
        }

        //
        $nroidentificacion = '';
        if ($tipoidentificacion != '') {
            if ($_SESSION["expediente"]["tipoidentificacion"] == '2') {
                $nitx1 = \funcionesCompite360::separarDv360(trim($_SESSION["expediente"]["identificacion"]));
                $nroidentificacion = ltrim($nitx1["identificacion"], "0") . '-' . $nitx1["dv"];
            } else {
                $nroidentificacion = $_SESSION["expediente"]["identificacion"];
            }
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro identificacion ' . $tipoidentificacion . '-' . $nroidentificacion);
        $_SESSION["compite360"]["tipoidentificacion"] = $tipoidentificacion;
        $_SESSION["compite360"]["nroidentificacion"] = $nroidentificacion;

        //
        $nitpersonanatural = '';
        if ($_SESSION["expediente"]["organizacion"] == '01') {
            $nitpersonanatural = $_SESSION["expediente"]["nit"];
            if (ltrim($nitpersonanatural, "0") != '') {
                $nitx1 = \funcionesCompite360::separarDv360(trim($nitpersonanatural));
                $nitpersonanatural = ltrim($nitx1["identificacion"], "0") . '-' . $nitx1["dv"];
            }
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro nit ' . $nitpersonanatural);
        $_SESSION["compite360"]["nitpersonanatural"] = $nitpersonanatural;

        //
        $swimportador = '0';
        $swexportador = '0';
        if ($_SESSION["expediente"]["impexp"] == '1') {
            $swimportador = '1';
        }
        if ($_SESSION["expediente"]["impexp"] == '2') {
            $swexportador = '1';
        }
        if ($_SESSION["expediente"]["impexp"] == '3') {
            $swimportador = '1';
            $swexportador = '1';
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro impexp ' . $swimportador . ' ' . $swexportador);
        $_SESSION["compite360"]["swimportador"] = $swimportador;
        $_SESSION["compite360"]["swexportador"] = $swexportador;

        //
        $benart7 = '0';
        if ($_SESSION["expediente"]["art7"] == 'S') {
            $benart7 = '1';
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro benart7 ' . $benart7);
        $_SESSION["compite360"]["benart7"] = $benart7;

        // Tamaño de la empresa
        $tamano = '';
        if ($_SESSION["expediente"]["organizacion"] != '02' && $_SESSION["expediente"]["categoria"] != '2' && $_SESSION["expediente"]["categoria"] != '3') {
            $tamano = sprintf("%02s", $_SESSION["expediente"]["tamanoempresarial957codigo"]);
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro tamano - codigo' . $tamano);
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro tamano ' . $_SESSION["expediente"]["tamanoempresarial957"]);
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Ciiu tamano ' . $_SESSION["expediente"]["ciiutamanoempresarial"]);
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Ingresos tamano ' . $_SESSION["expediente"]["ingresostamanoempresarial"]);
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Año datos tamano ' . $_SESSION["expediente"]["anodatostamanoempresarial"]);
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Fecha datos tamano ' . $_SESSION["expediente"]["fechadatostamanoempresarial"]);
        $_SESSION["compite360"]["tamano"] = $tamano;

        //
        if (ltrim($nroidenticacion, "0") == '') {
            $nroidenticacion = 0;
        }

        //
        $ciiu1 = '';
        if (trim((string) $_SESSION["expediente"]["ciius"][1]) != '') {
            if (strlen($_SESSION["expediente"]["ciius"][1]) == 5) {
                $ciiu1 = substr($_SESSION["expediente"]["ciius"][1], 1);
            }
            if (strlen($_SESSION["expediente"]["ciius"][1]) == 4) {
                $ciiu1 = $_SESSION["expediente"]["ciius"][1];
            }
            if (strlen($_SESSION["expediente"]["ciius"][1]) != 4 && strlen($_SESSION["expediente"]["ciius"][1]) != 5) {
                $ciiu1 = '';
            }
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro ciiu1 ' . $ciiu1);
        $_SESSION["compite360"]["ciiu1"] = $ciiu1;

        //
        if (trim((string) $_SESSION["expediente"]["fechaconstitucion"]) == '') {
            $fecconstitucion = $_SESSION["expediente"]["fechamatricula"];
        } else {
            $fecconstitucion = $_SESSION["expediente"]["fechaconstitucion"];
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro fechaconstitucion ' . $fecconstitucion);
        $_SESSION["compite360"]["fecconstitucion"] = $fecconstitucion;

        // JINT : 2017-03-01: En caso de indefinido (99999999) enviar vacio
        //Weymer : 2019-05-27 : Se incluye el comidin 99999997 para envia vacio.
        if (($_SESSION["expediente"]["fechavencimiento"] == '99999999') || ($_SESSION["expediente"]["fechavencimiento"] == '99999997')) {
            $_SESSION["expediente"]["fechavencimiento"] = '';
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro fechavencimiento ' . $_SESSION["expediente"]["fechavencimiento"]);
        // Validaciones antes del consumo
        if (trim($ciiu1) == '') {
            // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Matricula sin ciiu');
            return false;
        }


        //
        $ReportarDatos = \funcionesCompite360::armarReportarDatosMatricula('1');
        $xml = '<?xml version="1.0" encoding="iso-8859-1" ?>';
        $xml .= '<ReportarDatos>';
        foreach ($ReportarDatos["ReportarDatos"] as $key => $valor) {
            $xml .= '<' . $key . '>' . $valor . '</' . $key . '>';
        }
        $xml .= '</ReportarDatos>';
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Informacion enviada ' . $xml);
        //
        $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
        $res = \funcionesCompite360::consumirWsCompite360($mysqli, $ReportarDatos, $nameLog);
        if ($res) {
            if ($res["codigoError"] == '0004' || $res["codigoError"] == '4') {
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Error en operacion No. 1');
                $ReportarDatos = \funcionesCompite360::armarReportarDatosMatricula('2');
                $res = \funcionesCompite360::consumirWsCompite360($mysqli, $ReportarDatos, $nameLog);
                // if ($res["codigoError"] != '0000') {
                //    \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Error en operacion No. 2');
                // }
            }

            //
            if ($res["codigoError"] == '0000' || $res["codigoError"] == '0') {
                actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarDatosMatricula', $xml, 'OK');
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Actualizo ReportarDatos en forma correcta');
                return true;
            } else {
                actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarDatosMatricula', $xml, $res["msgError"]);
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Error en ReportarDatos');
                return false;
            }
        } else {
            return false;
        }
    }

    public static function ReportarRenovaciones($mysqli, $nameLog) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarRenovaciones";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a ReportarRenovaciones');
        //
        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }

        // Fecha inicial, ultimos 5 años
        $fecini = sprintf("%04s", date("Y") - 4) . date("md");

        //
        $res1 = true;

        //
        if (isset($_SESSION["expediente"]["matricula"]) && ltrim(trim($_SESSION["expediente"]["matricula"]), "0") != '') {
            $agnos = array();
            $resx = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "matricula='" . $_SESSION["expediente"]["matricula"] . "' and (servicio between '01020200' and '01020299')", "fecoperacion asc");
            if ($resx && !empty($resx)) {
                foreach ($resx as $rx) {
                    if ($rx["fecoperacion"] >= $fecini) {
                        if (!isset($agnos[$rx["anorenovacion"]])) {
                            $agnos[$rx["anorenovacion"]] = 1;
                            $datos = array(
                                'ReportarRenovaciones' => array(
                                    'pusuario' => $_SESSION["compite360"]["usuario"],
                                    'pclave' => $_SESSION["compite360"]["contrasena"],
                                    'camara' => $_SESSION["compite360"]["camara"],
                                    'matricula' => $matricula,
                                    'agno' => $rx["anorenovacion"],
                                    'fecha_renovacion' => $rx["fecoperacion"]
                                )
                            );

                            //
                            $xml = '<?xml version="1.0" encoding="iso-8859-1" ?>';
                            $xml .= '<ReportarRenovaciones>';
                            foreach ($datos["ReportarRenovaciones"] as $key => $valor) {
                                $xml .= '<' . $key . '>' . $valor . '</' . $key . '>';
                            }
                            $xml .= '</ReportarRenovaciones>';
                            // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Informacion enviada ' . $xml);
                            //
                            $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
                            $_SESSION["compite360"]["expediente"] = $_SESSION["expediente"]["matricula"];
                            $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos);
                            if ($res) {
                                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Respuesta del metodo ' . $res["codigoError"] . ' - ' . $res["msgError"]);
                                if ($res["codigoError"] != '0000') {
                                    actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarRenovaciones', $xml, $res["msgError"]);
                                    $res1 = false;
                                } else {
                                    actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarRenovaciones', $xml, 'OK');
                                }
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
            unset($res);
        }
        return $res1;
    }

    public static function ReportarInformacionFinancieraVector($mysqli, $nameLog) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarInformacionFinancieraVector";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a ReportarInformacionFinancieraVector');
        //
        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Armo numero de matricula ' . $matricula);
        //
        $arrayTipos = array();
        $arrayAnos = array();
        $arrayValores = array();
        $iTipo = -1;

        //
        $cuantosregistros = 0;
        $anoini = date("Y") - 5;
        $resFin = retornarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', "matricula = '" . $_SESSION["expediente"]["matricula"] . "'", "matricula,anodatos,fechadatos");
        if ($resFin && !empty($resFin)) {
            foreach ($resFin as $row) {
                if ($row["anodatos"] >= $anoini) {
                    $cuantosregistros++;
                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '0';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["actcte"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '1';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["actfij"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '50';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["fijnet"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '2';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["actotr"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '3';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["actval"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '4';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["acttot"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '5';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["pascte"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '6';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["paslar"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '7';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["pastot"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '8';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["patnet"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '9';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["paspat"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '10';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["ingope"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '11';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["cosven"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '13';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["gasope"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '14';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["gasnoope"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '15';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["ingnoope"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '18';
                    $arrayAnos[$iTipo] = $row["anodatos"];
                    $arrayValores[$iTipo] = intval($row["utinet"]);

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '20';
                    $arrayAnos[$iTipo] = intval($row["anodatos"]);
                    $arrayValores[$iTipo] = $row["utiope"];
                }
            }
            if ($cuantosregistros > 0) {
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Encontro financiera');

                $datos = array(
                    'ReportarInformacionFinancieraVector' => array(
                        'pusuario' => $_SESSION["compite360"]["usuario"],
                        'pclave' => $_SESSION["compite360"]["contrasena"],
                        'camara' => $_SESSION["compite360"]["camara"],
                        'matricula' => $matricula,
                        'tipo_valor' => $arrayTipos,
                        'agno' => $arrayAnos,
                        'valor' => $arrayValores
                    )
                );

                //            
                $xml = \funcionesCompite360::array_to_xml($datos["ReportarInformacionFinancieraVector"], 1);
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Data a enviar ' . $xml);
                //
                $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
                $_SESSION["compite360"]["expediente"] = $_SESSION["expediente"]["matricula"];
                $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos);
                if ($res) {
                    // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Respuesta del metodo : ' . $res["codigoError"] . ' - ' . $res["msgError"]);
                    if ($res["codigoError"] == '0000') {
                        actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarInformacionFinancieraVector', $xml, 'OK');
                        return true;
                    } else {
                        actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarInformacionFinancieraVector', $xml, $res["msgError"]);
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public static function ReportarRepresentantes($mysqli, $nameLog) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarRepresentantes";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a ReportarRepresentantes');

        if ($_SESSION["expediente"]["organizacion"] > '02') {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_codcargos', "1=1", "id");
            if ($arrTem && !empty($arrTem)) {
                $arrDesVin = array();
                foreach ($arrTem as $t) {
                    $arrDesVin[$t["id"]] = \funcionesGenerales::utf8_decode($t["descripcion"]);
                }
            } else {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14'", "idcodigo");
                $arrDesVin = array();
                foreach ($arrTem as $t) {
                    $arrDesVin[$t["idcodigo"]] = \funcionesGenerales::utf8_decode($t["descripcion"]);
                }
            }
        }

        //
        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Armo numero de matricula ' . $matricula);

        if (count($_SESSION["expediente"]["vinculos"]) > 0) {
            //
            $arrayNombres = array();
            $arrayTipoIdes = array();
            $arrayIdentificaciones = array();
            $arrayRepresentacion = array();
            $arrayTipos = array();
            $iTipo = -1;

            foreach ($_SESSION["expediente"]["vinculos"] as $rowf) {

                //
                if ($rowf["vinculootros"] == '2170' || $rowf["vinculootros"] == '2171' || $rowf["vinculootros"] == '2172' || $rowf["vinculootros"] == '2173' || $rowf["vinculootros"] == '2176' || $rowf["vinculootros"] == '2177' || $rowf["vinculootros"] == '2178' || $rowf["vinculootros"] == '2179') {
                    $xTipopIde = '';
                    switch ($rowf["idtipoidentificacionotros"]) {
                        case "1":
                            $xTipoIde = 'C.C.';
                            break;
                        case "2":
                            $xTipoIde = 'NIT';
                            break;
                        case "3":
                            $xTipoIde = 'C.E.';
                            break;
                        case "4":
                            $xTipoIde = 'T.I.';
                            break;
                        case "5":
                            $xTipoIde = 'PSP';
                            break;
                        default:
                            $xTipoIde = 'OTROS';
                            break;
                    }
                    $iTipo++;
                    $arrayNombres[$iTipo] = \funcionesGenerales::utf8_decode($rowf["nombreotros"]);
                    $arrayTipoIdes[$iTipo] = $xTipoIde;
                    $arrayIdentificaciones[$iTipo] = $rowf["identificacionotros"];
                    $arrayRepresentacion[$iTipo] = '1';

                    if ($rowf["cargootros"] == '') {
                        $arrayTipos[$iTipo] = $arrDesVin[$rowf["vinculootros"]];
                    } else {
                        $arrayTipos[$iTipo] = $rowf["cargootros"];
                    }
                }
            }

            if ($iTipo > -1) {

                $datos = array(
                    'ReportarRepresentantes' => array(
                        'pusuario' => $_SESSION["compite360"]["usuario"],
                        'pclave' => $_SESSION["compite360"]["contrasena"],
                        'camara' => $_SESSION["compite360"]["camara"],
                        'matricula' => $matricula,
                        'nombres' => $arrayNombres,
                        'tipo_identificacion' => $arrayTipoIdes,
                        'identificacion' => $arrayIdentificaciones,
                        'representacion' => $arrayRepresentacion,
                        'tipo' => $arrayTipos
                    )
                );

                $xml = \funcionesCompite360::array_to_xml($datos["ReportarRepresentantes"], 1);
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Data a enviar ' . $xml);

                $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
                $_SESSION["compite360"]["expediente"] = $_SESSION["expediente"]["matricula"];
                $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos);
                if ($res) {
                    // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Respuesta del metodo : ' . $res["codigoError"] . ' - ' . $res["msgError"]);
                    if ($res["codigoError"] == '0000') {
                        actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarRepresentantes', $xml, 'OK');
                        return true;
                    } else {
                        actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarRepresentantes', $xml, $res["msgError"]);
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public static function ReportarJuntaDirectiva($mysqli, $nameLog) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarJuntaDirectiva";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a ReportarJuntaDirectiva');

        if ($_SESSION["expediente"]["organizacion"] > '02') {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_codcargos', "1=1", "id");
            if ($arrTem && !empty($arrTem)) {
                $arrDesVin = array();
                foreach ($arrTem as $t) {
                    $arrDesVin[$t["id"]] = \funcionesGenerales::utf8_decode($t["descripcion"]);
                }
            } else {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_tablassirep', "idtabla='14'", "idcodigo");
                $arrDesVin = array();
                foreach ($arrTem as $t) {
                    $arrDesVin[$t["idcodigo"]] = \funcionesGenerales::utf8_decode($t["descripcion"]);
                }
            }
        }

        //
        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Armo numero de matricula ' . $matricula);

        if (count($_SESSION["expediente"]["vinculos"]) > 0) {
            //
            $arrayNombres = array();
            $arrayTipoIdes = array();
            $arrayIdentificaciones = array();
            $arrayRenglones = array();
            $arrayTipos = array();
            $iTipo = -1;
            $rengpri = 0;
            $rengsup = 0;

            foreach ($_SESSION["expediente"]["vinculos"] as $rowf) {

                //
                if ($rowf["vinculootros"] == '2100' || $rowf["vinculootros"] == '2101' || $rowf["vinculootros"] == '2140' || $rowf["vinculootros"] == '2141' || $rowf["vinculootros"] == '2148' || $rowf["vinculootros"] == '2149') {
                    $xTipopIde = '';
                    switch ($rowf["idtipoidentificacionotros"]) {
                        case "1":
                            $xTipoIde = 'C.C.';
                            break;
                        case "2":
                            $xTipoIde = 'NIT';
                            break;
                        case "3":
                            $xTipoIde = 'C.E.';
                            break;
                        case "4":
                            $xTipoIde = 'T.I.';
                            break;
                        case "5":
                            $xTipoIde = 'PSP';
                            break;
                        default:
                            $xTipoIde = 'OTROS';
                            break;
                    }


                    if (
                            $rowf["vinculootros"] == '2100' ||
                            $rowf["vinculootros"] == '2140' ||
                            $rowf["vinculootros"] == '2148'
                    ) {
                        $tipovin = 'PRINCIPALES';
                        $rengpri++;
                        $reng = $rengpri;
                    } else {
                        $tipovin = 'SUPLENTES';
                        $rengsup++;
                        $reng = $rengsup;
                    }


                    $iTipo++;
                    $arrayNombres[$iTipo] = $rowf["nombreotros"];
                    $arrayTipoIdes[$iTipo] = $xTipoIde;
                    $arrayIdentificaciones[$iTipo] = $rowf["identificacionotros"];
                    $arrayRenglones[$iTipo] = $reng;
                    $arrayTipos[$iTipo] = $tipovin;
                }
            }

            if ($iTipo > -1) {
                $datos = array(
                    'ReportarJuntaDirectiva' => array(
                        'pusuario' => $_SESSION["compite360"]["usuario"],
                        'pclave' => $_SESSION["compite360"]["contrasena"],
                        'camara' => $_SESSION["compite360"]["camara"],
                        'matricula' => $matricula,
                        'nombres' => $arrayNombres,
                        'tipo_identificacion' => $arrayTipoIdes,
                        'identificacion' => $arrayIdentificaciones,
                        'renglon' => $arrayRenglones,
                        'tipo' => $arrayTipos
                    )
                );

                $xml = \funcionesCompite360::array_to_xml($datos["ReportarJuntaDirectiva"], 1);
                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Data a enviar ' . $xml);

                $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
                $_SESSION["compite360"]["expediente"] = $_SESSION["expediente"]["matricula"];
                $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos);
                if ($res) {
                    // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Respuesta del metodo : ' . $res["codigoError"] . ' - ' . $res["msgError"]);
                    if ($res["codigoError"] == '0000') {
                        actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarJuntaDirectiva', $xml, 'OK');
                        return true;
                    } else {
                        actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarJuntaDirectiva', $xml, $res["msgError"]);
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    public static function ReportarEmbargos($mysqli, $nameLog) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarEmbargos";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a ReportarEmbargos');
        //
        $res1 = true;

        //
        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Armo numero de matricula ' . $matricula);

        foreach ($_SESSION["expediente"]["ctrembargos"] as $rowf) {
            if ($rowf["estado"] == '1') {
                if (
                        $rowf["acto"] == '0900' ||
                        $rowf["acto"] == '0940' ||
                        $rowf["acto"] == '0991' ||
                        $rowf["acto"] == '1000' ||
                        $rowf["acto"] == '1040'
                ) {
                    $txtTipoDoc = retornarNombreTablasSirepMysqliApi($mysqli, '12', $rowf["tipdoc"]);
                    if (trim($txtTipoDoc) == '') {
                        $txtTipoDoc = 'DEMANDA O MEDIDA CAUTELAR';
                    }
                    $xml = '';
                    $datos = array(
                        'ReportarEmbargos' => array(
                            'pusuario' => $_SESSION["compite360"]["usuario"],
                            'pclave' => $_SESSION["compite360"]["contrasena"],
                            'camara' => $_SESSION["compite360"]["camara"],
                            'matricula' => $matricula,
                            'titulo_proceso' => 'EMBARGOS Y MEDIDAS CAUTELARES',
                            'de' => 'EL DEMANDANTE',
                            'contra' => 'EL MATRICULADO',
                            // 'entidad' => \EncodingNew::fixUTF8($rowf["txtorigen"]),
                            // 'descripcion' => \EncodingNew::fixUTF8($rowf["noticia"]),
                            'entidad' => ($rowf["txtorigen"]),
                            'descripcion' => ($rowf["noticia"]),
                            'tipo_embargo' => $txtTipoDoc,
                            'numero_embargo' => 'No. ' . $rowf["numdoc"],
                            'fecha_embargo' => $rowf["fecdoc"],
                            'fecha_inscripcion' => $rowf["fecinscripcion"],
                            'liquidacion' => $rowf["numreg"]
                        )
                    );

                    $xml = \funcionesCompite360::array_to_xml($datos["ReportarEmbargos"], 1);
                    // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Data a enviar ' . $xml);
                    $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
                    $_SESSION["compite360"]["expediente"] = $_SESSION["expediente"]["matricula"];
                    $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos);
                    if ($res) {
                        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Respuesta del metodo : ' . $res["codigoError"] . ' - ' . $res["msgError"]);
                        if ($res["codigoError"] != '0000') {
                            $res1 = false;
                            actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarEmbargos', $xml, $res["msgError"]);
                        } else {
                            actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarEmbargos', $xml, 'OK');
                        }
                    } else {
                        return false;
                    }
                }
            }
        }
        return $res1;
    }

    public static function ReportarEstablecimientos($mysqli, $nameLog = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        $_SESSION["compite360"]["metodo"] = "ReportarEstablecimientos";
        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Entro a reportar establecimientos');
        //
        $matricula = $_SESSION["expediente"]["matricula"];
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'S') {
            $matricula = '9' . substr($_SESSION["expediente"]["matricula"], 1);
        }
        if (substr($_SESSION["expediente"]["matricula"], 0, 1) == 'N') {
            $matricula = '8' . substr($_SESSION["expediente"]["matricula"], 1);
        }

        //
        $estabs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_propietarios', "matriculapropietario='" . $_SESSION["expediente"]["matricula"] . "'", "id");

        if ($estabs && !empty($estabs)) {
            $estEnc = 0;
            $estEnv = 0;

            foreach ($estabs as $e) {
                if (trim($e["codigocamara"]) == '' || $e["codigocamara"] == CODIGO_EMPRESA) {
                    $temEst = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $e["matricula"] . "'");
                    if ($temEst && !empty($temEst)) {
                        $estEnc++;
                        $estado = '0';
                        if ($temEst["ctrestmatricula"] == 'MC' || $temEst["ctrestmatricula"] == 'MF') {
                            $estado = '1';
                        }
                        $ciiu1 = $temEst["ciiu1"];
                        if (strlen($temEst["ciiu1"]) == 5) {
                            $ciiu1 = substr($temEst["ciiu1"], 1);
                        }
                        $ReportarEstablecimientos = array(
                            'ReportarEstablecimientos' => array(
                                'pusuario' => $_SESSION["compite360"]["usuario"],
                                'pclave' => $_SESSION["compite360"]["contrasena"],
                                'camara' => $_SESSION["compite360"]["camara"],
                                'matricula' => $matricula,
                                'p_matriculaEstab' => ltrim($e["matricula"], "0"),
                                'p_nombre' => $temEst["razonsocial"],
                                'p_fecha_matricula' => substr($temEst["fecmatricula"], 6, 2) . '/' . substr($temEst["fecmatricula"], 4, 2) . '/' . substr($temEst["fecmatricula"], 0, 4),
                                'p_idMunicipio' => $temEst["muncom"],
                                'p_direccion' => $temEst["dircom"],
                                'p_telefono' => $temEst["telcom1"],
                                'p_idestado' => $estado,
                                'p_vlr_activos' => $temEst["actvin"],
                                'p_fecha_renovacion' => substr($temEst["fecrenovacion"], 6, 2) . '/' . substr($temEst["fecrenovacion"], 4, 2) . '/' . substr($temEst["fecrenovacion"], 0, 4),
                                'p_ciius' => $ciiu1
                            )
                        );

                        $xml = '<?xml version="1.0" encoding="iso-8859-1" ?>';
                        $xml .= '<ReportarEstablecimientos>';
                        foreach ($ReportarEstablecimientos["ReportarEstablecimientos"] as $key => $valor) {
                            $xml .= '<' . $key . '>' . $valor . '</' . $key . '>';
                        }
                        $xml .= '</ReportarEstablecimientos>';
                        // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Informacion enviada ' . $xml);
                        //
                        $_SESSION["compite360"]["proceso"] = 'mercantil-esadl';
                        $_SESSION["compite360"]["expediente"] = $_SESSION["expediente"]["matricula"];
                        $res = \funcionesCompite360::consumirWsCompite360($mysqli, $ReportarEstablecimientos, $nameLog);
                        if ($res) {
                            // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Respuesta del metodo ' . $res["codigoError"] . ' - ' . $res["msgError"]);
                            if ($res["codigoError"] == '0000') {
                                actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarEstablecimientos', $xml, 'OK');
                                // \logApi::general2($nameLog, $_SESSION["expediente"]["matricula"], 'Actualizo ReportarEstablecimientos en forma correcta');
                                $estEnv++;
                            } else {
                                actualizarLogCompite360MysqliApi($mysqli, 'mercantil-esadl', $_SESSION["expediente"]["matricula"], 'ReportarEstablecimientos', $xml, $res["msgError"]);
                            }
                        } else {
                            return false;
                        }
                    }
                }
            }
            if ($estEnv == $estEnc) {
                return true;
            }
        } else {
            return true;
        }
    }

    public static function ReportarCPInsertarFinacieraVector($mysqli, $mat = '', $prop = '', $nameLog = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        if (!isset($_SESSION["generales"]["forzar"])) {
            $_SESSION["generales"]["forzar"] = 'SI';
        }

        if (!isset($_SESSION["compite360"]["pagina"])) {
            $_SESSION["compite360"]["pagina"] = 1;
        }

        if (!isset($_SESSION["compite360"]["paquete"])) {
            $_SESSION["compite360"]["paquete"] = 100;
        }

        //
        ob_start();
        $continuar = 'si';
        $iConTot = 0;
        while ($continuar == 'si') {

            // lectura de la BD
            $offset = ($_SESSION["compite360"]["pagina"] - 1) * $_SESSION["compite360"]["paquete"];

            //
            if ($prop != '') {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $prop . "'", "proponente");
            } else {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente > '0' and matricula > ''", 'proponente', '*', $offset, $_SESSION["compite360"]["paquete"]);
            }
            if (!$res1) {
                $continuar = 'no';
                // \logApi::general2($nameLog, 0, \funcionesGenerales::utf8_encode($_SESSION["generales"]["codigoempresa"] . '-ReportarCPInsertarFinacieraVector : Error leyendo registros de la BD :  mreg_est_proponentes : ' . $_SESSION["generales"]["mensajeerror"]));
            }

            //
            if (empty($res1)) {
                $continuar = 'no';
            }

            // $continuar = 'no';
            //
            $iCont = 0;
            foreach ($res1 as $row) {
                if (ltrim($row["matricula"], "0") != '' && ltrim(substr($row["inffin1510_fechacorte"], 0, 4), "0") != '' && substr($row["matricula"], 0) != 'S') {
                    $batchLinea = $iConTot . ' - ' . $iCont . ') Matricula ... ' . $row["matricula"] . ' (' . $row["proponente"] . ') ';
                    $matricula = $row["matricula"];
                    if (substr($row["matricula"], 0, 1) == 'S') {
                        $matricula = '9' . substr($row["matricula"], 1);
                    }
                    if (substr($row["matricula"], 0, 1) == 'N') {
                        $matricula = '8' . substr($row["matricula"], 1);
                    }
                    $arrayTipos = array();
                    $arrayAnos = array();
                    $arrayValores = array();
                    $iTipo = -1;

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '0';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_actcte"];

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '4';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_acttot"];

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '5';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_pascte"];

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '7';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_pastot"];

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '8';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_patnet"];

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '20';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_utiope"];

                    //
                    $iTipo++;
                    $arrayTipos[$iTipo] = '51';
                    $arrayAnos[$iTipo] = substr($row["inffin1510_fechacorte"], 0, 4);
                    $arrayValores[$iTipo] = $row["inffin1510_gasint"];

                    //
                    $operacion = '';
                    $trama = '';
                    if ($iTipo > -1) {
                        for ($i = 0; $i <= $iTipo; $i++) {
                            $trama .= base64_encode($arrayValores[$i]);
                        }
                        $tramamd5 = md5($trama);
                        $regCom360 = \funcionesCompite360::localizarRegistroCompite360($mysqli, 'compite360_estadisticas', "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $row["proponente"] . "'");
                        if ($regCom360 === false || empty($regCom360)) {
                            $operacion = '1'; // Insertar
                        } else {
                            if (strtoupper($_SESSION["generales"]["forzar"]) == 'SI') {
                                $operacion = '2';
                            } else {
                                if ($regCom360["tramamd5"] != $tramamd5) {
                                    $operacion = '2';
                                }
                            }
                        }
                        if ($operacion == '') {
                            $batchLinea .= " - " . $row["matricula"] . ' : enviada previamente';
                        }
                    } else {
                        $batchLinea .= ' - Sin informacion financiera';
                    }

                    if ($operacion != '') {
                        $errores = '';
                        $codigoError = '0000';
                        $datos = array(
                            'ReportarCPInsertarFinacieraVector' => array(
                                'pusuario' => $_SESSION["compite360"]["usuario"],
                                'pclave' => $_SESSION["compite360"]["contrasena"],
                                'camara' => $_SESSION["compite360"]["camara"],
                                'matricula' => $matricula,
                                'p_registro' => ltrim($row["proponente"], "0"),
                                'tipo_valor' => $arrayTipos,
                                'agno' => $arrayAnos,
                                'valor' => $arrayValores
                            )
                        );

                        //
                        $_SESSION["compite360"]["proceso"] = 'proponentes';
                        $_SESSION["compite360"]["expediente"] = $prop;
                        $xml = \funcionesCompite360::array_to_xml($mysqli, $datos["ReportarCPInsertarFinacieraVector"], 1);
                        $_SESSION["compite360"]["metodo"] = "ReportarCPInsertarFinacieraVector";
                        $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos, $nameLog);
                        if ($res) {
                            if ($res["codigoError"] == '0005') {
                                actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $_SESSION["expediente"]["matricula"], 'ReportarCPInsertarFinacieraVector', $xml, 'Matricula no encontrada en compite360');
                                $codigoError = '0005';
                                $errores = 'Matricula no encontrada en compite360';
                            } else {
                                if ($res["codigoError"] != '0000') {
                                    $errores = $res["codigoError"] . ' - ' . $res["msgError"];
                                    actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $_SESSION["expediente"]["matricula"], 'ReportarCPInsertarFinacieraVector', $xml, $res["msgError"]);
                                } else {
                                    actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $_SESSION["expediente"]["matricula"], 'ReportarCPInsertarFinacieraVector', $xml, 'OK');
                                }
                            }
                            if ($errores == '') {
                                $batchLinea .= ' - Respuesta ... Actualizando ' . $row["matricula"] . " : OK\r\n";
                            } else {
                                $batchLinea .= ' - Respuesta ... Actualizando con errores ' . $row["matricula"] . " : " . $errores . "\r\n";
                            }
                            if ($errores == '') {
                                $codigoError = '0000';
                                $msgError = '';
                            } else {
                                if ($codigoError != '0005') {
                                    $codigoError = '9999';
                                }
                                $msgError = $errores;
                            }

                            //
                            $arrCampos = array(
                                'fecha',
                                'hora',
                                'metodo',
                                'indice',
                                'codigoError',
                                'mensajeError',
                                'tramamd5',
                                'xmlEnviado'
                            );
                            $arrValores = array(
                                "'" . date("Ymd") . "'",
                                "'" . date("His") . "'",
                                "'" . $_SESSION["compite360"]["metodo"] . "'",
                                "'" . $row["proponente"] . "'",
                                "'" . $codigoError . "'",
                                "'" . addslashes($msgError) . "'",
                                "'" . $tramamd5 . "'",
                                "'" . addslashes($xml) . "'"
                            );

                            if ($operacion == '2') {
                                $condicion = "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $row["proponente"] . "'";
                                borrarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $condicion);
                            }
                            insertarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $arrCampos, $arrValores);
                        } else {
                            return false;
                        }
                    }

                    //
                    // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarCPInsertarFinacieraVector : ' . $batchLinea);
                }
            }
            if ($prop != '') {
                $continuar = 'no';
            } else {
                if ($continuar == 'si') {
                    $_SESSION["compite360"]["pagina"]++;
                }
            }
        }
    }

    public static function ReportarCPCLASIFICACIONES($mysqli, $mat = '', $prop = '', $nameLog = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        if (!isset($_SESSION["generales"]["forzar"])) {
            $_SESSION["generales"]["forzar"] = 'SI';
        }

        if (!isset($_SESSION["compite360"]["pagina"])) {
            $_SESSION["compite360"]["pagina"] = 1;
        }

        if (!isset($_SESSION["compite360"]["paquete"])) {
            $_SESSION["compite360"]["paquete"] = 100;
        }

        //
        ob_start();
        $continuar = 'si';
        $iConTot = 0;
        while ($continuar == 'si') {

            // lectura de la BD
            $offset = ($_SESSION["compite360"]["pagina"] - 1) * $_SESSION["compite360"]["paquete"];

            $query = "select * from mreg_est_proponentes where '1=1' ";
            $query .= "order by matricula limit " . $offset . "," . $_SESSION["compite360"]["paquete"];

            //
            if ($prop != '') {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $prop . "'", "proponente");
            } else {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente > '0' and matricula > ''", 'proponente', '*', $offset, $_SESSION["compite360"]["paquete"]);
            }
            if (!$res1) {
                $continuar = 'no';
                // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarCPCLASIFICACIONES : Error leyendo registros de la BD :  mreg_est_proponentes : ' . $_SESSION["generales"]["mensajeerror"]);
            }

            //
            if (empty($res1)) {
                $continuar = 'no';
            }

            // $continuar = 'no';
            //
            $iCont = 0;
            foreach ($res1 as $row) {

                if (ltrim($row["matricula"], "0") != '' && substr($row["matricula"], 0) != 'S') {

                    //
                    $batchLinea = $iConTot . ' - ' . $iCont . ') Matricula ... ' . $row["matricula"] . ' (' . $row["proponente"] . ') - Inicio';
                    // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarCPCLASIFICACIONES : ' . $batchLinea);
                    //
                    $matricula = $row["matricula"];
                    if (substr($row["matricula"], 0, 1) == 'S') {
                        $matricula = '9' . substr($row["matricula"], 1);
                    }
                    if (substr($row["matricula"], 0, 1) == 'N') {
                        $matricula = '8' . substr($row["matricula"], 1);
                    }


                    //
                    $arraySegmento = array();
                    $arrayFamilia = array();
                    $arrayClase = array();
                    $arrayDescripcion = array();
                    $iTipo = -1;

                    //
                    $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_unspsc', "proponente='" . $row["proponente"] . "'", "unspsc");
                    if ($arrTem && !empty($arrTem)) {
                        foreach ($arrTem as $t) {
                            $iTipo++;
                            $arraySegmento[$iTipo] = substr($t["unspsc"], 0, 2);
                            $arrayFamilia[$iTipo] = substr($t["unspsc"], 2, 2);
                            $arrayClase[$iTipo] = substr($t["unspsc"], 4, 2);
                            if (!isset($_SESSION["compite360"]["unspsc"][$t["unspsc"] . "00"])) {
                                $arrayDescripcion[$iTipo] = '';
                            } else {
                                $arrayDescripcion[$iTipo] = $_SESSION["compite360"]["unspsc"][$t["unspsc"] . "00"]["descripcion"];
                            }
                        }
                    }

                    //
                    $operacion = '';
                    $trama = '';
                    if ($iTipo > -1) {
                        for ($i = 0; $i <= $iTipo; $i++) {
                            $trama .= base64_encode($arraySegmento[$i]);
                            $trama .= base64_encode($arrayFamilia[$i]);
                            $trama .= base64_encode($arrayClase[$i]);
                        }
                        $tramamd5 = md5($trama);
                        $regCom360 = \funcionesCompite360::localizarRegistroCompite360($mysqli, 'compite360_estadisticas', "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $row["proponente"] . "'");
                        if ($regCom360 === false || empty($regCom360)) {
                            $operacion = '1'; // Insertar
                        } else {
                            if (strtoupper($_SESSION["generales"]["forzar"]) == 'SI') {
                                $operacion = '2';
                            } else {
                                if ($regCom360["tramamd5"] != $tramamd5) {
                                    $operacion = '2';
                                }
                            }
                        }
                        if ($operacion == '') {
                            $batchLinea .= " - " . $row["matricula"] . " : enviada previamente\r\n";
                        }
                    } else {
                        $batchLinea .= " - Sin clasificaciones UNSPSC\r\n";
                    }

                    if ($operacion != '') {
                        $errores = '';
                        $codigoError = '0000';
                        $datos = array(
                            'ReportarCPCLASIFICACIONES' => array(
                                'pusuario' => $_SESSION["compite360"]["usuario"],
                                'pclave' => $_SESSION["compite360"]["contrasena"],
                                'camara' => $_SESSION["compite360"]["camara"],
                                'registro' => $row["proponente"],
                                'segmento' => $arraySegmento,
                                'familia' => $arrayFamilia,
                                'clase' => $arrayClase,
                                'descripcion' => $arrayDescripcion
                            )
                        );

                        //
                        $xml = \funcionesCompite360::array_to_xml($datos["ReportarCPCLASIFICACIONES"], 1);
                        $_SESSION["compite360"]["metodo"] = "ReportarCPCLASIFICACIONES";
                        $_SESSION["compite360"]["proceso"] = 'proponentes';
                        $_SESSION["compite360"]["expediente"] = $prop;
                        $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos, $nameLog);
                        if ($res) {
                            if ($res["codigoError"] == '0005') {
                                actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarCPCLASIFICACIONES', $xml, 'Matricula no encontrada en compite360');
                                $codigoError = '0005';
                                $errores = 'Matricula no encontrada en compite360';
                            } else {
                                if ($res["codigoError"] != '0000') {
                                    $errores = $res["codigoError"] . ' - ' . $res["msgError"];
                                    actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarCPCLASIFICACIONES', $xml, $res["msgError"]);
                                } else {
                                    actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarCPCLASIFICACIONES', $xml, 'OK');
                                }
                            }
                            if ($errores == '') {
                                $batchLinea .= ' - Respuesta ... Actualizando ' . $row["matricula"] . " : OK\r\n";
                            } else {
                                $batchLinea .= ' - Respuesta ... Actualizando con errores ' . $row["matricula"] . " : " . $errores . "\r\n";
                            }
                            if ($errores == '') {
                                $codigoError = '0000';
                                $msgError = '';
                            } else {
                                if ($codigoError != '0005') {
                                    $codigoError = '9999';
                                }
                                $msgError = $errores;
                            }

                            //
                            $arrCampos = array(
                                'fecha',
                                'hora',
                                'metodo',
                                'indice',
                                'codigoError',
                                'mensajeError',
                                'tramamd5',
                                'xmlEnviado'
                            );
                            $arrValores = array(
                                "'" . date("Ymd") . "'",
                                "'" . date("His") . "'",
                                "'" . $_SESSION["compite360"]["metodo"] . "'",
                                "'" . $row["proponente"] . "'",
                                "'" . $codigoError . "'",
                                "'" . addslashes($msgError) . "'",
                                "'" . $tramamd5 . "'",
                                "'" . addslashes($xml) . "'"
                            );

                            if ($operacion == '2') {
                                $condicion = "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $row["proponente"] . "'";
                                borrarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $condicion);
                            }
                            insertarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $arrCampos, $arrValores);
                        } else {
                            return false;
                        }
                    }

                    //
                    // if ($batchLinea != '') {
                    //    \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarCPCLASIFICACIONES : ' . $batchLinea);
                    // }
                }
            }
            if ($prop != '') {
                $continuar = 'no';
            } else {
                if ($continuar == 'si') {
                    $_SESSION["compite360"]["pagina"]++;
                }
            }
        }
    }

    public static function ReportarCP_EXPERIENCIA($mysqli, $mat = '', $prop = '', $nameLog = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        if (!isset($_SESSION["generales"]["forzar"])) {
            $_SESSION["generales"]["forzar"] = 'SI';
        }
        if (!isset($_SESSION["compite360"]["pagina"])) {
            $_SESSION["compite360"]["pagina"] = 1;
        }

        if (!isset($_SESSION["compite360"]["paquete"])) {
            $_SESSION["compite360"]["paquete"] = 100;
        }

        //
        ob_start();
        $continuar = 'si';
        $iConTot = 0;
        while ($continuar == 'si') {

            // lectura de la BD
            $offset = ($_SESSION["compite360"]["pagina"] - 1) * $_SESSION["compite360"]["paquete"];

            //
            if ($prop != '') {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $prop . "'", "proponente");
            } else {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente > '0' and matricula > ''", 'proponente', '*', $offset, $_SESSION["compite360"]["paquete"]);
            }
            if (!$res1) {
                $continuar = 'no';
                // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarCP_EXPERIENCIA : Error leyendo registros de la BD :  mreg_est_proponentes : ' . $_SESSION["generales"]["mensajeerror"]);
            }

            //
            if (empty($res1)) {
                $continuar = 'no';
            }

            // $continuar = 'no';
            //
            $iCont = 0;
            foreach ($res1 as $row) {

                if (ltrim($row["matricula"], "0") != '' && substr($row["matricula"], 0) != 'S') {
                    $matricula = $row["matricula"];
                    if (substr($row["matricula"], 0, 1) == 'S') {
                        $matricula = '9' . substr($row["matricula"], 1);
                    }
                    if (substr($row["matricula"], 0, 1) == 'N') {
                        $matricula = '8' . substr($row["matricula"], 1);
                    }


                    //
                    $arraySegmento = array();
                    $arrayFamilia = array();
                    $arrayClase = array();
                    $arrayDescripcion = array();
                    $iTipo = -1;

                    //
                    $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_experiencia', "proponente='" . $row["proponente"] . "'", "secuencia");
                    if ($arrTem && !empty($arrTem)) {
                        foreach ($arrTem as $t) {
                            $operacion = '';
                            $trama = base64_encode($t["secuencia"]) . base64_encode($t["nombrecontratante"]) . base64_encode($t["valor"]);
                            $tramamd5 = md5($trama);
                            $regCom360 = \funcionesCompite360::localizarRegistroCompite360($mysqli, 'compite360_estadisticas', "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $row["proponente"] . "-" . $t["secuencia"] . "'");
                            if ($regCom360 === false || empty($regCom360)) {
                                $operacion = '1'; // Insertar
                            } else {
                                if (strtoupper($_SESSION["generales"]["forzar"]) == 'SI') {
                                    $operacion = '2';
                                } else {
                                    if ($regCom360["tramamd5"] != $tramamd5) {
                                        $operacion = '2';
                                    }
                                }
                            }
                            if ($operacion != '') {
                                $batchLinea = $iConTot . ' - ' . $iCont . ') Matricula ... ' . $row["matricula"] . ' (' . $row["proponente"] . ') - (' . $t["secuencia"] . ') ';
                                $errores = '';
                                $codigoError = '0000';
                                $datos = array(
                                    'ReportarCP_EXPERIENCIA' => array(
                                        'pusuario' => $_SESSION["compite360"]["usuario"],
                                        'pclave' => $_SESSION["compite360"]["contrasena"],
                                        'camara' => $_SESSION["compite360"]["camara"],
                                        'registro' => $row["proponente"],
                                        'nit_contratante' => '0',
                                        'contrato' => $t["secuencia"],
                                        'contratante' => $t["nombrecontratante"],
                                        'valor' => round($t["valor"], 0),
                                    )
                                );
                                $xml = \funcionesCompite360::array_to_xml($datos["ReportarCP_EXPERIENCIA"], 1);
                                $_SESSION["compite360"]["metodo"] = "ReportarCP_EXPERIENCIA";
                                $_SESSION["compite360"]["proceso"] = 'proponentes';
                                $_SESSION["compite360"]["expediente"] = $prop;
                                $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos, $nameLog);
                                if ($res === false) {
                                    if ($res["codigoError"] == '0005') {
                                        actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarCP_EXPERIENCIA', $xml, 'Matricula no encontrada en compite360');
                                        $codigoError = '0005';
                                        $errores = 'Matricula no encontrada en compite360';
                                    } else {
                                        if ($res["codigoError"] != '0000') {
                                            $errores = $res["codigoError"] . ' - ' . $res["msgError"];
                                            actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarCP_EXPERIENCIA', $xml, $res["msgError"]);
                                        } else {
                                            actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarCP_EXPERIENCIA', $xml, 'OK');
                                        }
                                    }
                                    if ($errores == '') {
                                        $batchLinea .= ' - Respuesta ... Actualizando ' . $row["matricula"] . " : OK" . "\r\n";
                                    } else {
                                        $batchLinea .= ' - Respuesta ... Actualizando con errores ' . $row["matricula"] . " : " . $errores . " - " . $xml . "\r\n";
                                    }
                                    if ($errores == '') {
                                        $codigoError = '0000';
                                        $msgError = '';
                                    } else {
                                        if ($codigoError != '0005') {
                                            $codigoError = '9999';
                                        }
                                        $msgError = $errores;
                                    }

                                    //
                                    $arrCampos = array(
                                        'fecha',
                                        'hora',
                                        'metodo',
                                        'indice',
                                        'codigoError',
                                        'mensajeError',
                                        'tramamd5',
                                        'xmlEnviado'
                                    );
                                    $arrValores = array(
                                        "'" . date("Ymd") . "'",
                                        "'" . date("His") . "'",
                                        "'" . $_SESSION["compite360"]["metodo"] . "'",
                                        "'" . $row["proponente"] . "-" . $t["secuencia"] . "'",
                                        "'" . $codigoError . "'",
                                        "'" . addslashes($msgError) . "'",
                                        "'" . $tramamd5 . "'",
                                        "'" . addslashes($xml) . "'"
                                    );

                                    if ($operacion == '2') {
                                        $condicion = "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $row["proponente"] . "-" . $t["secuencia"] . "'";
                                        borrarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $condicion);
                                    }
                                    insertarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $arrCampos, $arrValores);
                                    // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarCP_EXPERIENCIA : ' . $batchLinea);
                                } else {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
            if ($prop != '') {
                $continuar = 'no';
            } else {
                if ($continuar == 'si') {
                    $_SESSION["compite360"]["pagina"]++;
                }
            }
        }
    }

    public static function ReportarSpCP_CONTRATOS($mysqli, $mat = '', $prop = '', $nameLog = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        if (!isset($_SESSION["compite360"]["pagina"])) {
            $_SESSION["compite360"]["pagina"] = 1;
        }

        if (!isset($_SESSION["generales"]["forzar"])) {
            $_SESSION["generales"]["forzar"] = 'SI';
        }

        if (!isset($_SESSION["compite360"]["paquete"])) {
            $_SESSION["compite360"]["paquete"] = 100;
        }

        //
        ob_start();
        $continuar = 'si';
        $iConTot = 0;
        while ($continuar == 'si') {

            // lectura de la BD
            $offset = ($_SESSION["compite360"]["pagina"] - 1) * $_SESSION["compite360"]["paquete"];

            $query = "select * from mreg_est_proponentes where '1=1' ";
            $query .= "order by matricula limit " . $offset . "," . $_SESSION["compite360"]["paquete"];

            //
            if ($prop != '') {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente='" . $prop . "'", "proponente");
            } else {
                $res1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes', "proponente > '0' and matricula > ''", 'proponente', '*', $offset, $_SESSION["compite360"]["paquete"]);
            }
            if (!$res1) {
                $continuar = 'no';
                // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarSpCP_CONTRATOS : Error leyendo registros de la BD :  mreg_est_proponentes : ' . $_SESSION["generales"]["mensajeerror"]);
            }

            //
            if (empty($res1)) {
                $continuar = 'no';
            }

            // $continuar = 'no';
            //
            $iCont = 0;
            foreach ($res1 as $row) {

                if (ltrim($row["matricula"], "0") != '' && substr($row["matricula"], 0) != 'S') {

                    //
                    $matricula = $row["matricula"];
                    if (substr($row["matricula"], 0, 1) == 'S') {
                        $matricula = '9' . substr($row["matricula"], 1);
                    }
                    if (substr($row["matricula"], 0, 1) == 'N') {
                        $matricula = '8' . substr($row["matricula"], 1);
                    }


                    //
                    $arraySegmento = array();
                    $arrayFamilia = array();
                    $arrayClase = array();
                    $arrayDescripcion = array();
                    $iTipo = -1;

                    //
                    $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_proponentes_contratos', "proponente='" . $row["proponente"] . "'", "secuencia");

                    if ($arrTem && !empty($arrTem)) {
                        foreach ($arrTem as $t) {
                            $batchLinea = $iConTot . ' - ' . $iCont . ') Matricula ... ' . $row["matricula"] . ' (' . $row["proponente"] . ') ';
                            $operacion = '';
                            $trama = base64_encode($t["nitentidad"]) .
                                    base64_encode($t["nombreentidad"]) .
                                    base64_encode($t["numcontrato"]) .
                                    base64_encode($t["divarea"]) .
                                    base64_encode($t["estadocont"]) .
                                    base64_encode($t["fechaadj"]) .
                                    base64_encode($t["fechaini"]) .
                                    base64_encode($t["fechater"]) .
                                    base64_encode($t["valorcont"]);
                            $tramamd5 = md5($trama);
                            $tx = md5($row["proponente"] . $t["nitentidad"] . $t["idmunientidad"] . $t["numcontrato"]);
                            $regCom360 = \funcionesCompite360::localizarRegistroCompite360($mysqli, 'compite360_estadisticas', "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $tx . "'");
                            if ($regCom360 === false || empty($regCom360)) {
                                $operacion = '1'; // Insertar
                            } else {
                                if (strtoupper($_SESSION["generales"]["forzar"]) == 'SI') {
                                    $operacion = '2';
                                } else {
                                    if ($regCom360["tramamd5"] != $tramamd5) {
                                        $operacion = '2';
                                    }
                                }
                            }
                            if ($operacion != '') {
                                $errores = '';
                                $codigoError = '0000';
                                $datos = array(
                                    'ReportarSpCP_CONTRATOS' => array(
                                        'pusuario' => $_SESSION["compite360"]["usuario"],
                                        'pclave' => $_SESSION["compite360"]["contrasena"],
                                        'camara' => $_SESSION["compite360"]["camara"],
                                        'registro' => $row["proponente"],
                                        'nit_contratante' => $t["nitentidad"],
                                        'contrato' => $t["numcontrato"],
                                        'contratante' => $t["nombreentidad"],
                                        'estado' => $t["estadocont"],
                                        'fecha_inicio' => $t["fechaini"],
                                        'fecha_adjudicacion' => $t["fechaadj"],
                                        'fecha_terminacion' => $t["fechater"],
                                        'valor' => round($t["valorcont"], 0)
                                    )
                                );
                                $xml = \funcionesCompite360::array_to_xml($datos["ReportarSpCP_CONTRATOS"], 1);
                                $_SESSION["compite360"]["metodo"] = "ReportarSpCP_CONTRATOS";
                                $_SESSION["compite360"]["proceso"] = 'proponentes';
                                $_SESSION["compite360"]["expediente"] = $prop;
                                $res = \funcionesCompite360::consumirWsCompite360($mysqli, $datos, $nameLog);
                                if ($res) {
                                    if ($res["codigoError"] == '0005') {
                                        actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $_SESSION["expediente"]["matricula"], 'ReportarSpCP_CONTRATOS', $xml, 'Matricula no encontrada en compite360');
                                        $codigoError = '0005';
                                        $errores = 'Matricula no encontrada en compite360';
                                    } else {
                                        if ($res["codigoError"] != '0000') {
                                            $errores .= $res["codigoError"] . ' - ' . $res["msgError"];
                                            actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarSpCP_CONTRATOS', $xml, $res["msgError"]);
                                        } else {
                                            actualizarLogCompite360MysqliApi($mysqli, 'proponentes', $prop, 'ReportarSpCP_CONTRATOS', $xml, 'OK');
                                        }
                                    }
                                    if ($errores == '') {
                                        $batchLinea .= ' - Respuesta ... Actualizando ' . $row["matricula"] . " : OK\r\n";
                                    } else {
                                        $batchLinea .= ' - Respuesta ... Actualizando con errores ' . $row["matricula"] . " : " . $errores . "\r\n";
                                    }
                                    if ($errores == '') {
                                        $codigoError = '0000';
                                        $msgError = '';
                                    } else {
                                        if ($codigoError != '0005') {
                                            $codigoError = '9999';
                                        }
                                        $msgError = $errores;
                                    }

                                    //
                                    $arrCampos = array(
                                        'fecha',
                                        'hora',
                                        'metodo',
                                        'indice',
                                        'codigoError',
                                        'mensajeError',
                                        'tramamd5',
                                        'xmlEnviado'
                                    );
                                    $tx = md5($row["matricula"] . $t["nitentidad"] . $t["idmunientidad"] . $t["numcontrato"]);
                                    $arrValores = array(
                                        "'" . date("Ymd") . "'",
                                        "'" . date("His") . "'",
                                        "'" . $_SESSION["compite360"]["metodo"] . "'",
                                        "'" . $tx . "'",
                                        "'" . $codigoError . "'",
                                        "'" . addslashes($msgError) . "'",
                                        "'" . $tramamd5 . "'",
                                        "'" . addslashes($xml) . "'"
                                    );

                                    if ($operacion == '2') {
                                        $condicion = "metodo='" . $_SESSION["compite360"]["metodo"] . "' and indice='" . $tx . "'";
                                        borrarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $condicion);
                                    }
                                    insertarRegistrosMysqliApi($mysqli, 'compite360_estadisticas', $arrCampos, $arrValores);
                                } else {
                                    return false;
                                }
                            }
                            // \logApi::general2($nameLog, 0, $_SESSION["generales"]["codigoempresa"] . '-ReportarSpCP_CONTRATOS : ' . $batchLinea);
                        }
                    }
                }
            }
            if ($prop != '') {
                $continuar = 'no';
            } else {
                if ($continuar == 'si') {
                    $_SESSION["compite360"]["pagina"]++;
                }
            }
        }
    }

    public static function separarDv360($id) {
        $id = str_replace(",", "", $id);
        $id = str_replace(".", "", $id);
        $id = str_replace("-", "", $id);
        $entrada = sprintf("%016s", $id);
        $dv = substr($entrada, 15, 1);
        return array(
            'identificacion' => ltrim(substr($entrada, 0, 15), "0"),
            'dv' => $dv
        );
    }

    public static function consumirWsCompite360Prueba($mysqli, $datos, $nameLog = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler_consumirWsCompite360.php');
        set_error_handler('myErrorHandler_consumirWsCompite360');

        //
        if ($nameLog == '') {
            $nameLog = 'consumirWsCompite360_' . date("Ymd");
        }

        //
        $respuesta = array(
            "codigoError" => '',
            "msgError" => ''
        );

        try {
            $arrContextOptions = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false, 'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT));
            $options = array(
                'soap_version' => SOAP_1_2,
                'exceptions' => true,
                'trace' => 1,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create($arrContextOptions)
            );
            $client = new SoapClient($_SESSION["compite360"]["wsdl"], $options);
        } catch (\SoapFault $e) {
            $error = $e->getMessage();
            $_SESSION["generales"]["mensajeerror"] = $error;
            // \logApi::general2($nameLog, '', '(new) Error SoapFault : ' . $error);
            actualizarLogCompite360MysqliApi($mysqli, $_SESSION["compite360"]["proceso"], $_SESSION["compite360"]["expediente"], $_SESSION["compite360"]["metodo"], '', '(new) Error SoapFault : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $_SESSION["generales"]["mensajeerror"] = $error;
            // \logApi::general2($nameLog, '', '(new) Error Exception : ' . $error);
            actualizarLogCompite360MysqliApi($mysqli, $_SESSION["compite360"]["proceso"], $_SESSION["compite360"]["expediente"], $_SESSION["compite360"]["metodo"], '', '(new) Error Exception : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        //
        switch ($_SESSION["compite360"]["metodo"]) {
            case "ReportarDatos" : $_SESSION["comite360"]["objeto"] = 'ReportarDatos';
                break;
            case "ReportarEstablecimientos" : $_SESSION["comite360"]["objeto"] = 'ReportarEstablecimientos';
                break;
            case "ReportarInformacionFinancieraVector" : $_SESSION["comite360"]["objeto"] = 'ReportarInformacionFinancieraVector';
                break;
            case "ReportarRenovaciones" : $_SESSION["comite360"]["objeto"] = 'ReportarRenovaciones';
                break;
            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarLiquidaciones';
                break;
            case "ReportarEmbargos" : $_SESSION["comite360"]["objeto"] = 'ReportarEmbargos';
                break;
            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarPrendas';
                break;
            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarAumentoCapital';
                break;
            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarDisminucionCapital';
                break;
            case "ReportarJuntaDirectiva" : $_SESSION["comite360"]["objeto"] = 'ReportarJuntaDirectiva';
                break;
            case "ReportarRepresentantes" : $_SESSION["comite360"]["objeto"] = 'ReportarRepresentantes';
                break;
            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarEstadosFinancieros';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarCPInsertarFinaciera';
                break;
            case "ReportarCPInsertarFinacieraVector" : $_SESSION["comite360"]["objeto"] = 'ReportarCPInsertarFinacieraVector';
                break;
            case "ReportarCPCLASIFICACIONES" : $_SESSION["comite360"]["objeto"] = 'ReportarCPCLASIFICACIONES';
                break;

            case "ReportarSpCP_CONTRATOS" : $_SESSION["comite360"]["objeto"] = 'ReportarSpCP_CONTRATOS';
                break;

            case "ReportarCP_EXPERIENCIA" : $_SESSION["comite360"]["objeto"] = 'ReportarCP_EXPERIENCIA';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarCP_SITUACION_CONTROL';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarMultas';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'ReportarSanciones';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'BorrarMultas';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'BorrarSanciones';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'BorrarEmbargos';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'BorrarPrendas';
                break;

            case "" : $_SESSION["comite360"]["objeto"] = 'BorrarProponente';
                break;
        }

        //
        try {
            $res = $client->$_SESSION["compite360"]["metodo"]($datos);
            unset($client);
        } catch (\SoapFault $e) {
            $error = $e->getMessage();
            $_SESSION["generales"]["mensajeerror"] = $error;
            // \logApi::general2($nameLog, '', '(client) Error SoapFault : ' . $error);
            actualizarLogCompite360MysqliApi($mysqli, $_SESSION["compite360"]["proceso"], $_SESSION["compite360"]["expediente"], $_SESSION["compite360"]["metodo"], '', '(client) Error SoapFault : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $_SESSION["generales"]["mensajeerror"] = $error;
            // \logApi::general2($nameLog, '', '(client) Error Exception : ' . $error);
            actualizarLogCompite360MysqliApi($mysqli, $_SESSION["compite360"]["proceso"], $_SESSION["compite360"]["expediente"], $_SESSION["compite360"]["metodo"], '', '(client) Error Exception : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        //
        $t = (array) $res;
        // \logApi::general2($nameLog, '', serialize($t));
        //
        if (is_array($t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores)) {
            foreach ($t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores as $errorx) {
                if ($errorx->Codigo == '0') {
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                } else {
                    $respuesta["codigoError"] = '9993';
                    $respuesta["msgError"] .= $errorx->Descripcion . '<bR>';
                }
            }
        } else {
            if ($t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores->Codigo == '0') {
                $respuesta["codigoError"] = '0000';
                $respuesta["msgError"] = '';
            } else {
                $respuesta["codigoError"] = $t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores->Codigo;
                $respuesta["msgError"] = $t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores->Descripcion;
            }
        }
        unset($par);
        unset($res);
        unset($client);
        return $respuesta;
    }

    public static function consumirWsCompite360($mysqli, $datos, $nameLog = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler_consumirWsCompite360.php');
        set_error_handler('myErrorHandler_consumirWsCompite360');

        //
        if ($nameLog == '') {
            $nameLog = 'consumirWsCompite360_' . date("Ymd");
        }

        //
        $respuesta = array(
            "codigoError" => '',
            "msgError" => ''
        );
        try {
            $client = new SoapClient($_SESSION["compite360"]["wsdl"], array('encoding' => 'iso8859-1'));
            $res = $client->__soapCall($_SESSION["compite360"]["metodo"], (array) $datos);
            // $res = $client->$_SESSION["compite360"]["metodo"]($datos);
            unset($client);
        } catch (\SoapFault $e) {
            $error = $e->getMessage();
            $_SESSION["generales"]["mensajeerror"] = $error;
            // \logApi::general2($nameLog, '', 'Error SoapFault : ' . $error);
            unset($client);
            actualizarLogCompite360MysqliApi($mysqli, $_SESSION["compite360"]["proceso"], $_SESSION["compite360"]["expediente"], $_SESSION["compite360"]["metodo"], '', 'Error SoapFault : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $_SESSION["generales"]["mensajeerror"] = $error;
            // \logApi::general2($nameLog, '', 'Error Exception : ' . $error);
            unset($client);
            actualizarLogCompite360MysqliApi($mysqli, $_SESSION["compite360"]["proceso"], $_SESSION["compite360"]["expediente"], $_SESSION["compite360"]["metodo"], '', 'Error Exception : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }

        //
        $t = (array) $res;
        // \logApi::general2($nameLog, '', serialize($t));
        //
        if (is_array($t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores)) {
            foreach ($t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores as $errorx) {
                if ($errorx->Codigo == '0') {
                    $respuesta["codigoError"] = '0000';
                    $respuesta["msgError"] = '';
                } else {
                    $respuesta["codigoError"] = '9993';
                    $respuesta["msgError"] .= $errorx->Descripcion . '<bR>';
                }
            }
        } else {
            if ($t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores->Codigo == '0') {
                $respuesta["codigoError"] = '0000';
                $respuesta["msgError"] = '';
            } else {
                $respuesta["codigoError"] = $t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores->Codigo;
                $respuesta["msgError"] = $t[$_SESSION["compite360"]["metodo"] . "Result"]->Errores->Descripcion;
            }
        }
        unset($par);
        unset($res);
        unset($client);
        return $respuesta;
    }

    public static function array_to_xml($array, $level = 1) {
        $xml = '';
        if ($level == 1) {
            $xml .= '<?xml version="1.0" encoding="ISO-8859-1"?>' .
                    "\n<array>\n";
        }
        foreach ($array as $key => $value) {
            $key = strtolower($key);
            if (is_array($value)) {
                $multi_tags = false;
                foreach ($value as $key2 => $value2) {
                    if (is_array($value2)) {
                        $xml .= str_repeat("\t", $level) . "<$key>\n";
                        $xml .= \funcionesCompite360::array_to_xml($value2, $level + 1);
                        $xml .= str_repeat("\t", $level) . "</$key>\n";
                        $multi_tags = true;
                    } else {
                        if (trim((string) $value2) != '') {
                            if (htmlspecialchars($value2) != $value2) {
                                $xml .= str_repeat("\t", $level) .
                                        "<$key><![CDATA[$value2]]>" .
                                        "</$key>\n";
                            } else {
                                $xml .= str_repeat("\t", $level) .
                                        "<$key>$value2</$key>\n";
                            }
                        }
                        $multi_tags = true;
                    }
                }
                if (!$multi_tags and count($value) > 0) {
                    $xml .= str_repeat("\t", $level) . "<$key>\n";
                    $xml .= \funcionesCompite360::array_to_xml($value, $level + 1);
                    $xml .= str_repeat("\t", $level) . "</$key>\n";
                }
            } else {
                if (trim((string) $value) != '') {
                    if (htmlspecialchars($value) != $value) {
                        $xml .= str_repeat("\t", $level) . "<$key>" .
                                "<![CDATA[$value]]></$key>\n";
                    } else {
                        $xml .= str_repeat("\t", $level) .
                                "<$key>$value</$key>\n";
                    }
                }
            }
        }
        if ($level == 1) {
            $xml .= "</array>\n";
        }
        return $xml;
    }

    public static function localizarRegistroCompite360($bd, $tabla, $query) {
        $res = retornarRegistrosMysqliApi($bd, $tabla, $query, "id");
        if ($res === false) {
            return false;
        }
        if (empty($res)) {
            return array();
        }
        $respuesta = array();
        foreach ($res as $row) {
            if ($row["codigoError"] == '0000') {
                $respuesta = $row;
            }
        }
        return $respuesta;
    }

    public static function armarReportarDatosMatricula($operacion) {
        $ReportarDatos = array(
            'ReportarDatos' => array(
                'pusuario' => $_SESSION["compite360"]["usuario"],
                'pclave' => $_SESSION["compite360"]["contrasena"],
                'operacion' => $operacion,
                'camara' => $_SESSION["compite360"]["camara"],
                'matricula' => $_SESSION["compite360"]["matriculaajustada"],
                'nombre' => \funcionesGenerales::utf8_decode($_SESSION["expediente"]["nombre"]),
                // 'nombre' => $_SESSION["expediente"]["nombre"],
                'sigla' => '',
                'fecha_matricula' => $_SESSION["expediente"]["fechamatricula"],
                'fecha_renovacion' => $_SESSION["expediente"]["fecharenovacion"],
                'fecha_cancelacion' => $_SESSION["expediente"]["fechacancelacion"],
                'fecha_afiliacion' => $_SESSION["expediente"]["fechaafiliacion"],
                'fecha_vigencia' => trim($_SESSION["expediente"]["fechavencimiento"]),
                'sw_afiliado' => $_SESSION["compite360"]["afiliado"],
                'estado' => $_SESSION["compite360"]["estado"],
                'tipo_juridico' => $_SESSION["compite360"]["organizacion"],
                'tipo_domicilio' => $_SESSION["compite360"]["tipodomicilio"],
                'clase' => $_SESSION["compite360"]["clase"],
                'tipo_identificacion' => $_SESSION["compite360"]["tipoidentificacion"],
                'nro_identificacion' => $_SESSION["compite360"]["nroidentificacion"],
                'nit_persona_natural' => $_SESSION["compite360"]["nitpersonanatural"],
                'nro_empleados' => $_SESSION["expediente"]["personal"],
                'sw_importador' => $_SESSION["compite360"]["swimportador"],
                'sw_exportador' => $_SESSION["compite360"]["swexportador"],
                'buzon_electronico' => $_SESSION["expediente"]["emailcom"],
                'nro_establecimientos' => intval(count($_SESSION["expediente"]["establecimientos"])),
                'fecha_constitucion' => $_SESSION["compite360"]["fecconstitucion"],
                'rep_legal' => '',
                'sw_acoge_beneficio_ley1429' => $_SESSION["compite360"]["benart7"],
                'codigo_empresa_tamano' => $_SESSION["compite360"]["tamano"],
                // 'direccion_comercial' => iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["expediente"]["dircom"]),
                'direccion_comercial' => \funcionesGenerales::utf8_decode($_SESSION["expediente"]["dircom"]),
                'cod_ciudad_comercial' => $_SESSION["expediente"]["muncom"],
                // 'cod_ciudad_comercial' => '54001',
                'telefono_comercial' => ltrim(trim((string) $_SESSION["expediente"]["telcom1"]), "0"),
                // 'direccion_judicial' => iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["expediente"]["dirnot"]),
                'direccion_judicial' => \funcionesGenerales::utf8_decode($_SESSION["expediente"]["dirnot"]),
                'cod_ciudad_judicial' => $_SESSION["expediente"]["munnot"],
                // 'cod_ciudad_judicial' => '54001',
                'telefono_judicial' => ltrim(trim((string) $_SESSION["expediente"]["telnot"]), "0"),
                // 'direccion_geo' => iconv("UTF-8", "UTF-8//IGNORE", $_SESSION["expediente"]["dircom"]),
                'direccion_geo' => \funcionesGenerales::utf8_decode($_SESSION["expediente"]["dircom"]),
                'cod_ciudad_geo' => $_SESSION["expediente"]["muncom"],
                'telefono_geo' => ltrim(trim((string) $_SESSION["expediente"]["telcom1"]), "0"),
                'ciiu' => $_SESSION["compite360"]["ciiu1"]
            )
        );
        return $ReportarDatos;
    }

}

?>