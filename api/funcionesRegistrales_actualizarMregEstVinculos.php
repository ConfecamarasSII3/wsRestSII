<?php

class funcionesRegistrales_actualizarMregEstVinculos {

    public static function actualizarMregEstVinculos($mysqli, $data, $codbarras = '', $tt = '', $rec = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $nameLog = 'actualizarMregEstVinculos_' . date("Ymd");

        //
        $cerrarmysql = 'no';
        if ($mysqli == null) {
            $cerrarmysql = 'si';
            $mysqli = conexionMysqliApi();
        }

        //
        if (ltrim($data["matricula"], "0") == '') {
            if ($cerrarmysql == 'si') {
                $mysqli->close();
            }
            return true;
        }

        $data["idclase"] = (isset($data["idclase"])) ? $data["idclase"] : '';
        $data["numid"] = (isset($data["numid"])) ? $data["numid"] : '';
        $data["nombre"] = (isset($data["nombre"])) ? $data["nombre"] : '';
        $data["nom1"] = (isset($data["nom1"])) ? $data["nom1"] : '';
        $data["nom2"] = (isset($data["nom2"])) ? $data["nom2"] : '';
        $data["ape1"] = (isset($data["ape1"])) ? $data["ape1"] : '';
        $data["ape2"] = (isset($data["ape2"])) ? $data["ape2"] : '';
        $data["vinculo"] = (isset($data["vinculo"])) ? $data["vinculo"] : '';
        $data["idcargo"] = (isset($data["idcargo"])) ? $data["idcargo"] : '';
        $data["descargo"] = (isset($data["descargo"])) ? $data["descargo"] : '';
        $data["idlibro"] = (isset($data["idlibro"])) ? $data["idlibro"] : '';
        $data["numreg"] = (isset($data["numreg"])) ? $data["numreg"] : '';
        $data["dupli"] = (isset($data["dupli"])) ? $data["dupli"] : '';
        $data["fecha"] = (isset($data["fecha"])) ? $data["fecha"] : '';
        $data["tirepresenta"] = (isset($data["tirepresenta"])) ? $data["tirepresenta"] : '';
        $data["idrepresenta"] = (isset($data["idrepresenta"])) ? $data["idrepresenta"] : '';
        $data["nmrepresenta"] = (isset($data["nmrepresenta"])) ? $data["nmrepresenta"] : '';
        $data["vinculodianpnat"] = (isset($data["vinculodianpnat"])) ? $data["vinculodianpnat"] : '';
        $data["vinculodianpjur"] = (isset($data["vinculodianpjur"])) ? $data["vinculodianpjur"] : '';
        $data["tarjprof"] = (isset($data["tarjprof"])) ? $data["tarjprof"] : '';
        $data["cuotasconst"] = (isset($data["cuotasconst"])) ? $data["cuotasconst"] : '';
        $data["valorconst"] = (isset($data["valorconst"])) ? $data["valorconst"] : '';
        $data["cuotasref"] = (isset($data["cuotasref"])) ? $data["cuotasref"] : '';
        $data["valorref"] = (isset($data["valorref"])) ? $data["valorref"] : '';
        $data["valorasociativa1"] = (isset($data["valorasociativa1"])) ? $data["valorasociativa1"] : '';
        $data["valorasociativa2"] = (isset($data["valorasociativa2"])) ? $data["valorasociativa2"] : '';
        $data["valorasociativa3"] = (isset($data["valorasociativa3"])) ? $data["valorasociativa3"] : '';
        $data["valorasociativa4"] = (isset($data["valorasociativa4"])) ? $data["valorasociativa4"] : '';
        $data["valorasociativa5"] = (isset($data["valorasociativa5"])) ? $data["valorasociativa5"] : '';
        $data["valorasociativa6"] = (isset($data["valorasociativa6"])) ? $data["valorasociativa6"] : '';
        $data["valorasociativa7"] = (isset($data["valorasociativa7"])) ? $data["valorasociativa7"] : '';
        $data["valorasociativa8"] = (isset($data["valorasociativa8"])) ? $data["valorasociativa8"] : '';
        $data["direccion"] = (isset($data["direccion"])) ? $data["direccion"] : '';
        $data["municipio"] = (isset($data["municipio"])) ? $data["municipio"] : '';
        $data["email"] = (isset($data["email"])) ? $data["email"] : '';
        $data["celular"] = (isset($data["celular"])) ? $data["celular"] : '';
        $data["email"] = (isset($data["email"])) ? $data["email"] : '';
        $data["fechanacimiento"] = (isset($data["fechanacimiento"])) ? $data["fechanacimiento"] : '';
        $data["pais"] = (isset($data["pais"])) ? $data["pais"] : '';
        $data["fechaexpdoc"] = (isset($data["fechaexpdoc"])) ? $data["fechaexpdoc"] : '';
        $data["ciiu1"] = (isset($data["ciiu1"])) ? $data["ciiu1"] : '';
        $data["ciiu2"] = (isset($data["ciiu2"])) ? $data["ciiu2"] : '';
        $data["ciiu3"] = (isset($data["ciiu3"])) ? $data["ciiu3"] : '';
        $data["ciiu4"] = (isset($data["ciiu4"])) ? $data["ciiu4"] : '';
        $data["tipositcontrol"] = (isset($data["tipositcontrol"])) ? $data["tipositcontrol"] : '';
        $data["desactiv"] = (isset($data["desactiv"])) ? $data["desactiv"] : '';
        $data["fechaconfiguracion"] = (isset($data["fechaconfiguracion"])) ? $data["fechaconfiguracion"] : '';
        $data["codcertifica"] = (isset($data["codcertifica"])) ? $data["codcertifica"] : '';
        $data["estado"] = (isset($data["estado"])) ? $data["estado"] : '';
        $data["fechahistorico"] = (isset($data["fechahistorico"])) ? $data["fechahistorico"] : '';
        $data["usuariohistorico"] = (isset($data["usuariohistorico"])) ? $data["usuariohistorico"] : '';
        $data["fecsincronizacion"] = (isset($data["fecsincronizacion"])) ? $data["fecsincronizacion"] : '';
        $data["horsincronizacion"] = (isset($data["horsincronizacion"])) ? $data["horsincronizacion"] : '';
        $data["compite360"] = (isset($data["compite360"])) ? $data["compite360"] : '';
        $data["sexo"] = (isset($data["sexo"])) ? $data["sexo"] : '';

        // 
        $arrCampos = array(
            'matricula',
            'idclase',
            'numid',
            'nombre',
            'nom1',
            'nom2',
            'ape1',
            'ape2',
            'vinculo',
            'idcargo',
            'descargo',
            'idlibro',
            'numreg',
            'dupli',
            'fecha',
            'tirepresenta',
            'idrepresenta',
            'nmrepresenta',
            'vinculodianpnat',
            'vinculodianpjur',
            'tarjprof',
            'cuotasconst',
            'valorconst',
            'cuotasref',
            'valorref',
            'valorasociativa1',
            'valorasociativa2',
            'valorasociativa3',
            'valorasociativa4',
            'valorasociativa5',
            'valorasociativa6',
            'valorasociativa7',
            'valorasociativa8',
            'direccion',
            'municipio',
            'email',
            'celular',
            'fechanacimiento',
            'pais',
            'fechaexpdoc',
            'ciiu1',
            'ciiu2',
            'ciiu3',
            'ciiu4',
            'tipositcontrol',
            'desactiv',
            'fechaconfiguracion',
            'codcertifica',
            'estado',
            'fechahistorico',
            'usuariohistorico',
            'fecsincronizacion',
            'horsincronizacion',
            'compite360',
            'sexo'
        );

        $arrValores = array(
            "'" . ltrim($data["matricula"], "0") . "'",
            "'" . trim($data["idclase"]) . "'",
            "'" . trim($data["numid"]) . "'",
            "'" . addslashes(trim($data["nombre"])) . "'",
            "'" . addslashes(trim($data["nom1"])) . "'",
            "'" . addslashes(trim($data["nom2"])) . "'",
            "'" . addslashes(trim($data["ape1"])) . "'",
            "'" . addslashes(trim($data["ape2"])) . "'",
            "'" . trim($data["vinculo"]) . "'",
            "'" . trim($data["idcargo"]) . "'",
            "'" . addslashes(trim($data["descargo"])) . "'",
            "'" . trim($data["idlibro"]) . "'",
            "'" . trim($data["numreg"]) . "'",
            "'" . trim($data["dupli"]) . "'",
            "'" . trim($data["fecha"]) . "'",
            "'" . trim($data["tirepresenta"]) . "'",
            "'" . trim($data["idrepresenta"]) . "'",
            "'" . trim($data["nmrepresenta"]) . "'",
            "'" . trim($data["vinculodianpnat"]) . "'",
            "'" . trim($data["vinculodianpjur"]) . "'",
            "'" . trim($data["tarjprof"]) . "'",
            "'" . trim($data["cuotasconst"]) . "'",
            "'" . trim($data["valorconst"]) . "'",
            "'" . trim($data["cuotasref"]) . "'",
            "'" . trim($data["valorref"]) . "'",
            "'" . trim($data["valorasociativa1"]) . "'",
            "'" . trim($data["valorasociativa2"]) . "'",
            "'" . trim($data["valorasociativa3"]) . "'",
            "'" . trim($data["valorasociativa4"]) . "'",
            "'" . trim($data["valorasociativa5"]) . "'",
            "'" . trim($data["valorasociativa6"]) . "'",
            "'" . trim($data["valorasociativa7"]) . "'",
            "'" . trim($data["valorasociativa8"]) . "'",
            "'" . trim($data["direccion"]) . "'",
            "'" . trim($data["municipio"]) . "'",
            "'" . trim($data["email"]) . "'",
            "'" . trim($data["celular"]) . "'",
            "'" . trim($data["fechanacimiento"]) . "'",
            "'" . trim($data["pais"]) . "'",
            "'" . trim($data["fechaexpdoc"]) . "'",
            "'" . trim($data["ciiu1"]) . "'",
            "'" . trim($data["ciiu2"]) . "'",
            "'" . trim($data["ciiu3"]) . "'",
            "'" . trim($data["ciiu4"]) . "'",
            "'" . trim($data["tipositcontrol"]) . "'",
            "'" . addslashes(trim($data["desactiv"])) . "'",
            "'" . trim($data["fechaconfiguracion"]) . "'",
            "'" . trim($data["codcertifica"]) . "'",
            "'" . trim($data["estado"]) . "'",
            "'" . trim($data["fechahistorico"]) . "'",
            "'" . trim($data["usuariohistorico"]) . "'",
            "'" . trim($data["fecsincronizacion"]) . "'",
            "'" . trim($data["horsincronizacion"]) . "'",
            "'NO'",
            "'" . trim($data["sexo"]) . "'"
        );

        \logApi::general2($nameLog, ltrim($data["matricula"], "0") . '-' . $tt, var_export($data, true));

        // Graba el registro en mreg_est_vinculos
        $idgrabar = 0;    //
        if ($data["matricula"] != '') {
            $condicion = "matricula='" . ltrim($data["matricula"], "0") . "' and vinculo='" . trim($data["vinculo"]) . "' and numid='" . trim($data["numid"]) . "'";

            $idreg = retornarRegistroMysqliApi($mysqli, 'mreg_est_vinculos', $condicion);

            if ($idreg && !empty($idreg)) {
                $idgrabar = $idreg["id"];
            }
        }

        if ($idgrabar == 0) {
            unset($_SESSION["expedienteactual"]);
            $control = 'insertar';
            $res = insertarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error creando matrícula en mreg_est_vinculos : ' . $_SESSION["generales"]["mensajeerror"]);
                if ($cerrarmysql == 'si') {
                    $mysqli->close();
                }
                return false;
            } else {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0") . '-' . $tt, "Grabado en mreg_est_vinculos");
            }

            //
            $cvinc = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $data["vinculo"] . "'");
            $tipomovimiento = 'vinculo-creado-otros';
            if ($cvinc && !empty($cvinc)) {
                if ($cvinc["tipovinculo"] == 'SOC' || $cvinc["tipovinculoesadl"] == 'SOC') {
                    $tipomovimiento = 'vinculo-creadao-socios';
                }
                if ($cvinc["tipovinculo"] == 'JDP') {
                    $tipomovimiento = 'vinculo-creado-jdp';
                }
                if ($cvinc["tipovinculo"] == 'JDS') {
                    $tipomovimiento = 'vinculo-creado-jds';
                }
                if ($cvinc["tipovinculoesadl"] == 'OAP') {
                    $tipomovimiento = 'vinculo-creado-oap';
                }
                if ($cvinc["tipovinculoesadl"] == 'OAS') {
                    $tipomovimiento = 'vinculo-creado-oas';
                }
                if ($cvinc["tipovinculo"] == 'RLP' || $cvinc["tipovinculoesadl"] == 'RLP') {
                    $tipomovimiento = 'vinculo-creado-rlp';
                }
                if ($cvinc["tipovinculo"] == 'RLPE') {
                    $tipomovimiento = 'vinculo-creado-rlp';
                }
                if ($cvinc["tipovinculo"] == 'RLS' || $cvinc["tipovinculoesadl"] == 'RLS') {
                    $tipomovimiento = 'vinculo-creado-rls';
                }
                if ($cvinc["tipovinculo"] == 'RFP' || $cvinc["tipovinculoesadl"] == 'RFP') {
                    $tipomovimiento = 'vinculo-creado-rfp';
                }
                if ($cvinc["tipovinculo"] == 'RFDP') {
                    $tipomovimiento = 'vinculo-creado-rfp';
                }
                if ($cvinc["tipovinculo"] == 'RFS' || $cvinc["tipovinculoesadl"] == 'RFS') {
                    $tipomovimiento = 'vinculo-creado-rfs';
                }
                if ($cvinc["tipovinculo"] == 'RFDS') {
                    $tipomovimiento = 'vinculo-creado-rfs';
                }
                if ($cvinc["tipovinculo"] == 'RFS1') {
                    $tipomovimiento = 'vinculo-creado-rfs';
                }
                if ($cvinc["tipovinculo"] == 'RFS2') {
                    $tipomovimiento = 'vinculo-creado-rfs';
                }
            }


