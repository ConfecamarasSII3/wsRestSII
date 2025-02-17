<?php

class funcionesRegistrales_serializarExpedienteProponente {

    public static function serializarExpedienteProponente($dbx = null, $data = '') {
        $xml = '<?xml version="1.0" encoding="utf-8" ?>' . chr(13);
        $xml .= '<expedientes>' . chr(13);
        $xml .= '<expediente>' . chr(13);

        //
        if ($data != '') {
            $xml .= '<tipotramite></tipotramite>' . chr(13);
            $xml .= '<numerorecuperacion></numerorecuperacion>' . chr(13);
            $xml .= '<numeroliquidacion></numeroliquidacion>' . chr(13);
            $xml .= '<numeroradicacion></numeroradicacion>' . chr(13);
            $xml .= '<proponente>' . ltrim($data["proponente"], '0') . '</proponente>' . chr(13);
            $xml .= '<matricula>' . ltrim($data["matricula"], '0') . '</matricula>' . chr(13);
            $xml .= '<nombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["nombre"])) . ']]></nombre>' . chr(13);
            $xml .= '<ape1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["ape1"])) . ']]></ape1>' . chr(13);
            $xml .= '<ape2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["ape2"])) . ']]></ape2>' . chr(13);
            $xml .= '<nom1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["nom1"])) . ']]></nom1>' . chr(13);
            $xml .= '<nom2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["nom2"])) . ']]></nom2>' . chr(13);
            $xml .= '<sigla><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["sigla"])) . ']]></sigla>' . chr(13);
            $xml .= '<idtipoidentificacion>' . $data["idtipoidentificacion"] . '</idtipoidentificacion>' . chr(13);
            $xml .= '<identificacion>' . ltrim($data["identificacion"], '0') . '</identificacion>' . chr(13);
            $xml .= '<idpaisidentificacion>' . $data["idpaisidentificacion"] . '</idpaisidentificacion>' . chr(13);
            $xml .= '<nit>' . ltrim($data["nit"], '0') . '</nit>' . chr(13);
            $xml .= '<nacionalidad><![CDATA[' . trim($data["nacionalidad"]) . ']]></nacionalidad>' . chr(13);
            $xml .= '<organizacion>' . $data["organizacion"] . '</organizacion>' . chr(13);
            $xml .= '<tamanoempresa>' . $data["tamanoempresa"] . '</tamanoempresa>' . chr(13);
            $xml .= '<emprendedor28>' . $data["emprendedor28"] . '</emprendedor28>' . chr(13);
            $xml .= '<pemprendedor28>' . $data["pemprendedor28"] . '</pemprendedor28>' . chr(13);
            $xml .= '<vigcontrol>' . $data["vigcontrol"] . '</vigcontrol>' . chr(13);
            $xml .= '<idestadoproponente>' . $data["idestadoproponente"] . '</idestadoproponente>' . chr(13);
            $xml .= '<certificardesde>' . $data["certificardesde"] . '</certificardesde>' . chr(13);
            $xml .= '<fechaultimainscripcion>' . $data["fechaultimainscripcion"] . '</fechaultimainscripcion>' . chr(13);
            $xml .= '<fechaultimarenovacion>' . $data["fechaultimarenovacion"] . '</fechaultimarenovacion>' . chr(13);
            $xml .= '<fechacancelacion>' . $data["fechacancelacion"] . '</fechacancelacion>' . chr(13);

            // Cambio de domicilio
            $xml .= '<cambidom_idmunicipioorigen>' . $data["cambidom_idmunicipioorigen"] . '</cambidom_idmunicipioorigen>' . chr(13);
            $xml .= '<cambidom_idmunicipiodestino>' . $data["cambidom_idmunicipiodestino"] . '</cambidom_idmunicipiodestino>' . chr(13);
            $xml .= '<cambidom_fechaultimainscripcion>' . $data["cambidom_fechaultimainscripcion"] . '</cambidom_fechaultimainscripcion>' . chr(13);
            $xml .= '<cambidom_fechaultimarenovacion>' . $data["cambidom_fechaultimarenovacion"] . '</cambidom_fechaultimarenovacion>' . chr(13);
            if (isset($data["propcamaraorigen"])) {
                $xml .= '<propcamaraorigen>' . $data["propcamaraorigen"] . '</propcamaraorigen>' . chr(13);
            } else {
                $xml .= '<propcamaraorigen></propcamaraorigen>' . chr(13);
            }
            if (isset($data["propcamaradestino"])) {
                $xml .= '<propcamaradestino>' . $data["propcamaradestino"] . '</propcamaradestino>' . chr(13);
            } else {
                $xml .= '<propcamaradestino></propcamaradestino>' . chr(13);
            }

            // Datos de personer&iacute;a jur&iacute;dica
            $xml .= '<idtipodocperjur>' . $data["idtipodocperjur"] . '</idtipodocperjur>' . chr(13); // A02
            $xml .= '<numdocperjur>' . $data["numdocperjur"] . '</numdocperjur>' . chr(13); // A20
            $xml .= '<fecdocperjur>' . $data["fecdocperjur"] . '</fecdocperjur>' . chr(13); // (D)
            $xml .= '<origendocperjur><![CDATA[' . $data["origendocperjur"] . ']]></origendocperjur>' . chr(13); // A128
            $xml .= '<fechaconstitucion>' . $data["fechaconstitucion"] . '</fechaconstitucion>' . chr(13); // (D)
            $xml .= '<fechavencimiento>' . $data["fechavencimiento"] . '</fechavencimiento>' . chr(13); // (D)
            // Datos de ubicacion
            $xml .= '<dircom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($data["dircom"]) . ']]></dircom>' . chr(13); // A128
            $xml .= '<dircom_tipovia></dircom_tipovia>' . chr(13); //A5
            $xml .= '<dircom_numvia></dircom_numvia>' . chr(13); //A3
            $xml .= '<dircom_apevia></dircom_apevia>' . chr(13); //A5
            $xml .= '<dircom_orivia></dircom_orivia>' . chr(13); //A5
            $xml .= '<dircom_numcruce></dircom_numcruce>' . chr(13); //A3
            $xml .= '<dircom_apecruce></dircom_apecruce>' . chr(13); //A5
            $xml .= '<dircom_oricruce></dircom_oricruce>' . chr(13); //A5
            $xml .= '<dircom_numplaca></dircom_numplaca>' . chr(13); //A3
            $xml .= '<dircom_complemento><![CDATA[]]></dircom_complemento>' . chr(13); //A20
            $xml .= '<muncom>' . $data["muncom"] . '</muncom>' . chr(13);
            $xml .= '<telcom1>' . $data["telcom1"] . '</telcom1>' . chr(13);
            $xml .= '<telcom2>' . $data["telcom2"] . '</telcom2>' . chr(13);
            $xml .= '<celcom>' . $data["celcom"] . '</celcom>' . chr(13);
            $xml .= '<faxcom>' . $data["faxcom"] . '</faxcom>' . chr(13);
            $xml .= '<emailcom><![CDATA[' . $data["emailcom"] . ']]></emailcom>' . chr(13);
            $xml .= '<enviarint>' . $data["enviarint"] . '</enviarint>' . chr(13);
            $xml .= '<dirnot><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($data["dirnot"]) . ']]></dirnot>' . chr(13);
            $xml .= '<dirnot_tipovia></dirnot_tipovia>' . chr(13);
            $xml .= '<dirnot_numvia></dirnot_numvia>' . chr(13);
            $xml .= '<dirnot_apevia></dirnot_apevia>' . chr(13);
            $xml .= '<dirnot_orivia></dirnot_orivia>' . chr(13);
            $xml .= '<dirnot_numcruce></dirnot_numcruce>' . chr(13);
            $xml .= '<dirnot_apecruce></dirnot_apecruce>' . chr(13);
            $xml .= '<dirnot_oricruce></dirnot_oricruce>' . chr(13);
            $xml .= '<dirnot_numplaca></dirnot_numplaca>' . chr(13);
            $xml .= '<dirnot_complemento><![CDATA[]]></dirnot_complemento>' . chr(13);
            $xml .= '<munnot>' . $data["munnot"] . '</munnot>' . chr(13);
            $xml .= '<telnot>' . $data["telnot"] . '</telnot>' . chr(13);
            $xml .= '<telnot2>' . $data["telnot2"] . '</telnot2>' . chr(13);
            $xml .= '<celnot>' . $data["celnot"] . '</celnot>' . chr(13);
            $xml .= '<faxnot>' . $data["faxnot"] . '</faxnot>' . chr(13);
            $xml .= '<emailnot><![CDATA[' . $data["emailnot"] . ']]></emailnot>' . chr(13);
            $xml .= '<enviarnot>' . $data["enviarnot"] . '</enviarnot>' . chr(13);

            // Representantes legales
            $xml .= '<representanteslegales>' . chr(13);
            if (!empty($data["representanteslegales"])) {
                foreach ($data["representanteslegales"] as $rep) {
                    $xml .= '<representantelegal>' . chr(13);
                    $xml .= '<idtipoidentificacionrepleg>' . $rep["idtipoidentificacionrepleg"] . "</idtipoidentificacionrepleg>";
                    $xml .= '<identificacionrepleg>' . $rep["identificacionrepleg"] . "</identificacionrepleg>";
                    $xml .= '<nombrerepleg><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["nombrerepleg"]) . "]]></nombrerepleg>";
                    $xml .= '<cargorepleg><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["cargorepleg"]) . "]]></cargorepleg>";
                    $xml .= '</representantelegal>' . chr(13);
                }
            }
            $xml .= '</representanteslegales>' . chr(13);

            // Facultades
            if (isset($data["facultades"])) {
                $xml .= '<facultades><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["facultades"])) . ']]></facultades>' . chr(13);
            } else {
                $data["facultades"] = '';
                $xml .= '<facultades><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($data["facultades"])) . ']]></facultades>' . chr(13);
            }


            // Situaciones de control
            if (!empty($data["sitcontrol"])) {
                foreach ($data["sitcontrol"] as $rep) {
                    $xml .= '<sitcontrol>' . chr(13);
                    $xml .= '<nombre><![CDATA[' . $rep["nombre"] . ']]></nombre>';
                    $xml .= '<identificacion>' . $rep["identificacion"] . '</identificacion>';
                    $xml .= '<domicilio>' . $rep["domicilio"] . '</domicilio>';
                    $xml .= '<tipo>' . $rep["tipo"] . '</tipo>';
                    $xml .= '</sitcontrol>' . chr(13);
                }
            }

            // ********************************************************************** //
            // Informacion financiera - Decreto 1510
            // ********************************************************************** //
            $xml .= '<inffin1510_fechacorte>' . $data["inffin1510_fechacorte"] . '</inffin1510_fechacorte>' . chr(13);
            $xml .= '<inffin1510_actcte>' . $data["inffin1510_actcte"] . '</inffin1510_actcte>' . chr(13);
            if (isset($data["inffin1510_actnocte"])) {
                $xml .= '<inffin1510_actnocte>' . $data["inffin1510_actnocte"] . '</inffin1510_actnocte>' . chr(13);
            } else {
                $xml .= '<inffin1510_actnocte>0</inffin1510_actnocte>' . chr(13);
            }
            $xml .= '<inffin1510_fijnet>' . $data["inffin1510_fijnet"] . '</inffin1510_fijnet>' . chr(13);
            $xml .= '<inffin1510_actotr>' . $data["inffin1510_actotr"] . '</inffin1510_actotr>' . chr(13);
            $xml .= '<inffin1510_actval>' . $data["inffin1510_actval"] . '</inffin1510_actval>' . chr(13);
            $xml .= '<inffin1510_acttot>' . $data["inffin1510_acttot"] . '</inffin1510_acttot>' . chr(13);

            $xml .= '<inffin1510_pascte>' . $data["inffin1510_pascte"] . '</inffin1510_pascte>' . chr(13);
            $xml .= '<inffin1510_paslar>' . $data["inffin1510_paslar"] . '</inffin1510_paslar>' . chr(13);
            $xml .= '<inffin1510_pastot>' . $data["inffin1510_pastot"] . '</inffin1510_pastot>' . chr(13);
            $xml .= '<inffin1510_patnet>' . $data["inffin1510_patnet"] . '</inffin1510_patnet>' . chr(13);
            $xml .= '<inffin1510_paspat>' . $data["inffin1510_paspat"] . '</inffin1510_paspat>' . chr(13);
            if (isset($data["inffin1510_balsoc"])) {
                $xml .= '<inffin1510_balsoc>' . $data["inffin1510_balsoc"] . '</inffin1510_balsoc>' . chr(13);
            } else {
                $xml .= '<inffin1510_balsoc>0</inffin1510_balsoc>' . chr(13);
            }
            $xml .= '<inffin1510_ingope>' . $data["inffin1510_ingope"] . '</inffin1510_ingope>' . chr(13);
            $xml .= '<inffin1510_ingnoope>' . $data["inffin1510_ingnoope"] . '</inffin1510_ingnoope>' . chr(13);
            $xml .= '<inffin1510_gasope>' . $data["inffin1510_gasope"] . '</inffin1510_gasope>' . chr(13);
            $xml .= '<inffin1510_gasnoope>' . $data["inffin1510_gasnoope"] . '</inffin1510_gasnoope>' . chr(13);
            $xml .= '<inffin1510_cosven>' . $data["inffin1510_cosven"] . '</inffin1510_cosven>' . chr(13);
            $xml .= '<inffin1510_utiope>' . $data["inffin1510_utiope"] . '</inffin1510_utiope>' . chr(13);
            $xml .= '<inffin1510_utinet>' . $data["inffin1510_utinet"] . '</inffin1510_utinet>' . chr(13);
            $xml .= '<inffin1510_gasint>' . $data["inffin1510_gasint"] . '</inffin1510_gasint>' . chr(13);
            if (isset($data["inffin1510_gasimp"])) {
                $xml .= '<inffin1510_gasimp>' . $data["inffin1510_gasimp"] . '</inffin1510_gasimp>' . chr(13);
            } else {
                $xml .= '<inffin1510_gasimp>0</inffin1510_gasimp>' . chr(13);
            }

            $xml .= '<inffin1510_indliq>' . $data["inffin1510_indliq"] . '</inffin1510_indliq>' . chr(13);
            $xml .= '<inffin1510_nivend>' . $data["inffin1510_nivend"] . '</inffin1510_nivend>' . chr(13);
            $xml .= '<inffin1510_razcob>' . $data["inffin1510_razcob"] . '</inffin1510_razcob>' . chr(13);
            $xml .= '<inffin1510_renpat>' . $data["inffin1510_renpat"] . '</inffin1510_renpat>' . chr(13);
            $xml .= '<inffin1510_renact>' . $data["inffin1510_renact"] . '</inffin1510_renact>' . chr(13);
            if (!isset($data["inffin1510_gruponiif"])) {
                $data["inffin1510_gruponiif"] = '';
            }
            $xml .= '<inffin1510_gruponiif>' . $data["inffin1510_gruponiif"] . '</inffin1510_gruponiif>' . chr(13);

            // ********************************************************************** //
            // Informacion financiera - Decreto 399a
            // ********************************************************************** //
            if (!isset($data["inffin399a_pregrabado"])) {
                $data["inffin399a_pregrabado"] = '';
            }
            $xml .= '<inffin399a_fechacorte>' . $data["inffin399a_fechacorte"] . '</inffin399a_fechacorte>' . chr(13);
            $xml .= '<inffin399a_pregrabado>' . $data["inffin399a_pregrabado"] . '</inffin399a_pregrabado>' . chr(13);
            $xml .= '<inffin399a_actcte>' . $data["inffin399a_actcte"] . '</inffin399a_actcte>' . chr(13);
            $xml .= '<inffin399a_actnocte>' . $data["inffin399a_actnocte"] . '</inffin399a_actnocte>' . chr(13);
            $xml .= '<inffin399a_acttot>' . $data["inffin399a_acttot"] . '</inffin399a_acttot>' . chr(13);
            $xml .= '<inffin399a_pascte>' . $data["inffin399a_pascte"] . '</inffin399a_pascte>' . chr(13);
            $xml .= '<inffin399a_paslar>' . $data["inffin399a_paslar"] . '</inffin399a_paslar>' . chr(13);
            $xml .= '<inffin399a_pastot>' . $data["inffin399a_pastot"] . '</inffin399a_pastot>' . chr(13);
            $xml .= '<inffin399a_patnet>' . $data["inffin399a_patnet"] . '</inffin399a_patnet>' . chr(13);
            $xml .= '<inffin399a_paspat>' . $data["inffin399a_paspat"] . '</inffin399a_paspat>' . chr(13);
            $xml .= '<inffin399a_balsoc>' . $data["inffin399a_balsoc"] . '</inffin399a_balsoc>' . chr(13);
            $xml .= '<inffin399a_ingope>' . $data["inffin399a_ingope"] . '</inffin399a_ingope>' . chr(13);
            $xml .= '<inffin399a_ingnoope>' . $data["inffin399a_ingnoope"] . '</inffin399a_ingnoope>' . chr(13);
            $xml .= '<inffin399a_gasope>' . $data["inffin399a_gasope"] . '</inffin399a_gasope>' . chr(13);
            $xml .= '<inffin399a_gasnoope>' . $data["inffin399a_gasnoope"] . '</inffin399a_gasnoope>' . chr(13);
            $xml .= '<inffin399a_cosven>' . $data["inffin399a_cosven"] . '</inffin399a_cosven>' . chr(13);
            $xml .= '<inffin399a_utiope>' . $data["inffin399a_utiope"] . '</inffin399a_utiope>' . chr(13);
            $xml .= '<inffin399a_utinet>' . $data["inffin399a_utinet"] . '</inffin399a_utinet>' . chr(13);
            $xml .= '<inffin399a_gasint>' . $data["inffin399a_gasint"] . '</inffin399a_gasint>' . chr(13);
            if (isset($data["inffin399a_gasimp"])) {
                $xml .= '<inffin399a_gasimp>' . $data["inffin399a_gasimp"] . '</inffin399a_gasimp>' . chr(13);
            } else {
                $xml .= '<inffin399a_gasimp>0</inffin399a_gasimp>' . chr(13);
            }
            $xml .= '<inffin399a_indliq>' . $data["inffin399a_indliq"] . '</inffin399a_indliq>' . chr(13);
            $xml .= '<inffin399a_nivend>' . $data["inffin399a_nivend"] . '</inffin399a_nivend>' . chr(13);
            $xml .= '<inffin399a_razcob>' . $data["inffin399a_razcob"] . '</inffin399a_razcob>' . chr(13);
            $xml .= '<inffin399a_renpat>' . $data["inffin399a_renpat"] . '</inffin399a_renpat>' . chr(13);
            $xml .= '<inffin399a_renact>' . $data["inffin399a_renact"] . '</inffin399a_renact>' . chr(13);
            if (!isset($data["inffin399a_gruponiif"])) {
                $data["inffin399a_gruponiif"] = '';
            }
            $xml .= '<inffin399a_gruponiif>' . $data["inffin399a_gruponiif"] . '</inffin399a_gruponiif>' . chr(13);

            // ********************************************************************** //
            // Informacion financiera - Decreto 399b
            // ********************************************************************** //
            if (!isset($data["inffin399b_pregrabado"])) {
                $data["inffin399b_pregrabado"] = '';
            }
            $xml .= '<inffin399b_fechacorte>' . $data["inffin399b_fechacorte"] . '</inffin399b_fechacorte>' . chr(13);
            $xml .= '<inffin399b_pregrabado>' . $data["inffin399b_pregrabado"] . '</inffin399b_pregrabado>' . chr(13);
            $xml .= '<inffin399b_actcte>' . $data["inffin399b_actcte"] . '</inffin399b_actcte>' . chr(13);
            $xml .= '<inffin399b_actnocte>' . $data["inffin399b_actnocte"] . '</inffin399b_actnocte>' . chr(13);
            $xml .= '<inffin399b_acttot>' . $data["inffin399b_acttot"] . '</inffin399b_acttot>' . chr(13);
            $xml .= '<inffin399b_pascte>' . $data["inffin399b_pascte"] . '</inffin399b_pascte>' . chr(13);
            $xml .= '<inffin399b_paslar>' . $data["inffin399b_paslar"] . '</inffin399b_paslar>' . chr(13);
            $xml .= '<inffin399b_pastot>' . $data["inffin399b_pastot"] . '</inffin399b_pastot>' . chr(13);
            $xml .= '<inffin399b_patnet>' . $data["inffin399b_patnet"] . '</inffin399b_patnet>' . chr(13);
            $xml .= '<inffin399b_paspat>' . $data["inffin399b_paspat"] . '</inffin399b_paspat>' . chr(13);
            $xml .= '<inffin399b_balsoc>' . $data["inffin399b_balsoc"] . '</inffin399b_balsoc>' . chr(13);
            $xml .= '<inffin399b_ingope>' . $data["inffin399b_ingope"] . '</inffin399b_ingope>' . chr(13);
            $xml .= '<inffin399b_ingnoope>' . $data["inffin399b_ingnoope"] . '</inffin399b_ingnoope>' . chr(13);
            $xml .= '<inffin399b_gasope>' . $data["inffin399b_gasope"] . '</inffin399b_gasope>' . chr(13);
            $xml .= '<inffin399b_gasnoope>' . $data["inffin399b_gasnoope"] . '</inffin399b_gasnoope>' . chr(13);
            $xml .= '<inffin399b_cosven>' . $data["inffin399b_cosven"] . '</inffin399b_cosven>' . chr(13);
            $xml .= '<inffin399b_utiope>' . $data["inffin399b_utiope"] . '</inffin399b_utiope>' . chr(13);
            $xml .= '<inffin399b_utinet>' . $data["inffin399b_utinet"] . '</inffin399b_utinet>' . chr(13);
            $xml .= '<inffin399b_gasint>' . $data["inffin399b_gasint"] . '</inffin399b_gasint>' . chr(13);
            if (isset($data["inffin399b_gasimp"])) {
                $xml .= '<inffin399b_gasimp>' . $data["inffin399b_gasimp"] . '</inffin399b_gasimp>' . chr(13);
            } else {
                $xml .= '<inffin399b_gasimp>0</inffin399b_gasimp>' . chr(13);
            }
            $xml .= '<inffin399b_indliq>' . $data["inffin399b_indliq"] . '</inffin399b_indliq>' . chr(13);
            $xml .= '<inffin399b_nivend>' . $data["inffin399b_nivend"] . '</inffin399b_nivend>' . chr(13);
            $xml .= '<inffin399b_razcob>' . $data["inffin399b_razcob"] . '</inffin399b_razcob>' . chr(13);
            $xml .= '<inffin399b_renpat>' . $data["inffin399b_renpat"] . '</inffin399b_renpat>' . chr(13);
            $xml .= '<inffin399b_renact>' . $data["inffin399b_renact"] . '</inffin399b_renact>' . chr(13);
            if (!isset($data["inffin399b_gruponiif"])) {
                $data["inffin399b_gruponiif"] = '';
            }
            $xml .= '<inffin399b_gruponiif>' . $data["inffin399b_gruponiif"] . '</inffin399b_gruponiif>' . chr(13);

            // Experiencia decreto 1510
            $i = 0;
            if (isset($data["exp1510"])) {
                if (count($data["exp1510"]) > 0) {
                    foreach ($data["exp1510"] as $cnt) {
                        if (trim($cnt["nombrecontratante"]) != '') {
                            $i++;
                            $xml .= '<exp1510>';
                            if (strlen(ltrim(trim($cnt["secuencia"]), "0")) <= 3) {
                                $xml .= '<secuencia>' . sprintf("%03s", $cnt["secuencia"]) . '</secuencia>';
                            } else {
                                $xml .= '<secuencia>' . $cnt["secuencia"] . '</secuencia>';
                            }
                            $xml .= '<clavecontrato>' . $cnt["clavecontrato"] . '</clavecontrato>';
                            $xml .= '<celebradopor>' . $cnt["celebradopor"] . '</celebradopor>';
                            $xml .= '<nombrecontratista><![CDATA[' . (\funcionesGenerales::reemplazarEspeciales($cnt["nombrecontratista"])) . ']]></nombrecontratista>';
                            $xml .= '<nombrecontratante><![CDATA[' . (\funcionesGenerales::reemplazarEspeciales($cnt["nombrecontratante"])) . ']]></nombrecontratante>';
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
                            $txtclas = '';
                            if (isset($cnt["clasif"])) {
                                foreach ($cnt["clasif"] as $c) {
                                    if (trim($c) != '') {
                                        if ($txtclas != '') {
                                            $txtclas .= ',';
                                        }
                                        $txtclas .= trim($c);
                                    }
                                }
                                $xml .= '<clasif>' . $txtclas . '</clasif>';
                            }
                            $xml .= '<generardeclaracion>' . $cnt["generardeclaracion"] . '</generardeclaracion>' . chr(13);
                            $xml .= '</exp1510>';
                        }
                    }
                }
            }

            //      
            $txtclas = '';
            if (isset($data["clasi1510"])) {
                asort($data["clasi1510"]);
                foreach ($data["clasi1510"] as $c) {
                    if (trim($c) != '') {
                        if ($txtclas != '') {
                            $txtclas .= ',';
                        }
                        $txtclas .= trim($c);
                    }
                }
                $xml .= '<cla1510>' . $txtclas . '</cla1510>';
            }

            // Bienes
            $xml .= '<bienes>';
            if (!empty($data["bienes"])) {
                foreach ($data["bienes"] as $bien) {
                    $xml .= '<bien>';
                    $xml .= '<matinmo>' . $bien["matinmo"] . '</matinmo>';
                    $xml .= '<dir>' . $bien["dir"] . '</dir>';
                    $xml .= '<barrio>' . $bien["barrio"] . '</barrio>';
                    $xml .= '<muni>' . $bien["muni"] . '</muni>';
                    $xml .= '<dpto>' . $bien["muni"] . '</dpto>';
                    $xml .= '<pais>' . $bien["pais"] . '</pais>';
                    $xml .= '</bien>';
                }
            }
            $xml .= '</bienes>';

            foreach ($data["contratos"] as $cnt) {

                if (!isset($cnt["isn"])) {
                    $cnt["isn"] = '';
                }
                if (!isset($cnt["codcamara"])) {
                    $cnt["codcamara"] = '';
                }
                if (!isset($cnt["numradic"])) {
                    $cnt["numradic"] = '';
                }
                if (!isset($cnt["fecradic"])) {
                    $cnt["fecradic"] = '';
                }
                if (!isset($cnt["horradic"])) {
                    $cnt["horradic"] = '';
                }
                if (!isset($cnt["fecreporte"])) {
                    $cnt["fecreporte"] = '';
                }
                if (!isset($cnt["nitentidad"])) {
                    $cnt["nitentidad"] = '';
                }
                if (!isset($cnt["nombreentidad"])) {
                    $cnt["nombreentidad"] = '';
                }
                if (!isset($cnt["idmunientidad"])) {
                    $cnt["idmunientidad"] = '';
                }
                if (!isset($cnt["divarea"])) {
                    $cnt["divarea"] = '';
                }
                if (!isset($cnt["idefun"])) {
                    $cnt["idefun"] = '';
                }
                if (!isset($cnt["nomfun"])) {
                    $cnt["nomfun"] = '';
                }
                if (!isset($cnt["carfun"])) {
                    $cnt["carfun"] = '';
                }
                if (!isset($cnt["numcontrato"])) {
                    $cnt["numcontrato"] = '';
                }
                if (!isset($cnt["numcontratosecop"])) {
                    $cnt["numcontratosecop"] = '';
                }

                if (!isset($cnt["fechaadj"])) {
                    $cnt["fechaadj"] = '';
                }
                if (!isset($cnt["fechaper"])) {
                    $cnt["fechaper"] = '';
                }
                if (!isset($cnt["fechaini"])) {
                    $cnt["fechaini"] = '';
                }
                if (!isset($cnt["fechater"])) {
                    $cnt["fechater"] = '';
                }
                if (!isset($cnt["fechaeje"])) {
                    $cnt["fechaeje"] = '';
                }
                if (!isset($cnt["fechasus"])) {
                    $cnt["fechasus"] = '';
                }
                if (!isset($cnt["fechaliq"])) {
                    $cnt["fechaliq"] = '';
                }
                if (!isset($cnt["fechaterant"])) {
                    $cnt["fechaterant"] = '';
                }
                if (!isset($cnt["fechaces"])) {
                    $cnt["fechaces"] = '';
                }

                if (!isset($cnt["codigoact"])) {
                    $cnt["codigoact"] = '';
                }
                if (!isset($cnt["ciiu1"])) {
                    $cnt["ciiu1"] = '';
                }
                if (!isset($cnt["ciiu2"])) {
                    $cnt["ciiu2"] = '';
                }
                if (!isset($cnt["ciiu3"])) {
                    $cnt["ciiu3"] = '';
                }
                if (!isset($cnt["ciiu4"])) {
                    $cnt["ciiu4"] = '';
                }
                if (!isset($cnt["objeto"])) {
                    $cnt["objeto"] = '';
                }
                if (!isset($cnt["tipocont"])) {
                    $cnt["tipocont"] = '';
                }
                if (!isset($cnt["valorcont"])) {
                    $cnt["valorcont"] = '';
                }
                if (!isset($cnt["valorcontpag"])) {
                    $cnt["valorcontpag"] = '';
                }
                if (!isset($cnt["indcump"])) {
                    $cnt["indcump"] = '';
                }
                if (!isset($cnt["estadocont"])) {
                    $cnt["estadocont"] = '';
                }
                if (!isset($cnt["motivoter"])) {
                    $cnt["motivoter"] = '';
                }
                if (!isset($cnt["motivoces"])) {
                    $cnt["motivoces"] = '';
                }
                if (!isset($cnt["observaciones"])) {
                    $cnt["observaciones"] = '';
                }
                if (!isset($cnt["numlibro"])) {
                    $cnt["numlibro"] = '';
                }
                if (!isset($cnt["numreglib"])) {
                    $cnt["numreglib"] = '';
                }
                if (!isset($cnt["fecreglib"])) {
                    $cnt["fecreglib"] = '';
                }
                if (!isset($cnt["codigocamaraorigen"])) {
                    $cnt["codigocamaraorigen"] = '';
                }
                if (!isset($cnt["numregistrocamaraorigen"])) {
                    $cnt["numregistrocamaraorigen"] = '';
                }
                if (!isset($cnt["fecregistrocamaraorigen"])) {
                    $cnt["fecregistrocamaraorigen"] = '';
                }

                $xml .= '<contrato>' . chr(13);
                $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
                $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
                $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
                $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
                $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
                $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
                $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
                $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
                $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
                $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
                $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
                $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
                $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
                $xml .= '<numcontrato>' . $cnt["numcontrato"] . '</numcontrato>' . chr(13);
                $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
                $xml .= '<fechaadj>' . $cnt["fechaadj"] . '</fechaadj>' . chr(13);
                $xml .= '<fechaper>' . $cnt["fechaper"] . '</fechaper>' . chr(13);
                $xml .= '<fechaini>' . $cnt["fechaini"] . '</fechaini>' . chr(13);
                $xml .= '<fechater>' . $cnt["fechater"] . '</fechater>' . chr(13);
                $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
                $xml .= '<fechaliq>' . $cnt["fechaliq"] . '</fechaliq>' . chr(13);
                $xml .= '<fechaterant>' . $cnt["fechaterant"] . '</fechaterant>' . chr(13);
                $xml .= '<fechaces>' . $cnt["fechaces"] . '</fechaces>' . chr(13);
                $xml .= '<codigoact>' . $cnt["codigoact"] . '</codigoact>' . chr(13);
                $xml .= '<ciiu1>' . $cnt["ciiu1"] . '</ciiu1>' . chr(13);
                $xml .= '<ciiu2>' . $cnt["ciiu2"] . '</ciiu2>' . chr(13);
                $xml .= '<ciiu3>' . $cnt["ciiu3"] . '</ciiu3>' . chr(13);
                $xml .= '<ciiu4>' . $cnt["ciiu4"] . '</ciiu4>' . chr(13);
                $xml .= '<tipocont>' . $cnt["tipocont"] . '</tipocont>' . chr(13);
                $xml .= '<valorcont>' . $cnt["valorcont"] . '</valorcont>' . chr(13);
                $xml .= '<valorcontpag>' . $cnt["valorcontpag"] . '</valorcontpag>' . chr(13);
                $xml .= '<indcump>' . $cnt["indcump"] . '</indcump>' . chr(13);
                $xml .= '<estadocont>' . $cnt["estadocont"] . '</estadocont>' . chr(13);
                $xml .= '<motivoter>' . $cnt["motivoter"] . '</motivoter>' . chr(13);
                $xml .= '<motivoces>' . $cnt["motivoces"] . '</motivoces>' . chr(13);
                $xml .= '<observaciones>' . $cnt["observaciones"] . '</observaciones>' . chr(13);
                $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
                $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
                $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
                if (isset($cnt["clasificaciones"])) {
                    if (!empty($cnt["clasificaciones"])) {
                        foreach ($cnt["clasificaciones"] as $clasicon) {
                            $xml .= '<clasicon>' . $clasicon . '</clasicon>' . chr(13);
                        }
                    }
                }
                if (isset($cnt["unspsc"])) {
                    if (!empty($cnt["unspsc"])) {
                        foreach ($cnt["unspsc"] as $clasicon) {
                            $xml .= '<unspsc>' . $clasicon . '</unspsc>' . chr(13);
                        }
                    }
                }
                $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
                $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
                $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
                $xml .= '</contrato>' . chr(13);
            }

            foreach ($data["multas"] as $cnt) {

                if (!isset($cnt["isn"])) {
                    $cnt["isn"] = '';
                }
                if (!isset($cnt["codcamara"])) {
                    $cnt["codcamara"] = '';
                }
                if (!isset($cnt["numradic"])) {
                    $cnt["numradic"] = '';
                }
                if (!isset($cnt["fecradic"])) {
                    $cnt["fecradic"] = '';
                }
                if (!isset($cnt["horradic"])) {
                    $cnt["horradic"] = '';
                }
                if (!isset($cnt["fecreporte"])) {
                    $cnt["fecreporte"] = '';
                }
                if (!isset($cnt["nitentidad"])) {
                    $cnt["nitentidad"] = '';
                }
                if (!isset($cnt["nombreentidad"])) {
                    $cnt["nombreentidad"] = '';
                }
                if (!isset($cnt["idmunientidad"])) {
                    $cnt["idmunientidad"] = '';
                }
                if (!isset($cnt["divarea"])) {
                    $cnt["divarea"] = '';
                }
                if (!isset($cnt["idefun"])) {
                    $cnt["idefun"] = '';
                }
                if (!isset($cnt["nomfun"])) {
                    $cnt["nomfun"] = '';
                }
                if (!isset($cnt["carfun"])) {
                    $cnt["carfun"] = '';
                }
                if (!isset($cnt["numcontrato"])) {
                    $cnt["numcontrato"] = '';
                }
                if (!isset($cnt["numcontratosecop"])) {
                    $cnt["numcontratosecop"] = '';
                }
                if (!isset($cnt["numacto"])) {
                    $cnt["numacto"] = '';
                }
                if (!isset($cnt["fechaacto"])) {
                    $cnt["fechaacto"] = '';
                }
                if (!isset($cnt["fechaeje"])) {
                    $cnt["fechaeje"] = '';
                }
                if (!isset($cnt["valormult"])) {
                    $cnt["valormult"] = '';
                }
                if (!isset($cnt["valormultpag"])) {
                    $cnt["valormultpag"] = '';
                }
                if (!isset($cnt["numsus"])) {
                    $cnt["numsus"] = '';
                }
                if (!isset($cnt["fechasus"])) {
                    $cnt["fechasus"] = '';
                }
                if (!isset($cnt["numconf"])) {
                    $cnt["numconf"] = '';
                }
                if (!isset($cnt["fechaconf"])) {
                    $cnt["fechaconf"] = '';
                }
                if (!isset($cnt["estadomult"])) {
                    $cnt["estadomult"] = '';
                }
                if (!isset($cnt["numrev"])) {
                    $cnt["numrev"] = '';
                }
                if (!isset($cnt["fechanumrev"])) {
                    $cnt["fechanumrev"] = '';
                }
                if (!isset($cnt["numlibro"])) {
                    $cnt["numlibro"] = '';
                }
                if (!isset($cnt["numreglib"])) {
                    $cnt["numreglib"] = '';
                }
                if (!isset($cnt["fecreglib"])) {
                    $cnt["fecreglib"] = '';
                }
                if (!isset($cnt["codigocamaraorigen"])) {
                    $cnt["codigocamaraorigen"] = '';
                }
                if (!isset($cnt["numregistrocamaraorigen"])) {
                    $cnt["numregistrocamaraorigen"] = '';
                }
                if (!isset($cnt["fecregistrocamaraorigen"])) {
                    $cnt["fecregistrocamaraorigen"] = '';
                }


                $xml .= '<multa>' . chr(13);
                $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
                $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
                $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
                $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
                $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
                $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
                $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
                $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
                $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
                $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
                $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
                $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
                $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
                $xml .= '<numcontrato>' . $cnt["numcontrato"] . '</numcontrato>' . chr(13);
                $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
                $xml .= '<numacto>' . $cnt["numacto"] . '</numacto>' . chr(13);
                $xml .= '<fechaacto>' . $cnt["fechaacto"] . '</fechaacto>' . chr(13);
                $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
                $xml .= '<valormult>' . $cnt["valormult"] . '</valormult>' . chr(13);
                $xml .= '<valormultpag>' . $cnt["valormultpag"] . '</valormultpag>' . chr(13);
                $xml .= '<numsus>' . $cnt["numsus"] . '</numsus>' . chr(13);
                $xml .= '<fechasus>' . $cnt["fechasus"] . '</fechasus>' . chr(13);
                $xml .= '<numconf>' . $cnt["numconf"] . '</numconf>' . chr(13);
                $xml .= '<fechaconf>' . $cnt["fechaconf"] . '</fechaconf>' . chr(13);
                $xml .= '<estadomult>' . $cnt["estadomult"] . '</estadomult>' . chr(13);
                $xml .= '<numrev>' . $cnt["numrev"] . '</numrev>' . chr(13);
                $xml .= '<fechanumrev>' . $cnt["fechanumrev"] . '</fechanumrev>' . chr(13);
                $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
                $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
                $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
                $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
                $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
                $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
                $xml .= '</multa>' . chr(13);
            }

            foreach ($data["sanciones"] as $cnt) {
                if (!isset($cnt["isn"])) {
                    $cnt["isn"] = '';
                }
                if (!isset($cnt["codcamara"])) {
                    $cnt["codcamara"] = '';
                }
                if (!isset($cnt["numradic"])) {
                    $cnt["numradic"] = '';
                }
                if (!isset($cnt["fecradic"])) {
                    $cnt["fecradic"] = '';
                }
                if (!isset($cnt["horradic"])) {
                    $cnt["horradic"] = '';
                }
                if (!isset($cnt["fecreporte"])) {
                    $cnt["fecreporte"] = '';
                }
                if (!isset($cnt["nitentidad"])) {
                    $cnt["nitentidad"] = '';
                }
                if (!isset($cnt["nombreentidad"])) {
                    $cnt["nombreentidad"] = '';
                }
                if (!isset($cnt["idmunientidad"])) {
                    $cnt["idmunientidad"] = '';
                }
                if (!isset($cnt["divarea"])) {
                    $cnt["divarea"] = '';
                }
                if (!isset($cnt["idefun"])) {
                    $cnt["idefun"] = '';
                }
                if (!isset($cnt["nomfun"])) {
                    $cnt["nomfun"] = '';
                }
                if (!isset($cnt["carfun"])) {
                    $cnt["carfun"] = '';
                }
                if (!isset($cnt["numcontrato"])) {
                    $cnt["numcontrato"] = '';
                }
                if (!isset($cnt["numcontratosecop"])) {
                    $cnt["numcontratosecop"] = '';
                }
                if (!isset($cnt["numacto"])) {
                    $cnt["numacto"] = '';
                }
                if (!isset($cnt["fechaacto"])) {
                    $cnt["fechaacto"] = '';
                }
                if (!isset($cnt["fechaeje"])) {
                    $cnt["fechaeje"] = '';
                }
                if (!isset($cnt["numsus"])) {
                    $cnt["numsus"] = '';
                }
                if (!isset($cnt["fechasus"])) {
                    $cnt["fechasus"] = '';
                }
                if (!isset($cnt["numconf"])) {
                    $cnt["numconf"] = '';
                }
                if (!isset($cnt["fechaconf"])) {
                    $cnt["fechaconf"] = '';
                }
                if (!isset($cnt["estadosanc"])) {
                    $cnt["estadosanc"] = '';
                }
                if (!isset($cnt["condinc"])) {
                    $cnt["condinc"] = '';
                }
                if (!isset($cnt["cumsanc"])) {
                    $cnt["cumsanc"] = '';
                }
                if (!isset($cnt["numrev"])) {
                    $cnt["numrev"] = '';
                }
                if (!isset($cnt["fechanumrev"])) {
                    $cnt["fechanumrev"] = '';
                }
                if (!isset($cnt["descripcion"])) {
                    $cnt["descripcion"] = '';
                }
                if (!isset($cnt["fundamento"])) {
                    $cnt["fundamento"] = '';
                }
                if (!isset($cnt["vigencia"])) {
                    $cnt["vigencia"] = '';
                }
                if (!isset($cnt["numlibro"])) {
                    $cnt["numlibro"] = '';
                }
                if (!isset($cnt["numreglib"])) {
                    $cnt["numreglib"] = '';
                }
                if (!isset($cnt["fecreglib"])) {
                    $cnt["fecreglib"] = '';
                }
                if (!isset($cnt["codigocamaraorigen"])) {
                    $cnt["codigocamaraorigen"] = '';
                }
                if (!isset($cnt["numregistrocamaraorigen"])) {
                    $cnt["numregistrocamaraorigen"] = '';
                }
                if (!isset($cnt["fecregistrocamaraorigen"])) {
                    $cnt["fecregistrocamaraorigen"] = '';
                }


                $xml .= '<sancion>' . chr(13);
                $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
                $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
                $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
                $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
                $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
                $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
                $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
                $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
                $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
                $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
                $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
                $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
                $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
                $xml .= '<numcontrato>' . $cnt["numcontrato"] . '</numcontrato>' . chr(13);
                $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
                $xml .= '<numacto>' . $cnt["numacto"] . '</numacto>' . chr(13);
                $xml .= '<fechaacto>' . $cnt["fechaacto"] . '</fechaacto>' . chr(13);
                $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
                $xml .= '<numsus>' . $cnt["numsus"] . '</numsus>' . chr(13);
                $xml .= '<fechasus>' . $cnt["fechasus"] . '</fechasus>' . chr(13);
                $xml .= '<numconf>' . $cnt["numconf"] . '</numconf>' . chr(13);
                $xml .= '<fechaconf>' . $cnt["fechaconf"] . '</fechaconf>' . chr(13);
                $xml .= '<estadosanc>' . $cnt["estadosanc"] . '</estadosanc>' . chr(13);
                $xml .= '<condinc>' . $cnt["condinc"] . '</condinc>' . chr(13);
                $xml .= '<cumsanc>' . $cnt["cumsanc"] . '</cumsanc>' . chr(13);
                $xml .= '<numrev>' . $cnt["numrev"] . '</numrev>' . chr(13);
                $xml .= '<dessanc><![CDATA[' . $cnt["descripcion"] . ']]></dessanc>' . chr(13);
                $xml .= '<fundamento>' . $cnt["fundamento"] . '</fundamento>' . chr(13);
                $xml .= '<vigencia>' . $cnt["vigencia"] . '</vigencia>' . chr(13);
                $xml .= '<fechanumrev>' . $cnt["fechanumrev"] . '</fechanumrev>' . chr(13);
                $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
                $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
                $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
                $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
                $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
                $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
                $xml .= '</sancion>' . chr(13);
            }

            foreach ($data["sandis"] as $cnt) {
                if (!isset($cnt["isn"])) {
                    $cnt["isn"] = '';
                }
                if (!isset($cnt["codcamara"])) {
                    $cnt["codcamara"] = '';
                }
                if (!isset($cnt["numradic"])) {
                    $cnt["numradic"] = '';
                }
                if (!isset($cnt["fecradic"])) {
                    $cnt["fecradic"] = '';
                }
                if (!isset($cnt["horradic"])) {
                    $cnt["horradic"] = '';
                }
                if (!isset($cnt["fecreporte"])) {
                    $cnt["fecreporte"] = '';
                }
                if (!isset($cnt["nitentidad"])) {
                    $cnt["nitentidad"] = '';
                }
                if (!isset($cnt["nombreentidad"])) {
                    $cnt["nombreentidad"] = '';
                }
                if (!isset($cnt["idmunientidad"])) {
                    $cnt["idmunientidad"] = '';
                }
                if (!isset($cnt["divarea"])) {
                    $cnt["divarea"] = '';
                }
                if (!isset($cnt["idefun"])) {
                    $cnt["idefun"] = '';
                }
                if (!isset($cnt["nomfun"])) {
                    $cnt["nomfun"] = '';
                }
                if (!isset($cnt["carfun"])) {
                    $cnt["carfun"] = '';
                }
                if (!isset($cnt["numcontratosecop"])) {
                    $cnt["numcontratosecop"] = '';
                }
                if (!isset($cnt["numacto"])) {
                    $cnt["numacto"] = '';
                }
                if (!isset($cnt["fechaacto"])) {
                    $cnt["fechaacto"] = '';
                }
                if (!isset($cnt["fechaeje"])) {
                    $cnt["fechaeje"] = '';
                }
                if (!isset($cnt["numsus"])) {
                    $cnt["numsus"] = '';
                }
                if (!isset($cnt["fechasus"])) {
                    $cnt["fechasus"] = '';
                }
                if (!isset($cnt["numconf"])) {
                    $cnt["numconf"] = '';
                }
                if (!isset($cnt["fechaconf"])) {
                    $cnt["fechaconf"] = '';
                }
                if (!isset($cnt["estadosanc"])) {
                    $cnt["estadosanc"] = '';
                }
                if (!isset($cnt["numrev"])) {
                    $cnt["numrev"] = '';
                }
                if (!isset($cnt["fechanumrev"])) {
                    $cnt["fechanumrev"] = '';
                }
                if (!isset($cnt["descripcion"])) {
                    $cnt["descripcion"] = '';
                }
                if (!isset($cnt["fundamento"])) {
                    $cnt["fundamento"] = '';
                }
                if (!isset($cnt["vigencia"])) {
                    $cnt["vigencia"] = '';
                }
                if (!isset($cnt["numlibro"])) {
                    $cnt["numlibro"] = '';
                }
                if (!isset($cnt["numreglib"])) {
                    $cnt["numreglib"] = '';
                }
                if (!isset($cnt["fecreglib"])) {
                    $cnt["fecreglib"] = '';
                }
                if (!isset($cnt["codigocamaraorigen"])) {
                    $cnt["codigocamaraorigen"] = '';
                }
                if (!isset($cnt["numregistrocamaraorigen"])) {
                    $cnt["numregistrocamaraorigen"] = '';
                }
                if (!isset($cnt["fecregistrocamaraorigen"])) {
                    $cnt["fecregistrocamaraorigen"] = '';
                }

                $xml .= '<sandis>' . chr(13);
                $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
                $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
                $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
                $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
                $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
                $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
                $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
                $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
                $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
                $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
                $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
                $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
                $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
                $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
                $xml .= '<numacto>' . $cnt["numacto"] . '</numacto>' . chr(13);
                $xml .= '<fechaacto>' . $cnt["fechaacto"] . '</fechaacto>' . chr(13);
                $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
                $xml .= '<numsus>' . $cnt["numsus"] . '</numsus>' . chr(13);
                $xml .= '<fechasus>' . $cnt["fechasus"] . '</fechasus>' . chr(13);
                $xml .= '<numconf>' . $cnt["numconf"] . '</numconf>' . chr(13);
                $xml .= '<fechaconf>' . $cnt["fechaconf"] . '</fechaconf>' . chr(13);
                $xml .= '<estadosanc>' . $cnt["estadosanc"] . '</estadosanc>' . chr(13);
                $xml .= '<numrev>' . $cnt["numrev"] . '</numrev>' . chr(13);
                $xml .= '<dessanc><![CDATA[' . $cnt["descripcion"] . ']]></dessanc>' . chr(13);
                $xml .= '<fundamento>' . $cnt["fundamento"] . '</fundamento>' . chr(13);
                $xml .= '<vigencia>' . $cnt["vigencia"] . '</vigencia>' . chr(13);
                $xml .= '<fechanumrev>' . $cnt["fechanumrev"] . '</fechanumrev>' . chr(13);
                $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
                $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
                $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
                $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
                $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
                $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
                $xml .= '</sancion>' . chr(13);
            }

            $xml .= '</expediente>' . chr(13);
            $xml .= '</expedientes>' . chr(13);
            return $xml;
        }

