<?php

class funcionesRegistrales_serializarExpedienteProponenteEnviarSirep {

    public static function serializarExpedienteProponenteEnviarSirep($dbx, $tiposerializacion = '', $gruposmodificados = array()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        $xml = '<expedientes>';
        $xml .= '<expediente>';

        //
        if (!isset($_SESSION["formulario"]["version"]) || trim($_SESSION["formulario"]["version"]) == '') {
            $_SESSION["formulario"]["version"] = '1510';
        }

        if (!isset($_SESSION["formulario"]["tipotramite"])) {
            $_SESSION["formulario"]["tipotramite"] = '';
        }
        if (!isset($_SESSION["formulario"]["numerorecuperacion"])) {
            $_SESSION["formulario"]["numerorecuperacion"] = '';
        }
        if (!isset($_SESSION["formulario"]["liquidacion"])) {
            $_SESSION["formulario"]["liquidacion"] = '';
        }
        if (!isset($_SESSION["formulario"]["numeroradicacion"])) {
            $_SESSION["formulario"]["numeroradicacion"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["tipotramite"]) || trim($_SESSION["formulario"]["tipotramite"]) == '') {
            $_SESSION["generales"]["mensajeerror"] = 'No se indic&oacute; el tipo de tr&aacute;mite';
            return false;
        }

        if ($_SESSION["formulario"]["tipotramite"] != 'actualizacionespecial') {
            if (!isset($_SESSION["formulario"]["numeroliquidacion"]) || ltrim($_SESSION["formulario"]["numeroliquidacion"], "0") == '') {
                $_SESSION["generales"]["mensajeerror"] = 'No se indic&oacute; el n&uacute;mero de liquidaci&oacute;n.';
                return false;
            }
        }

        if (!isset($_SESSION["formulario"]["datos"]) || empty($_SESSION["formulario"]["datos"])) {
            $_SESSION["generales"]["mensajeerror"] = 'No se indicaron datos el formulario.';
            return false;
        }

        //
        $tt = trim($_SESSION["formulario"]["tipotramite"]);
        $numliq = $_SESSION["formulario"]["numeroliquidacion"];
        $numrad = $_SESSION["formulario"]["numeroradicacion"];
        if (trim($tt) == '') {
            $tt = 'inscripcionproponente';
        }

        //
        $aV = \funcionesRegistrales::verificarDatosModificadosApi($dbx, $numliq, $numrad, $tt, $_SESSION["formulario"]["datos"], $_SESSION["generales"]["codigoempresa"], $_SESSION["formulario"]["version"]);

        //
        if ($aV === false) {
            $controles = array();
            $arrCnt = retornarRegistrosMysqliApi($dbx, 'mreg_radicacionesdatoscontrol', "idradicacion='" . ltrim($numrad, "0") . "'", "grupodatos");
            foreach ($arrCnt as $cnt) {
                $controles[$cnt["grupodatos"]] = $cnt["controlgrabacion"];
            }
            unset($arrCnt);
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '01') {
                $aV = array(
                    'cambidom' => 'N',
                    'datosbasicos' => 'S',
                    'perjur' => 'N',
                    'ubicacion' => 'S',
                    'repleg' => 'N',
                    'facultades' => 'N',
                    'clasi1510' => 'S',
                    'inffin1510' => 'S',
                    'inffin399a' => 'S',
                    'inffin399b' => 'S',
                    'sitcontrol' => 'N',
                    'exp1510' => 'S'
                );
            } else {
                $aV = array(
                    'cambidom' => 'N',
                    'datosbasicos' => 'S',
                    'perjur' => 'S',
                    'ubicacion' => 'S',
                    'repleg' => 'S',
                    'facultades' => 'S',
                    'clasi1510' => 'S',
                    'inffin1510' => 'S',
                    'inffin399a' => 'S',
                    'inffin399b' => 'S',
                    'sitcontrol' => 'N',
                    'exp1510' => 'S'
                );
            }
            foreach ($controles as $ctr => $valor) {
                if (substr($ctr, 0, 23) == 'sop-05-exp1510-certcon-') {
                    $aV['exp1510-' . substr($ctr, 23)] = 'S';
                }
            }
        }


