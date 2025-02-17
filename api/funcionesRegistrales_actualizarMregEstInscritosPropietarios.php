<?php

class funcionesRegistrales_actualizarMregEstInscritosPropietarios {

    public static function actualizarMregEstInscritosPropietarios ($mysqli, $data, $codbarras = '', $tt = '', $rec = '', $altoimpacto = 'no') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');

        //
        $nameLog = 'actualizarMregEstInscritosPropietarios_' . date("Ymd");

        // Valida que el expediente tenga diferencias
        if (ltrim($data["matricula"], "0") == '' && ltrim($data["proponente"], "0") == '') {
            return true;
        }

        if ($data["organizacion"] != '02') {
            return true;
        }
        
        //
        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarmysql = 'si';
        }


       
        // ********************************************************************************************* //
        // 2020 04 21 - JINT - Si es matriculado y solo tiene actividades no comerciales
        // Y no se ha notificado previamente
        // Envía alerta de persona natural solo con actividades no comerciales
        // Y si la matrícula esta activa
        // ********************************************************************************************* 
        \funcionesRegistrales::alertarNoComerciales($mysqli, $data);

        if ($cerrarmysql == 'si') {
            $mysqli->close();
        }

        //
        return true;
    }

}