//
        if (!isset($_SESSION["formulario"]["tipotramite"])) {
            $_SESSION["formulario"]["tipotramite"] = '';
        }
        if (!isset($_SESSION["formulario"]["numerorecuperacion"])) {
            $_SESSION["formulario"]["numerorecuperacion"] = '';
        }
        if (!isset($_SESSION["formulario"]["liquidacion"])) {
            $_SESSION["formulario"]["liquidacion"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["tamanoempresa"])) {
            $_SESSION["formulario"]["datos"]["tamanoempresa"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["emprendedor28"])) {
            $_SESSION["formulario"]["datos"]["emprendedor28"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["pemprendedor28"])) {
            $_SESSION["formulario"]["datos"]["pemprendedor28"] = 0;
        }
        if (!isset($_SESSION["formulario"]["datos"]["vigcontrol"])) {
            $_SESSION["formulario"]["datos"]["vigcontrol"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["fechatermestudios"])) {
            $_SESSION["formulario"]["datos"]["fechatermestudios"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["celcom"])) {
            $_SESSION["formulario"]["datos"]["celcom"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["celnot"])) {
            $_SESSION["formulario"]["datos"]["celnot"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["idtipoidentificacion"])) {
            $_SESSION["formulario"]["datos"]["idtipoidentificacion"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["certificardesde"])) {
            $_SESSION["formulario"]["datos"]["certificardesde"] = '';
        }