        // ************************************************************************************************************* //
        // ******************************* SERIALIZACION 1510 ********************************************************** //
        // ************************************************************************************************************* //
        if ($_SESSION["formulario"]["version"] == '1510') {

            $xml .= '<version>' . trim($_SESSION["formulario"]["version"]) . '</version>';
            $xml .= '<tipotramite>' . trim($_SESSION["formulario"]["tipotramite"]) . '</tipotramite>';
            if (isset($_SESSION["formulario"]["datos"]["numeroinscripcionlibros"])) {
                $xml .= '<numeroinscripcionlibros>' . trim($_SESSION["formulario"]["datos"]["numeroinscripcionlibros"]) . '</numeroinscripcionlibros>';
            } else {
                $xml .= '<numeroinscripcionlibros></numeroinscripcionlibros>';
            }
            $xml .= '<numerorecuperacion>' . trim($_SESSION["formulario"]["numerorecuperacion"]) . '</numerorecuperacion>';
            $xml .= '<numeroliquidacion>' . trim($_SESSION["formulario"]["liquidacion"]) . '</numeroliquidacion>';
            $xml .= '<proponente>' . ltrim($_SESSION["formulario"]["datos"]["proponente"], '0') . '</proponente>';
            $xml .= '<matricula>' . ltrim($_SESSION["formulario"]["datos"]["matricula"], '0') . '</matricula>';
            $xml .= '<nombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["nombre"])) . ']]></nombre>';
            $xml .= '<ape1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["ape1"])) . ']]></ape1>';
            $xml .= '<ape2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["ape2"])) . ']]></ape2>';
            $xml .= '<nom1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["nom1"])) . ']]></nom1>';
            $xml .= '<nom2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["nom2"])) . ']]></nom2>';
            $xml .= '<sigla><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["sigla"])) . ']]></sigla>';
            $xml .= '<idtipoidentificacion>' . $_SESSION["formulario"]["datos"]["idtipoidentificacion"] . '</idtipoidentificacion>';
            $xml .= '<identificacion>' . sprintf("%011s", $_SESSION["formulario"]["datos"]["identificacion"]) . '</identificacion>';
            $xml .= '<idpaisidentificacion>' . $_SESSION["formulario"]["datos"]["idpaisidentificacion"] . '</idpaisidentificacion>';
            $xml .= '<nit>' . sprintf("%015s", $_SESSION["formulario"]["datos"]["nit"]) . '</nit>';
            $xml .= '<nacionalidad><![CDATA[' . trim($_SESSION["formulario"]["datos"]["nacionalidad"]) . ']]></nacionalidad>';
            $xml .= '<organizacion>' . $_SESSION["formulario"]["datos"]["organizacion"] . '</organizacion>';
            $xml .= '<tamanoempresa>' . $_SESSION["formulario"]["datos"]["tamanoempresa"] . '</tamanoempresa>';
            $xml .= '<emprendedor28>' . $_SESSION["formulario"]["datos"]["emprendedor28"] . '</emprendedor28>';
            $xml .= '<pemprendedor28>' . $_SESSION["formulario"]["datos"]["pemprendedor28"] . '</pemprendedor28>';
            $xml .= '<vigcontrol>' . $_SESSION["formulario"]["datos"]["vigcontrol"] . '</vigcontrol>';
            $xml .= '<idestadoproponente>' . $_SESSION["formulario"]["datos"]["idestadoproponente"] . '</idestadoproponente>';
            $xml .= '<fechaultimainscripcion>' . $_SESSION["formulario"]["datos"]["fechaultimainscripcion"] . '</fechaultimainscripcion>';
            $xml .= '<fechaultimarenovacion>' . $_SESSION["formulario"]["datos"]["fechaultimarenovacion"] . '</fechaultimarenovacion>';
            $xml .= '<fechaultimaactualizacion>' . $_SESSION["formulario"]["datos"]["fechaultimaactualizacion"] . '</fechaultimaactualizacion>';
            $xml .= '<fechacancelacion>' . $_SESSION["formulario"]["datos"]["fechacancelacion"] . '</fechacancelacion>';

            // Cambio de domicilio
            if ($tt == 'cambiodomicilioproponente' || $tt == 'actualizacionespecial') {
                $xml .= '<cambidom_idmunicipioorigen>' . $_SESSION["formulario"]["datos"]["cambidom_idmunicipioorigen"] . '</cambidom_idmunicipioorigen>';
                $xml .= '<cambidom_idmunicipiodestino>' . $_SESSION["formulario"]["datos"]["cambidom_idmunicipiodestino"] . '</cambidom_idmunicipiodestino>';
                $xml .= '<cambidom_fechaultimainscripcion>' . $_SESSION["formulario"]["datos"]["cambidom_fechaultimainscripcion"] . '</cambidom_fechaultimainscripcion>';
                $xml .= '<cambidom_fechaultimarenovacion>' . $_SESSION["formulario"]["datos"]["cambidom_fechaultimarenovacion"] . '</cambidom_fechaultimarenovacion>';
            }

            // 	Personeriaa jur&iacute;dica
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '99') {
                if ((isset($aV["perjur"]) && $aV["perjur"] == 'S') ||
                        $tt == 'cambiodomicilioproponente' ||
                        $tt == 'actualizacionespecial' ||
                        $tt == 'inscripcionproponente'
                ) {
                    $xml .= '<idtipodocperjur>' . $_SESSION["formulario"]["datos"]["idtipodocperjur"] . '</idtipodocperjur>'; // A02
                    $xml .= '<numdocperjur>' . $_SESSION["formulario"]["datos"]["numdocperjur"] . '</numdocperjur>'; // A20
                    $xml .= '<fecdocperjur>' . $_SESSION["formulario"]["datos"]["fecdocperjur"] . '</fecdocperjur>'; // (D)
                    $xml .= '<origendocperjur>' . $_SESSION["formulario"]["datos"]["origendocperjur"] . '</origendocperjur>'; // A128
                    $xml .= '<fechaconstitucion>' . $_SESSION["formulario"]["datos"]["fechaconstitucion"] . '</fechaconstitucion>'; // (D)
                    $xml .= '<fechavencimiento>' . $_SESSION["formulario"]["datos"]["fechavencimiento"] . '</fechavencimiento>'; // (D)
                }
            }


            // Ubicacion
            if ((isset($aV["ubicacion"]) && $aV["ubicacion"] == 'S') ||
                    $tt == 'cambiodomicilioproponente' ||
                    $tt == 'actualizacionespecial' ||
                    $tt == 'inscripcionproponente'
            ) {
                $xml .= '<dircom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($_SESSION["formulario"]["datos"]["dircom"]) . ']]></dircom>'; // A128
                $xml .= '<dircom_tipovia>' . $_SESSION["formulario"]["datos"]["dircom_tipovia"] . '</dircom_tipovia>'; //A5
                $xml .= '<dircom_numvia>' . $_SESSION["formulario"]["datos"]["dircom_numvia"] . '</dircom_numvia>'; //A3
                $xml .= '<dircom_apevia>' . $_SESSION["formulario"]["datos"]["dircom_apevia"] . '</dircom_apevia>'; //A5
                $xml .= '<dircom_orivia>' . $_SESSION["formulario"]["datos"]["dircom_orivia"] . '</dircom_orivia>'; //A5
                $xml .= '<dircom_numcruce>' . $_SESSION["formulario"]["datos"]["dircom_numcruce"] . '</dircom_numcruce>'; //A3
                $xml .= '<dircom_apecruce>' . $_SESSION["formulario"]["datos"]["dircom_apecruce"] . '</dircom_apecruce>'; //A5
                $xml .= '<dircom_oricruce>' . $_SESSION["formulario"]["datos"]["dircom_oricruce"] . '</dircom_oricruce>'; //A5
                $xml .= '<dircom_numplaca>' . $_SESSION["formulario"]["datos"]["dircom_numplaca"] . '</dircom_numplaca>'; //A3
                $xml .= '<dircom_complemento><![CDATA[' . $_SESSION["formulario"]["datos"]["dircom_complemento"] . ']]></dircom_complemento>'; //A20
                $xml .= '<muncom>' . $_SESSION["formulario"]["datos"]["muncom"] . '</muncom>';
                $xml .= '<telcom1>' . $_SESSION["formulario"]["datos"]["telcom1"] . '</telcom1>';
                $xml .= '<telcom2>' . $_SESSION["formulario"]["datos"]["telcom2"] . '</telcom2>';
                $xml .= '<faxcom>' . $_SESSION["formulario"]["datos"]["faxcom"] . '</faxcom>';
                $xml .= '<celcom>' . $_SESSION["formulario"]["datos"]["celcom"] . '</celcom>';
                $xml .= '<emailcom><![CDATA[' . $_SESSION["formulario"]["datos"]["emailcom"] . ']]></emailcom>';
                $xml .= '<dirnot><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($_SESSION["formulario"]["datos"]["dirnot"]) . ']]></dirnot>';
                $xml .= '<dirnot_tipovia>' . $_SESSION["formulario"]["datos"]["dirnot_tipovia"] . '</dirnot_tipovia>';
                $xml .= '<dirnot_numvia>' . $_SESSION["formulario"]["datos"]["dirnot_numvia"] . '</dirnot_numvia>';
                $xml .= '<dirnot_apevia>' . $_SESSION["formulario"]["datos"]["dirnot_apevia"] . '</dirnot_apevia>';
                $xml .= '<dirnot_orivia>' . $_SESSION["formulario"]["datos"]["dirnot_orivia"] . '</dirnot_orivia>';
                $xml .= '<dirnot_numcruce>' . $_SESSION["formulario"]["datos"]["dirnot_numcruce"] . '</dirnot_numcruce>';
                $xml .= '<dirnot_apecruce>' . $_SESSION["formulario"]["datos"]["dirnot_apecruce"] . '</dirnot_apecruce>';
                $xml .= '<dirnot_oricruce>' . $_SESSION["formulario"]["datos"]["dirnot_oricruce"] . '</dirnot_oricruce>';
                $xml .= '<dirnot_numplaca>' . $_SESSION["formulario"]["datos"]["dirnot_numplaca"] . '</dirnot_numplaca>';
                $xml .= '<dirnot_complemento><![CDATA[' . $_SESSION["formulario"]["datos"]["dirnot_complemento"] . ']]></dirnot_complemento>';
                $xml .= '<munnot>' . $_SESSION["formulario"]["datos"]["munnot"] . '</munnot>';
                $xml .= '<telnot>' . $_SESSION["formulario"]["datos"]["telnot"] . '</telnot>';
                $xml .= '<telnot2>' . $_SESSION["formulario"]["datos"]["telnot2"] . '</telnot2>';
                $xml .= '<celnot>' . $_SESSION["formulario"]["datos"]["celnot"] . '</celnot>';
                $xml .= '<faxnot>' . $_SESSION["formulario"]["datos"]["faxnot"] . '</faxnot>';
                $xml .= '<emailnot><![CDATA[' . $_SESSION["formulario"]["datos"]["emailnot"] . ']]></emailnot>';
                $xml .= '<enviarint>' . $_SESSION["formulario"]["datos"]["enviarint"] . '</enviarint>';
                $xml .= '<enviarnot>' . $_SESSION["formulario"]["datos"]["enviarnot"] . '</enviarnot>';
            }

            // Representantes legales
            if ($_SESSION["formulario"]["datos"]["organizacion"] == '99') {
                if ((isset($aV["repleg"]) && $aV["repleg"] == 'S') ||
                        $tt == 'cambiodomicilioproponente' ||
                        $tt == 'actualizacionespecial' ||
                        $tt == 'inscripcionproponente'
                ) {
                    $xml .= '<representanteslegales>';
                    if (!empty($_SESSION["formulario"]["datos"]["representanteslegales"])) {
                        foreach ($_SESSION["formulario"]["datos"]["representanteslegales"] as $rep) {
                            $xml .= '<representantelegal>';
                            $xml .= '<idtipoidentificacionrepleg>' . $rep["idtipoidentificacionrepleg"] . "</idtipoidentificacionrepleg>";
                            $xml .= '<identificacionrepleg>' . sprintf("%011s", $rep["identificacionrepleg"]) . "</identificacionrepleg>";
                            $xml .= '<nombrerepleg><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["nombrerepleg"]) . "]]></nombrerepleg>";
                            $xml .= '<cargorepleg><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["cargorepleg"]) . "]]></cargorepleg>";
                            $xml .= '</representantelegal>';
                        }
                    }
                    $xml .= '</representanteslegales>';
                }

                // Facultades
                if ((isset($aV["facultades"])) && ($aV["facultades"] == 'S') ||
                        $tt == 'cambiodomicilioproponente' ||
                        $tt == 'actualizacionespecial' ||
                        $tt == 'inscripcionproponente'
                ) {
                    $_SESSION["formulario"]["datos"]["facultades"] = str_replace("<", "", $_SESSION["formulario"]["datos"]["facultades"]);
                    $_SESSION["formulario"]["datos"]["facultades"] = str_replace(">", "", $_SESSION["formulario"]["datos"]["facultades"]);
                    $xml .= '<facultades><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["facultades"])) . ']]></facultades>';
                }
            }

            // situaciones de control
            if ($_SESSION["formulario"]["datos"]["organizacion"] != '01') {
                if ((isset($aV["sitcontrol"]) && $aV["sitcontrol"] == 'S') ||
                        $tt == 'cambiodomicilioproponente' ||
                        $tt == 'actualizacionespecial' ||
                        $tt == 'inscripcionproponente'
                ) {
                    foreach ($_SESSION["formulario"]["datos"]["sitcontrol"] as $sc) {
                        $xml .= '<sitcontrol>';
                        $xml .= '<nombre><![CDATA[' . $sc["nombre"] . "]]></nombre>";
                        $xml .= '<identificacion>' . sprintf("%011s", $sc["identificacion"]) . "</identificacion>";
                        $xml .= '<domicilio><![CDATA[' . $sc["domicilio"] . "]]></domicilio>";
                        $xml .= '<tipo><![CDATA[' . $sc["tipo"] . "]]></tipo>";
                        $xml .= '</sitcontrol>';
                    }
                }
            }

            // ********************************************************************************************* //
            // Informacion financiera - Decreto 1510
            // ********************************************************************************************* //
            if ((isset($aV["inffin1510"]) && $aV["inffin1510"] == 'S') ||
                    $tt == 'cambiodomicilioproponente' ||
                    $tt == 'actualizacionespecial' ||
                    $tt == 'inscripcionproponente' ||
                    $tt == 'renovacionproponente'
            ) {
                $xml .= '<inffin1510_fechacorte>' . $_SESSION["formulario"]["datos"]["inffin1510_fechacorte"] . '</inffin1510_fechacorte>';
                $xml .= '<inffin1510_actcte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_actcte"]), 2, ".", "") . '</inffin1510_actcte>';
                $xml .= '<inffin1510_actnocte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_actnocte"]), 2, ".", "") . '</inffin1510_actnocte>';
                $xml .= '<inffin1510_fijnet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_fijnet"]), 2, ".", "") . '</inffin1510_fijnet>';
                $xml .= '<inffin1510_actotr>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_actotr"]), 2, ".", "") . '</inffin1510_actotr>';
                $xml .= '<inffin1510_actval>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_actval"]), 2, ".", "") . '</inffin1510_actval>';
                $xml .= '<inffin1510_acttot>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_acttot"]), 2, ".", "") . '</inffin1510_acttot>';
                $xml .= '<inffin1510_pascte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_pascte"]), 2, ".", "") . '</inffin1510_pascte>';
                $xml .= '<inffin1510_paslar>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_paslar"]), 2, ".", "") . '</inffin1510_paslar>';
                $xml .= '<inffin1510_pastot>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_pastot"]), 2, ".", "") . '</inffin1510_pastot>';
                $xml .= '<inffin1510_patnet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_patnet"]), 2, ".", "") . '</inffin1510_patnet>';
                $xml .= '<inffin1510_paspat>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_paspat"]), 2, ".", "") . '</inffin1510_paspat>';
                $xml .= '<inffin1510_balsoc>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_balsoc"]), 2, ".", "") . '</inffin1510_balsoc>';
                $xml .= '<inffin1510_ingope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_ingope"]), 2, ".", "") . '</inffin1510_ingope>';
                $xml .= '<inffin1510_ingnoope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_ingnoope"]), 2, ".", "") . '</inffin1510_ingnoope>';
                $xml .= '<inffin1510_gasope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_gasope"]), 2, ".", "") . '</inffin1510_gasope>';
                $xml .= '<inffin1510_gasnoope>' . number_format(exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_gasnoope"]), 2, ".", "") . '</inffin1510_gasnoope>';
                $xml .= '<inffin1510_cosven>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_cosven"]), 2, ".", "") . '</inffin1510_cosven>';
                $xml .= '<inffin1510_utiope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_utiope"]), 2, ".", "") . '</inffin1510_utiope>';
                $xml .= '<inffin1510_utinet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_utinet"]), 2, ".", "") . '</inffin1510_utinet>';
                $xml .= '<inffin1510_gasint>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_gasint"]), 2, ".", "") . '</inffin1510_gasint>';
                $xml .= '<inffin1510_gasimp>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_gasimp"]), 2, ".", "") . '</inffin1510_gasimp>';
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_actcte"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_pascte"]) == 0)) {
                    $xml .= '<inffin1510_indliq>999</inffin1510_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_actcte"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_pascte"]) == 0)) {
                    $xml .= '<inffin1510_indliq>998</inffin1510_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_actcte"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_pascte"]) != 0)) {
                    $xml .= '<inffin1510_indliq>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_indliq"]) . '</inffin1510_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_pastot"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_acttot"]) == 0)) {
                    $xml .= '<inffin1510_nivend>999</inffin1510_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_pastot"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_acttot"]) == 0)) {
                    $xml .= '<inffin1510_nivend>998</inffin1510_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_pastot"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_acttot"]) != 0)) {
                    $xml .= '<inffin1510_nivend>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_nivend"]) . '</inffin1510_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_gasint"]) == 0)) {
                    $xml .= '<inffin1510_razcob>999</inffin1510_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_gasint"]) == 0)) {
                    $xml .= '<inffin1510_razcob>998</inffin1510_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_gasint"]) != 0)) {
                    $xml .= '<inffin1510_razcob>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_razcob"]) . '</inffin1510_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_patnet"]) == 0)) {
                    $xml .= '<inffin1510_renpat>999</inffin1510_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_patnet"]) == 0)) {
                    $xml .= '<inffin1510_renpat>998</inffin1510_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_patnet"]) != 0)) {
                    $xml .= '<inffin1510_renpat>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_renpat"]) . '</inffin1510_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_acttot"]) == 0)) {
                    $xml .= '<inffin1510_renact>999</inffin1510_renact>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_acttot"]) == 0)) {
                    $xml .= '<inffin1510_renact>998</inffin1510_renact>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin1510_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin1510_patnet"]) != 0)) {
                    $xml .= '<inffin1510_renact>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin1510_renact"]) . '</inffin1510_renact>';
                }
                if (!isset($_SESSION["formulario"]["datos"]["inffin1510_gruponiif"])) {
                    $_SESSION["formulario"]["datos"]["inffin1510_gruponiif"] = '';
                }
                $xml .= '<inffin1510_gruponiif>' . $_SESSION["formulario"]["datos"]["inffin1510_gruponiif"] . '</inffin1510_gruponiif>';
            }

            // ********************************************************************************************* //
            // Informacion financiera - Decreto 399a
            // ********************************************************************************************* //
            if ((isset($aV["inffin399a"]) && $aV["inffin399a"] == 'S') ||
                    $tt == 'actualizacionproponente399' ||
                    $tt == 'inscripcionproponente' ||
                    $tt == 'renovacionproponente'
            ) {
                $xml .= '<inffin399a_fechacorte>' . $_SESSION["formulario"]["datos"]["inffin399a_fechacorte"] . '</inffin399a_fechacorte>';
                $xml .= '<inffin399a_actcte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_actcte"]), 2, ".", "") . '</inffin399a_actcte>';
                $xml .= '<inffin399a_actnocte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_actnocte"]), 2, ".", "") . '</inffin399a_actnocte>';
                $xml .= '<inffin399a_acttot>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_acttot"]), 2, ".", "") . '</inffin399a_acttot>';
                $xml .= '<inffin399a_pascte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_pascte"]), 2, ".", "") . '</inffin399a_pascte>';
                $xml .= '<inffin399a_paslar>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_paslar"]), 2, ".", "") . '</inffin399a_paslar>';
                $xml .= '<inffin399a_pastot>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_pastot"]), 2, ".", "") . '</inffin399a_pastot>';
                $xml .= '<inffin399a_patnet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_patnet"]), 2, ".", "") . '</inffin399a_patnet>';
                $xml .= '<inffin399a_paspat>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_paspat"]), 2, ".", "") . '</inffin399a_paspat>';
                $xml .= '<inffin399a_balsoc>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_balsoc"]), 2, ".", "") . '</inffin399a_balsoc>';
                $xml .= '<inffin399a_ingope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_ingope"]), 2, ".", "") . '</inffin399a_ingope>';
                $xml .= '<inffin399a_ingnoope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_ingnoope"]), 2, ".", "") . '</inffin399a_ingnoope>';
                $xml .= '<inffin399a_gasope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_gasope"]), 2, ".", "") . '</inffin399a_gasope>';
                $xml .= '<inffin399a_gasnoope>' . number_format(exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_gasnoope"]), 2, ".", "") . '</inffin399a_gasnoope>';
                $xml .= '<inffin399a_cosven>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_cosven"]), 2, ".", "") . '</inffin399a_cosven>';
                $xml .= '<inffin399a_utiope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_utiope"]), 2, ".", "") . '</inffin399a_utiope>';
                $xml .= '<inffin399a_utinet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_utinet"]), 2, ".", "") . '</inffin399a_utinet>';
                $xml .= '<inffin399a_gasint>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_gasint"]), 2, ".", "") . '</inffin399a_gasint>';
                $xml .= '<inffin399a_gasimp>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_gasimp"]), 2, ".", "") . '</inffin399a_gasimp>';
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_actcte"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_pascte"]) == 0)) {
                    $xml .= '<inffin399a_indliq>999</inffin399a_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_actcte"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_pascte"]) == 0)) {
                    $xml .= '<inffin399a_indliq>998</inffin399a_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_actcte"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_pascte"]) != 0)) {
                    $xml .= '<inffin399a_indliq>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_indliq"]) . '</inffin399a_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_pastot"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_acttot"]) == 0)) {
                    $xml .= '<inffin399a_nivend>999</inffin399a_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_pastot"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_acttot"]) == 0)) {
                    $xml .= '<inffin399a_nivend>998</inffin399a_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_pastot"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_acttot"]) != 0)) {
                    $xml .= '<inffin399a_nivend>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_nivend"]) . '</inffin399a_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_gasint"]) == 0)) {
                    $xml .= '<inffin399a_razcob>999</inffin399a_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_gasint"]) == 0)) {
                    $xml .= '<inffin399a_razcob>998</inffin399a_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_gasint"]) != 0)) {
                    $xml .= '<inffin399a_razcob>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_razcob"]) . '</inffin399a_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_patnet"]) == 0)) {
                    $xml .= '<inffin399a_renpat>999</inffin399a_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_patnet"]) == 0)) {
                    $xml .= '<inffin399a_renpat>998</inffin399a_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_patnet"]) != 0)) {
                    $xml .= '<inffin399a_renpat>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_renpat"]) . '</inffin399a_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_acttot"]) == 0)) {
                    $xml .= '<inffin399a_renact>999</inffin399a_renact>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_acttot"]) == 0)) {
                    $xml .= '<inffin399a_renact>998</inffin399a_renact>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399a_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399a_patnet"]) != 0)) {
                    $xml .= '<inffin399a_renact>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399a_renact"]) . '</inffin399a_renact>';
                }
            }

            // ********************************************************************************************* //
            // Informacion financiera - Decreto 399b
            // ********************************************************************************************* //
            if ((isset($aV["inffin399b"]) && $aV["inffin399b"] == 'S') ||
                    $tt == 'actualizacionproponente399' ||
                    $tt == 'inscripcionproponente' ||
                    $tt == 'renovacionproponente'
            ) {
                $xml .= '<inffin399b_fechacorte>' . $_SESSION["formulario"]["datos"]["inffin399b_fechacorte"] . '</inffin399b_fechacorte>';
                $xml .= '<inffin399b_actcte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_actcte"]), 2, ".", "") . '</inffin399b_actcte>';
                $xml .= '<inffin399b_actnocte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_actnocte"]), 2, ".", "") . '</inffin399b_actnocte>';
                $xml .= '<inffin399b_acttot>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_acttot"]), 2, ".", "") . '</inffin399b_acttot>';
                $xml .= '<inffin399b_pascte>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_pascte"]), 2, ".", "") . '</inffin399b_pascte>';
                $xml .= '<inffin399b_paslar>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_paslar"]), 2, ".", "") . '</inffin399b_paslar>';
                $xml .= '<inffin399b_pastot>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_pastot"]), 2, ".", "") . '</inffin399b_pastot>';
                $xml .= '<inffin399b_patnet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_patnet"]), 2, ".", "") . '</inffin399b_patnet>';
                $xml .= '<inffin399b_paspat>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_paspat"]), 2, ".", "") . '</inffin399b_paspat>';
                $xml .= '<inffin399b_balsoc>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_balsoc"]), 2, ".", "") . '</inffin399b_balsoc>';
                $xml .= '<inffin399b_ingope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_ingope"]), 2, ".", "") . '</inffin399b_ingope>';
                $xml .= '<inffin399b_ingnoope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_ingnoope"]), 2, ".", "") . '</inffin399b_ingnoope>';
                $xml .= '<inffin399b_gasope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_gasope"]), 2, ".", "") . '</inffin399b_gasope>';
                $xml .= '<inffin399b_gasnoope>' . number_format(exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_gasnoope"]), 2, ".", "") . '</inffin399b_gasnoope>';
                $xml .= '<inffin399b_cosven>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_cosven"]), 2, ".", "") . '</inffin399b_cosven>';
                $xml .= '<inffin399b_utiope>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_utiope"]), 2, ".", "") . '</inffin399b_utiope>';
                $xml .= '<inffin399b_utinet>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_utinet"]), 2, ".", "") . '</inffin399b_utinet>';
                $xml .= '<inffin399b_gasint>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_gasint"]), 2, ".", "") . '</inffin399b_gasint>';
                $xml .= '<inffin399b_gasimp>' . number_format(\funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_gasimp"]), 2, ".", "") . '</inffin399b_gasimp>';
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_actcte"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_pascte"]) == 0)) {
                    $xml .= '<inffin399b_indliq>999</inffin399b_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_actcte"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_pascte"]) == 0)) {
                    $xml .= '<inffin399b_indliq>998</inffin399b_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_actcte"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_pascte"]) != 0)) {
                    $xml .= '<inffin399b_indliq>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_indliq"]) . '</inffin399b_indliq>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_pastot"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_acttot"]) == 0)) {
                    $xml .= '<inffin399b_nivend>999</inffin399b_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_pastot"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_acttot"]) == 0)) {
                    $xml .= '<inffin399b_nivend>998</inffin399b_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_pastot"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_acttot"]) != 0)) {
                    $xml .= '<inffin399b_nivend>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_nivend"]) . '</inffin399b_nivend>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_gasint"]) == 0)) {
                    $xml .= '<inffin399b_razcob>999</inffin399b_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_gasint"]) == 0)) {
                    $xml .= '<inffin399b_razcob>998</inffin399b_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_gasint"]) != 0)) {
                    $xml .= '<inffin399b_razcob>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_razcob"]) . '</inffin399b_razcob>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_patnet"]) == 0)) {
                    $xml .= '<inffin399b_renpat>999</inffin399b_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_patnet"]) == 0)) {
                    $xml .= '<inffin399b_renpat>998</inffin399b_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_patnet"]) != 0)) {
                    $xml .= '<inffin399b_renpat>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_renpat"]) . '</inffin399b_renpat>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_acttot"]) == 0)) {
                    $xml .= '<inffin399b_renact>999</inffin399b_renact>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) == 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_acttot"]) == 0)) {
                    $xml .= '<inffin399b_renact>998</inffin399b_renact>';
                }
                if ((doubleval($_SESSION["formulario"]["datos"]["inffin399b_utiope"]) != 0) && (doubleval($_SESSION["formulario"]["datos"]["inffin399b_patnet"]) != 0)) {
                    $xml .= '<inffin399b_renact>' . \funcionesGenerales::exp_to_dec($_SESSION["formulario"]["datos"]["inffin399b_renact"]) . '</inffin399b_renact>';
                }
            }
