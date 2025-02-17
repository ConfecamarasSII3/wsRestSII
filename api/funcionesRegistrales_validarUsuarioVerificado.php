<?php

class funcionesRegistrales_validarUsuarioVerificado {

    public static function validarUsuarioVerificado($mysqli) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        

        $respuesta = array();
        $respuesta["codigoError"] = '0000';
        $respuesta["msgError"] = '';
        
        //
        $nal = \funcionesGenerales::validarSuscripcionNacional($_SESSION["generales"]["emailusuariocontrol"],$_SESSION["generales"]["identificacionusuariocontrol"]);
        if ($nal["codigoerror"] == '0000') {
            return $respuesta;
        }
        
        //
        if ($nal["codigoerror"] == '9994') {
            $respuesta["codigoError"] = '0006'; // verificado sin activar
            return $respuesta;
        }
        
        //
        $usu = false;
        $usus = retornarRegistrosMysqliApi($mysqli, 'usuarios_verificados', "email='" . $_SESSION["generales"]["emailusuariocontrol"] . "' and identificacion='" . $_SESSION["generales"]["identificacionusuariocontrol"] . "'", "id");
        if ($usus && !empty($usus)) {
            foreach ($usus as $usx) {
                if ($usx["estado"] != 'EL') {
                    $usu = $usx;
                }
            }
        }
        unset($usus);
        if ($usu === false || empty($usu) || $usu["estado"] == 'EL') {
            $respuesta["codigoError"] = '0001'; // usuario no existe  o está eliminado
        }
        if ($usu["estado"] == 'PE') {
            $respuesta["codigoError"] = '0002'; // usuario pendiente
        }
        if ($usu["estado"] == 'RZ') {
            $respuesta["codigoError"] = '0003'; // usuario rechazado
        }
        if ($usu["estado"] == 'SF') {
            $respuesta["codigoError"] = '0004'; // usuario sin informacion financiera
        }
        if ($usu["estado"] == 'IN') {
            $respuesta["codigoError"] = '0005'; // usuario inactivo
        }

        if ($usu["estado"] == 'VE' && $usu["claveconfirmacion"] == '') {
            $respuesta["codigoError"] = '0005'; // usuario no activado
        }

//
        return $respuesta;
    }
}

?>