//
        if (!isset($_SESSION["formulario"]["cambidom_idmunicipioorigen"])) {
            $_SESSION["formulario"]["cambidom_idmunicipioorigen"] = '';
        }
        if (!isset($_SESSION["formulario"]["cambidom_idmunicipiodestino"])) {
            $_SESSION["formulario"]["cambidom_idmunicipiodestino"] = '';
        }
        if (!isset($_SESSION["formulario"]["cambidom_fechaultimainscripcion"])) {
            $_SESSION["formulario"]["cambidom_fechaultimainscripcion"] = '';
        }
        if (!isset($_SESSION["formulario"]["cambidom_fechaultimarenovacion"])) {
            $_SESSION["formulario"]["cambidom_fechaultimarenovacion"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["sitcontrol"])) {
            $_SESSION["formulario"]["datos"]["sitcontrol"] = array();
        }

        // ******************************************************************************* //
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_fechacorte"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_fechacorte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_actcte"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_actcte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_actnocte"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_actnocte"] = '';
        }

        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_fijnet"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_fijnet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_actotr"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_actotr"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_actval"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_actval"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_acttot"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_acttot"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_pascte"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_pascte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_paslar"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_paslar"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_pastot"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_pastot"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_patnet"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_patnet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_paspat"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_paspat"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_ingope"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_ingope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_ingnoope"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_ingnoope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_gasope"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_gasope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_gasnoope"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_gasnoope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_cosven"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_cosven"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_utinet"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_utinet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_utiope"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_utiope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_gasint"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_gasint"] = '';
        }