//
            if ((isset($aV["clasi1510"]) && $aV["clasi1510"] == 'S') ||
                    $tt == 'cambiodomicilioproponente' ||
                    $tt == 'actualizacionespecial' ||
                    $tt == 'inscripcionproponente'
            ) {
                asort($_SESSION["formulario"]["datos"]["clasi1510"]);
                foreach ($_SESSION["formulario"]["datos"]["clasi1510"] as $cx) {
                    $xml .= '<clasi1510>' . trim($cx) . '</clasi1510>';
                }
            }


// Experiencia acreditada - Decreto 1510
            if ((isset($aV["exp1510"]) && $aV["exp1510"] == 'S') ||
                    $tt == 'cambiodomicilioproponente' ||
                    $tt == 'actualizacionespecial' ||
                    $tt == 'inscripcionproponente' ||
                    $tt == 'actualizacionespecial'
            ) {

                foreach ($_SESSION["formulario"]["datos"]["exp1510"] as $cnt) {

                    $incluir = 'no';

//
                    if ($tt == 'cambiodomicilioproponente' ||
                            $tt == 'inscripcionproponente' ||
                            $tt == 'actualizacionespecial'
                    ) {
                        $incluir = 'si';
                    }

//
                    if ($tt == 'renovacionproponente' || $tt == 'actualizacionproponente') {
                        $ind1 = '';
                        if (strlen(ltrim(trim($cnt["secuencia"]), "0")) <= 3) {
                            $ind1 = sprintf("%03s", $cnt["secuencia"]);
                        } else {
                            $ind1 = $cnt["secuencia"];
                        }
                        $ind = 'exp1510-' . $ind1;
                        if (isset($aV[$ind]) && $aV[$ind] == 'S') {
                            $incluir = 'si';
                        }
                    }

                    if ($incluir == 'si') {
                        $xml .= '<exp1510>';
                        if (strlen(ltrim(trim($cnt["secuencia"]), "0")) <= 3) {
                            $xml .= '<secuencia>' . sprintf("%03s", $cnt["secuencia"]) . '</secuencia>';
                        } else {
                            $xml .= '<secuencia>' . $cnt["secuencia"] . '</secuencia>';
                        }
                        $xml .= '<clavecontrato></clavecontrato>';
                        $xml .= '<celebradopor>' . $cnt["celebradopor"] . '</celebradopor>';
                        $xml .= '<nombrecontratista><![CDATA[' . $cnt["nombrecontratista"] . ']]></nombrecontratista>';
                        $xml .= '<nombrecontratante><![CDATA[' . $cnt["nombrecontratante"] . ']]></nombrecontratante>';
                        if (isset($cnt["fecejecucion"])) {
                            $xml .= '<fecejecucion>' . $cnt["fecejecucion"] . '</fecejecucion>';
                        } else {
                            $xml .= '<fecejecucion></fecejecucion>';
                        }
                        if (isset($cnt["valorpesos"])) {
                            $xml .= '<valorpesos>' . $cnt["valorpesos"] . '</valorpesos>';
                        } else {
                            $xml .= '<valorpesos></valorpesos>';
                        }
                        $xml .= '<valor>' . $cnt["valor"] . '</valor>';
                        $xml .= '<porcentaje>' . $cnt["porcentaje"] . '</porcentaje>';
                        foreach ($cnt["clasif"] as $cf) {
                            if (!empty($cf)) {
                                $xml .= '<clasif>' . trim($cf) . '</clasif>';
                            }
                        }
                        $xml .= '</exp1510>';
                    }
                }
            }
        }

        $xml .= '</expediente>';

