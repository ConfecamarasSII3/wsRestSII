<?php

class funcionesRegistrales_retornarMregRecibo {

    public static function retornarMregRecibo($dbx, $numrec) {

        // Inicializa las variables del trámite
        $respuesta = retornarRegistroMysqliApi($dbx, 'mreg_recibosgenerados', "recibo='" . $numrec . "'");
        if ($respuesta && !empty($respuesta)) {
            $respuesta["tipo"] = 'recuperado';
            $respuesta["detalle"] = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_detalle', "recibo='" . $numrec . "'");
            $respuesta["fpago"] = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_fpago', "recibo='" . $numrec . "'");
        } else {
            $liq = \funcionsRegistrales::retornarMregLiquidacion($dbx, $numrec, 'R');
            $respuesta = array();
            $respuesta["tipo"] = 'nuevo';
            $respuesta["recibo"] = $numrec;
            $respuesta["factura"] = '';
            $respuesta["codigobarras"] = $liq["numeroradicacion"];
            $respuesta["fecha"] = $liq["fecharecibo"];
            $respuesta["hora"] = $liq["horarecibo"];
            $respuesta["usuario"] = ''; //
            $respuesta["tipogasto"] = ''; //
            $respuesta["tipoidentificacion"] = $liq["idtipoidentificacioncliente"];
            $respuesta["identificacion"] = $liq["identificacioncliente"];
            $respuesta["razonsocial"] = trim($liq["razonsocialcliente"]);
            $respuesta["nombre1"] = trim($liq["nombre1cliente"]);
            $respuesta["nombre2"] = trim($liq["nombre2cliente"]);
            $respuesta["apellido1"] = trim($liq["apellido1cliente"]);
            $respuesta["apellido2"] = trim($liq["apellido2cliente"]);
            $respuesta["direccion"] = trim($liq["direccion"]);
            $respuesta["direccionnot"] = trim($liq["direccionnot"]);
            $respuesta["municipio"] = $liq["idmunicipio"];
            $respuesta["mnunicipionot"] = $liq["idmunicipionot"];
            $respuesta["pais"] = $liq["pais"];
            $respuesta["lenguaje"] = $liq["lenguaje"];
            $respuesta["telefono1"] = $liq["telefono"];
            $respuesta["telefono2"] = $liq["movil"];
            $respuesta["email"] = $liq["email"];
            $respuesta["codposcom"] = '';
            $respuesta["codposnot"] = '';
            $respuesta["codigoregimen"] = '';
            $respuesta["responsabilidadtributaria"] = '';
            $respuesta["responsabilidadfiscal"] = '';
            $respuesta["codigoimpuesto"] = '';
            $respuesta["nombreimpuesto"] = '';
            $respuesta["idliquidacion"] = $liq["idliquidacion"];
            $respuesta["tipotramite"] = $liq["tipotramite"];
            $respuesta["valorneto"] = $liq["valorneto"];
            $respuesta["pagoprepago"] = 0;
            $respuesta["pagoafiliado"] = 0;
            $respuesta["pagoefectivo"] = 0;
            $respuesta["pagocheque"] = 0;
            $respuesta["pagoconsignacion"] = 0;
            $respuesta["pagopseach"] = 0;
            $respuesta["pagovisa"] = 0;
            $respuesta["pagomastercard"] = 0;
            $respuesta["pagocredencial"] = 0;
            $respuesta["pagoamerican"] = 0;
            $respuesta["pagodiners"] = 0;
            $respuesta["pagotdebito"] = 0;
            $respuesta["numeroautorizacion"] = '';
            $respuesta["cheque"] = '';
            $respuesta["franquicia"] = '';
            $respuesta["nombrefranquicia"] = '';
            $respuesta["codbanco"] = '';
            $respuesta["nombrebanco"] = '';
            $respuesta["alertaid"] = 0;
            $respuesta["alertaservicio"] = '';
            $respuesta["alertavalor"] = 0;
            $respuesta["proyectocaja"] = '';
            $respuesta["numerounicorue"] = '';
            $respuesta["numerointernorue"] = '';
            $respuesta["tipotramiterue"] = '';
            $respuesta["idformapago"] = '';
            $respuesta["estado"] = '';
            $respuesta["estadoemail"] = '';
            $respuesta["estadosms"] = '';
            $respuesta["tipo_cfe"] = '';
            $respuesta["estado_cfe"] = '';
            $respuesta["fechahora_envio_cfe"] = '';
            $respuesta["prefijo_cfe"] = '';
            $respuesta["numero_cfe"] = '';
            $respuesta["fecha_factura_cfe"] = '';
            $respuesta["observaciones_cfe"] = '';
            $respuesta["fechavalidacionoperador_cfe"] = '';
            $respuesta["idnotificacionsiprefcontrol"] = 0;
            $respuesta["txtnotificacionsiprefcontrol"] = '';
            $respuesta["usuarionotificacionsiprefcontrol"] = '';
            $respuesta["fechahoranotificacionsiprefcontrol"] = '';
            $respuesta["justificacionreversion"] = '';
            $respuesta["prefijofacturareversar"] = '';
            $respuesta["numerofacturareversar"] = '';
            $respuesta["cufefacturareversar"] = '';
            $respuesta["fechafacturareversar"] = '';
            $respuesta["detalle"] = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_detalle', "recibo='" . $numrec . "'");
            $respuesta["fpago"] = retornarRegistrosMysqliApi($dbx, 'mreg_recibosgenerados_fpago', "recibo='" . $numrec . "'");
            if ($respuesta["detalle"] === false || empty($respuesta["detalle"])) {
                $respuesta["detalle"] = array();
                $dets = retornarRegistrosMysqliApi($dbx, 'mreg_est_recibos', "numerorecibo='" . $numrec . "'", "id");
                if ($dets && !empty($dets)) {
                    $ix = 0;
                    foreach ($dets as $dx) {
                        $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $dx["servicio"] . "'");
                        $ix++;
                        $row = array();
                        $row["recibo"] = $dx["numerorecibo"];
                        $row["secuencia"] = $ix;
                        $row["fecha"] = $dx["fecharecibo"];
                        $row["idservicio"] = $dx["servicio"];
                        $row["cc"] = $dx["codigocontable"];
                        if ($serv["tipoingreso"] >= '20' && $serv["tipoingreso"] <= '29') {
                            $row["proponente"] = $dx["matricula"];
                        } else {
                            $row["matricula"] = $dx["matricula"];
                        }
                        $row["ano"] = $dx["anorenovacion"];
                        $row["tipogasto"] = $dx["tipogasto"];
                        $row["cantidad"] = $dx["cantidad"];
                        $row["valorbase"] = $dx["base"];
                        $row["porcentaje"] = '';
                        $row["valorservicio"] = $dx["valor"];
                        $row["idservicioorigen"] = '';
                        $row["identificacion"] = $dx["identificacion"];
                        $row["razonsocial"] = $dx["nombre"];
                        $row["organizacion"] = '';
                        $row["categoria"] = '';
                        $row["idtipodoc"] = '';
                        $row["numdoc"] = '';
                        $row["origendoc"] = '';
                        $row["fechadoc"] = '';
                        $row["expedienteafectado"] = $dx["expedienteafectado"];
                        $row["fecharenovacionaplicbable"] = $dx["fecharenovacionaplicable"];
                        $row["porcentajeiva"] = 0;
                        $row["valoriva"] = 0;
                        $row["servicioiva"] = '';
                        $row["porcentajedescuento"] = 0;
                        $row["valordescuento"] = 0;
                        $row["serviciodescuento"] = '';
                        $row["cantdevparcial"] = 0;
                        $row["basedevparcial"] = 0;
                        $row["valordevparcial"] = 0;
                        $row["ivadevparcial"] = 0;
                        $row["idalerta"] = $dx["idalerta"];
                        $row["clavecontrol"] = $dx["clavecontrol"];
                        $respuesta["detalle"][] = $row;
                    }
                }
            }
        }
        return $respuesta;
    }

}

?>
