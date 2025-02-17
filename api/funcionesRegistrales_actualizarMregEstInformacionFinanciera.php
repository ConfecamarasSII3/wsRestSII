<?php

class funcionesRegistrales_actualizarMregEstInformacionFinanciera {

    public static function actualizarMregEstInformacionFinanciera($mysqli, $data, $codbarras = '', $tt = '', $rec = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        //
        if (!isset($data["patrimonio"])) {
            $data["patrimonio"] = 0;
        }
        $nameLog = 'actualizarMregEstInformacionFinanciera_' . date("Ymd");
        $dataori = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . ltrim($data["matricula"], "0") . "'");
        if ($dataori === false || empty($dataori)) {
            $accion = 'insertar';
        } else {
            $accion = 'regrabar';
        }
        $arrCampos = array(
            'matricula',
            'anodatos',
            'fecdatos',
            'personal',
            'personaltemp',
            'actcte',
            'actnocte',
            'actfij',
            'fijnet',
            'actval',
            'actotr',
            'acttot',
            'actsinaju',
            'invent',
            'pascte',
            'paslar',
            'pastot',
            'pattot',
            'paspat',
            'balsoc',
            'ingope',
            'ingnoope',
            'gasope',
            'gasnoope',
            'gtoven',
            'gtoadm',
            'utiope',
            'utinet',
            'cosven',
            'depamo',
            'gasint',
            'gasimp',
            'actvin',
            'fecactualizacion',
            'compite360',
            'rues',
            'ivc'
        );

        $arrValores = array(
            "'" . trim($data["matricula"]) . "'",
            "'" . trim($data["anodatos"]) . "'",
            "'" . trim($data["fechadatos"]) . "'",
            intval($data["personal"]),
            doubleval($data["personaltemp"]),
            doubleval($data["actcte"]),
            doubleval($data["actnocte"]),
            doubleval($data["actfij"]),
            doubleval($data["fijnet"]),
            doubleval($data["actval"]),
            doubleval($data["actotr"]),
            doubleval($data["acttot"]),
            doubleval($data["actsinaju"]),
            doubleval($data["invent"]),
            doubleval($data["pascte"]),
            doubleval($data["paslar"]),
            doubleval($data["pastot"]),
            doubleval($data["pattot"]),
            doubleval($data["paspat"]),
            doubleval($data["balsoc"]),
            doubleval($data["ingope"]),
            doubleval($data["ingnoope"]),
            doubleval($data["gasope"]), //
            doubleval($data["gasnoope"]), //
            doubleval($data["gtoven"]),
            doubleval($data["gtoadm"]),
            doubleval($data["utiope"]),
            doubleval($data["utinet"]),
            doubleval($data["cosven"]), //
            doubleval($data["depamo"]),
            doubleval($data["gasint"]),
            doubleval($data["gasimp"]),
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
                    $data["personaltemp"] != $arrFin["pcttemp"] ||
                    $data["actvin"] != $arrFin["actvin"] ||
                    $data["actcte"] != $arrFin["actcte"] ||
                    $data["actnocte"] != $arrFin["actnocte"] ||
                    $data["actfij"] != $arrFin["actfij"] ||
                    $data["fijnet"] != $arrFin["fijnet"] ||
                    $data["actval"] != $arrFin["actval"] ||
                    $data["actotr"] != $arrFin["actotr"] ||
                    $data["acttot"] != $arrFin["acttot"] ||
                    $data["actsinaju"] != $arrFin["actsinaju"] ||
                    $data["invent"] != $arrFin["invent"] ||
                    $data["pascte"] != $arrFin["pascte"] ||
                    $data["paslar"] != $arrFin["paslar"] ||
                    $data["pastot"] != $arrFin["pastot"] ||
                    $data["pattot"] != $arrFin["patnet"] ||
                    $data["paspat"] != $arrFin["paspat"] ||
                    $data["balsoc"] != $arrFin["balsoc"] ||
                    $data["ingope"] != $arrFin["ingope"] ||
                    $data["ingnoope"] != $arrFin["ingnoope"] ||
                    $data["gasope"] != $arrFin["gasope"] ||
                    $data["gtoadm"] != $arrFin["gasadm"] ||
                    $data["gasnoope"] != $arrFin["gasnoope"] ||
                    $data["cosven"] != $arrFin["cosven"] ||
                    $data["gtoven"] != $arrFin["gtoven"] ||
                    $data["gasint"] != $arrFin["gasint"] ||
                    $data["gasimp"] != $arrFin["gasimp"] ||
                    $data["utiope"] != $arrFin["utiope"] ||
                    $data["utinet"] != $arrFin["utinet"]
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
                'pcttemp',
                'patrimonio',
                'actvin',
                'actcte',
                'actnocte',
                'actfij',
                'fijnet',
                'actval',
                'actotr',
                'acttot',
                'actsinaju',
                'invent',
                'pascte',
                'paslar',
                'pastot',
                'patnet',
                'paspat',
                'balsoc',
                'ingope',
                'ingnoope',
                'gasope',
                'gasadm',
                'gasnoope',
                'cosven',
                'gtoven',
                'gasint',
                'gasimp',
                'utiope',
                'utinet',
                'depamo',
                'fecsincronizacion',
                'horsincronizacion',
                'compite360'
            );
            $arrValores = array(
                "'" . ltrim($data["matricula"], "0") . "'",
                "'" . $data["anodatos"] . "'",
                "'" . $data["fechadatos"] . "'",
                intval($data["personal"]),
                doubleval($data["personaltemp"]),
                doubleval($data["patrimonio"]),
                doubleval($data["actvin"]),
                doubleval($data["actcte"]),
                doubleval($data["actnocte"]),
                doubleval($data["actfij"]),
                doubleval($data["fijnet"]),
                doubleval($data["actval"]),
                doubleval($data["actotr"]),
                doubleval($data["acttot"]),
                doubleval($data["actsinaju"]),
                doubleval($data["invent"]),
                doubleval($data["pascte"]),
                doubleval($data["paslar"]),
                doubleval($data["pastot"]),
                doubleval($data["pattot"]),
                doubleval($data["paspat"]),
                doubleval($data["balsoc"]),
                doubleval($data["ingope"]),
                doubleval($data["ingnoope"]),
                doubleval($data["gasope"]),
                doubleval($data["gtoadm"]),
                doubleval($data["gasnoope"]), //
                doubleval($data["cosven"]), //
                doubleval($data["gtoven"]), //
                doubleval($data["gasint"]), //
                doubleval($data["gasimp"]), //
                doubleval($data["utiope"]),
                doubleval($data["utinet"]),
                0, // Depamo
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
        // \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Recarga expediente para sincronizar al rues');
        $datax = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, ltrim($data["matricula"], "0"));
        if ($datax && !empty($datax)) {
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
            // \funcionesRues::actualizarMercantilRues($datax);
        }

        //
        return true;
    }

}

?>