// Grupos modificados para el nuevo registro 1510.
        if ($_SESSION["formulario"]["version"] == '1510') {
            if (($tt == 'actualizacionproponente') || ($tt == 'renovacionproponente')) {
                $xml .= '<gruposmodificados>';
                if ((isset($aV["cambidom"])) && ($aV["cambidom"] == 'S')) {
                    $xml .= '<grupo>cambidom</grupo>';
                }
                if ((isset($aV["datosbasicos"])) && ($aV["datosbasicos"] == 'S')) {
                    $xml .= '<grupo>datosbasicos</grupo>';
                }
                if ((isset($aV["ubicacion"])) && ($aV["ubicacion"] == 'S')) {
                    $xml .= '<grupo>ubicacion</grupo>';
                }
                if ($_SESSION["formulario"]["datos"]["organizacion"] == '99') {
                    if ((isset($aV["perjur"])) && ($aV["perjur"] == 'S')) {
                        $xml .= '<grupo>perjur</grupo>';
                    }
                    if ((isset($aV["representacion"])) && ($aV["representacion"] == 'S')) {
                        $xml .= '<grupo>repleg</grupo>';
                    }
                    if ((isset($aV["facultades"])) && ($aV["facultades"] == 'S')) {
                        $xml .= '<grupo>facultades</grupo>';
                    }
                }
                if ((isset($aV["inffin1510"])) && ($aV["inffin1510"] == 'S')) {
                    $xml .= '<grupo>inffin1510</grupo>';
                }
                if ((isset($aV["sitcontrol"])) && ($aV["sitcontrol"] == 'S')) {
                    $xml .= '<grupo>sitcontrol</grupo>';
                }
                if ((isset($aV["clasi1510"])) && ($aV["clasi1510"] == 'S')) {
                    $xml .= '<grupo>clasi1510</grupo>';
                }
                if ((isset($aV["exp1510"])) && ($aV["exp1510"] == 'S')) {
                    $xml .= '<grupo>exp1510</grupo>' . chr(13);
                    foreach ($aV as $key => $v) {
                        if (substr($key, 0, 8) == 'exp1510-') {
                            if ($v == 'S' || $v == 'E') {
                                $xml .= '<grupo>' . $key . '-' . $v . '</grupo>';
                            }
                        }
                    }
                }
                $xml .= '</gruposmodificados>';
            }
        }

        $xml .= '</expedientes>';
        return $xml;
    }

 
}

?>
