<?php
class funcionesGenerales_desserializarExpedienteProponente {

    public static function desserializarExpedienteProponente($mysqli, $xml, $controlprimeravez = 'no', $proceso = 'llamado directo a desserializarExpedienteProponente', $tipotramite = '') {

        $retorno = array();

        // Datos del tramite
        $retorno["tipotramite"] = '';
        $retorno["numerorecuperacion"] = '';
        $retorno["numeroliquidacion"] = '';

        // Datos de Identificacion del expediente
        $retorno["proponente"] = '';
        $retorno["matricula"] = '';
        $retorno["fecmatricula"] = '';
        $retorno["nombre"] = '';
        $retorno["ape1"] = '';
        $retorno["ape2"] = '';
        $retorno["nom1"] = '';
        $retorno["nom2"] = '';
        $retorno["sigla"] = '';
        $retorno["tipoidentificacion"] = '';
        $retorno["categoria"] = '';

        $retorno["identificacion"] = '';
        $retorno["idpaisidentificacion"] = '';
        $retorno["nit"] = '';
        $retorno["nacionalidad"] = '';
        $retorno["organizacion"] = '';
        $retorno["tamanoempresa"] = '';
        $retorno["emprendedor28"] = '';
        $retorno["pemprendedor28"] = 0;
        $retorno["vigcontrol"] = '';

        // Datos de cambio de domicilio
        $retorno["cambidom_idmunicipioorigen"] = '';
        $retorno["cambidom_idmunicipiodestino"] = '';
        $retorno["cambidom_fechaultimainscripcion"] = '';
        $retorno["cambidom_fechaultimarenovacion"] = '';
        $retorno["propcamaraorigen"] = '';
        $retorno["propcamaradestino"] = '';

        // Datos de catalogacion del registro
        $retorno["idestadoproponente"] = '';
        $retorno["certificardesde"] = '';
        $retorno["fechaultimainscripcion"] = '';
        $retorno["fechaultimarenovacion"] = '';
        $retorno["fechaultimaactualizacion"] = '';
        $retorno["fechacancelacion"] = '';
        $retorno["idtipodocperjur"] = '';
        $retorno["numdocperjur"] = '';
        $retorno["fecdocperjur"] = '';
        $retorno["origendocperjur"] = '';
        $retorno["fechaconstitucion"] = '';
        $retorno["fechavencimiento"] = '';

        // informacion de ubicacion comercial en el registro mercantil
        $retorno["dircom"] = '';
        $retorno["dircom_tipovia"] = '';
        $retorno["dircom_numvia"] = '';
        $retorno["dircom_apevia"] = '';
        $retorno["dircom_orivia"] = '';
        $retorno["dircom_numcruce"] = '';
        $retorno["dircom_apecruce"] = '';
        $retorno["dircom_oricruce"] = '';
        $retorno["dircom_numplaca"] = '';
        $retorno["dircom_complemento"] = '';
        $retorno["muncom"] = '';
        $retorno["telcom1"] = '';
        $retorno["telcom2"] = '';
        $retorno["faxcom"] = '';
        $retorno["celcom"] = '';
        $retorno["telcomant1"] = '';
        $retorno["telcomant2"] = '';
        $retorno["telcomant3"] = '';
        $retorno["emailcom"] = '';
        $retorno["emailcomant"] = '';
        $retorno["enviarint"] = '';

        // informacion de ubicacion de notificacion
        $retorno["dirnot"] = '';
        $retorno["dirnot_tipovia"] = '';
        $retorno["dirnot_numvia"] = '';
        $retorno["dirnot_apevia"] = '';
        $retorno["dirnot_orivia"] = '';
        $retorno["dirnot_numcruce"] = '';
        $retorno["dirnot_apecruce"] = '';
        $retorno["dirnot_oricruce"] = '';
        $retorno["dirnot_numplaca"] = '';
        $retorno["dirnot_complemento"] = '';
        $retorno["munnot"] = '';
        $retorno["telnot"] = '';
        $retorno["telnot2"] = '';
        $retorno["faxnot"] = '';
        $retorno["celnot"] = '';
        $retorno["telnotant1"] = '';
        $retorno["telnotant2"] = '';
        $retorno["telnotant3"] = '';
        $retorno["emailnot"] = '';
        $retorno["emailnotant"] = '';
        $retorno["enviarnot"] = '';

        // representacion legal   
        $retorno["representanteslegales"] = array();
        $retorno["facultades"] = '';
        $retorno["crt0041"] = '';
        $retorno["crt1121"] = '';

        // Situaciones de control
        $retorno["sitcontrol"] = array();

        // Informacion financiera decreto 1510
        $retorno["inffin1510_fechacorte"] = '';
        $retorno["inffin1510_actcte"] = 0;
        $retorno["inffin1510_actnocte"] = 0;
        $retorno["inffin1510_fijnet"] = 0;
        $retorno["inffin1510_actotr"] = 0;
        $retorno["inffin1510_actval"] = 0;
        $retorno["inffin1510_acttot"] = 0;
        $retorno["inffin1510_pascte"] = 0;
        $retorno["inffin1510_paslar"] = 0;
        $retorno["inffin1510_pastot"] = 0;
        $retorno["inffin1510_patnet"] = 0;
        $retorno["inffin1510_paspat"] = 0;
        $retorno["inffin1510_balsoc"] = 0;
        $retorno["inffin1510_ingope"] = 0;
        $retorno["inffin1510_ingnoope"] = 0;
        $retorno["inffin1510_gasope"] = 0;
        $retorno["inffin1510_gasnoope"] = 0;
        $retorno["inffin1510_cosven"] = 0;
        $retorno["inffin1510_utinet"] = 0;
        $retorno["inffin1510_utiope"] = 0;
        $retorno["inffin1510_gasint"] = 0;
        $retorno["inffin1510_gasimp"] = 0;
        $retorno["inffin1510_indliq"] = 0;
        $retorno["inffin1510_nivend"] = 0;
        $retorno["inffin1510_razcob"] = 0;
        $retorno["inffin1510_renpat"] = 0;
        $retorno["inffin1510_renact"] = 0;
        $retorno["inffin1510_gruponiif"] = '';

        $retorno["inffin399a_fechacorte"] = '';
        $retorno["inffin399a_pregrabado"] = '';
        $retorno["inffin399a_actcte"] = 0;
        $retorno["inffin399a_actnocte"] = 0;
        $retorno["inffin399a_acttot"] = 0;
        $retorno["inffin399a_pascte"] = 0;
        $retorno["inffin399a_paslar"] = 0;
        $retorno["inffin399a_pastot"] = 0;
        $retorno["inffin399a_patnet"] = 0;
        $retorno["inffin399a_paspat"] = 0;
        $retorno["inffin399a_balsoc"] = 0;
        $retorno["inffin399a_ingope"] = 0;
        $retorno["inffin399a_ingnoope"] = 0;
        $retorno["inffin399a_gasope"] = 0;
        $retorno["inffin399a_gasnoope"] = 0;
        $retorno["inffin399a_cosven"] = 0;
        $retorno["inffin399a_utinet"] = 0;
        $retorno["inffin399a_utiope"] = 0;
        $retorno["inffin399a_gasint"] = 0;
        $retorno["inffin399a_gasimp"] = 0;
        $retorno["inffin399a_indliq"] = 0;
        $retorno["inffin399a_nivend"] = 0;
        $retorno["inffin399a_razcob"] = 0;
        $retorno["inffin399a_renpat"] = 0;
        $retorno["inffin399a_renact"] = 0;
        $retorno["inffin399a_gruponiif"] = '';

        $retorno["inffin399b_fechacorte"] = '';
        $retorno["inffin399b_pregrabado"] = '';
        $retorno["inffin399b_actcte"] = 0;
        $retorno["inffin399b_actnocte"] = 0;
        $retorno["inffin399b_acttot"] = 0;
        $retorno["inffin399b_pascte"] = 0;
        $retorno["inffin399b_paslar"] = 0;
        $retorno["inffin399b_pastot"] = 0;
        $retorno["inffin399b_patnet"] = 0;
        $retorno["inffin399b_paspat"] = 0;
        $retorno["inffin399b_balsoc"] = 0;
        $retorno["inffin399b_ingope"] = 0;
        $retorno["inffin399b_ingnoope"] = 0;
        $retorno["inffin399b_gasope"] = 0;
        $retorno["inffin399b_gasnoope"] = 0;
        $retorno["inffin399b_cosven"] = 0;
        $retorno["inffin399b_utinet"] = 0;
        $retorno["inffin399b_utiope"] = 0;
        $retorno["inffin399b_gasint"] = 0;
        $retorno["inffin399b_gasimp"] = 0;
        $retorno["inffin399b_indliq"] = 0;
        $retorno["inffin399b_nivend"] = 0;
        $retorno["inffin399b_razcob"] = 0;
        $retorno["inffin399b_renpat"] = 0;
        $retorno["inffin399b_renact"] = 0;
        $retorno["inffin399b_gruponiif"] = '';

        // Contratos ejecutados 1510
        $retorno["exp1510"] = array();

        // Clasificaciones 1510
        $retorno["clasi1510"] = array();
        $retorno["clasitem1510"] = array();

        // Bienes
        $retorno["bienes"] = array();

        // Contratos mults y sanciones de Entidades del Estado
        $retorno["contratos"] = array();
        $retorno["multas"] = array();
        $retorno["sanciones"] = array();
        $retorno["sandis"] = array();
        $retorno["inscripciones"] = array();

        // Datos del proponente que están en firme
        if ($tipotramite == 'enfirme') {
            $enfirme = 'enfirme';
            $retorno[$enfirme] = array();
            $retorno[$enfirme]["proponente"] = '';
            $retorno[$enfirme]["matricula"] = '';
            $retorno[$enfirme]["nombre"] = '';
            $retorno[$enfirme]["ape1"] = '';
            $retorno[$enfirme]["ape2"] = '';
            $retorno[$enfirme]["nom1"] = '';
            $retorno[$enfirme]["nom2"] = '';
            $retorno[$enfirme]["sigla"] = '';
            $retorno[$enfirme]["tipoidentificacion"] = '';
            $retorno[$enfirme]["categoria"] = '';

            $retorno[$enfirme]["identificacion"] = '';
            $retorno[$enfirme]["idpaisidentificacion"] = '';
            $retorno[$enfirme]["nit"] = '';
            $retorno[$enfirme]["nacionalidad"] = '';
            $retorno[$enfirme]["organizacion"] = '';
            $retorno[$enfirme]["tamanoempresa"] = '';
            $retorno[$enfirme]["emprendedor28"] = '';
            $retorno[$enfirme]["pemprendedor28"] = 0;
            $retorno[$enfirme]["vigcontrol"] = '';

            // Datos de cambio de domicilio
            $retorno[$enfirme]["cambidom_idmunicipioorigen"] = '';
            $retorno[$enfirme]["cambidom_idmunicipiodestino"] = '';
            $retorno[$enfirme]["cambidom_fechaultimainscripcion"] = '';
            $retorno[$enfirme]["cambidom_fechaultimarenovacion"] = '';
            $retorno[$enfirme]["propcamaraorigen"] = '';
            $retorno[$enfirme]["propcamaradestino"] = '';

            // Datos de catalogacion del registro
            $retorno[$enfirme]["idestadoproponente"] = '';
            $retorno[$enfirme]["fechaultimainscripcion"] = '';
            $retorno[$enfirme]["fechaultimarenovacion"] = '';
            $retorno[$enfirme]["fechaultimaactualizacion"] = '';
            $retorno[$enfirme]["fechacancelacion"] = '';
            $retorno[$enfirme]["idtipodocperjur"] = '';
            $retorno[$enfirme]["numdocperjur"] = '';
            $retorno[$enfirme]["fecdocperjur"] = '';
            $retorno[$enfirme]["origendocperjur"] = '';
            $retorno[$enfirme]["fechaconstitucion"] = '';
            $retorno[$enfirme]["fechavencimiento"] = '';

            // informacion de ubicacion comercial en el registro mercantil
            $retorno[$enfirme]["dircom"] = '';
            $retorno[$enfirme]["dircom_tipovia"] = '';
            $retorno[$enfirme]["dircom_numvia"] = '';
            $retorno[$enfirme]["dircom_apevia"] = '';
            $retorno[$enfirme]["dircom_orivia"] = '';
            $retorno[$enfirme]["dircom_numcruce"] = '';
            $retorno[$enfirme]["dircom_apecruce"] = '';
            $retorno[$enfirme]["dircom_oricruce"] = '';
            $retorno[$enfirme]["dircom_numplaca"] = '';
            $retorno[$enfirme]["dircom_complemento"] = '';
            $retorno[$enfirme]["muncom"] = '';
            $retorno[$enfirme]["telcom1"] = '';
            $retorno[$enfirme]["telcom2"] = '';
            $retorno[$enfirme]["faxcom"] = '';
            $retorno[$enfirme]["celcom"] = '';
            $retorno[$enfirme]["telcomant1"] = '';
            $retorno[$enfirme]["telcomant2"] = '';
            $retorno[$enfirme]["telcomant3"] = '';
            $retorno[$enfirme]["emailcom"] = '';
            $retorno[$enfirme]["emailcomant"] = '';
            $retorno[$enfirme]["enviarint"] = '';

            // informacion de ubicacion de notificacion
            $retorno[$enfirme]["dirnot"] = '';
            $retorno[$enfirme]["dirnot_tipovia"] = '';
            $retorno[$enfirme]["dirnot_numvia"] = '';
            $retorno[$enfirme]["dirnot_apevia"] = '';
            $retorno[$enfirme]["dirnot_orivia"] = '';
            $retorno[$enfirme]["dirnot_numcruce"] = '';
            $retorno[$enfirme]["dirnot_apecruce"] = '';
            $retorno[$enfirme]["dirnot_oricruce"] = '';
            $retorno[$enfirme]["dirnot_numplaca"] = '';
            $retorno[$enfirme]["dirnot_complemento"] = '';
            $retorno[$enfirme]["munnot"] = '';
            $retorno[$enfirme]["telnot"] = '';
            $retorno[$enfirme]["telnot2"] = '';
            $retorno[$enfirme]["faxnot"] = '';
            $retorno[$enfirme]["celnot"] = '';
            $retorno[$enfirme]["telnotant1"] = '';
            $retorno[$enfirme]["telnotant2"] = '';
            $retorno[$enfirme]["telnotant3"] = '';
            $retorno[$enfirme]["emailnot"] = '';
            $retorno[$enfirme]["emailnotant"] = '';
            $retorno[$enfirme]["enviarnot"] = '';

            // representacion legal   
            $retorno[$enfirme]["representanteslegales"] = array();
            $retorno[$enfirme]["facultades"] = '';

            // Situaciones de control
            $retorno[$enfirme]["sitcontrol"] = array();

            // Informacion financiera decreto 1510
            $retorno[$enfirme]["inffin1510_fechacorte"] = '';
            $retorno[$enfirme]["inffin1510_actcte"] = 0;
            $retorno[$enfirme]["inffin1510_actnocte"] = 0;
            $retorno[$enfirme]["inffin1510_fijnet"] = 0;
            $retorno[$enfirme]["inffin1510_actotr"] = 0;
            $retorno[$enfirme]["inffin1510_actval"] = 0;
            $retorno[$enfirme]["inffin1510_acttot"] = 0;

            $retorno[$enfirme]["inffin1510_pascte"] = 0;
            $retorno[$enfirme]["inffin1510_paslar"] = 0;
            $retorno[$enfirme]["inffin1510_pastot"] = 0;
            $retorno[$enfirme]["inffin1510_patnet"] = 0;
            $retorno[$enfirme]["inffin1510_paspat"] = 0;
            $retorno[$enfirme]["inffin1510_balsoc"] = 0;

            $retorno[$enfirme]["inffin1510_ingope"] = 0;
            $retorno[$enfirme]["inffin1510_ingnoope"] = 0;
            $retorno[$enfirme]["inffin1510_gasope"] = 0;
            $retorno[$enfirme]["inffin1510_gasnoope"] = 0;
            $retorno[$enfirme]["inffin1510_cosven"] = 0;
            $retorno[$enfirme]["inffin1510_utinet"] = 0;
            $retorno[$enfirme]["inffin1510_utiope"] = 0;
            $retorno[$enfirme]["inffin1510_gasint"] = 0;
            $retorno[$enfirme]["inffin1510_gasimp"] = 0;

            $retorno[$enfirme]["inffin1510_indliq"] = 0;
            $retorno[$enfirme]["inffin1510_nivend"] = 0;
            $retorno[$enfirme]["inffin1510_razcob"] = 0;
            $retorno[$enfirme]["inffin1510_renpat"] = 0;
            $retorno[$enfirme]["inffin1510_renact"] = 0;

            $retorno[$enfirme]["inffin1510_gruponiif"] = '';

            // Informacion financiera decreto 399a
            $retorno[$enfirme]["inffin399a_fechacorte"] = '';
            $retorno[$enfirme]["inffin399a_pregrabado"] = '';
            $retorno[$enfirme]["inffin399a_actcte"] = 0;
            $retorno[$enfirme]["inffin399a_actnocte"] = 0;
            $retorno[$enfirme]["inffin399a_acttot"] = 0;

            $retorno[$enfirme]["inffin399a_pascte"] = 0;
            $retorno[$enfirme]["inffin399a_paslar"] = 0;
            $retorno[$enfirme]["inffin399a_pastot"] = 0;
            $retorno[$enfirme]["inffin399a_patnet"] = 0;
            $retorno[$enfirme]["inffin399a_paspat"] = 0;
            $retorno[$enfirme]["inffin399a_balsoc"] = 0;

            $retorno[$enfirme]["inffin399a_ingope"] = 0;
            $retorno[$enfirme]["inffin399a_ingnoope"] = 0;
            $retorno[$enfirme]["inffin399a_gasope"] = 0;
            $retorno[$enfirme]["inffin399a_gasnoope"] = 0;
            $retorno[$enfirme]["inffin399a_cosven"] = 0;
            $retorno[$enfirme]["inffin399a_utinet"] = 0;
            $retorno[$enfirme]["inffin399a_utiope"] = 0;
            $retorno[$enfirme]["inffin399a_gasint"] = 0;
            $retorno[$enfirme]["inffin399a_gasimp"] = 0;

            $retorno[$enfirme]["inffin399a_indliq"] = 0;
            $retorno[$enfirme]["inffin399a_nivend"] = 0;
            $retorno[$enfirme]["inffin399a_razcob"] = 0;
            $retorno[$enfirme]["inffin399a_renpat"] = 0;
            $retorno[$enfirme]["inffin399a_renact"] = 0;

            $retorno[$enfirme]["inffin399a_gruponiif"] = '';

            // Informacion financiera decreto 399b
            $retorno[$enfirme]["inffin399b_fechacorte"] = '';
            $retorno[$enfirme]["inffin399b_pregrabado"] = '';
            $retorno[$enfirme]["inffin399b_actcte"] = 0;
            $retorno[$enfirme]["inffin399b_actnocte"] = 0;
            $retorno[$enfirme]["inffin399b_acttot"] = 0;

            $retorno[$enfirme]["inffin399b_pascte"] = 0;
            $retorno[$enfirme]["inffin399b_paslar"] = 0;
            $retorno[$enfirme]["inffin399b_pastot"] = 0;
            $retorno[$enfirme]["inffin399b_patnet"] = 0;
            $retorno[$enfirme]["inffin399b_paspat"] = 0;
            $retorno[$enfirme]["inffin399b_balsoc"] = 0;

            $retorno[$enfirme]["inffin399b_ingope"] = 0;
            $retorno[$enfirme]["inffin399b_ingnoope"] = 0;
            $retorno[$enfirme]["inffin399b_gasope"] = 0;
            $retorno[$enfirme]["inffin399b_gasnoope"] = 0;
            $retorno[$enfirme]["inffin399b_cosven"] = 0;
            $retorno[$enfirme]["inffin399b_utinet"] = 0;
            $retorno[$enfirme]["inffin399b_utiope"] = 0;
            $retorno[$enfirme]["inffin399b_gasint"] = 0;
            $retorno[$enfirme]["inffin399b_gasimp"] = 0;

            $retorno[$enfirme]["inffin399b_indliq"] = 0;
            $retorno[$enfirme]["inffin399b_nivend"] = 0;
            $retorno[$enfirme]["inffin399b_razcob"] = 0;
            $retorno[$enfirme]["inffin399b_renpat"] = 0;
            $retorno[$enfirme]["inffin399b_renact"] = 0;

            $retorno[$enfirme]["inffin399b_gruponiif"] = '';

            // Contratos ejecutados 1510
            $retorno[$enfirme]["exp1510"] = array();

            // Clasificaciones 1510
            $retorno[$enfirme]["clasi1510"] = array();
            $retorno[$enfirme]["clasitem1510"] = array();
        }


        //
        if (trim($xml) != '') {
            $dom = new DomDocument('1.0', 'utf-8');
            try {
                ini_set('display_errors', '1');
                $result = $dom->loadXML($xml);
                ini_set('display_errors', '1');
                if ($result === false) {
                    $detalle = isset($_SESSION["sirep"]["detalle"]) ? $_SESSION["sirep"]["detalle"] : "";
                    $_SESSION["generales"]["txtemergente"] = '(1) Error desserializando xml: ' . $result . ' (' . $detalle . ') (' . $controlprimeravez . ') (' . $proceso . ')';
                    return 0;
                }
            } catch (exception $e) {
                $_SESSION["generales"]["txtemergente"] = 'Error de excepci&oacute;n : ' . $e->getMessage();
                return 0;
            }

            $ix = 0;
            $reg1 = $dom->getElementsByTagName("expediente");

            foreach ($reg1 as $reg) {
                $ix++;

                // Datos de Identificacion del expediente
                $retorno["tipotramite"] = (isset($reg->getElementsByTagName("tipotramite")->item(0)->textContent)) ? ltrim($reg->getElementsByTagName("tipotramite")->item(0)->textContent, '0') : '';
                $retorno["numerorecuperacion"] = (isset($reg->getElementsByTagName("numerorecuperacion")->item(0)->textContent)) ? trim($reg->getElementsByTagName("numerorecuperacion")->item(0)->textContent) : '';
                $retorno["numeroliquidacion"] = (isset($reg->getElementsByTagName("numeroliquidacion")->item(0)->textContent)) ? ltrim($reg->getElementsByTagName("numeroliquidacion")->item(0)->textContent, '0') : '';

                // *********************************************************************************************************************** //
                // Datos en firme
                // *********************************************************************************************************************** //
                if ($tipotramite == 'enfirme') {
                    $enfirme = '';
                    switch ($ix) {
                        case 1 : $enfirme = 'enfirme';
                            break;
                        case 2 : $enfirme = 'nofirme01';
                            break;
                        case 3 : $enfirme = 'nofirme02';
                            break;
                        case 4 : $enfirme = 'nofirme03';
                            break;
                        case 5 : $enfirme = 'nofirme04';
                            break;
                    }
                    $retorno[$enfirme]["proponente"] = ltrim($reg->getElementsByTagName("proponente")->item(0)->textContent, '0');
                    $retorno[$enfirme]["matricula"] = ltrim($reg->getElementsByTagName("matricula")->item(0)->textContent, '0');
                    $retorno[$enfirme]["nombre"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("nombre")->item(0)->textContent));
                    $retorno[$enfirme]["ape1"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("ape1")->item(0)->textContent));
                    $retorno[$enfirme]["ape2"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("ape2")->item(0)->textContent));
                    $retorno[$enfirme]["nom1"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("nom1")->item(0)->textContent));
                    $retorno[$enfirme]["nom2"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("nom2")->item(0)->textContent));
                    $retorno[$enfirme]["sigla"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("sigla")->item(0)->textContent));
                    $retorno[$enfirme]["idtipoidentificacion"] = $reg->getElementsByTagName("idtipoidentificacion")->item(0)->textContent;
                    $retorno[$enfirme]["identificacion"] = ltrim($reg->getElementsByTagName("identificacion")->item(0)->textContent, '0');
                    $retorno[$enfirme]["idpaisidentificacion"] = $reg->getElementsByTagName("idpaisidentificacion")->item(0)->textContent;
                    $retorno[$enfirme]["nit"] = ltrim($reg->getElementsByTagName("nit")->item(0)->textContent, '0');
                    $retorno[$enfirme]["nacionalidad"] = trim($reg->getElementsByTagName("nacionalidad")->item(0)->textContent);
                    $retorno[$enfirme]["organizacion"] = $reg->getElementsByTagName("organizacion")->item(0)->textContent;

                    $retorno[$enfirme]["tamanoempresa"] = $reg->getElementsByTagName("tamanoempresa")->item(0)->textContent;
                    $retorno[$enfirme]["emprendedor28"] = $reg->getElementsByTagName("emprendedor28")->item(0)->textContent;
                    $retorno[$enfirme]["pemprendedor28"] = $reg->getElementsByTagName("pemprendedor28")->item(0)->textContent;
                    $retorno[$enfirme]["vigcontrol"] = $reg->getElementsByTagName("vigcontrol")->item(0)->textContent;

                    $retorno[$enfirme]["cambidom_fechaultimainscripcion"] = $reg->getElementsByTagName("cambidom_fechaultimainscripcion")->item(0)->textContent;
                    $retorno[$enfirme]["cambidom_fechaultimarenovacion"] = $reg->getElementsByTagName("cambidom_fechaultimarenovacion")->item(0)->textContent;
                    $retorno[$enfirme]["cambidom_idmunicipioorigen"] = $reg->getElementsByTagName("cambidom_idmunicipioorigen")->item(0)->textContent;
                    $retorno[$enfirme]["cambidom_idmunicipiodestino"] = $reg->getElementsByTagName("cambidom_idmunicipiodestino")->item(0)->textContent;
                    $retorno[$enfirme]["propcamaraorigen"] = $reg->getElementsByTagName("propcamaraorigen")->item(0)->textContent;
                    $retorno[$enfirme]["propcamaradestino"] = $reg->getElementsByTagName("propcamaradestino")->item(0)->textContent;

                    $retorno[$enfirme]["idestadoproponente"] = $reg->getElementsByTagName("idestadoproponente")->item(0)->textContent;
                    $retorno[$enfirme]["fechaultimainscripcion"] = $reg->getElementsByTagName("fechaultimainscripcion")->item(0)->textContent;
                    $retorno[$enfirme]["fechaultimarenovacion"] = $reg->getElementsByTagName("fechaultimarenovacion")->item(0)->textContent;
                    $retorno[$enfirme]["fechaultimaactualizacion"] = $reg->getElementsByTagName("fechaultimaactualizacion")->item(0)->textContent;
                    $retorno[$enfirme]["fechacancelacion"] = $reg->getElementsByTagName("fechacancelacion")->item(0)->textContent;

                    $retorno[$enfirme]["idtipodocperjur"] = $reg->getElementsByTagName("idtipodocperjur")->item(0)->textContent;
                    $retorno[$enfirme]["numdocperjur"] = ltrim($reg->getElementsByTagName("numdocperjur")->item(0)->textContent, '0');
                    $retorno[$enfirme]["fecdocperjur"] = $reg->getElementsByTagName("fecdocperjur")->item(0)->textContent;
                    $retorno[$enfirme]["origendocperjur"] = (trim($reg->getElementsByTagName("origendocperjur")->item(0)->textContent));
                    $retorno[$enfirme]["fechaconstitucion"] = $reg->getElementsByTagName("fechaconstitucion")->item(0)->textContent;
                    $retorno[$enfirme]["fechavencimiento"] = $reg->getElementsByTagName("fechavencimiento")->item(0)->textContent;
                    $retorno[$enfirme]["dircom"] = \funcionesGenerales::restaurarEspeciales(ltrim($reg->getElementsByTagName("dircom")->item(0)->textContent));
                    $retorno[$enfirme]["muncom"] = $reg->getElementsByTagName("muncom")->item(0)->textContent;
                    $retorno[$enfirme]["telcom1"] = $reg->getElementsByTagName("telcom1")->item(0)->textContent;
                    $retorno[$enfirme]["telcom2"] = $reg->getElementsByTagName("telcom2")->item(0)->textContent;
                    $retorno[$enfirme]["celcom"] = $reg->getElementsByTagName("celcom")->item(0)->textContent;
                    $retorno[$enfirme]["faxcom"] = $reg->getElementsByTagName("faxcom")->item(0)->textContent;
                    $retorno[$enfirme]["emailcom"] = $reg->getElementsByTagName("emailcom")->item(0)->textContent;
                    $retorno[$enfirme]["enviarint"] = $reg->getElementsByTagName("enviarint")->item(0)->textContent;
                    $retorno[$enfirme]["dirnot"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("dirnot")->item(0)->textContent));
                    $retorno[$enfirme]["munnot"] = $reg->getElementsByTagName("munnot")->item(0)->textContent;
                    $retorno[$enfirme]["telnot"] = $reg->getElementsByTagName("telnot")->item(0)->textContent;
                    $retorno[$enfirme]["telnot2"] = $reg->getElementsByTagName("telnot2")->item(0)->textContent;
                    $retorno[$enfirme]["celnot"] = $reg->getElementsByTagName("celnot")->item(0)->textContent;
                    $retorno[$enfirme]["faxnot"] = $reg->getElementsByTagName("faxnot")->item(0)->textContent;
                    $retorno[$enfirme]["emailnot"] = $reg->getElementsByTagName("emailnot")->item(0)->textContent;
                    $retorno[$enfirme]["enviarnot"] = $reg->getElementsByTagName("enviarnot")->item(0)->textContent;

                    // informacion de representantes legales
                    if ($retorno[$enfirme]["organizacion"] != '01') {
                        if (ltrim($retorno[$enfirme]["matricula"], '0') != '') {
                            if ($ix == 1) {
                                $i = 0;
                                $rlegs = $reg->getElementsByTagName("representantelegal");
                                if (count($rlegs) > 0) {
                                    foreach ($rlegs as $rleg) {
                                        $i++;
                                        $retorno[$enfirme]["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $rleg->getElementsByTagName("idtipoidentificacionrepleg")->item(0)->textContent;
                                        $retorno[$enfirme]["representanteslegales"][$i]["identificacionrepleg"] = ltrim($rleg->getElementsByTagName("identificacionrepleg")->item(0)->textContent, '0');
                                        if ($controlprimeravez == 'si') {
                                            $retorno[$enfirme]["representanteslegales"][$i]["nombrerepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("nombrerepleg")->item(0)->textContent));
                                            $retorno[$enfirme]["representanteslegales"][$i]["cargorepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("cargorepleg")->item(0)->textContent));
                                        } else {
                                            $retorno[$enfirme]["representanteslegales"][$i]["nombrerepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("nombrerepleg")->item(0)->textContent));
                                            $retorno[$enfirme]["representanteslegales"][$i]["cargorepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("cargorepleg")->item(0)->textContent));
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($reg->getElementsByTagName("representanteslegales")) {
                                $retorno[$enfirme]["representanteslegales"] = array();
                                $i = 0;
                                $rlegs = $reg->getElementsByTagName("representantelegal");
                                if (count($rlegs) > 0) {
                                    foreach ($rlegs as $rleg) {
                                        $i++;
                                        $retorno[$enfirme]["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $rleg->getElementsByTagName("idtipoidentificacionrepleg")->item(0)->textContent;
                                        $retorno[$enfirme]["representanteslegales"][$i]["identificacionrepleg"] = ltrim($rleg->getElementsByTagName("identificacionrepleg")->item(0)->textContent, '0');
                                        $retorno[$enfirme]["representanteslegales"][$i]["nombrerepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("nombrerepleg")->item(0)->textContent));
                                        $retorno[$enfirme]["representanteslegales"][$i]["cargorepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("cargorepleg")->item(0)->textContent));
                                    }
                                }
                            }
                        }
                    }
                    unset($rlegs);

                    // informacion de facultades y limitaciones
                    if ($retorno[$enfirme]["organizacion"] != '01') {
                        if (ltrim($retorno[$enfirme]["matricula"], '0') != '') {
                            if ($ix == 1) {
                                if (isset($reg->getElementsByTagName("facultades")->item(0)->textContent)) {
                                    $retorno[$enfirme]["facultades"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("facultades")->item(0)->textContent));
                                }
                            }
                        } else {
                            if (isset($reg->getElementsByTagName("facultades")->item(0)->textContent)) {
                                $retorno[$enfirme]["facultades"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("facultades")->item(0)->textContent));
                            }
                        }
                    }

                    $i = 0;
                    $sitcon = $reg->getElementsByTagName("sitcontrol");
                    if (count($sitcon) > 0) {
                        foreach ($sitcon as $sc) {
                            if (trim(\funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("nombre")->item(0)->textContent))) != '') {
                                $i++;
                                $retorno[$enfirme]["sitcontrol"][$i]["nombre"] = \funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("nombre")->item(0)->textContent));
                                $retorno[$enfirme]["sitcontrol"][$i]["identificacion"] = \funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("identificacion")->item(0)->textContent));
                                $retorno[$enfirme]["sitcontrol"][$i]["domicilio"] = \funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("domicilio")->item(0)->textContent));
                                $retorno[$enfirme]["sitcontrol"][$i]["tipo"] = trim($sc->getElementsByTagName("tipo")->item(0)->textContent);
                            }
                        }
                    }

                    // informacion de clasificaciones
                    $i = -1;
                    $j = 0;
                    $clas = $reg->getElementsByTagName("cla1510");
                    if (count($clas) > 0) {
                        foreach ($clas as $cla) {
                            $i++;
                            if (trim($clas->item($i)->textContent) != '') {
                                $listcla = explode(",", $clas->item($i)->textContent);
                                foreach ($listcla as $lc) {
                                    if (trim($lc) != '') {
                                        $j++;
                                        $retorno["clasi1510"][$j] = trim($lc);
                                    }
                                }
                            }
                        }
                    }
                    unset($clas);

                    $clas = $reg->getElementsByTagName("clasi1510");
                    if (count($clas) > 0) {
                        foreach ($clas as $cla) {
                            $i++;
                            if (trim($clas->item($i)->textContent) != '') {
                                $listcla = explode(",", $clas->item($i)->textContent);
                                foreach ($listcla as $lc) {
                                    if (trim($lc) != '') {
                                        $j++;
                                        $retorno["clasi1510"][$j] = trim($lc);
                                    }
                                }
                            }
                        }
                    }
                    unset($clas);

                    // Informaci&oacute;n financiera - Decreto 1510
                    if (isset($reg->getElementsByTagName("inffin1510_fechacorte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_fechacorte"] = $reg->getElementsByTagName("inffin1510_fechacorte")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_actcte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_actcte"] = trim($reg->getElementsByTagName("inffin1510_actcte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_actnocte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_actnocte"] = trim($reg->getElementsByTagName("inffin1510_actnocte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_fijnet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_fijnet"] = trim($reg->getElementsByTagName("inffin1510_fijnet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_actotr")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_actotr"] = trim($reg->getElementsByTagName("inffin1510_actotr")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_actval")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_actval"] = trim($reg->getElementsByTagName("inffin1510_actval")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_acttot")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_acttot"] = trim($reg->getElementsByTagName("inffin1510_acttot")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_pascte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_pascte"] = trim($reg->getElementsByTagName("inffin1510_pascte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_paslar")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_paslar"] = trim($reg->getElementsByTagName("inffin1510_paslar")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_pastot")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_pastot"] = trim($reg->getElementsByTagName("inffin1510_pastot")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_patnet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_patnet"] = trim($reg->getElementsByTagName("inffin1510_patnet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_paspat")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_paspat"] = trim($reg->getElementsByTagName("inffin1510_paspat")->item(0)->textContent);
                    }
                    $retorno[$enfirme]["inffin1510_paspat"] = doubleval($retorno[$enfirme]["inffin1510_pastot"]) + doubleval($retorno[$enfirme]["inffin1510_patnet"]);
                    if (isset($reg->getElementsByTagName("inffin1510_balsoc")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_balsoc"] = trim($reg->getElementsByTagName("inffin1510_balsoc")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_ingope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_ingope"] = trim($reg->getElementsByTagName("inffin1510_ingope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_ingnoope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_ingnoope"] = trim($reg->getElementsByTagName("inffin1510_ingnoope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_gasope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_gasope"] = trim($reg->getElementsByTagName("inffin1510_gasope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_gasnoope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_gasnoope"] = trim($reg->getElementsByTagName("inffin1510_gasnoope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_cosven")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_cosven"] = trim($reg->getElementsByTagName("inffin1510_cosven")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_utinet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_utinet"] = trim($reg->getElementsByTagName("inffin1510_utinet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_utiope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_utiope"] = trim($reg->getElementsByTagName("inffin1510_utiope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_gasint")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_gasint"] = trim($reg->getElementsByTagName("inffin1510_gasint")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin1510_gasimp")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_gasimp"] = trim($reg->getElementsByTagName("inffin1510_gasimp")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin1510_indliq"] = 'INDEFINIDO';
                        } else {
                            $pos = strpos($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent, 'E-');
                            if ($pos === false) {
                                $retorno[$enfirme]["inffin1510_indliq"] = trim($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent);
                            } else {
                                $valx = number_format($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent, 10);
                                $retorno[$enfirme]["inffin1510_indliq"] = substr($valx, 0, 4);
                            }
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_nivend")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin1510_nivend")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin1510_nivend"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin1510_nivend"] = trim($reg->getElementsByTagName("inffin1510_nivend")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_razcob")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin1510_razcob")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin1510_razcob"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin1510_razcob"] = trim($reg->getElementsByTagName("inffin1510_razcob")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_renpat")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin1510_renpat")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin1510_renpat"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin1510_renpat"] = trim($reg->getElementsByTagName("inffin1510_renpat")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin1510_renact")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin1510_renact")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin1510_renact"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin1510_renact"] = trim($reg->getElementsByTagName("inffin1510_renact")->item(0)->textContent);
                        }
                    }

                    //
                    $retorno[$enfirme]["inffin1510_actnocte"] = $retorno[$enfirme]["inffin1510_actnocte"] +
                            $retorno[$enfirme]["inffin1510_fijnet"] +
                            $retorno[$enfirme]["inffin1510_actotr"] +
                            $retorno[$enfirme]["inffin1510_actval"];

                    if (isset($reg->getElementsByTagName("inffin1510_gruponiif")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin1510_gruponiif"] = trim($reg->getElementsByTagName("inffin1510_gruponiif")->item(0)->textContent);
                    }

                    // Informacion financiera - Decreto 399a
                    if (isset($reg->getElementsByTagName("inffin399a_fechacorte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_fechacorte"] = $reg->getElementsByTagName("inffin399a_fechacorte")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_pregrabado")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_pregrabado"] = $reg->getElementsByTagName("inffin399a_pregrabado")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_actcte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_actcte"] = trim($reg->getElementsByTagName("inffin399a_actcte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_actnocte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_actnocte"] = trim($reg->getElementsByTagName("inffin399a_actnocte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_acttot")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_acttot"] = trim($reg->getElementsByTagName("inffin399a_acttot")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_pascte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_pascte"] = trim($reg->getElementsByTagName("inffin399a_pascte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_paslar")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_paslar"] = trim($reg->getElementsByTagName("inffin399a_paslar")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_pastot")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_pastot"] = trim($reg->getElementsByTagName("inffin399a_pastot")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_patnet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_patnet"] = trim($reg->getElementsByTagName("inffin399a_patnet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_paspat")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_paspat"] = trim($reg->getElementsByTagName("inffin399a_paspat")->item(0)->textContent);
                    }
                    $retorno[$enfirme]["inffin399a_paspat"] = doubleval($retorno[$enfirme]["inffin399a_pastot"]) + doubleval($retorno[$enfirme]["inffin399a_patnet"]);
                    if (isset($reg->getElementsByTagName("inffin399a_balsoc")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_balsoc"] = trim($reg->getElementsByTagName("inffin399a_balsoc")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_ingope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_ingope"] = trim($reg->getElementsByTagName("inffin399a_ingope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_ingnoope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_ingnoope"] = trim($reg->getElementsByTagName("inffin399a_ingnoope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_gasope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_gasope"] = trim($reg->getElementsByTagName("inffin399a_gasope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_gasnoope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_gasnoope"] = trim($reg->getElementsByTagName("inffin399a_gasnoope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_cosven")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_cosven"] = trim($reg->getElementsByTagName("inffin399a_cosven")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_utinet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_utinet"] = trim($reg->getElementsByTagName("inffin399a_utinet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_utiope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_utiope"] = trim($reg->getElementsByTagName("inffin399a_utiope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_gasint")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_gasint"] = trim($reg->getElementsByTagName("inffin399a_gasint")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399a_gasimp")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_gasimp"] = trim($reg->getElementsByTagName("inffin399a_gasimp")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399a_indliq"] = 'INDEFINIDO';
                        } else {
                            $pos = strpos($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent, 'E-');
                            if ($pos === false) {
                                $retorno[$enfirme]["inffin399a_indliq"] = trim($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent);
                            } else {
                                $valx = number_format($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent, 10);
                                $retorno[$enfirme]["inffin399a_indliq"] = substr($valx, 0, 4);
                            }
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_nivend")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399a_nivend")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399a_nivend"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399a_nivend"] = trim($reg->getElementsByTagName("inffin399a_nivend")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_razcob")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399a_razcob")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399a_razcob"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399a_razcob"] = trim($reg->getElementsByTagName("inffin399a_razcob")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_renpat")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399a_renpat")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399a_renpat"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399a_renpat"] = trim($reg->getElementsByTagName("inffin399a_renpat")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399a_renact")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399a_renact")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399a_renact"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399a_renact"] = trim($reg->getElementsByTagName("inffin399a_renact")->item(0)->textContent);
                        }
                    }

                    //
                    if (isset($reg->getElementsByTagName("inffin399a_gruponiif")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399a_gruponiif"] = trim($reg->getElementsByTagName("inffin399a_gruponiif")->item(0)->textContent);
                    }

                    // Informacion financiera - Decreto 399b
                    if (isset($reg->getElementsByTagName("inffin399b_fechacorte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_fechacorte"] = $reg->getElementsByTagName("inffin399b_fechacorte")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_pregrabado")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_pregrabado"] = $reg->getElementsByTagName("inffin399b_pregrabado")->item(0)->textContent;
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_actcte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_actcte"] = trim($reg->getElementsByTagName("inffin399b_actcte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_actnocte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_actnocte"] = trim($reg->getElementsByTagName("inffin399b_actnocte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_acttot")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_acttot"] = trim($reg->getElementsByTagName("inffin399b_acttot")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_pascte")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_pascte"] = trim($reg->getElementsByTagName("inffin399b_pascte")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_paslar")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_paslar"] = trim($reg->getElementsByTagName("inffin399b_paslar")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_pastot")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_pastot"] = trim($reg->getElementsByTagName("inffin399b_pastot")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_patnet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_patnet"] = trim($reg->getElementsByTagName("inffin399b_patnet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_paspat")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_paspat"] = trim($reg->getElementsByTagName("inffin399b_paspat")->item(0)->textContent);
                    }
                    $retorno[$enfirme]["inffin399b_paspat"] = doubleval($retorno[$enfirme]["inffin399b_pastot"]) + doubleval($retorno[$enfirme]["inffin399b_patnet"]);
                    if (isset($reg->getElementsByTagName("inffin399b_balsoc")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_balsoc"] = trim($reg->getElementsByTagName("inffin399b_balsoc")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_ingope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_ingope"] = trim($reg->getElementsByTagName("inffin399b_ingope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_ingnoope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_ingnoope"] = trim($reg->getElementsByTagName("inffin399b_ingnoope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_gasope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_gasope"] = trim($reg->getElementsByTagName("inffin399b_gasope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_gasnoope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_gasnoope"] = trim($reg->getElementsByTagName("inffin399b_gasnoope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_cosven")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_cosven"] = trim($reg->getElementsByTagName("inffin399b_cosven")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_utinet")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_utinet"] = trim($reg->getElementsByTagName("inffin399b_utinet")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_utiope")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_utiope"] = trim($reg->getElementsByTagName("inffin399b_utiope")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_gasint")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_gasint"] = trim($reg->getElementsByTagName("inffin399b_gasint")->item(0)->textContent);
                    }
                    if (isset($reg->getElementsByTagName("inffin399b_gasimp")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_gasimp"] = trim($reg->getElementsByTagName("inffin399b_gasimp")->item(0)->textContent);
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399b_indliq"] = 'INDEFINIDO';
                        } else {
                            $pos = strpos($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent, 'E-');
                            if ($pos === false) {
                                $retorno[$enfirme]["inffin399b_indliq"] = trim($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent);
                            } else {
                                $valx = number_format($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent, 10);
                                $retorno[$enfirme]["inffin399b_indliq"] = substr($valx, 0, 4);
                            }
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_nivend")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399b_nivend")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399b_nivend"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399b_nivend"] = trim($reg->getElementsByTagName("inffin399b_nivend")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_razcob")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399b_razcob")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399b_razcob"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399b_razcob"] = trim($reg->getElementsByTagName("inffin399b_razcob")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_renpat")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399b_renpat")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399b_renpat"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399b_renpat"] = trim($reg->getElementsByTagName("inffin399b_renpat")->item(0)->textContent);
                        }
                    }

                    if (isset($reg->getElementsByTagName("inffin399b_renact")->item(0)->textContent)) {
                        if ($reg->getElementsByTagName("inffin399b_renact")->item(0)->textContent == '999') {
                            $retorno[$enfirme]["inffin399b_renact"] = 'INDEFINIDO';
                        } else {
                            $retorno[$enfirme]["inffin399b_renact"] = trim($reg->getElementsByTagName("inffin399b_renact")->item(0)->textContent);
                        }
                    }

                    //
                    if (isset($reg->getElementsByTagName("inffin399ba_gruponiif")->item(0)->textContent)) {
                        $retorno[$enfirme]["inffin399b_gruponiif"] = trim($reg->getElementsByTagName("inffin399b_gruponiif")->item(0)->textContent);
                    }

                    /*        	  
                     * Experiencia decreto 1510
                     * Celebrado por: 
                     * 1.- El proponente
                     * 2.- Accionista
                     * 3.- Consorcio o union temporal
                     */
                    $cont = $reg->getElementsByTagName("exp1510");
                    if (count($cont) > 0) {
                        foreach ($cont as $cnt) {
                            if (intval($cnt->getElementsByTagName("secuencia")->item(0)->textContent) <= 3) {
                                $sec = sprintf("%03s", intval($cnt->getElementsByTagName("secuencia")->item(0)->textContent));
                            } else {
                                $sec = ltrim(trim($cnt->getElementsByTagName("secuencia")->item(0)->textContent), "0");
                            }
                            $ind = intval($sec);
                            $retorno[$enfirme]["exp1510"][$ind]["secuencia"] = $sec;
                            if (isset($cnt->getElementsByTagName("clavecontrato")->item(0)->textContent)) {
                                $retorno[$enfirme]["exp1510"][$ind]["clavecontrato"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("clavecontrato")->item(0)->textContent));
                            } else {
                                $retorno[$enfirme]["exp1510"][$ind]["clavecontrato"] = '';
                            }
                            $retorno[$enfirme]["exp1510"][$ind]["celebradopor"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("celebradopor")->item(0)->textContent));

                            // $retorno[$enfirme]["exp1510"][$ind]["nombrecontratista"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("nombrecontratista")->item(0)->textContent));
                            // $retorno[$enfirme]["exp1510"][$ind]["nombrecontratante"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("nombrecontratante")->item(0)->textContent));

                            $retorno[$enfirme]["exp1510"][$ind]["nombrecontratista"] = $cnt->getElementsByTagName("nombrecontratista")->item(0)->textContent;
                            $retorno[$enfirme]["exp1510"][$ind]["nombrecontratante"] = $cnt->getElementsByTagName("nombrecontratante")->item(0)->textContent;

                            if (isset($cnt->getElementsByTagName("fecejecucion")->item(0)->textContent)) {
                                $retorno[$enfirme]["exp1510"][$ind]["fecejecucion"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("fecejecucion")->item(0)->textContent));
                            } else {
                                $retorno[$enfirme]["exp1510"][$ind]["fecejecucion"] = '';
                            }
                            if (isset($cnt->getElementsByTagName("valorpesos")->item(0)->textContent)) {
                                $retorno[$enfirme]["exp1510"][$ind]["valorpesos"] = $cnt->getElementsByTagName("valorpesos")->item(0)->textContent;
                            } else {
                                $retorno[$enfirme]["exp1510"][$ind]["valorpesos"] = 0;
                            }
                            $xval = str_replace(",",".",$cnt->getElementsByTagName("valor")->item(0)->textContent); 
                            $retorno[$enfirme]["exp1510"][$ind]["valor"] = $xval;
                            $retorno[$enfirme]["exp1510"][$ind]["porcentaje"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("porcentaje")->item(0)->textContent));

                            $ixx = -1;
                            $iCla = 0;
                            $clas = $cnt->getElementsByTagName("clasif");
                            if (count($clas) > 0) {
                                foreach ($clas as $c) {
                                    $ixx++;
                                    if (trim($clas->item($ixx)->textContent) != '') {
                                        $listcla = explode(",", $clas->item($ixx)->textContent);
                                        $clatemp = array ();
                                        foreach ($listcla as $lc) {
                                            if (trim($lc) != '') {
                                                if (!isset($clatemp[$lc])) {
                                                    $clatemp[$lc] = $lc;
                                                }
                                            }
                                        }
                                        if (!empty($clatemp)) {
                                            foreach ($clatemp as $lc) {
                                                $iCla++;
                                                $retorno[$enfirme]["exp1510"][$ind]["clasif"][$iCla] = trim($lc);
                                            }
                                        }
                                        unset ($clatemp);
                                    }
                                }
                            }

                            if (isset($cnt->getElementsByTagName("generardeclaracion")->item(0)->textContent)) {
                                $retorno[$enfirme]["exp1510"][$ind]["generardeclaracion"] = $cnt->getElementsByTagName("generardeclaracion")->item(0)->textContent;
                            } else {
                                $retorno[$enfirme]["exp1510"][$ind]["generardeclaracion"] = '';
                            }

                            $retorno[$enfirme]["exp1510"][$ind]["soportedeclaracion"] = '';
                            $retorno[$enfirme]["exp1510"][$ind]["soportecontrato"] = '';
                        }
                    }
                    unset($cont);
                    if (!empty($retorno[$enfirme]["exp1510"])) {
                        $temx = ordenarMatriz($retorno[$enfirme]["exp1510"], "secuencia");
                        $retorno[$enfirme]["exp1510"] = array();
                        $iCon = 0;
                        foreach ($temx as $t) {
                            $iCon++;
                            $retorno[$enfirme]["exp1510"][$iCon] = $t;
                        }
                        unset($temx);
                    }
                }

                // *********************************************************************************************************************** //
                // Datos actuales
                // *********************************************************************************************************************** //
                if ($ix == 1) {
                    (!isset($reg->getElementsByTagName("proponente")->item(0)->textContent)) ? $retorno["proponente"] = '' : $retorno["proponente"] = ltrim($reg->getElementsByTagName("proponente")->item(0)->textContent, '0');
                    (!isset($reg->getElementsByTagName("matricula")->item(0)->textContent)) ? $retorno["matricula"] = '' : $retorno["matricula"] = ltrim($reg->getElementsByTagName("matricula")->item(0)->textContent, '0');
                    (!isset($reg->getElementsByTagName("nombre")->item(0)->textContent)) ? $retorno["nombre"] = '' : $retorno["nombre"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("nombre")->item(0)->textContent));
                    (!isset($reg->getElementsByTagName("ape1")->item(0)->textContent)) ? $retorno["ape1"] = '' : $retorno["ape1"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("ape1")->item(0)->textContent));
                    (!isset($reg->getElementsByTagName("ape2")->item(0)->textContent)) ? $retorno["ape2"] = '' : $retorno["ape2"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("ape2")->item(0)->textContent));
                    (!isset($reg->getElementsByTagName("nom1")->item(0)->textContent)) ? $retorno["nom1"] = '' : $retorno["nom1"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("nom1")->item(0)->textContent));
                    (!isset($reg->getElementsByTagName("nom2")->item(0)->textContent)) ? $retorno["nom2"] = '' : $retorno["nom2"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("nom2")->item(0)->textContent));
                    (!isset($reg->getElementsByTagName("sigla")->item(0)->textContent)) ? $retorno["sigla"] = '' : $retorno["sigla"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("sigla")->item(0)->textContent));
                    (!isset($reg->getElementsByTagName("idtipoidentificacion")->item(0)->textContent)) ? $retorno["idtipoidentificacion"] = '' : $retorno["idtipoidentificacion"] = $reg->getElementsByTagName("idtipoidentificacion")->item(0)->textContent;
                    (!isset($reg->getElementsByTagName("identificacion")->item(0)->textContent)) ? $retorno["identificacion"] = '' : $retorno["identificacion"] = ltrim($reg->getElementsByTagName("identificacion")->item(0)->textContent, '0');
                    (!isset($reg->getElementsByTagName("idpaisidentificacion")->item(0)->textContent)) ? $retorno["idpaisidentificacion"] = '' : $retorno["idpaisidentificacion"] = $reg->getElementsByTagName("idpaisidentificacion")->item(0)->textContent;
                    (!isset($reg->getElementsByTagName("nit")->item(0)->textContent)) ? $retorno["nit"] = '' : $retorno["nit"] = ltrim($reg->getElementsByTagName("nit")->item(0)->textContent, '0');
                    (!isset($reg->getElementsByTagName("nacionalidad")->item(0)->textContent)) ? $retorno["nacionalidad"] = '' : $retorno["nacionalidad"] = trim($reg->getElementsByTagName("nacionalidad")->item(0)->textContent);
                    (!isset($reg->getElementsByTagName("organizacion")->item(0)->textContent)) ? $retorno["organizacion"] = '' : $retorno["organizacion"] = $reg->getElementsByTagName("organizacion")->item(0)->textContent;
                }

                if (isset($reg->getElementsByTagName("tamanoempresa")->item(0)->textContent)) {
                    $retorno["tamanoempresa"] = $reg->getElementsByTagName("tamanoempresa")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("emprendedor28")->item(0)->textContent)) {
                    $retorno["emprendedor28"] = $reg->getElementsByTagName("emprendedor28")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("pemprendedor28")->item(0)->textContent)) {
                    $retorno["pemprendedor28"] = $reg->getElementsByTagName("pemprendedor28")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("vigcontrol")->item(0)->textContent)) {
                    $retorno["vigcontrol"] = $reg->getElementsByTagName("vigcontrol")->item(0)->textContent;
                }

                // Datos de cambio de domicilio
                if ($ix == 1) {
                    (isset($reg->getElementsByTagName("cambidom_fechaultimainscripcion")->item(0)->textContent)) ? $retorno["cambidom_fechaultimainscripcion"] = $reg->getElementsByTagName("cambidom_fechaultimainscripcion")->item(0)->textContent : $retorno["cambidom_fechaultimainscripcion"] = '';
                    (isset($reg->getElementsByTagName("cambidom_fechaultimarenovacion")->item(0)->textContent)) ? $retorno["cambidom_fechaultimarenovacion"] = $reg->getElementsByTagName("cambidom_fechaultimarenovacion")->item(0)->textContent : $retorno["cambidom_fechaultimarenovacion"] = '';
                    (isset($reg->getElementsByTagName("cambidom_idmunicipioorigen")->item(0)->textContent)) ? $retorno["cambidom_idmunicipioorigen"] = $reg->getElementsByTagName("cambidom_idmunicipioorigen")->item(0)->textContent : $retorno["cambidom_idmunicipioorigen"] = '';
                    (isset($reg->getElementsByTagName("cambidom_idmunicipiodestino")->item(0)->textContent)) ? $retorno["cambidom_idmunicipiodestino"] = $reg->getElementsByTagName("cambidom_idmunicipiodestino")->item(0)->textContent : $retorno["cambidom_idmunicipiodestino"] = '';
                    (isset($reg->getElementsByTagName("propcamaraorigen")->item(0)->textContent)) ? $retorno["propcamaraorigen"] = $reg->getElementsByTagName("propcamaraorigen")->item(0)->textContent : $retorno["propcamaraorigen"] = '';
                    (isset($reg->getElementsByTagName("propcamaradestino")->item(0)->textContent)) ? $retorno["propcamaradestino"] = $reg->getElementsByTagName("propcamaradestino")->item(0)->textContent : $retorno["propcamaradestino"] = '';
                }

                // Datos de catalogacion del registro
                if ($ix == 1) {
                    $retorno["idestadoproponente"] = $reg->getElementsByTagName("idestadoproponente")->item(0)->textContent;
                    (!isset($reg->getElementsByTagName("certificardesde")->item(0)->textContent)) ? $retorno["certificardesde"] = '' : $retorno["certificardesde"] = $reg->getElementsByTagName("certificardesde")->item(0)->textContent;
                    $retorno["fechaultimainscripcion"] = $reg->getElementsByTagName("fechaultimainscripcion")->item(0)->textContent;
                    $retorno["fechaultimarenovacion"] = $reg->getElementsByTagName("fechaultimarenovacion")->item(0)->textContent;
                    (isset($reg->getElementsByTagName("fechaultimaactualizacion")->item(0)->textContent)) ? $retorno["fechaultimaactualizacion"] = $reg->getElementsByTagName("fechaultimaactualizacion")->item(0)->textContent : $retorno["fechaultimaactualizacion"] = '';
                    $retorno["fechacancelacion"] = $reg->getElementsByTagName("fechacancelacion")->item(0)->textContent;
                }

                if (isset($reg->getElementsByTagName("idtipodocperjur")->item(0)->textContent)) {
                    $retorno["idtipodocperjur"] = $reg->getElementsByTagName("idtipodocperjur")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("numdocperjur")->item(0)->textContent)) {
                    $retorno["numdocperjur"] = ltrim($reg->getElementsByTagName("numdocperjur")->item(0)->textContent, '0');
                }
                if (isset($reg->getElementsByTagName("fecdocperjur")->item(0)->textContent)) {
                    $retorno["fecdocperjur"] = $reg->getElementsByTagName("fecdocperjur")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("origendocperjur")->item(0)->textContent)) {
                    $retorno["origendocperjur"] = (trim($reg->getElementsByTagName("origendocperjur")->item(0)->textContent));
                }
                if (isset($reg->getElementsByTagName("fechaconstitucion")->item(0)->textContent)) {
                    $retorno["fechaconstitucion"] = $reg->getElementsByTagName("fechaconstitucion")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("fechavencimiento")->item(0)->textContent)) {
                    $retorno["fechavencimiento"] = $reg->getElementsByTagName("fechavencimiento")->item(0)->textContent;
                }

                // informaci&oacute;n de ubicaci&oacute;n comercial en el registro mercantil
                if (isset($reg->getElementsByTagName("dircom")->item(0)->textContent)) {
                    $retorno["dircom"] = \funcionesGenerales::restaurarEspeciales(ltrim($reg->getElementsByTagName("dircom")->item(0)->textContent));
                }
                if (isset($reg->getElementsByTagName("muncom")->item(0)->textContent)) {
                    $retorno["muncom"] = $reg->getElementsByTagName("muncom")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("telcom1")->item(0)->textContent)) {
                    $retorno["telcom1"] = $reg->getElementsByTagName("telcom1")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("telcom2")->item(0)->textContent)) {
                    $retorno["telcom2"] = $reg->getElementsByTagName("telcom2")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("celcom")->item(0)->textContent)) {
                    $retorno["celcom"] = $reg->getElementsByTagName("celcom")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("faxcom")->item(0)->textContent)) {
                    $retorno["faxcom"] = $reg->getElementsByTagName("faxcom")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("emailcom")->item(0)->textContent)) {
                    $retorno["emailcom"] = $reg->getElementsByTagName("emailcom")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("enviarint")->item(0)->textContent)) {
                    $retorno["enviarint"] = $reg->getElementsByTagName("enviarint")->item(0)->textContent;
                }

                // informacion de ubicacion de notificacion
                if (isset($reg->getElementsByTagName("dirnot")->item(0)->textContent)) {
                    $retorno["dirnot"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("dirnot")->item(0)->textContent));
                }
                if (isset($reg->getElementsByTagName("munnot")->item(0)->textContent)) {
                    $retorno["munnot"] = $reg->getElementsByTagName("munnot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("telnot")->item(0)->textContent)) {
                    $retorno["telnot"] = $reg->getElementsByTagName("telnot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("telnot2")->item(0)->textContent)) {
                    $retorno["telnot2"] = $reg->getElementsByTagName("telnot2")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("celnot")->item(0)->textContent)) {
                    $retorno["celnot"] = $reg->getElementsByTagName("celnot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("faxnot")->item(0)->textContent)) {
                    $retorno["faxnot"] = $reg->getElementsByTagName("faxnot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("emailnot")->item(0)->textContent)) {
                    $retorno["emailnot"] = $reg->getElementsByTagName("emailnot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("enviarnot")->item(0)->textContent)) {
                    $retorno["enviarnot"] = $reg->getElementsByTagName("enviarnot")->item(0)->textContent;
                }

                // informaci&oacute;n de representantes legales
                if ($retorno["organizacion"] != '01') {
                    if (ltrim($retorno["matricula"], '0') != '') {
                        if ($ix == 1) {
                            $i = 0;
                            $rlegs = $reg->getElementsByTagName("representantelegal");
                            if (count($rlegs) > 0) {
                                foreach ($rlegs as $rleg) {
                                    $i++;
                                    $retorno["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $rleg->getElementsByTagName("idtipoidentificacionrepleg")->item(0)->textContent;
                                    $retorno["representanteslegales"][$i]["identificacionrepleg"] = ltrim($rleg->getElementsByTagName("identificacionrepleg")->item(0)->textContent, '0');
                                    if ($controlprimeravez == 'si') {
                                        $retorno["representanteslegales"][$i]["nombrerepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("nombrerepleg")->item(0)->textContent));
                                        $retorno["representanteslegales"][$i]["cargorepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("cargorepleg")->item(0)->textContent));
                                    } else {
                                        $retorno["representanteslegales"][$i]["nombrerepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("nombrerepleg")->item(0)->textContent));
                                        $retorno["representanteslegales"][$i]["cargorepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("cargorepleg")->item(0)->textContent));
                                    }
                                }
                            }
                        }
                    } else {
                        if ($reg->getElementsByTagName("representanteslegales")) {
                            $retorno["representanteslegales"] = array();
                            $i = 0;
                            $rlegs = $reg->getElementsByTagName("representantelegal");
                            if (count($rlegs) > 0) {
                                foreach ($rlegs as $rleg) {
                                    $i++;
                                    $retorno["representanteslegales"][$i]["idtipoidentificacionrepleg"] = $rleg->getElementsByTagName("idtipoidentificacionrepleg")->item(0)->textContent;
                                    $retorno["representanteslegales"][$i]["identificacionrepleg"] = ltrim($rleg->getElementsByTagName("identificacionrepleg")->item(0)->textContent, '0');
                                    $retorno["representanteslegales"][$i]["nombrerepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("nombrerepleg")->item(0)->textContent));
                                    $retorno["representanteslegales"][$i]["cargorepleg"] = \funcionesGenerales::restaurarEspeciales(trim($rleg->getElementsByTagName("cargorepleg")->item(0)->textContent));
                                }
                            }
                        }
                    }
                }
                unset($rlegs);

                // informacion de facultades y limitaciones
                if ($retorno["organizacion"] != '01') {
                    if (ltrim($retorno["matricula"], '0') != '') {
                        if ($ix == 1) {
                            if (isset($reg->getElementsByTagName("facultades")->item(0)->textContent)) {
                                $retorno["facultades"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("facultades")->item(0)->textContent));
                            }
                        }
                    } else {
                        if (isset($reg->getElementsByTagName("facultades")->item(0)->textContent)) {
                            $retorno["facultades"] = \funcionesGenerales::restaurarEspeciales(trim($reg->getElementsByTagName("facultades")->item(0)->textContent));
                        }
                    }
                }

                /* Situaciones de control
                 * Tipo puede ser:
                 * 0.- Matriz
                 * 1.- Subordinada
                 * 2.- Controlante
                 * 3.- Controlada
                 * 4.- Matriz y Subordinada
                 * 5.- Matriz y Controlante
                 * 6.- Matriz y Controlada
                 * 7.- Subordinada y Controlante
                 * 8.- Subordinada y Controlada
                 * 9.- Controlante y Controlada
                 */
                $i = 0;
                $sitcon = $reg->getElementsByTagName("sitcontrol");
                if (count($sitcon) > 0) {
                    foreach ($sitcon as $sc) {
                        if (trim(\funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("nombre")->item(0)->textContent))) != '') {
                            $i++;
                            $retorno["sitcontrol"][$i]["nombre"] = \funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("nombre")->item(0)->textContent));
                            $retorno["sitcontrol"][$i]["identificacion"] = \funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("identificacion")->item(0)->textContent));
                            $retorno["sitcontrol"][$i]["domicilio"] = \funcionesGenerales::restaurarEspeciales(trim($sc->getElementsByTagName("domicilio")->item(0)->textContent));
                            $retorno["sitcontrol"][$i]["tipo"] = trim($sc->getElementsByTagName("tipo")->item(0)->textContent);
                        }
                    }
                }

                // informacion de clasificaciones
                $i = -1;
                $j = 0;
                $clas = $reg->getElementsByTagName("cla1510");
                if (count($clas) > 0) {
                    foreach ($clas as $cla) {
                        $i++;
                        if (trim($clas->item($i)->textContent) != '') {
                            $listcla = explode(",", $clas->item($i)->textContent);
                            foreach ($listcla as $lc) {
                                if (trim($lc) != '') {
                                    $j++;
                                    $retorno["clasi1510"][$j] = trim($lc);
                                }
                            }
                        }
                    }
                }
                unset($clas);

                $clas = $reg->getElementsByTagName("clasi1510");
                if (count($clas) > 0) {
                    foreach ($clas as $cla) {
                        $i++;
                        if (trim($clas->item($i)->textContent) != '') {
                            $listcla = explode(",", $clas->item($i)->textContent);
                            foreach ($listcla as $lc) {
                                if (trim($lc) != '') {
                                    $j++;
                                    $retorno["clasi1510"][$j] = trim($lc);
                                }
                            }
                        }
                    }
                }
                unset($clas);

                // **************************************************************************************************************** //
                // Informacion financiera - Decreto 1510
                // **************************************************************************************************************** //
                if (isset($reg->getElementsByTagName("inffin1510_fechacorte")->item(0)->textContent)) {
                    $retorno["inffin1510_fechacorte"] = $reg->getElementsByTagName("inffin1510_fechacorte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_actcte")->item(0)->textContent)) {
                    $retorno["inffin1510_actcte"] = $reg->getElementsByTagName("inffin1510_actcte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_actnocte")->item(0)->textContent)) {
                    $retorno["inffin1510_actnocte"] = $reg->getElementsByTagName("inffin1510_actnocte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_fijnet")->item(0)->textContent)) {
                    $retorno["inffin1510_fijnet"] = $reg->getElementsByTagName("inffin1510_fijnet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_actotr")->item(0)->textContent)) {
                    $retorno["inffin1510_actotr"] = $reg->getElementsByTagName("inffin1510_actotr")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_actval")->item(0)->textContent)) {
                    $retorno["inffin1510_actval"] = $reg->getElementsByTagName("inffin1510_actval")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_acttot")->item(0)->textContent)) {
                    $retorno["inffin1510_acttot"] = $reg->getElementsByTagName("inffin1510_acttot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_pascte")->item(0)->textContent)) {
                    $retorno["inffin1510_pascte"] = $reg->getElementsByTagName("inffin1510_pascte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_paslar")->item(0)->textContent)) {
                    $retorno["inffin1510_paslar"] = $reg->getElementsByTagName("inffin1510_paslar")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_pastot")->item(0)->textContent)) {
                    $retorno["inffin1510_pastot"] = $reg->getElementsByTagName("inffin1510_pastot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_patnet")->item(0)->textContent)) {
                    $retorno["inffin1510_patnet"] = $reg->getElementsByTagName("inffin1510_patnet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_paspat")->item(0)->textContent)) {
                    $retorno["inffin1510_paspat"] = $reg->getElementsByTagName("inffin1510_paspat")->item(0)->textContent;
                }
                $retorno["inffin1510_paspat"] = doubleval($retorno["inffin1510_pastot"]) + doubleval($retorno["inffin1510_patnet"]);
                if (isset($reg->getElementsByTagName("inffin1510_balsoc")->item(0)->textContent)) {
                    $retorno["inffin1510_balsoc"] = $reg->getElementsByTagName("inffin1510_balsoc")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_ingope")->item(0)->textContent)) {
                    $retorno["inffin1510_ingope"] = $reg->getElementsByTagName("inffin1510_ingope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_ingnoope")->item(0)->textContent)) {
                    $retorno["inffin1510_ingnoope"] = $reg->getElementsByTagName("inffin1510_ingnoope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_gasope")->item(0)->textContent)) {
                    $retorno["inffin1510_gasope"] = $reg->getElementsByTagName("inffin1510_gasope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_gasnoope")->item(0)->textContent)) {
                    $retorno["inffin1510_gasnoope"] = $reg->getElementsByTagName("inffin1510_gasnoope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_cosven")->item(0)->textContent)) {
                    $retorno["inffin1510_cosven"] = $reg->getElementsByTagName("inffin1510_cosven")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_utinet")->item(0)->textContent)) {
                    $retorno["inffin1510_utinet"] = $reg->getElementsByTagName("inffin1510_utinet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_utiope")->item(0)->textContent)) {
                    $retorno["inffin1510_utiope"] = $reg->getElementsByTagName("inffin1510_utiope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_gasint")->item(0)->textContent)) {
                    $retorno["inffin1510_gasint"] = $reg->getElementsByTagName("inffin1510_gasint")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_gasimp")->item(0)->textContent)) {
                    $retorno["inffin1510_gasimp"] = $reg->getElementsByTagName("inffin1510_gasimp")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent == '999') {
                        $retorno["inffin1510_indliq"] = 'INDEFINIDO';
                    } else {
                        $pos = strpos($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent, 'E-');
                        if ($pos === false) {
                            $retorno["inffin1510_indliq"] = trim($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent);
                        } else {
                            $valx = number_format($reg->getElementsByTagName("inffin1510_indliq")->item(0)->textContent, 10);
                            $retorno["inffin1510_indliq"] = substr($valx, 0, 4);
                        }
                    }
                }
                if (isset($reg->getElementsByTagName("inffin1510_nivend")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin1510_nivend")->item(0)->textContent == '999') {
                        $retorno["inffin1510_nivend"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin1510_nivend"] = $reg->getElementsByTagName("inffin1510_nivend")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin1510_razcob")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin1510_razcob")->item(0)->textContent == '999') {
                        $retorno["inffin1510_razcob"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin1510_razcob"] = $reg->getElementsByTagName("inffin1510_razcob")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin1510_renpat")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin1510_renpat")->item(0)->textContent == '999') {
                        $retorno["inffin1510_renpat"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin1510_renpat"] = $reg->getElementsByTagName("inffin1510_renpat")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin1510_renact")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin1510_renact")->item(0)->textContent == '999') {
                        $retorno["inffin1510_renact"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin1510_renact"] = $reg->getElementsByTagName("inffin1510_renact")->item(0)->textContent;
                    }
                }
                $retorno["inffin1510_actnocte"] = (double) $retorno["inffin1510_actnocte"] +
                        (double) $retorno["inffin1510_fijnet"] +
                        (double) $retorno["inffin1510_actotr"] +
                        (double) $retorno["inffin1510_actval"];
                if (isset($reg->getElementsByTagName("inffin1510_gruponiif")->item(0)->textContent)) {
                    $retorno["inffin1510_gruponiif"] = $reg->getElementsByTagName("inffin1510_gruponiif")->item(0)->textContent;
                }

                // **************************************************************************************************************** //
                // Informacion financiera - Decreto 399a
                // **************************************************************************************************************** //
                if (isset($reg->getElementsByTagName("inffin399a_fechacorte")->item(0)->textContent)) {
                    $retorno["inffin399a_fechacorte"] = $reg->getElementsByTagName("inffin399a_fechacorte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_pregrabado")->item(0)->textContent)) {
                    $retorno["inffin399a_pregrabado"] = $reg->getElementsByTagName("inffin399a_pregrabado")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_actcte")->item(0)->textContent)) {
                    $retorno["inffin399a_actcte"] = $reg->getElementsByTagName("inffin399a_actcte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_actnocte")->item(0)->textContent)) {
                    $retorno["inffin399a_actnocte"] = $reg->getElementsByTagName("inffin399a_actnocte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_acttot")->item(0)->textContent)) {
                    $retorno["inffin399a_acttot"] = $reg->getElementsByTagName("inffin399a_acttot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_pascte")->item(0)->textContent)) {
                    $retorno["inffin399a_pascte"] = $reg->getElementsByTagName("inffin399a_pascte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_paslar")->item(0)->textContent)) {
                    $retorno["inffin399a_paslar"] = $reg->getElementsByTagName("inffin399a_paslar")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_pastot")->item(0)->textContent)) {
                    $retorno["inffin399a_pastot"] = $reg->getElementsByTagName("inffin399a_pastot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_patnet")->item(0)->textContent)) {
                    $retorno["inffin399a_patnet"] = $reg->getElementsByTagName("inffin399a_patnet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_paspat")->item(0)->textContent)) {
                    $retorno["inffin399a_paspat"] = $reg->getElementsByTagName("inffin399a_paspat")->item(0)->textContent;
                }
                $retorno["inffin399a_paspat"] = doubleval($retorno["inffin399a_pastot"]) + doubleval($retorno["inffin399a_patnet"]);
                if (isset($reg->getElementsByTagName("inffin399a_balsoc")->item(0)->textContent)) {
                    $retorno["inffin399a_balsoc"] = $reg->getElementsByTagName("inffin399a_balsoc")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_ingope")->item(0)->textContent)) {
                    $retorno["inffin399a_ingope"] = $reg->getElementsByTagName("inffin399a_ingope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_ingnoope")->item(0)->textContent)) {
                    $retorno["inffin399a_ingnoope"] = $reg->getElementsByTagName("inffin399a_ingnoope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_gasope")->item(0)->textContent)) {
                    $retorno["inffin399a_gasope"] = $reg->getElementsByTagName("inffin399a_gasope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_gasnoope")->item(0)->textContent)) {
                    $retorno["inffin399a_gasnoope"] = $reg->getElementsByTagName("inffin399a_gasnoope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_cosven")->item(0)->textContent)) {
                    $retorno["inffin399a_cosven"] = $reg->getElementsByTagName("inffin399a_cosven")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_utinet")->item(0)->textContent)) {
                    $retorno["inffin399a_utinet"] = $reg->getElementsByTagName("inffin399a_utinet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_utiope")->item(0)->textContent)) {
                    $retorno["inffin399a_utiope"] = $reg->getElementsByTagName("inffin399a_utiope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_gasint")->item(0)->textContent)) {
                    $retorno["inffin399a_gasint"] = $reg->getElementsByTagName("inffin399a_gasint")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_gasimp")->item(0)->textContent)) {
                    $retorno["inffin399a_gasimp"] = $reg->getElementsByTagName("inffin399a_gasimp")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent == '999') {
                        $retorno["inffin399a_indliq"] = 'INDEFINIDO';
                    } else {
                        $pos = strpos($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent, 'E-');
                        if ($pos === false) {
                            $retorno["inffin399a_indliq"] = trim($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent);
                        } else {
                            $valx = number_format($reg->getElementsByTagName("inffin399a_indliq")->item(0)->textContent, 10);
                            $retorno["inffin399a_indliq"] = substr($valx, 0, 4);
                        }
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399a_nivend")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399a_nivend")->item(0)->textContent == '999') {
                        $retorno["inffin399a_nivend"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399a_nivend"] = $reg->getElementsByTagName("inffin399a_nivend")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399a_razcob")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399a_razcob")->item(0)->textContent == '999') {
                        $retorno["inffin399a_razcob"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399a_razcob"] = $reg->getElementsByTagName("inffin399a_razcob")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399a_renpat")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399a_renpat")->item(0)->textContent == '999') {
                        $retorno["inffin399a_renpat"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399a_renpat"] = $reg->getElementsByTagName("inffin399a_renpat")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399a_renact")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399a_renact")->item(0)->textContent == '999') {
                        $retorno["inffin399a_renact"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399a_renact"] = $reg->getElementsByTagName("inffin399a_renact")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399a_gruponiif")->item(0)->textContent)) {
                    $retorno["inffin399a_gruponiif"] = $reg->getElementsByTagName("inffin399a_gruponiif")->item(0)->textContent;
                }

                // **************************************************************************************************************** //
                // Informacion financiera - Decreto 399b
                // **************************************************************************************************************** //
                if (isset($reg->getElementsByTagName("inffin399b_fechacorte")->item(0)->textContent)) {
                    $retorno["inffin399b_fechacorte"] = $reg->getElementsByTagName("inffin399b_fechacorte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_pregrabado")->item(0)->textContent)) {
                    $retorno["inffin399b_pregrabado"] = $reg->getElementsByTagName("inffin399b_pregrabado")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_actcte")->item(0)->textContent)) {
                    $retorno["inffin399b_actcte"] = $reg->getElementsByTagName("inffin399b_actcte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_actnocte")->item(0)->textContent)) {
                    $retorno["inffin399b_actnocte"] = $reg->getElementsByTagName("inffin399b_actnocte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_acttot")->item(0)->textContent)) {
                    $retorno["inffin399b_acttot"] = $reg->getElementsByTagName("inffin399b_acttot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_pascte")->item(0)->textContent)) {
                    $retorno["inffin399b_pascte"] = $reg->getElementsByTagName("inffin399b_pascte")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_paslar")->item(0)->textContent)) {
                    $retorno["inffin399b_paslar"] = $reg->getElementsByTagName("inffin399b_paslar")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_pastot")->item(0)->textContent)) {
                    $retorno["inffin399b_pastot"] = $reg->getElementsByTagName("inffin399b_pastot")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_patnet")->item(0)->textContent)) {
                    $retorno["inffin399b_patnet"] = $reg->getElementsByTagName("inffin399b_patnet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_paspat")->item(0)->textContent)) {
                    $retorno["inffin399b_paspat"] = $reg->getElementsByTagName("inffin399b_paspat")->item(0)->textContent;
                }
                $retorno["inffin399b_paspat"] = doubleval($retorno["inffin399b_pastot"]) + doubleval($retorno["inffin399b_patnet"]);
                if (isset($reg->getElementsByTagName("inffin399b_balsoc")->item(0)->textContent)) {
                    $retorno["inffin399b_balsoc"] = $reg->getElementsByTagName("inffin399b_balsoc")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_ingope")->item(0)->textContent)) {
                    $retorno["inffin399b_ingope"] = $reg->getElementsByTagName("inffin399b_ingope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_ingnoope")->item(0)->textContent)) {
                    $retorno["inffin399b_ingnoope"] = $reg->getElementsByTagName("inffin399b_ingnoope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_gasope")->item(0)->textContent)) {
                    $retorno["inffin399b_gasope"] = $reg->getElementsByTagName("inffin399b_gasope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_gasnoope")->item(0)->textContent)) {
                    $retorno["inffin399b_gasnoope"] = $reg->getElementsByTagName("inffin399b_gasnoope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_cosven")->item(0)->textContent)) {
                    $retorno["inffin399b_cosven"] = $reg->getElementsByTagName("inffin399b_cosven")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_utinet")->item(0)->textContent)) {
                    $retorno["inffin399b_utinet"] = $reg->getElementsByTagName("inffin399b_utinet")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_utiope")->item(0)->textContent)) {
                    $retorno["inffin399b_utiope"] = $reg->getElementsByTagName("inffin399b_utiope")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_gasint")->item(0)->textContent)) {
                    $retorno["inffin399b_gasint"] = $reg->getElementsByTagName("inffin399b_gasint")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_gasimp")->item(0)->textContent)) {
                    $retorno["inffin399b_gasimp"] = $reg->getElementsByTagName("inffin399b_gasimp")->item(0)->textContent;
                }
                if (isset($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent == '999') {
                        $retorno["inffin399b_indliq"] = 'INDEFINIDO';
                    } else {
                        $pos = strpos($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent, 'E-');
                        if ($pos === false) {
                            $retorno["inffin399b_indliq"] = trim($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent);
                        } else {
                            $valx = number_format($reg->getElementsByTagName("inffin399b_indliq")->item(0)->textContent, 10);
                            $retorno["inffin399b_indliq"] = substr($valx, 0, 4);
                        }
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399b_nivend")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399b_nivend")->item(0)->textContent == '999') {
                        $retorno["inffin399b_nivend"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399b_nivend"] = $reg->getElementsByTagName("inffin399b_nivend")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399b_razcob")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399b_razcob")->item(0)->textContent == '999') {
                        $retorno["inffin399b_razcob"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399b_razcob"] = $reg->getElementsByTagName("inffin399b_razcob")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399b_renpat")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399b_renpat")->item(0)->textContent == '999') {
                        $retorno["inffin399b_renpat"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399b_renpat"] = $reg->getElementsByTagName("inffin399b_renpat")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399b_renact")->item(0)->textContent)) {
                    if ($reg->getElementsByTagName("inffin399b_renact")->item(0)->textContent == '999') {
                        $retorno["inffin399b_renact"] = 'INDEFINIDO';
                    } else {
                        $retorno["inffin399b_renact"] = $reg->getElementsByTagName("inffin399b_renact")->item(0)->textContent;
                    }
                }
                if (isset($reg->getElementsByTagName("inffin399b_gruponiif")->item(0)->textContent)) {
                    $retorno["inffin399b_gruponiif"] = $reg->getElementsByTagName("inffin399b_gruponiif")->item(0)->textContent;
                }

                /*        	  
                 * Experiencia decreto 1510
                 * Celebrado por: 
                 * 1.- El proponente
                 * 2.- Accionista
                 * 3.- Consorcio o union temporal
                 */

                $cont = $reg->getElementsByTagName("exp1510");
                if (count($cont) > 0) {
                    foreach ($cont as $cnt) {
                        if (strlen(ltrim(trim($cnt->getElementsByTagName("secuencia")->item(0)->textContent), "0")) <= 3) {
                            $sec = sprintf("%03s", ltrim(trim($cnt->getElementsByTagName("secuencia")->item(0)->textContent), "0"));
                        } else {
                            $sec = ltrim(trim($cnt->getElementsByTagName("secuencia")->item(0)->textContent), "0");
                        }
                        $ind = intval($sec);
                        $retorno["exp1510"][$ind]["secuencia"] = $sec;
                        if (isset($cnt->getElementsByTagName("clavecontrato")->item(0)->textContent)) {
                            $retorno["exp1510"][$ind]["clavecontrato"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("clavecontrato")->item(0)->textContent));
                        } else {
                            $retorno["exp1510"][$ind]["clavecontrato"] = '';
                        }
                        $retorno["exp1510"][$ind]["celebradopor"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("celebradopor")->item(0)->textContent));
                        $retorno["exp1510"][$ind]["nombrecontratista"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("nombrecontratista")->item(0)->textContent));
                        $retorno["exp1510"][$ind]["nombrecontratante"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("nombrecontratante")->item(0)->textContent));
                        if (isset($cnt->getElementsByTagName("fecejecucion")->item(0)->textContent)) {
                            $retorno["exp1510"][$ind]["fecejecucion"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("fecejecucion")->item(0)->textContent));
                        } else {
                            $retorno["exp1510"][$ind]["fecejecucion"] = '';
                        }
                        if (isset($cnt->getElementsByTagName("valorpesos")->item(0)->textContent)) {
                            $retorno["exp1510"][$ind]["valorpesos"] = $cnt->getElementsByTagName("valorpesos")->item(0)->textContent;
                        } else {
                            $retorno["exp1510"][$ind]["valorpesos"] = 0;
                        }
                        $xval = str_replace(",",".",$cnt->getElementsByTagName("valor")->item(0)->textContent);
                        $retorno["exp1510"][$ind]["valor"] = trim(\funcionesGenerales::restaurarEspeciales($xval));
                        $retorno["exp1510"][$ind]["porcentaje"] = trim(\funcionesGenerales::restaurarEspeciales($cnt->getElementsByTagName("porcentaje")->item(0)->textContent));

                        $ixx = -1;
                        $iCla = 0;
                        $clas = $cnt->getElementsByTagName("clasif");
                        if (count($clas) > 0) {
                            foreach ($clas as $c) {
                                $ixx++;
                                if (trim($clas->item($ixx)->textContent) != '') {
                                    $listcla = explode(",", $clas->item($ixx)->textContent);
                                    foreach ($listcla as $lc) {
                                        if (trim($lc) != '') {
                                            $iCla++;
                                            $retorno["exp1510"][$ind]["clasif"][$iCla] = trim($lc);
                                        }
                                    }
                                }
                            }
                        }

                        if (isset($cnt->getElementsByTagName("generardeclaracion")->item(0)->textContent)) {
                            $retorno["exp1510"][$ind]["generardeclaracion"] = $cnt->getElementsByTagName("generardeclaracion")->item(0)->textContent;
                        } else {
                            $retorno["exp1510"][$ind]["generardeclaracion"] = '';
                        }
                        $retorno["exp1510"][$ind]["soportedeclaracion"] = '';
                        $retorno["exp1510"][$ind]["soportecontrato"] = '';
                        $retorno["exp1510"][$ind]["revisado"] = '';
                        // if ($retorno["tipotramite"] == 'renovacionproponente' || $retorno["tipotramite"] == 'actualizacionespecialproponente') {
                        if (ltrim($retorno["numeroliquidacion"], "0") != '') {
                            $rets = retornarRegistroMysqliApi($mysqli, 'mreg_revision_experiencia', "idliquidacion=" . $retorno["numeroliquidacion"] . " and secuencia='" . $sec . "'");
                            if ($rets && !empty($rets)) {
                                if (substr($rets["controlcalidad"], 0, 2) == 'si') {
                                    $retorno["exp1510"][$ind]["revisado"] = $rets["controlcalidad"];
                                }
                            }
                        }
                        // }
                    }
                }
                unset($cont);
                if (!empty($retorno["exp1510"])) {
                    $temx = \funcionesGenerales::ordenarMatriz($retorno["exp1510"], "secuencia");
                    $retorno["exp1510"] = array();
                    $iCon = 0;
                    foreach ($temx as $t) {
                        $iCon++;
                        $retorno["exp1510"][$iCon] = $t;
                    }
                    unset($temx);
                }

                // Bienes
                $retorno["bienes"] = array();
                $i = 0;
                $bies = $reg->getElementsByTagName("bien");
                if (!empty($bies)) {
                    foreach ($bies as $bie) {
                        $i++;
                        $retorno["bienes"][$i]["matinmo"] = $bie->getElementsByTagName("matinmo")->item(0)->textContent;
                        $retorno["bienes"][$i]["dir"] = $bie->getElementsByTagName("dir")->item(0)->textContent;
                        $retorno["bienes"][$i]["barrio"] = $bie->getElementsByTagName("barrio")->item(0)->textContent;
                        $retorno["bienes"][$i]["muni"] = $bie->getElementsByTagName("muni")->item(0)->textContent;
                        $retorno["bienes"][$i]["pais"] = $bie->getElementsByTagName("pais")->item(0)->textContent;
                    }
                }
                unset($bies);
                unset($bie);

                // Contratos mults y sanciones
                $retorno["contratos"] = array();
                $retorno["multas"] = array();
                $retorno["sanciones"] = array();
                $retorno["sandis"] = array();

                // Desserializa informacion de contratos de entidades del estado
                $i = 0;
                $contratos = $reg->getElementsByTagName("contrato");
                if (count($contratos) > 0) {
                    foreach ($contratos as $contrato) {
                        $i++;
                        if (isset($contrato->getElementsByTagName("isn")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["isn"] = ltrim($contrato->getElementsByTagName("isn")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["isn"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("codcamara")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["codcamara"] = ltrim($contrato->getElementsByTagName("codcamara")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["codcamara"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("numradic")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["numradic"] = ltrim($contrato->getElementsByTagName("numradic")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["numradic"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("fecradic")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["fecradic"] = ltrim($contrato->getElementsByTagName("fecradic")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["fecradic"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("horradic")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["horradic"] = trim($contrato->getElementsByTagName("horradic")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["horradic"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("fecreporte")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["fecreporte"] = ltrim($contrato->getElementsByTagName("fecreporte")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["fecreporte"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("nitentidad")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["nitentidad"] = ltrim($contrato->getElementsByTagName("nitentidad")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["nitentidad"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("nombreentidad")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["nombreentidad"] = (trim(\funcionesGenerales::restaurarEspeciales($contrato->getElementsByTagName("nombreentidad")->item(0)->textContent)));
                        } else {
                            $retorno["contratos"][$i]["nombreentidad"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("idmunientidad")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["idmunientidad"] = trim($contrato->getElementsByTagName("idmunientidad")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["idmunientidad"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("divarea")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["divarea"] = trim($contrato->getElementsByTagName("divarea")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["divarea"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("idefun")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["idefun"] = ltrim($contrato->getElementsByTagName("idefun")->item(0)->textContent, "0");
                        } else {
                            $retorno["contratos"][$i]["idefun"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("nomfun")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["nomfun"] = (trim(\funcionesGenerales::restaurarEspeciales($contrato->getElementsByTagName("nomfun")->item(0)->textContent)));
                        } else {
                            $retorno["contratos"][$i]["nomfun"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("carfun")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["carfun"] = trim($contrato->getElementsByTagName("carfun")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["carfun"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("numcontrato")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["numcontrato"] = trim($contrato->getElementsByTagName("numcontrato")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["numcontrato"] = '';
                        }

                        if (isset($contrato->getElementsByTagName("numcontratosecop")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["numcontratosecop"] = trim($contrato->getElementsByTagName("numcontratosecop")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["numcontratosecop"] = '';
                        }
                        if (isset($contrato->getElementsByTagName("fechaadj")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["fechaadj"] = trim($contrato->getElementsByTagName("fechaadj")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["fechaadj"] = trim($contrato->getElementsByTagName("fechaadj")->item(0)->textContent);
                        }

                        //
                        (isset($contrato->getElementsByTagName("fechaper")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["fechaper"] = trim($contrato->getElementsByTagName("fechaper")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["fechaper"] = '';

                        //
                        $retorno["contratos"][$i]["fechaini"] = trim($contrato->getElementsByTagName("fechaini")->item(0)->textContent);
                        $retorno["contratos"][$i]["fechater"] = trim($contrato->getElementsByTagName("fechater")->item(0)->textContent);

                        //
                        (isset($contrato->getElementsByTagName("fechaeje")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["fechaeje"] = trim($contrato->getElementsByTagName("fechaeje")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["fechaeje"] = '';

                        //
                        (isset($contrato->getElementsByTagName("fechaliq")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["fechaliq"] = trim($contrato->getElementsByTagName("fechaliq")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["fechaliq"] = '';

                        //
                        (isset($contrato->getElementsByTagName("codigoact")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["codigoact"] = trim($contrato->getElementsByTagName("codigoact")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["codigoact"] = '';

                        //
                        (isset($contrato->getElementsByTagName("ciiu1")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["ciiu1"] = trim($contrato->getElementsByTagName("ciiu1")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["ciiu1"] = '';

                        //
                        (isset($contrato->getElementsByTagName("ciiu2")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["ciiu2"] = trim($contrato->getElementsByTagName("ciiu2")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["ciiu2"] = '';

                        //
                        (isset($contrato->getElementsByTagName("ciiu3")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["ciiu3"] = trim($contrato->getElementsByTagName("ciiu3")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["ciiu3"] = '';

                        //
                        (isset($contrato->getElementsByTagName("ciiu4")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["ciiu4"] = trim($contrato->getElementsByTagName("ciiu4")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["ciiu4"] = '';

                        //
                        $retorno["contratos"][$i]["tipocont"] = trim($contrato->getElementsByTagName("tipocont")->item(0)->textContent);
                        $retorno["contratos"][$i]["valorcont"] = ltrim($contrato->getElementsByTagName("valorcont")->item(0)->textContent, "0");
                        $retorno["contratos"][$i]["valorcontpag"] = ltrim($contrato->getElementsByTagName("valorcontpag")->item(0)->textContent, "0");
                        $retorno["contratos"][$i]["indcump"] = trim($contrato->getElementsByTagName("indcump")->item(0)->textContent);
                        $retorno["contratos"][$i]["estadocont"] = trim($contrato->getElementsByTagName("estadocont")->item(0)->textContent);

                        //
                        (isset($contrato->getElementsByTagName("motivoter")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["motivoter"] = trim($contrato->getElementsByTagName("motivoter")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["motivoter"] = '';

                        //
                        (isset($contrato->getElementsByTagName("fechaterant")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["fechaterant"] = trim($contrato->getElementsByTagName("fechaterant")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["fechaterant"] = '';

                        //
                        (isset($contrato->getElementsByTagName("motivoces")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["motivoces"] = trim($contrato->getElementsByTagName("motivoces")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["motivoces"] = '';

                        //
                        (isset($contrato->getElementsByTagName("fechaces")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["fechaces"] = trim($contrato->getElementsByTagName("fechaces")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["fechaces"] = '';

                        //
                        (isset($contrato->getElementsByTagName("observaciones")->item(0)->textContent)) ?
                                        $retorno["contratos"][$i]["observaciones"] = trim($contrato->getElementsByTagName("observaciones")->item(0)->textContent) :
                                        $retorno["contratos"][$i]["observaciones"] = '';

                        //
                        $retorno["contratos"][$i]["numlibro"] = trim($contrato->getElementsByTagName("numlibro")->item(0)->textContent);
                        $retorno["contratos"][$i]["numreglib"] = trim($contrato->getElementsByTagName("numreglib")->item(0)->textContent);
                        $retorno["contratos"][$i]["fecreglib"] = trim($contrato->getElementsByTagName("fecreglib")->item(0)->textContent);

                        //
                        $retorno["contratos"][$i]["clasificaciones"] = array();
                        $clasif = $contrato->getElementsByTagName("clasicon");
                        $j = -1;
                        if (count($clasif) == 0) {
                            $retorno["contratos"][$i]["clasificaciones"] = array();
                        } else {
                            foreach ($clasif as $clas) {
                                $j++;
                                $retorno["contratos"][$i]["clasificaciones"][$j] = trim($clas->textContent);
                            }
                        }

                        //	
                        $retorno["contratos"][$i]["unspsc"] = array();
                        $clasif = $contrato->getElementsByTagName("unspsc");
                        $j = -1;
                        if (count($clasif) == 0) {
                            $retorno["contratos"][$i]["unspsc"] = array();
                        } else {
                            foreach ($clasif as $clas) {
                                $j++;
                                $retorno["contratos"][$i]["unspsc"][$j] = trim($clas->textContent);
                            }
                        }

                        //
                        $retorno["contratos"][$i]["objeto"] = '';
                        $descs = $contrato->getElementsByTagName("objeto");
                        if (count($descs) != 0) {
                            foreach ($descs as $des) {
                                $retorno["contratos"][$i]["objeto"] .= trim($des->textContent);
                            }
                        }
                        $retorno["contratos"][$i]["objeto"] = \funcionesGenerales::restaurarEspeciales($retorno["contratos"][$i]["objeto"]);

                        //
                        if (isset($contrato->getElementsByTagName("codigocamaraorigen")->item(0)->textContent)) {
                            $retorno["contratos"][$i]["codigocamaraorigen"] = trim($contrato->getElementsByTagName("codigocamaraorigen")->item(0)->textContent);
                            $retorno["contratos"][$i]["numregistrocamaraorigen"] = trim($contrato->getElementsByTagName("numregistrocamaraorigen")->item(0)->textContent);
                            $retorno["contratos"][$i]["fecregistrocamaraorigen"] = trim($contrato->getElementsByTagName("fecregistrocamaraorigen")->item(0)->textContent);
                        } else {
                            $retorno["contratos"][$i]["codigocamaraorigen"] = '';
                            $retorno["contratos"][$i]["numregistrocamaraorigen"] = '';
                            $retorno["contratos"][$i]["fecregistrocamaraorigen"] = '';
                        }
                        $retorno["contratos"][$i]["indicadorenvio"] = '0';

                        //
                    }
                }

                // Desserializa informacion de multas
                $i = 0;
                $multas = $reg->getElementsByTagName("multa");
                if (count($multas) > 0) {
                    foreach ($multas as $multa) {
                        $i++;
                        if (isset($multa->getElementsByTagName("isn")->item(0)->textContent)) {
                            $retorno["multas"][$i]["isn"] = ltrim($multa->getElementsByTagName("isn")->item(0)->textContent, "0");
                        } else {
                            $retorno["multas"][$i]["isn"] = '';
                        }
                        $retorno["multas"][$i]["codcamara"] = ltrim($multa->getElementsByTagName("codcamara")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["numradic"] = ltrim($multa->getElementsByTagName("numradic")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["fecradic"] = ltrim($multa->getElementsByTagName("fecradic")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["horradic"] = trim($multa->getElementsByTagName("horradic")->item(0)->textContent);
                        $retorno["multas"][$i]["fecreporte"] = ltrim($multa->getElementsByTagName("fecreporte")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["nitentidad"] = ltrim($multa->getElementsByTagName("nitentidad")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["nombreentidad"] = (trim(\funcionesGenerales::restaurarEspeciales($multa->getElementsByTagName("nombreentidad")->item(0)->textContent)));
                        $retorno["multas"][$i]["idmunientidad"] = trim($multa->getElementsByTagName("idmunientidad")->item(0)->textContent);
                        $retorno["multas"][$i]["divarea"] = trim($multa->getElementsByTagName("divarea")->item(0)->textContent);
                        $retorno["multas"][$i]["idefun"] = ltrim($multa->getElementsByTagName("idefun")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["nomfun"] = (trim(\funcionesGenerales::restaurarEspeciales($multa->getElementsByTagName("nomfun")->item(0)->textContent)));
                        $retorno["multas"][$i]["carfun"] = trim($multa->getElementsByTagName("carfun")->item(0)->textContent);
                        $retorno["multas"][$i]["numcontrato"] = trim($multa->getElementsByTagName("numcontrato")->item(0)->textContent);
                        if (isset($multa->getElementsByTagName("numcontratosecop")->item(0)->textContent)) {
                            $retorno["multas"][$i]["numcontratosecop"] = trim($multa->getElementsByTagName("numcontratosecop")->item(0)->textContent);
                        } else {
                            $retorno["multas"][$i]["numcontratosecop"] = '';
                        }
                        $retorno["multas"][$i]["numacto"] = trim($multa->getElementsByTagName("numacto")->item(0)->textContent);
                        $retorno["multas"][$i]["fechaacto"] = trim($multa->getElementsByTagName("fechaacto")->item(0)->textContent);
                        (isset($multa->getElementsByTagName("fechaeje")->item(0)->textContent)) ?
                                        $retorno["multas"][$i]["fechaeje"] = trim($multa->getElementsByTagName("fechaeje")->item(0)->textContent) :
                                        $retorno["multas"][$i]["fechaeje"] = '';
                        $retorno["multas"][$i]["valormult"] = ltrim($multa->getElementsByTagName("valormult")->item(0)->textContent, "0");
                        $retorno["multas"][$i]["valormultpag"] = ltrim($multa->getElementsByTagName("valormultpag")->item(0)->textContent, "0");
                        (isset($multa->getElementsByTagName("numsus")->item(0)->textContent)) ?
                                        $retorno["multas"][$i]["numsus"] = trim($multa->getElementsByTagName("numsus")->item(0)->textContent) :
                                        $retorno["multas"][$i]["numsus"] = '';
                        (isset($multa->getElementsByTagName("fechasus")->item(0)->textContent)) ?
                                        $retorno["multas"][$i]["fechasus"] = trim($multa->getElementsByTagName("fechasus")->item(0)->textContent) :
                                        $retorno["multas"][$i]["fechasus"] = '';
                        (isset($multa->getElementsByTagName("numconf")->item(0)->textContent)) ?
                                        $retorno["multas"][$i]["numconf"] = trim($multa->getElementsByTagName("numconf")->item(0)->textContent) :
                                        $retorno["multas"][$i]["numconf"] = '';
                        (isset($multa->getElementsByTagName("fechaconf")->item(0)->textContent)) ?
                                        $retorno["multas"][$i]["fechaconf"] = trim($multa->getElementsByTagName("fechaconf")->item(0)->textContent) :
                                        $retorno["multas"][$i]["fechaconf"] = '';
                        $retorno["multas"][$i]["estadomult"] = trim($multa->getElementsByTagName("estadomult")->item(0)->textContent);
                        $retorno["multas"][$i]["numrev"] = trim($multa->getElementsByTagName("numrev")->item(0)->textContent);
                        $retorno["multas"][$i]["fechanumrev"] = trim($multa->getElementsByTagName("fechanumrev")->item(0)->textContent);
                        $retorno["multas"][$i]["numlibro"] = trim($multa->getElementsByTagName("numlibro")->item(0)->textContent);
                        $retorno["multas"][$i]["numreglib"] = trim($multa->getElementsByTagName("numreglib")->item(0)->textContent);
                        $retorno["multas"][$i]["fecreglib"] = trim($multa->getElementsByTagName("fecreglib")->item(0)->textContent);

                        if (isset($multa->getElementsByTagName("codigocamaraorigen")->item(0)->textContent)) {
                            $retorno["multas"][$i]["codigocamaraorigen"] = trim($multa->getElementsByTagName("codigocamaraorigen")->item(0)->textContent);
                            $retorno["multas"][$i]["numregistrocamaraorigen"] = trim($multa->getElementsByTagName("numregistrocamaraorigen")->item(0)->textContent);
                            $retorno["multas"][$i]["fecregistrocamaraorigen"] = trim($multa->getElementsByTagName("fecregistrocamaraorigen")->item(0)->textContent);
                        } else {
                            $retorno["multas"][$i]["codigocamaraorigen"] = '';
                            $retorno["multas"][$i]["numregistrocamaraorigen"] = '';
                            $retorno["multas"][$i]["fecregistrocamaraorigen"] = '';
                        }
                        $retorno["multas"][$i]["indicadorenvio"] = '0';

                        //
                        (isset($multa->getElementsByTagName("observaciones")->item(0)->textContent)) ?
                                        $retorno["multas"][$i]["observaciones"] = trim($multa->getElementsByTagName("observaciones")->item(0)->textContent) :
                                        $retorno["multas"][$i]["observaciones"] = '';
                    }
                }

                // Desserializa informacion de sanciones
                $i = 0;
                $sanciones = $reg->getElementsByTagName("sancion");
                if (count($sanciones) > 0) {
                    foreach ($sanciones as $sancion) {
                        $i++;
                        if (isset($sancion->getElementsByTagName("isn")->item(0)->textContent)) {
                            $retorno["sanciones"][$i]["isn"] = ltrim($sancion->getElementsByTagName("isn")->item(0)->textContent, "0");
                        } else {
                            $retorno["sanciones"][$i]["isn"] = '';
                        }
                        $retorno["sanciones"][$i]["codcamara"] = ltrim($sancion->getElementsByTagName("codcamara")->item(0)->textContent, "0");
                        $retorno["sanciones"][$i]["numradic"] = ltrim($sancion->getElementsByTagName("numradic")->item(0)->textContent, "0");
                        $retorno["sanciones"][$i]["fecradic"] = ltrim($sancion->getElementsByTagName("fecradic")->item(0)->textContent, "0");
                        $retorno["sanciones"][$i]["horradic"] = trim($sancion->getElementsByTagName("horradic")->item(0)->textContent);
                        $retorno["sanciones"][$i]["fecreporte"] = ltrim($sancion->getElementsByTagName("fecreporte")->item(0)->textContent, "0");
                        $retorno["sanciones"][$i]["nitentidad"] = ltrim($sancion->getElementsByTagName("nitentidad")->item(0)->textContent, "0");
                        $retorno["sanciones"][$i]["nombreentidad"] = (trim(\funcionesGenerales::restaurarEspeciales($sancion->getElementsByTagName("nombreentidad")->item(0)->textContent)));
                        $retorno["sanciones"][$i]["idmunientidad"] = trim($sancion->getElementsByTagName("idmunientidad")->item(0)->textContent);
                        $retorno["sanciones"][$i]["divarea"] = trim($sancion->getElementsByTagName("divarea")->item(0)->textContent);
                        $retorno["sanciones"][$i]["idefun"] = ltrim($sancion->getElementsByTagName("idefun")->item(0)->textContent, "0");
                        $retorno["sanciones"][$i]["nomfun"] = (trim(\funcionesGenerales::restaurarEspeciales($sancion->getElementsByTagName("nomfun")->item(0)->textContent)));
                        $retorno["sanciones"][$i]["carfun"] = trim($sancion->getElementsByTagName("carfun")->item(0)->textContent);
                        $retorno["sanciones"][$i]["numcontrato"] = trim($sancion->getElementsByTagName("numcontrato")->item(0)->textContent);
                        if (isset($sancion->getElementsByTagName("numcontratosecop")->item(0)->textContent)) {
                            $retorno["sanciones"][$i]["numcontratosecop"] = trim($sancion->getElementsByTagName("numcontratosecop")->item(0)->textContent);
                        } else {
                            $retorno["sanciones"][$i]["numcontratosecop"] = '';
                        }
                        $retorno["sanciones"][$i]["numacto"] = trim($sancion->getElementsByTagName("numacto")->item(0)->textContent);
                        $retorno["sanciones"][$i]["fechaacto"] = trim($sancion->getElementsByTagName("fechaacto")->item(0)->textContent);
                        (isset($sancion->getElementsByTagName("fechaeje")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["fechaeje"] = trim($sancion->getElementsByTagName("fechaeje")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["fechaeje"] = '';
                        (isset($sancion->getElementsByTagName("numsus")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["numsus"] = trim($sancion->getElementsByTagName("numsus")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["numsus"] = '';
                        (isset($sancion->getElementsByTagName("fechasus")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["fechasus"] = trim($sancion->getElementsByTagName("fechasus")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["fechasus"] = '';
                        (isset($sancion->getElementsByTagName("numconf")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["numconf"] = trim($sancion->getElementsByTagName("numconf")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["numconf"] = '';
                        (isset($sancion->getElementsByTagName("fechaconf")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["fechaconf"] = trim($sancion->getElementsByTagName("fechaconf")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["fechaconf"] = '';
                        (isset($sancion->getElementsByTagName("estadosanc")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["estadosanc"] = trim($sancion->getElementsByTagName("estadosanc")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["estadosanc"] = '';
                        (isset($sancion->getElementsByTagName("condinc")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["condinc"] = trim($sancion->getElementsByTagName("condinc")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["condinc"] = '';
                        (isset($sancion->getElementsByTagName("cumsanc")->item(0)->textContent)) ?
                                        $retorno["sanciones"][$i]["cumsanc"] = trim($sancion->getElementsByTagName("cumsanc")->item(0)->textContent) :
                                        $retorno["sanciones"][$i]["cumsanc"] = '';

                        $retorno["sanciones"][$i]["numrev"] = trim($sancion->getElementsByTagName("numrev")->item(0)->textContent);
                        $retorno["sanciones"][$i]["fechanumrev"] = trim($sancion->getElementsByTagName("fechanumrev")->item(0)->textContent);
                        $retorno["sanciones"][$i]["numlibro"] = trim($sancion->getElementsByTagName("numlibro")->item(0)->textContent);
                        $retorno["sanciones"][$i]["numreglib"] = trim($sancion->getElementsByTagName("numreglib")->item(0)->textContent);
                        $retorno["sanciones"][$i]["fecreglib"] = trim($sancion->getElementsByTagName("fecreglib")->item(0)->textContent);

                        //
                        $retorno["sanciones"][$i]["descripcion"] = '';
                        $descs = $sancion->getElementsByTagName("dessanc");
                        if (count($descs) != 0) {
                            foreach ($descs as $des) {
                                $retorno["sanciones"][$i]["descripcion"] .= trim($des->textContent);
                            }
                        }
                        $retorno["sanciones"][$i]["descripcion"] = \funcionesGenerales::restaurarEspeciales($retorno["sanciones"][$i]["descripcion"]);

                        //
                        $retorno["sanciones"][$i]["fundamento"] = '';
                        $descs = $sancion->getElementsByTagName("fundame");
                        if (count($descs) != 0) {
                            foreach ($descs as $des) {
                                $retorno["sanciones"][$i]["fundamento"] .= trim($des->textContent);
                            }
                        }
                        $retorno["sanciones"][$i]["fundamento"] = \funcionesGenerales::restaurarEspeciales($retorno["sanciones"][$i]["fundamento"]);

                        if (isset($sancion->getElementsByTagName("vigencia")->item(0)->textContent)) {
                            $retorno["sanciones"][$i]["vigencia"] = trim($sancion->getElementsByTagName("vigencia")->item(0)->textContent);
                        } else {
                            $retorno["sanciones"][$i]["vigencia"] = '';
                        }

                        if (isset($sancion->getElementsByTagName("codigocamaraorigen")->item(0)->textContent)) {
                            $retorno["sanciones"][$i]["codigocamaraorigen"] = trim($sancion->getElementsByTagName("codigocamaraorigen")->item(0)->textContent);
                            $retorno["sanciones"][$i]["numregistrocamaraorigen"] = trim($sancion->getElementsByTagName("numregistrocamaraorigen")->item(0)->textContent);
                            $retorno["sanciones"][$i]["fecregistrocamaraorigen"] = trim($sancion->getElementsByTagName("fecregistrocamaraorigen")->item(0)->textContent);
                        } else {
                            $retorno["sanciones"][$i]["codigocamaraorigen"] = '';
                            $retorno["sanciones"][$i]["numregistrocamaraorigen"] = '';
                            $retorno["sanciones"][$i]["fecregistrocamaraorigen"] = '';
                        }
                        $retorno["sanciones"][$i]["indicadorenvio"] = '0';
                    }
                }

                // Desserializa informacion de sanciones
                $i = 0;
                $sandisc = $reg->getElementsByTagName("sandis");
                if (count($sandisc) > 0) {
                    foreach ($sandisc as $sancion) {
                        $i++;
                        if (isset($sancion->getElementsByTagName("isn")->item(0)->textContent)) {
                            $retorno["sandis"][$i]["isn"] = ltrim($sancion->getElementsByTagName("isn")->item(0)->textContent, "0");
                        } else {
                            $retorno["sandis"][$i]["isn"] = '';
                        }
                        $retorno["sandis"][$i]["codcamara"] = ltrim($sancion->getElementsByTagName("codcamara")->item(0)->textContent, "0");
                        $retorno["sandis"][$i]["numradic"] = ltrim($sancion->getElementsByTagName("numradic")->item(0)->textContent, "0");
                        $retorno["sandis"][$i]["fecradic"] = ltrim($sancion->getElementsByTagName("fecradic")->item(0)->textContent, "0");
                        $retorno["sandis"][$i]["horradic"] = trim($sancion->getElementsByTagName("horradic")->item(0)->textContent);
                        $retorno["sandis"][$i]["fecreporte"] = ltrim($sancion->getElementsByTagName("fecreporte")->item(0)->textContent, "0");
                        $retorno["sandis"][$i]["nitentidad"] = ltrim($sancion->getElementsByTagName("nitentidad")->item(0)->textContent, "0");
                        $retorno["sandis"][$i]["nombreentidad"] = (trim($sancion->getElementsByTagName("nombreentidad")->item(0)->textContent));
                        $retorno["sandis"][$i]["idmunientidad"] = trim($sancion->getElementsByTagName("idmunientidad")->item(0)->textContent);
                        $retorno["sandis"][$i]["divarea"] = trim($sancion->getElementsByTagName("divarea")->item(0)->textContent);
                        $retorno["sandis"][$i]["idefun"] = ltrim($sancion->getElementsByTagName("idefun")->item(0)->textContent, "0");
                        $retorno["sandis"][$i]["nomfun"] = (trim($sancion->getElementsByTagName("nomfun")->item(0)->textContent));
                        $retorno["sandis"][$i]["carfun"] = trim($sancion->getElementsByTagName("carfun")->item(0)->textContent);

                        if (isset($sancion->getElementsByTagName("numcontratosecop")->item(0)->textContent)) {
                            $retorno["sandis"][$i]["numcontratosecop"] = trim($sancion->getElementsByTagName("numcontratosecop")->item(0)->textContent);
                        } else {
                            $retorno["sandis"][$i]["numcontratosecop"] = '';
                        }

                        $retorno["sandis"][$i]["numacto"] = trim($sancion->getElementsByTagName("numacto")->item(0)->textContent);
                        $retorno["sandis"][$i]["fechaacto"] = trim($sancion->getElementsByTagName("fechaacto")->item(0)->textContent);
                        (isset($sancion->getElementsByTagName("fechaeje")->item(0)->textContent)) ?
                                        $retorno["sandis"][$i]["fechaeje"] = trim($sancion->getElementsByTagName("fechaeje")->item(0)->textContent) :
                                        $retorno["sandis"][$i]["fechaeje"] = '';
                        (isset($sancion->getElementsByTagName("numsus")->item(0)->textContent)) ?
                                        $retorno["sandis"][$i]["numsus"] = trim($sancion->getElementsByTagName("numsus")->item(0)->textContent) :
                                        $retorno["sandis"][$i]["numsus"] = '';
                        (isset($sancion->getElementsByTagName("fechasus")->item(0)->textContent)) ?
                                        $retorno["sandis"][$i]["fechasus"] = trim($sancion->getElementsByTagName("fechasus")->item(0)->textContent) :
                                        $retorno["sandis"][$i]["fechasus"] = '';
                        (isset($sancion->getElementsByTagName("numconf")->item(0)->textContent)) ?
                                        $retorno["sandis"][$i]["numconf"] = trim($sancion->getElementsByTagName("numconf")->item(0)->textContent) :
                                        $retorno["sandis"][$i]["numconf"] = '';
                        (isset($sancion->getElementsByTagName("fechaconf")->item(0)->textContent)) ?
                                        $retorno["sandis"][$i]["fechaconf"] = trim($sancion->getElementsByTagName("fechaconf")->item(0)->textContent) :
                                        $retorno["sandis"][$i]["fechaconf"] = '';
                        (isset($sancion->getElementsByTagName("estadosanc")->item(0)->textContent)) ?
                                        $retorno["sandis"][$i]["estadosanc"] = trim($sancion->getElementsByTagName("estadosanc")->item(0)->textContent) :
                                        $retorno["sandis"][$i]["estadosanc"] = '';
                        $retorno["sandis"][$i]["numrev"] = trim($sancion->getElementsByTagName("numrev")->item(0)->textContent);
                        $retorno["sandis"][$i]["fechanumrev"] = trim($sancion->getElementsByTagName("fechanumrev")->item(0)->textContent);
                        $retorno["sandis"][$i]["numlibro"] = trim($sancion->getElementsByTagName("numlibro")->item(0)->textContent);
                        $retorno["sandis"][$i]["numreglib"] = trim($sancion->getElementsByTagName("numreglib")->item(0)->textContent);
                        $retorno["sandis"][$i]["fecreglib"] = trim($sancion->getElementsByTagName("fecreglib")->item(0)->textContent);

                        //
                        $retorno["sandis"][$i]["descripcion"] = '';
                        $descs = $sancion->getElementsByTagName("dessanc");
                        if (count($descs) != 0) {
                            foreach ($descs as $des) {
                                $retorno["sandis"][$i]["descripcion"] .= trim($des->textContent);
                            }
                        }

                        //
                        $retorno["sandis"][$i]["fundamento"] = '';
                        $descs = $sancion->getElementsByTagName("fundame");
                        if (count($descs) != 0) {
                            foreach ($descs as $des) {
                                $retorno["sandis"][$i]["fundamento"] .= trim($des->textContent);
                            }
                        }

                        if (isset($sancion->getElementsByTagName("vigencia")->item(0)->textContent)) {
                            $retorno["sandis"][$i]["vigencia"] = trim($contrato->getElementsByTagName("vigencia")->item(0)->textContent);
                        } else {
                            $retorno["sandis"][$i]["vigencia"] = '';
                        }

                        if (isset($sancion->getElementsByTagName("codigocamaraorigen")->item(0)->textContent)) {
                            $retorno["sandis"][$i]["codigocamaraorigen"] = trim($sancion->getElementsByTagName("codigocamaraorigen")->item(0)->textContent);
                            $retorno["sandis"][$i]["numregistrocamaraorigen"] = trim($sancion->getElementsByTagName("numregistrocamaraorigen")->item(0)->textContent);
                            $retorno["sandis"][$i]["fecregistrocamaraorigen"] = trim($sancion->getElementsByTagName("fecregistrocamaraorigen")->item(0)->textContent);
                        } else {
                            $retorno["sandis"][$i]["codigocamaraorigen"] = '';
                            $retorno["sandis"][$i]["numregistrocamaraorigen"] = '';
                            $retorno["sandis"][$i]["fecregistrocamaraorigen"] = '';
                        }

                        $retorno["sandis"][$i]["indicadorenvio"] = '0';
                    }
                }
            }

            // Verifica si en el XML hay grupos modificados
            $retorno["gruposmodificados"] = array();
            if ($dom->getElementsByTagName("gruposmodificados") != null) {
                $reg1 = $dom->getElementsByTagName("gruposmodificados");
                foreach ($reg1 as $reg) {
                    $gm = $reg->getElementsByTagName("grupo");
                    if (count($gm) > 0) {
                        foreach ($gm as $g) {
                            $retorno["gruposmodificados"][$g->textContent] = $g->textContent;
                        }
                    }
                }
            }
            unset($gm);
            unset($reg);
            unset($reg1);
            unset($dom);
        }
        return $retorno;
    }

}
