<?php

class funcionesRegistrales_actualizarMregEstInformacionFinancieraEstablecimientos {

    public static function actualizarMregEstInformacionFinancieraEstablecimientos($mysqli, $data, $codbarras = '', $tt = '', $rec = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        
        //
        $nameLog = 'actualizarMregEstInformacionFinancieraEstablecimientos_' . date("Ymd");
        $dataori = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
        if ($dataori === false || empty($dataori)) {
            $accion = 'insertar';
        } else {
            $accion = 'regrabar';
        }
        $arrCampos = array(
            'anodatos',
            'fecdatos',
            'personal',
            'actvin',
            'fecactualizacion',
            'compite360',
            'rues',
            'ivc'
        );

        $arrValores = array(
            "'" . trim($data["anodatos"]) . "'",
            "'" . trim($data["fechadatos"]) . "'",
            intval($data["personal"]),
            doubleval($data["actvin"]),
            "'" . date("Ymd") . "'", // fecha de actualizacion
            "'NO'", // Compite360
            "'NO'", // Rues
            "'NO'" // Ivc
        );

        // Graba el registro en mreg_est_inscritos
        if ($accion == 'insertar') {
            unset($_SESSION["expedienteactual"]);
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error creando matrícula en mreg_est_inscritos : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        } else {
            unset($_SESSION["expedienteactual"]);
            $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "'");
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_inscritos: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        // Revisa información financiera
        $arrFin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'");
        if ($arrFin === false || empty($arrFin)) {
            $accion = 'insertar';
        } else {
            $accion = 'no';
            if (
                    $data["personal"] != $arrFin["personal"] ||
                    $data["actvin"] != $arrFin["actvin"]
            ) {
                $accion = 'regrabar';
            }
        }

        if ($accion == 'insertar' || $accion == 'regrabar') {
            $arrCampos = array(
                'matricula',
                'anodatos',
                'fechadatos',
                'personal',
                'actvin',
                'fecsincronizacion',
                'horsincronizacion',
                'compite360'
            );
            $arrValores = array(
                "'" . ltrim($data["matricula"], "0") . "'",
                "'" . $data["anodatos"] . "'",
                "'" . $data["fechadatos"] . "'",
                intval($data["personal"]),
                doubleval($data["actvin"]),
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'NO'"
            );
            if ($accion == 'insertar') {
                $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error insertando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }
            if ($accion == 'regrabar') {
                $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_financiera', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'");
                if ($res === false) {
                    \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_financiera: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }

            //
            $arrCampos = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );
            $arrValores = array();
            $iCambios = 0;
            $fecxact = date("Ymd");
            $horxact = date("His");
            $arrFinFin = retornarRegistroMysqliApi($mysqli, 'mreg_est_financiera', "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'");
            foreach ($arrFinFin as $key => $valor) {
                if (
                        $key != 'id' &&
                        !is_numeric($key) &&
                        $key != 'matricula' &&
                        $key != 'anodatos' &&
                        $key != 'fechadatos' &&
                        $key != 'fecsincronizacion' &&
                        $key != 'horsincronizacion' &&
                        $key != 'compite360'
                ) {
                    if (!isset($arrFin[$key])) {
                        $arrFin[$key] = '';
                    }
                    if ($arrFin[$key] == null) {
                        $arrFin[$key] = '';
                    }
                    if (ltrim(trim($arrFinFin[$key]), "0") != ltrim(trim($arrFin[$key]), "0")) {
                        $iCambios++;
                        $arrValores[$iCambios] = array(
                            "'" . ltrim($data["matricula"], "0") . "'",
                            "'" . $key . '-' . $data["anodatos"] . '-' . $data["fechadatos"] . "'",
                            "'" . $fecxact . "'",
                            "'" . $horxact . "'",
                            "'" . $codbarras . "'",
                            "'" . addslashes(ltrim(trim($arrFin[$key]), "0")) . "'",
                            "'" . addslashes(ltrim(trim($arrFinFin[$key]), "0")) . "'",
                            "'" . $_SESSION["generales"]["codigousuario"] . "'",
                            "'" . \funcionesGenerales::localizarIP() . "'",
                            "'" . $tt . "'",
                            "'" . $rec . "'"
                        );
                    }
                }
            }
            if ($iCambios > 0) {
                $res = insertarRegistrosBloqueMysqliApi($mysqli, 'mreg_campos_historicos_' . substr($fecxact, 0, 4), $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error grabando matrícula en mreg_campos_historicos: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
            }
        }

        //
        $datax = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim($data["matricula"], "0"));
        $arrCampos = array(
            'hashcontrol'
        );
        $arrValores = array(
            "'" . $datax["hashcontrolnuevo"] . "'"
        );
        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . ltrim($data["matricula"], "0") . "'");

        // ************************************************************************************************ //
        // JINT: 2021-04-30
        // ************************************************************************************************ //
        \funcionesRues::actualizarMercantilRues($datax);

        //
        return true;
    }

}

?>
