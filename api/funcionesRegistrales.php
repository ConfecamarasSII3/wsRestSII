<?php

class funcionesRegistrales
{

    /**
     * 
     * @param type $mysqli
     * @param type $liq
     * @param type $dat
     * @param type $mom
     * @return type
     */
    public static function almacenarDatosImportantesRenovacion($mysqli, $liq, $dat, $mom)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_almacenarDatosImportantesRenovacion.php';
        return funcionesRegistrales_almacenarDatosImportantesRenovacion::almacenarDatosImportantesRenovacion($mysqli, $liq, $dat, $mom);
    }

    /**
     *
     * @param type $mysqli
     * @param type $data
     * @return type
     */
    public static function alertarNoComerciales($mysqli, $data)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_alertarNoComerciales.php';
        return funcionesRegistrales_alertarNoComerciales::alertarNoComerciales($mysqli, $data);
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbar
     * @param type $estado
     * @param type $ope
     * @param type $sede
     * @param type $imagenes
     * @param type $fecha
     * @param type $hora
     * @return type
     */
    public static function actualizarEstadoCodigoBarras($mysqli = null, $codbar = '', $estado = '', $ope = '', $sede = '', $imagenes = '', $fecha = '', $hora = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarEstadoCodigoBarras.php';
        return funcionesRegistrales_actualizarEstadoCodigoBarras::actualizarEstadoCodigoBarras($mysqli, $codbar, $estado, $ope, $sede, $imagenes, $fecha, $hora);
    }

    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @param type $altoimpacto
     * @param type $crear
     * @param type $arregloServiciosMatricula
     * @param type $arregloServiciosRenovacion
     * @param type $arregloServiciosAfiliacion
     * @return type
     */
    public static function actualizarMregEstInscritos($mysqli = null, $data = array(), $codbarras = '', $tt = '', $rec = '', $altoimpacto = 'no', $crear = 'si', $arregloServiciosMatricula = array(), $arregloServiciosRenovacion = array(), $arregloServiciosAfiliacion = array())
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInscritos.php';
        return \funcionesRegistrales_actualizarMregEstInscritos::actualizarMregEstInscritos($mysqli, $data, $codbarras, $tt, $rec, $altoimpacto, $crear, $arregloServiciosMatricula, $arregloServiciosRenovacion, $arregloServiciosAfiliacion);
    }

    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return type
     */
    public static function actualizarMregEstTextos($mysqli, $mat = '', $data = array(), $codbarras = '', $tt = '', $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInscritos.php';
        return \funcionesRegistrales_actualizarMregEstInscritos::actualizarMregEstTextos($mysqli, $mat, $data, $codbarras, $tt, $rec);
    }


    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return type
     */
    public static function actualizarMregEstInformacionFinanciera($mysqli, $data, $codbarras = '', $tt = '', $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInformacionFinanciera.php';
        return \funcionesRegistrales_actualizarMregEstInformacionFinanciera::actualizarMregEstInformacionFinanciera($mysqli, $data, $codbarras, $tt, $rec);
    }

    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return type
     */
    public static function actualizarMregEstInformacionFinancieraEstablecimientos($mysqli, $data, $codbarras = '', $tt = '', $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInformacionFinancieraEstablecimientos.php';
        return \funcionesRegistrales_actualizarMregEstInformacionFinancieraEstablecimientos::actualizarMregEstInformacionFinancieraEstablecimientos($mysqli, $data, $codbarras, $tt, $rec);
    }


    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return type
     */
    public static function actualizarMregEstCapitales($mysqli, $data, $codbarras = '', $tt = '', $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstCapitales.php';
        return \funcionesRegistrales_actualizarMregEstCapitales::actualizarMregEstCapitales($mysqli, $data, $codbarras, $tt, $rec);
    }

    /**
     *
     * @param type $dbx
     * @param type $matricula
     * @param type $campo
     * @param type $contenido
     * @param type $tipocampo
     * @param type $codbarras
     * @param type $tipotramite
     * @param type $recibo
     * @return type
     */
    public static function actualizarMregEstInscritosCampo($dbx, $matricula, $campo, $contenido = '', $tipocampo = 'varchar', $codbarras = '', $tipotramite = '', $recibo = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInscritosCampo.php';
        return \funcionesRegistrales_actualizarMregEstInscritosCampo::actualizarMregEstInscritosCampo($dbx, $matricula, $campo, $contenido, $tipocampo, $codbarras, $tipotramite, $recibo);
    }

    /**
     *
     * @param type $dbx
     * @param type $matricula
     * @param type $campo
     * @param type $contenido
     * @param type $codbarras
     * @param type $tipotramite
     * @param type $recibo
     * @return type
     */
    public static function actualizarMregEstInscritosCampoCampos($dbx, $matricula, $campo, $contenido = '', $codbarras = '', $tipotramite = '', $recibo = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInscritosCampo.php';
        return \funcionesRegistrales_actualizarMregEstInscritosCampo::actualizarMregEstInscritosCampoCampos($dbx, $matricula, $campo, $contenido, $codbarras, $tipotramite, $recibo);
    }


    /**
     *
     * @param type $dbx
     * @param type $proponente
     * @param type $campo
     * @param type $contenido
     * @param type $tipocampo
     * @param type $codbarras
     * @param type $tipotramite
     * @param type $recibo
     * @return type
     */
    public static function actualizarMregEstInscritosCampoPorProponente($dbx = null, $proponente = '', $campo = '', $contenido = '', $tipocampo = 'varchar', $codbarras = '', $tipotramite = '', $recibo = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInscritosCampoPorProponente.php';
        return \funcionesRegistrales_actualizarMregEstInscritosCampoPorProponente::actualizarMregEstInscritosCampoPorProponente($dbx, $proponente, $campo, $contenido, $tipocampo, $codbarras, $tipotramite, $recibo);
    }

    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return type
     */
    public static function actualizarMregEstVinculos($mysqli, $data, $codbarras = '', $tt = '', $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstVinculos.php';
        return \funcionesRegistrales_actualizarMregEstVinculos::actualizarMregEstVinculos($mysqli, $data, $codbarras, $tt, $rec);
    }

    /**
     *
     * @param type $mysqli
     * @param type $data
     * @param type $codbarras
     * @param type $tt
     * @param type $rec
     * @return type
     */
    public static function actualizarMregCertificasSii($mysqli, $data, $codbarras = '', $tt = '', $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregCertificasSii.php';
        return \funcionesRegistrales_actualizarMregCertificasSii::actualizarMregCertificasSii($mysqli, $data, $codbarras, $tt, $rec);
    }

    /**
     *
     * @param type $dbx
     * @param type $data
     * @param type $acto
     * @param type $gm
     * @param type $estado
     * @return type
     */
    public static function actualizarMregEstInscritosProponente($dbx, $data, $acto = '', $gm = array(), $estado = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregEstInscritosProponente.php';
        return \funcionesRegistrales_actualizarMregEstInscritosProponente::actualizarMregEstInscritosProponente($dbx, $data, $acto, $gm, $estado);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $libro
     * @param type $acto
     * @param type $matricula
     * @return type
     */
    public static function actualizarMregInscritosPendienteNuevoCertificado($mysqli = null, $libro = null, $acto = null, $matricula = null)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregInscritosPendienteNuevoCertificado.php';
        return \funcionesRegistrales_actualizarMregInscritosPendienteNuevoCertificado::actualizarMregInscritosPendienteNuevoCertificado($mysqli, $libro, $acto, $matricula);
    }

    /**
     *
     * @param type $dbx
     * @param type $tt
     * @param type $numliq
     * @param type $idsol
     * @param type $est
     * @param type $numrec
     * @param type $numope
     * @param type $numrad
     * @param type $fecrec
     * @param type $horrec
     * @return type
     */
    public static function actualizarMregLiquidacionFlujo($dbx, $tt, $numliq, $idsol, $est, $numrec, $numope, $numrad, $fecrec, $horrec)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionFlujo.php';
        return \funcionesRegistrales_actualizarMregLiquidacionFlujo::actualizarMregLiquidacionFlujo($dbx, $tt, $numliq, $idsol, $est, $numrec, $numope, $numrad, $fecrec, $horrec);
    }

    /**
     *
     * @param type $dbx
     * @param type $liqui
     * @param type $est
     * @param type $nomcli
     * @param type $idtipide
     * @param type $dir
     * @param type $tel
     * @param type $mun
     * @param type $email
     * @param type $ide
     * @param type $pagefe
     * @param type $pagche
     * @param type $pagvis
     * @param type $pagach
     * @param type $pagmas
     * @param type $pagame
     * @param type $pagcre
     * @param type $pagdin
     * @param type $pagtdeb
     * @param type $codban
     * @param type $numche
     * @param type $numaut
     * @param type $caj
     * @param type $numope
     * @param type $numrec
     * @param type $fecrec
     * @param type $horrec
     * @param type $numopegob
     * @param type $numrecgob
     * @param type $fecrecgob
     * @param type $horrecgob
     * @param type $codbar
     * @param type $xfra
     * @param type $xnfra
     * @param type $formapago
     * @return type
     */
    public static function actualizarMregLiquidacionPagoElectronico($dbx, $liqui, $est, $nomcli, $idtipide, $dir, $tel, $mun, $email, $ide, $pagefe, $pagche, $pagvis, $pagach, $pagmas, $pagame, $pagcre, $pagdin, $pagtdeb, $codban, $numche, $numaut, $caj, $numope = '', $numrec = '', $fecrec = '', $horrec = '', $numopegob = '', $numrecgob = '', $fecrecgob = '', $horrecgob = '', $codbar = '', $xfra = '', $xnfra = '', $formapago = '05')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionPagoElectronico.php';
        return \funcionesRegistrales_actualizarMregLiquidacionPagoElectronico::actualizarMregLiquidacionPagoElectronico($dbx, $liqui, $est, $nomcli, $idtipide, $dir, $tel, $mun, $email, $ide, $pagefe, $pagche, $pagvis, $pagach, $pagmas, $pagame, $pagcre, $pagdin, $pagtdeb, $codban, $numche, $numaut, $caj, $numope, $numrec, $fecrec, $horrec, $numopegob, $numrecgob, $fecrecgob, $horrecgob, $codbar, $xfra, $xnfra, $formapago);
    }

    /**
     *
     * @param type $dbx
     * @param type $liqui
     * @param type $est
     * @return type
     */
    public static function actualizarMregLiquidacionEstado($dbx, $liqui, $est)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionEstado.php';
        return \funcionesRegistrales_actualizarMregLiquidacionEstado::actualizarMregLiquidacionEstado($dbx, $liqui, $est);
    }

    /**
     *
     * @param type $dbx
     * @param type $liq
     * @param type $sec
     * @param type $exp
     * @param type $ide
     * @param type $gru
     * @param type $xml
     * @param type $est
     * @return type
     */
    public static function actualizarMregLiquidacionDatos($dbx, $liq, $sec, $exp, $ide, $gru, $xml, $est)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionDatos.php';
        return \funcionesRegistrales_actualizarMregLiquidacionDatos::actualizarMregLiquidacionDatos($dbx, $liq, $sec, $exp, $ide, $gru, $xml, $est);
    }

    /**
     *
     * @param type $dbx
     * @param type $liq
     * @param type $sec
     * @param type $gru
     * @param type $xml
     * @return type
     */
    public static function actualizarMregLiquidacionDatosOriginal($dbx, $liq, $sec, $gru, $xml)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionDatosOriginal.php';
        return \funcionesRegistrales_actualizarMregLiquidacionDatosOriginal::actualizarMregLiquidacionDatosOriginal($dbx, $liq, $sec, $gru, $xml);
    }

    /**
     *
     * @param type $dbx
     * @param type $idliquidacion
     * @param type $expediente
     * @param type $xml1
     * @return type
     */
    public static function grabarMregLiquidacionDatosLog($dbx, $idliquidacion, $expediente, $xml1)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarMregLiquidacionDatosLog.php';
        return \funcionesRegistrales_grabarMregLiquidacionDatosLog::grabarMregLiquidacionDatosLog($dbx, $idliquidacion, $expediente, $xml1);
    }

    /**
     *
     * @param type $dbx
     * @param type $liq
     * @param type $sec
     * @param type $exp
     * @param type $ide
     * @param type $dat
     * @param type $est
     * @return type
     */
    public static function actualizarMregLiquidacionDatosControl($dbx, $liq, $sec, $exp, $ide, $dat, $est)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionDatosControl.php';
        return \funcionesRegistrales_actualizarMregLiquidacionDatosControl::actualizarMregLiquidacionDatosControl($dbx, $liq, $sec, $exp, $ide, $dat, $est);
    }

    /**
     *
     * @param type $dbx
     * @param type $liq
     * @param type $sec
     * @param type $exp
     * @param type $ide
     * @param type $dat
     * @param type $est
     * @return type
     */
    public static function actualizarMregLiquidacionGruposModificados($dbx, $liq, $sec, $exp, $ide, $dat, $est)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregLiquidacionGruposModificados.php';
        return \funcionesRegistrales_actualizarMregLiquidacionGruposModificados::actualizarMregLiquidacionGruposModificados($dbx, $liq, $sec, $exp, $ide, $dat, $est);
    }

    /**
     *
     * @param type $dbx
     * @param type $numrad
     * @param type $sec
     * @param type $tipotra
     * @param type $exp
     * @param type $est
     * @param type $xml
     * @return type
     */
    public static function actualizarMregRadicacionesDatos($dbx, $numrad, $sec, $tipotra, $exp, $est, $xml)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregRadicacionesDatos.php';
        return \funcionesRegistrales_actualizarMregRadicacionesDatos::actualizarMregRadicacionesDatos($dbx, $numrad, $sec, $tipotra, $exp, $est, $xml);
    }

    /**
     *
     * @param type $mysqli
     * @param type $numrad
     * @param type $sec
     * @param type $tipotra
     * @param type $xml
     * @return bool
     */
    public static function actualizarMregRadicacionesDatosOriginal($mysqli, $numrad, $sec, $tipotra, $xml)
    {
        $arrCampos = array(
            'idradicacion',
            'secuencia',
            'tipotramite',
            'xml'
        );
        $arrValues = array(
            "'" . ltrim($numrad, "0") . "'",
            "'" . $sec . "'",
            "'" . $tipotra . "'",
            "'" . addslashes(\funcionesGenerales::restaurarEspeciales($xml)) . "'"
        );
        $query = "idradicacion='" . ltrim($numrad, "0") . "' and secuencia='" . $sec . "'";
        $result = borrarRegistrosMysqliApi($mysqli, 'mreg_radicacionesdatosoriginal', $query);
        if ($result === false) {
            return false;
        } else {
            $result = insertarRegistrosMysqliApi($mysqli, 'mreg_radicacionesdatosoriginal', $arrCampos, $arrValues);
            if ($result === false) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     *
     * @param type $dbx
     * @param type $rad
     * @param type $sec
     * @param type $exp
     * @param type $ide
     * @param type $dat
     * @param type $est
     * @return type
     */
    public static function actualizarMregRadicacionDatosControl($dbx, $rad, $sec, $exp, $ide, $dat, $est)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregRadicacionDatosControl.php';
        return \funcionesRegistrales_actualizarMregRadicacionDatosControl::actualizarMregRadicacionDatosControl($dbx, $rad, $sec, $exp, $ide, $dat, $est);
    }

    /**
     *
     * @param type $dbx
     * @param type $clave
     * @param type $contenido
     * @return type
     */
    public static function actualizarMregSecuencia($dbx, $clave, $contenido)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregSecuencia.php';
        return \funcionesRegistrales_actualizarMregSecuencia::actualizarMregSecuencia($dbx, $clave, $contenido);
    }

    /**
     *
     * @param type $dbx
     * @param type $clave
     * @param type $contenido
     * @return type
     */
    public static function actualizarMregSecuenciasAsentarRecibo($dbx, $clave, $contenido)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregSecuenciasAsentarRecibo.php';
        return \funcionesRegistrales_actualizarMregSecuenciasAsentarRecibo::actualizarMregSecuenciasAsentarRecibo($dbx, $clave, $contenido);
    }

    /**
     *
     * @param type $mysqli
     * @param type $tnot
     * @param type $rad
     * @param type $dev
     * @param type $ope
     * @param type $rec
     * @param type $lib
     * @param type $reg
     * @param type $dup
     * @param type $idc
     * @param type $ide
     * @param type $mat
     * @param type $pro
     * @param type $nom
     * @param type $ema
     * @param type $det
     * @param type $fpro
     * @param type $hpro
     * @param type $fnot
     * @param type $hnot
     * @param type $est
     * @param type $obs
     * @param type $bandeja
     * @return type
     */
    public static function actualizarMregNotificacionesParaEnviarEmail($mysqli, $tnot = '', $rad = '', $dev = '', $ope = '', $rec = '', $lib = '', $reg = '', $dup = '', $idc = '', $ide = '', $mat = '', $pro = '', $nom = '', $ema = '', $det = '', $fpro = '', $hpro = '', $fnot = '', $hnot = '', $est = '', $obs = '', $bandeja = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarMregNotificacionesParaEnviarEmail.php';
        return \funcionesRegistrales_actualizarMregNotificacionesParaEnviarEmail::actualizarMregNotificacionesParaEnviarEmail($mysqli, $tnot, $rad, $dev, $ope, $rec, $lib, $reg, $dup, $idc, $ide, $mat, $pro, $nom, $ema, $det, $fpro, $hpro, $fnot, $hnot, $est, $obs, $bandeja);
    }

    /**
     *
     * @param type $mysqli
     * @param type $pref
     * @param type $cel
     * @param type $tip
     * @param type $rec
     * @param type $cba
     * @param type $ins
     * @param type $dev
     * @param type $exp
     * @param type $mat
     * @param type $pro
     * @param type $ide
     * @param type $nom
     * @param type $txt
     * @param type $obs
     * @param type $bandeja
     * @return type
     */
    public static function actualizarPilaSms($mysqli = null, $pref = '', $cel = '', $tip = '', $rec = '', $cba = '', $ins = '', $dev = '', $exp = '', $mat = '', $pro = '', $ide = '', $nom = '', $txt = '', $obs = '', $bandeja = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarPilaSms.php';
        return \funcionesRegistrales_actualizarPilaSms::actualizarPilaSms($mysqli, $pref, $cel, $tip, $rec, $cba, $ins, $dev, $exp, $mat, $pro, $ide, $nom, $txt, $obs, $bandeja);
    }

    /**
     *
     * @param type $mysqli
     * @param type $tipo
     * @param type $radicacion
     * @param type $devolucion
     * @param type $operacion
     * @param type $recibo
     * @param type $libro
     * @param type $registro
     * @param type $dupli
     * @param type $iclase
     * @param type $numid
     * @param type $matricula
     * @param type $proponente
     * @param type $nombre
     * @param type $detalle
     * @param type $emailsdestino
     * @return type
     */
    public static function actualizarPilaEmails($mysqli = null, $tipo = '', $radicacion = '', $devolucion = '', $operacion = '', $recibo = '', $libro = '', $registro = '', $dupli = '', $idclase = '', $numid = '', $matricula = '', $proponente = '', $nombre = '', $detalle = '', $emailsdestino = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarPilaSms.php';
        return \funcionesRegistrales_actualizarPilaEmails::actualizarPilaEmails($mysqli, $tipo, $radicacion, $devolucion, $operacion, $recibo, $libro, $registro, $dupli, $idclase, $numid, $matricula, $proponente, $nombre, $detalle, $emailsdestino);
    }

    public static function actualizarPasosDigitacion($mysqli = null, $codigobarras = '', $numlibro = '', $numreg = '', $codigopaso = '')
    {

        //
        if ($codigobarras != '') {
            $arrCampos = array(
                'codigobarras',
                'numlibro',
                'numreg',
                'codigopaso',
                'usuario',
                'fecha',
                'hora'
            );

            //
            $arrValores = array(
                "'" . ltrim((string) $codigobarras, "0") . "'",
                "'" . $numlibro . "'",
                "'" . $numreg . "'",
                "'" . $codigopaso . "'",
                "'" . $_SESSION["generales"]["codigousuario"] . "'",
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'"
            );

            //
            $condicion = "codigobarras='" . ltrim((string) $codigobarras, "0") . "' and ";
            $condicion .= "numlibro='" . $numlibro . "' and ";
            $condicion .= "numreg='" . $numreg . "' and ";
            $condicion .= "codigopaso='" . $codigopaso . "'";

            //
            insertarRegistrosMysqliApi($mysqli, 'mreg_pasosdigitacion_ejecutados', $arrCampos, $arrValores);
        }
    }

    /**
     *
     * @param type $dbx
     * @param type $cri
     * @param type $ide
     * @param type $clave
     * @param type $valor
     * @param type $recibo
     * @param type $numoperacion
     * @param type $servicio
     * @param type $cantidad
     * @param type $detalle
     * @param type $ip
     * @param type $usuario
     * @param type $expediente
     * @param type $email
     * @param type $nombre
     * @param type $celular
     * @param type $direccion
     * @param type $municipio
     * @param type $tipousuario
     * @param type $telefono
     * @param type $nom1
     * @param type $nom2
     * @param type $ape1
     * @param type $ape2
     * @return type
     */
    public static function actualizarPrepago($dbx, $cri, $ide, $clave = '', $valor = '', $recibo = '', $numoperacion = '', $servicio = '', $cantidad = '', $detalle = '', $ip = '', $usuario = '', $expediente = '', $email = '', $nombre = '', $celular = '', $direccion = '', $municipio = '', $tipousuario = '', $telefono = '', $nom1 = '', $nom2 = '', $ape1 = '', $ape2 = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_actualizarPrepago.php';
        return \funcionesRegistrales_actualizarPrepago::actualizarPrepago($dbx, $cri, $ide, $clave, $valor, $recibo, $numoperacion, $servicio, $cantidad, $detalle, $ip, $usuario, $expediente, $email, $nombre, $celular, $direccion, $municipio, $tipousuario, $telefono, $nom1, $nom2, $ape1, $ape2);
    }

    /**
     *
     * @param type $dbx
     * @param type $ind
     * @param type $tra
     * @param type $trans
     * @return type
     */
    public static function adicionarActoEstudioAsentarRecibo($dbx, $ind, $tra, $trans)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_adicionarActoEstudioAsentarRecibo.php';
        return \funcionesRegistrales_adicionarActoEstudioAsentarRecibo::adicionarActoEstudioAsentarRecibo($dbx, $ind, $tra, $trans);
    }

    /**
     *
     * @param type $dbx
     * @param type $tramae
     * @param type $tradat
     * @param type $tiptra
     * @param type $numacto
     * @return type
     */
    public static function adicionarInscripcionCancelacion($dbx, $tramae, $tradat, $tiptra, $numacto)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_adicionarInscripcionCancelacion.php';
        return \funcionesRegistrales_adicionarInscripcionCancelacion::adicionarInscripcionCancelacion($dbx, $tramae, $tradat, $tiptra, $numacto);
    }

    public static function adicionarInscripcionCancelacionIndividual($dbx = null, $matricula = '', $motivo = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_adicionarInscripcionCancelacion.php';
        return \funcionesRegistrales_adicionarInscripcionCancelacion::adicionarInscripcionCancelacionIndividual($dbx, $matricula, $motivo);
    }

    /**
     *
     * @return type
     */
    public static function armarDataBasicaMercantil()
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_armarDataBasicaMercantil.php';
        return \funcionesRegistrales_armarDataBasicaMercantil::armarDataBasicaMercantil();
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbarras
     * @param type $iddevolucion
     * @param type $firma
     * @return type
     */
    public static function armarDevolutivoNuevo($mysqli, $codbarras, $iddevolucion, $firma = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_armarDevolutivoNuevo.php';
        return \funcionesRegistrales_armarDevolutivoNuevo::armarDevolutivoNuevo($mysqli, $codbarras, $iddevolucion, $firma);
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbarras
     * @param type $iddevolucion
     * @param type $firma
     * @return type
     */
    public static function armarFormatoResponsabilidades($mysqli, $numrec, $numliq)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_armarFormatoResponsabilidades.php';
        return \funcionesRegistrales_armarFormatoResponsabilidades::armarFormatoResponsabilidades($mysqli, $numrec, $numliq);
    }

    /**
     *
     * @param type $mysqli
     * @param type $d
     * @param type $arrUsu
     * @return type
     */
    public static function armarDesistimiento($mysqli, $d, $arrUsu)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_armarDesistimiento.php';
        return \funcionesRegistrales_armarDesistimiento::armarDesistimiento($mysqli, $d, $arrUsu);
    }

    /**
     *
     * @param type $dbx
     * @param type $numliq
     * @return type
     */
    public static function borrarMregLiquidacion($dbx, $numliq)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_borrarMregLiquidacion.php';
        return \funcionesRegistrales_borrarMregLiquidacion::borrarMregLiquidacion($dbx, $numliq);
    }

    /**
     *
     * @param type $mysqli
     * @param type $idservicio
     * @param type $ano
     * @param type $base
     * @param type $tipotarifa
     * @param type $idclasevalor
     * @return type
     */
    public static function buscarRangoTarifa($mysqli, $idservicio = '', $ano = 0, $base = 0, $tipotarifa = 'tarifa', $idclasevalor = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_buscarRangoTarifa.php';
        return \funcionesRegistrales_buscarRangoTarifa::buscarRangoTarifa($mysqli, $idservicio, $ano, $base, $tipotarifa, $idclasevalor);
    }

    /**
     *
     * @param type $mysqli
     * @param type $idservicio
     * @param type $ano
     * @param type $base
     * @param type $tipotarifa
     * @return type
     */
    public static function buscarTarifaValor($mysqli, $idservicio = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_buscarTarifaValor.php';
        return \funcionesRegistrales_buscarTarifaValor::buscarTarifaValor($mysqli, $idservicio);
    }

    /**
     *
     * @param type $mysqli
     * @param type $idservicio
     * @param type $ano
     * @param type $cantidad
     * @param type $base
     * @param type $tipotarifa
     * @param type $actprop
     * @param type $cantesttot
     * @param type $matricula
     * @param type $descuentoaplicable
     * @return type
     */
    public static function buscaTarifa($mysqli, $idservicio = '', $ano = 0, $cantidad = 0, $base = 0, $tipotarifa = 'tarifa', $actprop = 0, $cantesttot = 0, $matricula = '', $descuentoaplicable = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_buscaTarifa.php';
        return \funcionesRegistrales_buscaTarifa::buscaTarifa($mysqli, $idservicio, $ano, $cantidad, $base, $tipotarifa, $actprop, $cantesttot, $matricula, $descuentoaplicable);
    }

    /**
     *
     * @param type $mysqli
     * @param type $mat
     * @param type $actos
     * @param type $fini
     * @return type
     */
    public static function busqueInscripciones($mysqli, $mat, $actos, $fini = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_busqueInscripciones.php';
        return \funcionesRegistrales_busqueInscripciones::busqueInscripciones($mysqli, $mat, $actos, $fini);
    }

    /**
     *
     * @param type $mysqli
     * @param type $atra
     * @param type $nameLog
     */
    public static function recalcularValorTotalLiquidacion($mysqli, $atra, $nameLog)
    {
        $valtot = 0;
        $valbru = 0;
        $valiva = 0;
        foreach ($atra["liquidacion"] as $l) {
            $valtot = $valtot + $l["valorservicio"];
            $serv = retornarRegistroMysqliApi($mysqli, 'mreg_servicios', "idservicio='" . $l["idservicio"] . "'");
            if ($serv && !empty($serv)) {
                if ($serv["idesiva"] == 'S') {
                    $valiva = $valiva + $l["valorservicio"];
                } else {
                    $valbru = $valbru + $l["valorservicio"];
                }
            }
        }
        if ($valtot != $atra) {
            \logApi::general2($nameLog, $atra["idliquidacion"], 'Total de liquidación reportado erroneamente y recalculado, llegó: ' . $_SESSION["tramite"]["valortotal"] . ', valor real según detalle: ' . $valtot);
            $atra["valorbruto"] = $valbru;
            $atra["valoriva"] = $valiva;
            $atra["valortotal"] = $valtot;
            $arrCampos = array(
                'valorbruto',
                'valoriva',
                'valortotal',
                'totalrecibo'
            );
            $arrValores = array(
                $valbru,
                $valiva,
                $valtot,
                $valtot
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
        }

        return $atra;
    }

    /**
     *
     * @param type $mysqli
     * @param type $servicio
     * @param type $valor
     * @return type
     */
    public static function redondearServicio($mysqli, $servicio, $valor)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_redondearServicio.php';
        return \funcionesRegistrales_redondearServicio::redondearServicio($mysqli, $servicio, $valor);
    }

    /**
     *
     * @param type $mysqli
     * @param type $txt
     * @return type
     */
    public static function relacionMatriculasRenovar($mysqli, $txt)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_relacionMatriculasRenovar.php';
        return \funcionesRegistrales_relacionMatriculasRenovar::relacionMatriculasRenovar($mysqli, $servicio, $valor);
    }

    /**
     *
     * @param type $mysqli
     * @param type $criterio
     * @param type $matbase
     * @param type $propbase
     * @param type $nombase
     * @param type $palbase
     * @param type $idebase
     * @param type $tipoide
     * @param type $semilla
     * @param type $cantidadregistros
     * @param type $mostrarnomatriculados
     * @param type $soloestablecimientos
     * @param type $mostrarcancelados
     * @param type $relacionestablecimientos
     * @param type $registroexacto
     * @return type
     */
    public static function retornarBusquedaExpedientes($mysqli, $criterio = '', $matbase = '', $propbase = '', $nombase = '', $palbase = '', $idebase = '', $tipoide = '', $semilla = '0', $cantidadregistros = 15, $mostrarnomatriculados = 'S', $soloestablecimientos = 'N', $mostrarcancelados = 'S', $relacionestablecimientos = 'N', $registroexacto = 'N')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarBusquedaExpedientes.php';
        return \funcionesRegistrales_retornarBusquedaExpedientes::retornarBusquedaExpedientes($mysqli, $criterio, $matbase, $propbase, $nombase, $palbase, $idebase, $tipoide, $semilla, $cantidadregistros, $mostrarnomatriculados, $soloestablecimientos, $mostrarcancelados, $relacionestablecimientos, $registroexacto);
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbarras
     * @return type
     */
    public static function retornarCodigoBarras($mysqli, $codbarras)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarCodigoBarras.php';
        return \funcionesRegistrales_retornarCodigoBarras::retornarCodigoBarras($mysqli, $codbarras);
    }

    /**
     *
     * @param type $dbx
     * @param type $arrTem
     * @return type
     */
    public static function retornarExpedienteSolicitudNit($dbx, $arrTem)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarExpedienteSolicitudNit.php';
        return \funcionesRegistrales_retornarExpedienteSolicitudNit::retornarExpedienteSolicitudNit($dbx, $arrTem);
    }

    /**
     *
     * @param type $mysqli
     * @param type $cri
     * @param type $lib
     * @param type $regi
     * @param type $dup
     * @param type $fecini
     * @param type $tipo
     * @return type
     */
    public static function retornarInscripcionesNoNotificadas($mysqli, $cri, $lib = '', $regi = '', $dup = '', $fecini = '', $tipo = 'I')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarInscripcionesNoNotificadas.php';
        return \funcionesRegistrales_retornarInscripcionesNoNotificadas::retornarInscripcionesNoNotificadas($mysqli, $cri, $lib, $regi, $dup, $fecini, $tipo);
    }

    /**
     *
     * @param type $dbx
     * @param type $mat
     * @param type $retorno
     * @return type
     */
    public static function calcularHashMercantil($dbx, $mat = '', $retorno = array())
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_calcularHashMercantil.php';
        return \funcionesRegistrales_calcularHashMercantil::calcularHashMercantil($dbx, $mat, $retorno);
    }

    /**
     *
     * @param type $dbx
     * @param type $mat
     * @param type $idclase
     * @param type $numid
     * @param type $namex
     * @param type $tipodata
     * @param type $tipoconsulta
     * @param type $establecimientosnacionales
     * @param type $serviciosMatriculaE
     * @param type $serviciosRenovacionE
     * @param type $serviciosAfiliacionE
     * @return type
     */
    public static function retornarExpedienteMercantil($dbx = null, $mat = '', $idclase = '', $numid = '', $namex = '', $tipodata = '', $tipoconsulta = 'T', $establecimientosnacionales = 'N', $serviciosMatriculaE = array(), $serviciosRenovacionE = array(), $serviciosAfiliacionE = array())
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarExpedienteMercantil.php';
        return \funcionesRegistrales_retornarExpedienteMercantil::retornarExpedienteMercantil($dbx, $mat, $idclase, $numid, $namex, $tipodata, $tipoconsulta, $establecimientosnacionales, $serviciosMatriculaE, $serviciosRenovacionE, $serviciosAfiliacionE);
    }

    /**
     *
     * @param type $dbx
     * @param type $mat
     * @return type
     */
    public static function retornarExpedienteMercantilPropietarios($dbx = null, $mat = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarExpedienteMercantilPropietarios.php';
        return \funcionesRegistrales_retornarExpedienteMercantilPropietarios::retornarExpedienteMercantilPropietarios($dbx, $mat);
    }

    /**
     * 
     * @param type $dbx
     * @param type $mat
     * @return type
     */
    public static function retornarExpedienteMercantilRazonSocial($dbx = null, $mat = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarExpedienteMercantilRazonSocial.php';
        return \funcionesRegistrales_retornarExpedienteMercantilRazonSocial::retornarExpedienteMercantilRazonSocial($dbx, $mat);
    }

    /**
     * 
     * @param type $dbx
     * @param type $mat
     * @return type
     */

    public static function retornarExpedienteMercantilCodigosBarrasPendientes($dbx = null, $mat = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarExpedienteMercantilCodigosBarrasPendientes.php';
        return \funcionesRegistrales_retornarExpedienteMercantilCodigosBarrasPendientes::retornarExpedienteMercantilCodigosBarrasPendientes($dbx, $mat);
    }

    /**
     *
     * @param type $dbx
     * @param type $prop
     * @param type $mat
     * @param type $tipotramite
     * @param type $proceso
     * @param type $origen
     * @param type $retornarInhabilidad
     * @param type $retornarRee
     * @param type $incluir
     * @return type
     */
    public static function retornarExpedienteProponente($dbx = null, $prop = '', $mat = '', $tipotramite = '', $proceso = 'Sin identificar la rutina', $origen = '', $retornarInhabilidad = 'si', $retornarRee = 'si', $incluir = 'todos')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarExpedienteProponente.php';
        return \funcionesRegistrales_retornarExpedienteProponente::retornarExpedienteProponente($dbx, $prop, $mat, $tipotramite, $proceso, $origen, $retornarInhabilidad, $retornarRee, $incluir);
    }

    /**
     *
     * @param type $mysqli
     * @param type $numliq
     * @param type $tipotramite
     * @param type $numexp
     * @param type $grudat
     * @param type $proceso
     * @param type $secuencia
     * @return type
     */
    public static function retornarLiquidacionDatosExpediente($mysqli, $numliq, $tipotramite, $numexp = '', $grudat = '', $proceso = '', $secuencia = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarLiquidacionDatosExpediente.php';
        return \funcionesRegistrales_retornarLiquidacionDatosExpediente::retornarLiquidacionDatosExpediente($mysqli, $numliq, $tipotramite, $numexp, $grudat, $proceso, $secuencia);
    }

    /**
     *
     * @param type $mysqli
     * @param type $mat
     * @param type $procesartodas
     * @param type $ide
     * @param type $idliq
     * @param type $controlmultas
     * @return type
     */
    public static function retornarListaMatriculasRenovar($mysqli, $mat = '', $procesartodas = 'L', $ide = '', $idliq = 0, $controlmultas = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarListaMatriculasRenovar.php';
        return \funcionesRegistrales_retornarListaMatriculasRenovar::retornarListaMatriculasRenovar($mysqli, $mat, $procesartodas, $ide, $idliq, $controlmultas);
    }

    /**
     *
     * @param type $mysqli
     * @param type $mat
     * @param type $ide
     * @param type $procesartodas
     * @param type $usuario
     * @return type
     */
    public static function retornarListaMatriculasRenovarNuevo($mysqli, $mat = '', $ide = '', $procesartodas = 'L', $usuario = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarListaMatriculasRenovar.php';
        return \funcionesRegistrales_retornarListaMatriculasRenovar::retornarListaMatriculasRenovarNuevo($mysqli, $mat, $ide, $procesartodas, $usuario);
    }

    /**
     *
     * @param type $mysqli
     * @param type $criterio
     * @param type $pagina
     * @param type $fecini
     * @param type $caj
     * @param type $dig
     * @param type $reg
     * @return type
     */
    public static function retornarParaFirma($mysqli = null, $criterio = '1', $pagina = 0, $fecini = '', $caj = '', $dig = '', $reg = '')
    {
        $origen = 'SII';
        $retorno = array();
        $i = -1;

        // Recupera inscripciones por código de barras (REGMER / REGESADL)
        if ($criterio == '1') {
            $inx = '';
            if (!is_array($caj)) {
                if ($caj != '' && $caj != 'ZZZ') {
                    $inx = "'" . $caj . "'";
                }
            }
            if (is_array($caj)) {
                foreach ($caj as $c) {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx .= "'" . $c . "'";
                }
            }
            if (!is_array($dig)) {
                if ($dig != '' && $dig != 'ZZZ') {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx .= "'" . $dig . "'";
                }
            }
            if (is_array($dig)) {
                foreach ($dig as $c) {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx .= "'" . $c . "'";
                }
            }
            if (!is_array($reg)) {
                if ($reg != '' && $reg != 'ZZZ') {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx .= "'" . $reg . "'";
                }
            }
            if (is_array($reg)) {
                foreach ($reg as $c) {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx .= "'" . $c . "'";
                }
            }
            //
            if ($inx != '') {
                $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "fecharadicacion>='" . $fecini . "' and actoreparto NOT IN ('09','33','53') and estadofinal IN ('34','35') and operadorfinal IN (" . $inx . ")", "codigobarras");
            } else {
                $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "fecharadicacion>='" . $fecini . "' and actoreparto NOT IN ('09','33','53') and estadofinal IN ('34','35')", "codigobarras");
            }
            foreach ($arrTems as $t) {
                $arrTems0 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras_libros', "codigobarras='" . $t["codigobarras"] . "'", "libro,registro");
                if ($arrTems0 && !empty($arrTems0)) {
                    foreach ($arrTems0 as $tx0) {
                        $arrTems1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $tx0["libro"] . "' and registro='" . ltrim(trim($tx0["registro"]), "0") . "'", "libro,registro,dupli");
                        if ($arrTems1 && !empty($arrTems1)) {
                            foreach ($arrTems1 as $reg) {
                                $i++;
                                $retorno[$i]["codbarras"] = $t["codigobarras"];
                                $retorno[$i]["idlibro"] = $reg["libro"];
                                $retorno[$i]["registro"] = $reg["registro"];
                                $retorno[$i]["dupli"] = $reg["dupli"];
                                $retorno[$i]["actorep"] = $t["actoreparto"];
                                $retorno[$i]["estado"] = $t["estadofinal"];
                                $retorno[$i]["abg"] = $t["operadorfinal"];
                                $retorno[$i]["fecharegistro"] = $reg["fecharegistro"];
                                $retorno[$i]["certif"] = $reg["acto"];
                                $retorno[$i]["matricula"] = $reg["matricula"];
                                $retorno[$i]["organizacion"] = $reg["organizacion"];
                                $retorno[$i]["categoria"] = $reg["categoria"];
                                $retorno[$i]["idclase"] = $reg["tipoidentificacion"];
                                $retorno[$i]["numid"] = $reg["identificacion"];
                                $retorno[$i]["nombre"] = $reg["nombre"];
                                $retorno[$i]["noticia"] = $reg["noticia"];
                                $retorno[$i]["txtlibrovii"] = $reg["descripcionlibro"];
                                $retorno[$i]["codlibro"] = $reg["codigolibro"];
                                $retorno[$i]["numhojas"] = $reg["numeropaginas"];
                                $retorno[$i]["txtnoticia"] = $reg["noticia"];
                                $retorno[$i]["firma"] = $reg["firma"];
                                $retorno[$i]["clavefirmado"] = $reg["clavefirmado"];
                                $retorno[$i]["usuariofirma"] = $reg["usuariofirma"];
                            }
                        }
                    }
                }
            }
        }

        // Recupera inscripciones por inscripción (REGMER / REGESADL)
        if ($criterio == '1X') {
            $inx = '';
            if (!is_array($caj)) {
                if ($caj != '' && $caj != 'ZZZ') {
                    $inx = "'" . $caj . "'";
                }
            }
            if (is_array($caj)) {
                foreach ($caj as $c) {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx = "'" . $c . "'";
                }
            }
            if (!is_array($dig)) {
                if ($dig != '' && $dig != 'ZZZ') {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx = "'" . $dig . "'";
                }
            }
            if (is_array($dig)) {
                foreach ($dig as $c) {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx = "'" . $c . "'";
                }
            }
            if (!is_array($reg)) {
                if ($reg != '' && $reg != 'ZZZ') {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx = "'" . $reg . "'";
                }
            }
            if (is_array($reg)) {
                foreach ($reg as $c) {
                    if (trim($inx) != '') {
                        $inx .= ',';
                    }
                    $inx = "'" . $c . "'";
                }
            }

            //
            if ($inx != '') {
                $arrTems1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fecharegistro >= '" . $fecini . "' and LENGTH(firma) = 0 and operador IN (" . $inx . ")", "fecharegistro", $pagina - 1 * 200, 200);
            } else {
                $arrTems1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fecharegistro >= '" . $fecini . "' and LENGTH(firma) = 0", "fecharegistro", ($pagina - 1) * 200, 200);
            }
            echo 'Cantidad: ' . count($arrTems1) . '<br>';

            //
            if ($arrTems1 && !empty($arrTems1)) {
                foreach ($arrTems1 as $reg) {
                    $i++;
                    if ($reg["idradicacion"] != 0) {
                        $t = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $reg["idradicacion"] . "'");
                    } else {
                        $t = array(
                            'actoreparto' => '',
                            'estadofinal' => '',
                            'operadorfinal' => ''
                        );
                    }
                    $retorno[$i]["codbarras"] = $reg["idradicacion"];
                    $retorno[$i]["idlibro"] = $reg["libro"];
                    $retorno[$i]["registro"] = $reg["registro"];
                    $retorno[$i]["dupli"] = $reg["dupli"];
                    $retorno[$i]["actorep"] = $t["actoreparto"];
                    $retorno[$i]["estado"] = $t["estadofinal"];
                    $retorno[$i]["abg"] = '';
                    if (trim($reg["operador"]) != '') {
                        $retorno[$i]["abg"] = $reg["operador"];
                    }
                    if (trim($reg["usuarioinscribe"]) != '') {
                        if ($retorno[$i]["abg"] != '') {
                            $retorno[$i]["abg"] .= '/';
                        }
                        $retorno[$i]["abg"] .= $reg["usuarioinscribe"];
                    }
                    $retorno[$i]["fecharegistro"] = $reg["fecharegistro"];
                    $retorno[$i]["certif"] = $reg["acto"];
                    $retorno[$i]["matricula"] = $reg["matricula"];
                    $retorno[$i]["organizacion"] = $reg["organizacion"];
                    $retorno[$i]["categoria"] = $reg["categoria"];
                    $retorno[$i]["idclase"] = $reg["tipoidentificacion"];
                    $retorno[$i]["numid"] = $reg["identificacion"];
                    $retorno[$i]["nombre"] = $reg["nombre"];
                    $retorno[$i]["noticia"] = $reg["noticia"];
                    $retorno[$i]["txtlibrovii"] = $reg["descripcionlibro"];
                    $retorno[$i]["codlibro"] = $reg["codigolibro"];
                    $retorno[$i]["numhojas"] = $reg["numeropaginas"];
                    $retorno[$i]["txtnoticia"] = $reg["noticia"];
                    $retorno[$i]["firma"] = $reg["firma"];
                }
            }
        }


        // Recupera inscripciones por código de barras (REGPRO)
        if ($criterio == '2') {
            $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "fecharadicacion>='" . $fecini . "' and actoreparto IN ('09','33','53') and estadofinal IN ('34')", "codigobarras");
            foreach ($arrTems as $t) {
                $arrTems1 = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "idradicacion=" . $t["codigobarras"], "libro,registro");
                if ($arrTems1 && !empty($arrTems1)) {
                    foreach ($arrTems1 as $reg) {
                        $i++;
                        $retorno[$i]["codbarras"] = $t["codigobarras"];
                        $retorno[$i]["idlibro"] = $reg["libro"];
                        $retorno[$i]["registro"] = $reg["registro"];
                        $retorno[$i]["dupli"] = $reg["dupli"];
                        $retorno[$i]["actorep"] = $t["actoreparto"];
                        $retorno[$i]["estado"] = $t["estadofinal"];
                        $retorno[$i]["abg"] = $t["operadorfinal"];
                        $retorno[$i]["fecharegistro"] = $reg["fecharegistro"];
                        $retorno[$i]["certif"] = $reg["acto"];
                        $retorno[$i]["proponente"] = $reg["proponente"];
                        $retorno[$i]["organizacion"] = '';
                        $retorno[$i]["categoria"] = '';
                        $retorno[$i]["idclase"] = $reg["tipoidentificacion"];
                        $retorno[$i]["numid"] = $reg["identificacion"];
                        $retorno[$i]["nombre"] = $reg["nombre"];
                        $retorno[$i]["noticia"] = '';
                        $retorno[$i]["txtlibrovii"] = '';
                        $retorno[$i]["codlibro"] = '';
                        $retorno[$i]["numhojas"] = '';
                        $retorno[$i]["txtnoticia"] = '';
                        $retorno[$i]["firma"] = $reg["firma"];
                        $retorno[$i]["noticia"] = retornarNombreActosProponentesMysqliApi($mysqli, $reg["acto"]);
                        $retorno[$i]["txtnoticia"] = $retorno[$i]["noticia"];
                        $retorno[$i]["firma"] = $reg["firma"];
                        $retorno[$i]["clavefirmado"] = $reg["clavefirmado"];
                        $retorno[$i]["usuariofirma"] = $reg["usuariofirma"];
                    }
                }
            }
        }

        // Recupera inscripciones por libro (sin firma)
        // Se usa cuando no se busca por código de barras
        if ($criterio == '3') {
            $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "fechararegistro>='" . $fecini . "' and libro between ('RE01' and 'RM99') and trim(firma) = ''", "libro,registro");
            foreach ($arrTems as $reg) {
                $i++;
                $retorno[$i]["codbarras"] = $reg["idradicacion"];
                $retorno[$i]["idlibro"] = $reg["libro"];
                $retorno[$i]["registro"] = $reg["dupli"];
                $retorno[$i]["dupli"] = $reg["registro"];
                $retorno[$i]["actorep"] = '';
                $retorno[$i]["estado"] = '';
                $retorno[$i]["abg"] = $reg["operador"];
                $retorno[$i]["fecha"] = $reg["fecharegistro"];
                $retorno[$i]["certif"] = $reg["acto"];
                $retorno[$i]["matricula"] = $reg["matricula"];
                $retorno[$i]["organizacion"] = $reg["organizacion"];
                $retorno[$i]["categoria"] = $reg["categoria"];
                $retorno[$i]["idclase"] = $reg["tipoidentificacion"];
                $retorno[$i]["numid"] = $reg["identificacion"];
                $retorno[$i]["nombre"] = $reg["nombre"];
                $retorno[$i]["noticia"] = $reg["noticia"];
                $retorno[$i]["txtlibrovii"] = $reg["descripcionlibro"];
                $retorno[$i]["codlibro"] = $reg["codigolibro"];
                $retorno[$i]["numhojas"] = $reg["numeropaginas"];
                $retorno[$i]["txtnoticia"] = $reg["noticia"];
                $retorno[$i]["firma"] = $reg["firma"];
            }
        }

        // Recupera inscripciones por libro (sin firma) - REGPRO
        // Se usa cuando no se busca por código de barras
        if ($criterio == '4') {
            $arrTems = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "fechararegistro>='" . $fecini . "' and trim(firma) = ''", "libro,registro");
            foreach ($arrTems as $reg) {
                $i++;
                $retorno[$i]["codbarras"] = 0;
                $retorno[$i]["idlibro"] = $reg["libro"];
                $retorno[$i]["registro"] = $reg["dupli"];
                $retorno[$i]["dupli"] = $reg["registro"];
                $retorno[$i]["actorep"] = '';
                $retorno[$i]["estado"] = '';
                $retorno[$i]["abg"] = $reg["operador"];
                $retorno[$i]["fecha"] = $reg["fecharegistro"];
                $retorno[$i]["certif"] = $reg["acto"];
                $retorno[$i]["proponente"] = $reg["proponente"];
                $retorno[$i]["organizacion"] = '';
                $retorno[$i]["categoria"] = '';
                $retorno[$i]["idclase"] = $reg["tipoidentificacion"];
                $retorno[$i]["numid"] = $reg["identificacion"];
                $retorno[$i]["nombre"] = $reg["nombre"];
                $retorno[$i]["noticia"] = '';
                $retorno[$i]["txtlibrovii"] = '';
                $retorno[$i]["codlibro"] = '';
                $retorno[$i]["numhojas"] = '';
                $retorno[$i]["txtnoticia"] = '';
                $retorno[$i]["firma"] = $reg["firma"];
                $retorno[$i]["noticia"] = retornarNombreActosProponentesMysqliApi($mysqli, $reg["acto"]);
                $retorno[$i]["txtnoticia"] = $retorno[$i]["noticia"];
            }
        }

        $_SESSION["generales"]["cantidad"] = $i + 1;
        return $retorno;
    }

    public static function retornarParaFirmaInscripcion($mysqli, $criterio, $libro, $registro, $dupli)
    {
        $origen = 'SII';
        $retorno = array();
        if ($criterio == '5') {
            $reg1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones', "libro='" . $libro . "' and registro='" . $registro . "' and dupli='" . $dupli . "'");
        }
        if ($criterio == '6') {
            $reg1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscripciones_proponentes', "libro='" . $libro . "' and registro='" . $registro . "'");
        }

        if ($reg1 && !empty($reg1)) {
            $retorno["codigoError"] = '0000';
            $retorno["msgError"] = '';
            $retorno["libro"] = $reg1["libro"];
            $retorno["registro"] = $reg1["registro"];
            $retorno["dupli"] = '';
            if (isset($retorno["dupli"])) {
                $retorno["dupli"] = $reg1["dupli"];
            }
            $retorno["tipdoc"] = $reg1["tipodocumento"];
            $retorno["numdoc"] = $reg1["numerodocumento"];
            if (isset($reg1["idcodlibro"])) {
                $retorno["idlibrovii"] = $reg1["idcodlibro"];
            } else {
                $retorno["idlibrovii"] = '';
            }
            if (isset($reg1["idcodlibro"])) {
                $retorno["codlibrovii"] = $reg1["codigolibro"];
            } else {
                $retorno["codlibrovii"] = '';
            }
            $retorno["idorigendoc"] = $reg1["idorigendocumento"];
            $retorno["txtorigen"] = $reg1["origendocumento"];
            $retorno["idmunipdoc"] = $reg1["municipiodocumento"];
            $retorno["idpais"] = $reg1["paisdocumento"];
            $retorno["fecdoc"] = $reg1["fechadocumento"];
            $retorno["numhojas"] = '';
            $retorno["matricula"] = '';
            $retorno["proponente"] = '';
            if ($criterio == '5') {
                $retorno["numhojas"] = $reg1["numeropaginas"];
                $retorno["matricula"] = $reg1["matricula"];
            }
            if ($criterio == '6') {
                $retorno["proponente"] = '';
            }
            $retorno["fecreg"] = $reg1["fecharegistro"];
            $retorno["anoreg"] = substr($reg1["fecharegistro"], 0, 4);
            $retorno["horreg"] = $reg1["horaregistro"];
            $retorno["idsucur"] = '';
            $retorno["idclase"] = $reg1["tipoidentificacion"];
            $retorno["numid"] = $reg1["identificacion"];
            $retorno["nombre"] = $reg1["nombre"];
            $retorno["idcertif"] = $reg1["acto"];
            if ($criterio == '5') {
                $retorno["txtcertif"] = retornarNombreActosRegistroMysqliApi($mysqli, $reg1["libro"], $reg1["acto"]);
            }
            if ($criterio == '6') {
                $retorno["txtcertif"] = retornarNombreActosProponentesMysqliApi($mysqli, $reg1["acto"]);
            }
            $retorno["idope"] = $reg1["operador"];
            $retorno["idopera"] = $reg1["numerooperacion"];
            $retorno["ctrrevoca"] = $reg1["ctrrevoca"];
            $retorno["regrevoca"] = $reg1["registrorevocacion"];
            $retorno["noticia"] = '';
            if ($criterio == '5') {
                $retorno["noticia"] = $reg1["noticia"];
            }
            if ($criterio == '6') {
                $retorno["noticia"] = $reg1["texto"];
            }
            $retorno["firma"] = $reg1["firma"];
            $retorno["clavefirmado"] = $reg1["clavefirmado"];
            $retorno["codbarras"] = $reg1["idradicacion"];
            $retorno["recibo"] = $reg1["recibo"];
            $retorno["fecradica"] = $reg1["fecharadicacion"];
            $retorno["xml"] = '';
            $retorno["estado"] = '';
            if ($criterio == '6') {
                $retorno["xml"] = $reg1["xml"];
                $retorno["estado"] = $reg1["estado"];
            }
        } else {
            return false;
        }
        unset($reg1);
        $_SESSION["generales"]["cantidad"] = 1;
        return $retorno;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $cri
     * @param type $recibo
     * @return type
     */
    public static function retornarRadicadosNoNotificados($mysqli, $cri, $recibo = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarRadicadosNoNotificados.php';
        return \funcionesRegistrales_retornarRadicadosNoNotificados::retornarRadicadosNoNotificados($mysqli, $cri, $recibo);
    }

    /**
     *
     * @param type $dbx
     * @param type $numliq
     * @param type $tipo
     * @return type
     */
    public static function retornarMregLiquidacion($dbx, $numliq, $tipo = 'L')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarMregLiquidacion.php';
        return \funcionesRegistrales_retornarMregLiquidacion::retornarMregLiquidacion($dbx, $numliq, $tipo);
    }

    /**
     *
     * @param type $dbx
     * @param type $numrec
     * @return type
     */
    public static function retornarMregRecibo($dbx, $numrec)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarMregRecibo.php';
        return \funcionesRegistrales_retornarMregRecibo::retornarMregRecibo($dbx, $numrec);
    }

    public static function retornarMregSecuencia($dbx, $clave)
    {
        $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_secuencias', "id='" . $clave . "'");
        if ($arrTem === false || empty($arrTem)) {
            return 0;
        } else {
            return $arrTem["secuencia"];
        }
    }

    public static function retornarTipoRegistro($dbx, $tra)
    {
        $arr = retornarRegistroMysqliApi($dbx, 'bas_tipotramites', "id='" . $tra . "'");
        if ($arr === false || empty($arr)) {
            return '';
        } else {
            return $arr["tiporegistro"];
        }
    }

    public static function retornarMregSecuenciaMaxAsentarRecibo($dbx, $clave)
    {

        //
        if ($clave == 'RECIBOS-NORMALES') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'S'";
        }
        if ($clave == 'RECIBOS-NOTAS') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'M'";
        }
        if ($clave == 'RECIBOS-GA') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'H'";
        }
        if ($clave == 'RECIBOS-CO') {
            $query = "select max(recibo) as a from mreg_recibosgenerados where substring(recibo,1,1) = 'D'";
        }

        //
        $temx = ejecutarQueryMysqliApi($dbx, $query);
        $siguiente = ltrim(substr($temx[1]["a"], 1), "0");

        //
        return $siguiente;
    }

    /**
     *
     * @param type $mysqli
     * @param type $recibo
     * @param type $codbarras
     * @param type $emailsentrada
     * @param type $celularesentrada
     * @param type $nameLog
     * @param type $idliquidacion
     * @return type
     */
    public static function rutinaNotificarRadicacion($mysqli, $recibo = '', $codbarras = '', $emailsentrada = array(), $celularesentrada = array(), $nameLog = '', $idliquidacion = 0)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_rutinaNotificarRadicacion.php';
        return \funcionesRegistrales_rutinaNotificarRadicacion::rutinaNotificarRadicacion($mysqli, $recibo, $codbarras, $emailsentrada, $celularesentrada, $nameLog, $idliquidacion);
    }

    /**
     *
     * @param type $mysqli
     * @param type $recibo
     * @param type $codbarras
     * @param type $emailsentrada
     * @param type $celularesentrada
     * @param type $nameLog
     * @param type $idliquidacion
     * @return type
     */
    public static function rutinaNotificarAsentamiento($mysqli, $recibo = '', $codbarras = '', $emailsentrada = array(), $celularesentrada = array(), $nameLog = '', $idliquidacion = 0)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_rutinaNotificarRadicacion.php';
        return \funcionesRegistrales_rutinaNotificarRadicacion::rutinaNotificarAsentamiento($mysqli, $recibo, $codbarras, $emailsentrada, $celularesentrada, $nameLog, $idliquidacion);
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbarras
     * @param type $nameLog
     * @return type
     */
    public static function rutinaInformarArchivoTramite($mysqli, $codbarras = '', $nameLog = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_rutinaNotificarRadicacion.php';
        return \funcionesRegistrales_rutinaNotificarRadicacion::rutinaInformarArchivoTramite($mysqli, $codbarras, $nameLog);
    }

    /**
     *
     * @param type $mysqli
     * @param type $transaccion
     * @param type $tipoliquidacion
     * @param type $expediente
     * @return type
     */
    public static function rutinaLiquidacionTransacciones($mysqli, $transaccion = '', $tipoliquidacion = '', $expediente = false)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_rutinaLiquidacionTransacciones.php';
        return \funcionesRegistrales_rutinaLiquidacionTransacciones::rutinaLiquidacionTransacciones($mysqli, $transaccion, $tipoliquidacion, $expediente);
    }

    public static function retornarListaSoportesCaja($dbx, $tramite)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php';

        //
        $res = retornarRegistrosMysqliApi($dbx, 'mreg_soportescaja', "tipotramite='" . $tramite . "'", "secuencia");
        $retornar = array();
        $i = 0;
        if ($res === false) {
            return $retornar;
        } else {
            foreach ($res as $rs1) {
                $i++;
                $retornar[$i]["tipotramite"] = $rs1["tipotramite"];
                $retornar[$i]["secuencia"] = $rs1["secuencia"];
                $retornar[$i]["contenido"] = $rs1["contenido"];
                $retornar[$i]["tooltip"] = $rs1["tooltip"];
                $retornar[$i]["tipo"] = $rs1["tipo"];
                $retornar[$i]["obligatoriedad"] = $rs1["obligatoriedad"];
                $retornar[$i]["respuestavalida"] = $rs1["respuestavalida"];
            }
            unset($res);
            return $retornar;
        }
    }

    public static function retornarListaSoportesTramite($mysqli, $tipotramite, $grupodatos, $idliquidacion, $idsecuencia, $matricula)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php';
        //
        $arrSoportes = array();
        $iSop = 0;

        //
        $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_soportesproponentes_1510', "tipotramitegenerico='" . $tipotramite . "' and grupodatos='" . $grupodatos . "'", "orden");

        if (!empty($arrTem)) {

            foreach ($arrTem as $t) {
                $iSop++;
                $arrSoportes[$iSop] = $t;
                $arrSoportes[$iSop]["archivo"] = '';
                $arrSoportes[$iSop]["formulario"] = 'NO';
                $arrSoportes[$iSop]["anexos"] = array();
                $arrIdx = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliquidacion . " and sectransaccion='" . sprintf("%03s", $idsecuencia) . "' and identificador='" . $t["identificador"] . "'", "secuenciaanexo");
                $iAnx = 0;
                if (!empty($arrIdx)) {
                    foreach ($arrIdx as $ix) {
                        $iAnx++;
                        $arrSoportes[$iSop]["anexos"][$iAnx] = $ix;
                    }
                }

                if ($t["tipoformulario"] != '' && $t["tipoformulario"] != 'NO') {
                    if (
                        $matricula != '' &&
                        $matricula != 'NUEVANAT' &&
                        $matricula != 'NUEVAEST' &&
                        $matricula != 'NUEVAJUR' &&
                        $matricula != 'NUEVAAGE' &&
                        $matricula != 'NUEVASUC' &&
                        $matricula != 'NUEVAESA'
                    ) {
                        $arrTem1 = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $idliquidacion . " and secuencia='" . sprintf("%03s", $idsecuencia) . "' and expediente='" . $matricula . "'");
                    } else {
                        $arrTem1 = retornarRegistroMysqliApi($mysqli, 'mreg_liquidaciondatos', "idliquidacion=" . $idliquidacion . " and secuencia='" . sprintf("%03s", $idsecuencia) . "'");
                    }
                    if ($arrTem1 && !empty($arrTem1)) {
                        $arrSoportes[$iSop]["formulario"] = 'SI';
                    } else {
                        $arrSoportes[$iSop]["formulario"] = 'NO';
                    }
                }
            }
        }

        return $arrSoportes;
    }

    /**
     *
     * @param type $mysqli
     * @param type $criterio
     * @param type $lib
     * @param type $numreg
     * @param type $dup
     * @param type $fini
     * @param type $mat
     * @param type $pro
     * @param type $tide
     * @param type $numid
     * @return type
     */
    public static function retornarNoticiaMercantil($mysqli, $criterio = '1', $lib = '', $numreg = '', $dup = '', $fini = '', $mat = '', $pro = '', $tide = '', $numid = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_retornarNoticiaMercantil.php';
        return \funcionesRegistrales_retornarNoticiaMercantil::retornarNoticiaMercantil($mysqli, $criterio, $lib, $numreg, $dup, $fini, $mat, $pro, $tide, $numid);
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbarras
     * @param type $idliquidacion
     * @param type $nameLog
     * @param type $libro
     * @param type $inscripcion
     * @param type $tipo
     * @param type $forzar
     * @return type
     */
    public static function rutinaNotificarInscripciones($mysqli, $codbarras = '', $idliquidacion = 0, $nameLog = '', $libro = '', $inscripcion = '', $tipo = 'todos', $forzar = 'no')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_rutinaNotificarInscripciones.php';
        return \funcionesRegistrales_rutinaNotificarInscripciones::rutinaNotificarInscripciones($mysqli, $codbarras, $idliquidacion, $nameLog, $libro, $inscripcion, $tipo, $forzar);
    }

    /**
     *
     * @param type $mysqli
     * @param type $proponente
     * @param type $libro
     * @param type $registro
     * @return type
     */
    public static function rutinaNotificarInscripcionesProponentes($mysqli, $proponente, $libro, $registro)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_rutinaNotificarInscripcionesProponentes.php';
        return \funcionesRegistrales_rutinaNotificarInscripcionesProponentes::rutinaNotificarInscripcionesProponentes($mysqli, $proponente, $libro, $registro);
    }

    /**
     *
     * @param type $mysqli
     * @param type $r
     * @param type $inscs
     * @param type $arrActos
     * @return type
     */
    public static function ajustarRazonSocial($mysqli, $r, $inscs, $arrActos)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_ajustarRazonSocial.php';
        return \funcionesRegistrales_ajustarRazonSocial::ajustarRazonSocial($mysqli, $r, $inscs, $arrActos);
    }

    /**
     *
     * @param type $mysqli
     * @param type $codbarras
     * @return type
     */
    public static function emailsCeluaresAsociadosRadicacion($mysqli, $codbarras)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_emailsCeluaresAsociadosRadicacion.php';
        return \funcionesRegistrales_emailsCeluaresAsociadosRadicacion::emailsCeluaresAsociadosRadicacion($mysqli, $codbarras);
    }

    public static function serializacionLineal($arr)
    {
        $sal = '';
        foreach ($arr as $key => $datos) {
            if (!is_array($datos)) {
                $sal .= '<' . $key . '><![CDATA[' . $datos . ']]></' . $key . '>';
            } else {
                $sal .= '<' . $key . '>';
                foreach ($datos as $key1 => $datos1) {
                    if (!is_array($datos1)) {
                        $sal .= '<' . $key1 . '><![CDATA[' . $datos1 . ']]></' . $key1 . '>';
                    } else {
                        $sal .= '<' . $key1 . '>';
                        foreach ($datos1 as $key2 => $datos2) {
                            if (!is_array($datos2)) {
                                $sal .= '<' . $key2 . '><![CDATA[' . $datos2 . ']]></' . $key2 . '>';
                            } else {
                                $sal .= '<' . $key2 . '>';
                                foreach ($datos2 as $key3 => $datos3) {
                                    $sal .= '<' . $key3 . '><![CDATA[' . $datos3 . ']]></' . $key3 . '>';
                                }
                                $sal .= '</' . $key2 . '>';
                            }
                        }
                        $sal .= '</' . $key1 . '>';
                    }
                }
                $sal .= '</' . $key . '>';
            }
        }
        return $sal;
    }

    /**
     *
     * @param type $dbx
     * @param type $numrec
     * @param type $datos
     * @param type $reemplazar
     * @param type $extendido
     * @return type
     */
    public static function serializarExpedienteMatricula($dbx, $numrec = '', $datos = array(), $reemplazar = 'si', $extendido = 'si')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_serializarExpedienteMatricula.php';
        return \funcionesRegistrales_serializarExpedienteMatricula::serializarExpedienteMatricula($dbx, $numrec, $datos, $reemplazar, $extendido);
    }

    /**
     *
     * @param type $dbx
     * @param type $data
     * @return type
     */
    public static function serializarExpedienteProponente($dbx = null, $data = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_serializarExpedienteProponente.php';
        return \funcionesRegistrales_serializarExpedienteProponente::serializarExpedienteProponente($dbx, $data);
    }

    /**
     *
     * @param type $dbx
     * @param type $tiposerializacion
     * @param type $gruposmodificados
     * @return type
     */
    public static function serializarExpedienteProponenteEnviarSirep($dbx, $tiposerializacion = '', $gruposmodificados = array())
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_serializarExpedienteProponenteEnviarSirep.php';
        return \funcionesRegistrales_serializarExpedienteProponenteEnviarSirep::serializarExpedienteProponenteEnviarSirep($dbx, $tiposerializacion, $gruposmodificados);
    }

    public static function sumarCargaDiaria($mysqli, $est, $us, $tt)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';

        $condicion = "fecha='" . date("Ymd") . "' and idestado='" . $est . "' and idusuario='" . $us . "' and tiporuta='" . $tt . "'";
        $arr = retornarRegistroMysqliApi($mysqli, 'mreg_carga_diaria', $condicion);
        if (($arr === false) || (empty($arr))) {
            $arrCampos = array(
                'fecha',
                'idestado',
                'idusuario',
                'tiporuta',
                'cantidad'
            );
            $arrValores = array(
                "'" . date("Ymd") . "'",
                "'" . $est . "'",
                "'" . $us . "'",
                "'" . $tt . "'",
                1
            );
            insertarRegistrosMysqliApi($mysqli, 'mreg_carga_diaria', $arrCampos, $arrValores);
        } else {
            $arrCampos = array(
                'fecha',
                'idestado',
                'idusuario',
                'tiporuta',
                'cantidad'
            );
            $arrValores = array(
                "'" . date("Ymd") . "'",
                "'" . $est . "'",
                "'" . $us . "'",
                "'" . $tt . "'",
                $arr["cantidad"] + 1
            );
            regrabarRegistrosMysqliApi($mysqli, 'mreg_carga_diaria', $arrCampos, $arrValores, $condicion);
        }
    }

    /**
     *
     * @param type $mysqli
     * @param type $key
     * @param type $serv
     * @param type $tran
     * @param type $servs
     * @param type $temServ
     * @param type $indice
     * @param type $cant
     * @param type $fcorte
     * @param type $liqactprop
     * @param type $liqtotestnal
     * @return int
     */
    public static function sumarServicio($mysqli, $key, $serv, $tran, $servs = array(), $temServ = array(), $indice = '', $cant = '', $fcorte = '', $liqactprop = 0, $liqtotestnal = 0)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $ok = 'no';

        // **************************************************************************** //
        // Asigna el expediente dependiendo del tipo de servicio que se esté liquidando //
        // Por defecto, asigna la matrícula afectada en la transacción
        // **************************************************************************** //
        $matricula = $tran["dattrans"]["matriculaafectada"];
        $nombre = $tran["dattrans"]["razonsocial"];
        $organizacion = $tran["dattrans"]["organizacion"];
        $categoria = $tran["dattrans"]["categoria"];

        if ($tran["maetrans"]["tipotramite"] == 'constitucionpjur') {
            $matricula = 'NUEVAJUR';
        }
        if ($tran["maetrans"]["tipotramite"] == 'constitucionesadl') {
            $matricula = 'NUEVAESA';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculapnat') {
            $matricula = 'NUEVANAT';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculaest') {
            $matricula = 'NUEVAEST';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculasuc') {
            $matricula = 'NUEVASUC';
        }
        if ($tran["maetrans"]["tipotramite"] == 'matriculaage') {
            $matricula = 'NUEVAAGE';
        }

        //
        // Si el tipo de transacción es una compraventa
        if ($tran["maetrans"]["idtipotransaccion"] == '022') {
            if ($serv == '01020101') { //
                $matricula = 'NUEVANAT';
                $nombre = $tran["dattrans"]["nombrecomprador"];
                $organizacion = $tran["dattrans"]["organizacioncomprador"];
                $categoria = '0';
            }
            if ($serv == '01031501' || $serv == '01031509') { //
                $matricula = $tran["dattrans"]["matriculavendedor"];
                $nombre = $tran["dattrans"]["nombrevendedor"];
                $organizacion = '01';
                $categoria = '0';
            }
        }

        //
        if (ltrim($cant, "0") == '') {
            if (ltrim($tran["dattrans"]["cantidad"], "0") != '') {
                $cant = $tran["dattrans"]["cantidad"];
            }
        }
        if (ltrim($cant, "0") == '') {
            $cant = 1;
        }

        //
        $arrSal = array(
            'idsec' => sprintf("%03s", $key),
            'idservicio' => $serv,
            'expediente' => $matricula,
            'nombre' => $nombre,
            'organizacion' => $organizacion,
            'categoria' => $categoria,
            'ano' => date("Y"),
            'cantidad' => $cant,
            'valorbase' => 0,
            'porcentaje' => 0,
            'valorservicio' => 0,
            'benart7' => 'N',
            'reliquidacion' => 'N',
            'serviciobase' => 'S',
            'pagoafiliacion' => 'N',
            'ir' => 'N',
            'iva' => 'N',
        );

        //
        $tipobase = '';
        switch ($indice) {
            case 1:
                $tipobase = $tran["maetrans"]["valorbase1"];
                break;
            case 2:
                $tipobase = $tran["maetrans"]["valorbase2"];
                break;
            case 3:
                $tipobase = $tran["maetrans"]["valorbase3"];
                break;
            case 4:
                $tipobase = $tran["maetrans"]["valorbase4"];
                break;
            case 5:
                $tipobase = $tran["maetrans"]["valorbase5"];
                break;
            case 6:
                $tipobase = $tran["maetrans"]["valorbase6"];
                break;
            case 7:
                $tipobase = $tran["maetrans"]["valorbase7"];
                break;
            case 8:
                $tipobase = $tran["maetrans"]["valorbase8"];
                break;
        }

        if ($tipobase == 'sinbase') {
            $arrSal["valorbase"] = 0;
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, 0, 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        if ($tipobase == 'activos') {
            $bas = $tran["dattrans"]["activos"];
            $arrSal["valorbase"] = $tran["dattrans"]["activos"];
            if ($tran["maetrans"]["idtipotransaccion"] == '022') { // Si se trata de una compraventa
                $arrSal["valorbase"] = $tran["dattrans"]["activoscomprador"];
                $bas = $tran["dattrans"]["activoscomprador"];
            }
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $bas, 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'valorcontrato') {
            $arrSal["valorbase"] = $tran["dattrans"]["costotransaccion"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["costotransaccion"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'valorcompraventa') {
            $arrSal["valorbase"] = $tran["dattrans"]["costotransaccion"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["costotransaccion"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'patrimonio') {
            $arrSal["valorbase"] = $tran["dattrans"]["patrimonio"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["patrimonio"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalsocial') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalsocial"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["capitalsocial"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalautorizado') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalautorizado"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["capitalautorizado"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalsuscrito') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalsuscrito"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["capitalsuscrito"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalpagado') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalpagado"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["capitalpagado"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'capitalasociativas') {
            $arrSal["valorbase"] = $tran["dattrans"]["aporteactivos"] + $tran["dattrans"]["aportedinero"] + $tran["dattrans"]["aportelaboral"] + $tran["dattrans"]["aportelaboraladicional"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $arrSal["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($tipobase == 'patrimonio' || $tipobase == 'capitalasignado') {
            $arrSal["valorbase"] = $tran["dattrans"]["capitalasignado"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $tran["dattrans"]["capitalasignado"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        if ($tipobase == 'impreg') {
            $arrSal["valorbase"] = $_SESSION["tramite"]["valorimpregistro"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $arrSal["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if ($ok == 'no') {
            $arrSal["valorbase"] = $tran["dattrans"]["costotransaccion"];
            $arrSal["valorservicio"] = \funcionesRegistrales::buscaTarifa($mysqli, $serv, date("Y"), $cant, $arrSal["valorbase"], 'tarifa', $liqactprop, $liqtotestnal);
            $ok = 'si';
        }

        //
        if (!isset($_SESSION["tramite"]["cobrarmutacion"])) {
            $_SESSION["tramite"]["cobrarmutacion"] = '';
        }
        if ($_SESSION["tramite"]["cobrarmutacion"] == 'N') {
            $arrSal["valorservicio"] = 0;
        }

        // ******************************************************************************************************* //
        // JINT: 2023 03 21
        // Ajustes para liquidar en cero los asociaciones campesinas y agremiaciones (ley 2219 de 2022)
        // Siempre y cuando estén renovadas
        // Tarifa 0
        // ******************************************************************************************************* //
        if ($tran["maetrans"]["tipotramite"] != 'renovacionmatricula' && $tran["maetrans"]["tipotramite"] != 'renovacionesadl') {
            if (substr($matricula, 0, 5) != 'NUEVA' && $matricula != '') {
                $expx = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $matricula . "'");
            } else {
                $expx = false;
            }

            if ($expx) {
                if ($expx["ctrclasegenesadl"] == '2') {
                    if ($expx["ctrclaseespeesadl"] == '73' || $expx["ctrclaseespeesadl"] == '74' || $expx["ctrclaseespeesadl"] == '75') {
                        if ($expx["ultanoren"] == date("Y")) {
                            $arrSal["valorservicio"] = 0;
                        } else {
                            $ano1 = date("Y") - 1;
                            if ($expx["ultanoren"] == $ano1) {
                                if (date("Ymd") <= $fcorte) {
                                    $arrSal["valorservicio"] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        //
        return $arrSal;
    }

    /**
     * 
     * @param type $mysqli
     * @param type $idsec
     * @param type $serv
     * @param type $expediente
     * @param type $nombre
     * @param type $ano
     * @param type $cantidad
     * @param type $valorbase
     * @param type $porcentaje
     * @param type $valorservicio
     * @param type $benart7
     * @param type $reliquidacion
     * @param type $serviciobase
     * @param type $pagoafiliacion
     * @param type $ir
     * @param type $iva
     * @param type $liqactprop
     * @param type $liqtotestnal
     * @param type $servicioorigen
     * @param type $diasmora
     * @return type
     */
    public static function sumarServicioGeneral($mysqli, $idsec = '', $serv = '', $expediente = '', $nombre = '', $ano = '', $cantidad = 0, $valorbase = 0, $porcentaje = 0, $valorservicio = 0, $benart7 = 'N', $reliquidacion = 'N', $serviciobase = 'N', $pagoafiliacion = 'N', $ir = 'N', $iva = 'N', $liqactprop = 0, $liqtotestnal = 0, $servicioorigen = '', $diasmora = 0)
    {
        $arrSal = array(
            'idsec' => sprintf("%03s", $idsec),
            'idservicio' => $serv,
            'expediente' => $expediente,
            'nombre' => $nombre,
            'ano' => $ano,
            'cantidad' => $cantidad,
            'valorbase' => $valorbase,
            'porcentaje' => $porcentaje,
            'valorservicio' => $valorservicio,
            'servicioorigen' => $servicioorigen,
            'diasmora' => $diasmora,
            'benart7' => $benart7,
            'reliquidacion' => $reliquidacion,
            'serviciobase' => $serviciobase,
            'pagoafiliacion' => $pagoafiliacion,
            'ir' => $ir,
            'iva' => $iva
        );
        return $arrSal;
    }

    public static function tieneLibros($dbx = null, $mat = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php';

        set_error_handler('myErrorHandler');

        $nameLog = 'tieneLibros_' . $mat;
        $genlog = 'no';

        if ($genlog == 'si') {
            \logApi::general2($nameLog, $mat, 'Inicia lectura expediente');
        }

        // ********************************************************************************** //
        // Instancia la BD si no existe
        // ********************************************************************************** //
        if ($dbx === null) {
            $mysqli = new \mysqli(DB_HOST, DB_USUARIO, DB_PASSWORD, DB_NAME, DB_PORT);
            if (mysqli_connect_error()) {
                $_SESSION["generales"]["mensajerror"] = 'Error coenctando con la BD';
                return false;
            }
        } else {
            $mysqli = $dbx;
        }

        // ********************************************************************************** //
        // carga maestro de actos
        // ********************************************************************************** //
        $_SESSION["maestroactos"] = array();
        $temx = retornarRegistrosMysqliApi($mysqli, "mreg_actos", "1=1", "idlibro,idacto");
        foreach ($temx as $x) {
            $ind = $x["idlibro"] . '-' . $x["idacto"];
            $_SESSION["maestroactos"][$ind] = $x;
        }
        unset($temx);

        // Armado del arreglo de respuesta
        $retorno = 'no';
        $arrX = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscripciones', "matricula='" . $mat . "'", "fecharegistro,libro,registro,dupli");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                $ind = $x["libro"] . '-' . $x["acto"];
                $eslib = 'no';
                if ($x["libro"] == 'RM07' || $x["libro"] == 'RE52') {
                    if (ltrim(trim($x["acto"]), "0") == '') {
                        $eslib = 'si';
                    } else {
                        if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '004' || $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '059') {
                            $eslib = 'si';
                        }
                    }
                }
                if ($x["libro"] == 'RM22') {
                    if (trim($x["acto"]) == '0003') {
                        if ($_SESSION["maestroactos"][$ind]["idgrupoacto"] == '004' || $_SESSION["maestroactos"][$ind]["idgrupoacto"] == '059') {
                            $eslib = 'si';
                        }
                    }
                }
                if ($eslib == 'si') {
                    $retorno = 'si';
                }
            }
        }

        //
        if ($genlog == 'si') {
            \logApi::general2($nameLog, $retorno["matricula"], 'Leyo inscripciones en libros');
        }

        //
        if ($dbx == null) {
            $mysqli->close();
        }

        //
        return $retorno;
    }

    public static function validarEstadoMatriculasRenovar($txt)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        if ($txt != '') {
            $lista = explode(',', $txt);
            $canceladas = 0;
            $aldia = 0;
            $in = '';
            $inx = '';
            foreach ($lista as $l) {
                $lx = explode('-', $l);
                if (isset($lx[1])) {
                    $ly = ltrim($lx[1], "0");
                } else {
                    $ly = ltrim($l, "0");
                }
                if ($in != '') {
                    $in .= ',';
                }
                $in .= "'" . $ly . "'";
                $inx = $ly . ' ';
            }
            $arrTem = retornarRegistrosMysqliApi(null, 'mreg_est_inscritos', "matricula IN (" . $in . ")", "matricula");
            foreach ($arrTem as $t) {
                if ($t["ctrestmatricula"] != 'MA' && $t["ctrestmatricula"] != 'IA') {
                    $canceladas++;
                }
                if ($t["ultanoren"] == date("Y")) {
                    $aldia++;
                }
            }
            unset($arrTem);

            if ($canceladas == 0 && $aldia == 0) {
                return true;
            }
            if ($canceladas > 0) {
                $_SESSION["generales"]["mensajeerror"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) cancelada(s) en fecha posterior a la elaboraci&oacute;n de la liquidación (' . $txt . ')';
                return false;
            }
            if ($aldia > 0) {
                $_SESSION["generales"]["mensajeerror"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) renovadas en fecha posterior a la elaboraci&oacute;n de la liquidación (' . $txt . ')';
                return false;
            }
        } else {
            return true;
        }
    }

    public static function consultarSaldoAfiliadoMysqliApi($mysqli = null, $matricula = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';

        $salida = array(
            'valorultpagoafi' => 0,
            'fechaultpagoafi' => '',
            'pago' => 0,
            'cupo' => 0
        );

        if ($matricula == '') {
            return $salida;
        }
        //
        $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

        //
        $formaCalculoAfiliacion = retornarClaveValorMysqliApi($mysqli, '90.01.60');

        $salida = array(
            'valorultpagoafi' => 0,
            'fechaultpagoafi' => '',
            'pago' => 0,
            'cupo' => 0
        );

        //
        $arrSerAfil = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "grupoventas='02'", "idservicio");
        $Servicios = "";
        $ServiciosAfiliacion = array();
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
            $ServiciosAfiliacion[] = $ServAfil["idservicio"];
        }

        //
        $arrFecValAfi = retornarRegistroMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $matricula . "' or expedienteafectado='" . $matricula . "') and servicio in (" . $Servicios . ") and (ctranulacion = '0') and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') and (tipogasto <> '7') order by fecoperacion desc limit 1");
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
        unset($arrFecValAfi);

        //
        $fecpagoafiliacion = '';
        $detalle = array();
        $iDetalle = 0;
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            $anox = date("Y") - 1;
            $feciniafi = (date("Y") - 1) . '0101';
        } else {
            $anox = date("Y");
            $feciniafi = date("Y") . '0101';
        }
        $inix = retornarRegistroMysqliApi($mysqli, 'mreg_saldos_afiliados_sirp', "ano='" . $anox . "' and matricula='" . $matricula . "'");
        if ($inix && !empty($inix)) {
            $iDetalle++;
            $detalle[$iDetalle] = array(
                'tipo' => 'SaldoInicial-SIRP',
                'fecha' => $anox,
                'recibo' => '',
                'valor' => $inix["cupocargado"],
                'cupo' => $inix["cupocargado"] - $inix["cupoconsumido"]
            );
            $salida["cupo"] = $inix["cupocargado"] - $inix["cupoconsumido"];
        }

        //
        $arrRecs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $matricula . "' or expedienteafectado='" . $matricula . "') and (ctranulacion = '0') and (left(numerorecibo,1) IN ('H','G','R','S')) and (fecoperacion >= '" . $feciniafi . "') and (tipogasto <> '7')", "fecoperacion");
        if ($arrRecs && !empty($arrRecs)) {
            foreach ($arrRecs as $rx) {
                if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    if (substr($rx["fecoperacion"], 0, 4) != substr($feciniafi, 0, 4)) {
                        $iDetalle = 0;
                        $detalle = array();
                        $salida["cupo"] = 0;
                        $salida["pago"] = 0;
                        $fecpagoafiliacion = '';
                    } else {
                        if ($fecpagoafiliacion == '') {
                            $fecpagoafiliacion = $rx["fecoperacion"];
                        }
                    }

                    //
                    $iDetalle++;
                    $detalle[$iDetalle] = array(
                        'tipo' => 'PagoAfiliación',
                        'fecha' => $rx["fecoperacion"],
                        'recibo' => $rx["numerorecibo"],
                        'valor' => $rx["valor"],
                        'cupo' => 0
                    );
                    $salida["pago"] = $salida["pago"] + $rx["valor"];
                    if ($formaCalculoAfiliacion != '') {
                        if ($formaCalculoAfiliacion == 'RANGO_VAL_AFI') {
                            $arrRan = retornarRegistrosMysqliApi($mysqli, 'mreg_rangos_cupo_afiliacion', "ano='" . date("Y") . "'", "orden");
                            foreach ($arrRan as $rx1) {
                                if ($rx1["minimo"] <= $salida["pago"] && $rx1["maximo"] >= $salida["pago"]) {
                                    $salida["cupo"] = $rx1["cupo"];
                                }
                            }
                            unset($arrRan);
                            unset($rx1);
                        } else {
                            $salida["cupo"] = round(doubleval($formaCalculoAfiliacion) * $salida["pago"], 0);
                        }
                    }
                    $detalle[$iDetalle]["cupo"] = $salida["cupo"];
                }
            }

            //
            foreach ($arrRecs as $rx) {
                if (!in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    if ($salida["cupo"] > 0) {
                        if ($rx["tipogasto"] == '1') {
                            if ($rx["fecoperacion"] >= $fecpagoafiliacion) {
                                if ($salida["cupo"] - $rx["valor"] >= 0) {
                                    $salida["cupo"] = $salida["cupo"] - $rx["valor"];
                                } else {
                                    $salida["cupo"] = 0;
                                }
                                $iDetalle++;
                                $detalle[$iDetalle] = array(
                                    'tipo' => 'Consumo',
                                    'fecha' => $rx["fecoperacion"],
                                    'recibo' => $rx["numerorecibo"],
                                    'valor' => $rx["valor"],
                                    'cupo' => $salida["cupo"]
                                );
                            }
                        }
                    }
                }
            }
        }

        return $detalle;
    }

    public static function consultarSaldoAfiliadoCantidadMysqliApi($mysqli = null, $matricula = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';

        $detalle = array();
        if ($matricula == '') {
            return $detalle;
        }

        //
        $_SESSION["generales"]["corterenovacion"] = retornarRegistroMysqliApi($mysqli, 'mreg_cortes_renovacion', "ano='" . date("Y") . "'", "corte");
        $_SESSION["generales"]["corterenovacionmesdia"] = substr($_SESSION["generales"]["corterenovacion"], 4, 4);

        //
        $cupo = 0;

        //
        $arrSerAfil = retornarRegistrosMysqliApi($mysqli, "mreg_servicios", "grupoventas='02'", "idservicio");
        $Servicios = "";
        $ServiciosAfiliacion = array();
        foreach ($arrSerAfil as $ServAfil) {
            if ($Servicios != '') {
                $Servicios .= ",";
            }
            $Servicios .= "'" . $ServAfil["idservicio"] . "'";
            $ServiciosAfiliacion[] = $ServAfil["idservicio"];
        }

        //
        $arrFecValAfi = retornarRegistroMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $matricula . "' or expedienteafectado='" . $matricula . "') and servicio in (" . $Servicios . ") and ctranulacion = '0' and (substring(numerorecibo,1,1)='R' or substring(numerorecibo,1,1)='S') and (tipogasto <> '7') order by fecoperacion desc limit 1");
        $salida["valorultpagoafi"] = $arrFecValAfi["valor"];
        $salida["fechaultpagoafi"] = $arrFecValAfi["fecoperacion"];
        unset($arrFecValAfi);

        //
        $fecpagoafiliacion = '';
        $detalle = array();
        $iDetalle = 0;
        if (date("md") <= $_SESSION["generales"]["corterenovacionmesdia"]) {
            $anox = date("Y") - 1;
            $feciniafi = (date("Y") - 1) . '0101';
        } else {
            $anox = date("Y");
            $feciniafi = date("Y") . '0101';
        }

        //
        $arrRecs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_recibos', "(matricula='" . $matricula . "' or expedienteafectado='" . $matricula . "') and ctranulacion = '0' and left(numerorecibo,1) IN ('H','G','R','S') and (fecoperacion >= '" . $feciniafi . "') and (tipogasto <> '7')", "fecoperacion");
        if ($arrRecs && !empty($arrRecs)) {
            foreach ($arrRecs as $rx) {
                if (in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    // Si se cambia de año en el pago, se reinicia el histórico e pagos
                    if (substr($rx["fecoperacion"], 0, 4) != substr($feciniafi, 0, 4)) {
                        $iDetalle = 0;
                        $detalle = array();
                        $fecpagoafiliacion = '';
                    } else {
                        if ($fecpagoafiliacion == '') {
                            $fecpagoafiliacion = $rx["fecoperacion"];
                        }
                    }

                    //
                    $iDetalle++;
                    $detalle[$iDetalle] = array(
                        'tipo' => 'PagoAfiliación',
                        'fecha' => $rx["fecoperacion"],
                        'recibo' => $rx["numerorecibo"],
                        'valor' => $rx["valor"],
                        'cantidad' => CANTIDAD_CERTIFICADOS_CUPO_AFILIADO,
                        'cupo' => CANTIDAD_CERTIFICADOS_CUPO_AFILIADO
                    );
                    $cupo = CANTIDAD_CERTIFICADOS_CUPO_AFILIADO;
                }
            }
            foreach ($arrRecs as $rx) {
                if (!in_array($rx["servicio"], $ServiciosAfiliacion)) {
                    if ($cupo > 0) {
                        if ($rx["tipogasto"] == '1') {
                            if ($rx["fecoperacion"] >= $fecpagoafiliacion) {
                                if ($cupo - $rx["cantidad"] >= 0) {
                                    $cupo = $cupo - $rx["cantidad"];
                                } else {
                                    $cupo = 0;
                                }
                                $iDetalle++;
                                $detalle[$iDetalle] = array(
                                    'tipo' => 'Consumo',
                                    'fecha' => $rx["fecoperacion"],
                                    'recibo' => $rx["numerorecibo"],
                                    'valor' => $rx["valor"],
                                    'cantidad' => $rx["cantidad"],
                                    'cupo' => $cupo
                                );
                            }
                        }
                    }
                }
            }
        }

        return $detalle;
    }

    public static function consultarWsSipp($cod, $liq)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/includes/nusoap_5.3/lib/nusoap.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';

        // Consumir el servicio web de SIPP para consultar estado de transacciones

        $client = new nusoap_client(URL_SIPP_WS, 'wsdl');
        $resultado = $client->call("consultarLiquidacion", array('datosEntrada' => $cod . sprintf("%07s", $liq)));
        if ($client->fault) {
            $_SESSION["generales"]["mensajeerror"] = '(1) ' . $client->fault;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $_SESSION["generales"]["mensajeerror"] = '(2X) ' . $err . ' - ' . $resultado;
                return false;
            }
        }

        $dom = new DomDocument('1.0', 'utf-8');
        $result = $dom->loadXML($resultado);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = '(3) Error recuperando xml: ' . $result;
            return false;
            exit();
        }
        $reg1 = $dom->getElementsByTagName("respuesta");
        $retorno = array();
        foreach ($reg1 as $reg) {
            if ($reg->getElementsByTagName("codigo_error")->item(0)->textContent == '0000') {

                $retorno["id"] = $reg->getElementsByTagName("id")->item(0)->textContent;
                $retorno["idliquidacion"] = $reg->getElementsByTagName("idliquidacion")->item(0)->textContent;
                $retorno["fecha"] = $reg->getElementsByTagName("fecha")->item(0)->textContent;
                $retorno["hora"] = $reg->getElementsByTagName("hora")->item(0)->textContent;
                $retorno["fechaultimamodificacion"] = ltrim($reg->getElementsByTagName("fechaultimamodificacion")->item(0)->textContent, '0');
                $retorno["idusuario"] = trim($reg->getElementsByTagName("idusuario")->item(0)->textContent);
                $retorno["tipotramite"] = trim($reg->getElementsByTagName("tipotramite")->item(0)->textContent);
                $retorno["iptramite"] = trim($reg->getElementsByTagName("iptramite")->item(0)->textContent);
                $retorno["idestado"] = trim($reg->getElementsByTagName("idestado")->item(0)->textContent);
                $retorno["idexpedientebase"] = trim($reg->getElementsByTagName("idexpedientebase")->item(0)->textContent);
                $retorno["tipoidentificacionbase"] = trim($reg->getElementsByTagName("tipoidentificacionbase")->item(0)->textContent);
                $retorno["identificacionbase"] = trim($reg->getElementsByTagName("identificacionbase")->item(0)->textContent);
                $retorno["idtipoidentificacioncliente"] = trim($reg->getElementsByTagName("idtipoidentificacioncliente")->item(0)->textContent);
                $retorno["identificacioncliente"] = trim($reg->getElementsByTagName("identificacioncliente")->item(0)->textContent);
                $retorno["nombrecliente"] = trim($reg->getElementsByTagName("nombrecliente")->item(0)->textContent);
                $retorno["email"] = trim($reg->getElementsByTagName("email")->item(0)->textContent);
                $retorno["direccion"] = trim($reg->getElementsByTagName("direccion")->item(0)->textContent);
                $retorno["idmunicipio"] = trim($reg->getElementsByTagName("idmunicipio")->item(0)->textContent);
                $retorno["telefono"] = trim($reg->getElementsByTagName("telefono")->item(0)->textContent);
                $retorno["movil"] = trim($reg->getElementsByTagName("movil")->item(0)->textContent);
                $retorno["valorbruto"] = trim($reg->getElementsByTagName("valorbruto")->item(0)->textContent);
                $retorno["valorbase"] = trim($reg->getElementsByTagName("valorbase")->item(0)->textContent);
                $retorno["valoriva"] = trim($reg->getElementsByTagName("valoriva")->item(0)->textContent);
                $retorno["valortotal"] = trim($reg->getElementsByTagName("valortotal")->item(0)->textContent);
                $retorno["idsolicitudpago"] = trim($reg->getElementsByTagName("idsolicitudpago")->item(0)->textContent);
                $retorno["sistemapago"] = trim($reg->getElementsByTagName("sistemapago")->item(0)->textContent);
                $retorno["pagoefectivo"] = trim($reg->getElementsByTagName("pagoefectivo")->item(0)->textContent);
                $retorno["pagocheque"] = trim($reg->getElementsByTagName("pagocheque")->item(0)->textContent);
                $retorno["pagovisa"] = trim($reg->getElementsByTagName("pagovisa")->item(0)->textContent);
                $retorno["pagoach"] = trim($reg->getElementsByTagName("pagoach")->item(0)->textContent);
                $retorno["pagomastercard"] = trim($reg->getElementsByTagName("pagomastercard")->item(0)->textContent);
                $retorno["pagoamerican"] = trim($reg->getElementsByTagName("pagoamerican")->item(0)->textContent);
                $retorno["pagocredencial"] = trim($reg->getElementsByTagName("pagocredencial")->item(0)->textContent);
                $retorno["pagodiners"] = trim($reg->getElementsByTagName("pagodiners")->item(0)->textContent);
                $retorno["pagotdebito"] = trim($reg->getElementsByTagName("pagotdebito")->item(0)->textContent);
                $retorno["idformapago"] = trim($reg->getElementsByTagName("idformapago")->item(0)->textContent);
                $retorno["numerorecibo"] = trim($reg->getElementsByTagName("numerorecibo")->item(0)->textContent);
                $retorno["numerooperacion"] = trim($reg->getElementsByTagName("numerooperacion")->item(0)->textContent);
                $retorno["fecharecibo"] = trim($reg->getElementsByTagName("fecharecibo")->item(0)->textContent);
                $retorno["horarecibo"] = trim($reg->getElementsByTagName("horarecibo")->item(0)->textContent);
                $retorno["idfranquicia"] = trim($reg->getElementsByTagName("idfranquicia")->item(0)->textContent);
                $retorno["nombrefranquicia"] = trim($reg->getElementsByTagName("nombrefranquicia")->item(0)->textContent);
                $retorno["numeroautorizacion"] = trim($reg->getElementsByTagName("numeroautorizacion")->item(0)->textContent);
                $retorno["idcodban"] = trim($reg->getElementsByTagName("idcodban")->item(0)->textContent);
                $retorno["nombrebanco"] = trim($reg->getElementsByTagName("nombrebanco")->item(0)->textContent);
                $retorno["numerocheque"] = trim($reg->getElementsByTagName("numerocheque")->item(0)->textContent);
                $retorno["numerorecuperacion"] = trim($reg->getElementsByTagName("numerorecuperacion")->item(0)->textContent);
                $retorno["numeroradicacion"] = trim($reg->getElementsByTagName("numeroradicacion")->item(0)->textContent);
                $retorno["codigoError"] = '0000';
            } else {
                if ($reg->getElementsByTagName("codigo_error")->item(0)->textContent != '0001' && $reg->getElementsByTagName("codigo_error")->item(0)->textContent != '0005') {
                    $_SESSION["generales"]["mensajeerror"] = '(4) ' . trim($reg->getElementsByTagName("mensaje_error")->item(0)->textContent);
                    return false;
                } else {
                    $retorno["codigoError"] = $reg->getElementsByTagName("codigo_error")->item(0)->textContent;
                }
            }
        }

        unset($reg);
        unset($reg1);
        unset($dom);
        return $retorno;
    }

    public static function consultarWsZonaVirtual($cod, $numliq, $log = 'sondaZonaVirtual')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/includes/nusoap_5.3/lib/nusoap.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';

        $nameLog = $log;
        if ($nameLog == '') {
            $nameLog = 'sondaZonaVirtual_' . date("Ymd");
        }

        // Consumir el servicio web de ZonaVirtual para consultar estado de transacciones
        $client = new nusoap_client(URL_ZONAVIRTUAL_WS, 'wsdl', '', '', '', '');
        $client->setUseCurl('0');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $params = array(
            'str_id_pago' => sprintf("%02s", $cod) . sprintf("%011s", $numliq),
            'int_id_tienda' => ZONAVIRTUAL_ID,
            'str_id_clave' => ZONAVIRTUAL_CLAVE
        );

        $resultado = $client->call('verificar_pago_v3', $params, '', '', false, true);

        if ($client->fault) {
            \logApi::general2($nameLog, 0, utf8_encode('Error Fault:' . $client->fault));
            $_SESSION["generales"]["mensajeerror"] = $client->fault;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $_SESSION["generales"]["mensajeerror"] = $err;
                \logApi::general2($nameLog, 0, utf8_encode('Error GetError:' . $err));
                return false;
            }
        }

        \logApi::general2($nameLog, 0, utf8_encode('Result:' . $resultado));

        if (isset($resultado['return'])) {
            $result1 = ($resultado['return']);
        } elseif (!is_array($resultado)) {
            $result1 = ($resultado);
        } else {
            $result1 = $resultado;
        }

        $txt = '';
        foreach ($result1 as $key => $valor) {
            if (!is_array($valor)) {
                $txt .= $key . ' = ' . $valor . chr(13) . chr(10);
            } else {
                foreach ($valor as $key1 => $valor1) {
                    $txt .= '----------> ' . $key1 . ' = ' . $valor1 . chr(13) . chr(10);
                }
            }
        }
        \logApi::general2($nameLog, 0, utf8_encode('Result Parser:' . $txt));
        // Se retorno una respuesta
        // Se encontraron pagos

        $arrSal = array();
        if ($result1["verificar_pago_v3Result"] == '0') { // No se retornaron registros
            if ($result1["int_error"] == '1') { // No se retornaron registros
                $arrSal["int_id_forma_pago"] = '';
                $arrSal["dbl_valor_pagado"] = '';
                $arrSal["str_ticketID"] = '';
                $arrSal["str_id_clave"] = '';
                $arrSal["str_id_cliente"] = '';
                $arrSal["str_franquicia"] = '';
                $arrSal["int_estado_pago"] = '';
                $arrSal["int_cod_aprobacion"] = '';
                $arrSal["int_codigo_servico"] = '';
                $arrSal["int_codigo_banco"] = '';
                $arrSal["str_nombre_banco"] = '';
                $arrSal["str_codigo_transaccion"] = '';
                $arrSal["int_ciclo_transaccion"] = '';
                $arrSal["str_campo1"] = '';
                $arrSal["str_campo2"] = '';
                $arrSal["str_campo3"] = '';
                $arrSal["dat_fecha"] = '';
                $arrSal["idestado"] = '89'; // Significa que el pago no fue encontrado en la plataforma
                $arrSal["idestadozonavirtual"] = '89';
                $arrSal["numeroautorizacion"] = '';
                $arrSal["idliquidacion"] = $numliq;
                $arrSal["valortotal"] = $arrSal["dbl_valor_pagado"];
                return $arrSal;
            }
        }

        if ($result1["verificar_pago_v3Result"] == '1') {

            $arrSal["int_id_forma_pago"] = $result1["res_pagos_v3"]["pagos_v3"]["int_id_forma_pago"];
            $arrSal["int_estado_pago"] = $result1["res_pagos_v3"]["pagos_v3"]["int_estado_pago"];
            $arrSal["dbl_valor_pagado"] = $result1["res_pagos_v3"]["pagos_v3"]["dbl_valor_pagado"];
            $arrSal["str_ticketID"] = $result1["res_pagos_v3"]["pagos_v3"]["str_ticketID"];
            $arrSal["str_id_clave"] = $result1["res_pagos_v3"]["pagos_v3"]["str_id_clave"];
            $arrSal["str_id_cliente"] = $result1["res_pagos_v3"]["pagos_v3"]["str_id_cliente"];
            $arrSal["str_franquicia"] = $result1["res_pagos_v3"]["pagos_v3"]["str_franquicia"];
            $arrSal["int_cod_aprobacion"] = $result1["res_pagos_v3"]["pagos_v3"]["int_cod_aprobacion"];
            $arrSal["int_codigo_servico"] = $result1["res_pagos_v3"]["pagos_v3"]["int_codigo_servico"];
            $arrSal["int_codigo_banco"] = $result1["res_pagos_v3"]["pagos_v3"]["int_codigo_banco"];
            $arrSal["str_nombre_banco"] = $result1["res_pagos_v3"]["pagos_v3"]["str_nombre_banco"];
            $arrSal["str_codigo_transaccion"] = $result1["res_pagos_v3"]["pagos_v3"]["str_codigo_transaccion"];
            $arrSal["int_ciclo_transaccion"] = $result1["res_pagos_v3"]["pagos_v3"]["int_ciclo_transaccion"];
            $arrSal["str_campo1"] = $result1["res_pagos_v3"]["pagos_v3"]["str_campo1"];
            $arrSal["str_campo2"] = $result1["res_pagos_v3"]["pagos_v3"]["str_campo2"];
            $arrSal["str_campo3"] = $result1["res_pagos_v3"]["pagos_v3"]["str_campo3"];
            $arrSal["dat_fecha"] = $result1["res_pagos_v3"]["pagos_v3"]["dat_fecha"];
            $arrSal["idliquidacion"] = $numliq;
            $arrSal["numeroautorizacion"] = $arrSal["str_codigo_transaccion"];
            $arrSal["valortotal"] = $arrSal["dbl_valor_pagado"];
            if ($arrSal["int_estado_pago"] == '1') {
                if ($result1["int_error"] == '0') {
                    $arrSal["idestado"] = '07';
                    return $arrSal;
                }
            }
        }

        if ($result1["verificar_pago_v3Result"] > '1') {

            $iCont = 0;
            foreach ($result1["res_pagos_v3"]["pagos_v3"] as $res) {
                $iCont++;
                if ($iCont == 1) {
                    $arrSal["int_id_forma_pago"] = $res["int_id_forma_pago"];
                    $arrSal["int_estado_pago"] = $res["int_estado_pago"];
                    $arrSal["dbl_valor_pagado"] = $res["dbl_valor_pagado"];
                    $arrSal["str_ticketID"] = $res["str_ticketID"];
                    $arrSal["str_id_clave"] = $res["str_id_clave"];
                    $arrSal["str_id_cliente"] = $res["str_id_cliente"];
                    $arrSal["str_franquicia"] = $res["str_franquicia"];
                    $arrSal["int_cod_aprobacion"] = $res["int_cod_aprobacion"];
                    $arrSal["int_codigo_servico"] = $res["int_codigo_servico"];
                    $arrSal["int_codigo_banco"] = $res["int_codigo_banco"];
                    $arrSal["str_nombre_banco"] = $res["str_nombre_banco"];
                    $arrSal["str_codigo_transaccion"] = $res["str_codigo_transaccion"];
                    $arrSal["int_ciclo_transaccion"] = $res["int_ciclo_transaccion"];
                    $arrSal["str_campo1"] = $res["str_campo1"];
                    $arrSal["str_campo2"] = $res["str_campo2"];
                    $arrSal["str_campo3"] = $res["str_campo3"];
                    $arrSal["dat_fecha"] = $res["dat_fecha"];
                    $arrSal["idliquidacion"] = $numliq;
                    $arrSal["numeroautorizacion"] = $arrSal["str_codigo_transaccion"];
                    $arrSal["valortotal"] = $arrSal["dbl_valor_pagado"];
                    if ($arrSal["int_estado_pago"] == '1') {
                        if ($result1["int_error"] == '0') {
                            $arrSal["idestado"] = '07';
                            return $arrSal;
                        }
                    }
                }
            }
        }

        if ($result1["verificar_pago_v3Result"] > '0') {
            //
            if ($result1["int_error"] == '-1') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }

            // No se encontraron pagos
            if ($result1["int_error"] == '1') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }

            // Datos incorrectos
            if ($result1["int_error"] == '2') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }

            // Error inesperado
            if ($result1["int_error"] == '3') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }

            // Se encontraron pagos
            if ($result1["int_error"] == '0') {

                // pago satisfactorio

                if ($arrSal["int_estado_pago"] == '1') {
                    $arrSal["idestado"] = '07';
                    $arrSal["idliquidacion"] = $numliq;
                    $arRSal["numeroautorizacion"] = $arrSal["str_codigo_transaccion"];
                    return $arrSal;
                }

                // 	Si el pago no ha sido iniciado a&uacute;n
                if ($arrSal["int_estado_pago"] == '888') {
                    $arrSal["idliquidacion"] = $numliq;
                    $arrSal["idestado"] = '88';
                    $arrSal["numeroautorizacion"] = '';
                    return $arrSal;
                }

                // Si el pago no ha sido finalizado a&uacute;n
                if ($arrSal["int_estado_pago"] == '999') {
                    $arrSal["idliquidacion"] = $numliq;
                    $arrSal["idestado"] = '06';
                    $arrSal["numeroautorizacion"] = $arrSal["str_codigo_transaccion"];
                    return $arrSal;
                }
            }
        }

        // Se retorno una respuesta
        // No se encontraron pagos
        if ($result1["verificar_pago_v3Result"] == '-1') {

            // No se encontraron pagos (Clave o usaurio inv&aacute;lido
            if ($result1["int_error"] == '1') {
                $arrSal["idliquidacion"] = $numliq;
                $arrSal["idestado"] = '08';
                $arrSal["numeroautorizacion"] = $result1["str_codigo_transaccion"];
                return $arrSal;
            }
            // Datos incorrectos
            if ($result1["int_error"] == '2') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }

            // Error inesperado
            if ($result1["int_error"] == '3') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }

            // Error inesperado
            if ($result1["int_error"] == '-1') {
                $_SESSION["mensajeerror"] = $result1["str_error"];
                return false;
            }
        }

        if ($result1["verificar_pago_v3Result"] == '2') { // Pago iniciado
            if ($result1["int_error"] == '1') { // No se retornaron registros
                $arrSal["int_id_forma_pago"] = '';
                $arrSal["dbl_valor_pagado"] = '';
                $arrSal["str_ticketID"] = '';
                $arrSal["str_id_clave"] = '';
                $arrSal["str_id_cliente"] = '';
                $arrSal["str_franquicia"] = '';
                $arrSal["int_cod_aprobacion"] = '';
                $arrSal["int_codigo_servico"] = '';
                $arrSal["int_codigo_banco"] = '';
                $arrSal["str_nombre_banco"] = '';
                $arrSal["str_codigo_transaccion"] = '';
                $arrSal["int_ciclo_transaccion"] = '';
                $arrSal["str_campo1"] = '';
                $arrSal["str_campo2"] = '';
                $arrSal["str_campo3"] = '';
                $arrSal["dat_fecha"] = '';
                $arrSal["idestado"] = '06'; // Significa que el pago no fue encontrado en la plataforma
                $arrSal["idliquidacion"] = $numliq;
                $arRSal["numeroautorizacion"] = '';
                $arrSal["valortotal"] = '';
                return $arrSal;
            }
        }
    }

    public static function consultarMultasPolicia($mysqli, $tid, $id, $idliq = 0)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php';

        $nameLog = 'validacionMultasPonal_' . date("Ymd");

        //
        $reintentar = 3;
        $multadovencido = 'ER';
        $textoerror = '';
        while ($reintentar > 0) {
            $buscartoken = true;
            $buscarmulta = true;
            $name = PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt';
            if (file_exists($name)) {
                $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt', true);
                list($token, $expira) = explode("|", $x);
                $act = date("Y-m-d H:i:s");
                if ($act <= $expira) {
                    $buscartoken = false;
                }
            }

            if ($buscartoken) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://catalogoservicioweb.policia.gov.co/sw/token');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "username=fvera@confecamaras.org.co&password=fveraPolicia2017*2018&grant_type=password");
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                curl_close($ch);
                if ((is_string($result) &&
                    (is_object(json_decode($result)) ||
                        is_array(json_decode($result))))) {
                    $resultado = json_decode($result, true);
                    $access_token = $resultado['access_token'];
                    $fecha = date('Y-m-d H:i:s', (strtotime("+20 Hours")));
                    $f = fopen($name, "w");
                    fwrite($f, $access_token . '|' . $fecha);
                    fclose($f);
                } else {
                    $textoerror = 'NO FUE POSIBLE SOLICITAR EL TOKEN (1), LA RESPUESTA DEL SERVICIUO WEB DE TOKEN ES INCORRECTA';
                    \logApi::general2($nameLog, $tid . '-' . $id, $textoerror . ' : ' . $result);
                    $buscarmulta = false;
                    $reintentar--;
                }
            }

            //
            if ($buscarmulta) {
                if (file_exists($name)) {
                    $x = file_get_contents(PATH_ABSOLUTO_SITIO . '/tmp/' . CODIGO_EMPRESA . '-tokenponal.txt', true);
                    list($access_token, $expira) = explode("|", $x);
                } else {
                    $textoerror = 'NO FUE POSIBLE SOLICITAR EL TOKEN (2), NO EXISTE ARCHIVO CON EL TOKEN ALMACENADO';
                    $buscarmulta = false;
                    $reintentar--;
                }
            }

            //
            if ($buscarmulta) {
                $data = array(
                    'codigoCamara' => CODIGO_EMPRESA,
                    'tipoConsulta' => 'CC',
                    'numeroIdentificacion' => $id
                );

                //
                $fields = json_encode($data);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://catalogoservicioweb.policia.gov.co/sw/api/Multa/ConsultaMultaVencidaSeisMeses');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                curl_close($ch);

                \logApi::general2($nameLog, $tid . '-' . $id, $result);
                if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"])) {
                    mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"], 0777);
                    \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"]);
                }
                if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/")) {
                    mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/", 0777);
                    \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/");
                }
                if (!is_dir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/")) {
                    mkdir(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/", 0777);
                    \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . "/" . $_SESSION["generales"]["codigoempresa"] . "/mreg/ponal/");
                }
                if (trim($result) == '') {
                    $result = "El servicio web PONAL-MULTAS no retorno respuesta";
                }
                $name1 = PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/ponal/validaciones-' . date("Ym") . '.log';
                $f1 = fopen($name1, "a");
                fwrite($f1, date("Y-m-d") . '|' . date("His") . '|' . $tid . '|' . $id . '|' . $result . chr(13) . chr(10));
                fclose($f1);
                if ((is_string($result) &&
                    (is_object(json_decode($result)) ||
                        is_array(json_decode($result))))) {
                    $resultado = json_decode($result, true);
                    if (isset($resultado["Message"])) {
                        if ($resultado["Message"] == 'Authorization has been denied for this request.') {
                            unlink($name);
                            $reintentar--;
                        } else {
                            $reintentar = 0;
                        }
                    } else {
                        $multadovencido = 'NO';
                        $fecx = date("Ymd");
                        $horx = date("His");
                        foreach ($resultado as $multa) {
                            $arrCampos = array(
                                'fecha',
                                'hora',
                                'tipoidentificacion',
                                'identificacion',
                                'nombres',
                                'apellidos',
                                'nit',
                                'razonsocial',
                                'estado',
                                'fechaimposicion',
                                'multavencida',
                                'direccionhechos',
                                'codigomunicipio',
                                'nombremunicipio',
                                'codigodpto',
                                'nombredpto',
                                'codigobarrio',
                                'nombrebarrio',
                                'numeralinfringido',
                                'articuloinfringido',
                                'idliquidacion'
                            );
                            $arrValores = array(
                                "'" . $fecx . "'",
                                "'" . $horx . "'",
                                "'" . $tid . "'",
                                "'" . $id . "'",
                                "'" . addslashes($multa["NOMBRES"]) . "'",
                                "'" . addslashes($multa["APELLIDOS"]) . "'",
                                "'" . $multa["NIT"] . "'",
                                "'" . addslashes($multa["RAZON_SOCIAL"]) . "'",
                                "'" . $multa["ESTADO"] . "'",
                                "'" . $multa["FECHA_IMPOSICION"] . "'",
                                "'" . $multa["MULTA_VENCIDA"] . "'",
                                "'" . addslashes($multa["DIRECCION_HECHOS"]) . "'",
                                "'" . $multa["COD_MUNICIPIO"] . "'",
                                "'" . addslashes($multa["MUNICIPIO"]) . "'",
                                "'" . $multa["COD_DEPARTAMENTO"] . "'",
                                "'" . addslashes($multa["DEPARTAMENTO"]) . "'",
                                "'" . $multa["COD_BARRIO"] . "'",
                                "'" . addslashes($multa["BARRIO"]) . "'",
                                "'" . addslashes($multa["ARTICULO_INFRINGIDO"]) . "'",
                                "'" . addslashes($multa["NUMERAL_INFRINGIDO"]) . "'",
                                $idliq
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_multas_ponal', $arrCampos, $arrValores);
                            //
                            if ($multa["MULTA_VENCIDA"] == 'SI') {
                                $multadovencido = 'SI';
                            }
                        }
                        $arrCampos = array(
                            'sincronizomultasponal',
                            'fechasincronizomultasponal',
                            'resultadosincronizomultasponal'
                        );
                        $arrValores = array(
                            "'SI'",
                            "'" . date("Ymd") . "'",
                            "'" . $multadovencido . "'"
                        );
                        regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                        $reintentar = 0;
                    }
                } else {
                    $result = str_replace('"', '', $result);
                    if (substr($result, 0, 16) == 'Para la consulta') {
                        $multadovencido = 'NO';
                        $arrCampos = array(
                            'sincronizomultasponal',
                            'fechasincronizomultasponal',
                            'resultadosincronizomultasponal'
                        );
                        $arrValores = array(
                            "'SI'",
                            "'" . date("Ymd") . "'",
                            "'" . $multadovencido . "'"
                        );
                    } else {
                        $multadovencido = 'ER';
                        $arrCampos = array(
                            'sincronizomultasponal',
                            'fechasincronizomultasponal',
                            'resultadosincronizomultasponal'
                        );
                        $arrValores = array(
                            "'SI'",
                            "'" . date("Ymd") . "'",
                            "'" . $multadovencido . "'"
                        );
                    }
                    regrabarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $arrCampos, $arrValores, "idclase='" . $tid . "' and numid='" . $id . "'");
                    $reintentar = 0;
                }
            }
        }
        return $multadovencido;
    }

    /**
     *
     * @param type $mysqli
     * @param type $ope
     * @param type $rec
     * @return type
     */
    public static function consumirWsVRRECREC($mysqli = null, $rec = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_consumirWsVRRECREC.php';
        return \funcionesRegistrales_consumirWsVRRECREC::consumirWsVRRECREC($mysqli, $rec);
    }

    /**
     *
     * @param type $mysqli
     * @param type $cri
     * @param type $ide
     * @param type $clave
     * @param type $valor
     * @param type $recibo
     * @param type $numoperacion
     * @param type $servicio
     * @param type $cantidad
     * @param type $detalle
     * @param type $ip
     * @param type $usuario
     * @param type $expediente
     * @param type $email
     * @param type $nombre
     * @param type $celular
     * @param type $direccion
     * @param type $municipio
     * @param type $tipousuario
     * @param type $telefono
     * @param type $nom1
     * @param type $nom2
     * @param type $ape1
     * @param type $ape2
     * @return type
     */
    public static function consumirPrepago($mysqli, $cri, $ide, $clave = '', $valor = '', $recibo = '', $numoperacion = '', $servicio = '', $cantidad = '', $detalle = '', $ip = '', $usuario = '', $expediente = '', $email = '', $nombre = '', $celular = '', $direccion = '', $municipio = '', $tipousuario = '', $telefono = '', $nom1 = '', $nom2 = '', $ape1 = '', $ape2 = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_consumirPrepago.php';
        return \funcionesRegistrales_consumirPrepago::consumirPrepago($mysqli, $cri, $ide, $clave, $valor, $recibo, $numoperacion, $servicio, $cantidad, $detalle, $ip, $usuario, $expediente, $email, $nombre, $celular, $direccion, $municipio, $tipousuario, $telefono, $nom1, $nom2, $ape1, $ape2);
    }

    public static function consultarRelacionMatriculasPendientesRenovar($mysqli, $txt)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';

        if ($txt != '') {
            $lista = explode(',', $txt);
            $canceladas = 0;
            $aldia = 0;
            $in = '';
            $inx = '';
            foreach ($lista as $l) {
                $lx = explode('-', $l);
                if (isset($lx[1])) {
                    $ly = ltrim($lx[1], "0");
                } else {
                    $ly = ltrim($l, "0");
                }
                if ($in != '') {
                    $in .= ',';
                }
                $in .= "'" . $ly . "'";
                $inx = $ly . ' ';
            }
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', "matricula IN (" . $in . ")", "matricula");
            foreach ($arrTem as $t) {
                if ($t["ctrestmatricula"] != 'MA' && $t["ctrestmatricula"] != 'IA') {
                    $canceladas++;
                }
                if ($t["ultanoren"] == date("Y")) {
                    $aldia++;
                }
            }
            unset($arrTem);

            if ($canceladas == 0 && $aldia == 0) {
                $resultado["codigoError"] = '0000';
                $resultado["msgError"] = '';
            }
            if ($canceladas > 0) {
                $resultado["codigoError"] = '0002';
                $resultado["msgError"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) cancelada(s) en fecha posterior a la elaboraci&oacute;n de la liquidaci&oacute;n (' . $txt . ')';
            }
            if ($aldia > 0) {
                $resultado["codigoError"] = '0003';
                $resultado["msgError"] = 'Alguna(s) de la(s) matr&iacute;cula(s) a renovar fue(ron) renovadas en fecha posterior a la elaboraci&oacute;n de la liquidaci&oacute;n (' . $txt . ')';
            }
        } else {
            $resultado["codigoError"] = '0000';
            $resultado["msgError"] = '';
        }

        return $resultado;
    }

    /**
     *
     * @param type $mysqli
     * @param type $nameLog
     * @param type $cb
     * @param type $usuariodesiste
     * @return bool
     */
    public static function desistirCodigoBarras($mysqli, $nameLog, $cb, $usuariodesiste = 'BATCH', $nuevoestado = '39')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php';

        //
        if (!isset($_SESSION["generales"]["batch"])) {
            $_SESSION["generales"]["batch"] = 'no';
        }

        if (!isset($_SESSION["desistimientos"]["totaldecretadoserror"])) {
            $_SESSION["desistimientos"]["totaldecretadoserror"] = 0;
        }

        if (!isset($_SESSION["desistimientos"]["totaldecretados"])) {
            $_SESSION["desistimientos"]["totaldecretados"] = 0;
        }

        //
        $tipot = '';
        $tipoDevolucion = 'R';
        $bandeja = '';
        $fechadevolucion = '';
        $rest = retornarRegistrosMysqliApi($mysqli, 'mreg_devoluciones_nueva', "idradicacion='" . ltrim($cb, "0") . "'", "fechadevolucion,horadevolucion");
        if ($rest && !empty($rest)) {
            foreach ($rest as $r) {
                $tipot = $r["tipotramite"];
                $fechadevolucion = $r["fechadevolucion"];
                if ($r["estado"] == '1' || $r["estado"] == '2') {
                    if ($r["tipodevolucion"] == 'D') {
                        $tipoDevolucion = 'D';
                    }
                }
            }
        }
        if ($tipoDevolucion == 'R') {
            if ($tipot != '') {
                $arrTx = retornarRegistroMysqliApi($mysqli, 'mreg_tipotramite', "idtramite='" . $tipot . "'");
                if ($arrTx && !empty($arrTx)) {
                    $bandeja = $arrTx["bandeja"];
                }
            }
        }

        if ($tipoDevolucion == 'R') {
            // Localiza el código de barras es la ruta de documentos
            $cba = \funcionesRegistrales::retornarCodigoBarras($mysqli, ltrim($cb, '0'));
            if ($cba === false || empty($cba)) {
                \logApi::general2($nameLog, $cb, 'No fue posible recuperar el código de barras');
                return false;
            }

            // Si es embrago no desiste
            if ($cba["tramite"] == '07' || $cba["tramite"] == '29') {
                $tipoDevolucion = 'D';
            }
        }

        if ($tipoDevolucion == 'R') {
            $rx = retornarRegistroMysqliApi($mysqli, 'mreg_desistimientos', "codigobarras='" . ltrim($cb, "0") . "'");
            if ($rx && !empty($rx)) {
                \logApi::general2($nameLog, $cb, 'Desistimiento decretado previamente');
                $_SESSION["generales"]["mensajeerror"] = 'El código de barras ' . $cb . ' fue desistido  previamente el ' . $rx["fechadocdesistimiento"];
                return false;
            } else {
                $arrCampos = array(
                    'codigobarras',
                    'operacion',
                    'recibo',
                    'fecharadicacion',
                    'fechadevolucionentrega',
                    'matricula',
                    'proponente',
                    'identificacion',
                    'nombre',
                    'tipotramite',
                    'servicio',
                    'emails',
                    'telefonos',
                    'idtipodoc',
                    'numdoc',
                    'fechadoc',
                    'origendoc',
                    'idestado',
                    'bandeja',
                    'idtipodocdesistimiento',
                    'numdocdesistimiento',
                    'fechadocdesistimiento',
                    'pathdocumento',
                    'usuariodeclaradesistimiento',
                    'mensaje',
                    'fechanotificacionemail',
                    'horanotificacionemail',
                    'detallenotificacionemail',
                    'fechanotificaciontelefonica',
                    'horanotificaciontelefonica',
                    'usuarionotificaciontelefonica',
                    'habloconconfirmaciontelefonica',
                    'detalleconfirmaciontelefonica',
                    'fechanotificacionsms',
                    'horanotificacionsms',
                    'detallenotificacionsms',
                    'fechaentregapresencial',
                    'horaentregapresencial',
                    'usuarioentregapresencial',
                    'fechaarchivodesistimiento',
                    'horaarchivodesistimiento',
                    'usuarioarchivadesistimiento',
                    'process_id',
                    'informado_powerfile'
                ); // 44
                //
                $servicio = '';
                if (isset($cba["servicios"][1]["ser"])) {
                    $servicio = $cba["servicios"][1]["ser"];
                }

                //
                $emails = '';
                $telefonos = '';
                $emi = 0;
                foreach ($cba["emails"] as $em) {
                    if (trim($emails) != '') {
                        $emails .= ',';
                    }
                    $emails .= $em;
                }

                //
                foreach ($cba["telefonos"] as $em) {
                    if (strlen($em) == 10 && substr($em, 0, 1) == '3') {
                        if (trim($telefonos) != '') {
                            $telefonos .= ',';
                        }
                        $telefonos .= $em;
                    }
                }

                //
                $arrValores = array(
                    "'" . $cba["codbarras"] . "'",
                    "'" . $cba["operacion"] . "'",
                    "'" . $cba["recibo"] . "'",
                    "'" . $cba["fecharad"] . "'",
                    "'" . $fechadevolucion . "'",
                    "'" . $cba["matricula"] . "'",
                    "'" . $cba["proponente"] . "'",
                    "'" . $cba["numid"] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($cba["nombre"])) . "'",
                    "'" . $cba["tramite"] . "'",
                    "'" . $servicio . "'",
                    "'" . $emails . "'",
                    "'" . $telefonos . "'",
                    "'" . $cba["tipodoc"] . "'",
                    "'" . $cba["numdoc"] . "'",
                    "'" . $cba["fechadoc"] . "'",
                    "'" . $cba["txtorigendoc"] . "'",
                    "'39'",
                    "'" . $bandeja . "'",
                    "''", // Tipo doc desistimiento
                    "''", // Numdoc desistimiento	
                    "''", // Fecha doc desistimiento
                    "''", // Path de la imagen o pdf del acto de desistimiento
                    "'" . $_SESSION["desistimientos"]["ususec"] . "'", // Usuario que declara el desistimiento
                    "''", // mensaje que se env&iacute;a por email
                    "''", // fecha notificacion por email
                    "''", // hora notificacion por email
                    "''", // detalle notificacion por email
                    "''", // fecha notificacion telef&oacute;nica
                    "''", // hora notificacion telef&oacute;nica
                    "''", // usuario notificacion telef&oacute;nica
                    "''", // Hablo con en la notificaci&oacute;n telef&oacute;nica
                    "''", // Detalle de la notificaci&oacute;n telef&oacute;nica
                    "''", // fecha notificacion sms
                    "''", // hora notificacion sms
                    "''", // detalle notificacion sms
                    "''", // fecha notificacion presencial
                    "''", // hora notificacion presencial
                    "''", // usuario notificacion presencial
                    "''", // Fecha de archivo del desistimiento
                    "''", // Hora de archivo del desistimiento
                    "''", // Usuario que archiva el desistimiento				
                    "''", // Process Id
                    "''" // Informado powerfile				                
                );

                //
                $resx = insertarRegistrosMysqliApi($mysqli, 'mreg_desistimientos', $arrCampos, $arrValores);
                if ($resx === false) {
                    \logApi::general2($nameLog, $cb, 'Error insertando desistimiento: ' . $cb . ' descripcion error: ' . $_SESSION["generales"]["mensajeerror"]);
                    if ($_SESSION["generales"]["batch"] == 'no') {
                        if ($usuariodesiste != 'BATCH') {
                            echo 'Error insertando desistimiento: ' . $cb . ' descripcion error: ' . $_SESSION["generales"]["mensajeerror"] . '<br>';
                        }
                    }
                    $_SESSION["desistimientos"]["totaldecretadoserror"]++;
                } else {
                    $_SESSION["desistimientos"]["totaldecretados"]++;
                    \logApi::general2($nameLog, '', 'Decretado desistimiento Codigo de Barras: ' . $cb);
                    if ($_SESSION["generales"]["batch"] == 'no') {
                        if ($usuariodesiste != 'BATCH') {
                            echo 'Decretado desistimiento Codigo de Barras: ' . $cb . '<br>';
                        }
                    }

                    // Actualiza el estado del codigo de barras (39) - Decretado desistimiento
                    \funcionesRegistrales::actualizarEstadoCodigoBarras($mysqli, $cb, $nuevoestado, $usuariodesiste);

                    // 2019-09-10: JINT
                    // Generar nota de reversión al recibo de caja
                    // marcar el recibo como reversado
                    // 2020-03-08: JINT: Se habilita parámetro en commonXX para saber siu genera o no reversion
                    // al momento e desistir un tramite localmente
                    if (defined('GENERAR_REVERSION_AL_DESISTIR') && GENERAR_REVERSION_AL_DESISTIR == 'SI') {
                        $nota = \funcionesRegistrales::grabarRegistroAnulacion($mysqli, $cba, 'POR DESISTIMIENTO', $nameLog, '42', 'POR DESISTIMIENTO DEL TRAMITE');
                    }

                    $estados = array();
                    $ruta = retornarRegistroMysqliApi($mysqli, 'mreg_codrutas', "id='" . $cba["tramite"] . "'");
                    if ($ruta["tipo"] == 'PR') {
                        $txs = retornarRegistrosMysqliApi($mysqli, 'mreg_codestados_rutaproponentes', "1=1", "id");
                    } else {
                        $txs = retornarRegistrosMysqliApi($mysqli, 'mreg_codestados_rutamercantil', "1=1", "id");
                    }
                    foreach ($txs as $tx) {
                        $estados[$tx["id"]] = $tx["estadoterminal"];
                    }
                    unset($txs);

                    //
                    if ($cba["tramite"] != '09' && $cba["tramite"] != '53') {
                        unset($_SESSION["expedienteactual"]);
                        if (isset($cba["matriculasasociadas"]) && !empty($cba["matriculasasociadas"])) {
                            foreach ($cba["matriculasasociadas"] as $mx => $dat) {
                                $am = 'si';
                                $cbms = retornarRegistrosMysqliApi($mysqli, 'mreg_est_codigosbarras', "matricula='" . $mx . "'", "codigobarras");
                                foreach ($cbms as $cbx) {
                                    if ($estados[$cbx["estadofinal"]] != 'S') {
                                        $am = 'no';
                                    }
                                }
                                unset($cbms);
                                if ($am == 'si') {
                                    if ($nuevoestado == '19') {
                                        $opc = 'mostrarBandejas_desistimientovoluntario';
                                    } else {
                                        $opc = 'mostrarBandejas_desistimientoforzado';
                                    }
                                    \funcionesRegistrales::actualizarMregEstInscritosCampo($mysqli, $mx, 'ctrestdatos', '6', 'varchar', $cb, $opc, '');
                                    $_SESSION['formulario'] = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $mx, '', '', '', 'si', 'no');
                                    $r = \funcionesRues::actualizarMercantilRues();
                                }
                            }
                        }
                    }

                    // enviar estado 14 al rues
                    $rec = retornarRegistroMysqliApi($mysqli, 'mreg_recibosgenerados', "recibo='" . $cba["recibo"] . "'");
                    if ($rec && !empty($rec)) {
                        if ($rec["numerointernorue"] != '' && TIPO_AMBIENTE == 'PRODUCCION') {
                            $resrues = \funcionesRues::consumirMR03N($rec["numerointernorue"], '14', '');
                            if ($resrues && $resrues["codigo_error"] == '0000') {
                                $arrCampos = array(
                                    'estadotransaccion'
                                );
                                $arrValores = array(
                                    "'14'"
                                );
                                regrabarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion', $arrCampos, $arrValores, "numerointernorue='" . $rec["numerointernorue"] . "'");

                                $arrCampos = array(
                                    'numerointernorue',
                                    'fecha',
                                    'hora',
                                    'usuario',
                                    'estado'
                                );
                                $arrValores = array(
                                    "'" . $rec["numerointernorue"] . "'",
                                    "'" . date("Ymd") . "'",
                                    "'" . date("His") . "'",
                                    "'" . $usuariodesiste . "'"
                                );
                                insertarRegistrosMysqliApi($mysqli, 'mreg_rue_radicacion_estados', $arrCampos, $arrValores);
                            }
                        }
                    }
                }
            }
        }

        //
        return true;
    }

    /**
     *
     * @param type $mysqli
     * @param type $xml
     * @return type
     */
    public static function desserializarReciboSirep($mysqli = null, $xml = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_desserializarReciboSirep.php';
        return \funcionesRegistrales_desserializarReciboSirep::desserializarReciboSirep($mysqli, $xml);
    }

    /**
     *
     * @return type
     */
    public static function docXflowSolicitarToken()
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_docXflowSolicitarToken.php';
        return \funcionesRegistrales_docXflowSolicitarToken::docXflowSolicitarToken();
    }

    /**
     *
     * @param type $mysqli
     * @param type $ciiu
     * @param type $ingresos
     * @param type $anodatos
     * @param type $fechadatos
     * @param type $anomatricula
     * @return type
     */
    public static function determinarTamanoEmpresarial($mysqli = null, $ciiu = '', $ingresos = 0, $anodatos = '', $fechadatos = '', $anomatricula = 'no')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_determinarTamanoEmpresarial.php';
        return \funcionesRegistrales_determinarTamanoEmpresarial::determinarTamanoEmpresarial($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula);
    }

    public static function determinarTamanoEmpresarialUvts($mysqli = null, $ciiu = '', $ingresos = 0, $anodatos = '', $fechadatos = '', $anomatricula = 'no')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_determinarTamanoEmpresarial.php';
        return \funcionesRegistrales_determinarTamanoEmpresarial::determinarTamanoEmpresarialUvts($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula);
    }

    public static function determinarTamanoEmpresarialUvbs($mysqli = null, $ciiu = '', $ingresos = 0, $anodatos = '', $fechadatos = '', $anomatricula = 'no')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_determinarTamanoEmpresarial.php';
        return \funcionesRegistrales_determinarTamanoEmpresarial::determinarTamanoEmpresarialUvbs($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula);
    }

    public static function determinarTamanoEmpresarialActivos($mysqli = null, $activos = 0, $personal = 0, $fechadatos = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_determinarTamanoEmpresarial.php';
        return \funcionesRegistrales_determinarTamanoEmpresarial::determinarTamanoEmpresarialActivos($mysqli, $activos, $personal, $fechadatos);
    }

    /**
     *
     * @param type $mysqli
     * @param type $ciiu
     * @param type $ingresos
     * @param type $anodatos
     * @param type $fechadatos
     * @param type $anomatricula
     * @return type
     */
    public static function determinarTamanoEmpresarialCodigo($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula = 'no')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_determinarTamanoEmpresarialCodigo.php';
        return \funcionesRegistrales_determinarTamanoEmpresarialCodigo::determinarTamanoEmpresarialCodigo($mysqli, $ciiu, $ingresos, $anodatos, $fechadatos, $anomatricula);
    }

    /**
     *
     * @param type $mysqli
     * @param type $cb
     * @param type $estado
     * @return type
     */
    public static function docXflowNotificarCambioEstado($mysqli, $cb, $estado)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_docXflowNotificarCambioEstado.php';
        return \funcionesRegistrales_docXflowNotificarCambioEstado::docXflowNotificarCambioEstado($mysqli, $cb, $estado);
    }

    /**
     *
     * @param type $mysqli
     * @param type $mat
     * @param type $prop
     * @param type $rad
     * @return type
     */
    public static function docXflowConsultarExpediente($mysqli, $mat = '', $prop = '', $rad = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_docXflowConsultarExpediente.php';
        return \funcionesRegistrales_docXflowConsultarExpediente::docXflowConsultarExpediente($mysqli, $mat, $prop, $rad);
    }

    /**
     *
     * @param type $mysqli
     * @param type $tiporegistro
     * @param type $liquidacion
     * @param type $matricula
     * @param type $proponente
     * @param type $tipotramite
     * @return type
     */
    public static function programarAlertaTemprana($mysqli, $tiporegistro, $liquidacion, $matricula, $proponente, $tipotramite)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_programarAlertaTemprana.php';
        return \funcionesRegistrales_programarAlertaTemprana::programarAlertaTemprana($mysqli, $tiporegistro, $liquidacion, $matricula, $proponente, $tipotramite);
    }

    /**
     *
     * @param type $mysqli
     * @param type $expediente
     * @param type $usuariocontrol
     * @param type $emailusuariocontrol
     * @param type $nombreusuariocontrol
     * @param type $celularusuariocontrol
     * @return type
     */
    public static function generarAlertaSiprefConsulta($mysqli = null, $expediente = '', $usuariocontrol = '', $emailusuariocontrol = '', $nombreusuariocontrol = '', $celularusuariocontrol = '', $ipcliente = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarAlertasSipref.php';
        return \funcionesRegistrales_generarAlertasSipref::generarAlertaSiprefConsulta($mysqli, $expediente, $usuariocontrol, $emailusuariocontrol, $nombreusuariocontrol, $celularusuariocontrol, $ipcliente);
    }

    /**
     *
     * @param type $dbx
     * @param type $idliquidacion
     * @param type $expediente
     * @param type $tiporegistro
     * @param type $tipotramite
     * @param type $usuariocontrol
     * @param type $emailusuariocontrol
     * @param type $nombreusuariocontrol
     * @param type $celularusuariocontrol
     * @return type
     */
    public static function generarAlertaSiprefTemprana($dbx = null, $idliquidacion = '', $expediente = '', $tiporegistro = '', $tipotramite = '', $usuariocontrol = '', $emailusuariocontrol = '', $nombreusuariocontrol = '', $celularusuariocontrol = '', $ipcliente = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarAlertasSipref.php';
        return \funcionesRegistrales_generarAlertasSipref::generarAlertaSiprefTemprana($dbx, $idliquidacion, $expediente, $tiporegistro, $tipotramite, $usuariocontrol, $emailusuariocontrol, $nombreusuariocontrol, $celularusuariocontrol, $ipcliente);
    }

    /**
     *
     * @param type $mysqli
     * @param type $pathsalida
     * @return type
     */
    public static function powerFileReportarDevolutivo($mysqli, $pathsalida)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_powerFileReportarDevolutivo.php';
        return \funcionesRegistrales_powerFileReportarDevolutivo::powerFileReportarDevolutivo($mysqli, $pathsalida);
    }

    /**
     *
     * @param type $mysqli
     * @param type $pathsalida
     * @return type
     */
    public static function powerFileReportarDesistimiento($mysqli, $pathsalida)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_powerFileReportarDesistimiento.php';
        return \funcionesRegistrales_powerFileReportarDesistimiento::powerFileReportarDesistimiento($mysqli, $pathsalida);
    }

    /**
     *
     * @param type $mysqli
     * @param type $cb
     * @param type $rec
     * @param type $usu
     * @param type $fec
     * @param type $hor
     * @param type $sed
     * @return type
     */
    public static function powerFileReportarReingreso($mysqli, $cb, $rec, $usu, $fec, $hor, $sed)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_powerFileReportarReingreso.php';
        return \funcionesRegistrales_powerFileReportarReingreso::powerFileReportarReingreso($mysqli, $cb, $rec, $usu, $fec, $hor, $sed);
    }

    /**
     *
     * @param type $mysqli
     * @return type
     */
    public static function powerFileGenerarSecuenciaDesistimiento($mysqli)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_powerFileGenerarSecuenciaDesistimiento.php';
        return \funcionesRegistrales_powerFileGenerarSecuenciaDesistimiento::powerFileGenerarSecuenciaDesistimiento($mysqli);
    }

    public static function evaluarArchivadorAsentarRecibo($idserv)
    {
        $archivador = '01';
        if (substr($idserv, 0, 6) == '010101') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010102') {
            $archivador = '02';
        }
        if (substr($idserv, 0, 6) == '010103') {
            $archivador = '03';
        }
        if (substr($idserv, 0, 6) == '010201') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010202') {
            $archivador = '01';
        }
        if (
            substr($idserv, 0, 6) == '010203' ||
            substr($idserv, 0, 6) == '010204' ||
            substr($idserv, 0, 6) == '010205' ||
            substr($idserv, 0, 6) == '010206'
        ) {
            $archivador = '02';
        }
        if (
            substr($idserv, 0, 6) >= '010301' &&
            substr($idserv, 0, 6) <= '010322'
        ) {
            $archivador = '01';
        }
        if (
            substr($idserv, 0, 6) >= '010351' &&
            substr($idserv, 0, 6) <= '010355'
        ) {
            $archivador = '03';
        }
        if (substr($idserv, 0, 8) == '01020208') {
            $archivador = '03';
        }
        if (substr($idserv, 0, 6) == '010501') {
            $archivador = '01';
        }
        if (substr($idserv, 0, 6) == '010502') {
            $archivador = '02';
        }
        if (substr($idserv, 0, 6) == '010503') {
            $archivador = '03';
        }
        return $archivador;
    }

    public static function funcValidarFormularioProponente1510($mysqli, $liq, $datos, $codigoEmpresa, $tipotramite = '', $funcion = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php';
        set_error_handler('myErrorHandler');

        //
        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        } else {
            $cerrarMysqli = 'no';
        }

        //
        $arrErrores = array();
        $counterr = 0;
        $arrErroresCambioDomicilio = array();
        $arrErroresDatosBasicos = array();
        $arrErroresPersoneriaJuridica = array();
        $arrErroresUbicacion = array();
        $arrErroresRepresentantesLegales = array();
        $arrErroresFacultades = array();
        $arrErroresClasificacion = array();
        $arrErroresExperiencia = array();
        $arrErroresSitControl = array();
        $arrErroresInformacionFinanciera = array();
        $arrErroresInformacionFinanciera399a = array();
        $arrErroresInformacionFinanciera399b = array();

        if ($tipotramite == 'cambiodomicilioproponente') {
            $arrErroresCambioDomicilio = \funcionesRegistrales::funcValidarCambioDomicilio($mysqli, $datos, $codigoEmpresa);
        }

        if ($tipotramite != 'actualizacionproponente399') {
            $arrErroresDatosBasicos = \funcionesRegistrales::funcValidarDatosBasicos1510($mysqli, $liq, $datos, $codigoEmpresa);
            $arrErroresPersoneriaJuridica = \funcionesRegistrales::funcValidarPersoneriaJuridica1510($mysqli, $liq, $datos, $codigoEmpresa);
            $arrErroresUbicacion = \funcionesRegistrales::funcValidarUbicacion($mysqli, $datos, $codigoEmpresa);
            $arrErroresRepresentantesLegales = \funcionesRegistrales::funcValidarRepresentantesLegales($mysqli, $datos, $codigoEmpresa);
            $arrErroresFacultades = \funcionesRegistrales::funcValidarFacultades($mysqli, $datos, $codigoEmpresa);
            $arrErroresClasificacion = \funcionesRegistrales::funcValidarClasificacion1510($mysqli, $datos, $codigoEmpresa);
            $arrErroresExperiencia = \funcionesRegistrales::funcValidarExperiencia1510($mysqli, $liq, $datos, $codigoEmpresa);
            $arrErroresSitControl = \funcionesRegistrales::funcValidarSitControl1510($mysqli, $liq, $datos, $codigoEmpresa);
            if ($tipotramite != 'actualizacionproponente') {
                $arrErroresInformacionFinanciera = \funcionesRegistrales::funcValidarInformacionFinanciera1510($mysqli, $liq, $datos, $codigoEmpresa);
                $arrErroresInformacionFinanciera399a = \funcionesRegistrales::funcValidarInformacionFinanciera399a($mysqli, $liq, $datos, $codigoEmpresa);
                $arrErroresInformacionFinanciera399b = \funcionesRegistrales::funcValidarInformacionFinanciera399b($mysqli, $liq, $datos, $codigoEmpresa);
            }
        }

        if ($tipotramite == 'actualizacionproponente399') {
            if (isset($datos["inffin399a_fechacorte"]) && $datos["inffin399a_fechacorte"] != '' && $datos["inffin399a_pregrabado"] != 'si') {
                $arrErroresInformacionFinanciera399a = \funcionesRegistrales::funcValidarInformacionFinanciera399a($mysqli, $liq, $datos, $codigoEmpresa);
            }
            if (isset($datos["inffin399b_fechacorte"]) && $datos["inffin399b_fechacorte"] != '' && $datos["inffin399b_pregrabado"] != 'si') {
                $arrErroresInformacionFinanciera399b = \funcionesRegistrales::funcValidarInformacionFinanciera399b($mysqli, $liq, $datos, $codigoEmpresa);
            }
        }

        if (!empty($arrErroresCambioDomicilio)) {
            foreach ($arrErroresCambioDomicilio as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresDatosBasicos)) {
            foreach ($arrErroresDatosBasicos as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresPersoneriaJuridica)) {
            foreach ($arrErroresPersoneriaJuridica as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresUbicacion)) {
            foreach ($arrErroresUbicacion as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresRepresentantesLegales)) {
            foreach ($arrErroresRepresentantesLegales as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresFacultades)) {
            foreach ($arrErroresFacultades as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresClasificacion)) {
            foreach ($arrErroresClasificacion as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresSitControl)) {
            foreach ($arrErroresSitControl as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresExperiencia)) {
            foreach ($arrErroresExperiencia as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresInformacionFinanciera)) {
            foreach ($arrErroresInformacionFinanciera as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresInformacionFinanciera399a)) {
            foreach ($arrErroresInformacionFinanciera399a as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresInformacionFinanciera399b)) {
            foreach ($arrErroresInformacionFinanciera399b as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        //
        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }

        return $arrErrores;
    }

    public static function funcValidarFormularioProponenteSoportes1510($mysqli, $liq, $datos, $codigoEmpresa, $tipotramite = '', $funcion = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistralesCalculos.php';

        if ($mysqli == null) {
            $mysqli = conexionMysqliApi();
            $cerrarMysqli = 'si';
        } else {
            $cerrarMysqli = 'no';
        }
        //
        $arrErrores = array();
        $counterr = 0;
        $arrErroresCambioDomicilio = array();
        $arrErroresDatosBasicos = array();
        $arrErroresPersoneriaJuridica = array();
        $arrErroresUbicacion = array();
        $arrErroresRepresentantesLegales = array();
        $arrErroresFacultades = array();
        $arrErroresClasificacion = array();
        $arrErroresExperiencia = array();
        $arrErroresSitControl = array();
        $arrErroresInformacionFinanciera = array();
        $arrErroresInformacionFinanciera399a = array();
        $arrErroresInformacionFinanciera399b = array();

        if ($tipotramite != 'actualizacionproponente399') {
            $arrErroresDatosBasicos = \funcionesRegistrales::funcValidarDatosBasicosSoportes1510($mysqli, $liq, $datos, $codigoEmpresa);
            $arrErroresPersoneriaJuridica = \funcionesRegistrales::funcValidarPersoneriaJuridicaSoportes1510($mysqli, $liq, $datos, $codigoEmpresa);
            $arrErroresExperiencia = \funcionesRegistrales::funcValidarExperienciaSoportes1510($mysqli, $liq, $datos, $codigoEmpresa);
            $arrErroresSitControl = \funcionesRegistrales::funcValidarSitControlSoportes1510($mysqli, $liq, $datos, $codigoEmpresa);
            if ($tipotramite != 'actualizacionproponente') {
                $arrErroresInformacionFinanciera = \funcionesRegistrales::funcValidarInformacionFinancieraSoportes1510($mysqli, $liq, $datos, $codigoEmpresa);
                if ($datos["inffin399a_pregrabado"] != 'si') {
                    if ($datos["inffin399a_fechacorte"] != '') {
                        $arrErroresInformacionFinanciera399a = \funcionesRegistrales::funcValidarInformacionFinancieraSoportes399a($mysqli, $liq, $datos, $codigoEmpresa);
                    }
                }
                if ($datos["inffin399b_pregrabado"] != 'si') {
                    if ($datos["inffin399b_fechacorte"] != '') {
                        $arrErroresInformacionFinanciera399b = \funcionesRegistrales::funcValidarInformacionFinancieraSoportes399b($mysqli, $liq, $datos, $codigoEmpresa);
                    }
                }
            }
        }

        if ($tipotramite == 'actualizacionproponente399') {
            if ($datos["inffin399a_pregrabado"] != 'si') {
                if ($datos["inffin399a_fechacorte"] != '') {
                    $arrErroresInformacionFinanciera399a = \funcionesRegistrales::funcValidarInformacionFinancieraSoportes399a($mysqli, $liq, $datos, $codigoEmpresa);
                }
            }
            if ($datos["inffin399b_pregrabado"] != 'si') {
                if ($datos["inffin399b_fechacorte"] != '') {
                    $arrErroresInformacionFinanciera399b = \funcionesRegistrales::funcValidarInformacionFinancieraSoportes399b($mysqli, $liq, $datos, $codigoEmpresa);
                }
            }
        }


        if (!empty($arrErroresCambioDomicilio)) {
            foreach ($arrErroresCambioDomicilio as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresDatosBasicos)) {
            foreach ($arrErroresDatosBasicos as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresPersoneriaJuridica)) {
            foreach ($arrErroresPersoneriaJuridica as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresUbicacion)) {
            foreach ($arrErroresUbicacion as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresRepresentantesLegales)) {
            foreach ($arrErroresRepresentantesLegales as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresFacultades)) {
            foreach ($arrErroresFacultades as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresSitControl)) {
            foreach ($arrErroresSitControl as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresExperiencia)) {
            foreach ($arrErroresExperiencia as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresInformacionFinanciera)) {
            foreach ($arrErroresInformacionFinanciera as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresInformacionFinanciera399a)) {
            foreach ($arrErroresInformacionFinanciera399a as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if (!empty($arrErroresInformacionFinanciera399b)) {
            foreach ($arrErroresInformacionFinanciera399b as $err) {
                $counterr++;
                $arrErrores[$counterr] = $err;
            }
        }

        if ($cerrarMysqli == 'si') {
            $mysqli->close();
        }

        return $arrErrores;
    }

    public static function funcValidarDatosBasicos1510($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        $incon = 0;
        $arrIncon = array();

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Datos b&aacute;sicos: La organizacion jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
        }

        if (ltrim($datos["nit"], '0') == '') {
            $incon++;
            $arrIncon[$incon] = 'Datos b&aacute;sicos: El campo Nit debe ser ser num&eacute;rico mayor a ceros';
        }

        if (validarDv($datos["nit"]) === false) {
            $incon++;
            $arrIncon[$incon] = 'Datos b&aacute;sicos - Nit incorrecto - d&iacute;gito de verificaci&oacute;n';
        }

        if ($datos["organizacion"] == '01') {

            if (trim($datos["idtipoidentificacion"]) == '') {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: Debe indicar un tipo de Identificaci&oacute;n';
            }
            if (ltrim($datos["identificacion"], '0') == '') {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: Debe indicar un N&uacute;mero de Identificaci&oacute;n';
            }
        } else {

            if (ltrim($datos["idpaisidentificacion"], '0') != '') {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: No debe reportar un pa&iacute;s de nacionalidad (cuando se trate de persona jur&iacute;dica)';
            }
        }

        if ($datos["organizacion"] == '01') {

            if (trim($datos["ape1"]) == '') {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: Debe indicar el primer apellido';
            }
            if (trim($datos["nom1"]) == '') {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: El primer nombre no debe estar en blancos';
            }
        } else {

            if ((trim($datos["ape1"]) != '') ||
                (trim($datos["ape2"]) != '') ||
                (trim($datos["nom1"]) != '') ||
                (trim($datos["nom2"]) != '')
            ) {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: No debe reportar apellidos y/o nombres cuando se trate de personas jur&iacute;dicas';
            }
        }

        if ($datos["organizacion"] != '01') {
            if (trim($datos["nombre"]) == '') {
                $incon++;
                $arrIncon[$incon] = 'Datos b&aacute;sicos: Debe indicar la raz&oacute;n social de la persona jur&iacute;dica';
            }
        }

        if (trim($datos["tamanoempresa"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Datos b&aacute;sicos: No ha seleccionado el tama&ntilde;o de la empresa';
        }

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: La organizacion jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
        }

        //
        return $arrIncon;
    }

    public static function funcValidarDatosBasicosSoportes1510($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $incon = 0;
        $arrIncon = array();

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Datos b&aacute;sicos: La organizacion jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
        }

        if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente') {
            if ($datos["organizacion"] == '01') {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'datosbasicos' and 'datosbasicos-zzzz')", "idanexo");
                if ($arrTem === false || empty($arrTem)) {
                    $incon++;
                    $arrIncon[$incon] = "Datos B&aacute;sicos - No se encontraron soportes relacionados con los datos b&aacute;sicos";
                }
            }
        }

        //
        return $arrIncon;
    }

    public static function funcValidarClasificacion1510($mysqli, $datos, $codigoEmpresa)
    {
        $incon = 0;
        $arrIncon = array();

        if (!is_array($datos["clasi1510"]) || empty($datos["clasi1510"])) {
            $incon++;
            $arrIncon[$incon] = '(1) Los c&oacute;digos UNSPSC de la clasificaci&oacute;n no han sido digitados';
        } else {
            $okEncontro = 0;
            foreach ($datos["clasi1510"] as $c) {
                if (trim($c) != '') {
                    $okEncontro++;
                }
            }
            if ($okEncontro == 0) {
                $incon++;
                $arrIncon[$incon] = '(2) Los c&oacute;digos UNSPSC de la clasificaci&oacute;n no han sido digitados';
            }
        }

        return $arrIncon;
    }

    public static function funcValidarCambioDomicilio($mysqli, $datos, $codigoEmpresa)
    {
        $incon = 0;
        $arrIncon = array();
        if (trim($datos["cambidom_idmunicipioorigen"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Cambio de domicilio: Municipio origen no debe estar en blancos';
        }
        if (trim($datos["cambidom_idmunicipiodestino"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Cambio de domicilio: Municipio destino no debe estar en blancos';
        }
        if (trim($datos["cambidom_fechaultimainscripcion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Cambio de domicilio: Fecha de la &uacute;ltima inscripci&oacute;n no debe estar en blancos';
        }
        if (trim($datos["cambidom_fechaultimarenovacion"]) != '') {
            if ($datos["cambidom_fechaultimarenovacion"] <= $datos["cambidom_fechaultimainscripcion"]) {
                $incon++;
                $arrIncon[$incon] = 'Cambio de domicilio: Fecha de ulatima renovaci&oacute;n no debe ser superior a la fecha de la &uacute;ltima inscripci&oacute;n';
            }
        }

        return $arrIncon;
    }

    public static function funcValidarExperiencia1510($mysqli, $idliq, $datos, $codigoEmpresa, $validar = 'S')
    {

        $arrContratos = array();
        $incon = 0;
        $arrIncon = array();

        if (count($datos["exp1510"]) > 0) {
            foreach ($datos["exp1510"] as $cnt) {
                if (isset($arrContratos[$cnt["secuencia"]])) {
                    $incon++;
                    $arrIncon[$incon] = "Experiencia - El contrato con secuencia No.  " . $cnt["secuencia"] . " ha sido reportado m&aacute;s de una vez.";
                } else {
                    $arrContratos[$cnt["secuencia"]] = $cnt["secuencia"];
                }
            }
        }
        return $arrIncon;
    }

    public static function funcValidarExperienciaSoportes1510($mysqli, $idliq, $datos, $codigoEmpresa, $validar = 'S')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $incon = 0;
        $arrIncon = array();
        if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente') {
            if ($datos["organizacion"] == '01') {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'exp1510' and 'exp1510-zzzz')", "idanexo");
                if ($arrTem === false || empty($arrTem)) {
                    $incon++;
                    $arrIncon[$incon] = "Experiencia - No se han encontrado soportes de la experiencia reportada";
                }
            }
        }
        return $arrIncon;
    }

    public static function funcValidarInformacionFinanciera1510($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $incon = 0;
        $arrIncon = array();

        // Valida datos reportados
        if (ltrim($datos["inffin1510_fechacorte"], '0') == '') {
            $incon++;
            $arrIncon[$incon] = "Informaci&oacute;n financiera: Fecha de corte de la informaci&oacute;n financiera ha sido reportada en blancos";
        }

        if ($datos["inffin1510_fechacorte"] >= date("Ymd")) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: La fecha de corte de los estados debe ser inferior a la fecha actual';
        }

        if (!is_numeric($datos["inffin1510_actcte"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Activo corriente debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_fijnet"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Activo fijo neto debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_acttot"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Activo total debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_pascte"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: pasivo Corriente debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_pastot"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: pasivo total debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_patnet"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Patrimonio neto debe ser num&eacute;rico ';
        }

        $dif = floatval($datos["inffin1510_acttot"]) - floatval($datos["inffin1510_pastot"]) - floatval($datos["inffin1510_patnet"]);
        if (abs($dif) > 0.005) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: La ecucaci&oacute;n contable Act Tot = PasTot + PatNet no se est&aacute; cumpliendo : ' . $datos["inffin1510_acttot"] . ' = ' . $datos["inffin1510_pastot"] . ' + ' . $datos["inffin1510_patnet"] . ' : Diferencia : ' . $dif;
        }

        if (!is_numeric($datos["inffin1510_utiope"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Utilidad operacional debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_utinet"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Utilidad neta debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_cosven"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Costo de ventas debe ser num&eacute;rico ';
        }

        if (!is_numeric($datos["inffin1510_gasint"])) {
            $incon++;
            $arrIncon[$incon] = 'Informaci&oacute;n financiera: Gastos por intereses debe ser num&eacute;rico ';
        }


        // Razon de cobertura
        $razcob = 'INDEFINIDO';
        if ($datos["inffin1510_gasint"] != 0) {
            $razcob = floatval($datos["inffin1510_utiope"]) / floatval($datos["inffin1510_gasint"]);
            $dif = $razcob - floatval($datos["inffin1510_razcob"]);
            if (abs($dif) > 0.05) {
                $incon++;
                $arrIncon[$incon] = '(1) Informaci&oacute;n financiera: La raz&oacute;n de cobertura reportada no concuerda con el c&aacute;lculo de acuerdo con sus ';
                $arrIncon[$incon] .= 'componentes - Reportado : ' . $datos["inffin1510_razcob"] . ', Calculado: ' . $razcob . '; ';
                $arrIncon[$incon] .= 'Utilidad : ' . $datos["inffin1510_utiope"] . ', gastos por intereses: ' . $datos["inffin1510_gasint"] . '.';
            }
        } else {
            if ($datos["inffin1510_razcob"] == 0) {
                $datos["inffin1510_razcob"] = 'INDEFINIDO';
            }
            if ($datos["inffin1510_razcob"] == 998) {
                $datos["inffin1510_razcob"] = 'INDEFINIDO';
            }
            if ($razcob != $datos["inffin1510_razcob"]) {
                $incon++;
                $arrIncon[$incon] = '(2) Informaci&oacute;n financiera: La raz&oacute;n de cobertura reportada no concuerda con el c&aacute;lculo de acuerdo con sus componentes - Reportado : ' . $datos["inffin1510_razcob"] . ', Calculado: ' . $razcob;
            }
        }

        // retorna arreglo de inconsistencias
        return $arrIncon;
    }

    public static function funcValidarInformacionFinanciera399a($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $incon = 0;
        $arrIncon = array();

        // Valida datos reportados
        if (ltrim($datos["inffin399a_fechacorte"], '0') != '' && $datos["inffin399a_pregrabado"] != 'si') {
            if ($datos["inffin399a_fechacorte"] >= date("Ymd")) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: La fecha de corte de los estados debe ser inferior a la fecha actual';
            }

            if (!is_numeric($datos["inffin399a_actcte"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Activo corriente debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_actnocte"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Activo no Corriente debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_acttot"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Activo total debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_pascte"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: pasivo Corriente debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_pastot"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: pasivo total debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_patnet"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Patrimonio neto debe ser num&eacute;rico ';
            }

            $dif = floatval($datos["inffin399a_acttot"]) - floatval($datos["inffin399a_pastot"]) - floatval($datos["inffin399a_patnet"]);
            if (abs($dif) > 0.005) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: La ecucaci&oacute;n contable Act Tot = PasTot + PatNet no se est&aacute; cumpliendo : ' . $datos["inffin399a_acttot"] . ' = ' . $datos["inffin399a_pastot"] . ' + ' . $datos["inffin399a_patnet"] . ' : Diferencia : ' . $dif;
            }

            if (!is_numeric($datos["inffin399a_utiope"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Utilidad operacional debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_utinet"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Utilidad neta debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_cosven"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Costo de ventas debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399a_gasint"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399a: Gastos por intereses debe ser num&eacute;rico ';
            }


            // Razon de cobertura
            $razcob = 'INDEFINIDO';
            if ($datos["inffin399a_gasint"] != 0) {
                $razcob = floatval($datos["inffin399a_utiope"]) / floatval($datos["inffin399a_gasint"]);
                $dif = $razcob - floatval($datos["inffin399a_razcob"]);
                if (abs($dif) > 0.05) {
                    $incon++;
                    $arrIncon[$incon] = '(1) Informaci&oacute;n financiera 399a: La raz&oacute;n de cobertura reportada no concuerda con el c&aacute;lculo de acuerdo con sus ';
                    $arrIncon[$incon] .= 'componentes - Reportado : ' . $datos["inffin399a_razcob"] . ', Calculado: ' . $razcob . '; ';
                    $arrIncon[$incon] .= 'Utilidad : ' . $datos["inffin399a_utiope"] . ', gastos por intereses: ' . $datos["inffin399a_gasint"] . '.';
                }
            } else {
                if ($datos["inffin399a_razcob"] == 0) {
                    $datos["inffin399a_razcob"] = 'INDEFINIDO';
                }
                if ($datos["inffin399a_razcob"] == 998) {
                    $datos["inffin399a_razcob"] = 'INDEFINIDO';
                }
                if ($razcob != $datos["inffin399a_razcob"]) {
                    $incon++;
                    $arrIncon[$incon] = '(2) Informaci&oacute;n financiera 399a: La raz&oacute;n de cobertura reportada no concuerda con el c&aacute;lculo de acuerdo con sus componentes - Reportado : ' . $datos["inffin399a_razcob"] . ', Calculado: ' . $razcob;
                }
            }
        }

        // retorna arreglo de inconsistencias
        return $arrIncon;
    }

    public static function funcValidarInformacionFinanciera399b($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $incon = 0;
        $arrIncon = array();

        // Valida datos reportados
        if (ltrim($datos["inffin399b_fechacorte"], '0') != '' && $datos["inffin399b_pregrabado"] != 'si') {
            if ($datos["inffin399b_fechacorte"] >= date("Ymd")) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: La fecha de corte de los estados debe ser inferior a la fecha actual';
            }

            if (!is_numeric($datos["inffin399b_actcte"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Activo corriente debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_actnocte"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Activo no Corriente debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_acttot"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Activo total debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_pascte"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: pasivo Corriente debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_pastot"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: pasivo total debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_patnet"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Patrimonio neto debe ser num&eacute;rico ';
            }

            $dif = floatval($datos["inffin399b_acttot"]) - floatval($datos["inffin399b_pastot"]) - floatval($datos["inffin399b_patnet"]);
            if (abs($dif) > 0.005) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: La ecucaci&oacute;n contable Act Tot = PasTot + PatNet no se est&aacute; cumpliendo : ' . $datos["inffin399b_acttot"] . ' = ' . $datos["inffin399b_pastot"] . ' + ' . $datos["inffin399b_patnet"] . ' : Diferencia : ' . $dif;
            }

            if (!is_numeric($datos["inffin399b_utiope"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Utilidad operacional debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_utinet"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Utilidad neta debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_cosven"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Costo de ventas debe ser num&eacute;rico ';
            }

            if (!is_numeric($datos["inffin399b_gasint"])) {
                $incon++;
                $arrIncon[$incon] = 'Informaci&oacute;n financiera 399b: Gastos por intereses debe ser num&eacute;rico ';
            }


            // Razon de cobertura
            $razcob = 'INDEFINIDO';
            if ($datos["inffin399b_gasint"] != 0) {
                $razcob = floatval($datos["inffin399b_utiope"]) / floatval($datos["inffin399b_gasint"]);
                $dif = $razcob - floatval($datos["inffin399b_razcob"]);
                if (abs($dif) > 0.05) {
                    $incon++;
                    $arrIncon[$incon] = '(1) Informaci&oacute;n financiera 399b: La raz&oacute;n de cobertura reportada no concuerda con el c&aacute;lculo de acuerdo con sus ';
                    $arrIncon[$incon] .= 'componentes - Reportado : ' . $datos["inffin399b_razcob"] . ', Calculado: ' . $razcob . '; ';
                    $arrIncon[$incon] .= 'Utilidad : ' . $datos["inffin399b_utiope"] . ', gastos por intereses: ' . $datos["inffin399b_gasint"] . '.';
                }
            } else {
                if ($datos["inffin399b_razcob"] == 0) {
                    $datos["inffin399b_razcob"] = 'INDEFINIDO';
                }
                if ($datos["inffin399b_razcob"] == 998) {
                    $datos["inffin399b_razcob"] = 'INDEFINIDO';
                }
                if ($razcob != $datos["inffin399b_razcob"]) {
                    $incon++;
                    $arrIncon[$incon] = '(2) Informaci&oacute;n financiera 399a: La raz&oacute;n de cobertura reportada no concuerda con el c&aacute;lculo de acuerdo con sus componentes - Reportado : ' . $datos["inffin399b_razcob"] . ', Calculado: ' . $razcob;
                }
            }
        }

        // retorna arreglo de inconsistencias
        return $arrIncon;
    }

    public static function funcValidarInformacionFinancieraSoportes1510($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $_SESSION["generales"]["codigoempresa"] = $codigoEmpresa;
        $incon = 0;
        $arrIncon = array();
        // Valida soportes siempre y cuando se trate de una inscripci&oacute;n o una renovaci&oacute;n.
        if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente') {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'inffin-' and 'inffin-zzzzzz')", "idanexo");
            if ($arrTem === false || empty($arrTem)) {
                $incon++;
                $arrIncon[$incon] = "Informaci&oacute;n financiera - No se encontraron soportes documentales para la informaci&oacute;n financiera";
            }
        }
        return $arrIncon;
    }

    public static function funcValidarInformacionFinancieraSoportes399a($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $_SESSION["generales"]["codigoempresa"] = $codigoEmpresa;
        $incon = 0;
        $arrIncon = array();
        if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente' || $datos["tipotramite"] == 'actualizacionproponente399') {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'inffin399a-' and 'inffin399a-zzzzz')", "idanexo");
            if ($arrTem === false || empty($arrTem)) {
                $incon++;
                $arrIncon[$incon] = "Informaci&oacute;n financiera 399a - No se encontraron soportes documentales para la informaci&oacute;n financiera";
            }
        }
        return $arrIncon;
    }

    public static function funcValidarInformacionFinancieraSoportes399b($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $_SESSION["generales"]["codigoempresa"] = $codigoEmpresa;
        $incon = 0;
        $arrIncon = array();
        if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente' || $datos["tipotramite"] == 'actualizacionproponente399') {
            $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'inffin399b-' and 'inffin399b-zzzzz')", "idanexo");
            if ($arrTem === false || empty($arrTem)) {
                $incon++;
                $arrIncon[$incon] = "Informaci&oacute;n financiera 399b - No se encontraron soportes documentales para la informaci&oacute;n financiera";
            }
        }
        return $arrIncon;
    }

    public static function funcValidarFacultades($mysqli, $datos, $codigoEmpresa)
    {
        $incon = 0;
        $arrIncon = array();

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Facultades Representantes Legales: La organizacion jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
            exit();
        }

        if ($datos["organizacion"] != '01') {
            if ($datos["organizacion"] != '11') {
                if (trim($datos["facultades"]) == '') {
                    $incon++;
                    $arrIncon[$incon] = 'Facultades Representantes Legales: Debe indicar las facultades de la representaci&oacute;n legal';
                }
            }
        } else {
            $datos["facultades"] = '';
        }

        return $arrIncon;
    }

    public static function funcValidarPersoneriaJuridica1510($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        $incon = 0;
        $arrIncon = array();

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: La organizacion jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
        }

        if ($datos["organizacion"] != '01') {
            if (trim($datos["matricula"] == '')) {
                if (trim($datos["idtipodocperjur"]) == '') {
                    $incon++;
                    $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: Debe indicar el tipo de documento mediante el cual se otorga la Personer&iacute;a jur&iacute;dica';
                }
                if (ltrim($datos["fecdocperjur"], '0') == '') {
                    $incon++;
                    $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: Debe indicar la fecha del documento mediante el cual se otorga la Personer&iacute;a jur&iacute;dica';
                }
                if (ltrim($datos["fechaconstitucion"], '0') == '') {
                    $incon++;
                    $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: Debe indicar la fecha de constituci&oacute;n';
                }
                if (ltrim($datos["fechavencimiento"], '0') != '') {
                    if ($datos["fechavencimiento"] <= date("Ymd")) {
                        $incon++;
                        $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: La fecha de vencimiento (duraci&oacute;n) de la persona jur&iacute;dica no debe ser inferior a la fecha de diligencimiento del formulario ';
                    }
                }
            }
        } else {
            if ((trim($datos["idtipodocperjur"]) != '') ||
                (ltrim($datos["numdocperjur"], '0') != '') ||
                (ltrim($datos["fecdocperjur"], '0') != '') ||
                (trim($datos["origendocperjur"]) != '') ||
                (ltrim($datos["fechaconstitucion"], '0') != '') ||
                (ltrim($datos["fechavencimiento"], '0') != '')
            ) {
                $incon++;
                $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: No debe reportar ning&uacute;n dato relacionado con la Personer&iacute;a jur&iacute;dica (cuando se trate de personas naturales)';
            }
        }

        return $arrIncon;
    }

    public static function funcValidarPersoneriaJuridicaSoportes1510($mysqli, $idliq, $datos, $codigoEmpresa)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        $incon = 0;
        $arrIncon = array();

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Personer&iacute;a jur&iacute;dica: La organizacion jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
        }

        if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente') {
            if ($datos["organizacion"] != '01') {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'perjur' and 'perjur-zzzz')", "idanexo");
                if ($arrTem === false || empty($arrTem)) {
                    $incon++;
                    $arrIncon[$incon] = "Personer&iacute;a Jur&iacute;dica - No se han incluido soportes de la personer&iacute;a jur&iacute;dica.";
                }
            }
        }


        return $arrIncon;
    }

    public static function funcValidarRepresentantesLegales($mysqli, $datos, $codigoEmpresa)
    {

        $incon = 0;
        $arrIncon = array();

        if (trim($datos["organizacion"]) == '') {
            $incon++;
            $arrIncon[$incon] = 'Representantes Legales: La organizaci&oacute;n jur&iacute;dica es obligatoria para poder continuar con la validaci&oacute;n';
            return $arrIncon;
        }

        if ($datos["organizacion"] != '01') {
            if (empty($datos["representanteslegales"])) {
                $incon++;
                $arrIncon[$incon] = 'Representantes Legales (1): Debe indicar al menos los datos de un representante legal';
            } else {
                $sinRepresentantes = '';
                foreach ($datos["representanteslegales"] as $r) {
                    if ((ltrim($r["idtipoidentificacionrepleg"], '0') != '') &&
                        (ltrim($r["identificacionrepleg"], '0') != '') &&
                        (trim($r["nombrerepleg"]) != '')
                    ) {
                        $sinRepresentantes = 'no';
                    }
                }
                if ($sinRepresentantes != 'no') {
                    $incon++;
                    $arrIncon[$incon] = 'Representantes Legales (2): Debe indicar al menos los datos de un representante legal';
                }
            }
        } else {
            $datos["representanteslegales"] = array();
        }

        return $arrIncon;
    }

    public static function funcValidarSitControl1510($mysqli, $idliq, $datos, $codigoEmpresa, $validar = 'S')
    {
        $incon = 0;
        $arrIncon = array();
        return $arrIncon;
    }

    public static function funcValidarSitControlSoportes1510($mysqli, $idliq, $datos, $codigoEmpresa, $validar = 'S')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';

        $incon = 0;
        $arrIncon = array();
        if (!empty($datos["sitcontrol"])) {
            if ($datos["tipotramite"] == 'inscripcionproponente' || $datos["tipotramite"] == 'renovacionproponente') {
                $arrTem = retornarRegistrosMysqliApi($mysqli, 'mreg_anexos_liquidaciones', "idliquidacion=" . $idliq . " and (identificador between 'sitcontrol-' and 'sitcontrol-zzzzz')", "idanexo");
                if ($arrTem === false || empty($arrTem)) {
                    $incon++;
                    $arrIncon[$incon] = "Situaciones de control - No se encontr&oacute; la declaraci&oacute;n del representante legal en relaci&oacute;n con las situaciones de control y los grupos empresariales";
                }
            }
        }
        return $arrIncon;
    }

    public static function funcValidarUbicacion($mysqli, $datos, $codigoEmpresa)
    {

        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        $incon = 0;
        $arrIncon = array();

        if (strlen(trim($datos["dircom"])) < 8) {
            $arrIncon[] = 'Ubicaci&oacute;n comercial: La direcci&oacute;n de ubicaci&oacute;n comercial parece estar err&oacute;nea';
        }

        if (ltrim($datos["muncom"], '0') == '') {
            $arrIncon[] = 'Ubicaci&oacute;n comercial: El municipio de ubicaci&oacute;n comercial parece estar incorrecto';
        }

        if ((ltrim($datos["telcom1"], '0') == '') || ((strlen($datos["telcom1"]) != 7) && (strlen($datos["telcom1"]) != 10))) {
            $arrIncon[] = 'Ubicaci&oacute;n comercial: N&uacute;mero telef&oacute;nico 1 comercial parece estar incorrecto';
        }

        if (ltrim($datos["telcom2"], '0') != '') {
            if ((strlen($datos["telcom2"]) != 7) && (strlen($datos["telcom2"]) != 10)) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: N&uacute;mero telef&oacute;nico 2 comercial parece estar incorrecto';
            }
        }

        if (ltrim($datos["celcom"], '0') != '') {
            if ((strlen($datos["celcom"]) != 10) && (strlen($datos["celcom"]) != 7)) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: N&uacute;mero telef&oacute;nico 3 parece estar incorrecto';
            }
        }

        if (strlen(trim($datos["dirnot"])) < 8) {
            $arrIncon[] = 'Ubicaci&oacute;n de notificaci&oacute;n: La direcci&oacute;n de notificaci&oacute;n parece estar err&oacute;nea';
        }

        if (ltrim($datos["munnot"], '0') == '') {
            $arrIncon[] = 'Ubicaci&oacute;n de notificaci&oacute;n: El municipio de ubicaci&oacute;n para notificaci&oacute;n parece estar incorrecto';
        }

        if ((ltrim($datos["telnot"], '0') == '') || ((strlen($datos["telnot"]) != 7) && (strlen($datos["telnot"]) != 10))) {
            $arrIncon[] = 'Ubicaci&oacute;n de notificaci&oacute;n: N&uacute;mero telef&oacute;nico 1 para notificaci&oacute;n parece estar incorrecto';
        }

        if (ltrim($datos["telnot2"], '0') != '') {
            if ((strlen($datos["telnot2"]) != 7) && (strlen($datos["telnot2"]) != 10)) {
                $arrIncon[] = 'Ubicaci&oacute;n de notificaci&oacute;n: N&uacute;mero telef&oacute;nico 2 para notificaci&oacute;n parece estar incorrecto';
            }
        }

        if (ltrim($datos["celnot"], '0') != '') {
            if ((strlen($datos["celnot"]) != 10) && (strlen($datos["celnot"]) != 7)) {
                $arrIncon[] = 'Ubicaci&oacute;n de notificaci&oacute;n: N&uacute;mero telef&oacute;nico 3 de notificaci&oacute;n parece estar incorrecto';
            }
        }

        if (\funcionesRegistrales::validarMunicipioJurisdiccion($mysqli, $datos["muncom"]) === false) {
            $arrIncon[] = 'Ubicaci&oacute;n comercial: El municipio de ubicaci&oacute;n comercial (domicilio) no corresponde con un municipio permitido en la jurisdicci&oacute;n de la C&aacute;mara de Comercio';
        }

        if (\funcionesGenerales::validarPermitidosDireccion($datos["dircom"]) === false) {
            $arrIncon[] = 'Ubicaci&oacute;n comercial: La direcci&oacute;n comercial parece tener caract&eacute;res no permitidos';
        }

        if (trim($datos["dircom_tipovia"]) != '') {
            if (\funcionesGenerales::validarPermitidosDireccion($datos["dircom_tipovia"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: El tipo de v&iacute;a de la direcci&oacute;n comercial parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["dircom_numvia"]) != '') {
            if (\funcionesGenerales::validarNumerico($datos["dircom_numvia"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: El n&uacute;mero de la v&iacute;a de la direcci&oacute;n comercial parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["dircom_numcruce"]) != '') {
            if (\funcionesGenerales::validarNumerico($datos["dircom_numcruce"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: El n&uacute;mero cruce  de la direcci&oacute;n comercial parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["dircom_numplaca"]) != '') {
            if (\funcionesGenerales::validarNumerico($datos["dircom_numplaca"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: El n&uacute;mero de placa  de la direcci&oacute;n comercial parece tener caract&eacute;res no permitidos';
            }
        }

        if (\funcionesGenerales::validarPermitidosDireccion($datos["dircom_complemento"]) === false) {
            $arrIncon[] = 'Ubicaci&oacute;n comercial: El complemento  de la direcci&oacute;n comercial parece tener caract&eacute;res no permitidos';
        }

        if (\funcionesGenerales::validarPermitidosDireccion($datos["dirnot"]) === false) {
            $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: La direcci&oacute;n de notificaci&oacute;n parece tener caract&eacute;res no permitidos';
        }

        if (\funcionesGenerales::validarPermitidosDireccion($datos["dirnot_tipovia"]) === false) {
            $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: El tipo de v&iacute;a de la direcci&oacute;n de notificaci&oacute;n parece tener caract&eacute;res no permitidos';
        }

        if (trim($datos["dirnot_numvia"]) != '') {
            if (\funcionesGenerales::validarNumerico($datos["dirnot_numvia"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: El n&uacute;mero de v&iacute;a de la direcci&oacute;n de notificaci&oacute;n parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["dirnot_numcruce"]) != '') {
            if (\funcionesGenerales::validarNumerico($datos["dirnot_numcruce"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: El n&uacute;mero cruce  de la direcci&oacute;n de notificaci&oacute;n parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["dirnot_numplaca"]) != '') {
            if (\funcionesGenerales::validarNumerico($datos["dirnot_numplaca"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: El n&uacute;mero de placa  de la direcci&oacute;n de notificaci&oacute;n parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["dirnot_complemento"]) != '') {
            if (\funcionesGenerales::validarPermitidosDireccion($datos["dirnot_complemento"]) === false) {
                $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: El complemento  de la direcci&oacute;n de notificaci&oacute;n parece tener caract&eacute;res no permitidos';
            }
        }

        if (trim($datos["muncom"]) != '') {
            if (contarRegistrosMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $datos["muncom"] . "'") == 0) {
                $arrIncon[] = 'Ubicaci&oacute;n comercial: Municipio (domicilio) comercial no es un c&oacute;digo de municipio v&aacute;lido';
            }
        }

        if (trim($datos["munnot"]) != '') {
            if (contarRegistrosMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . $datos["munnot"] . "'") == 0) {
                $arrIncon[] = 'Ubicaci&oacute;n para notificaci&oacute;n: Municipio (domicilio) de notificaci&oacute;n no es un c&oacute;digo de municipio v&aacute;lido';
            }
        }

        if (trim($datos["emailcom"]) != '') {
            if (\funcionesGenerales::validarEmail($datos["emailcom"]) === false) {
                $arrIncon[] = 'Correo electr&oacute;nico comercial: Correo electr&oacute;nico incorrecto';
            }
        }

        if (trim($datos["emailnot"]) != '') {
            if (\funcionesGenerales::validarEmail($datos["emailnot"]) === false) {
                $arrIncon[] = 'Correo electr&oacute;nico para notificaci&oacute;n: Correo electr&oacute;nico incorrecto';
            }
        }

        return $arrIncon;
        exit();
    }

    public static function generarEmailNotificacionInscripcionSipref($dbx, $t)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarEmailNotificacionInscripcionSipref.php';
        return \funcionesRegistrales_generarEmailNotificacionInscripcionSipref::generarEmailNotificacionInscripcionSipref($dbx, $t);
    }

    public static function generarFormularioRenovacionRepositorio($dbx, $mat = '', $nameLog = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMercantil.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/unirPdfs.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';

        if ($nameLog == '') {
            $nameLog = 'generarFormularioRenovacionRepositorio_' . date("Ymd");
        }

        //
        $iForm = 0;
        $iEstab = 0;
        $iPpal = 0;

        //
        $_SESSION["formulario"]["datos"] = array();
        $_SESSION["formulario"]["datoshijos"] = array();
        $_SESSION["formulario"]["matriculashijos"] = array();
        $_SESSION["formulario"]["tipotramite"] = $_SESSION["tramite"]["tipotramite"];

        //
        $datPrincipal = array();
        $textoFirmadoDatPrincipal = '';
        $tramasha1DatPrincipal = '';

        $arrForms = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "idliquidacion");
        if (!empty($arrForms)) {
            foreach ($arrForms as $form) {

                $x = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $form["xml"]);
                \logApi::general2($nameLog, $_SESSION["tramite"]["numeroliquidacion"], 'Encontro datos para generar formulario de la matricula ' . $x["matricula"]);

                if (defined('TIPO_DOC_FOR_AFILIACION') && TIPO_DOC_FOR_AFILIACION != '') {
                    if ($_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula') {
                        if ($x["organizacion"] == '01' || $x["organizacion"] > '02') {
                            if ($x["afiliado"] == '1') {
                                //                                $datPrincipal = $x;
                            }
                        }
                    }
                }

                if (
                    $x["organizacion"] == '01' ||
                    ($x["organizacion"] > '02' && $x["categoria"] == '1')
                ) {
                    $_SESSION["formulario"]["datos"] = $x;
                    $_SESSION["formulario"]["datos"]["fechaultimamodificacion"] = $_SESSION["tramite"]["fechaultimamodificacion"];
                    $iPpal++;
                } else {
                    $iEstab++;
                    $_SESSION["formulario"]["datoshijos"][$iEstab] = $x;
                    $_SESSION["formulario"]["datoshijos"][$iEstab]["fechaultimamodificacion"] = $_SESSION["tramite"]["fechaultimamodificacion"];
                }
                $iForm++;
            }


            if ($iForm > 0) {

                if ($iPpal > 0) {

                    $det = '';

                    if (
                        $_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' || $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl'
                    ) {
                        $det = 'FORMULARIO DE RENOVACION - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    if (
                        $_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                        $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                        $_SESSION["tramite"]["tipotramite"] == 'matriculapjur'
                    ) {
                        $det = 'FORMULARIO DE MATRICULA - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    if (
                        $_SESSION["tramite"]["tipotramite"] == 'matriculaesadl'
                    ) {
                        $det = 'FORMULARIO DE INSCRIPCION AL REG-ESADL - NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                    }

                    //
                    if (!defined('FECHA_INICIAL_FORMULARIO_2020') || FECHA_INICIAL_FORMULARIO_2020 == '') {
                        $fecfor2020 = '20200803';
                    } else {
                        $fecfor2020 = FECHA_INICIAL_FORMULARIO_2020;
                    }

                    //
                    if (defined('ACTIVAR_IMPRESION_FORMULARIO_2023') && ACTIVAR_IMPRESION_FORMULARIO_2023 == 'SI') {
                        $name = armarPdfPrincipalNuevo2023Api($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                    } else {
                        $name = armarPdfPrincipalNuevo2020Api($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                    }
                    if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                        $name1 = armarPdfPrincipalNuevoAnosAnteriores1082Api($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                        unirPdfsApiV2(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                    }
                    if (!defined('ACTIVAR_IMPRESION_FORMULARIO_2023') || ACTIVAR_IMPRESION_FORMULARIO_2023 != 'SI') {
                        if ($_SESSION["formulario"]["datos"]["emprendimientosocial"] != '') {
                            $name2 = armarPdfFormatoEmpSoc($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"]);
                            unirPdfsApiV2(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name2), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                        }
                    }

                    $bandeja = '';
                    if (substr(ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), 0, 1) == 'S') {
                        $bandeja = '5.-REGESADL';
                    } else {
                        $bandeja = '4.-REGMER';
                    }

                    //
                    $id = \funcionesRegistrales::grabarAnexoRadicacion(
                        $dbx,
                        ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // Codigo barras
                        trim($_SESSION["tramite"]["numerorecibo"]), // Recibo
                        trim($_SESSION["tramite"]["numerooperacion"]), // Operacion
                        ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // Identificacion
                        trim($_SESSION["tramite"]["nombrecliente"]), // Nombre
                        '', // Acreedor
                        '', // Nombre acreedor
                        ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                        '', // N&uacute;mero de proponente
                        TIPO_DOC_FOR_MERCANTIL, // Tipo de documento
                        '', // N&uacute;mero del documento
                        $_SESSION["tramite"]["fecharecibo"],
                        '', // C&oacute;digo de origen
                        'EL COMERCIANTE',
                        '', // Clasificaci&oacute;n
                        '', // N&uacute;mero del contrato
                        '', // Idfuente
                        1, // versi&oacute;n
                        '', // Path
                        '1', // Estado
                        date("Ymd"), // fecha de escaneo o generaci&oacute;n
                        $_SESSION["generales"]["codigousuario"],
                        '', // Caja
                        '', // Libro
                        $det,
                        '', // Libro del registro
                        '', // Numero del registro
                        '', // Dupli
                        $bandeja, // Bandeja de registro
                        'S', // Soporte del recibo
                        '', // identificador
                        '503' // Tipo anexo
                    );

                    $dirx = date("Ymd");
                    $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                    if (!is_dir($path) || !is_readable($path)) {
                        mkdir($path, 0777);
                        \funcionesGenerales::crearIndex($path);
                    }

                    $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                    copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                    unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                    \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
                }

                if ($iEstab > 0) {

                    foreach ($_SESSION["formulario"]["datoshijos"] as $hijo) {

                        $_SESSION["formulario"]["datos"] = $hijo;
                        $det = '';

                        //
                        if (
                            $_SESSION["tramite"]["tipotramite"] == 'renovacionmatricula' ||
                            $_SESSION["tramite"]["tipotramite"] == 'renovacionesadl'
                        ) {
                            $det = 'FORMULARIO DE RENOVACION - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                        }

                        //
                        if (
                            $_SESSION["tramite"]["tipotramite"] == 'matriculapnat' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaest' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculapjur' ||
                            $_SESSION["tramite"]["tipotramite"] == 'matriculaesadl'
                        ) {
                            $det = 'FORMULARIO DE MATRICULA - MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0");
                        }

                        //
                        $name = armarPdfEstablecimientoNuevo1082Api($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');

                        //
                        if (count($_SESSION["formulario"]["datos"]["f"]) > 1) {
                            $name1 = armarPdfEstablecimientoNuevoAnosAnteriores1082Api($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '');
                            unirPdfsApi(array($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name1), $_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                        }

                        //
                        $bandeja = '';
                        if (substr(ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), 0, 1) == 'S') {
                            $bandeja = '5.-REGESADL';
                        } else {
                            $bandeja = '4.-REGMER';
                        }

                        //
                        $id = \funcionesRegistrales::grabarAnexoRadicacion(
                            $dbx, // Conexion BD
                            ltrim($_SESSION["tramite"]["numeroradicacion"], "0"),
                            trim($_SESSION["tramite"]["numerorecibo"]),
                            trim($_SESSION["tramite"]["numerooperacion"]),
                            ltrim($_SESSION["tramite"]["identificacioncliente"], "0"),
                            trim($_SESSION["tramite"]["nombrecliente"]),
                            '', // Acreedor
                            '', // Nombre acreedor
                            ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                            '', // N&uacute;mero de proponente
                            TIPO_DOC_FOR_MERCANTIL, // Tipo de documento
                            '', // N&uacute;mero del documento
                            $_SESSION["tramite"]["fecharecibo"],
                            '', // C&oacute;digo de origen
                            'EL COMERCIANTE',
                            '', // Clasificaci&oacute;n
                            '', // N&uacute;mero del contrato
                            '', // Idfuente
                            1, // versi&oacute;n
                            '', // Path
                            '1', // Estado
                            date("Ymd"), // fecha de escaneo o generaci&oacute;n
                            $_SESSION["generales"]["codigousuario"],
                            '', // Caja
                            '', // Libro
                            $det,
                            '', // Libro del registro
                            '', // Numero del registro
                            '', // Dupli
                            $bandeja, // Bandeja de registro
                            'S', // Soporte del recibo
                            '', // Identificador
                            '503' // Tipo de anexo
                        );

                        $dirx = date("Ymd");
                        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                        if (!is_dir($path) || !is_readable($path)) {
                            mkdir($path, 0777);
                            \funcionesGenerales::crearIndex($path);
                        }

                        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                        copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                        unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                        \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
                    }
                }
            }
            unset($arrForms);
            unset($form);

            //
            if (!empty($datPrincipal)) {
                $bandeja = '4.-REGMER';
                $_SESSION["formulario"]["datos"] = $datPrincipal;
                $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
                $name = armarPdfFormatoAfiliacion($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"], '', '');
                $det = 'Formato de renovación de afiliación matrícula No. ' . $datPrincipal["matricula"];
                $id = \funcionesRegistrales::grabarAnexoRadicacion(
                    $dbx, // COnexion bd
                    ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // COdigo barras
                    trim($_SESSION["tramite"]["numerorecibo"]), // Recibo
                    trim($_SESSION["tramite"]["numerooperacion"]), // Operacion
                    ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // identificacion
                    trim($_SESSION["tramite"]["nombrecliente"]), // Nombre
                    '', // Acreedor
                    '', // Nombre acreedor
                    ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // Numero de matricula
                    '', // N&uacute;mero de proponente
                    TIPO_DOC_FOR_AFILIACION, // Tipo de documento
                    '', // Numero del documento
                    $_SESSION["tramite"]["fecharecibo"], // fecha del recibo
                    '', // Codigo de origen
                    'EL COMERCIANTE',
                    '', // Clasificacion
                    '', // Numero del contrato
                    '', // Idfuente
                    1, // version
                    '', // Path
                    '1', // Estado
                    date("Ymd"), // fecha de escaneo o generacion
                    $_SESSION["generales"]["codigousuario"], // Usuario que genera
                    '', // Caja
                    '', // Libro
                    $det, // Detalle
                    '', // Libro del registro
                    '', // Numero del registro
                    '', // Dupli
                    $bandeja, // Bandeja de registro
                    'S', // Soporte del recibo
                    '', // Identificador
                    '503' // Tipo de anexo
                );

                $dirx = date("Ymd");
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionesGenerales::crearIndex($path);
                }

                $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
                copy($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                unlink($_SESSION["generales"]["pathabsoluto"] . '/tmp/' . $name);
                \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
            }
        }
    }

    public static function generarMutacionRepositorio($dbx = null)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMutacion.php';

        $_SESSION["formulario"]["datos"] = array();
        $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
        $arrForms = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "xml");
        foreach ($arrForms as $form) {
            $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $form);
            if (substr($_SESSION["formulario"]["datos"]["matricula"], 0, 1) == 'S') {
                $clavevalor = '90.20.43';
                $bandeja = '5.-REGESADL';
            } else {
                $clavevalor = '90.20.41';
                $bandeja = '4.-REGMER';
            }
            $name = armarPdfMutacion($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"]);
            $id = \funcionesRegistrales::grabarAnexoRadicacion(
                $dbx, // Conexion BD
                ltrim($_SESSION["tramite"]["numeroradicacion"], "0"),
                trim($_SESSION["tramite"]["numerorecibo"]),
                trim($_SESSION["tramite"]["numerooperacion"]),
                ltrim($_SESSION["tramite"]["identificacioncliente"], "0"),
                trim($_SESSION["tramite"]["nombrecliente"]),
                '', // Acreedor
                '', // Nombre acreedor
                ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                '', // N&uacute;mero de proponente
                retornarClaveValorMysqliApi($dbx, $clavevalor), // Tipo de documento
                'N/A', // N&uacute;mero del documento
                $_SESSION["tramite"]["fecharecibo"],
                '', // C&oacute;digo de origen
                'EL COMERCIANTE - INSCRITO',
                '', // Clasificaci&oacute;n
                '', // N&uacute;mero del contrato
                '', // Idfuente
                1, // versi&oacute;n
                '', // Path
                '1', // Estado
                date("Ymd"), // fecha de escaneo o generaci&oacute;n
                $_SESSION["generales"]["codigousuario"],
                '', // Caja
                '', // Libro
                'SOLICITUD DE MUTACION DE ACTIVIDAD ECONOMICA MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // Noticia
                '', // LIBRO
                '', // Registro
                '', // Dupli
                $bandeja, // Bandeja de registro
                'S', // Soporte del recibo de caja
                '', // Identificador
                '501' // Tipo anexo	
            );
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }

            $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
            copy($name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
            unlink($name);
            grabarPathAnexoRadicacion($id, $pathsalida);
            unset($arrForms);
            unset($form);
        }
    }

    public static function generarReingresoAutomatico($dbx, $cb)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php';

        //
        $nameLog = 'generarReingresoAutomatico_' . date("Ymd");
        \logApi::general2($nameLog, $_SESSION["tramite"]["numeroradicacion"], 'Ingreso a reingresar tramite No. ' . $cb);

        //
        $_SESSION["temporal"]["radicacion"] = \funcionesRegistrales::retornarCodigoBarras($dbx, $cb);
        if ($_SESSION["temporal"]["radicacion"] === false || empty($_SESSION["temporal"]["radicacion"])) {
            \logApi::general2($nameLog, $_SESSION["tramite"]["numeroradicacion"], 'No localizado');
            return false;
        }
        $_SESSION["temporal"]["codigobarras"] = $cb;

        // ************************************************************************************* //
        // Localiza bandeja de digitalización
        // ************************************************************************************* //
        $bandejaDigitalizacion = '4.-REGMER';
        $rut = retornarRegistroMysqliApi($dbx, 'mreg_codrutas', "id='" . $_SESSION["temporal"]["radicacion"]["tramite"] . "'");
        if ($rut && !empty($rut)) {
            $bandejaDigitalizacion = $rut["bandeja"];
        }

        // ************************************************************************************* //
        // En caso de reingreso de trámites de proponentes
        // ************************************************************************************* //    
        if (($_SESSION["temporal"]["radicacion"]["codigoservicio"] == '01020401') || ($_SESSION["temporal"]["radicacion"]["codigoservicio"] == '01020501')) {
            if (($_SESSION["temporal"]["radicacion"]["estadoproponente"] != '00') && ($_SESSION["temporal"]["radicacion"]["estadoproponente"] != '02') && ($_SESSION["temporal"]["radicacion"]["estadoproponente"] != 'IA')) {
                \logApi::general2($nameLog, $_SESSION["tramite"]["numeroradicacion"], 'Proponente no está activo');
                return false;
            }
        }

        // ************************************************************************************* //
        // Verifica que la devolucion si permita ser reingresada
        // ************************************************************************************* //
        $continuar = 'si';
        $modificar = 'si';
        $arrDevs = retornarRegistrosMysqliApi($dbx, 'mreg_devoluciones_nueva', "idradicacion='" . ltrim(trim($_SESSION["temporal"]["codigobarras"]), "0") . "'", "fechadevolucion, horadevolucion");
        if (($arrDevs) && (!empty($arrDevs))) {
            foreach ($arrDevs as $d) {
                if (($d["estado"] == '1') || ($d["estado"] == '2')) {
                    if ($d["tipodevolucion"] == 'D') {
                        $continuar = 'no';
                    }
                    if ($d["modificarformulario"] == 'N') {
                        $modificar = 'no';
                    } else {
                        $modificar = 'si';
                    }
                }
            }
        }
        unset($arrDevs);
        if ($continuar == 'no') {
            \logApi::general2($nameLog, $_SESSION["tramite"]["numeroradicacion"], 'No reingresable, devolucion de plano');
            return false;
        }

        // ************************************************************************************* //
        // Borra si existe un registro previo en mreg_radicacionesdatos
        // ************************************************************************************* //
        borrarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatos', "idradicacion='" . ltrim($_SESSION["temporal"]["codigobarras"], "0") . "'");
        \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Borro soportes previos en mreg_radicacionesdatos');

        // ************************************************************************************* //
        // Recupera formularios desde mreg_liquidaciondatos
        // ************************************************************************************* //
        $dat = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"], "secuencia");
        \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Recupera formularios desde mreg_liquidaciondatos');

        // ************************************************************************************* //
        // Graba radicación datos
        // ************************************************************************************* //
        $cant = 0;
        if (!empty($dat)) {
            foreach ($dat as $da) {
                $cant++;
                $arrayCampos = array(
                    'idradicacion',
                    'secuencia',
                    'tipotramite',
                    'expediente',
                    'idestado',
                    'xml'
                );

                $arrayValues = array(
                    "'" . ltrim($_SESSION["temporal"]["codigobarras"], "0") . "'",
                    "'" . sprintf("%03s", $cant) . "'",
                    "'" . $_SESSION["tramite"]["tipotramite"] . "'",
                    "'" . $da["expediente"] . "'",
                    "'" . $da["idestado"] . "'",
                    "'" . addslashes($da["xml"]) . "'"
                );
                insertarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatos', $arrayCampos, $arrayValues);
                \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Creo formulario en mreg_radicacionesdatos : ' . $da["expediente"] . ' - ' . $da["xml"]);
            }
        }

        // ************************************************************************************* //
        // Actualiza estado del código de barras en el SII
        // 2017-06-07 : JINT
        // ************************************************************************************* //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            $sedx = '99';
        } else {
            $sedx = $_SESSION["generales"]["sedeusuario"];
        }
        \funcionesRegistrales::actualizarEstadoCodigoBarras($dbx, ltrim(trim($_SESSION["temporal"]["codigobarras"]), "0"), '09', $_SESSION["generales"]["codigousuario"], $sedx);
        \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Marco como reingresado el codigo de barras');

        // ************************************************************************************* //
        // Pone la liquidación en estado 12 (reingresada)
        // ************************************************************************************* //
        $arrCampos = array('idestado');
        $arrValores = array("'12'");
        regrabarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValores, "idliquidacion=" . $_SESSION["tramite"]["idliquidacion"]);
        $_SESSION["tramite"]["idestado"] = '12';
        \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Cambio estado de la liquidacion a 12');

        // ************************************************************************************* //
        // Actualiza el expediente afectado a 7 en SII .- CON DOCUMENTOS EN TRAMITE 
        // 2017.06-07 : JINT
        // ************************************************************************************* //
        if (trim($_SESSION["temporal"]["radicacion"]["matricula"]) != '') {
            $arrCampos = array(
                'ctrestdatos',
                'fecactualizacion',
                'compite360',
                'rues',
                'ivc'
            );
            $arrValores = array(
                "'7'",
                "'" . date("Ymd") . "'",
                "'NO'",
                "'NO'",
                "'NO'"
            );
            unset($_SESSION["expedienteactual"]);
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $_SESSION["temporal"]["radicacion"]["matricula"] . "'");
            \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Coloco en 7 el estado de la matricula ' . $_SESSION["temporal"]["radicacion"]["matricula"]);
        }


        // ************************************************************************************* //
        // Evalua los soportes que debe trasladar desde mreg_anexos_liquidaciones a mreg_radicacionesanexos
        // ************************************************************************************* //
        $arrTemL = retornarRegistrosMysqliApi($dbx, 'mreg_anexos_liquidaciones', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "identificador");

        // ************************************************************************************* //
        // 2015-05-28: Recupera anexos desde mreg_anexos_liquidaciones
        // Crea un nuevo anexo
        // ************************************************************************************* //
        if ($arrTemL && !empty($arrTemL)) {
            foreach ($arrTemL as $x) {
                $temAnx = retornarRegistroMysqliApi($dbx, 'mreg_anexos_liquidaciones', "idanexo=" . $x["idanexo"]);

                $tem = explode("-", $temAnx["idtipodoc"]);
                $tipoDocumento = trim($tem[0]);

                //
                $pptx = '';
                $matx = '';
                if (
                    $_SESSION["tramite"]["tipotramite"] == 'inscripcionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'renovacionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'actualizacionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'actualizacionproponente399' ||
                    $_SESSION["tramite"]["tipotramite"] == 'cancelacionproponente' ||
                    $_SESSION["tramite"]["tipotramite"] == 'cambiodomicilioproponente'
                ) {
                    $pptx = ltrim($temAnx["expediente"], "0");
                    $matx = '';
                } else {
                    $pptx = '';
                    $matx = ltrim($temAnx["expediente"], "0");
                }

                //
                $id = \funcionesRegistrales::grabarAnexoRadicacion(
                    $dbx,
                    $_SESSION["temporal"]["codigobarras"], // Codigo de barras
                    $_SESSION["tramite"]["numerorecibo"], // Número de recibo
                    $_SESSION["tramite"]["numerooperacion"], // Número de operacion
                    ltrim($temAnx["identificacion"], "0"), //Identificacion
                    trim($temAnx["nombre"]),
                    '', // Acreedor
                    '', // Nombre acreedor
                    $matx, // Matrícula
                    $pptx, // Proponente
                    $tipoDocumento, // Tipo de documento
                    $temAnx["numdoc"], // Número del documento
                    $temAnx["fechadoc"],
                    '', // Código de origen
                    $temAnx["txtorigendoc"], // txt origen del documento
                    '', // Clasificación
                    '', // Número del contrato
                    '', // Idfuente
                    1, // versión
                    '', // Path
                    '1', // Estado
                    date("Ymd"), // fecha de escaneo o generación
                    $_SESSION["generales"]["codigousuario"],
                    '', // Caja
                    '', // Libro
                    $temAnx["observaciones"],
                    '', // Libro
                    '', // Número de registro en libros
                    '', // Dupli
                    $temAnx["bandeja"], // Bandeja
                    'N', // Soporte del recibo de caja
                    $temAnx["identificador"], // Identificador del tipo de soporte
                    $temAnx["tipoanexo"] // Tipo de anexo
                );

                $namefx = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $temAnx["path"] . $temAnx["idanexo"] . '.' . $temAnx["tipoarchivo"];

                //
                $dirx = date("Ymd");
                $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
                if (!is_dir($path) || !is_readable($path)) {
                    mkdir($path, 0777);
                    \funcionesGenerales::crearIndex($path);
                }

                $pathsalida = 'mreg/' . $dirx . '/' . $id . '.' . $temAnx["tipoarchivo"];
                copy($namefx, PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
                \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
                \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Creo en mreg_radicacionanexos el anexo no. ' . $id . ' : ' . $pathsalida);
            }
        }


        // ******************************************************************************************************** //    
        // 2018-08-27: JINT Incluye en el repositorio de imágenes el sobre digital del reingreso
        // ******************************************************************************************************** //
        $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_liquidacion_sobre', "idsobre='" . $_SESSION["tramite"]["numerosobredigital"] . "'");
        if ($arrTem && !empty($arrTem)) {
            $nombre = $arrTem["apellido1firmante"];
            if (trim($arrTem["apellido2firmante"])) {
                $nombre .= ' ' . $arrTem["apellido2firmante"];
            }
            if (trim($arrTem["nombre1firmante"])) {
                $nombre .= ' ' . $arrTem["nombre1firmante"];
            }
            if (trim($arrTem["nombre2firmante"])) {
                $nombre .= ' ' . $arrTem["nombre2firmante"];
            }
            if (!defined('TIPO_DOC_SOBRE_MERCANTIL')) {
                define('TIPO_DOC_SOBRE_MERCANTIL', '');
            }
            if (!defined('TIPO_DOC_SOBRE_PROPONENTES')) {
                define('TIPO_DOC_SOBRE_PROPONENTES', '');
            }
            if (!defined('TIPO_DOC_SOBRE_ESADL')) {
                define('TIPO_DOC_SOBRE_ESADL', '');
            }
            if ($bandejaDigitalizacion == '4.-REGMER') {
                $tipoDocumento = TIPO_DOC_SOBRE_MERCANTIL;
            }
            if ($bandejaDigitalizacion == '5.-REGESADL') {
                $tipoDocumento = TIPO_DOC_SOBRE_ESADL;
            }
            if ($bandejaDigitalizacion == '6.-REGPRO') {
                $tipoDocumento = TIPO_DOC_SOBRE_PROPONENTES;
            }
            $id = \funcionesRegistrales::grabarAnexoRadicacion(
                $dbx,
                $_SESSION["temporal"]["codigobarras"], // Codigo de barras
                $_SESSION["temporal"]["numerorecibo"], // Numero del recibo
                $_SESSION["temporal"]["numerooperacion"], // Numero de operacion
                ltrim($arrTem["identificacionfirmante"], "0"), // Identificacion
                trim($nombre), // Nombre
                '', // Acreedor
                '', // Nombre acreedor
                '', // Matricula
                '', // Proponente
                $tipoDocumento, // Tipo de documento 
                '', // Numero del documento
                $arrTem["fecha"],
                '', // Codigo de origen
                'EL CLIENTE', // txt origen del documento
                '', // Clasificacion
                '', // Numero del contrato
                '', // Idfuente
                1, // version
                '', // Path
                '1', // Estado
                date("Ymd"), // fecha de escaneo o generacion
                $_SESSION["generales"]["codigousuario"], // COdigo del usuario
                '', // Caja
                '', // Libro
                'SOBRE DIGITAL - REINGRESO', // Detalle
                '', // Libro
                '', // Numero de registro en libros
                '', // Dupli
                $bandejaDigitalizacion, // Bandeja
                'N', // Soporte del recibo de caja
                '', // Identificador del tipo de soporte
                '601' // Tipo de anexo	
            );
            $tipoarchivo = \funcionesGenerales::encontrarExtension($arrTem["path"]);
            $namefx = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $arrTem["path"];

            //
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }

            $pathsalida = 'mreg/' . $dirx . '/' . $id . '.' . $tipoarchivo;
            copy($namefx, PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
            \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
            \logApi::general2($nameLog, '', utf8_encode('Traslado el sobre digital de la liquidación al repositorio : ' . $arrTem["path"]));
        }


        // ************************************************************************************* //
        // 2017-09-14: JINT: Envia notificaciones SIPREF a emails y celulares
        // ************************************************************************************* //    

        if ($_SESSION["temporal"]["radicacion"]["esembargo"] == 'si') {
            unset($_SESSION["temporal"]["radicacion"]["emails"]);
            unset($_SESSION["temporal"]["radicacion"]["telefonos"]);
            $_SESSION["temporal"]["radicacion"]["emails"][$_SESSION["tramite"]["emailradicador"]] = $_SESSION["tramite"]["emailradicador"];
            $_SESSION["temporal"]["radicacion"]["telefonos"][$_SESSION["tramite"]["celularradicador"]] = $_SESSION["tramite"]["celularradicador"];
            \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Al ser embargo o medida cautelar solo informara a ' . $_SESSION["tramite"]["emailradicador"] . ' y ' . $_SESSION["tramite"]["celularradicador"]);
        }

        // Notificación al EMAIL
        if (!empty($_SESSION["temporal"]["radicacion"]["emails"])) {
            $msg = '';
            $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . \funcionesGenerales::mostrarFecha(date("Ymd")) . ' a las ' . \funcionesGenerales::mostrarHora(date("His")) . ' ';
            $msg .= 'fue reingresado en nuestras oficinas una transaccion sujeta a inscripcion en los registros publicos que ';
            $msg .= 'administra y maneja nuestra entidad. Los datos del tramite radicado son los siguientes:<br><br>';
            $msg .= 'Radicado  No. ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . '<br>';
            $msg .= 'Recibo  No. ' . $_SESSION["temporal"]["radicacion"]["recibo"] . '<br>';
            $msg .= 'Tipo tramite  No. ' . $_SESSION["temporal"]["radicacion"]["datliq"]["tipotramite"] . '<br>';
            if (ltrim($_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"], "0") != '') {
                $msg .= 'Matricula : ' . $_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"] . '<br>';
            }
            if (ltrim($_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"], "0") != '') {
                $msg .= 'Proponente : ' . $_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"] . '<br>';
            }
            $msg .= 'Nombre: ' . $_SESSION["tramite"]["nombreradicador"] . '<br>';
            $msg .= 'Identificacion: ' . $_SESSION["tramite"]["ideradicador"] . '<br>';

            foreach ($_SESSION["temporal"]["radicacion"]["emails"] as $e) {
                $msg .= 'Email ... ' . $e . '<br>';
            }
            $msg .= '<br>';

            if (!defined('NOTIFICAR_TELEFONO')) {
                define('NOTIFICAR_TELEFONO', 'NO');
            }
            if (NOTIFICAR_TELEFONO == 'SI') {
                $msg .= 'Si tiene alguna duda o inquietud con el contenido de esta notificacion, puede comunicarse al ';
                $msg .= 'numero ' . TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarNombreMunicipioMysqliApi($dbx, MUNICIPIO) . ' ';
                $msg .= 'citando el tramite (recibo de caja) No. ' . $_SESSION["temporal"]["radicacion"]["recibo"] . '<br><br>';
            }

            $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' ';
            $msg .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
            $msg .= '<br><br>';
            $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
            $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
            $msg .= '<br><br>';
            $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';

            $notificados = 0;
            $emails = 0;

            // Arma en memoria tabla de rutas documentales
            $trr = array();
            $resQueryRR = retornarRegistrosMysqliApi($dbx, 'mreg_codrutas', "1=1", "id");
            foreach ($resQueryRR as $trdtemp) {
                $trr[$trdtemp['id']] = $trdtemp['bandeja'];
            }

            //
            foreach ($_SESSION["temporal"]["radicacion"]["emails"] as $e) {
                $emails++;
                \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Email No. ' . $emails . ' - ' . $e);

                $bandeja = $trr[$_SESSION["temporal"]["radicacion"]["tramite"]];
                \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Código de barras: ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ' se asigna a bandeja ' . $bandeja);

                $emx = trim($e);

                //
                if (TIPO_AMBIENTE == 'PRODUCCION') {
                    $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Notificacion de reingreso del Codigo de Barras No. ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ' en  LA ' . RAZONSOCIAL, $msg, array());
                } else {
                    $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, 'jint@confecamaras.org.co', 'Notificacion de reingreso del Codigo de Barras No. ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ' en  LA ' . RAZONSOCIAL, $msg, array());
                }

                //
                if ($rEmail) {
                    $notificados++;
                    \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Notificando reingreso del codigo de barras: ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ', Email : ' . $emx . ' ** OK **');
                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($dbx, '05', $_SESSION["temporal"]["radicacion"]["codbarras"], '', $_SESSION["temporal"]["radicacion"]["operacion"], $_SESSION["temporal"]["radicacion"]["recibo"], '', '', '', '', $_SESSION["tramite"]["ideradicador"], $_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"], $_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"], $_SESSION["tramite"]["nombreradicador"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '3', '', $bandeja);
                } else {
                    \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Notificando reingreso del codigo de barras: ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ', Email : ' . $emx . ' ** ERRROR : ' . $_SESSION["generales"]["mensajeerror"]);
                    \funcionesRegistrales::actualizarMregNotificacionesParaEnviarEmail($dbx, '05', $_SESSION["temporal"]["radicacion"]["codbarras"], '', $_SESSION["temporal"]["radicacion"]["operacion"], $_SESSION["temporal"]["radicacion"]["recibo"], '', '', '', '', $_SESSION["tramite"]["ideradicador"], $_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"], $_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"], $_SESSION["tramite"]["nombreradicador"], $emx, $msg, date("Ymd"), date("His"), date("Ymd"), date("His"), '2', 'ERROR : ' . $_SESSION["generales"]["mensajeerror"], $bandeja);
                }
            }
        }

        //
        if ($_SESSION["generales"]["codigousuario"] == 'USUPUBXX') {
            if (defined('EMAIL_NOTIFICACION_REINGRESOS_VIRTUALES') && EMAIL_NOTIFICACION_REINGRESOS_VIRTUALES != '') {
                $msg = 'Se reporta el REINGRESO del Código de Barras ' . $_SESSION["temporal"]["radicacion"]["codbarras"];
                $rEmail = \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, EMAIL_NOTIFICACION_REINGRESOS_VIRTUALES, 'Reingreso virtual', $msg, array());
            }
        }

        //
        if (empty($_SESSION["temporal"]["radicacion"]["telefonos"])) {
            \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Reingreso No. ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ' - Mensaje : Sin telefonos para notificar');
        } else {
            $txtSms = 'La ' . RAZONSOCIALSMS . ' le informa que el ' . \funcionesGenerales::mostrarFecha(date("Ymd")) . ' a las ' . \funcionesGenerales::mostrarHora(date("His")) . ' fue reingresado el tramite No. ' . $_SESSION["temporal"]["radicacion"]["codbarras"];
            \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Reingreso No. ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ' - Mensaje SMS: ' . $txtSms);
            foreach ($_SESSION["temporal"]["radicacion"]["telefonos"] as $t) {
                $exp1 = '';
                if (ltrim($_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"], "0") != '') {
                    $exp1 = $_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"];
                }
                if (ltrim($_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"], "0") != '') {
                    $exp1 = $_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"];
                }
                //
                \funcionesRegistrales::actualizarPilaSms($dbx, '', $t, '8', $_SESSION["temporal"]["radicacion"]["recibo"], $_SESSION["temporal"]["radicacion"]["codbarras"], '', '', '', $_SESSION["temporal"]["radicacion"]["datliq"]["idmatriculabase"], $_SESSION["temporal"]["radicacion"]["datliq"]["idproponentebase"], $_SESSION["tramite"]["ideradicador"], $_SESSION["tramite"]["nombreradicador"], $txtSms, '', $bandeja);
                \logApi::general2($nameLog, $_SESSION["temporal"]["codigobarras"], 'Pila sms : Encoló notificación para ' . $t);
            }
        }

        // 2017-08-10: JINT: Reporta el reingreso a DocXflow
        $mensajeSalida = 'Radicado/código de barras ' . $_SESSION["temporal"]["radicacion"]["codbarras"] . ' reingresado';

        return true;
    }

    /**
     * 
     * @param type $dbx
     * @param string $mat
     * @param string $pro
     * @param type $arreglo
     * @param type $tiporegistro
     * @param type $genrec
     * @param type $tiporecibo (S o G
     */
    public static function generarReciboCajaRepositorio($dbx, $mat, $pro, $arreglo = array(), $tiporegistro = 'RegMer', $genrec = '', $tiporecibo = 'S')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsGenerales.php';

        //
        if (!isset($_SESSION["tramite"]["claveprepago"])) {
            $_SESSION["tramite"]["claveprepago"] = '';
        }
        if (!isset($_SESSION["tramite"]["saldoprepago"])) {
            $_SESSION["tramite"]["saldoprepago"] = 0;
        }

        $name = armarPdfRecibo($dbx, $_SESSION["tramite"]["idliquidacion"], 'T', 'SI', $arreglo, $_SESSION["tramite"]["claveprepago"], $_SESSION["tramite"]["saldoprepago"], $genrec, $tiporecibo);

        $tipodoc = '';
        $bandeja = '';
        switch ($tiporegistro) {
            case "RegMer": {
                    $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                    $bandeja = '4.-REGMER';
                    break;
                }
            case "LibCom":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "CerEle":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "CerVirt":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "InscDoc":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "InscDocRegMer":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "InscDocEsadl":
                $tipodoc = TIPO_DOC_REC_CAJA_ESADL;
                $bandeja = '5.-REGESADL';
                break;
            case "crm":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            case "RegEsadl":
                $tipodoc = TIPO_DOC_REC_CAJA_ESADL;
                $bandeja = '5.-REGESADL';
                break;
            case "RegPro":
                $tipodoc = TIPO_DOC_REC_CAJA_PROPONENTES;
                $bandeja = '6.-REGPRO';
                break;
            case "PrePag":
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
            default:
                $tipodoc = TIPO_DOC_REC_CAJA_MERCANTIL;
                $bandeja = '4.-REGMER';
                break;
        }

        //
        if (!defined('SEPARAR_RECIBOS') || SEPARAR_RECIBOS == 'NO' || SEPARAR_RECIBOS == '') {
            $nrec = $_SESSION["tramite"]["numerorecibo"];
            $nope = $_SESSION["tramite"]["numerooperacion"];
        } else {
            if ($tiporecibo == 'S') {
                $nrec = $_SESSION["tramite"]["numerorecibo"];
                $nope = $_SESSION["tramite"]["numerooperacion"];
            } else {
                $nrec = $_SESSION["tramite"]["numerorecibogob"];
                $nope = $_SESSION["tramite"]["numerooperaciongob"];
            }
        }
        $id = \funcionesRegistrales::grabarAnexoRadicacion(
            $dbx, // COnexion
            ltrim($_SESSION["tramite"]["numeroradicacion"], "0"), // NumRad
            trim($nrec), // NumRecibo
            trim($nope), // NumOpe
            ltrim($_SESSION["tramite"]["identificacioncliente"], "0"), // IdeCliente
            trim($_SESSION["tramite"]["nombrecliente"]), // NomCliente
            '', // Acreedor
            '', // Nombre acreedor
            ltrim($mat, "0"), // mat
            ltrim($pro, "0"), // Prop
            $tipodoc, // Tipo de documento
            '', // N&uacute;mero del documento
            $_SESSION["tramite"]["fecharecibo"], // fechaRecibo
            '', // C&oacute;digo de origen
            'CAJA DE LA CAMARA DE COMERCIO', // Txtorigen
            '', // Clasificaci&oacute;n
            '', // N&uacute;mero del contrato
            '', // Idfuente
            1, // versi&oacute;n
            '', // Path
            '1', // Estado
            date("Ymd"), // fecha de escaneo o generaci&oacute;n
            $_SESSION["generales"]["codigousuario"], // Usuario
            '', // Caja
            '', // Libro
            'RECIBO DE CAJA No. ' . $nrec, // Detalle
            '', // Libro de comercio
            '', // Numero de registros en libro
            '', // Dupli
            $bandeja, // Bandeja de registro
            'S', // Soporte del recibo de caja
            '', // Identificador
            '509' // Tipo anexo	
        );

        $dirx = date("Ymd");
        $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;

        if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"]) || !is_readable(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"])) {
            mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"], 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"]);
        }

        if (!is_dir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg') || !is_readable(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg')) {
            mkdir(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg', 0777);
            \funcionesGenerales::crearIndex(PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg');
        }

        if (!is_dir($path) || !is_readable($path)) {
            mkdir($path, 0777);
            \funcionesGenerales::crearIndex($path);
        }

        $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
        copy(PATH_ABSOLUTO_SITIO . '/' . $name, PATH_ABSOLUTO_SITIO . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
        unlink(PATH_ABSOLUTO_SITIO . '/' . $name);
        \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
    }

    public static function generarMutacionDireccionRepositorio($dbx = null)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMutacionDireccion.php';

        $_SESSION["formulario"]["datos"] = array();
        $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
        $arrForms = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "xml");
        foreach ($arrForms as $form) {
            $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $form);
            $_SESSION["formulario"]["datos"]["modcom"] = $_SESSION["tramite"]["modcom"];
            $_SESSION["formulario"]["datos"]["modnot"] = $_SESSION["tramite"]["modnot"];
            if (substr($_SESSION["formulario"]["datos"]["matricula"], 0, 1) == 'S') {
                $clavevalor = '90.20.43';
                $bandeja = '5.-REGESADL';
            } else {
                $clavevalor = '90.20.41';
                $bandeja = '4.-REGMER';
            }
            $name = armarPdfMutacionDireccion($_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"]);
            $id = \funcionesRegistrales::grabarAnexoRadicacion(
                $dbx, // Conexion BD
                ltrim($_SESSION["tramite"]["numeroradicacion"], "0"),
                trim($_SESSION["tramite"]["numerorecibo"]),
                trim($_SESSION["tramite"]["numerooperacion"]),
                ltrim($_SESSION["tramite"]["identificacioncliente"], "0"),
                trim($_SESSION["tramite"]["nombrecliente"]),
                '', // Acreedor
                '', // Nombre acreedor
                ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                '', // N&uacute;mero de proponente
                retornarClaveValorMysqliApi($dbx, $clavevalor), // Tipo de documento
                'N/A', // N&uacute;mero del documento
                $_SESSION["tramite"]["fecharecibo"],
                '', // C&oacute;digo de origen
                'EL COMERCIANTE - INSCRITO',
                '', // Clasificaci&oacute;n
                '', // N&uacute;mero del contrato
                '', // Idfuente
                1, // versi&oacute;n
                '', // Path
                '1', // Estado
                date("Ymd"), // fecha de escaneo o generaci&oacute;n
                $_SESSION["generales"]["codigousuario"],
                '', // Caja
                '', // Libro
                'SOLICITUD DE MUTACION DE DIRECCION MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // Noticia
                '', // LIBRO
                '', // Registro
                '', // Dupli
                $bandeja, // Bandeja de registro
                'S', // Soporte del recibo de caja
                '', // identificador
                '501' // Tipo anexo				
            );
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }

            $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
            copy($name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
            unlink($name);
            grabarPathAnexoRadicacion($id, $pathsalida);
            unset($arrForms);
            unset($form);
        }
    }

    public static function generarMutacionNombreRepositorio($dbx = null)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/genPdfsMutacionNombre.php';

        $_SESSION["formulario"]["datos"] = array();
        $_SESSION["formulario"]["numrec"] = $_SESSION["tramite"]["numerorecuperacion"];
        $arrForms = retornarRegistrosMysqliApi($dbx, 'mreg_liquidaciondatos', "idliquidacion=" . $_SESSION["tramite"]["numeroliquidacion"], "xml");
        foreach ($arrForms as $form) {
            $_SESSION["formulario"]["datos"] = \funcionesGenerales::desserializarExpedienteMatricula($dbx, $form);
            if (substr($_SESSION["formulario"]["datos"]["matricula"], 0, 1) == 'S') {
                $clavevalor = '90.20.43';
                $bandeja = '5.-REGESADL';
            } else {
                $clavevalor = '90.20.41';
                $bandeja = '4.-REGMER';
            }
            $name = armarPdfMutacionNombre($dbx, $_SESSION["tramite"]["numerorecuperacion"], $_SESSION["tramite"]["numeroliquidacion"]);
            $id = \funcionesRegistrales::grabarAnexoRadicacion(
                $dbx, // Conexion BD
                ltrim($_SESSION["tramite"]["numeroradicacion"], "0"),
                trim($_SESSION["tramite"]["numerorecibo"]),
                trim($_SESSION["tramite"]["numerooperacion"]),
                ltrim($_SESSION["tramite"]["identificacioncliente"], "0"),
                trim($_SESSION["tramite"]["nombrecliente"]),
                '', // Acreedor
                '', // Nombre acreedor
                ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // N&uacute;mero de matr&iacute;cula
                '', // N&uacute;mero de proponente
                retornarClaveValorMysqliApi($dbx, $clavevalor), // Tipo de documento
                'N/A', // N&uacute;mero del documento
                $_SESSION["tramite"]["fecharecibo"],
                '', // C&oacute;digo de origen
                'EL COMERCIANTE - INSCRITO',
                '', // Clasificaci&oacute;n
                '', // Numero del contrato
                '', // Idfuente
                1, // version
                '', // Path
                '1', // Estado
                date("Ymd"), // fecha de escaneo o generaci&oacute;n
                $_SESSION["generales"]["codigousuario"],
                '', // Caja
                '', // Libro
                'SOLICITUD DE MUTACION DE NOMBRE MATRICULA NO. ' . ltrim($_SESSION["formulario"]["datos"]["matricula"], "0"), // Noticia
                '', // LIBRO
                '', // Registro
                '', // Dupli
                $bandeja, // Bandeja de registro
                'S', // Soporte del recibo de caja
                '', // identificador
                '501' // Tipo anexo
            );
            $dirx = date("Ymd");
            $path = PATH_ABSOLUTO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/mreg/' . $dirx;
            if (!is_dir($path) || !is_readable($path)) {
                mkdir($path, 0777);
                \funcionesGenerales::crearIndex($path);
            }

            $pathsalida = 'mreg/' . $dirx . '/' . $id . '.pdf';
            copy($name, $_SESSION["generales"]["pathabsoluto"] . '/' . PATH_RELATIVO_IMAGES . '/' . $_SESSION["generales"]["codigoempresa"] . '/' . $pathsalida);
            unlink($name);
            \funcionesRegistrales::grabarPathAnexoRadicacion($dbx, $id, $pathsalida);
            unset($arrForms);
            unset($form);
        }
    }

    public static function generarSecuenciaCodigoBarras($dbx, $fechacb = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/log.php';
        $nameLog = 'generarSecuenciaCodigoBarras_' . date("Ymd");
        //
        $cb = 0;
        $cb = \funcionesRegistrales::retornarMregSecuencia($dbx, 'CODIGOS-BARRAS');
        if ($cb == 0) {
            \logApi::general2($nameLog, '', 'Error recuperando secuencia codigo de barras : ' . $_SESSION["generales"]["mensajeerror"]);
            $_SESSION["generales"]["mensajeerror"] = 'No fue posible localizar el ultimo codigo de barras asignado';
            return false;
        }

        // ************************************************************************************************ //
        // Revisa que el codigo de barras no este previamente creado
        // ************************************************************************************************ //
        $seguir = "si";
        while ($seguir == 'si') {
            $cb++;
            if (contarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras', "codigobarras='" . $cb . "'") == 0) {
                $seguir = 'no';
            }
        }

        // ************************************************************************************************ //
        // Actualiza el consecutivo en claves valor
        // ************************************************************************************************ //	
        \funcionesRegistrales::actualizarMregSecuencia($dbx, 'CODIGOS-BARRAS', $cb);

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //

        if ($fechacb != '') {
            $fecasignar = $fechacb;
        } else {
            $fecasignar = date("Ymd");
        }
        $arrCampos = array(
            'codigobarras',
            'operacion',
            'recibo',
            'fecharadicacion',
            'matricula',
            'proponente',
            'idclase',
            'numid',
            'numdocextenso',
            'nombre',
            'estadofinal',
            'operadorfinal',
            'fechaestadofinal',
            'horaestadofinal',
            'sucursalfinal',
            'activos',
            'liquidacion',
            'reliquidacion',
            'actoreparto',
            'tipdoc',
            'numdoc',
            'oridoc',
            'mundoc',
            'fecdoc',
            'detalle',
            'canins',
            'candoc',
            'canfor',
            'cananx1',
            'cananx2',
            'cananx3',
            'cananx4',
            'cananx5',
            'sucursalradicacion',
            'tiprut',
            'numcaja',
            'escaneocompleto',
            'clavefirmado'
        );

        //
        $arrValores = array(
            "'" . $cb . "'",
            "''", // operacion
            "''", // recibo
            "'" . $fecasignar . "'",
            "''", // matricula
            "''", // proponente
            "''", // idclase
            "''", // numid
            "''", // numdocextenso
            "''", // nombre
            "'" . '01' . "'",
            "''", // operadorfinal
            "'" . $fecasignar . "'",
            "'" . date("His") . "'",
            "''", // sucursalfinal
            0, // activos
            0, // liquidacion
            "''", // reliquidacion
            "''", // actoreparto
            "''", // tipdoc
            "''", // numdoc
            "''", // oridoc
            "''", // mundoc
            "''", // fecdoc
            "''", // detalle
            0, // canins
            0, // candoc
            0, // canfor
            0, // cananx1
            0, // cananx2
            0, // cananx3
            0, // cananx4
            0, // cananx5
            "''", // sucursal radicacion
            "''", // tiprut
            "''", // numcaja
            "''", // escaneo completo
            "''" // clave firmado
        );

        //
        $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras', $arrCampos, $arrValores);
        if ($res === false) {
            \logApi::general2($nameLog, '', 'Error creando codigo de barras : ' . $_SESSION["generales"]["mensajeerror"]);
            return false;
        }
        $detalle = 'Creo codigo de barras No. ' . $cb . ', estado final: 01';
        actualizarLogMysqliApi($dbx, '069', $_SESSION["generales"]["codigousuario"], 'generarSecuenciaCodigoBarras', '', '', '', $detalle, '', '');
        return $cb;
    }

    public static function generarSecuenciaCodigoBarrasPowerFile($dbx)
    {
        $cb = false;
        $headers = array(
            'function: authenticationUser',
            'pmhost: http://bpm.cccucuta.org.co',
            'workspace: cccucuta',
            'Content-Type: application/json'
        );
        $data = array(
            'pmhost' => 'http://bpm.cccucuta.org.co',
            'workspace' => 'cccucuta',
            'clientId' => 'NZPXVTXFOLFZTOGQVGGYUDXGCGLLRFQV',
            'clientSecret' => '13653564459a7194df19bb2021547438',
            'username' => 'confecamara',
            'password' => '123456789'
        );

        $fields = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/loginUserService.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        $resultado = json_decode($result, true);
        $access_token = $resultado['access_token'];
        $expires_in = $resultado['expires_in'];
        $token_type = $resultado['token_type'];
        $scope = $resultado['scope'];
        $refresh_token = $resultado['refresh_token'];

        //echo $access_token;
        //Validacion de Autenticacion
        if ($access_token != '') {
            $header = array(
                'function: newCaseTrigger',
                'pmhost: http://bpm.cccucuta.org.co',
                'workspace: cccucuta',
                'Authorization:' . $access_token,
                'statusIngestion: SUCCESSFUL',
                'processId: 125488281598ddd07bbb5e4006297031',
                'taskId: 130462646598ddd080ea780009585776',
                'userId: 85714721659a72800144f89026507541',
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://bpm.cccucuta.org.co/syscccucuta/en/neoclassic/NewUser/services/powerfileService.php');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $results = curl_exec($ch);
            curl_close($ch);
            $resultado = json_decode($results, true);
            if (isset($resultado["txt_NumRad"])) {
                $cb = ltrim(trim($resultado["txt_NumRad"]), "0");
            }
        }

        // ************************************************************************************************ //
        // Arma los arreglos para grabar el recibo
        // ************************************************************************************************ //

        if ($cb && $cb != '') {
            $arrCampos = array(
                'codigobarras',
                'operacion',
                'recibo',
                'fecharadicacion',
                'matricula',
                'proponente',
                'idclase',
                'numid',
                'numdocextenso',
                'nombre',
                'estadofinal',
                'operadorfinal',
                'fechaestadofinal',
                'horaestadofinal',
                'sucursalfinal',
                'activos',
                'liquidacion',
                'reliquidacion',
                'actoreparto',
                'tipdoc',
                'numdoc',
                'oridoc',
                'mundoc',
                'fecdoc',
                'detalle',
                'canins',
                'candoc',
                'canfor',
                'cananx1',
                'cananx2',
                'cananx3',
                'cananx4',
                'cananx5',
                'sucursalradicacion',
                'tiprut',
                'numcaja',
                'escaneocompleto',
                'clavefirmado'
            );

            //
            $arrValores = array(
                "'" . $cb . "'",
                "''", // operacion
                "''", // recibo
                "'" . date("Ymd") . "'",
                "''", // matricula
                "''", // proponente
                "''", // idclase
                "''", // numid
                "''", // numdocextenso
                "''", // nombre
                "'" . '01' . "'",
                "''", // operadorfinal
                "'" . date("Ymd") . "'",
                "'" . date("His") . "'",
                "''", // sucursalfinal
                0, // activos
                0, // liquidacion
                "''", // reliquidacion
                "''", // actoreparto
                "''", // tipdoc
                "''", // numdoc
                "''", // oridoc
                "''", // mundoc
                "''", // fecdoc
                "''", // detalle
                0, // canins
                0, // candoc
                0, // canfor
                0, // cananx1
                0, // cananx2
                0, // cananx3
                0, // cananx4
                0, // cananx5
                "''", // sucursal radicacion
                "''", // tiprut
                "''", // numcaja
                "''", // escaneo completo
                "''" // clave firmado
            );

            //
            $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras', $arrCampos, $arrValores);
            if ($res === false) {
                return false;
            }
            $detalle = 'Creo codigo de barras No. ' . $cb . ', estado final: 01';
            actualizarLogMysqliApi($dbx, '069', $_SESSION["generales"]["codigousuario"], 'generarSecuenciaCodigoBarrasPowerFile', '', '', '', $detalle, '', '');
        }

        return $cb;
    }

    /**
     * 
     * @param type $dbx
     * @param type $libro
     * @return type
     */
    public static function generarSecuenciaLibros($dbx, $libro = '', $crear = 'si')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaLibros.php';
        return \funcionesRegistrales_generarSecuenciaLibros::generarSecuenciaLibros($dbx, $libro, $crear);
    }

    /**
     * 
     * @param type $dbx
     * @return type
     */
    public static function generarSecuenciaLibrosProponentes($dbx)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaLibrosProponentes.php';
        return \funcionesRegistrales_generarSecuenciaLibrosProponentes::generarSecuenciaLibrosProponentes($dbx);
    }

    /**
     * 
     * @param type $dbx
     * @param type $tipomat
     * @param type $org
     * @param type $cat
     * @param type $nom
     * @param type $fmat
     * @param type $fren
     * @param type $aren
     * @param type $est
     * @param type $codbar
     * @param type $proceso
     * @return type
     */
    public static function generarSecuenciaMatricula($dbx, $tipomat = '', $org = '', $cat = '', $nom = '', $fmat = '', $fren = '', $aren = '', $est = '', $codbar = '', $proceso = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaMatricula.php';
        return \funcionesRegistrales_generarSecuenciaMatricula::generarSecuenciaMatricula($dbx, $tipomat, $org, $cat, $nom, $fmat, $fren, $aren, $est, $codbar, $proceso);
    }

    /**
     * 
     * @param type $dbx
     * @return type
     */
    public static function generarSecuenciaProponente($dbx)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaProponente.php';
        return \funcionesRegistrales_generarSecuenciaProponente::generarSecuenciaProponente($dbx);
    }

    /**
     * 
     * @param type $dbx
     * @param type $usuario
     * @param type $fecha
     * @param type $cajero
     * @param type $sedex
     * @return type
     */
    public static function generarSecuenciaOperacion($dbx, $usuario, $fecha, $cajero = '', $sedex = '')
    {
        $sec = 0;
        $sec1 = 0;
        $sede = $sedex;

        // ************************************************************************************************ //
        // Si el usuario es INTERNET o USUPUBXX
        // ************************************************************************************************ //
        if ($cajero == 'INTERNET' || $cajero == 'USUPUBXX') {
            $sede = '99';
        }

        // ************************************************************************************************ //
        // Si el usuario es INTERNET o USUPUBXX
        // ************************************************************************************************ //    
        if ($cajero == 'RUE') {
            $sede = '90';
        }

        // ************************************************************************************************ //
        // Calcula la secuencia de la operacion
        // ************************************************************************************************ //
        $contar = contarRegistrosMysqliApi($dbx, 'mreg_controlusuarios', "usuario like '" . $cajero . "' and fecha like '" . $fecha . "'");
        if ($contar === false) {
            return false;
        }

        if ($contar == 0) {
            $arrCampos = array(
                'usuario',
                'fecha',
                'secuencia'
            );
            $arrValores = array(
                "'" . $usuario . "'",
                "'" . $fecha . "'",
                1
            );
            insertarRegistrosMysqliApi($dbx, 'mreg_controlusuarios', $arrCampos, $arrValores);
            $sec1 = 1;
        } else {
            $arrTem = retornarRegistroMysqliApi($dbx, 'mreg_controlusuarios', "usuario like '" . $cajero . "' and fecha like '" . $fecha . "'");
            $sec = $arrTem["secuencia"] + 1;
            $sec1 = $sec;
            $arrCampos = array(
                'secuencia'
            );
            $arrValores = array(
                $sec
            );
            regrabarRegistrosMysqliApi($dbx, 'mreg_controlusuarios', $arrCampos, $arrValores, "usuario like '" . $cajero . "' and fecha like '" . $fecha . "'");
        }

        // ************************************************************************************************ //
        // Retorna el numero de operacion
        // 2016-07-30 : JINT
        // Si hay integración con SIREP, retornar el número de operación a 12 dígitos
        // Si no hay integración con SIREP retorna el número de operación a 25 digitos
        // ************************************************************************************************ //
        return $sede . '-' . trim($cajero) . '-' . $fecha . '-' . sprintf("%04s", $sec1);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipo
     * @param type $fps
     * @param type $operacion
     * @param type $fecha
     * @param type $hora
     * @param type $codbarras
     * @param type $tiporegistro
     * @param type $identificacion
     * @param type $nombre
     * @param type $organizacion
     * @param type $categoria
     * @param type $idtipodoc
     * @param type $numdoc
     * @param type $origendoc
     * @param type $fechadoc
     * @param type $estado
     * @param type $fecharenovacionaplicable
     * @param type $tiporecibo
     * @param type $arrServs
     * @return type
     */
    public static function generarSecuenciaRecibo($mysqli, $tipo = 'S', $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '', $estado = '', $fecharenovacionaplicable = '', $tiporecibo = 'S', $arrServs = array())
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaRecibo.php';
        return \funcionesRegistrales_generarSecuenciaRecibo::generarSecuenciaRecibo($mysqli, $tipo, $fps, $operacion, $fecha, $hora, $codbarras, $tiporegistro, $identificacion, $nombre, $organizacion, $categoria, $idtipodoc, $numdoc, $origendoc, $fechadoc, $estado, $fecharenovacionaplicable, $tiporecibo, $arrServs);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipo
     * @return type
     */
    public static function generarSecuenciaReciboVacia($mysqli, $tipo = 'S')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaReciboVacia.php';
        return \funcionesRegistrales_generarSecuenciaReciboVacia::generarSecuenciaReciboVacia($mysqli, $tipo);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipo
     * @param type $recori
     * @param type $arrDet
     * @param type $arrTot
     * @param type $fps
     * @param type $operacion
     * @param type $fecha
     * @param type $hora
     * @param type $codbarras
     * @param type $tiporegistro
     * @param type $identificacion
     * @param type $nombre
     * @param type $organizacion
     * @param type $categoria
     * @param type $idtipodoc
     * @param type $numdoc
     * @param type $origendoc
     * @param type $fechadoc
     * @param type $nameLog
     * @return type
     */
    public static function generarSecuenciaReciboReversion($mysqli, $tipo = 'S', $recori = array(), $arrDet = array(), $arrTot = array(), $fps = array(), $operacion = '', $fecha = '', $hora = '', $codbarras = '', $tiporegistro = '', $identificacion = '', $nombre = '', $organizacion = '', $categoria = '', $idtipodoc = '', $numdoc = '', $origendoc = '', $fechadoc = '', $nameLog = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_generarSecuenciaReciboReversion.php';
        return \funcionesRegistrales_generarSecuenciaReciboReversion::generarSecuenciaReciboReversion($mysqli, $tipo, $recori, $arrDet, $arrTot, $fps, $operacion, $fecha, $hora, $codbarras, $tiporegistro, $identificacion, $nombre, $organizacion, $categoria, $idtipodoc, $numdoc, $origendoc, $fechadoc, $nameLog);
    }

    /**
     * 
     * @param type $dbx
     * @param type $idradicacion
     * @param type $numerorecibo
     * @param type $numerooperacion
     * @param type $identificacion
     * @param type $nombre
     * @param type $acreedor
     * @param type $nombreacreedor
     * @param type $matricula
     * @param type $proponente
     * @param type $idtipodoc
     * @param type $numdoc
     * @param type $fechadoc
     * @param type $idorigendoc
     * @param type $txtorigendoc
     * @param type $idclasificacion
     * @param type $numcontrato
     * @param type $idfuente
     * @param type $version
     * @param type $path
     * @param type $estado
     * @param type $fechaescaneo
     * @param type $idusuarioescaneo
     * @param type $idcajaarchivo
     * @param type $idlibroarchivo
     * @param type $observaciones
     * @param type $libro
     * @param type $registro
     * @param type $dupli
     * @param type $bandeja
     * @param type $soporterecibo
     * @param type $identificador
     * @param type $tipoanexo
     * @param type $procesoespecial
     * @param type $nir
     * @param type $nuc
     * @param type $datareferencia
     * @return type
     */
    public static function grabarAnexoRadicacion($dbx = null, $idradicacion = 0, $numerorecibo = '', $numerooperacion = '', $identificacion = '', $nombre = '', $acreedor = '', $nombreacreedor = '', $matricula = '', $proponente = '', $idtipodoc = '', $numdoc = '', $fechadoc = '', $idorigendoc = '', $txtorigendoc = '', $idclasificacion = '', $numcontrato = '', $idfuente = '', $version = 1, $path = '', $estado = '', $fechaescaneo = '', $idusuarioescaneo = '', $idcajaarchivo = '', $idlibroarchivo = '', $observaciones = '', $libro = '', $registro = '', $dupli = '', $bandeja = '', $soporterecibo = '', $identificador = '', $tipoanexo = '', $procesoespecial = '', $nir = '', $nuc = '', $datareferencia = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarAnexoRadicacion.php';
        return \funcionesRegistrales_grabarAnexoRadicacion::grabarAnexoRadicacion($dbx, $idradicacion, $numerorecibo, $numerooperacion, $identificacion, $nombre, $acreedor, $nombreacreedor, $matricula, $proponente, $idtipodoc, $numdoc, $fechadoc, $idorigendoc, $txtorigendoc, $idclasificacion, $numcontrato, $idfuente, $version, $path, $estado, $fechaescaneo, $idusuarioescaneo, $idcajaarchivo, $idlibroarchivo, $observaciones, $libro, $registro, $dupli, $bandeja, $soporterecibo, $identificador, $tipoanexo, $procesoespecial, $nir, $nuc, $datareferencia);
    }

    /**
     * 
     * @param type $dbx
     * @param type $fecha
     * @param type $hora
     * @param type $mat
     * @param type $pro
     * @param type $tipoid
     * @param type $numid
     * @param type $nom
     * @param type $tt
     * @param type $reg
     * @param type $lib
     * @param type $numreg
     * @param type $rec
     * @param type $ope
     * @param type $codbar
     * @param type $xmlo
     * @param type $xmlf
     * @param type $usu
     * @param type $ip
     * @return type
     */
    public static function grabarHistoricos($dbx, $fecha = '', $hora = '', $mat = '', $pro = '', $tipoid = '', $numid = '', $nom = '', $tt = '', $reg = '', $lib = '', $numreg = '', $rec = '', $ope = '', $codbar = '', $xmlo = '', $xmlf = '', $usu = '', $ip = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarHistoricos.php';
        return \funcionesRegistrales_grabarHistoricos::grabarHistoricos($dbx, $fecha, $hora, $mat, $pro, $tipoid, $numid, $nom, $tt, $reg, $lib, $numreg, $rec, $ope, $codbar, $xmlo, $xmlf, $usu, $ip);
    }


    /**
     * 
     * @param type $dbx
     * @param type $datax
     * @return type
     */
    public static function grabarLiquidacionMreg($dbx = null, $datat = array())
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarLiquidacionMreg.php';
        return \funcionesRegistrales_grabarLiquidacionMreg::grabarLiquidacionMreg($dbx, $datat);
    }

    /**
     * 
     * @param type $dbx
     * @param type $idliquidacion
     * @param type $expediente
     * @param type $xml1
     * @return type
     */
    public static function grabarMregLiquidacionDatosAnteriores($dbx, $idliquidacion, $expediente, $xml1)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarMregLiquidacionDatosAnteriores.php';
        return \funcionesRegistrales_grabarMregLiquidacionDatosAnteriores::grabarMregLiquidacionDatosAnteriores($dbx, $idliquidacion, $expediente, $xml1);
    }

    /**
     * 
     * @param type $dbx
     * @param type $idanexo
     * @param type $path
     * @return type
     */
    public static function grabarPathAnexoRadicacion($dbx = null, $idanexo = '', $path = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarPathAnexoRadicacion.php';
        return \funcionesRegistrales_grabarPathAnexoRadicacion::grabarPathAnexoRadicacion($dbx, $idanexo, $path);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $cb
     * @param type $estado
     * @param type $nameLog
     * @param type $idmotivoe
     * @param type $motivoe
     * @param type $idusuarioe
     * @return type
     */
    public static function grabarRegistroAnulacion($mysqli, $cb, $estado, $nameLog = '', $idmotivoe = '', $motivoe = '', $idusuarioe = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_grabarRegistroAnulacion.php';
        return \funcionesRegistrales_grabarRegistroAnulacion::grabarRegistroAnulacion($mysqli, $cb, $estado, $nameLog, $idmotivoe, $motivoe, $idusuarioe);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mat
     * @param type $fmat
     * @param type $fren
     * @return type
     */
    public static function inactivarSiprefMatriculas($mysqli, $mat, $fmat, $fren)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_inactivarSiprefMatriculas.php';
        return \funcionesRegistrales_inactivarSiprefMatriculas::inactivarSiprefMatriculas($mysqli, $mat, $fmat, $fren);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $ide
     * @return type
     */
    public static function localizarSaldoPrepago($mysqli, $ide)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_localizarSaldoPrepago.php';
        return \funcionesRegistrales_localizarSaldoPrepago::localizarSaldoPrepago($mysqli, $ide);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mat
     * @param type $org
     * @param type $cat
     * @param type $libs
     * @param type $motivox
     * @return type
     */
    public static function localizarMotivoCancelacion($mysqli, $mat, $org, $cat, $libs, $motivox)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_localizarMotivoCancelacion.php';
        return \funcionesRegistrales_localizarMotivoCancelacion::localizarMotivoCancelacion($mysqli, $mat, $org, $cat, $libs, $motivox);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $numcon
     * @param type $condicion
     * @return type
     */
    public static function matrizActividadEconomica($mysqli, $numcon, $condicion)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_matrizActividadEconomica.php';
        return \funcionesRegistrales_matrizActividadEconomica::matrizActividadEconomica($mysqli, $numcon, $condicion);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $numcon
     * @param type $condicion
     * @return type
     */
    public static function matrizActividadEconomicaS($mysqli, $numcon, $condicion)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_matrizActividadEconomicaS.php';
        return \funcionesRegistrales_matrizActividadEconomicaS::matrizActividadEconomicaS($mysqli, $numcon, $condicion);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tipo
     * @param type $anno
     * @param type $fechaini
     * @param type $fechafin
     * @param type $condicionPresencial
     * @param type $condicionVirtual
     * @param type $condicionRueReceptora
     * @param type $condicionRueResponsable
     * @param type $servicios
     * @param type $serviciosRenovacion
     * @param type $serviciosCertificados
     * @return type
     */
    public static function matrizActividadservicio($mysqli, $tipo, $anno, $fechaini, $fechafin, $condicionPresencial, $condicionVirtual, $condicionRueReceptora, $condicionRueResponsable, $servicios, $serviciosRenovacion, $serviciosCertificados)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_matrizActividadservicio.php';
        return \funcionesRegistrales_matrizActividadservicio::matrizActividadservicio($mysqli, $tipo, $anno, $fechaini, $fechafin, $condicionPresencial, $condicionVirtual, $condicionRueReceptora, $condicionRueResponsable, $servicios, $serviciosRenovacion, $serviciosCertificados);
    }

    /**
     * 
     * @param type $mysqli
     * @return type
     */
    public static function matrizRevisionfiscal($mysqli = null, $listavinc = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_matrizRevisionfiscal.php';
        return \funcionesRegistrales_matrizRevisionfiscal::matrizRevisionfiscal($mysqli, $listavinc);
    }

    /**
     * 
     * @param type $mysqli
     * @return type
     */
    public static function validarParametrosFirmado($mysqli = null)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarParametrosFirmado.php';
        return \funcionesRegistrales_validarParametrosFirmado::validarParametrosFirmado($mysqli);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $tram
     * @param type $expentrada
     * @return type
     */
    public static function validarFirmante($mysqli, $tram = array(), $expentrada = false)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarFirmante.php';
        return \funcionesRegistrales_validarFirmante::validarFirmante($mysqli, $tram, $expentrada);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $exp
     * @return type
     */
    public static function validarFirmanteReactivacion($mysqli, $exp)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarFirmanteReactivacion.php';
        return \funcionesRegistrales_validarFirmanteReactivacion::validarFirmanteReactivacion($mysqli, $exp);
    }

    /**
     * 
     * @param type $mysqli
     * @return type
     */
    public static function validarFirmanteReingresoGenerico($mysqli)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarFirmanteReingresoGenerico.php';
        return \funcionesRegistrales_validarFirmanteReingresoGenerico::validarFirmanteReingresoGenerico($mysqli);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $exp
     * @return type
     */
    public static function validarFirmanteMatriculaPnatEst($mysqli, $exp)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarFirmanteMatriculaPnatEst.php';
        return \funcionesRegistrales_validarFirmanteMatriculaPnatEst::validarFirmanteMatriculaPnatEst($mysqli, $exp);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $liquidacion
     * @return type
     */
    public static function validarMatriculasRenovadas($mysqli, $liquidacion)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarMatriculasRenovadas.php';
        return \funcionesRegistrales_validarMatriculasRenovadas::validarMatriculasRenovadas($mysqli, $exp);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $mun
     * @return type
     */
    public static function validarMunicipioJurisdiccion($mysqli, $mun)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarMunicipioJurisdiccion.php';
        return \funcionesRegistrales_validarMunicipioJurisdiccion::validarMunicipioJurisdiccion($mysqli, $mun);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $numliq
     * @param type $logName
     * @return type
     */
    public static function validarPagoPendienteLog($mysqli, $numliq, $logName = 'validarPagoPendienteLog_')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarPagoPendienteLog.php';
        return \funcionesRegistrales_validarPagoPendienteLog::validarPagoPendienteLog($mysqli, $numliq, $logName);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $codcam
     * @param type $servicio
     * @param type $ano
     * @return type
     */
    public static function validarReglasEspecialesRenovacion($mysqli, $codcam = '', $servicio = '', $ano = '')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarReglasEspecialesRenovacion.php';
        return \funcionesRegistrales_validarReglasEspecialesRenovacion::validarReglasEspecialesRenovacion($mysqli, $codcam, $servicio, $ano);
    }

    /**
     * 
     * @param type $mysqli
     * @return type
     */
    public static function validarUsuarioVerificado($mysqli)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_validarUsuarioVerificado.php';
        return \funcionesRegistrales_validarUsuarioVerificado::validarUsuarioVerificado($mysqli);
    }

    /**
     * 
     * @param type $dbx
     * @param type $numliq
     * @param type $numrad
     * @param type $tipotra
     * @param type $datos
     * @param type $codigoEmpresa
     * @param type $version
     * @return type
     */
    public static function verificarDatosModificadosApi($dbx, $numliq, $numrad, $tipotra, $datos, $codigoEmpresa = '', $version = '1510')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_verificarDatosModificadosApi.php';
        return \funcionesRegistrales_verificarDatosModificadosApi::verificarDatosModificadosApi($dbx, $numliq, $numrad, $tipotra, $datos, $codigoEmpresa, $version);
    }


    public static function encontrarAnexoBalances($mysqli, $idliq)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_encontrarAnexoBalances.php';
        return \funcionesRegistrales_encontrarAnexoBalances::encontrarAnexoBalances($mysqli, $idliq);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $control
     * @param type $tipoenvio
     * @return type
     */
    public static function encontrarNuevas($mysqli, $control, $tipoenvio = '1')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_encontrarNuevas.php';
        return \funcionesRegistrales_encontrarNuevas::encontrarNuevas($mysqli, $control, $tipoenvio);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $control
     * @param type $tipoenvio
     * @return type
     */
    public static function encontrarModificaciones($mysqli, $control, $tipoenvio = '1')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_encontrarModificaciones.php';
        return \funcionesRegistrales_encontrarModificaciones::encontrarModificaciones($mysqli, $control, $tipoenvio);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $control
     * @param type $tipoenvio
     * @return type
     */
    public static function encontrarCancelaciones($mysqli, $control, $tipoenvio = '1')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_encontrarCancelaciones.php';
        return \funcionesRegistrales_encontrarCancelaciones::encontrarCancelaciones($mysqli, $control, $tipoenvio);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $exp
     * @param type $tiporeporte
     * @param type $sistemadestino
     * @param type $tipoenvio: 0.- pruebas 1.- Produccion
     * @return type
     */
    public static function construirJsonSistemasExternos($mysqli, $exp, $tiporeporte, $sistemadestino, $tipoenvio = '1')
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_construirJsonSistemasExternos.php';
        return \funcionesRegistrales_construirJsonSistemasExternos::construirJsonSistemasExternos($mysqli, $exp, $tiporeporte, $sistemadestino, $tipoenvio);
    }

    /**
     * 
     * @param type $mysqli
     * @param type $exp
     * @return type
     */
    public static function construirJsonGeoreferenciacion($mysqli, $exp)
    {
        require_once $_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales_construirJsonGeoreferenciacion.php';
        return \funcionesRegistrales_construirJsonGeoreferenciacion::construirJsonGeoreferenciacion($mysqli, $exp);
    }

    public static function validarCancelacionEnTramite($mysqli, $tra)
    {
        $cancelacionentramite = '';
        $estadosvalidos = array('00', '05', '06', '07', '15', '16', '17', '19', '39,', '40', '41', '42', '99');
        foreach ($tra["expedientes"] as $exp1) {
            $liqs = retornarRegistrosMysqliApi($mysqli, 'mreg_liquidacion', "idexpedientebase='" . $exp1["matricula"] . "'or idmatriculabase='" . $exp1["matricula"] . "'", "fecha");
            foreach ($liqs as $lq) {
                if (substr($lq["tipotramite"], 0, 20) == 'solicitudcancelacion') {
                    if ($lq["numerorecibo"] != '' && $lq["numeroradicacion"] != '') {
                        $cb = retornarRegistroMysqliApi($mysqli, 'mreg_est_codigosbarras', "codigobarras='" . $lq["numeroradicacion"] . "'");
                        if ($cb && !empty($cb)) {
                            if (in_array($cb["estadofinal"], $estadosvalidos) === false) {
                                if ($cancelacionentramite != '') {
                                    $cancelacionentramite .= '<br>';
                                }
                                $cancelacionentramite .= 'Para el expediente No. ' . $exp1["matricula"] . ' existe una cancelación en trámite';
                            }
                        }
                    }
                }
            }
        }
        return $cancelacionentramite;
    }
}
