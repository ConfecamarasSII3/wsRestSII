<?php

class funcionesRegistrales_actualizarPrepago {

    public static function actualizarPrepago($dbx, $cri, $ide, $clave = '', $valor = '', $recibo = '', $numoperacion = '', $servicio = '', $cantidad = '', $detalle = '', $ip = '', $usuario = '', $expediente = '', $email = '', $nombre = '', $celular = '', $direccion = '', $municipio = '', $tipousuario = '', $telefono = '', $nom1 = '', $nom2 = '', $ape1 = '', $ape2 = '') {

        $resultado = array();
        $resultado["codigoError"] = '0000';
        $resultado["msgError"] = '';
        $resultado["identificacion"] = $ide;
        $resultado["nombre"] = '';
        $resultado["nombre1"] = '';
        $resultado["nombre2"] = '';
        $resultado["apellido1"] = '';
        $resultado["apellido2"] = '';
        $resultado["email"] = '';
        $resultado["telefono"] = '';
        $resultado["celular"] = '';
        $resultado["direccion"] = '';
        $resultado["municipio"] = '';
        $resultado["clave"] = '';
        $resultado["saldoprepago"] = '0';
        $resultado["saldoafiliado"] = '0';
        $resultado["kardex"] = array();
        $resultado["saldos"] = array();

        if ($cri == 'S' || $cri == 'R') { // Retorna los datos y el saldo de un prepago
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if ($prep && !empty($prep)) {
                $resultado["nombre"] = $prep["nombre"];
                $resultado["nombre1"] = $prep["nombre1"];
                $resultado["nombre2"] = $prep["nombre2"];
                $resultado["apellido1"] = $prep["apellido1"];
                $resultado["apellido2"] = $prep["apellido2"];
                $resultado["email"] = $prep["email"];
                $resultado["telefono"] = $prep["telefono"];
                $resultado["celular"] = $prep["celular"];
                $resultado["direccion"] = $prep["direccion"];
                $resultado["municipio"] = $prep["municipio"];
                $resultado["clave"] = $prep["clave"];
                $resultado["saldoprepago"] = '0';
                $resultado["saldoafiliado"] = '0';
                $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
                if ($preps && !empty($preps)) {
                    foreach ($preps as $p) {
                        if ($p["tipomov"] == 'C') {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                        } else {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                        }
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'K') { // retorna el kardex de un prepago
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if ($prep && !empty($prep)) {
                $resultado["nombre"] = $prep["nombre"];
                $resultado["nombre1"] = $prep["nombre1"];
                $resultado["nombre2"] = $prep["nombre2"];
                $resultado["apellido1"] = $prep["apellido1"];
                $resultado["apellido2"] = $prep["apellido2"];
                $resultado["email"] = $prep["email"];
                $resultado["telefono"] = $prep["telefono"];
                $resultado["celular"] = $prep["celular"];
                $resultado["direccion"] = $prep["direccion"];
                $resultado["municipio"] = $prep["municipio"];
                $resultado["clave"] = $prep["clave"];
                $resultado["saldoprepago"] = '0';
                $resultado["saldoafiliado"] = '0';
                $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
                if ($preps && !empty($preps)) {
                    $ik = 0;
                    foreach ($preps as $p) {
                        if ($p["tipomov"] == 'C') {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                        } else {
                            $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                        }
                        $ik++;
                        $resultado["kardex"][$i]["fecha"] = $p["fecha"];
                        $resultado["kardex"][$i]["hora"] = $p["hora"];
                        $resultado["kardex"][$i]["usuario"] = $p["usuario"];
                        $resultado["kardex"][$i]["operador"] = $p["operador"];
                        $resultado["kardex"][$i]["tipomov"] = $p["tipomov"];
                        $resultado["kardex"][$i]["servicio"] = $p["servicio"];
                        $resultado["kardex"][$i]["expediente"] = $p["expediente"];
                        $resultado["kardex"][$i]["cantidad"] = $p["cantidad"];
                        $resultado["kardex"][$i]["valor"] = $p["valor"];
                        $resultado["kardex"][$i]["recibo"] = $p["recibo"];
                        $resultado["kardex"][$i]["operacion"] = '';
                        $resultado["kardex"][$i]["concepto"] = $p["concepto"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'L') { // retorna la lista de prepagos y su saldo
            $i = 0;
            $prep = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos', "1=1", "identificacion");
            if ($prep && !empty($prep)) {
                foreach ($prep as $p1) {
                    $i++;
                    $resultado["saldos"][$i]["identificacion"] = $p1["identificacion"];
                    $resultado["saldos"][$i]["nombre"] = $p1["nombre"];
                    $resultado["saldos"][$i]["email"] = $p1["email"];
                    $resultado["saldos"][$i]["telefono"] = $p1["telefono"];
                    $resultado["saldos"][$i]["saldo"] = 0;
                    $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $p1["identificacion"] . "'", "fecha,hora");
                    if ($preps && !empty($preps)) {
                        $ik = 0;
                        foreach ($preps as $p) {
                            if ($p["tipomov"] == 'C') {
                                $resultado["saldos"][$i]["saldo"] = $resultado["saldos"][$i]["saldo"] + $p["valor"];
                            } else {
                                $resultado["saldos"][$i]["saldo"] = $resultado["saldos"][$i]["saldo"] - $p["valor"];
                            }
                        }
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'A') { // Verifica la clave de un prepago
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if (!$prep || empty($prep)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE PREPAGO PARA LA IDENTIFICACION';
                return $resultado;
            }
            if ($tipousuario != '3') {
                if (md5($clave) != $prep["clave"] && sha1($clave) != $prep["clave"]) {
                    $resultado["codigoError"] = '0020';
                    $resultado["msgError"] = 'CLAVE NO CORRESPONDE';
                    return $resultado;
                }
            }
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["celular"] = $prep["celular"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'P') { // Cambio de clave
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if (!$prep || empty($prep)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE PREPAGO PARA LA IDENTIFICACION';
                return $resultado;
            }
            $arrCampos = array('clave');
            $arrValores = array("'" . md5($clave) . "'");
            regrabarRegistrosMysqliApi($dbx, 'mreg_prepagos', $arrCampos, $arrValores, "identificacion='" . $ide . "'");
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["celular"] = $prep["celular"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $clave;
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'C') { //Carga al prepgo
            $arrCampos = array(
                'identificacion',
                'nombre',
                'nombre1',
                'nombre2',
                'apellido1',
                'apellido2',
                'direccion',
                'telefono',
                'celular',
                'email',
                'municipio',
                'clave'
            );
            $arrValores = array(
                "'" . $ide . "'",
                "'" . addslashes($nombre) . "'",
                "'" . addslashes($nom1) . "'",
                "'" . addslashes($nom2) . "'",
                "'" . addslashes($ape1) . "'",
                "'" . addslashes($ape2) . "'",
                "'" . addslashes($direccion) . "'",
                "'" . $telefono . "'",
                "'" . $celular . "'",
                "'" . addslashes($email) . "'",
                "'" . $municipio . "'",
                "'" . md5($clave) . "'"
            );
            if (contarRegistrosMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'") == 0) {
                insertarRegistrosMysqliApi($dbx, 'mreg_prepagos', $arrCampos, $arrValores);
            } else {
                regrabarRegistrosMysqliApi($dbx, 'mreg_prepagos', $arrCampos, $arrValores, "identificacion='" . $ide . "'");
            }
            $arrCampos = array(
                'identificacion',
                'fecha',
                'hora',
                'ip',
                'operador',
                'usuario',
                'tipomov',
                'servicio',
                'concepto',
                'expediente',
                'recibo',
                'cantidad',
                'valor'
            );
            $arrValores = array(
                "'" . $ide . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $ip . "'",
                "''", // Operador
                "'" . $usuario . "'",
                "'C'",
                "'" . $servicio . "'",
                "'" . $detalle . "'",
                "''",
                "'" . $recibo . "'",
                0,
                $valor
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', $arrCampos, $arrValores);
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["celular"] = $prep["celular"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'D') { //Descuenta del prepgo
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            if (!$prep || empty($prep)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE PREPAGO PARA LA IDENTIFICACION';
                return $resultado;
            }
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["celular"] = $prep["celular"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            if ($resultado["saldoprepago"] < $valor) {
                $resultado["codigoError"] = '0021';
                $resultado["msgError"] = 'NO EXISTE SALDO SUFICIENTE';
                return $resultado;
            }

            $arrCampos = array(
                'identificacion',
                'fecha',
                'hora',
                'ip',
                'operador',
                'usuario',
                'tipomov',
                'servicio',
                'concepto',
                'expediente',
                'recibo',
                'cantidad',
                'valor'
            );
            $arrValores = array(
                "'" . $ide . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "'" . $ip . "'",
                "''", // Operador
                "'" . $usuario . "'",
                "'D",
                "'" . $servicio . "'",
                "'" . $detalle . "'",
                "'" . $expediente . "'",
                "'" . $recibo . "'",
                $cantidad,
                $valor
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', $arrCampos, $arrValores);
            $prep = retornarRegistroMysqliApi($dbx, 'mreg_prepagos', "identificacion='" . $ide . "'");
            $resultado["nombre"] = $prep["nombre"];
            $resultado["nombre1"] = $prep["nombre1"];
            $resultado["nombre2"] = $prep["nombre2"];
            $resultado["apellido1"] = $prep["apellido1"];
            $resultado["apellido2"] = $prep["apellido2"];
            $resultado["email"] = $prep["email"];
            $resultado["celular"] = $prep["celular"];
            $resultado["telefono"] = $prep["telefono"];
            $resultado["direccion"] = $prep["direccion"];
            $resultado["municipio"] = $prep["municipio"];
            $resultado["clave"] = $prep["clave"];
            $resultado["saldoprepago"] = '0';
            $preps = retornarRegistrosMysqliApi($dbx, 'mreg_prepagos_uso', "identificacion='" . $ide . "'", "fecha,hora");
            if ($preps && !empty($preps)) {
                $ik = 0;
                foreach ($preps as $p) {
                    if ($p["tipomov"] == 'C') {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] + $p["valor"];
                    } else {
                        $resultado["saldoprepago"] = $resultado["saldoprepago"] - $p["valor"];
                    }
                }
            }
            return $resultado;
        }

        if ($cri == 'X') { // Resta del cupo de afiliado
            $expediente = ltrim($expediente, "0");

            $afil = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', "matricula='" . $expediente . "'");
            if ($afil === false || empty($afil)) {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE AFILIADO PARA DESCONTAR';
                return $resultado;
            }
            if ($afil["ctrafiliacion"] != '1') {
                $resultado["codigoError"] = '0020';
                $resultado["msgError"] = 'NO EXISTE AFILIADO PARA DESCONTAR';
                return $resultado;
            }
            if ($afil["saldoaflia"] < $valor) {
                $resultado["codigoError"] = '0021';
                $resultado["msgError"] = 'NO EXISTE SALDO SUFICIENTE COMO AFILIADO';
                return $resultado;
            }
            $afil["saldoaflia"] = $afil["saldoaflia"] - $valor;
            $arrCampos = array(
                'saldoaflia'
            );
            $arrValores = array(
                $afil["saldoaflia"]
            );
            unset($_SESSION["expedienteactual"]);
            regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $expediente . "'");

            $resultado["nombre"] = $afil["razonsocial"];
            $resultado["nombre1"] = $afil["nombre1"];
            $resultado["nombre2"] = $afil["nombre2"];
            $resultado["apellido1"] = $afil["apellido1"];
            $resultado["apellido2"] = $afil["apellido2"];
            $resultado["email"] = $afil["emailcom"];
            $resultado["telefono"] = $afil["telcom1"];
            $resultado["celular"] = '';
            $resultado["direccion"] = $prep["dircom"];
            $resultado["municipio"] = $prep["muncom"];
            $resultado["clave"] = '';
            $resultado["saldoprepago"] = '0';
            $resultado["saldoafiliado"] = $afil["saldoaflia"];
            return $resultado;
        }
    }

}

?>
