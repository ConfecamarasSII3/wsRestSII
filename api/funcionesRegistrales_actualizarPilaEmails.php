<?php

class funcionesRegistrales_actualizarPilaEmails {

    public static function actualizarPilaEmails($mysqli = null, $tipo = '', $radicacion = '', $devolucion = '', $operacion = '', $recibo = '', $libro = '', $registro = '', $dupli = '', $idclase = '', $numid = '', $matricula = '', $proponente = '', $nombre = '', $detalle = '', $emailsdestino = '') {

//
        $arrCampos = array(
            'tiponotificacion',
            'fechaprogramacion',
            'radicacion',
            'devolucion',
            'operacion',
            'recibo',
            'libro',
            'registro',
            'dupli',
            'idclase',
            'numid',
            'matricula',
            'proponente',
            'nombre',
            'detallenotificacion',
            'fechanotificacion',
            'horanotificacion',
            'idestadonotificacion',
            'emailsdestino',
            'observaciones'
        );

//
        $arrValores = array(
            "'" . $tipo . "'",
            "'" . date ("Ymd") . ' ' . date ("His") . "'",
            "'" . $radicacion . "'",
            "'" . $devolucion . "'",
            "'" . $operacion . "'",
            "'" . $recibo . "'",
            "'" . $libro . "'",
            "'" . $registro . "'",
            "'" . $dupli . "'",
            "'" . $idclase . "'",
            "'" . $numid . "'",
            "'" . $matricula . "'",
            "'" . $proponente . "'",
            "'" . addslashes($nombre) . "'",
            "'" . addslashes($detalle) . "'",
            "''",
            "''",
            "'1'",
            "'" . "'" . addslashes($emailsdestino) . "'",
            "''"
        );

        insertarRegistrosMysqliApi($mysqli, 'pila_email', $arrCampos, $arrValores);


        return true;
    }
    
}

?>
