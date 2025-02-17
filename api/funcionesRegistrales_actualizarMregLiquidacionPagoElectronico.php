<?php

class funcionesRegistrales_actualizarMregLiquidacionPagoElectronico {

    public static function actualizarMregLiquidacionPagoElectronico($dbx, $liqui, $est, $nomcli, $idtipide, $dir, $tel, $mun, $email, $ide, $pagefe, $pagche, $pagvis, $pagach, $pagmas, $pagame, $pagcre, $pagdin, $pagtdeb, $codban, $numche, $numaut, $caj, $numope = '', $numrec = '', $fecrec = '', $horrec = '', $numopegob = '', $numrecgob = '', $fecrecgob = '', $horrecgob = '' , $codbar = '', $xfra = '', $xnfra = '', $formapago = '05') {

        $arrCampos = array(
            'idestado',
            'idtipoidentificacioncliente',
            'identificacioncliente',
            'nombrecliente',
            'email',
            'direccion',
            'idmunicipio',
            'telefono',
            'pagoefectivo',
            'pagocheque',
            'pagovisa',
            'pagomastercard',
            'pagoamerican',
            'pagocredencial',
            'pagodiners',
            'pagotdebito',
            'idformapago',
            'numerorecibo',
            'numerooperacion',
            'fecharecibo',
            'horarecibo',
            
            'numerorecibogob',
            'numerooperaciongob',
            'fecharecibogob',
            'horarecibogob',
            
            'idfranquicia',
            'nombrefranquicia',
            'numeroautorizacion',
            'idcodban',
            'nombrebanco',
            'numerocheque',
            'numeroradicacion'
        );
        $arrValues = array(
            "'" . $est . "'",
            "'" . $idtipide . "'",
            "'" . $ide . "'",
            "'" . $nomcli . "'",
            "'" . $email . "'",
            "'" . addslashes($dir) . "'",
            "'" . $mun . "'",
            "'" . $tel . "'",
            $pagefe,
            $pagche,
            $pagvis,
            $pagmas,
            $pagame,
            $pagcre,
            $pagdin,
            $pagtdeb,
            "'" . $formapago . "'", // idformapago (pago electr&oacute;nico)
            "'" . $numrec . "'",
            "'" . $numope . "'",
            "'" . $fecrec . "'",
            "'" . $horrec . "'",
            
            "'" . $numrecgob . "'",
            "'" . $numopegob . "'",
            "'" . $fecrecgob . "'",
            "'" . $horrecgob . "'",
            
            "'" . $xfra . "'", // idfranquicia
            "'" . $xnfra . "'", // nombre franquicia
            "'" . $numaut . "'",
            "'" . $codban . "'",
            "''", // nombre banco
            "'" . $numche . "'",
            "'" . $codbar . "'"
        );
        regrabarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValues, "idliquidacion=" . $liqui);
        return true;
    }

}

?>
