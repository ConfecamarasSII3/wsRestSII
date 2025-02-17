<?php

class funcionesRegistrales_actualizarMregLiquidacionGruposModificados {

    public static function actualizarMregLiquidacionGruposModificados($dbx, $liq, $sec, $exp, $ide, $dat, $est) {
        $condicion = "idliquidacion=" . $liq . " and secuencia='" . $sec . "' and expediente='" . $exp . "' and numrue='" . $ide . "' and grupodatos='" . $dat . "'";
        $contar = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciongruposmodificados', $condicion, "idliquidacion");
        $arrCampos = array(
            'idliquidacion',
            'secuencia',
            'expediente',
            'numrue',
            'grupodatos',
            'controlgrabacion'
        );
        $arrValues = array(
            $liq,
            "'" . $sec . "'",
            "'" . $exp . "'",
            "'" . $ide . "'",
            "'" . $dat . "'",
            "'" . $est . "'"
        );
        if ($contar == false || empty ($contar)) {
            $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciongruposmodificados', $arrCampos, $arrValues);
        }
        return $result;
    }

}

?>
