<?php

class funcionesRegistrales_grabarHistoricos {

    public static function grabarHistoricos($dbx, $fecha = '', $hora = '', $mat = '', $pro = '', $tipoid = '', $numid = '', $nom = '', $tt = '', $reg = '', $lib = '', $numreg = '', $rec = '', $ope = '', $codbar = '', $xmlo = '', $xmlf = '', $usu = '', $ip = '') {
        $arrCampos = array(
            'fecha',
            'hora',
            'matricula',
            'proponente',
            'tipoidentificacion',
            'identificacion',
            'nombre',
            'tipotramite',
            'registro',
            'libro',
            'inscripcion',
            'recibo',
            'operacion',
            'codigobarras',
            'xmloriginal',
            'xmlfinal',
            'usuario',
            'ip'
        );
        $arrValores = array(
            "'" . $fecha . "'",
            "'" . $hora . "'",
            "'" . $mat . "'",
            "'" . $pro . "'",
            "'" . $tipoid . "'",
            "'" . $numid . "'",
            "'" . addslashes($nom) . "'",
            "'" . $tt . "'",
            "'" . $reg . "'",
            "'" . $lib . "'",
            "'" . $numreg . "'",
            "'" . $rec . "'",
            "'" . $ope . "'",
            "'" . $codbar . "'",
            "'" . addslashes($xmlo) . "'",
            "'" . addslashes($xmlf) . "'",
            "'" . $usu . "'",
            "'" . $ip . "'"
        );

        insertarRegistrosMysqliApi($dbx, 'mreg_historicos_' . date("Y"), $arrCampos, $arrValores);
    }

}

?>