//
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_indliq"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_indliq"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_nivend"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_nivend"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_razcob"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_razcob"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_renpat"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_renpat"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_renact"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_renact"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_gruponiif"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_gruponiif"] = '';
        }


        // ******************************************************************************* //
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_fechacorte"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_fechacorte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_pregrabado"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_pregrabado"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_actcte"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_actcte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_actnocte"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_actnocte"] = '';
        }

        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_acttot"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_acttot"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_pascte"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_pascte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_paslar"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_paslar"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_pastot"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_pastot"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_patnet"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_patnet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_paspat"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_paspat"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_ingope"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_ingope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_ingnoope"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_ingnoope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_gasope"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_gasope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_gasnoope"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_gasnoope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_cosven"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_cosven"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_utinet"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_utinet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_utiope"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_utiope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_gasint"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_gasint"] = '';
        }

//
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_indliq"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_indliq"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_nivend"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_nivend"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_razcob"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_razcob"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_renpat"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_renpat"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_renact"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_renact"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_gruponiif"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_gruponiif"] = '';
        }

        // ******************************************************************************* //
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_fechacorte"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_fechacorte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_pregrabado"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_pregrabado"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_actcte"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_actcte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399ba_actnocte"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_actnocte"] = '';
        }

        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_acttot"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_acttot"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_pascte"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_pascte"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_paslar"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_paslar"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_pastot"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_pastot"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_patnet"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_patnet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_paspat"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_paspat"] = '';
        }

        //
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_ingope"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_ingope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_ingnoope"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_ingnoope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_gasope"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_gasope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_gasnoope"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_gasnoope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_cosven"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_cosven"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_utinet"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_utinet"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_utiope"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_utiope"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_gasint"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_gasint"] = '';
        }

//
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_indliq"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_indliq"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_nivend"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_nivend"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_razcob"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_razcob"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399ba_renpat"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_renpat"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_renact"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_renact"] = '';
        }
        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_gruponiif"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_gruponiif"] = '';
        }

//
        if (!isset($_SESSION["formulario"]["datos"]["clasi1510"])) {
            $_SESSION["formulario"]["datos"]["clasi1510"] = array();
        }

//
        if (!isset($_SESSION["formulario"]["datos"]["exp1510"])) {
            $_SESSION["formulario"]["datos"]["exp1510"] = array();
        }