            //Grabar histórico
            $arrCampos1 = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );
            $arrValores1 = array(
                "'" . ltrim($data["matricula"], "0") . "'",
                "'" . $tipomovimiento . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''", // Codigo de barras
                "''", // Datos originales
                "'" . addslashes($data["idclase"] . ':' . trim($data["numid"]) . ':' . trim($data["nombre"]) . ':' . trim($data["vinculo"]) . ':' . trim($data["idlibro"]) . ':' . trim($data["numreg"])) . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesGenerales::localizarIP() . "'",
                "'" . $tt . "'",
                "''" // recibo
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . date("Y"), $arrCampos1, $arrValores1);
        }

        //
        if ($idgrabar != 0) {
            unset($_SESSION["expedienteactual"]);
            $control = 'regrabar';
            $res = regrabarRegistrosMysqliApi($mysqli, 'mreg_est_vinculos', $arrCampos, $arrValores, "id=" . $idgrabar);
            if ($res === false) {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0"), 'Error regrabando matrícula en mreg_est_vinculos: ' . $_SESSION["generales"]["mensajeerror"]);
                if ($cerrarmysql == 'si') {
                    $mysqli->close();
                }
                return false;
            } else {
                \logApi::general2($nameLog, ltrim($data["matricula"], "0") . '-' . $tt, "Regrabado en mreg_est_vinculos");
            }

            //
            $cvinc = retornarRegistroMysqliApi($mysqli, 'mreg_codvinculos', "id='" . $data["vinculo"] . "'");
            $tipomovimiento = 'vinculo-modificado-otros';
            if ($cvinc && !empty($cvinc)) {
                if ($cvinc["tipovinculo"] == 'SOC' || $cvinc["tipovinculoesadl"] == 'SOC') {
                    $tipomovimiento = 'vinculo-modificado-socios';
                }
                if ($cvinc["tipovinculo"] == 'JDP') {
                    $tipomovimiento = 'vinculo-modificado-jdp';
                }
                if ($cvinc["tipovinculo"] == 'JDS') {
                    $tipomovimiento = 'vinculo-modificado-jds';
                }
                if ($cvinc["tipovinculoesadl"] == 'OAP') {
                    $tipomovimiento = 'vinculo-modificado-oap';
                }
                if ($cvinc["tipovinculoesadl"] == 'OAS') {
                    $tipomovimiento = 'vinculo-modificado-oas';
                }
                if ($cvinc["tipovinculo"] == 'RLP' || $cvinc["tipovinculoesadl"] == 'RLP') {
                    $tipomovimiento = 'vinculo-modificado-rlp';
                }
                if ($cvinc["tipovinculo"] == 'RLPE') {
                    $tipomovimiento = 'vinculo-modificado-rlp';
                }
                if ($cvinc["tipovinculo"] == 'RLS' || $cvinc["tipovinculoesadl"] == 'RLS') {
                    $tipomovimiento = 'vinculo-modificado-rls';
                }
                if ($cvinc["tipovinculo"] == 'RFP' || $cvinc["tipovinculoesadl"] == 'RFP') {
                    $tipomovimiento = 'vinculo-modificado-rfp';
                }
                if ($cvinc["tipovinculo"] == 'RFDP') {
                    $tipomovimiento = 'vinculo-modificado-rfp';
                }
                if ($cvinc["tipovinculo"] == 'RFS' || $cvinc["tipovinculoesadl"] == 'RFS') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }
                if ($cvinc["tipovinculo"] == 'RFDS') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }
                if ($cvinc["tipovinculo"] == 'RFS1') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }
                if ($cvinc["tipovinculo"] == 'RFS2') {
                    $tipomovimiento = 'vinculo-modificado-rfs';
                }
            }

            //Grabar histórico
            $arrCampos1 = array(
                'matricula',
                'campo',
                'fecha',
                'hora',
                'codigobarras',
                'datoanterior',
                'datonuevo',
                'usuario',
                'ip',
                'tipotramite',
                'recibo'
            );
            $arrValores1 = array(
                "'" . ltrim($data["matricula"], "0") . "'",
                "'" . $tipomovimiento . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''", // Codigo de barras
                "'" . addslashes($idreg["idclase"] . ':' . $idreg["numid"] . ':' . $idreg["nombre"] . ':' . $idreg["vinculo"] . ':' . $idreg["idlibro"] . ':' . $idreg["numreg"]) . "'", // Datos originales
                "'" . addslashes($data["idclase"] . ':' . trim($data["numid"]) . ':' . trim($data["nombre"]) . ':' . trim($data["vinculo"]) . ':' . trim($data["idlibro"]) . ':' . trim($data["numreg"])) . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . \funcionesGenerales::localizarIP() . "'",
                "'" . $tt . "'",
                "''" // recibo
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . date("Y"), $arrCampos1, $arrValores1);
        }

        // ********************************************************************************************* //
        // Cierra conexión con BD
        // ********************************************************************************************* //

        if ($cerrarmysql == 'si') {
            $mysqli->close();
        }

        //
        return true;
    }

}

?>
