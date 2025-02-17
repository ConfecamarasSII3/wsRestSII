<?php

class funcionesRegistrales_actualizarPilaSms {

    public static function actualizarPilaSms($mysqli = null, $pref = '', $cel = '', $tip = '', $rec = '', $cba = '', $ins = '', $dev = '', $exp = '', $mat = '', $pro = '', $ide = '', $nom = '', $txt = '', $obs = '', $bandeja = '') {

//
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/genPdfImagenes635.php');

//
        $arrCampos = array(
            'prefijo',
            'celular',
            'texto',
            'recibo',
            'codigobarras',
            'inscripcion',
            'devolucion',
            'expediente',
            'matricula',
            'proponente',
            'identificacion',
            'nombre',
            'fechaprogramacion',
            'horaprogramacion',
            'tipo',
            'estado',
            'fechaenvio',
            'horaenvio'
        );

//
        $arrValores = array(
            "'" . $pref . "'",
            "'" . $cel . "'",
            "'" . addslashes((string)$txt) . "'",
            "'" . $rec . "'",
            "'" . $cba . "'",
            "'" . $ins . "'",
            "'" . $dev . "'",
            "'" . ltrim($exp, "0") . "'",
            "'" . ltrim($mat, "0") . "'",
            "'" . ltrim($pro, "0") . "'",
            "'" . ltrim($ide, "0") . "'",
            "'" . addslashes($nom) . "'",
            "'" . date("Ymd") . "'",
            "'" . date("His") . "'",
            "'" . $tip . "'",
            "'1'",
            "''",
            "''"
        );

        insertarRegistrosMysqliApi($mysqli, 'pila_sms', $arrCampos, $arrValores);

        // Crea el pdf de la notificación sipref 
        if ($tip != '6' && $tip != '7' && $tip != '90') {
            $id = generarPdfNotificacionSmsApi635($mysqli, $cel, $tip, $rec, $cba, $ins, $dev, $exp, $mat, $pro, $ide, $nom, $txt, $obs, $bandeja);
        }
        return true;
    }
    
}

?>