//
        $xml .= '<tipotramite>' . trim($_SESSION["formulario"]["tipotramite"]) . '</tipotramite>' . chr(13);
        $xml .= '<numerorecuperacion>' . trim($_SESSION["formulario"]["numerorecuperacion"]) . '</numerorecuperacion>' . chr(13);
        $xml .= '<numeroliquidacion>' . trim($_SESSION["formulario"]["liquidacion"]) . '</numeroliquidacion>' . chr(13);
        $xml .= '<numeroradicacion>' . trim($_SESSION["formulario"]["numeroradicacion"]) . '</numeroradicacion>' . chr(13);
        $xml .= '<proponente>' . ltrim($_SESSION["formulario"]["datos"]["proponente"], '0') . '</proponente>' . chr(13);
        $xml .= '<matricula>' . ltrim($_SESSION["formulario"]["datos"]["matricula"], '0') . '</matricula>' . chr(13);
        $xml .= '<nombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["nombre"])) . ']]></nombre>' . chr(13);
        $xml .= '<ape1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["ape1"])) . ']]></ape1>' . chr(13);
        $xml .= '<ape2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["ape2"])) . ']]></ape2>' . chr(13);
        $xml .= '<nom1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["nom1"])) . ']]></nom1>' . chr(13);
        $xml .= '<nom2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["nom2"])) . ']]></nom2>' . chr(13);
        $xml .= '<sigla><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["sigla"])) . ']]></sigla>' . chr(13);
        $xml .= '<idtipoidentificacion>' . $_SESSION["formulario"]["datos"]["idtipoidentificacion"] . '</idtipoidentificacion>' . chr(13);
        $xml .= '<identificacion>' . ltrim($_SESSION["formulario"]["datos"]["identificacion"], '0') . '</identificacion>' . chr(13);
        $xml .= '<idpaisidentificacion>' . $_SESSION["formulario"]["datos"]["idpaisidentificacion"] . '</idpaisidentificacion>' . chr(13);
        $xml .= '<nit>' . ltrim($_SESSION["formulario"]["datos"]["nit"], '0') . '</nit>' . chr(13);
        $xml .= '<nacionalidad><![CDATA[' . trim($_SESSION["formulario"]["datos"]["nacionalidad"]) . ']]></nacionalidad>' . chr(13);
        $xml .= '<organizacion>' . $_SESSION["formulario"]["datos"]["organizacion"] . '</organizacion>' . chr(13);
        $xml .= '<tamanoempresa>' . $_SESSION["formulario"]["datos"]["tamanoempresa"] . '</tamanoempresa>' . chr(13);
        $xml .= '<emprendedor28>' . $_SESSION["formulario"]["datos"]["emprendedor28"] . '</emprendedor28>' . chr(13);
        $xml .= '<pemprendedor28>' . $_SESSION["formulario"]["datos"]["pemprendedor28"] . '</pemprendedor28>' . chr(13);
        $xml .= '<vigcontrol>' . $_SESSION["formulario"]["datos"]["vigcontrol"] . '</vigcontrol>' . chr(13);
        $xml .= '<idestadoproponente>' . $_SESSION["formulario"]["datos"]["idestadoproponente"] . '</idestadoproponente>' . chr(13);
        $xml .= '<certificardesde>' . $_SESSION["formulario"]["datos"]["certificardesde"] . '</certificardesde>' . chr(13);
        $xml .= '<fechaultimainscripcion>' . $_SESSION["formulario"]["datos"]["fechaultimainscripcion"] . '</fechaultimainscripcion>' . chr(13);
        $xml .= '<fechaultimarenovacion>' . $_SESSION["formulario"]["datos"]["fechaultimarenovacion"] . '</fechaultimarenovacion>' . chr(13);
        $xml .= '<fechacancelacion>' . $_SESSION["formulario"]["datos"]["fechacancelacion"] . '</fechacancelacion>' . chr(13);

        // Cambio de domicilio
        $xml .= '<cambidom_idmunicipioorigen>' . $_SESSION["formulario"]["datos"]["cambidom_idmunicipioorigen"] . '</cambidom_idmunicipioorigen>' . chr(13);
        $xml .= '<cambidom_idmunicipiodestino>' . $_SESSION["formulario"]["datos"]["cambidom_idmunicipiodestino"] . '</cambidom_idmunicipiodestino>' . chr(13);
        $xml .= '<cambidom_fechaultimainscripcion>' . $_SESSION["formulario"]["datos"]["cambidom_fechaultimainscripcion"] . '</cambidom_fechaultimainscripcion>' . chr(13);
        $xml .= '<cambidom_fechaultimarenovacion>' . $_SESSION["formulario"]["datos"]["cambidom_fechaultimarenovacion"] . '</cambidom_fechaultimarenovacion>' . chr(13);
        if (isset($_SESSION["formulario"]["datos"]["propcamaraorigen"])) {
            $xml .= '<propcamaraorigen>' . $_SESSION["formulario"]["datos"]["propcamaraorigen"] . '</propcamaraorigen>' . chr(13);
        } else {
            $xml .= '<propcamaraorigen></propcamaraorigen>' . chr(13);
        }
        if (isset($_SESSION["formulario"]["datos"]["propcamaradestino"])) {
            $xml .= '<propcamaradestino>' . $_SESSION["formulario"]["datos"]["propcamaradestino"] . '</propcamaradestino>' . chr(13);
        } else {
            $xml .= '<propcamaradestino></propcamaradestino>' . chr(13);
        }

        // Datos de personeria juridica
        $xml .= '<idtipodocperjur>' . $_SESSION["formulario"]["datos"]["idtipodocperjur"] . '</idtipodocperjur>' . chr(13); // A02
        $xml .= '<numdocperjur>' . $_SESSION["formulario"]["datos"]["numdocperjur"] . '</numdocperjur>' . chr(13); // A20
        $xml .= '<fecdocperjur>' . $_SESSION["formulario"]["datos"]["fecdocperjur"] . '</fecdocperjur>' . chr(13); // (D)
        $xml .= '<origendocperjur><![CDATA[' . $_SESSION["formulario"]["datos"]["origendocperjur"] . ']]></origendocperjur>' . chr(13); // A128
        $xml .= '<fechaconstitucion>' . $_SESSION["formulario"]["datos"]["fechaconstitucion"] . '</fechaconstitucion>' . chr(13); // (D)
        $xml .= '<fechavencimiento>' . $_SESSION["formulario"]["datos"]["fechavencimiento"] . '</fechavencimiento>' . chr(13); // (D)
        // Datos de ubicacion
        $xml .= '<dircom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($_SESSION["formulario"]["datos"]["dircom"]) . ']]></dircom>' . chr(13); // A128
        $xml .= '<dircom_tipovia></dircom_tipovia>' . chr(13); //A5
        $xml .= '<dircom_numvia></dircom_numvia>' . chr(13); //A3
        $xml .= '<dircom_apevia></dircom_apevia>' . chr(13); //A5
        $xml .= '<dircom_orivia></dircom_orivia>' . chr(13); //A5
        $xml .= '<dircom_numcruce></dircom_numcruce>' . chr(13); //A3
        $xml .= '<dircom_apecruce></dircom_apecruce>' . chr(13); //A5
        $xml .= '<dircom_oricruce></dircom_oricruce>' . chr(13); //A5
        $xml .= '<dircom_numplaca></dircom_numplaca>' . chr(13); //A3
        $xml .= '<dircom_complemento><![CDATA[]]></dircom_complemento>' . chr(13); //A20
        $xml .= '<muncom>' . $_SESSION["formulario"]["datos"]["muncom"] . '</muncom>' . chr(13);
        $xml .= '<telcom1>' . $_SESSION["formulario"]["datos"]["telcom1"] . '</telcom1>' . chr(13);
        $xml .= '<telcom2>' . $_SESSION["formulario"]["datos"]["telcom2"] . '</telcom2>' . chr(13);
        $xml .= '<celcom>' . $_SESSION["formulario"]["datos"]["celcom"] . '</celcom>' . chr(13);
        $xml .= '<faxcom>' . $_SESSION["formulario"]["datos"]["faxcom"] . '</faxcom>' . chr(13);
        $xml .= '<emailcom><![CDATA[' . $_SESSION["formulario"]["datos"]["emailcom"] . ']]></emailcom>' . chr(13);
        $xml .= '<enviarint>' . $_SESSION["formulario"]["datos"]["enviarint"] . '</enviarint>' . chr(13);
        $xml .= '<dirnot><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($_SESSION["formulario"]["datos"]["dirnot"]) . ']]></dirnot>' . chr(13);
        $xml .= '<dirnot_tipovia></dirnot_tipovia>' . chr(13);
        $xml .= '<dirnot_numvia></dirnot_numvia>' . chr(13);
        $xml .= '<dirnot_apevia></dirnot_apevia>' . chr(13);
        $xml .= '<dirnot_orivia></dirnot_orivia>' . chr(13);
        $xml .= '<dirnot_numcruce></dirnot_numcruce>' . chr(13);
        $xml .= '<dirnot_apecruce></dirnot_apecruce>' . chr(13);
        $xml .= '<dirnot_oricruce></dirnot_oricruce>' . chr(13);
        $xml .= '<dirnot_numplaca></dirnot_numplaca>' . chr(13);
        $xml .= '<dirnot_complemento><![CDATA[]]></dirnot_complemento>' . chr(13);
        $xml .= '<munnot>' . $_SESSION["formulario"]["datos"]["munnot"] . '</munnot>' . chr(13);
        $xml .= '<telnot>' . $_SESSION["formulario"]["datos"]["telnot"] . '</telnot>' . chr(13);
        $xml .= '<telnot2>' . $_SESSION["formulario"]["datos"]["telnot2"] . '</telnot2>' . chr(13);
        $xml .= '<celnot>' . $_SESSION["formulario"]["datos"]["celnot"] . '</celnot>' . chr(13);
        $xml .= '<faxnot>' . $_SESSION["formulario"]["datos"]["faxnot"] . '</faxnot>' . chr(13);
        $xml .= '<emailnot><![CDATA[' . $_SESSION["formulario"]["datos"]["emailnot"] . ']]></emailnot>' . chr(13);
        $xml .= '<enviarnot>' . $_SESSION["formulario"]["datos"]["enviarnot"] . '</enviarnot>' . chr(13);

        // Representantes legales
        $xml .= '<representanteslegales>' . chr(13);
        if (!empty($_SESSION["formulario"]["datos"]["representanteslegales"])) {
            foreach ($_SESSION["formulario"]["datos"]["representanteslegales"] as $rep) {
                $xml .= '<representantelegal>' . chr(13);
                $xml .= '<idtipoidentificacionrepleg>' . $rep["idtipoidentificacionrepleg"] . "</idtipoidentificacionrepleg>";
                $xml .= '<identificacionrepleg>' . $rep["identificacionrepleg"] . "</identificacionrepleg>";
                $xml .= '<nombrerepleg><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["nombrerepleg"]) . "]]></nombrerepleg>";
                $xml .= '<cargorepleg><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["cargorepleg"]) . "]]></cargorepleg>";
                $xml .= '</representantelegal>' . chr(13);
            }
        }
        $xml .= '</representanteslegales>' . chr(13);

        // Facultades
        if (isset($_SESSION["formulario"]["datos"]["facultades"])) {
            $xml .= '<facultades><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["facultades"])) . ']]></facultades>' . chr(13);
        } else {
            $_SESSION["formulario"]["datos"]["facultades"] = '';
            $xml .= '<facultades><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim($_SESSION["formulario"]["datos"]["facultades"])) . ']]></facultades>' . chr(13);
        }


        // Situaciones de control
        if (!empty($_SESSION["formulario"]["datos"]["sitcontrol"])) {
            foreach ($_SESSION["formulario"]["datos"]["sitcontrol"] as $rep) {
                $xml .= '<sitcontrol>' . chr(13);
                $xml .= '<nombre><![CDATA[' . $rep["nombre"] . ']]></nombre>';
                $xml .= '<identificacion>' . $rep["identificacion"] . '</identificacion>';
                $xml .= '<domicilio>' . $rep["domicilio"] . '</domicilio>';
                $xml .= '<tipo>' . $rep["tipo"] . '</tipo>';
                $xml .= '</sitcontrol>' . chr(13);
            }
        }

        // Informacion financiera - Decreto 1510
        $xml .= '<inffin1510_fechacorte>' . $_SESSION["formulario"]["datos"]["inffin1510_fechacorte"] . '</inffin1510_fechacorte>' . chr(13);
        $xml .= '<inffin1510_actcte>' . $_SESSION["formulario"]["datos"]["inffin1510_actcte"] . '</inffin1510_actcte>' . chr(13);
        $xml .= '<inffin1510_actnocte>' . $_SESSION["formulario"]["datos"]["inffin1510_actnocte"] . '</inffin1510_actnocte>' . chr(13);
        $xml .= '<inffin1510_fijnet>' . $_SESSION["formulario"]["datos"]["inffin1510_fijnet"] . '</inffin1510_fijnet>' . chr(13);
        $xml .= '<inffin1510_actotr>' . $_SESSION["formulario"]["datos"]["inffin1510_actotr"] . '</inffin1510_actotr>' . chr(13);
        $xml .= '<inffin1510_actval>' . $_SESSION["formulario"]["datos"]["inffin1510_actval"] . '</inffin1510_actval>' . chr(13);
        $xml .= '<inffin1510_acttot>' . $_SESSION["formulario"]["datos"]["inffin1510_acttot"] . '</inffin1510_acttot>' . chr(13);

        $xml .= '<inffin1510_pascte>' . $_SESSION["formulario"]["datos"]["inffin1510_pascte"] . '</inffin1510_pascte>' . chr(13);
        $xml .= '<inffin1510_paslar>' . $_SESSION["formulario"]["datos"]["inffin1510_paslar"] . '</inffin1510_paslar>' . chr(13);
        $xml .= '<inffin1510_pastot>' . $_SESSION["formulario"]["datos"]["inffin1510_pastot"] . '</inffin1510_pastot>' . chr(13);
        $xml .= '<inffin1510_patnet>' . $_SESSION["formulario"]["datos"]["inffin1510_patnet"] . '</inffin1510_patnet>' . chr(13);
        $xml .= '<inffin1510_paspat>' . $_SESSION["formulario"]["datos"]["inffin1510_paspat"] . '</inffin1510_paspat>' . chr(13);
        $xml .= '<inffin1510_balsoc>' . $_SESSION["formulario"]["datos"]["inffin1510_balsoc"] . '</inffin1510_balsoc>' . chr(13);

        $xml .= '<inffin1510_ingope>' . $_SESSION["formulario"]["datos"]["inffin1510_ingope"] . '</inffin1510_ingope>' . chr(13);
        $xml .= '<inffin1510_ingnoope>' . $_SESSION["formulario"]["datos"]["inffin1510_ingnoope"] . '</inffin1510_ingnoope>' . chr(13);
        $xml .= '<inffin1510_gasope>' . $_SESSION["formulario"]["datos"]["inffin1510_gasope"] . '</inffin1510_gasope>' . chr(13);
        $xml .= '<inffin1510_gasnoope>' . $_SESSION["formulario"]["datos"]["inffin1510_gasnoope"] . '</inffin1510_gasnoope>' . chr(13);
        $xml .= '<inffin1510_cosven>' . $_SESSION["formulario"]["datos"]["inffin1510_cosven"] . '</inffin1510_cosven>' . chr(13);
        $xml .= '<inffin1510_utiope>' . $_SESSION["formulario"]["datos"]["inffin1510_utiope"] . '</inffin1510_utiope>' . chr(13);
        $xml .= '<inffin1510_utinet>' . $_SESSION["formulario"]["datos"]["inffin1510_utinet"] . '</inffin1510_utinet>' . chr(13);
        $xml .= '<inffin1510_gasint>' . $_SESSION["formulario"]["datos"]["inffin1510_gasint"] . '</inffin1510_gasint>' . chr(13);
        $xml .= '<inffin1510_gasimp>' . $_SESSION["formulario"]["datos"]["inffin1510_gasimp"] . '</inffin1510_gasimp>' . chr(13);

        $xml .= '<inffin1510_indliq>' . $_SESSION["formulario"]["datos"]["inffin1510_indliq"] . '</inffin1510_indliq>' . chr(13);
        $xml .= '<inffin1510_nivend>' . $_SESSION["formulario"]["datos"]["inffin1510_nivend"] . '</inffin1510_nivend>' . chr(13);
        $xml .= '<inffin1510_razcob>' . $_SESSION["formulario"]["datos"]["inffin1510_razcob"] . '</inffin1510_razcob>' . chr(13);
        $xml .= '<inffin1510_renpat>' . $_SESSION["formulario"]["datos"]["inffin1510_renpat"] . '</inffin1510_renpat>' . chr(13);
        $xml .= '<inffin1510_renact>' . $_SESSION["formulario"]["datos"]["inffin1510_renact"] . '</inffin1510_renact>' . chr(13);

        if (!isset($_SESSION["formulario"]["datos"]["inffin1510_gruponiif"])) {
            $_SESSION["formulario"]["datos"]["inffin1510_gruponiif"] = '';
        }
        $xml .= '<inffin1510_gruponiif>' . $_SESSION["formulario"]["datos"]["inffin1510_gruponiif"] . '</inffin1510_gruponiif>' . chr(13);

        // Informacion financiera - Decreto 399a
        $xml .= '<inffin399a_fechacorte>' . $_SESSION["formulario"]["datos"]["inffin399a_fechacorte"] . '</inffin399a_fechacorte>' . chr(13);
        $xml .= '<inffin399a_pregrabado>' . $_SESSION["formulario"]["datos"]["inffin399a_pregrabado"] . '</inffin399a_pregrabado>' . chr(13);
        $xml .= '<inffin399a_actcte>' . $_SESSION["formulario"]["datos"]["inffin399a_actcte"] . '</inffin399a_actcte>' . chr(13);
        $xml .= '<inffin399a_actnocte>' . $_SESSION["formulario"]["datos"]["inffin399a_actnocte"] . '</inffin399a_actnocte>' . chr(13);
        $xml .= '<inffin399a_acttot>' . $_SESSION["formulario"]["datos"]["inffin399a_acttot"] . '</inffin399a_acttot>' . chr(13);

        $xml .= '<inffin399a_pascte>' . $_SESSION["formulario"]["datos"]["inffin399a_pascte"] . '</inffin399a_pascte>' . chr(13);
        $xml .= '<inffin399a_paslar>' . $_SESSION["formulario"]["datos"]["inffin399a_paslar"] . '</inffin399a_paslar>' . chr(13);
        $xml .= '<inffin399a_pastot>' . $_SESSION["formulario"]["datos"]["inffin399a_pastot"] . '</inffin399a_pastot>' . chr(13);
        $xml .= '<inffin399a_patnet>' . $_SESSION["formulario"]["datos"]["inffin399a_patnet"] . '</inffin399a_patnet>' . chr(13);
        $xml .= '<inffin399a_paspat>' . $_SESSION["formulario"]["datos"]["inffin399a_paspat"] . '</inffin399a_paspat>' . chr(13);
        $xml .= '<inffin399a_balsoc>' . $_SESSION["formulario"]["datos"]["inffin399a_balsoc"] . '</inffin399a_balsoc>' . chr(13);

        $xml .= '<inffin399a_ingope>' . $_SESSION["formulario"]["datos"]["inffin399a_ingope"] . '</inffin399a_ingope>' . chr(13);
        $xml .= '<inffin399a_ingnoope>' . $_SESSION["formulario"]["datos"]["inffin399a_ingnoope"] . '</inffin399a_ingnoope>' . chr(13);
        $xml .= '<inffin399a_gasope>' . $_SESSION["formulario"]["datos"]["inffin399a_gasope"] . '</inffin399a_gasope>' . chr(13);
        $xml .= '<inffin399a_gasnoope>' . $_SESSION["formulario"]["datos"]["inffin399a_gasnoope"] . '</inffin399a_gasnoope>' . chr(13);
        $xml .= '<inffin399a_cosven>' . $_SESSION["formulario"]["datos"]["inffin399a_cosven"] . '</inffin399a_cosven>' . chr(13);
        $xml .= '<inffin399a_utiope>' . $_SESSION["formulario"]["datos"]["inffin399a_utiope"] . '</inffin399a_utiope>' . chr(13);
        $xml .= '<inffin399a_utinet>' . $_SESSION["formulario"]["datos"]["inffin399a_utinet"] . '</inffin399a_utinet>' . chr(13);
        $xml .= '<inffin399a_gasint>' . $_SESSION["formulario"]["datos"]["inffin399a_gasint"] . '</inffin399a_gasint>' . chr(13);
        $xml .= '<inffin399a_gasimp>' . $_SESSION["formulario"]["datos"]["inffin399a_gasimp"] . '</inffin399a_gasimp>' . chr(13);

        $xml .= '<inffin399a_indliq>' . $_SESSION["formulario"]["datos"]["inffin399a_indliq"] . '</inffin399a_indliq>' . chr(13);
        $xml .= '<inffin399a_nivend>' . $_SESSION["formulario"]["datos"]["inffin399a_nivend"] . '</inffin399a_nivend>' . chr(13);
        $xml .= '<inffin399a_razcob>' . $_SESSION["formulario"]["datos"]["inffin399a_razcob"] . '</inffin399a_razcob>' . chr(13);
        $xml .= '<inffin399a_renpat>' . $_SESSION["formulario"]["datos"]["inffin399a_renpat"] . '</inffin399a_renpat>' . chr(13);
        $xml .= '<inffin399a_renact>' . $_SESSION["formulario"]["datos"]["inffin399a_renact"] . '</inffin399a_renact>' . chr(13);

        if (!isset($_SESSION["formulario"]["datos"]["inffin399a_gruponiif"])) {
            $_SESSION["formulario"]["datos"]["inffin399a_gruponiif"] = '';
        }
        $xml .= '<inffin399a_gruponiif>' . $_SESSION["formulario"]["datos"]["inffin399a_gruponiif"] . '</inffin399a_gruponiif>' . chr(13);

        // Informacion financiera - Decreto 399b
        $xml .= '<inffin399b_fechacorte>' . $_SESSION["formulario"]["datos"]["inffin399b_fechacorte"] . '</inffin399b_fechacorte>' . chr(13);
        $xml .= '<inffin399b_pregrabado>' . $_SESSION["formulario"]["datos"]["inffin399b_pregrabado"] . '</inffin399b_pregrabado>' . chr(13);
        $xml .= '<inffin399b_actcte>' . $_SESSION["formulario"]["datos"]["inffin399b_actcte"] . '</inffin399b_actcte>' . chr(13);
        $xml .= '<inffin399b_actnocte>' . $_SESSION["formulario"]["datos"]["inffin399b_actnocte"] . '</inffin399b_actnocte>' . chr(13);
        $xml .= '<inffin399b_acttot>' . $_SESSION["formulario"]["datos"]["inffin399b_acttot"] . '</inffin399b_acttot>' . chr(13);

        $xml .= '<inffin399b_pascte>' . $_SESSION["formulario"]["datos"]["inffin399b_pascte"] . '</inffin399b_pascte>' . chr(13);
        $xml .= '<inffin399b_paslar>' . $_SESSION["formulario"]["datos"]["inffin399b_paslar"] . '</inffin399b_paslar>' . chr(13);
        $xml .= '<inffin399b_pastot>' . $_SESSION["formulario"]["datos"]["inffin399b_pastot"] . '</inffin399b_pastot>' . chr(13);
        $xml .= '<inffin399b_patnet>' . $_SESSION["formulario"]["datos"]["inffin399b_patnet"] . '</inffin399b_patnet>' . chr(13);
        $xml .= '<inffin399b_paspat>' . $_SESSION["formulario"]["datos"]["inffin399b_paspat"] . '</inffin399b_paspat>' . chr(13);
        $xml .= '<inffin399b_balsoc>' . $_SESSION["formulario"]["datos"]["inffin399b_balsoc"] . '</inffin399b_balsoc>' . chr(13);

        $xml .= '<inffin399b_ingope>' . $_SESSION["formulario"]["datos"]["inffin399b_ingope"] . '</inffin399b_ingope>' . chr(13);
        $xml .= '<inffin399b_ingnoope>' . $_SESSION["formulario"]["datos"]["inffin399b_ingnoope"] . '</inffin399b_ingnoope>' . chr(13);
        $xml .= '<inffin399b_gasope>' . $_SESSION["formulario"]["datos"]["inffin399b_gasope"] . '</inffin399b_gasope>' . chr(13);
        $xml .= '<inffin399b_gasnoope>' . $_SESSION["formulario"]["datos"]["inffin399b_gasnoope"] . '</inffin399b_gasnoope>' . chr(13);
        $xml .= '<inffin399b_cosven>' . $_SESSION["formulario"]["datos"]["inffin399b_cosven"] . '</inffin399b_cosven>' . chr(13);
        $xml .= '<inffin399b_utiope>' . $_SESSION["formulario"]["datos"]["inffin399b_utiope"] . '</inffin399b_utiope>' . chr(13);
        $xml .= '<inffin399b_utinet>' . $_SESSION["formulario"]["datos"]["inffin399b_utinet"] . '</inffin399b_utinet>' . chr(13);
        $xml .= '<inffin399b_gasint>' . $_SESSION["formulario"]["datos"]["inffin399b_gasint"] . '</inffin399b_gasint>' . chr(13);
        $xml .= '<inffin399b_gasimp>' . $_SESSION["formulario"]["datos"]["inffin399b_gasimp"] . '</inffin399b_gasimp>' . chr(13);

        $xml .= '<inffin399b_indliq>' . $_SESSION["formulario"]["datos"]["inffin399b_indliq"] . '</inffin399b_indliq>' . chr(13);
        $xml .= '<inffin399b_nivend>' . $_SESSION["formulario"]["datos"]["inffin399b_nivend"] . '</inffin399b_nivend>' . chr(13);
        $xml .= '<inffin399b_razcob>' . $_SESSION["formulario"]["datos"]["inffin399b_razcob"] . '</inffin399b_razcob>' . chr(13);
        $xml .= '<inffin399b_renpat>' . $_SESSION["formulario"]["datos"]["inffin399b_renpat"] . '</inffin399b_renpat>' . chr(13);
        $xml .= '<inffin399b_renact>' . $_SESSION["formulario"]["datos"]["inffin399b_renact"] . '</inffin399b_renact>' . chr(13);

        if (!isset($_SESSION["formulario"]["datos"]["inffin399b_gruponiif"])) {
            $_SESSION["formulario"]["datos"]["inffin399b_gruponiif"] = '';
        }
        $xml .= '<inffin399b_gruponiif>' . $_SESSION["formulario"]["datos"]["inffin399b_gruponiif"] . '</inffin399b_gruponiif>' . chr(13);

        // Experiencia decreto 1510
        $i = 0;
        if (isset($_SESSION["formulario"]["datos"]["exp1510"])) {
            if (count($_SESSION["formulario"]["datos"]["exp1510"]) > 0) {
                foreach ($_SESSION["formulario"]["datos"]["exp1510"] as $cnt) {
                    if (trim($cnt["nombrecontratante"]) != '') {
                        $i++;
                        $xml .= '<exp1510>';
                        if (strlen(ltrim(trim($cnt["secuencia"]), "0")) <= 3) {
                            $xml .= '<secuencia>' . sprintf("%03s", $cnt["secuencia"]) . '</secuencia>';
                        } else {
                            $xml .= '<secuencia>' . $cnt["secuencia"] . '</secuencia>';
                        }
                        $xml .= '<clavecontrato>' . $cnt["clavecontrato"] . '</clavecontrato>';
                        $xml .= '<celebradopor>' . $cnt["celebradopor"] . '</celebradopor>';
                        $xml .= '<nombrecontratista><![CDATA[' . (\funcionesGenerales::reemplazarEspeciales($cnt["nombrecontratista"])) . ']]></nombrecontratista>';
                        $xml .= '<nombrecontratante><![CDATA[' . (\funcionesGenerales::reemplazarEspeciales($cnt["nombrecontratante"])) . ']]></nombrecontratante>';
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
                        if (isset($cnt["clasif"])) {
                            foreach ($cnt["clasif"] as $c) {
                                $xml .= '<clasif>' . trim($c) . '</clasif>';
                            }
                        }
                        $xml .= '<generardeclaracion>' . $cnt["generardeclaracion"] . '</generardeclaracion>' . chr(13);
                        $xml .= '</exp1510>';
                    }
                }
            }
        }

        //
        if (isset($_SESSION["formulario"]["datos"]["clasi1510"])) {
            asort($_SESSION["formulario"]["datos"]["clasi1510"]);
            foreach ($_SESSION["formulario"]["datos"]["clasi1510"] as $c) {
                $xml .= '<cla1510>' . trim($c) . '</cla1510>';
            }
        }


        // Bienes
        $xml .= '<bienes>';
        if (!empty($_SESSION["formulario"]["datos"]["bienes"])) {
            foreach ($_SESSION["formulario"]["datos"]["bienes"] as $bien) {
                $xml .= '<bien>';
                $xml .= '<matinmo>' . $bien["matinmo"] . '</matinmo>';
                $xml .= '<dir>' . $bien["dir"] . '</dir>';
                $xml .= '<barrio>' . $bien["barrio"] . '</barrio>';
                $xml .= '<muni>' . $bien["muni"] . '</muni>';
                $xml .= '<dpto>' . $bien["muni"] . '</dpto>';
                $xml .= '<pais>' . $bien["pais"] . '</pais>';
                $xml .= '</bien>';
            }
        }
        $xml .= '</bienes>';

        foreach ($_SESSION["formulario"]["datos"]["contratos"] as $cnt) {

            if (!isset($cnt["isn"])) {
                $cnt["isn"] = '';
            }
            if (!isset($cnt["codcamara"])) {
                $cnt["codcamara"] = '';
            }
            if (!isset($cnt["numradic"])) {
                $cnt["numradic"] = '';
            }
            if (!isset($cnt["fecradic"])) {
                $cnt["fecradic"] = '';
            }
            if (!isset($cnt["horradic"])) {
                $cnt["horradic"] = '';
            }
            if (!isset($cnt["fecreporte"])) {
                $cnt["fecreporte"] = '';
            }
            if (!isset($cnt["nitentidad"])) {
                $cnt["nitentidad"] = '';
            }
            if (!isset($cnt["nombreentidad"])) {
                $cnt["nombreentidad"] = '';
            }
            if (!isset($cnt["idmunientidad"])) {
                $cnt["idmunientidad"] = '';
            }
            if (!isset($cnt["divarea"])) {
                $cnt["divarea"] = '';
            }
            if (!isset($cnt["idefun"])) {
                $cnt["idefun"] = '';
            }
            if (!isset($cnt["nomfun"])) {
                $cnt["nomfun"] = '';
            }
            if (!isset($cnt["carfun"])) {
                $cnt["carfun"] = '';
            }
            if (!isset($cnt["numcontrato"])) {
                $cnt["numcontrato"] = '';
            }
            if (!isset($cnt["numcontratosecop"])) {
                $cnt["numcontratosecop"] = '';
            }

            if (!isset($cnt["fechaadj"])) {
                $cnt["fechaadj"] = '';
            }
            if (!isset($cnt["fechaper"])) {
                $cnt["fechaper"] = '';
            }
            if (!isset($cnt["fechaini"])) {
                $cnt["fechaini"] = '';
            }
            if (!isset($cnt["fechater"])) {
                $cnt["fechater"] = '';
            }
            if (!isset($cnt["fechaeje"])) {
                $cnt["fechaeje"] = '';
            }
            if (!isset($cnt["fechasus"])) {
                $cnt["fechasus"] = '';
            }
            if (!isset($cnt["fechaliq"])) {
                $cnt["fechaliq"] = '';
            }
            if (!isset($cnt["fechaterant"])) {
                $cnt["fechaterant"] = '';
            }
            if (!isset($cnt["fechaces"])) {
                $cnt["fechaces"] = '';
            }

            if (!isset($cnt["codigoact"])) {
                $cnt["codigoact"] = '';
            }
            if (!isset($cnt["ciiu1"])) {
                $cnt["ciiu1"] = '';
            }
            if (!isset($cnt["ciiu2"])) {
                $cnt["ciiu2"] = '';
            }
            if (!isset($cnt["ciiu3"])) {
                $cnt["ciiu3"] = '';
            }
            if (!isset($cnt["ciiu4"])) {
                $cnt["ciiu4"] = '';
            }
            if (!isset($cnt["objeto"])) {
                $cnt["objeto"] = '';
            }
            if (!isset($cnt["tipocont"])) {
                $cnt["tipocont"] = '';
            }
            if (!isset($cnt["valorcont"])) {
                $cnt["valorcont"] = '';
            }
            if (!isset($cnt["valorcontpag"])) {
                $cnt["valorcontpag"] = '';
            }
            if (!isset($cnt["indcump"])) {
                $cnt["indcump"] = '';
            }
            if (!isset($cnt["estadocont"])) {
                $cnt["estadocont"] = '';
            }
            if (!isset($cnt["motivoter"])) {
                $cnt["motivoter"] = '';
            }
            if (!isset($cnt["motivoces"])) {
                $cnt["motivoces"] = '';
            }
            if (!isset($cnt["observaciones"])) {
                $cnt["observaciones"] = '';
            }
            if (!isset($cnt["numlibro"])) {
                $cnt["numlibro"] = '';
            }
            if (!isset($cnt["numreglib"])) {
                $cnt["numreglib"] = '';
            }
            if (!isset($cnt["fecreglib"])) {
                $cnt["fecreglib"] = '';
            }
            if (!isset($cnt["codigocamaraorigen"])) {
                $cnt["codigocamaraorigen"] = '';
            }
            if (!isset($cnt["numregistrocamaraorigen"])) {
                $cnt["numregistrocamaraorigen"] = '';
            }
            if (!isset($cnt["fecregistrocamaraorigen"])) {
                $cnt["fecregistrocamaraorigen"] = '';
            }

            $xml .= '<contrato>' . chr(13);
            $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
            $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
            $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
            $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
            $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
            $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
            $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
            $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
            $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
            $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
            $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
            $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
            $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
            $xml .= '<numcontrato>' . $cnt["numcontrato"] . '</numcontrato>' . chr(13);
            $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
            $xml .= '<fechaadj>' . $cnt["fechaadj"] . '</fechaadj>' . chr(13);
            $xml .= '<fechaper>' . $cnt["fechaper"] . '</fechaper>' . chr(13);
            $xml .= '<fechaini>' . $cnt["fechaini"] . '</fechaini>' . chr(13);
            $xml .= '<fechater>' . $cnt["fechater"] . '</fechater>' . chr(13);
            $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
            $xml .= '<fechaliq>' . $cnt["fechaliq"] . '</fechaliq>' . chr(13);
            $xml .= '<fechaterant>' . $cnt["fechaterant"] . '</fechaterant>' . chr(13);
            $xml .= '<fechaces>' . $cnt["fechaces"] . '</fechaces>' . chr(13);
            $xml .= '<codigoact>' . $cnt["codigoact"] . '</codigoact>' . chr(13);
            $xml .= '<ciiu1>' . $cnt["ciiu1"] . '</ciiu1>' . chr(13);
            $xml .= '<ciiu2>' . $cnt["ciiu2"] . '</ciiu2>' . chr(13);
            $xml .= '<ciiu3>' . $cnt["ciiu3"] . '</ciiu3>' . chr(13);
            $xml .= '<ciiu4>' . $cnt["ciiu4"] . '</ciiu4>' . chr(13);
            $xml .= '<tipocont>' . $cnt["tipocont"] . '</tipocont>' . chr(13);
            $xml .= '<valorcont>' . $cnt["valorcont"] . '</valorcont>' . chr(13);
            $xml .= '<valorcontpag>' . $cnt["valorcontpag"] . '</valorcontpag>' . chr(13);
            $xml .= '<indcump>' . $cnt["indcump"] . '</indcump>' . chr(13);
            $xml .= '<estadocont>' . $cnt["estadocont"] . '</estadocont>' . chr(13);
            $xml .= '<motivoter>' . $cnt["motivoter"] . '</motivoter>' . chr(13);
            $xml .= '<motivoces>' . $cnt["motivoces"] . '</motivoces>' . chr(13);
            $xml .= '<observaciones>' . $cnt["observaciones"] . '</observaciones>' . chr(13);
            $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
            $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
            $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
            if (isset($cnt["clasificaciones"])) {
                if (!empty($cnt["clasificaciones"])) {
                    foreach ($cnt["clasificaciones"] as $clasicon) {
                        $xml .= '<clasicon>' . $clasicon . '</clasicon>' . chr(13);
                    }
                }
            }
            if (isset($cnt["unspsc"])) {
                if (!empty($cnt["unspsc"])) {
                    foreach ($cnt["unspsc"] as $clasicon) {
                        $xml .= '<unspsc>' . $clasicon . '</unspsc>' . chr(13);
                    }
                }
            }
            $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
            $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
            $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
            $xml .= '</contrato>' . chr(13);
        }

        foreach ($_SESSION["formulario"]["datos"]["multas"] as $cnt) {

            if (!isset($cnt["isn"])) {
                $cnt["isn"] = '';
            }
            if (!isset($cnt["codcamara"])) {
                $cnt["codcamara"] = '';
            }
            if (!isset($cnt["numradic"])) {
                $cnt["numradic"] = '';
            }
            if (!isset($cnt["fecradic"])) {
                $cnt["fecradic"] = '';
            }
            if (!isset($cnt["horradic"])) {
                $cnt["horradic"] = '';
            }
            if (!isset($cnt["fecreporte"])) {
                $cnt["fecreporte"] = '';
            }
            if (!isset($cnt["nitentidad"])) {
                $cnt["nitentidad"] = '';
            }
            if (!isset($cnt["nombreentidad"])) {
                $cnt["nombreentidad"] = '';
            }
            if (!isset($cnt["idmunientidad"])) {
                $cnt["idmunientidad"] = '';
            }
            if (!isset($cnt["divarea"])) {
                $cnt["divarea"] = '';
            }
            if (!isset($cnt["idefun"])) {
                $cnt["idefun"] = '';
            }
            if (!isset($cnt["nomfun"])) {
                $cnt["nomfun"] = '';
            }
            if (!isset($cnt["carfun"])) {
                $cnt["carfun"] = '';
            }
            if (!isset($cnt["numcontrato"])) {
                $cnt["numcontrato"] = '';
            }
            if (!isset($cnt["numcontratosecop"])) {
                $cnt["numcontratosecop"] = '';
            }
            if (!isset($cnt["numacto"])) {
                $cnt["numacto"] = '';
            }
            if (!isset($cnt["fechaacto"])) {
                $cnt["fechaacto"] = '';
            }
            if (!isset($cnt["fechaeje"])) {
                $cnt["fechaeje"] = '';
            }
            if (!isset($cnt["valormult"])) {
                $cnt["valormult"] = '';
            }
            if (!isset($cnt["valormultpag"])) {
                $cnt["valormultpag"] = '';
            }
            if (!isset($cnt["numsus"])) {
                $cnt["numsus"] = '';
            }
            if (!isset($cnt["fechasus"])) {
                $cnt["fechasus"] = '';
            }
            if (!isset($cnt["numconf"])) {
                $cnt["numconf"] = '';
            }
            if (!isset($cnt["fechaconf"])) {
                $cnt["fechaconf"] = '';
            }
            if (!isset($cnt["estadomult"])) {
                $cnt["estadomult"] = '';
            }
            if (!isset($cnt["numrev"])) {
                $cnt["numrev"] = '';
            }
            if (!isset($cnt["fechanumrev"])) {
                $cnt["fechanumrev"] = '';
            }
            if (!isset($cnt["numlibro"])) {
                $cnt["numlibro"] = '';
            }
            if (!isset($cnt["numreglib"])) {
                $cnt["numreglib"] = '';
            }
            if (!isset($cnt["fecreglib"])) {
                $cnt["fecreglib"] = '';
            }
            if (!isset($cnt["codigocamaraorigen"])) {
                $cnt["codigocamaraorigen"] = '';
            }
            if (!isset($cnt["numregistrocamaraorigen"])) {
                $cnt["numregistrocamaraorigen"] = '';
            }
            if (!isset($cnt["fecregistrocamaraorigen"])) {
                $cnt["fecregistrocamaraorigen"] = '';
            }


            $xml .= '<multa>' . chr(13);
            $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
            $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
            $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
            $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
            $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
            $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
            $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
            $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
            $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
            $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
            $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
            $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
            $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
            $xml .= '<numcontrato>' . $cnt["numcontrato"] . '</numcontrato>' . chr(13);
            $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
            $xml .= '<numacto>' . $cnt["numacto"] . '</numacto>' . chr(13);
            $xml .= '<fechaacto>' . $cnt["fechaacto"] . '</fechaacto>' . chr(13);
            $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
            $xml .= '<valormult>' . $cnt["valormult"] . '</valormult>' . chr(13);
            $xml .= '<valormultpag>' . $cnt["valormultpag"] . '</valormultpag>' . chr(13);
            $xml .= '<numsus>' . $cnt["numsus"] . '</numsus>' . chr(13);
            $xml .= '<fechasus>' . $cnt["fechasus"] . '</fechasus>' . chr(13);
            $xml .= '<numconf>' . $cnt["numconf"] . '</numconf>' . chr(13);
            $xml .= '<fechaconf>' . $cnt["fechaconf"] . '</fechaconf>' . chr(13);
            $xml .= '<estadomult>' . $cnt["estadomult"] . '</estadomult>' . chr(13);
            $xml .= '<numrev>' . $cnt["numrev"] . '</numrev>' . chr(13);
            $xml .= '<fechanumrev>' . $cnt["fechanumrev"] . '</fechanumrev>' . chr(13);
            $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
            $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
            $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
            $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
            $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
            $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
            $xml .= '</multa>' . chr(13);
        }

        foreach ($_SESSION["formulario"]["datos"]["sanciones"] as $cnt) {
            if (!isset($cnt["isn"])) {
                $cnt["isn"] = '';
            }
            if (!isset($cnt["codcamara"])) {
                $cnt["codcamara"] = '';
            }
            if (!isset($cnt["numradic"])) {
                $cnt["numradic"] = '';
            }
            if (!isset($cnt["fecradic"])) {
                $cnt["fecradic"] = '';
            }
            if (!isset($cnt["horradic"])) {
                $cnt["horradic"] = '';
            }
            if (!isset($cnt["fecreporte"])) {
                $cnt["fecreporte"] = '';
            }
            if (!isset($cnt["nitentidad"])) {
                $cnt["nitentidad"] = '';
            }
            if (!isset($cnt["nombreentidad"])) {
                $cnt["nombreentidad"] = '';
            }
            if (!isset($cnt["idmunientidad"])) {
                $cnt["idmunientidad"] = '';
            }
            if (!isset($cnt["divarea"])) {
                $cnt["divarea"] = '';
            }
            if (!isset($cnt["idefun"])) {
                $cnt["idefun"] = '';
            }
            if (!isset($cnt["nomfun"])) {
                $cnt["nomfun"] = '';
            }
            if (!isset($cnt["carfun"])) {
                $cnt["carfun"] = '';
            }
            if (!isset($cnt["numcontrato"])) {
                $cnt["numcontrato"] = '';
            }
            if (!isset($cnt["numcontratosecop"])) {
                $cnt["numcontratosecop"] = '';
            }
            if (!isset($cnt["numacto"])) {
                $cnt["numacto"] = '';
            }
            if (!isset($cnt["fechaacto"])) {
                $cnt["fechaacto"] = '';
            }
            if (!isset($cnt["fechaeje"])) {
                $cnt["fechaeje"] = '';
            }
            if (!isset($cnt["numsus"])) {
                $cnt["numsus"] = '';
            }
            if (!isset($cnt["fechasus"])) {
                $cnt["fechasus"] = '';
            }
            if (!isset($cnt["numconf"])) {
                $cnt["numconf"] = '';
            }
            if (!isset($cnt["fechaconf"])) {
                $cnt["fechaconf"] = '';
            }
            if (!isset($cnt["estadosanc"])) {
                $cnt["estadosanc"] = '';
            }
            if (!isset($cnt["condinc"])) {
                $cnt["condinc"] = '';
            }
            if (!isset($cnt["cumsanc"])) {
                $cnt["cumsanc"] = '';
            }
            if (!isset($cnt["numrev"])) {
                $cnt["numrev"] = '';
            }
            if (!isset($cnt["fechanumrev"])) {
                $cnt["fechanumrev"] = '';
            }
            if (!isset($cnt["descripcion"])) {
                $cnt["descripcion"] = '';
            }
            if (!isset($cnt["fundamento"])) {
                $cnt["fundamento"] = '';
            }
            if (!isset($cnt["vigencia"])) {
                $cnt["vigencia"] = '';
            }
            if (!isset($cnt["numlibro"])) {
                $cnt["numlibro"] = '';
            }
            if (!isset($cnt["numreglib"])) {
                $cnt["numreglib"] = '';
            }
            if (!isset($cnt["fecreglib"])) {
                $cnt["fecreglib"] = '';
            }
            if (!isset($cnt["codigocamaraorigen"])) {
                $cnt["codigocamaraorigen"] = '';
            }
            if (!isset($cnt["numregistrocamaraorigen"])) {
                $cnt["numregistrocamaraorigen"] = '';
            }
            if (!isset($cnt["fecregistrocamaraorigen"])) {
                $cnt["fecregistrocamaraorigen"] = '';
            }


            $xml .= '<sancion>' . chr(13);
            $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
            $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
            $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
            $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
            $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
            $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
            $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
            $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
            $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
            $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
            $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
            $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
            $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
            $xml .= '<numcontrato>' . $cnt["numcontrato"] . '</numcontrato>' . chr(13);
            $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
            $xml .= '<numacto>' . $cnt["numacto"] . '</numacto>' . chr(13);
            $xml .= '<fechaacto>' . $cnt["fechaacto"] . '</fechaacto>' . chr(13);
            $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
            $xml .= '<numsus>' . $cnt["numsus"] . '</numsus>' . chr(13);
            $xml .= '<fechasus>' . $cnt["fechasus"] . '</fechasus>' . chr(13);
            $xml .= '<numconf>' . $cnt["numconf"] . '</numconf>' . chr(13);
            $xml .= '<fechaconf>' . $cnt["fechaconf"] . '</fechaconf>' . chr(13);
            $xml .= '<estadosanc>' . $cnt["estadosanc"] . '</estadosanc>' . chr(13);
            $xml .= '<condinc>' . $cnt["condinc"] . '</condinc>' . chr(13);
            $xml .= '<cumsanc>' . $cnt["cumsanc"] . '</cumsanc>' . chr(13);
            $xml .= '<numrev>' . $cnt["numrev"] . '</numrev>' . chr(13);
            $xml .= '<dessanc>' . $cnt["descripcion"] . '</dessanc>' . chr(13);
            $xml .= '<fundamento>' . $cnt["fundamento"] . '</fundamento>' . chr(13);
            $xml .= '<vigencia>' . $cnt["vigencia"] . '</vigencia>' . chr(13);
            $xml .= '<fechanumrev>' . $cnt["fechanumrev"] . '</fechanumrev>' . chr(13);
            $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
            $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
            $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
            $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
            $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
            $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
            $xml .= '</sancion>' . chr(13);
        }

        foreach ($_SESSION["formulario"]["datos"]["sandis"] as $cnt) {
            if (!isset($cnt["isn"])) {
                $cnt["isn"] = '';
            }
            if (!isset($cnt["codcamara"])) {
                $cnt["codcamara"] = '';
            }
            if (!isset($cnt["numradic"])) {
                $cnt["numradic"] = '';
            }
            if (!isset($cnt["fecradic"])) {
                $cnt["fecradic"] = '';
            }
            if (!isset($cnt["horradic"])) {
                $cnt["horradic"] = '';
            }
            if (!isset($cnt["fecreporte"])) {
                $cnt["fecreporte"] = '';
            }
            if (!isset($cnt["nitentidad"])) {
                $cnt["nitentidad"] = '';
            }
            if (!isset($cnt["nombreentidad"])) {
                $cnt["nombreentidad"] = '';
            }
            if (!isset($cnt["idmunientidad"])) {
                $cnt["idmunientidad"] = '';
            }
            if (!isset($cnt["divarea"])) {
                $cnt["divarea"] = '';
            }
            if (!isset($cnt["idefun"])) {
                $cnt["idefun"] = '';
            }
            if (!isset($cnt["nomfun"])) {
                $cnt["nomfun"] = '';
            }
            if (!isset($cnt["carfun"])) {
                $cnt["carfun"] = '';
            }
            if (!isset($cnt["numcontratosecop"])) {
                $cnt["numcontratosecop"] = '';
            }
            if (!isset($cnt["numacto"])) {
                $cnt["numacto"] = '';
            }
            if (!isset($cnt["fechaacto"])) {
                $cnt["fechaacto"] = '';
            }
            if (!isset($cnt["fechaeje"])) {
                $cnt["fechaeje"] = '';
            }
            if (!isset($cnt["numsus"])) {
                $cnt["numsus"] = '';
            }
            if (!isset($cnt["fechasus"])) {
                $cnt["fechasus"] = '';
            }
            if (!isset($cnt["numconf"])) {
                $cnt["numconf"] = '';
            }
            if (!isset($cnt["fechaconf"])) {
                $cnt["fechaconf"] = '';
            }
            if (!isset($cnt["estadosanc"])) {
                $cnt["estadosanc"] = '';
            }
            if (!isset($cnt["numrev"])) {
                $cnt["numrev"] = '';
            }
            if (!isset($cnt["fechanumrev"])) {
                $cnt["fechanumrev"] = '';
            }
            if (!isset($cnt["descripcion"])) {
                $cnt["descripcion"] = '';
            }
            if (!isset($cnt["fundamento"])) {
                $cnt["fundamento"] = '';
            }
            if (!isset($cnt["vigencia"])) {
                $cnt["vigencia"] = '';
            }
            if (!isset($cnt["numlibro"])) {
                $cnt["numlibro"] = '';
            }
            if (!isset($cnt["numreglib"])) {
                $cnt["numreglib"] = '';
            }
            if (!isset($cnt["fecreglib"])) {
                $cnt["fecreglib"] = '';
            }
            if (!isset($cnt["codigocamaraorigen"])) {
                $cnt["codigocamaraorigen"] = '';
            }
            if (!isset($cnt["numregistrocamaraorigen"])) {
                $cnt["numregistrocamaraorigen"] = '';
            }
            if (!isset($cnt["fecregistrocamaraorigen"])) {
                $cnt["fecregistrocamaraorigen"] = '';
            }

            $xml .= '<sandis>' . chr(13);
            $xml .= '<isn>' . $cnt["isn"] . '</isn>' . chr(13);
            $xml .= '<codcamara>' . $cnt["codcamara"] . '</codcamara>' . chr(13);
            $xml .= '<numradic>' . $cnt["numradic"] . '</numradic>' . chr(13);
            $xml .= '<fecradic>' . $cnt["fecradic"] . '</fecradic>' . chr(13);
            $xml .= '<horradic>' . $cnt["horradic"] . '</horradic>' . chr(13);
            $xml .= '<fecreporte>' . $cnt["fecreporte"] . '</fecreporte>' . chr(13);
            $xml .= '<nitentidad>' . $cnt["nitentidad"] . '</nitentidad>' . chr(13);
            $xml .= '<nombreentidad>' . $cnt["nombreentidad"] . '</nombreentidad>' . chr(13);
            $xml .= '<idmunientidad>' . $cnt["idmunientidad"] . '</idmunientidad>' . chr(13);
            $xml .= '<divarea>' . $cnt["divarea"] . '</divarea>' . chr(13);
            $xml .= '<idefun>' . $cnt["idefun"] . '</idefun>' . chr(13);
            $xml .= '<nomfun>' . $cnt["nomfun"] . '</nomfun>' . chr(13);
            $xml .= '<carfun>' . $cnt["carfun"] . '</carfun>' . chr(13);
            $xml .= '<numcontratosecop>' . $cnt["numcontratosecop"] . '</numcontratosecop>' . chr(13);
            $xml .= '<numacto>' . $cnt["numacto"] . '</numacto>' . chr(13);
            $xml .= '<fechaacto>' . $cnt["fechaacto"] . '</fechaacto>' . chr(13);
            $xml .= '<fechaeje>' . $cnt["fechaeje"] . '</fechaeje>' . chr(13);
            $xml .= '<numsus>' . $cnt["numsus"] . '</numsus>' . chr(13);
            $xml .= '<fechasus>' . $cnt["fechasus"] . '</fechasus>' . chr(13);
            $xml .= '<numconf>' . $cnt["numconf"] . '</numconf>' . chr(13);
            $xml .= '<fechaconf>' . $cnt["fechaconf"] . '</fechaconf>' . chr(13);
            $xml .= '<estadosanc>' . $cnt["estadosanc"] . '</estadosanc>' . chr(13);
            $xml .= '<numrev>' . $cnt["numrev"] . '</numrev>' . chr(13);
            $xml .= '<dessanc>' . $cnt["descripcion"] . '</dessanc>' . chr(13);
            $xml .= '<fundamento>' . $cnt["fundamento"] . '</fundamento>' . chr(13);
            $xml .= '<vigencia>' . $cnt["vigencia"] . '</vigencia>' . chr(13);
            $xml .= '<fechanumrev>' . $cnt["fechanumrev"] . '</fechanumrev>' . chr(13);
            $xml .= '<numlibro>' . $cnt["numlibro"] . '</numlibro>' . chr(13);
            $xml .= '<numreglib>' . $cnt["numreglib"] . '</numreglib>' . chr(13);
            $xml .= '<fecreglib>' . $cnt["fecreglib"] . '</fecreglib>' . chr(13);
            $xml .= '<codigocamaraorigen>' . $cnt["codigocamaraorigen"] . '</codigocamaraorigen>' . chr(13);
            $xml .= '<numregistrocamaraorigen>' . $cnt["numregistrocamaraorigen"] . '</numregistrocamaraorigen>' . chr(13);
            $xml .= '<fecregistrocamaraorigen>' . $cnt["fecregistrocamaraorigen"] . '</fecregistrocamaraorigen>' . chr(13);
            $xml .= '</sancion>' . chr(13);
        }

        $xml .= '</expediente>' . chr(13);
        $xml .= '</expedientes>' . chr(13);
        return $xml;
    }

}

?>
