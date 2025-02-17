<?php

class funcionesRegistrales_actualizarMregEstCapitales {

    public static function actualizarMregEstCapitales($mysqli, $data, $codbarras = '', $tt = '', $rec = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        
        //
        $nameLog = 'actualizarMregEstCapitales_' . date("Ymd");
        $condicion = "matricula='" . ltrim($data["matricula"], "0") . "' and anodatos='" . $data["anodatos"] . "' and fechadatos='" . $data["fechadatos"] . "'";        
        $dataori = retornarRegistroMysqliApi($mysqli, 'mreg_est_capitales', $condicion);
        if ($dataori === false || empty($dataori)) {
            $accion = 'insertar';
        } else {
            $accion = 'regrabar';
        }
        $arrCampos = array(
            'matricula',
            'anodatos',
            'fechadatos',
            'libro',
            'registro',
            'tipoeconomia',
            'pornalpub',
            'pornalpri',
            'pornaltot',
            'porextpub',
            'porextpri',
            'porexttot',
            'capsucursal',
            'cuotassocial',
            'cuotasautorizado',
            'cuotassuscrito',
            'cuotaspagado',
            'nominalsocial',
            'nominalautorizado',
            'nominalsuscrito',
            'nominalpagado',
            'valorsocial',
            'valorautorizado',
            'valorsuscrito',
            'valorpagado',
            'aportedinero',
            'aportelaboral',
            'aportelaboraladi',
            'aporteactivos',
            'aportetotal',
            'moneda',
            'fecsincronizacion',
            'horsincronizacion'
        );

        $arrValores = array(
            "'" . trim($data["matricula"]) . "'",
            "'" . trim($data["anodatos"]) . "'",
            "'" . trim($data["fechadatos"]) . "'",
            "'" . trim($data["libro"]) . "'",
            "'" . trim($data["registro"]) . "'",
            "'" . trim($data["tipoeconomia"]) . "'",
            doubleval($data["pornalpub"]),
            doubleval($data["pornalpri"]),
            doubleval($data["pornaltot"]),
            doubleval($data["porextpub"]),
            doubleval($data["porextpri"]),
            doubleval($data["porexttot"]),
            doubleval($data["capsucursal"]),
            doubleval($data["cuotassocial"]),
            doubleval($data["cuotasautorizado"]),
            doubleval($data["cuotassuscrito"]),
            doubleval($data["cuotaspagado"]),
            doubleval($data["nominalsocial"]),
            doubleval($data["nominalautorizado"]),
            doubleval($data["nominalsuscrito"]),
            doubleval($data["nominalpagado"]),
            doubleval($data["valorsocial"]),
            doubleval($data["valorautorizado"]),
            doubleval($data["valorsuscrito"]),
            doubleval($data["valorpagado"]), //
            doubleval($data["aportedinero"]), //
            doubleval($data["aportelaboral"]),
            doubleval($data["aportelaboraladi"]),
            doubleval($data["aporteactivos"]),
            doubleval($data["aportetotal"]),
            "'" . trim($data["moneda"]) . "'",
            "'" . date("Ymd") . "'", 
            "'" . date("His") . "'" 
        );

        // Graba el registro en mreg_est_inscritos
        if ($accion == 'insertar') {
            unset($_SESSION["expedienteactual"]);
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_capitales', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error creando registro en mreg_est_capitales : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        } else {
            unset($_SESSION["expedienteactual"]);
            $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_capitales', $arrCampos, $arrValores, $condicion);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error regrabando registro en mreg_est_capitales: ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
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

        //
        return true;
    }

}

?>
