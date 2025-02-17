<?php

class funcionesRegistrales_actualizarMregEstInscritosProponente {

    public static function actualizarMregEstInscritosProponente($dbx, $data, $acto = '', $gm = array(), $estado = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        $cerrarmysql = 'no';
        if ($dbx == null) {
            $dbx = conexionMysqliApi();
            $cerrarmysql = 'si';
        }

        $nameLog = 'actualizarMregEstInscritosProponente_' . date("Ymd");

        // ****************************************************************************** //
        // GESTION GENERAL DE ERRORES
        // ****************************************************************************** //
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        \logApi::general2($nameLog, '', 'Entro a actualizarMregEstInscritosProponente, Acto ' . $acto);

        $tx1 = '';
        foreach ($data as $d => $v) {
            if (!is_array($v)) {
                $tx1 .= $d . ' = ' . $v . chr(13) . chr(10);
            }
        }
        \logApi::general2($nameLog, '', 'Datos recibidos : ' . $tx1);

        // ********************************************************************************* //
        // Si se trata de un acto cancelación, unicamente actualiza la fecha de cancelacion
        // y el estado del proponente
        // ********************************************************************************* //
        if ($acto == '04' || $acto == '05' || $acto == '15') {
            \logApi::general2($nameLog, '', 'En caso de Cancelacion, cesacion o cancelacion por cambidom');

            // Actualiza mreg_est_proponentes
            $arrCampos = array('idestadoproponente', 'fechacancelacion', 'fecactualizacion', 'fecsincronizacion', 'horsincronizacion', 'compite360');
            $arrValores = array("'01'", "'" . date("Ymd") . "'", "'" . date("Ymd") . "'", "''", "''", "'NO'");
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_proponentes', $arrCampos, $arrValores, "proponente='" . $data["proponente"] . "'");
            if ($res === false) {
                \logApi::general2($nameLog, '', '1.- Error : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }

            // Actualiza mreg_est_inscritos
            unset($_SESSION["expedienteactual"]);
            $arrCampos = array('ctrestproponente', 'fecactualizacion', 'compite360', 'rues', 'ivc');
            $arrValores = array("'01'", "'" . date("Ymd") . "'", "'NO'", "'NO'", "'NO'");
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "proponente='" . $data["proponente"] . "'");
            if ($res === false) {
                \logApi::general2('persistenciamreg_actualizarMreg_' . date("Ymd"), '', '2.- Error : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
            return true;
        }


        // ****************************************************************************** //
        // ACTUALIZA O INSERTA REGISTRO EN MREG_EST_INSCRITOS
        // ****************************************************************************** //    
        $idx = 0;
        $matricula = '';
        $arrMatIns = false;

        // ********************************************************************************************************** //    
        // En caso que requiera desasociar la matrícula
        // ********************************************************************************************************** //        
        if (isset($data["matriculadesasociar"]) && $data["matriculadesasociar"] != '') {
            $arrCampos = array('proponente', 'ctrestproponente', 'fecactualizacion', 'fecsincronizacion', 'horsincronizacion', 'compite360', 'rues', 'ivc');
            $arrValores = array("''", "''", "'" . date("Ymd") . "'", "''", "''", "'NO'", "'NO'", "'NO'");
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $data["matriculadesasociar"] . "'");
        }

        // ********************************************************************************************************** //    
        // Si debe asociarse con un previamente matriculado
        // En este caso priman los datos del registro mercantil
        // ********************************************************************************************************** //    
        if ($data["matricula"] != '') {
            $arrCampos = array('proponente', 'ctrestproponente', 'fecactualizacion', 'fecsincronizacion', 'horsincronizacion', 'compite360', 'rues', 'ivc');
            $arrValores = array("'" . $data["proponente"] . "'", "'00'", "'" . date("Ymd") . "'", "''", "''", "'NO'", "'NO'", "'NO'");
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "matricula='" . $data["matricula"] . "'");
        } else {
            if ($data["proponente"] != '') {
                $condicion = "proponente='" . ltrim($data["proponente"], "0") . "' and organizacion <> '02' and categoria <> '2' and categoria <> '3'";
                $arrMatIns = retornarRegistroMysqliApi($dbx, 'mreg_est_inscritos', $condicion);
                $iCampos = -1;
                $iCampos++;
                $arrCampos[$iCampos] = 'matricula';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'proponente';
                $arrValores[$iCampos] = "'" . ltrim($data["proponente"], "0") . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'organizacion';
                $arrValores[$iCampos] = "'" . $data["organizacion"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'categoria';
                if ($data["organizacion"] == '01') {
                    $arrValores[$iCampos] = "'0'";
                } else {
                    $arrValores[$iCampos] = "'1'";
                }
                $iCampos++;
                $arrCampos[$iCampos] = 'razonsocial';
                $arrValores[$iCampos] = "'" . addslashes($data["nombre"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'nombre1';
                $arrValores[$iCampos] = "'" . addslashes($data["nom1"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'nombre2';
                $arrValores[$iCampos] = "'" . addslashes($data["nom2"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'apellido1';
                $arrValores[$iCampos] = "'" . addslashes($data["ape1"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'apellido2';
                $arrValores[$iCampos] = "'" . addslashes($data["ape2"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'idclase';
                $arrValores[$iCampos] = "'" . $data["idtipoidentificacion"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'numid';
                $arrValores[$iCampos] = "'" . $data["identificacion"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'nit';
                $arrValores[$iCampos] = "'" . $data["nit"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'nacionalidad';
                $arrValores[$iCampos] = "'" . $data["nacionalidad"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'fecmatricula';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'fecrenovacion';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'ultanoren';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'feccancelacion';
                $arrValores[$iCampos] = "''";
                if (isset($data["fechaconstitucion"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'fecconstitucion';
                    $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechaconstitucion"]) . "'";
                }
                $iCampos++;
                $arrCampos[$iCampos] = 'fecdisolucion';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'fecliquidacion';
                $arrValores[$iCampos] = "''";

                if (isset($data["fechavencimiento"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'fecvigencia';
                    $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechavencimiento"]) . "'";
                }

                $iCampos++;
                $arrCampos[$iCampos] = 'tamanoempresa';
                $arrValores[$iCampos] = "'" . $data["tamanoempresa"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'emprendedor28';
                $arrValores[$iCampos] = "'" . $data["emprendedor28"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'pemprendedor28';
                $arrValores[$iCampos] = doubleval($data["emprendedor28"]);
                $iCampos++;
                $arrCampos[$iCampos] = 'vigcontrol';
                $arrValores[$iCampos] = "''";

                if (isset($data["numdocperjur"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'numperj';
                    $arrValores[$iCampos] = "'" . $data["numdocperjur"] . "'";
                }

                if (isset($data["fechaconstitucion"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'fecperj';
                    $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechaconstitucion"]) . "'";
                }

                if (isset($data["origendocperjur"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'otorgaperj';
                    $arrValores[$iCampos] = "'" . addslashes($data["origendocperjur"]) . "'";
                }

                if (isset($data["numdocperjur"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'numdocconst';
                    $arrValores[$iCampos] = "'" . $data["numdocperjur"] . "'";
                }

                if (isset($data["fecdocperjur"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'fecdocconst';
                    $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fecdocperjur"]) . "'";
                }

                if (isset($data["origendocperjur"])) {
                    $iCampos++;
                    $arrCampos[$iCampos] = 'origendocconst';
                    $arrValores[$iCampos] = "'" . addslashes($data["origendocperjur"]) . "'";
                }

                $iCampos++;
                $arrCampos[$iCampos] = 'fecmatant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'fecrenant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'camant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'munant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'ultanorenant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'matant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'benart7ant';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'benley1780ant';
                $arrValores[$iCampos] = "''";

                $iCampos++;
                $arrCampos[$iCampos] = 'dircom';
                $arrValores[$iCampos] = "'" . addslashes($data["dircom"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'muncom';
                $arrValores[$iCampos] = "'" . $data["muncom"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'telcom1';
                $arrValores[$iCampos] = "'" . $data["telcom1"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'telcom2';
                $arrValores[$iCampos] = "'" . $data["telcom2"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'telcom3';
                $arrValores[$iCampos] = "'" . $data["celcom"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'faxcom';
                $arrValores[$iCampos] = "'" . $data["faxcom"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'emailcom';
                $arrValores[$iCampos] = "'" . addslashes($data["emailcom"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'emailcom2';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'emailcom3';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'urlcom';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'numpredial';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'latitud';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'longitud';
                $arrValores[$iCampos] = "''";

                $iCampos++;
                $arrCampos[$iCampos] = 'dirnot';
                $arrValores[$iCampos] = "'" . addslashes($data["dirnot"]) . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'munnot';
                $arrValores[$iCampos] = "'" . $data["munnot"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'telnot';
                $arrValores[$iCampos] = "'" . $data["telnot"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'telnot2';
                $arrValores[$iCampos] = "'" . $data["telnot2"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'telnot3';
                $arrValores[$iCampos] = "'" . $data["celnot"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'faxnot';
                $arrValores[$iCampos] = "'" . $data["faxnot"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'emailnot';
                $arrValores[$iCampos] = "'" . addslashes($data["emailnot"]) . "'";

                $iCampos++;
                $arrCampos[$iCampos] = 'ctrestmatricula';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'ctrestproponente';
                $arrValores[$iCampos] = "'00'";

                $iCampos++;
                $arrCampos[$iCampos] = 'ctrestdatos';
                $arrValores[$iCampos] = "''";
                $iCampos++;
                $arrCampos[$iCampos] = 'ctrnotemail';
                $arrValores[$iCampos] = "'" . $data["enviarnot"] . "'";
                $iCampos++;
                $arrCampos[$iCampos] = 'ctrnotsms';
                $arrValores[$iCampos] = "'" . $data["enviarint"] . "'";

                $iCampos++;
                $arrCampos[$iCampos] = 'fecactualizacion';
                $arrValores[$iCampos] = "'" . date("Ymd") . "'";

                $iCampos++;
                $arrCampos[$iCampos] = 'fecsincronizacion';
                $arrValores[$iCampos] = "''";

                $iCampos++;
                $arrCampos[$iCampos] = 'horsincronizacion';
                $arrValores[$iCampos] = "''";

                $iCampos++;
                $arrCampos[$iCampos] = 'compite360';
                $arrValores[$iCampos] = "'NO'";

                $iCampos++;
                $arrCampos[$iCampos] = 'rues';
                $arrValores[$iCampos] = "'NO'";

                $iCampos++;
                $arrCampos[$iCampos] = 'ivc';
                $arrValores[$iCampos] = "'NO'";

                if ($arrMatIns && !empty($arrMatIns)) {
                    regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "proponente='" . $data["proponente"] . "'");
                } else {
                    insertarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores);
                }
            }
        }


        // ****************************************************************************** //
        // ACTUALIZA O INSERTA REGISTRO EN MREG_EST_PROPONENTES
        // ****************************************************************************** //
        $insertar = 'si';

        // Si el proponente existe y está asociado con una matrícula cancelada, entonces debe separar los registros.
        if (ltrim($data["proponente"], "0") != '') {
            $arrMatPro = retornarRegistroMysqliApi($dbx, 'mreg_est_proponentes', "proponente='" . ltrim($data["proponente"], "0") . "'");
            if ($arrMatPro && !empty($arrMatPro)) {
                $insertar = 'no';
            }
        }

        // Datos básicos
        $iCampos = -1;
        $arrCampos = array();
        $arrValores = array();

        $iCampos++;
        $arrCampos[$iCampos] = 'proponente';
        $arrValores[$iCampos] = "'" . ltrim($data["proponente"], "0") . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'matricula';
        $arrValores[$iCampos] = "'" . ltrim($data["matricula"], "0") . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'nombre';
        $arrValores[$iCampos] = "'" . addslashes($data["nombre"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'nom1';
        $arrValores[$iCampos] = "'" . addslashes($data["nom1"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'nom2';
        $arrValores[$iCampos] = "'" . addslashes($data["nom2"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'ape1';
        $arrValores[$iCampos] = "'" . addslashes($data["ape1"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'ape2';
        $arrValores[$iCampos] = "'" . addslashes($data["ape2"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'sigla';
        $arrValores[$iCampos] = "'" . addslashes($data["sigla"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'idtipoidentificacion';
        $arrValores[$iCampos] = "'" . $data["idtipoidentificacion"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'identificacion';
        $arrValores[$iCampos] = "'" . $data["identificacion"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'nit';
        $arrValores[$iCampos] = "'" . $data["nit"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'nacionalidad';
        $arrValores[$iCampos] = "'" . $data["nacionalidad"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'organizacion';
        $arrValores[$iCampos] = "'" . $data["organizacion"] . "'";

        // Valida la actualizacion en firme del tamaño de la empresa
        if ($acto == '' || $acto == '16') {
            $iCampos++;
            $arrCampos[$iCampos] = 'tamanoempresa';
            $arrValores[$iCampos] = "'" . $data["tamanoempresa"] . "'";
        }
        if ($acto == '01') {
            $iCampos++;
            $arrCampos[$iCampos] = 'tamanoempresa';
            $arrValores[$iCampos] = "''";
        }

        $iCampos++;
        $arrCampos[$iCampos] = 'emprendedor28';
        $arrValores[$iCampos] = "'" . $data["emprendedor28"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'pemprendedor28';
        $arrValores[$iCampos] = doubleval($data["pemprendedor28"]);

        $iCampos++;
        $arrCampos[$iCampos] = 'vigcontrol';
        $arrValores[$iCampos] = "'" . $data["vigcontrol"] . "'";

        // Valida la actualizacion en firme de los datos de cambio de domicilio
        if ($acto == '' || $acto == '16') {
            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_fechaultimainscripcion';
            $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["cambidom_fechaultimainscripcion"]) . "'";

            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_fechaultimarenovacion';
            $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["cambidom_fechaultimarenovacion"]) . "'";

            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_idmunicipioorigen';
            $arrValores[$iCampos] = "'" . $data["cambidom_idmunicipioorigen"] . "'";

            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_idmunicipiodestino';
            $arrValores[$iCampos] = "'" . $data["cambidom_idmunicipiodestino"] . "'";
        }
        if ($acto == '01') {
            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_fechaultimainscripcion';
            $arrValores[$iCampos] = "''";

            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_fechaultimarenovacion';
            $arrValores[$iCampos] = "''";

            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_idmunicipioorigen';
            $arrValores[$iCampos] = "''";

            $iCampos++;
            $arrCampos[$iCampos] = 'cambidom_idmunicipiodestino';
            $arrValores[$iCampos] = "''";
        }


        $iCampos++;
        $arrCampos[$iCampos] = 'idestadoproponente';
        $arrValores[$iCampos] = "'" . $data["idestadoproponente"] . "'";

        if ($acto == '') {
            if (!isset($data["certificardesde"])) {
                $data["certificardesde"]  = '';
            }
            $iCampos++;
            $arrCampos[$iCampos] = 'certificardesde';
            $arrValores[$iCampos] = "'" . $data["certificardesde"] . "'";
        }

        $iCampos++;
        $arrCampos[$iCampos] = 'fechaultimainscripcion';
        $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechaultimainscripcion"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'fechaultimarenovacion';
        $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechaultimarenovacion"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'fechaultimaactualizacion';
        $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechaultimaactualizacion"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'fechacancelacion';
        $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechacancelacion"]) . "'";

        // Valida la actualizacion en firme de los datos de constitución
        if ($acto == '' || $acto == '16') {
            if (isset($data["idtipodocperjur"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'idtipodocperjur';
                $arrValores[$iCampos] = "'" . $data["idtipodocperjur"] . "'";
            }

            if (isset($data["numdocperjur"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'numdocperjur';
                $arrValores[$iCampos] = "'" . $data["numdocperjur"] . "'";
            }

            if (isset($data["origendocperjur"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'origendocperjur';
                $arrValores[$iCampos] = "'" . $data["origendocperjur"] . "'";
            }

            if (isset($data["fechaconstitucion"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'fechaconstitucion';
                $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechaconstitucion"]) . "'";
            }

            if (isset($data["fechavencimiento"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'fechavencimiento';
                $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["fechavencimiento"]) . "'";
            }
        }
        if ($acto == '01' && $matricula == '') {
            if (isset($data["idtipodocperjur"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'idtipodocperjur';
                $arrValores[$iCampos] = "''";
            }

            if (isset($data["numdocperjur"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'numdocperjur';
                $arrValores[$iCampos] = "''";
            }

            if (isset($data["origendocperjur"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'origendocperjur';
                $arrValores[$iCampos] = "''";
            }

            if (isset($data["fechaconstitucion"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'fechaconstitucion';
                $arrValores[$iCampos] = "''";
            }

            if (isset($data["fechavencimiento"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'fechavencimiento';
                $arrValores[$iCampos] = "''";
            }
        }

        $iCampos++;
        $arrCampos[$iCampos] = 'dircom';
        $arrValores[$iCampos] = "'" . addslashes($data["dircom"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'muncom';
        $arrValores[$iCampos] = "'" . $data["muncom"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'telcom1';
        $arrValores[$iCampos] = "'" . $data["telcom1"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'telcom2';
        $arrValores[$iCampos] = "'" . $data["telcom2"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'celcom';
        $arrValores[$iCampos] = "'" . $data["celcom"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'faxcom';
        $arrValores[$iCampos] = "'" . $data["faxcom"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'emailcom';
        $arrValores[$iCampos] = "'" . addslashes($data["emailcom"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'dirnot';
        $arrValores[$iCampos] = "'" . addslashes($data["dirnot"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'munnot';
        $arrValores[$iCampos] = "'" . $data["munnot"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'telnot';
        $arrValores[$iCampos] = "'" . $data["telnot"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'telnot2';
        $arrValores[$iCampos] = "'" . $data["telnot2"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'celnot';
        $arrValores[$iCampos] = "'" . $data["celnot"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'faxnot';
        $arrValores[$iCampos] = "'" . $data["faxnot"] . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'emailnot';
        $arrValores[$iCampos] = "'" . addslashes($data["emailnot"]) . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'enviarnot';
        $arrValores[$iCampos] = "'" . ($data["enviarnot"]) . "'";

        // Valida la actualizacion en firme de los datos de constitución
        if ($acto == '' || $acto == '16') {
            if (isset($data["facultades"])) {
                $iCampos++;
                $arrCampos[$iCampos] = 'facultades';
                $arrValores[$iCampos] = "'" . addslashes($data["facultades"]) . "'";
            }
        }
        if ($acto == '01') {
            $iCampos++;
            $arrCampos[$iCampos] = 'facultades';
            $arrValores[$iCampos] = "''";
        }

        // Valida la actualizacion en firme de los datos financieros
        if ($acto == '' || $acto == '16') {

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_fechacorte';
            $arrValores[$iCampos] = "'" . str_replace(array("/","-"),"",$data["inffin1510_fechacorte"]) . "'";

            if (!isset($data["inffin1510_gruponiif"])) {
                $data["inffin1510_gruponiif"] = '';
            }
            $iCampos++;
            $arrCampos[$iCampos] = 'gruponiif';
            $arrValores[$iCampos] = "'" . $data["inffin1510_gruponiif"] . "'";

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actcte';
            $arrValores[$iCampos] = doubleval($data["inffin1510_actcte"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actnocte';
            $arrValores[$iCampos] = doubleval($data["inffin1510_actnocte"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_fijnet';
            $arrValores[$iCampos] = doubleval($data["inffin1510_fijnet"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actotr';
            $arrValores[$iCampos] = doubleval($data["inffin1510_actotr"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actval';
            $arrValores[$iCampos] = doubleval($data["inffin1510_actval"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_acttot';
            $arrValores[$iCampos] = doubleval($data["inffin1510_acttot"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_pascte';
            $arrValores[$iCampos] = doubleval($data["inffin1510_pascte"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_paslar';
            $arrValores[$iCampos] = doubleval($data["inffin1510_paslar"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_pastot';
            $arrValores[$iCampos] = doubleval($data["inffin1510_pastot"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_patnet';
            $arrValores[$iCampos] = doubleval($data["inffin1510_patnet"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_paspat';
            $arrValores[$iCampos] = doubleval($data["inffin1510_paspat"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_balsoc';
            $arrValores[$iCampos] = doubleval($data["inffin1510_balsoc"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_ingope';
            $arrValores[$iCampos] = doubleval($data["inffin1510_ingope"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_ingnoope';
            $arrValores[$iCampos] = doubleval($data["inffin1510_ingnoope"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasope';
            $arrValores[$iCampos] = doubleval($data["inffin1510_gasope"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasnoope';
            $arrValores[$iCampos] = doubleval($data["inffin1510_gasnoope"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_cosven';
            $arrValores[$iCampos] = doubleval($data["inffin1510_cosven"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_utinet';
            $arrValores[$iCampos] = doubleval($data["inffin1510_utinet"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_utiope';
            $arrValores[$iCampos] = doubleval($data["inffin1510_utiope"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasint';
            $arrValores[$iCampos] = doubleval($data["inffin1510_gasint"]);

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasimp';
            $arrValores[$iCampos] = doubleval($data["inffin1510_gasimp"]);

            if ($data["inffin1510_indliq"] == 'INDEFINIDO') {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_indliq';
                $arrValores[$iCampos] = 0;
            } else {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_indliq';
                $arrValores[$iCampos] = $data["inffin1510_indliq"];
            }

            if ($data["inffin1510_nivend"] == 'INDEFINIDO') {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_nivend';
                $arrValores[$iCampos] = 0;
            } else {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_nivend';
                $arrValores[$iCampos] = doubleval($data["inffin1510_nivend"]);
            }

            if ($data["inffin1510_razcob"] == 'INDEFINIDO') {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_razcob';
                $arrValores[$iCampos] = 0;
            } else {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_razcob';
                $arrValores[$iCampos] = doubleval($data["inffin1510_razcob"]);
            }

            if ($data["inffin1510_renpat"] == 'INDEFINIDO') {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_renpat';
                $arrValores[$iCampos] = 0;
            } else {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_renpat';
                $arrValores[$iCampos] = doubleval($data["inffin1510_renpat"]);
            }

            if ($data["inffin1510_renact"] == 'INDEFINIDO') {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_renact';
                $arrValores[$iCampos] = 0;
            } else {
                $iCampos++;
                $arrCampos[$iCampos] = 'inffin1510_renact';
                $arrValores[$iCampos] = doubleval($data["inffin1510_renact"]);
            }
        }

        if ($acto == '01') {
            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_fechacorte';
            $arrValores[$iCampos] = "''";

            $iCampos++;
            $arrCampos[$iCampos] = 'gruponiif';
            $arrValores[$iCampos] = "''";

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actcte';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actnocte';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_fijnet';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actotr';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_actval';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_acttot';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_pascte';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_paslar';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_pastot';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_patnet';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_paspat';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_balsoc';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_ingope';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_ingnoope';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasope';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasnoope';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_cosven';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_utinet';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_utiope';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasint';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_gasimp';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_indliq';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_nivend';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_razcob';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_renpat';
            $arrValores[$iCampos] = 0;

            $iCampos++;
            $arrCampos[$iCampos] = 'inffin1510_renact';
            $arrValores[$iCampos] = 0;
        }


        $iCampos++;
        $arrCampos[$iCampos] = 'fecactualizacion';
        $arrValores[$iCampos] = "'" . date("Ymd") . "'";

        $iCampos++;
        $arrCampos[$iCampos] = 'fecsincronizacion';
        $arrValores[$iCampos] = "''";

        $iCampos++;
        $arrCampos[$iCampos] = 'horsincronizacion';
        $arrValores[$iCampos] = "''";

        $iCampos++;
        $arrCampos[$iCampos] = 'compite360';
        $arrValores[$iCampos] = "'NO'";

        if ($insertar === 'si') {
            $res = insertarRegistrosMysqliApi($dbx, 'mreg_est_proponentes', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, '', '14.- Error insertando mreg_est_proponentes: ' . $_SESSION["generales"]["mensajeerror"]);
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_est_proponentes : ' . $_SESSION["generales"]["mensajeerror"];
                return false;
            } else {
                \logApi::general2($nameLog, '', 'en mreg_est_proponentes se inserto un nuevo registro');
            }
        } else {
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_proponentes', $arrCampos, $arrValores, "proponente='" . ltrim($data["proponente"], "0") . "'");
            if ($res === false) {
                \logApi::general2($nameLog, '', '15.- Error regrabando mreg_est_proponentes: ' . $_SESSION["generales"]["mensajeerror"]);
                $_SESSION["generales"]["mensajeerror"] = 'Error regrabando registros en mreg_est_proponentes : ' . $_SESSION["generales"]["mensajeerror"];
                return false;
            } else {
                \logApi::general2($nameLog, '', 'en mreg_est_proponentes se regrabo el registro');
            }
        }

        // ****************************************************************************** //
        // ACTUALIZA O INSERTA REGISTROS EN MREG_EST_PROPONENTES_UNSPSC
        // ****************************************************************************** //    
        if (isset($data["clasi1510"]) && !empty($data["clasi1510"])) {
            $res = borrarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_unspsc', "proponente='" . ltrim($data["proponente"], "0") . "'");
            if ($res === false) {
                if ($res === false) {
                    \logApi::general2($nameLog, '', '16.- Error borrando mreg_est_proponentes_unspsc: ' . $_SESSION["generales"]["mensajeerror"]);
                    $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros en mreg_est_proponentes_unspsc : ' . $_SESSION["generales"]["mensajeerror"];
                    return false;
                }
            }

            $arrCampos = array(
                'proponente',
                'unspsc'
            );
            $arrValores = array();
            $iCla = 0;
            foreach ($data["clasi1510"] as $c) {
                $iCla++;
                $arrValores[$iCla] = array(
                    "'" . ltrim($data["proponente"], "0") . "'",
                    "'" . trim($c) . "'"
                );
            }

            $res = insertarRegistrosBloqueMysqliApi($dbx, 'mreg_est_proponentes_unspsc', $arrCampos, $arrValores);
            if ($res === false) {
                \logApi::general2($nameLog, '', '17.- Error insertando mreg_est_proponentes_unspsc: ' . $_SESSION["generales"]["mensajeerror"]);
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_est_proponentes_unspsc : ' . $_SESSION["generales"]["mensajeerror"];
                return false;
            }
            \logApi::general2($nameLog, '', 'Actualizo mreg_est_proponentes_unspsc');
        }


        // ****************************************************************************** //
        // ACTUALIZA O INSERTA REGISTROS EN MREG_EST_PROPONENTES_REPRESENTACION
        // ****************************************************************************** //
        if (isset($data["representanteslegales"]) && !empty($data["representanteslegales"])) {
            if ($acto == '' || $acto == '01' || $acto == '16') {
                $res = borrarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_representacion', "proponente='" . ltrim($data["proponente"], "0") . "'");
                if ($res === false) {
                    \logApi::general2($nameLog, '', '18.- Error borrando mreg_est_proponentes_representacion: ' . $_SESSION["generales"]["mensajeerror"]);
                    $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros en mreg_est_proponentes_representacion : ' . $_SESSION["generales"]["mensajeerror"];
                    return false;
                }
            }
            if ($acto == '' || $acto == '16') {
                if ($matricula == '') {
                    $arrCampos = array(
                        'proponente',
                        'tipoidentificacion',
                        'identificacion',
                        'nombre',
                        'cargo'
                    );
                    $arrValores = array();
                    $iCla = 0;
                    foreach ($data["representanteslegales"] as $c) {
                        $iCla++;
                        $arrValores[$iCla] = array(
                            "'" . ltrim($data["proponente"], "0") . "'",
                            "'" . trim($c["idtipoidentificacionrepleg"]) . "'",
                            "'" . trim($c["identificacionrepleg"]) . "'",
                            "'" . addslashes(trim($c["nombrerepleg"])) . "'",
                            "'" . addslashes(trim($c["cargorepleg"])) . "'"
                        );
                    }
                    $res = insertarRegistrosBloqueMysqliApi($dbx, 'mreg_est_proponentes_representacion', $arrCampos, $arrValores);
                    if ($res === false) {
                        \logApi::general2($nameLog, '', '19.- Error insertando mreg_est_proponentes_representacion: ' . $_SESSION["generales"]["mensajeerror"]);
                        $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_est_proponentes_representacion : ' . $_SESSION["generales"]["mensajeerror"];
                        return false;
                    }
                    \logApi::general2($nameLog, '', 'Actualizo mreg_est_proponentes_representacion');
                }
            }
        }

        // ****************************************************************************** //
        // ACTUALIZA O INSERTA REGISTROS EN MREG_EST_PROPONENTES_SITCONTROL
        // ****************************************************************************** //
        if (isset($data["sitcontrol"]) && !empty($data["sitcontrol"])) {
            if ($acto == '' || $acto == '01' || $acto == '16') {
                $res = borrarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_sitcontrol', "proponente='" . ltrim($data["proponente"], "0") . "'");
                if ($res === false) {
                    \logApi::general2($nameLog, '', '20.- Error borrando mreg_est_proponentes_sitcontrol: ' . $_SESSION["generales"]["mensajeerror"]);
                    $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros en mreg_est_proponentes_sitcontrol : ' . $_SESSION["generales"]["mensajeerror"];
                    return false;
                }
            }
            if ($acto == '' || $acto == '16') {
                $arrCampos = array(
                    'proponente',
                    'secuencia',
                    'nombre',
                    'identificacion',
                    'tipo',
                    'domicilio'
                );
                $arrValores = array();
                $iCla = 0;
                foreach ($data["sitcontrol"] as $c) {
                    $iCla++;
                    $arrValores[$iCla] = array(
                        "'" . ltrim($data["proponente"], "0") . "'",
                        "'" . sprintf("%03s", $iCla) . "'",
                        "'" . addslashes(trim($c["nombre"])) . "'",
                        "'" . trim($c["identificacion"]) . "'",
                        "'" . trim($c["tipo"]) . "'",
                        "'" . addslashes(trim($c["domicilio"])) . "'"
                    );
                }
                $res = insertarRegistrosBloqueMysqliApi($dbx, 'mreg_est_proponentes_sitcontrol', $arrCampos, $arrValores);
                if ($res === false) {
                    \logApi::general2($nameLog, '', '21.- Error insertando mreg_est_proponentes_sitcontrol: ' . $_SESSION["generales"]["mensajeerror"]);
                    $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_est_proponentes_sitcontrol : ' . $_SESSION["generales"]["mensajeerror"];
                    return false;
                }
                \logApi::general2($nameLog, '', 'Actualizo mreg_est_proponentes_sitcontrol');
            }
        }


        // ****************************************************************************** //
        // ACTUALIZA O INSERTA REGISTROS EN MREG_EST_PROPONENTES_EXPERIENCIA
        // ****************************************************************************** //
        if (isset($data["exp1510"]) && !empty($data["exp1510"])) {
            if ($acto == '' || $acto == '01' || $acto == '16') {
                $res = borrarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_experiencia', "proponente='" . ltrim($data["proponente"], "0") . "'");
                if ($res === false) {
                    \logApi::general2($nameLog, '', '22.- Error borrando mreg_est_proponentes_experiencia: ' . $_SESSION["generales"]["mensajeerror"]);
                    $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros en mreg_est_proponentes_experiencia : ' . $_SESSION["generales"]["mensajeerror"];
                    return false;
                }
            }
            if ($acto == '' || $acto == '16') {
                $arrCampos = array(
                    'proponente',
                    'secuencia',
                    'celebradopor',
                    'nombrecontratista',
                    'nombrecontratante',
                    'valor',
                    'fecejecucion',
                    'valorpesos',
                    'porcentaje',
                    'clasificaciones'
                );
                $arrValores = array();
                $iCla = 0;
                foreach ($data["exp1510"] as $c) {
                    if (!isset($c["valorpesos"]) || $c["valorpesos"] == '') {
                        $c["valorpesos"] = 0;
                    }
                    if (!isset($c["fecejecucion"]) || $c["fecejecucion"] == '') {
                        $c["fecejecucion"] = '';
                    } else {
                        $c["fecejecucion"] = str_replace("-","",$c["fecejecucion"]);
                    }
                    
                    $clas = '';
                    foreach ($c["clasif"] as $cx) {
                        if ($clas != '') {
                            $clas .= ',';
                        }
                        $clas .= trim($cx);
                    }
                    $iCla++;

//
                    $secs = '';
                    if (intval($c["secuencia"]) <= 999) {
                        $secs = sprintf("%03s", $c["secuencia"]);
                    } else {
                        $secs = $c["secuencia"];
                    }
                    $arrValores[$iCla] = array(
                        "'" . ltrim($data["proponente"], "0") . "'",
                        "'" . $secs . "'",
                        "'" . trim($c["celebradopor"]) . "'",
                        "'" . addslashes(trim($c["nombrecontratista"])) . "'",
                        "'" . addslashes(trim($c["nombrecontratante"])) . "'",
                        doubleval($c["valor"]),
                        "'" . str_replace(array("/","-"),"",trim($c["fecejecucion"])) . "'",
                        doubleval($c["valorpesos"]),
                        doubleval($c["porcentaje"]),
                        "'" . addslashes(trim($clas)) . "'"
                    );
                }
                $res = insertarRegistrosBloqueMysqliApi($dbx, 'mreg_est_proponentes_experiencia', $arrCampos, $arrValores);
                if ($res === false) {
                    $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_est_proponentes_experiencia : ' . $_SESSION["generales"]["mensajeerror"];
                    \logApi::general2($nameLog, '', '23.- Error insertando mreg_est_proponentes_experiencia: ' . $_SESSION["generales"]["mensajeerror"]);
                    return false;
                }
                \logApi::general2($nameLog, '', 'Actualizo mreg_est_proponentes_experiencia');
            }
        }

        // ****************************************************************************** //
        // ACTUALIZA INFORMACION FINANCIERA
        // ****************************************************************************** //
        $arrCampos = array(
            'proponente',
            'libro',
            'registro',
            'codigobarras',
            'fechacorte',
            'actcte',
            'actnocte',
            'acttot',
            'pascte',
            'paslar',
            'pastot',
            'patnet',
            'paspat',
            'balsoc',
            'ingope',
            'ingnoope',
            'gasope',
            'gasnoope',
            'cosven',
            'gasimp',
            'gasint',
            'utiope',
            'utinet',
            'indliq',
            'nivend',
            'razcob',
            'renpat',
            'renact',
            'gruponiif'
        );
        if ($acto == '01' || $acto == '02' || $acto == '16' || $acto == '36') {
            if ($acto != '36') {
                if (isset($data["inffin1510_fechacorte"]) && $data["inffin1510_fechacorte"] != '') {
                    $arrValores = array(
                        "'" . ltrim($data["proponente"], "0") . "'",
                        "'" . $data["libro"] . "'",
                        "'" . $data["registro"] . "'",
                        "'" . $data["codigobarras"] . "'",
                        "'" . str_replace(array("/","-"),"",$data["inffin1510_fechacorte"]) . "'",
                        doubleval($data["inffin1510_actcte"]),
                        doubleval($data["inffin1510_actnocte"]),
                        doubleval($data["inffin1510_acttot"]),
                        doubleval($data["inffin1510_pascte"]),
                        doubleval($data["inffin1510_paslar"]),
                        doubleval($data["inffin1510_pastot"]),
                        doubleval($data["inffin1510_patnet"]),
                        doubleval($data["inffin1510_paspat"]),
                        doubleval($data["inffin1510_balsoc"]),
                        doubleval($data["inffin1510_ingope"]),
                        doubleval($data["inffin1510_ingnoope"]),
                        doubleval($data["inffin1510_gasope"]),
                        doubleval($data["inffin1510_gasnoope"]),
                        doubleval($data["inffin1510_cosven"]),
                        doubleval($data["inffin1510_gasimp"]),
                        doubleval($data["inffin1510_gasint"]),
                        doubleval($data["inffin1510_utiope"]),
                        doubleval($data["inffin1510_utinet"]),
                        doubleval($data["inffin1510_indliq"]),
                        doubleval($data["inffin1510_nivend"]),
                        doubleval($data["inffin1510_razcob"]),
                        doubleval($data["inffin1510_renpat"]),
                        doubleval($data["inffin1510_renact"]),
                        "'" . $data["inffin1510_gruponiif"] . "'"
                    );
                    if (contarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', "proponente='" . ltrim($data["proponente"], "0") . "' and libro='" . $data["libro"] . "' and registro='" . $data["registro"] . "' and fechacorte='" . $data["inffin1510_fechacorte"] . "'") == 0) {
                        insertarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', $arrCampos, $arrValores);
                    } else {
                        regrabarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', $arrCampos, $arrValores, "proponente='" . ltrim($data["proponente"], "0") . "' and libro='" . $data["libro"] . "' and registro='" . $data["registro"] . "' and fechacorte='" . $data["inffin1510_fechacorte"] . "'");
                    }
                }
            }
            if (isset($data["inffin399a_fechacorte"]) && $data["inffin399a_fechacorte"] != '' && $data["inffin399a_pregrabado"] != 'si') {
                $arrValores = array(
                    "'" . ltrim($data["proponente"], "0") . "'",
                    "'" . $data["libro"] . "'",
                    "'" . $data["registro"] . "'",
                    "'" . $data["codigobarras"] . "'",
                    "'" . str_replace(array("/","-"),"",$data["inffin399a_fechacorte"]) . "'",
                    doubleval($data["inffin399a_actcte"]),
                    doubleval($data["inffin399a_actnocte"]),
                    doubleval($data["inffin399a_acttot"]),
                    doubleval($data["inffin399a_pascte"]),
                    doubleval($data["inffin399a_paslar"]),
                    doubleval($data["inffin399a_pastot"]),
                    doubleval($data["inffin399a_patnet"]),
                    doubleval($data["inffin399a_paspat"]),
                    doubleval($data["inffin399a_balsoc"]),
                    doubleval($data["inffin399a_ingope"]),
                    doubleval($data["inffin399a_ingnoope"]),
                    doubleval($data["inffin399a_gasope"]),
                    doubleval($data["inffin399a_gasnoope"]),
                    doubleval($data["inffin399a_cosven"]),
                    doubleval($data["inffin399a_gasimp"]),
                    doubleval($data["inffin399a_gasint"]),
                    doubleval($data["inffin399a_utiope"]),
                    doubleval($data["inffin399a_utinet"]),
                    doubleval($data["inffin399a_indliq"]),
                    doubleval($data["inffin399a_nivend"]),
                    doubleval($data["inffin399a_razcob"]),
                    doubleval($data["inffin399a_renpat"]),
                    doubleval($data["inffin399a_renact"]),
                    "'" . $data["inffin399a_gruponiif"] . "'"
                );
                if (contarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', "proponente='" . ltrim($data["proponente"], "0") . "' and libro='" . $data["libro"] . "' and registro='" . $data["registro"] . "' and fechacorte='" . $data["inffin399a_fechacorte"] . "'") == 0) {
                    $resx = insertarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', $arrCampos, $arrValores);
                } else {
                    $resx = regrabarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', $arrCampos, $arrValores, "proponente='" . ltrim($data["proponente"], "0") . "' and libro='" . $data["libro"] . "' and registro='" . $data["registro"] . "' and fechacorte='" . $data["inffin399a_fechacorte"] . "'");
                }
                if ($resx === false) {
                    \logApi::general2($nameLog, '', '4.- Error actualizando mreg_est_proponentes_financiera 399a: ' . $_SESSION["generales"]["mensajeerror"]);
                } else {
                    \logApi::general2($nameLog, '', 'Actualizo mreg_est_proponentes_financiera 399a');
                }
            }

            if (isset($data["inffin399b_fechacorte"]) && $data["inffin399b_fechacorte"] != '' && $data["inffin399b_pregrabado"] != 'si') {
                $arrValores = array(
                    "'" . ltrim($data["proponente"], "0") . "'",
                    "'" . $data["libro"] . "'",
                    "'" . $data["registro"] . "'",
                    "'" . $data["codigobarras"] . "'",
                    "'" . str_replace(array("/","-"),"",$data["inffin399b_fechacorte"]) . "'",
                    doubleval($data["inffin399b_actcte"]),
                    doubleval($data["inffin399b_actnocte"]),
                    doubleval($data["inffin399b_acttot"]),
                    doubleval($data["inffin399b_pascte"]),
                    doubleval($data["inffin399b_paslar"]),
                    doubleval($data["inffin399b_pastot"]),
                    doubleval($data["inffin399b_patnet"]),
                    doubleval($data["inffin399b_paspat"]),
                    doubleval($data["inffin399b_balsoc"]),
                    doubleval($data["inffin399b_ingope"]),
                    doubleval($data["inffin399b_ingnoope"]),
                    doubleval($data["inffin399b_gasope"]),
                    doubleval($data["inffin399b_gasnoope"]),
                    doubleval($data["inffin399b_cosven"]),
                    doubleval($data["inffin399b_gasimp"]),
                    doubleval($data["inffin399b_gasint"]),
                    doubleval($data["inffin399b_utiope"]),
                    doubleval($data["inffin399b_utinet"]),
                    doubleval($data["inffin399b_indliq"]),
                    doubleval($data["inffin399b_nivend"]),
                    doubleval($data["inffin399b_razcob"]),
                    doubleval($data["inffin399b_renpat"]),
                    doubleval($data["inffin399b_renact"]),
                    "'" . $data["inffin399b_gruponiif"] . "'"
                );
                if (contarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', "proponente='" . ltrim($data["proponente"], "0") . "' and libro='" . $data["libro"] . "' and registro='" . $data["registro"] . "' and fechacorte='" . $data["inffin399b_fechacorte"] . "'") == 0) {
                    $resx = insertarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', $arrCampos, $arrValores);
                } else {
                    $resx = regrabarRegistrosMysqliApi($dbx, 'mreg_est_proponentes_financiera', $arrCampos, $arrValores, "proponente='" . ltrim($data["proponente"], "0") . "' and libro='" . $data["libro"] . "' and registro='" . $data["registro"] . "' and fechacorte='" . $data["inffin399b_fechacorte"] . "'");
                }
                if ($resx === false) {
                    \logApi::general2($nameLog, '', '4.- Error actualizando mreg_est_proponentes_financiera 399b: ' . $_SESSION["generales"]["mensajeerror"]);
                } else {
                    \logApi::general2($nameLog, '', 'Actualizo mreg_est_proponentes_financiera 399b');
                }
            }
        }


        if ($estado != '') {
            if (ltrim($data["proponente"], "0") != '') {
                unset($_SESSION["expedienteactual"]);
                $arrCampos = array(
                    'ctrestproponente'
                );
                $arrValores = array(
                    "'" . $estado . "'"
                );
                regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "proponentre='" . ltrim($data["proponente"], "0") . "'");
            }
        }

        // 2017-07-21: JINT: Se incluye para que cambie el estado.
        if ($acto == '01' || $acto == '02' || $acto == '03' || $acto == '16') {
            \logApi::general2($nameLog, '', 'En caso de inscripcion, actualizacion o renovacion');

            // Actualiza mreg_est_proponentes
            $arrCampos = array('idestadoproponente', 'fechacancelacion', 'fecactualizacion', 'fecsincronizacion', 'horsincronizacion', 'compite360');
            $arrValores = array("'00'", "''", "'" . date("Ymd") . "'", "''", "''", "'NO'");
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_proponentes', $arrCampos, $arrValores, "proponente='" . $data["proponente"] . "'");
            if ($res === false) {
                if ($cerrarmysql == 'si') {
                    $dbx->close();
                }
                \logApi::general2($nameLog, '', '4.- Error : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }

            // Actualiza mreg_est_inscritos
            unset($_SESSION["expedienteactual"]);
            $arrCampos = array('ctrestproponente', 'fecactualizacion', 'compite360', 'rues', 'ivc');
            $arrValores = array("'00'", "'" . date("Ymd") . "'", "'NO'", "'NO'", "'NO'");
            $res = regrabarRegistrosMysqliApi($dbx, 'mreg_est_inscritos', $arrCampos, $arrValores, "proponente='" . $data["proponente"] . "'");
            if ($res === false) {
                if ($cerrarmysql == 'si') {
                    $dbx->close();
                }
                \logApi::general2($nameLog, '', '5.- Error : ' . $_SESSION["generales"]["mensajeerror"]);
                return false;
            }
        }

        if ($cerrarmysql == 'si') {
            $dbx->close();
        }
        return true;
    }

}

?>
