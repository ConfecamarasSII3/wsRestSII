<?php

class funcionesRegistrales_actualizarMregLiquidacionFlujo {

    public static function actualizarMregLiquidacionFlujo($dbx, $tt, $numliq, $idsol, $est, $numrec, $numope, $numrad, $fecrec, $horrec) {
        $arrCampos = array(
            'tipotramite',
            'idliquidacion',
            'idsolicitudpago',
            'idestadoflujo',
            'numerorecibo',
            'numerooperacion',
            'numeroradicacion',
            'fecharecibo',
            'horarecibo'
        );
        $arrValues = array(
            "'" . $tt . "'",
            $numliq,
            "'" . $idsol . "'",
            "'" . $est . "'",
            "'" . $numrec . "'",
            "'" . $numope . "'",
            "'" . $numrad . "'",
            "'" . $fecrec . "'",
            "'" . $horrec . "'"
        );

        insertarRegistrosMysqliApi($dbx, 'mreg_liquidacionflujo', $arrCampos, $arrValues);
    }

}

?>
