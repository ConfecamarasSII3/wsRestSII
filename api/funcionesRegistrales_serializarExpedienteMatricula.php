<?php

class funcionesRegistrales_serializarExpedienteMatricula {

    public static function serializarExpedienteMatricula($dbx, $numrec = '', $datos = array(), $reemplazar = 'si', $extendido = 'si') {
//
        $xml = '<?xml version="1.0" encoding="utf-8" ?>';
        $xml .= '<expedientes>';
        $xml .= '<error>0000</error>';
        $xml .= '<msgError>Registro serializado correctamente</msgError>';
        $xml .= '<expediente>';

// Valida los datos conm nlos que debe trabajar
        if (trim((string) $numrec) != '') {
            $xml .= '<numerorecuperacion>' . $numrec . '</numerorecuperacion>';
        } else {
            if (isset($_SESSION["tramite"]["numerorecuperacion"])) {
                $xml .= '<numerorecuperacion>' . trim((string) $_SESSION["tramite"]["numerorecuperacion"]) . '</numerorecuperacion>';
            } else {
                $xml .= '<numerorecuperacion></numerorecuperacion>';
            }
        }
        if (empty($datos)) {
            $datos = $_SESSION["formulario"]["datos"];
        }

//
        if (!isset($datos["cc"])) {
            $datos["cc"] = CODIGO_EMPRESA;
        }
        if (!isset($datos["complementorazonsocial"])) {
            $datos["complementorazonsocial"] = '';
        }
        if (!isset($datos["nombrebase64"])) {
            $datos["nombrebase64"] = '';
        }
        if (!isset($datos["siglabase64"])) {
            $datos["siglabase64"] = '';
        }        
        if (!isset($datos["estadoproponente"])) {
            $datos["estadoproponente"] = '';
        }
        if (!isset($datos["nuevonombre"])) {
            $datos["nuevonombre"] = '';
        }
        if (!isset($datos["fechanacimiento"])) {
            $datos["fechanacimiento"] = '';
        }
        if (!isset($datos["sexo"])) {
            $datos["sexo"] = '';
        }
        if (!isset($datos["ideext"])) {
            $datos["ideext"] = '';
        }
        if (!isset($datos["estadonit"])) {
            $datos["estadonit"] = '';
        }
        if (!isset($datos["prerut"])) {
            $datos["prerut"] = '';
        }
        if (!isset($datos["expedienteinactivo"])) {
            $datos["expedienteinactivo"] = '';
        }
        if (!isset($datos["fechacancelacion"])) {
            $datos["fechacancelacion"] = '';
        }
        if (!isset($datos["motivocancelacion"])) {
            $datos["motivocancelacion"] = '';
        }
        if (!isset($datos["estadotipoliquidacion"])) {
            $datos["estadotipoliquidacion"] = '';
        }
        if (!isset($datos["paiori"])) {
            $datos["paiori"] = '';
        }
        if (!isset($datos["idetripaiori"])) {
            $datos["idetripaiori"] = '';
        }
        if (!isset($datos["idetriextep"])) {
            $datos["idetriextep"] = '';
        }

        if (!isset($datos["empresafamiliar"])) {
            $datos["empresafamiliar"] = '';
        }
        if (!isset($datos["procesosinnovacion"])) {
            $datos["procesosinnovacion"] = '';
        }
        if (!isset($datos["tipogruemp"])) {
            $datos["tipogruemp"] = '';
        }
        if (!isset($datos["nombregruemp"])) {
            $datos["nombregruemp"] = '';
        }
        if (!isset($datos["ctrderpub"])) {
            $datos["ctrderpub"] = '';
        }
        if (!isset($datos["ctrcodcoop"])) {
            $datos["ctrcodcoop"] = '';
        }
        if (!isset($datos["ctrcodotras"])) {
            $datos["ctrcodotras"] = '';
        }
        if (!isset($datos["ctresacntasociados"])) {
            $datos["ctresacntasociados"] = 0;
        }
        if (!isset($datos["ctresacntmujeres"])) {
            $datos["ctresacntmujeres"] = 0;
        }
        if (!isset($datos["ctresacnthombres"])) {
            $datos["ctresacnthombres"] = 0;
        }
        if (!isset($datos["ctresapertgremio"])) {
            $datos["ctresapertgremio"] = '';
        }
        if (!isset($datos["ctresagremio"])) {
            $datos["ctresagremio"] = '';
        }
        if (!isset($datos["ctresaacredita"])) {
            $datos["ctresaacredita"] = '';
        }
        if (!isset($datos["ctresaivc"])) {
            $datos["ctresaivc"] = '';
        }
        if (!isset($datos["ctresainfoivc"])) {
            $datos["ctresainfoivc"] = '';
        }
        if (!isset($datos["ctresaautregistro"])) {
            $datos["ctresaautregistro"] = '';
        }
        if (!isset($datos["ctresaentautoriza"])) {
            $datos["ctresaentautoriza"] = '';
        }
        if (!isset($datos["ctresacodnat"])) {
            $datos["ctresacodnat"] = '';
        }
        if (!isset($datos["ctresadiscap"])) {
            $datos["ctresadiscap"] = '';
        }
        if (!isset($datos["ctresaetnia"])) {
            $datos["ctresaetnia"] = '';
        }
        if (!isset($datos["ctresacualetnia"])) {
            $datos["ctresacualetnia"] = '';
        }
        if (!isset($datos["ctresadespvictreins"])) {
            $datos["ctresadespvictreins"] = '';
        }
        if (!isset($datos["ctresacualdespvictreins"])) {
            $datos["ctresacualdespvictreins"] = '';
        }
        if (!isset($datos["ctresalgbti"])) {
            $datos["ctresalgbti"] = '';
        }
        if (!isset($datos["ctresaindgest"])) {
            $datos["ctresaindgest"] = '';
        }
        if (!isset($datos["codigopostalcom"])) {
            $datos["codigopostalcom"] = '';
        }
        if (!isset($datos["codigopostalnot"])) {
            $datos["codigopostalnot"] = '';
        }
        if (!isset($datos["feciniact1"])) {
            $datos["feciniact1"] = '';
        }
        if (!isset($datos["feciniact2"])) {
            $datos["feciniact2"] = '';
        }
        if (!isset($datos["codaduaneros"])) {
            $datos["codaduaneros"] = '';
        }
        if (!isset($datos["gruponiif"])) {
            $datos["gruponiif"] = '';
        }
        if (!isset($datos["niifconciliacion"])) {
            $datos["niifconciliacion"] = '';
        }
        if (!isset($datos["aportantesegsocial"])) {
            $datos["aportantesegsocial"] = '';
        }
        if (!isset($datos["tipoaportantesegsocial"])) {
            $datos["tipoaportantesegsocial"] = '';
        }

        if (!isset($datos["prerut"])) {
            $datos["prerut"] = '';
        }
        if (!isset($datos["fecmatant"])) {
            $datos["fecmatant"] = '';
        }
        if (!isset($datos["fecrenant"])) {
            $datos["fecrenant"] = '';
        }
        if (!isset($datos["camant"])) {
            $datos["camant"] = '';
        }
        if (!isset($datos["munant"])) {
            $datos["munant"] = '';
        }
        if (!isset($datos["benart7ant"])) {
            $datos["benart7ant"] = '';
        }
        if (!isset($datos["ultanorenant"])) {
            $datos["ultanorenant"] = '';
        }
        if (!isset($datos["matant"])) {
            $datos["matant"] = '';
        }
        if (!isset($datos["otorgaperjur"])) {
            $datos["otorgaperjur"] = '';
        }
        if (!isset($datos["nroperjur"])) {
            $datos["nroperjur"] = '';
        }
        if (!isset($datos["patrimonio"])) {
            $datos["patrimonio"] = 0;
        }
        if (!isset($datos["nombresegundocontacto"])) {
            $datos["nombresegundocontacto"] = '';
        }
        if (!isset($datos["cargosegundocontacto"])) {
            $datos["cargosegundocontacto"] = '';
        }

        if (!isset($datos["cumplerequisitos1780"])) {
            $datos["cumplerequisitos1780"] = '';
        }
        if (!isset($datos["cumplerequisitos1780primren"])) {
            $datos["cumplerequisitos1780primren"] = '';
        }
        if (!isset($datos["renunciabeneficios1780"])) {
            $datos["renunciabeneficios1780"] = '';
        }
        if (!isset($datos["naturaleza"])) {
            $datos["naturaleza"] = '0';
        }

        // 
        if (!isset($datos["nombrevia"])) {
            $datos["nombrevia"] = '';
        }
        if (!isset($datos["nombrecruce"])) {
            $datos["nombrecruce"] = '';
        }
        if (!isset($datos["nombrecomplemento1"])) {
            $datos["nombrecomplemento1"] = '';
        }
        if (!isset($datos["nombrecomplemento2"])) {
            $datos["nombrecomplemento2"] = '';
        }

        //
        if (!isset($datos["paicom"])) {
            $datos["paicom"] = '';
        }
        if (!isset($datos["painot"])) {
            $datos["painot"] = '';
        }
        if (!isset($datos["estadocapturado"])) {
            $datos["estadocapturado"] = '';
        }
        if (!isset($datos["estadocapturadootros"])) {
            $datos["estadocapturadootros"] = '';
        }
        if (!isset($datos["cantest"])) {
            $datos["cantest"] = 0;
        }
        if (!isset($datos["certificardesde"])) {
            $datos["certificardesde"] = '';
        }
        if (!isset($datos["ctrbic"])) {
            $datos["ctrbic"] = '';
        }

        if (!isset($datos["shd"])) {
            $datos["shd"] = array();
        }
        if (!isset($datos["shd"][1])) {
            $datos["shd"][1] = '';
        }
        if (!isset($datos["shd"][2])) {
            $datos["shd"][2] = '';
        }
        if (!isset($datos["shd"][3])) {
            $datos["shd"][3] = '';
        }
        if (!isset($datos["shd"][4])) {
            $datos["shd"][4] = '';
        }

        if (!isset($datos["cantidadmujeres"])) {
            $datos["cantidadmujeres"] = 0;
        }
        if (!isset($datos["cantidadmujerescargosdirectivos"])) {
            $datos["cantidadmujerescargosdirectivos"] = 0;
        }
        if (!isset($datos["cantidadcargosdirectivos"])) {
            $datos["cantidadcargosdirectivos"] = 0;
        }
        if (!isset($datos["participacionmujeres"])) {
            $datos["participacionmujeres"] = 0;
        }
        if (!isset($datos["etnia"])) {
            $datos["etnia"] = '';
        }
        if (!isset($datos["participacionetnia"])) {
            $datos["participacionetnia"] = 0;
        }
        if (!isset($datos["emprendimientosocial"])) {
            $datos["emprendimientosocial"] = '';
        }
        if (!isset($datos["empsoccategorias_otros"])) {
            $datos["empsoccategorias_otros"] = '';
        }
        if (!isset($datos["empsocbeneficiarios_otros"])) {
            $datos["empsocbeneficiarios_otros"] = '';
        }
        if (!isset($datos["ciiutamanoempresarial"])) {
            $datos["ciiutamanoempresarial"] = '';
        }
        if (!isset($datos["ingresostamanoempresarial"])) {
            $datos["ingresostamanoempresarial"] = 0;
        }
        if (!isset($datos["anodatostamanoempresarial"])) {
            $datos["anodatostamanoempresarial"] = '';
        }
        if (!isset($datos["fechadatostamanoempresarial"])) {
            $datos["fechadatostamanoempresarial"] = '';
        }

        if (!isset($datos["vigilanciasuperfinanciera"])) {
            $datos["vigilanciasuperfinanciera"] = '';
        }

        if (!isset($datos["fechavencimiento"])) {
            $datos["fechavencimiento"] = '';
        }

        if (!isset($datos["fechavencimiento1"])) {
            $datos["fechavencimiento1"] = '';
        }

        if (!isset($datos["fechavencimiento2"])) {
            $datos["fechavencimiento2"] = '';
        }

        if (!isset($datos["fechavencimiento3"])) {
            $datos["fechavencimiento3"] = '';
        }

        if (!isset($datos["fechavencimiento4"])) {
            $datos["fechavencimiento4"] = '';
        }

        if (!isset($datos["fechavencimiento5"])) {
            $datos["fechavencimiento5"] = '';
        }

        if (!isset($datos["codrespotri"])) {
            $datos["codrespotri"] = array();
        }

        $datos["empsoccategorias"] = '';
        foreach ($datos as $key => $valor) {
            if (substr($key, 0, 10) == 'empsoccat_') {
                if ($valor == 'S') {
                    if ($datos["empsoccategorias"] !== '') {
                        $datos["empsoccategorias"] .= ',';
                    }
                    $datos["empsoccategorias"] .= $key;
                }
            }
        }

        $datos["empsocbeneficiarios"] = '';
        foreach ($datos as $key => $valor) {
            if (substr($key, 0, 10) == 'empsocben_') {
                if ($valor == 'S') {
                    if ($datos["empsocbeneficiarios"] !== '') {
                        $datos["empsocbeneficiarios"] .= ',';
                    }
                    $datos["empsocbeneficiarios"] .= $key;
                }
            }
        }

        if (!isset($datos["condiespe2219"])) {
            $datos["condiespe2219"] = '';
        }

        if (trim((string) $datos["nombre"]) == '') {
            if ($datos["organizacion"] == '01') {
                $datos["nombre"] = trim((string) $datos["ape1"]);
                if (trim((string) $datos["ape2"]) != '') {
                    $datos["nombre"] .= ' ' . trim((string) $datos["ape2"]);
                }
                if (trim((string) $datos["nom1"]) != '') {
                    $datos["nombre"] .= ' ' . trim((string) $datos["nom1"]);
                }
                if (trim((string) $datos["nom2"]) != '') {
                    $datos["nombre"] .= ' ' . trim((string) $datos["nom2"]);
                }
            }
        }

        // Datos basicos
        $xml .= '<cc>' . $datos["cc"] . '</cc>';
        $xml .= '<matricula>' . ltrim((string) $datos["matricula"], '0') . '</matricula>';
        $xml .= '<proponente>' . sprintf("%08s", $datos["proponente"]) . '</proponente>';
        if ($reemplazar == 'si') {
            $xml .= '<nombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["nombre"]) . ']]></nombre>';
            $xml .= '<nuevonombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["nuevonombre"]) . ']]></nuevonombre>';
            $xml .= '<ape1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["ape1"]) . ']]></ape1>';
            $xml .= '<ape2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["ape2"]) . ']]></ape2>';
            $xml .= '<nom1><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["nom1"]) . ']]></nom1>';
            $xml .= '<nom2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["nom2"]) . ']]></nom2>';
            $xml .= '<sigla><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["sigla"]) . ']]></sigla>';
        } else {
            $xml .= '<nombre><![CDATA[' . $datos["nombre"] . ']]></nombre>';
            $xml .= '<nuevonombre><![CDATA[' . $datos["nuevonombre"] . ']]></nuevonombre>';
            $xml .= '<ape1><![CDATA[' . $datos["ape1"] . ']]></ape1>';
            $xml .= '<ape2><![CDATA[' . $datos["ape2"] . ']]></ape2>';
            $xml .= '<nom1><![CDATA[' . $datos["nom1"] . ']]></nom1>';
            $xml .= '<nom2><![CDATA[' . $datos["nom2"] . ']]></nom2>';
            $xml .= '<sigla><![CDATA[' . $datos["sigla"] . ']]></sigla>';
        }
        $xml .= '<nombrebase64>' . $datos["nombrebase64"] . '</nombrebase64>';
        $xml .= '<siglabase64>' . $datos["siglabase64"] . '</siglabase64>';
        $xml .= '<complementorazonsocial><![CDATA[' . $datos["complementorazonsocial"] . ']]></complementorazonsocial>';
        $xml .= '<tipoidentificacion>' . $datos["tipoidentificacion"] . '</tipoidentificacion>';
        if (ltrim((string) $datos["identificacion"], "0") == '') {
            $xml .= '<identificacion></identificacion>';
        } else {
            $xml .= '<identificacion>' . sprintf("%011s", $datos["identificacion"]) . '</identificacion>';
        }
        $xml .= '<sexo>' . $datos["sexo"] . '</sexo>';
        $xml .= '<etnia>' . $datos["etnia"] . '</etnia>';
        $xml .= '<emprendimientosocial>' . $datos["emprendimientosocial"] . '</emprendimientosocial>';
        $xml .= '<empsoccategorias><![CDATA[' . $datos["empsoccategorias"] . ']]></empsoccategorias>';
        $xml .= '<empsoccategorias_otros><![CDATA[' . $datos["empsoccategorias_otros"] . ']]></empsoccategorias_otros>';
        $xml .= '<empsocbeneficiarios><![CDATA[' . $datos["empsocbeneficiarios"] . ']]></empsocbeneficiarios>';
        $xml .= '<empsocbeneficiarios_otros><![CDATA[' . $datos["empsocbeneficiarios_otros"] . ']]></empsocbeneficiarios_otros>';
        if (ltrim((string) $datos["ideext"], "0") == '') {
            $xml .= '<ideext></ideext>';
        } else {
            $xml .= '<ideext>' . sprintf("%015s", $datos["ideext"]) . '</ideext>';
        }
        $xml .= '<idmunidoc>' . $datos["idmunidoc"] . '</idmunidoc>';
        $xml .= '<fechanacimiento>' . $datos["fechanacimiento"] . '</fechanacimiento>';
        $xml .= '<fecexpdoc>' . $datos["fecexpdoc"] . '</fecexpdoc>';
        $xml .= '<paisexpdoc>' . $datos["paisexpdoc"] . '</paisexpdoc>';
        $xml .= '<nit>' . sprintf("%015s", $datos["nit"]) . '</nit>';
        $xml .= '<prerut>' . $datos["prerut"] . '</prerut>';
        $xml .= '<admondian>' . $datos["admondian"] . '</admondian>';
        $xml .= '<estadonit>' . $datos["estadonit"] . '</estadonit>';
        $xml .= '<expedienteinactivo>' . $datos["expedienteinactivo"] . '</expedienteinactivo>';
        $xml .= '<nacionalidad><![CDATA[' . $datos["nacionalidad"] . ']]></nacionalidad>';
        $xml .= '<fechamatricula>' . $datos["fechamatricula"] . '</fechamatricula>';

//
        $xml .= '<fecmatant>' . $datos["fecmatant"] . '</fecmatant>';
        $xml .= '<fecrenant>' . $datos["fecrenant"] . '</fecrenant>';
        $xml .= '<camant>' . $datos["camant"] . '</camant>';
        $xml .= '<munant>' . $datos["munant"] . '</munant>';
        $xml .= '<benart7ant>' . $datos["benart7ant"] . '</benart7ant>';
        $xml .= '<ultanorenant>' . $datos["ultanorenant"] . '</ultanorenant>';
        $xml .= '<matant>' . $datos["matant"] . '</matant>';

//
        $xml .= '<fecharenovacion>' . $datos["fecharenovacion"] . '</fecharenovacion>';
        $xml .= '<fechaconstitucion>' . ltrim((string) $datos["fechaconstitucion"], "0") . '</fechaconstitucion>';
        $xml .= '<fechavencimiento>' . ltrim((string) $datos["fechavencimiento"], "0") . '</fechavencimiento>';
        $xml .= '<fechavencimiento1>' . ltrim((string) $datos["fechavencimiento1"], "0") . '</fechavencimiento1>';
        $xml .= '<fechavencimiento2>' . ltrim((string) $datos["fechavencimiento2"], "0") . '</fechavencimiento2>';
        $xml .= '<fechavencimiento3>' . ltrim((string) $datos["fechavencimiento3"], "0") . '</fechavencimiento3>';
        $xml .= '<fechavencimiento4>' . ltrim((string) $datos["fechavencimiento4"], "0") . '</fechavencimiento4>';
        $xml .= '<fechavencimiento5>' . ltrim((string) $datos["fechavencimiento5"], "0") . '</fechavencimiento5>';

        $xml .= '<fechacancelacion>' . ltrim((string) $datos["fechacancelacion"], "0") . '</fechacancelacion>';
        $xml .= '<motivocancelacion>' . $datos["motivocancelacion"] . '</motivocancelacion>';
        $xml .= '<estadotipoliquidacion>' . $datos["estadotipoliquidacion"] . '</estadotipoliquidacion>';

        $xml .= '<ultanoren>' . $datos["ultanoren"] . '</ultanoren>';
        $xml .= '<estadomatricula>' . $datos["estadomatricula"] . '</estadomatricula>';
        $xml .= '<estadoproponente>' . $datos["estadoproponente"] . '</estadoproponente>';
        $xml .= '<estadoactiva>' . $datos["estadoactiva"] . '</estadoactiva>';
        $xml .= '<estadopreoperativa>' . $datos["estadopreoperativa"] . '</estadopreoperativa>';
        $xml .= '<estadoconcordato>' . $datos["estadoconcordato"] . '</estadoconcordato>';
        $xml .= '<estadointervenida>' . $datos["estadointervenida"] . '</estadointervenida>';
        $xml .= '<estadodisuelta>' . $datos["estadodisuelta"] . '</estadodisuelta>';
        $xml .= '<estadoreestructuracion>' . $datos["estadoreestructuracion"] . '</estadoreestructuracion>';
        $xml .= '<estadodatosmatricula>' . $datos["estadodatosmatricula"] . '</estadodatosmatricula>';
        $xml .= '<ctrcertificardesde>' . $datos["certificardesde"] . '</ctrcertificardesde>';
        $xml .= '<tamanoempresa>' . $datos["tamanoempresa"] . '</tamanoempresa>';
        $xml .= '<emprendedor28>' . $datos["emprendedor28"] . '</emprendedor28>';
        $xml .= '<pemprendedor28>' . $datos["pemprendedor28"] . '</pemprendedor28>';
        $xml .= '<empresafamiliar>' . $datos["empresafamiliar"] . '</empresafamiliar>';
        $xml .= '<procesosinnovacion>' . $datos["procesosinnovacion"] . '</procesosinnovacion>';
        $xml .= '<estadocapturado>' . $datos["estadocapturado"] . '</estadocapturado>';
        $xml .= '<estadocapturadootros>' . $datos["estadocapturadootros"] . '</estadocapturadootros>';
        $xml .= '<cantest>' . $datos["cantest"] . '</cantest>';
        $xml .= '<idetripaiori>' . $datos["idetripaiori"] . '</idetripaiori>';
        $xml .= '<paiori>' . $datos["paiori"] . '</paiori>';
        $xml .= '<idetriextep>' . $datos["idetriextep"] . '</idetriextep>';
        $xml .= '<vigilanciasuperfinanciera>' . $datos["vigilanciasuperfinanciera"] . '</vigilanciasuperfinanciera>';

        $xml .= '<organizacion>' . $datos["organizacion"] . '</organizacion>';
        $xml .= '<categoria>' . $datos["categoria"] . '</categoria>';
        $xml .= '<naturaleza>' . $datos["naturaleza"] . '</naturaleza>';
        $xml .= '<imppredil>' . $datos["imppredil"] . '</imppredil>';

//
        $xml .= '<tipogruemp>' . $datos["tipogruemp"] . '</tipogruemp>';
        $xml .= '<nombregruemp>' . $datos["nombregruemp"] . '</nombregruemp>';
        $xml .= '<impexp>' . $datos["impexp"] . '</impexp>';
        $xml .= '<tipopropiedad>' . $datos["tipopropiedad"] . '</tipopropiedad>';
        $xml .= '<tipolocal>' . $datos["tipolocal"] . '</tipolocal>';

// Informacion de ESADL
        $xml .= '<vigcontrol>' . $datos["vigcontrol"] . '</vigcontrol>';
        $xml .= '<fecperj>' . $datos["fecperj"] . '</fecperj>';
        $xml .= '<idorigenperj>' . $datos["idorigenperj"] . '</idorigenperj>';
        if (isset($datos["origendocconst"])) {
            $xml .= '<origendocconst>' . $datos["origendocconst"] . '</origendocconst>';
        } else {
            $xml .= '<origendocconst></origendocconst>';
        }
        $xml .= '<numperj>' . $datos["numperj"] . '</numperj>';
        $xml .= '<vigifecini>' . $datos["vigifecini"] . '</vigifecini>';
        $xml .= '<vigifecfin>' . $datos["vigifecfin"] . '</vigifecfin>';
        $xml .= '<patrimonio>' . $datos["patrimonio"] . '</patrimonio>';
        $xml .= '<clasegenesadl>' . $datos["clasegenesadl"] . '</clasegenesadl>';
        $xml .= '<claseespesadl>' . $datos["claseespesadl"] . '</claseespesadl>';
        $xml .= '<claseeconsoli>' . $datos["claseeconsoli"] . '</claseeconsoli>';
        $xml .= '<condiespe2219>' . $datos["condiespe2219"] . '</condiespe2219>';

        $xml .= '<ctrderpub>' . $datos["ctrderpub"] . '</ctrderpub>';
        $xml .= '<ctrcodcoop>' . $datos["ctrcodcoop"] . '</ctrcodcoop>';
        $xml .= '<ctrcodotras>' . $datos["ctrcodotras"] . '</ctrcodotras>';
        $xml .= '<ctresacntasociados>' . $datos["ctresacntasociados"] . '</ctresacntasociados>';
        $xml .= '<ctresacntmujeres>' . $datos["ctresacntmujeres"] . '</ctresacntmujeres>';
        $xml .= '<ctresacnthombres>' . $datos["ctresacnthombres"] . '</ctresacnthombres>';
        $xml .= '<ctresapertgremio>' . $datos["ctresapertgremio"] . '</ctresapertgremio>';
        $xml .= '<ctresagremio><![CDATA[' . $datos["ctresagremio"] . ']]></ctresagremio>';
        $xml .= '<ctresaacredita><![CDATA[' . $datos["ctresaacredita"] . ']]></ctresaacredita>';
        $xml .= '<ctresaivc><![CDATA[' . $datos["ctresaivc"] . ']]></ctresaivc>';
        $xml .= '<ctresainfoivc>' . $datos["ctresainfoivc"] . '</ctresainfoivc>';
        $xml .= '<ctresaautregistro>' . $datos["ctresaautregistro"] . '</ctresaautregistro>';
        $xml .= '<ctresaentautoriza><![CDATA[' . $datos["ctresaentautoriza"] . ']]></ctresaentautoriza>';
        $xml .= '<ctresacodnat>' . $datos["ctresacodnat"] . '</ctresacodnat>';
        $xml .= '<ctresadiscap>' . $datos["ctresadiscap"] . '</ctresadiscap>';
        $xml .= '<ctresaetnia>' . $datos["ctresaetnia"] . '</ctresaetnia>';
        $xml .= '<ctresacualetnia><![CDATA[' . $datos["ctresacualetnia"] . ']]></ctresacualetnia>';
        $xml .= '<ctresadespvictreins>' . $datos["ctresadespvictreins"] . '</ctresadespvictreins>';
        $xml .= '<ctresacualdespvictreins><![CDATA[' . $datos["ctresacualdespvictreins"] . ']]></ctresacualdespvictreins>';
        $xml .= '<ctresaindgest>' . $datos["ctresaindgest"] . '</ctresaindgest>';
        $xml .= '<ctresalgbti>' . $datos["ctresalgbti"] . '</ctresalgbti>';

//Datos de afiliacion
        $xml .= '<afiliado>' . $datos["afiliado"] . '</afiliado>';
        $xml .= '<fechaafiliacion>' . $datos["fechaafiliacion"] . '</fechaafiliacion>';
        $xml .= '<ultanorenafi>' . $datos["ultanorenafi"] . '</ultanorenafi>';
        $xml .= '<fechaultpagoafi>' . $datos["fechaultpagoafi"] . '</fechaultpagoafi>';
        $xml .= '<valorultpagoafi>' . $datos["valorultpagoafi"] . '</valorultpagoafi>';
        (isset($datos["saldoafiliado"])) ? $xml .= '<saldoafiliado>' . $datos["saldoafiliado"] . '</saldoafiliado>' : $xml .= '<saldoafiliado></saldoafiliado>';
        (isset($datos["telaflia"])) ? $xml .= '<telaflia>' . $datos["telaflia"] . '</telaflia>' : $xml .= '<telaflia></telaflia>';
        (isset($datos["diraflia"])) ? $xml .= '<diraflia>' . str_replace("#", "Nro.", $datos["diraflia"]) . '</diraflia>' : $xml .= '<diraflia></diraflia>';
        (isset($datos["munaflia"])) ? $xml .= '<munaflia>' . $datos["munaflia"] . '</munaflia>' : $xml .= '<munaflia></munaflia>';
        (isset($datos["profaflia"])) ? $xml .= '<profaflia>' . $datos["profaflia"] . '</profaflia>' : $xml .= '<profaflia></profaflia>';
        (isset($datos["contaflia"])) ? $xml .= '<contaflia>' . $datos["contaflia"] . '</contaflia>' : $xml .= '<contaflia></contaflia>';
        (isset($datos["dircontaflia"])) ? $xml .= '<dircontaflia>' . str_replace("#", "Nro.", $datos["dircontaflia"]) . '</dircontaflia>' : $xml .= '<dircontaflia></dircontaflia>';
        (isset($datos["muncontaflia"])) ? $xml .= '<muncontaflia>' . $datos["muncontaflia"] . '</muncontaflia>' : $xml .= '<muncontaflia></muncontaflia>';
        (isset($datos["numactaaflia"])) ? $xml .= '<numactaaflia>' . $datos["numactaaflia"] . '</numactaaflia>' : $xml .= '<numactaaflia></numactaaflia>';
        (isset($datos["fecactaaflia"])) ? $xml .= '<fecactaaflia>' . $datos["fecactaaflia"] . '</fecactaaflia>' : $xml .= '<fecactaaflia></fecactaaflia>';
        (isset($datos["numactaafliacan"])) ? $xml .= '<numactaafliacan>' . $datos["numactaafliacan"] . '</numactaafliacan>' : $xml .= '<numactaafliacan></numactaafliacan>';
        (isset($datos["fecactaafliacan"])) ? $xml .= '<fecactaafliacan>' . $datos["fecactaafliacan"] . '</fecactaafliacan>' : $xml .= '<fecactaafliacan></fecactaafliacan>';
        (isset($datos["fecexafiliacion"])) ? $xml .= '<fecexafiliacion>' . $datos["fecexafiliacion"] . '</fecexafiliacion>' : $xml .= '<fecexafiliacion></fecexafiliacion>';

        if (isset($datos["periodicoafiliados"])) {
            foreach ($datos["periodicoafiliados"] as $pagafi) {
                $xml .= '<pagosafiliado>';
                $xml .= '<perafiano>' . $pagafi["ano"] . '</perafiano>';
                $xml .= '<perafifecha>' . $pagafi["fecha"] . '</perafifecha>';
                $xml .= '<perafitipo>' . $pagafi["tipo"] . '</perafitipo>';
                $xml .= '<perafirecibo>' . $pagafi["recibo"] . '</perafirecibo>';
                $xml .= '<perafivalor>' . sprintf("%015s", str_replace(array(",", "."), "", $pagafi["valor"])) . '</perafivalor>';
                $xml .= '</pagosafiliado>';
            }
        }

// Datos de ubicaci&oacute;n
        $xml .= '<lggr>' . $datos["lggr"] . '</lggr>';
        $xml .= '<nombrecomercial><![CDATA[' . $datos["nombrecomercial"] . ']]></nombrecomercial>';
        if ($reemplazar == 'si') {
            $xml .= '<dircom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(str_replace("#", "Nro.", $datos["dircom"])) . ']]></dircom>';
        } else {
            $xml .= '<dircom><![CDATA[' . str_replace("#", "Nro.", $datos["dircom"]) . ']]></dircom>';
        }
        $xml .= '<dircom_tipovia><![CDATA[' . $datos["dircom_tipovia"] . ']]></dircom_tipovia>';
        $xml .= '<dircom_numvia><![CDATA[' . $datos["dircom_numvia"] . ']]></dircom_numvia>';
        $xml .= '<dircom_apevia><![CDATA[' . $datos["dircom_apevia"] . ']]></dircom_apevia>';
        $xml .= '<dircom_orivia><![CDATA[' . $datos["dircom_orivia"] . ']]></dircom_orivia>';
        $xml .= '<dircom_numcruce><![CDATA[' . $datos["dircom_numcruce"] . ']]></dircom_numcruce>';
        $xml .= '<dircom_apecruce><![CDATA[' . $datos["dircom_apecruce"] . ']]></dircom_apecruce>';
        $xml .= '<dircom_oricruce><![CDATA[' . $datos["dircom_oricruce"] . ']]></dircom_oricruce>';
        $xml .= '<dircom_numplaca><![CDATA[' . $datos["dircom_numplaca"] . ']]></dircom_numplaca>';
        if ($reemplazar == 'si') {
            $xml .= '<dircom_complemento><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["dircom_complemento"]) . ']]></dircom_complemento>';
        } else {
            $xml .= '<dircom_complemento><![CDATA[' . $datos["dircom_complemento"] . ']]></dircom_complemento>';
        }
        $xml .= '<muncom>' . $datos["muncom"] . '</muncom>';
        $xml .= '<paicom>' . $datos["paicom"] . '</paicom>';
        $xml .= '<telcom1>' . $datos["telcom1"] . '</telcom1>';
        $xml .= '<telcom2>' . $datos["telcom2"] . '</telcom2>';
        $xml .= '<celcom>' . $datos["celcom"] . '</celcom>';
        $xml .= '<faxcom>' . $datos["faxcom"] . '</faxcom>';
        $xml .= '<aacom>' . $datos["aacom"] . '</aacom>';
        $xml .= '<zonapostalcom>' . $datos["zonapostalcom"] . '</zonapostalcom>';
        $xml .= '<barriocom>' . $datos["barriocom"] . '</barriocom>';
        $xml .= '<numpredial>' . $datos["numpredial"] . '</numpredial>';
        if ($reemplazar == 'si') {
            $xml .= '<emailcom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["emailcom"]) . ']]></emailcom>';
            $xml .= '<emailcom2><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["emailcom2"]) . ']]></emailcom2>';
            $xml .= '<emailcom3><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["emailcom3"]) . ']]></emailcom3>';
            $xml .= '<urlcom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["urlcom"]) . ']]></urlcom>';
        } else {
            $xml .= '<emailcom><![CDATA[' . $datos["emailcom"] . ']]></emailcom>';
            $xml .= '<emailcom2><![CDATA[' . $datos["emailcom2"] . ']]></emailcom2>';
            $xml .= '<emailcom3><![CDATA[' . $datos["emailcom3"] . ']]></emailcom3>';
            $xml .= '<urlcom><![CDATA[' . $datos["urlcom"] . ']]></urlcom>';
        }
        $xml .= '<codigopostalcom>' . $datos["codigopostalcom"] . '</codigopostalcom>';
        $xml .= '<codigozonacom>' . $datos["codigozonacom"] . '</codigozonacom>';

//
        if ($reemplazar == 'si') {
            $xml .= '<nombresegundocontacto><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["nombresegundocontacto"]) . ']]></nombresegundocontacto>';
            $xml .= '<cargosegundocontacto><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["cargosegundocontacto"]) . ']]></cargosegundocontacto>';
        } else {
            $xml .= '<nombresegundocontacto><![CDATA[' . $datos["nombresegundocontacto"] . ']]></nombresegundocontacto>';
            $xml .= '<cargosegundocontacto><![CDATA[' . $datos["cargosegundocontacto"] . ']]></cargosegundocontacto>';
        }

// Datos de ubicaci&oacute;n geogr&aacute;fica (latitud y longitud)
        if (isset($datos["latitudgrados"])) {
            $xml .= '<latitudgrados>' . $datos["latitudgrados"] . '</latitudgrados>';
        }
        if (isset($datos["latitudminutos"])) {
            $xml .= '<latitudminutos>' . $datos["latitudminutos"] . '</latitudminutos>';
        }
        if (isset($datos["latitudsegundos"])) {
            $xml .= '<latitudsegundos>' . $datos["latitudsegundos"] . '</latitudsegundos>';
        }
        if (isset($datos["latitudorientacion"])) {
            $xml .= '<latitudorientacion>' . $datos["latitudorientacion"] . '</latitudorientacion>';
        }
        if (isset($datos["longitudgrados"])) {
            $xml .= '<longitudgrados>' . $datos["longitudgrados"] . '</longitudgrados>';
        }
        if (isset($datos["longitudminutos"])) {
            $xml .= '<longitudminutos>' . $datos["longitudminutos"] . '</longitudminutos>';
        }
        if (isset($datos["longitudsegundos"])) {
            $xml .= '<longitudsegundos>' . $datos["longitudsegundos"] . '</longitudsegundos>';
        }
        if (isset($datos["longitudorientacion"])) {
            $xml .= '<longitudorientacion>' . $datos["longitudorientacion"] . '</longitudorientacion>';
        }

// Direcci&oacute;n de notificaci&oacute;n
        if ($reemplazar == 'si') {
            $xml .= '<dirnot><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(str_replace("#", "Nro.", $datos["dirnot"])) . ']]></dirnot>';
        } else {
            $xml .= '<dirnot><![CDATA[' . str_replace("#", "Nro.", $datos["dirnot"]) . ']]></dirnot>';
        }
        $xml .= '<dirnot_tipovia><![CDATA[' . $datos["dirnot_tipovia"] . ']]></dirnot_tipovia>';
        $xml .= '<dirnot_numvia><![CDATA[' . $datos["dirnot_numvia"] . ']]></dirnot_numvia>';
        $xml .= '<dirnot_apevia><![CDATA[' . $datos["dirnot_apevia"] . ']]></dirnot_apevia>';
        $xml .= '<dirnot_orivia><![CDATA[' . $datos["dirnot_orivia"] . ']]></dirnot_orivia>';
        $xml .= '<dirnot_numcruce><![CDATA[' . $datos["dirnot_numcruce"] . ']]></dirnot_numcruce>';
        $xml .= '<dirnot_apecruce><![CDATA[' . $datos["dirnot_apecruce"] . ']]></dirnot_apecruce>';
        $xml .= '<dirnot_oricruce><![CDATA[' . $datos["dirnot_oricruce"] . ']]></dirnot_oricruce>';
        $xml .= '<dirnot_numplaca><![CDATA[' . $datos["dirnot_numplaca"] . ']]></dirnot_numplaca>';
        if ($reemplazar == 'si') {
            $xml .= '<dirnot_complemento><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["dirnot_complemento"]) . ']]></dirnot_complemento>';
        } else {
            $xml .= '<dirnot_complemento><![CDATA[' . $datos["dirnot_complemento"] . ']]></dirnot_complemento>';
        }
        $xml .= '<munnot>' . $datos["munnot"] . '</munnot>';
        $xml .= '<painot>' . $datos["painot"] . '</painot>';
        $xml .= '<telnot>' . $datos["telnot"] . '</telnot>';
        $xml .= '<telnot2>' . $datos["telnot2"] . '</telnot2>';
        $xml .= '<celnot>' . $datos["celnot"] . '</celnot>';
        $xml .= '<faxnot>' . $datos["faxnot"] . '</faxnot>';
        $xml .= '<aanot>' . $datos["aanot"] . '</aanot>';
        $xml .= '<zonapostalnot>' . $datos["zonapostalnot"] . '</zonapostalnot>';
        $xml .= '<barrionot>' . $datos["barrionot"] . '</barrionot>';
        if ($reemplazar == 'si') {
            $xml .= '<emailnot><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["emailnot"]) . ']]></emailnot>';
            $xml .= '<urlnot><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["urlnot"]) . ']]></urlnot>';
            $xml .= '<dircor><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["dircor"]) . ']]></dircor>';
        } else {
            $xml .= '<emailnot><![CDATA[' . $datos["emailnot"] . ']]></emailnot>';
            $xml .= '<urlnot><![CDATA[' . $datos["urlnot"] . ']]></urlnot>';
            $xml .= '<dircor><![CDATA[' . $datos["dircor"] . ']]></dircor>';
        }
        $xml .= '<telcor><![CDATA[' . $datos["telcor"] . ']]></telcor>';
        $xml .= '<muncor><![CDATA[' . $datos["muncor"] . ']]></muncor>';
        $xml .= '<codigopostalnot>' . $datos["codigopostalnot"] . '</codigopostalnot>';
        $xml .= '<codigozonanot>' . $datos["codigozonanot"] . '</codigozonanot>';
        $xml .= '<tiposedeadm>' . $datos["tiposedeadm"] . '</tiposedeadm>';

        // Ciius
        $xml .= '<ciius>';
        if (trim((string) $datos["ciius"][1]) != '') {
            $xml .= '<ciiu>' . $datos["ciius"][1] . '</ciiu>';
        }
        if (trim((string) $datos["ciius"][2]) != '') {
            $xml .= '<ciiu>' . $datos["ciius"][2] . '</ciiu>';
        }
        if (trim((string) $datos["ciius"][3]) != '') {
            $xml .= '<ciiu>' . $datos["ciius"][3] . '</ciiu>';
        }
        if (trim((string) $datos["ciius"][4]) != '') {
            $xml .= '<ciiu>' . $datos["ciius"][4] . '</ciiu>';
        }
        $xml .= '</ciius>';

        //
        if (trim((string) $datos["versionciiu"]) != '') {
            $xml .= '<versionciiu>' . $datos["versionciiu"] . '</versionciiu>';
        } else {
            $xml .= '<versionciiu></versionciiu>';
        }

        // shd
        $xml .= '<shds>';
        if (trim((string) $datos["shd"][1]) != '') {
            $xml .= '<shd>' . $datos["shd"][1] . '</shd>';
        }
        if (trim((string) $datos["shd"][2]) != '') {
            $xml .= '<shd>' . $datos["shd"][2] . '</shd>';
        }
        if (trim((string) $datos["shd"][3]) != '') {
            $xml .= '<shd>' . $datos["shd"][3] . '</shd>';
        }
        if (trim((string) $datos["shd"][4]) != '') {
            $xml .= '<shd>' . $datos["shd"][4] . '</shd>';
        }
        $xml .= '</shds>';

        //
        $xml .= '<feciniact1>' . $datos["feciniact1"] . '</feciniact1>';
        $xml .= '<feciniact2>' . $datos["feciniact2"] . '</feciniact2>';

        //
        $tcodrespotri = '';
        if (!empty($datos["codrespotri"])) {
            foreach ($datos["codrespotri"] as $r1) {
                if (trim((string) $r1) != '') {
                    if (trim((string) $tcodrespotri) != '') {
                        $tcodrespotri .= ',';
                    }
                    $tcodrespotri .= trim((string) $r1);
                }
            }
        }
        $xml .= '<codrespotri>' . $tcodrespotri . '</codrespotri>';

        //
        $xml .= '<codaduaneros>' . $datos["codaduaneros"] . '</codaduaneros>';
        if (!isset($datos["ingesperados"]) || doubleval($datos["ingesperados"]) == 0) {
            $datos["ingesperados"] = 0;
        }
        $xml .= '<ingesperados>' . $datos["ingesperados"] . '</ingesperados>';
        $xml .= '<gruponiif>' . $datos["gruponiif"] . '</gruponiif>';
        $xml .= '<niifconciliacion>' . $datos["niifconciliacion"] . '</niifconciliacion>';
        $xml .= '<aportantesegsocial>' . $datos["aportantesegsocial"] . '</aportantesegsocial>';
        $xml .= '<tipoaportantesegsocial>' . $datos["tipoaportantesegsocial"] . '</tipoaportantesegsocial>';
        if (isset($datos["desactiv"])) {
            $xml .= '<desactiv>' . trim((string) $datos["desactiv"]) . '</desactiv>';
        } else {
            $xml .= '<desactiv></desactiv>';
        }

// Porcentajes de capitales
        if (isset($datos["cap_porcnaltot"])) {
            $xml .= '<cap_porcnaltot>' . $datos["cap_porcnaltot"] . '</cap_porcnaltot>';
        } else {
            $xml .= '<cap_porcnaltot></cap_porcnaltot>';
        }
        if (isset($datos["cap_porcnalpri"])) {
            $xml .= '<cap_porcnalpri>' . $datos["cap_porcnalpri"] . '</cap_porcnalpri>';
        } else {
            $xml .= '<cap_porcnalpri></cap_porcnalpri>';
        }
        if (isset($datos["cap_porcnalpub"])) {
            $xml .= '<cap_porcnalpub>' . $datos["cap_porcnalpub"] . '</cap_porcnalpub>';
        } else {
            $xml .= '<cap_porcnalpub></cap_porcnalpub>';
        }
        if (isset($datos["cap_porcexttot"])) {
            $xml .= '<cap_porcexttot>' . $datos["cap_porcexttot"] . '</cap_porcexttot>';
        } else {
            $xml .= '<cap_porcexttot></cap_porcexttot>';
        }
        if (isset($datos["cap_porcextpri"])) {
            $xml .= '<cap_porcextpri>' . $datos["cap_porcextpri"] . '</cap_porcextpri>';
        } else {
            $xml .= '<cap_porcextpri></cap_porcextpri>';
        }
        if (isset($datos["cap_porcextpub"])) {
            $xml .= '<cap_porcextpub>' . $datos["cap_porcextpub"] . '</cap_porcextpub>';
        } else {
            $xml .= '<cap_porcextpub></cap_porcextpub>';
        }

        if (!isset($datos["cap_apolab"])) {
            $datos["cap_apolab"] = 0;
        }
        if (!isset($datos["cap_apolabadi"])) {
            $datos["cap_apolabadi"] = 0;
        }
        if (!isset($datos["cap_apodin"])) {
            $datos["cap_apodin"] = 0;
        }
        if (!isset($datos["cap_apoact"])) {
            $datos["cap_apoact"] = 0;
        }
        $xml .= '<cap_apolab>' . $datos["cap_apolab"] . '</cap_apolab>';
        $xml .= '<cap_apolabadi>' . $datos["cap_apolabadi"] . '</cap_apolabadi>';
        $xml .= '<cap_apodin>' . $datos["cap_apodin"] . '</cap_apodin>';
        $xml .= '<cap_apoact>' . $datos["cap_apoact"] . '</cap_apoact>';
        if (isset($datos["cntestab01"])) {
            $xml .= '<cntestab01>' . $datos["cntestab01"] . '</cntestab01>';
            $xml .= '<cntestab02>' . $datos["cntestab02"] . '</cntestab02>';
            $xml .= '<cntestab03>' . $datos["cntestab03"] . '</cntestab03>';
            $xml .= '<cntestab04>' . $datos["cntestab04"] . '</cntestab04>';
            $xml .= '<cntestab05>' . $datos["cntestab05"] . '</cntestab05>';
            $xml .= '<cntestab06>' . $datos["cntestab06"] . '</cntestab06>';
            $xml .= '<cntestab07>' . $datos["cntestab07"] . '</cntestab07>';
            $xml .= '<cntestab08>' . $datos["cntestab08"] . '</cntestab08>';
            $xml .= '<cntestab09>' . $datos["cntestab09"] . '</cntestab09>';
            $xml .= '<cntestab10>' . $datos["cntestab10"] . '</cntestab10>';
            $xml .= '<cntestab11>' . $datos["cntestab11"] . '</cntestab11>';
        } else {
            $xml .= '<cntestab01></cntestab01>';
            $xml .= '<cntestab02></cntestab02>';
            $xml .= '<cntestab03></cntestab03>';
            $xml .= '<cntestab04></cntestab04>';
            $xml .= '<cntestab05></cntestab05>';
            $xml .= '<cntestab06></cntestab06>';
            $xml .= '<cntestab07></cntestab07>';
            $xml .= '<cntestab08></cntestab08>';
            $xml .= '<cntestab09></cntestab09>';
            $xml .= '<cntestab10></cntestab10>';
            $xml .= '<cntestab11></cntestab11>';
        }

// Referencias
        $xml .= '<refcrenom1><![CDATA[' . $datos["refcrenom1"] . ']]></refcrenom1>';
        $xml .= '<refcreofi1><![CDATA[' . $datos["refcreofi1"] . ']]></refcreofi1>';
        $xml .= '<refcretel1><![CDATA[' . $datos["refcretel1"] . ']]></refcretel1>';
        $xml .= '<refcrenom2><![CDATA[' . $datos["refcrenom2"] . ']]></refcrenom2>';
        $xml .= '<refcreofi2><![CDATA[' . $datos["refcreofi2"] . ']]></refcreofi2>';
        $xml .= '<refcretel2><![CDATA[' . $datos["refcretel2"] . ']]></refcretel2>';
        $xml .= '<refcomnom1><![CDATA[' . $datos["refcomnom1"] . ']]></refcomnom1>';
        $xml .= '<refcomdir1><![CDATA[' . $datos["refcomdir1"] . ']]></refcomdir1>';
        $xml .= '<refcomtel1>' . $datos["refcomtel1"] . '</refcomtel1>';
        $xml .= '<refcomnom2><![CDATA[' . $datos["refcomnom2"] . ']]></refcomnom2>';
        $xml .= '<refcomdir2><![CDATA[' . $datos["refcomdir2"] . ']]></refcomdir2>';
        $xml .= '<refcomtel2>' . $datos["refcomtel2"] . '</refcomtel2>';

// informacion financiera historica
        if (isset($datos["hf"])) {
            foreach ($datos["hf"] as $hf) {
                if ($datos["organizacion"] == '01' ||
                        ($datos["organizacion"] > '02' && $datos["categoria"] == '1')
                ) {
                    $xml .= '<hf>';
                    $xml .= '<hf_anodatos>' . $hf["anodatos"] . '</hf_anodatos>';
                    $xml .= '<hf_feccredat>' . $hf["fechadatos"] . '</hf_feccredat>';
                    $xml .= '<hf_actcte>' . $hf["actcte"] . '</hf_actcte>';
                    $xml .= '<hf_actnocte>' . $hf["actnocte"] . '</hf_actnocte>';
                    $xml .= '<hf_actfij>' . $hf["actfij"] . '</hf_actfij>';
                    $xml .= '<hf_fijnet>' . $hf["fijnet"] . '</hf_fijnet>';
                    $xml .= '<hf_actval>' . $hf["actval"] . '</hf_actval>';
                    $xml .= '<hf_actotr>' . $hf["actotr"] . '</hf_actotr>';
                    $xml .= '<hf_acttot>' . $hf["acttot"] . '</hf_acttot>';
                    $xml .= '<hf_actsin>' . $hf["actsinaju"] . '</hf_actsin>';
                    $xml .= '<hf_invent>' . $hf["invent"] . '</hf_invent>';
                    $xml .= '<hf_pascte>' . $hf["pascte"] . '</hf_pascte>';
                    $xml .= '<hf_paslar>' . $hf["paslar"] . '</hf_paslar>';
                    $xml .= '<hf_pastot>' . $hf["pastot"] . '</hf_pastot>';
                    $xml .= '<hf_patliq>' . $hf["pattot"] . '</hf_patliq>';
                    $xml .= '<hf_paspat>' . $hf["paspat"] . '</hf_paspat>';
                    $xml .= '<hf_balsoc>' . $hf["balsoc"] . '</hf_balsoc>';
                    $xml .= '<hf_ingope>' . $hf["ingope"] . '</hf_ingope>';
                    $xml .= '<hf_ingnoope>' . $hf["ingnoope"] . '</hf_ingnoope>';
                    $xml .= '<hf_cosven>' . $hf["cosven"] . '</hf_cosven>';
                    $xml .= '<hf_gasint>' . $hf["gasint"] . '</hf_gasint>';
                    $xml .= '<hf_gasimp>' . $hf["gasimp"] . '</hf_gasimp>';
                    $xml .= '<hf_gasadm>' . $hf["gtoadm"] . '</hf_gasadm>';
                    $xml .= '<hf_gasope>' . $hf["gtoven"] . '</hf_gasope>';
                    $xml .= '<hf_utiope>' . $hf["utiope"] . '</hf_utiope>';
                    $xml .= '<hf_utinet>' . $hf["utinet"] . '</hf_utinet>';
                    $xml .= '<hf_person>' . $hf["personal"] . '</hf_person>';
                    $xml .= '<hf_pcttem>' . $hf["personaltemp"] . '</hf_pcttem>';
                    $xml .= '<hf_depamo>' . $hf["depamo"] . '</hf_depamo>';
                    $xml .= '</hf>';
                } else {
                    if ($hf["anodatos"] != '') {
                        $xml .= '<hf>';
                        $xml .= '<hf_anodatos>' . $hf["anodatos"] . '</hf_anodatos>';
                        if (isset($hf["feccredat"])) {
                            $xml .= '<hf_feccredat>' . $hf["feccredat"] . '</hf_feccredat>';
                        } else {
                            $xml .= '<hf_feccredat></hf_feccredat>';
                        }
                        if (isset($hf["valest"])) {
                            $xml .= '<hf_valest>' . $hf["actvin"] . '</hf_valest>';
                        } else {
                            $xml .= '<hf_valest></hf_valest>';
                        }
                        if (isset($hf["person"])) {
                            $xml .= '<hf_person>' . $hf["personal"] . '</hf_person>';
                        } else {
                            $xml .= '<hf_person></hf_person>';
                        }
                        $xml .= '</hf>';
                    }
                }
            }
        }

// informacion financiera
        $xml .= '<anodatosactual>' . $datos["anodatos"] . '</anodatosactual>';
        $xml .= '<fechadatosactual>' . $datos["fechadatos"] . '</fechadatosactual>';
        $xml .= '<personalactual>' . $datos["personal"] . '</personalactual>';
        $xml .= '<personaltempactual>' . $datos["personaltemp"] . '</personaltempactual>';
        $xml .= '<actvinactual>' . $datos["actvin"] . '</actvinactual>';
        $xml .= '<actcteactual>' . $datos["actcte"] . '</actcteactual>';
        $xml .= '<actnocteactual>' . $datos["actnocte"] . '</actnocteactual>';
        $xml .= '<actfijactual>' . $datos["actfij"] . '</actfijactual>';
        $xml .= '<fijnetactual>' . $datos["fijnet"] . '</fijnetactual>';
        $xml .= '<actotractual>' . $datos["actotr"] . '</actotractual>';
        $xml .= '<actvalactual>' . $datos["actval"] . '</actvalactual>';
        $xml .= '<acttotactual>' . $datos["acttot"] . '</acttotactual>';
        $xml .= '<actsinajuactual>' . $datos["acttot"] . '</actsinajuactual>';
        $xml .= '<inventactual>' . $datos["invent"] . '</inventactual>';
        $xml .= '<pascteactual>' . $datos["pascte"] . '</pascteactual>';
        $xml .= '<paslaractual>' . $datos["paslar"] . '</paslaractual>';
        $xml .= '<pastotactual>' . $datos["pastot"] . '</pastotactual>';
        $xml .= '<pattotactual>' . $datos["pattot"] . '</pattotactual>';
        $xml .= '<paspatactual>' . $datos["paspat"] . '</paspatactual>';
        $xml .= '<balsocactual>' . $datos["balsoc"] . '</balsocactual>';
        $xml .= '<ingopeactual>' . $datos["ingope"] . '</ingopeactual>';
        $xml .= '<ingnoopeactual>' . $datos["ingnoope"] . '</ingnoopeactual>';

        $xml .= '<gtovenactual>' . $datos["gtoven"] . '</gtovenactual>';
        $xml .= '<gtoadmactual>' . $datos["gtoadm"] . '</gtoadmactual>';

        $xml .= '<gasopeactual>' . $datos["gtoven"] . '</gasopeactual>';
        $xml .= '<gasnoopeactual>' . $datos["gtoadm"] . '</gasnoopeactual>';

        $xml .= '<cosvenactual>' . $datos["cosven"] . '</cosvenactual>';
        $xml .= '<depamoactual>' . $datos["depamo"] . '</depamoactual>';
        $xml .= '<gasintactual>' . $datos["gasint"] . '</gasintactual>';
        $xml .= '<gasimpactual>' . $datos["gasimp"] . '</gasimpactual>';
        $xml .= '<utiopeactual>' . $datos["utiope"] . '</utiopeactual>';
        $xml .= '<utinetactual>' . $datos["utinet"] . '</utinetactual>';

//
        $xml .= '<anodatos>' . $datos["anodatos"] . '</anodatos>';
        $xml .= '<fechadatos>' . $datos["fechadatos"] . '</fechadatos>';
        $xml .= '<personal>' . $datos["personal"] . '</personal>';
        $xml .= '<personaltemp>' . $datos["personaltemp"] . '</personaltemp>';
        $xml .= '<actvin>' . $datos["actvin"] . '</actvin>';
        $xml .= '<actcte>' . $datos["actcte"] . '</actcte>';
        $xml .= '<actnocte>' . $datos["actnocte"] . '</actnocte>';
        $xml .= '<actfij>' . $datos["actfij"] . '</actfij>';
        $xml .= '<fijnet>' . $datos["fijnet"] . '</fijnet>';
        $xml .= '<actotr>' . $datos["actotr"] . '</actotr>';
        $xml .= '<actval>' . $datos["actval"] . '</actval>';
        $xml .= '<acttot>' . $datos["acttot"] . '</acttot>';
        $xml .= '<actsinaju>' . $datos["acttot"] . '</actsinaju>';
        $xml .= '<invent>' . $datos["invent"] . '</invent>';
        $xml .= '<pascte>' . $datos["pascte"] . '</pascte>';
        $xml .= '<paslar>' . $datos["paslar"] . '</paslar>';
        $xml .= '<pastot>' . $datos["pastot"] . '</pastot>';
        $xml .= '<pattot>' . $datos["pattot"] . '</pattot>';
        $xml .= '<paspat>' . $datos["paspat"] . '</paspat>';
        $xml .= '<balsoc>' . $datos["balsoc"] . '</balsoc>';
        $xml .= '<ingope>' . $datos["ingope"] . '</ingope>';
        $xml .= '<ingnoope>' . $datos["ingnoope"] . '</ingnoope>';
        $xml .= '<gtoven>' . $datos["gtoven"] . '</gtoven>';
        $xml .= '<gtoadm>' . $datos["gtoadm"] . '</gtoadm>';

        $xml .= '<gasope>' . $datos["gtoven"] . '</gasope>';
        $xml .= '<gasnoope>' . $datos["gtoadm"] . '</gasnoope>';

        $xml .= '<cosven>' . $datos["cosven"] . '</cosven>';
        $xml .= '<depamo>' . $datos["depamo"] . '</depamo>';
        $xml .= '<gasint>' . $datos["gasint"] . '</gasint>';
        $xml .= '<gasimp>' . $datos["gasimp"] . '</gasimp>';
        $xml .= '<utiope>' . $datos["utiope"] . '</utiope>';
        $xml .= '<utinet>' . $datos["utinet"] . '</utinet>';

//    
        if (isset($datos["apolab"])) {
            $xml .= '<apolab>' . $datos["apolab"] . '</apolab>';
        }
        if (isset($datos["apolabadi"])) {
            $xml .= '<apolabadi>' . $datos["apolabadi"] . '</apolabadi>';
        }
        if (isset($datos["apoact"])) {
            $xml .= '<apoact>' . $datos["apoact"] . '</apoact>';
        }
        if (isset($datos["apodin"])) {
            $xml .= '<apodin>' . $datos["apodin"] . '</apodin>';
        }
        if (isset($datos["apotot"])) {
            $xml .= '<apotot>' . $datos["apotot"] . '</apotot>';
        }

//
        if (!isset($datos["anodatospatrimonio"])) {
            $datos["anodatospatrimonio"] = '';
        }
        if (!isset($datos["fechadatospatrimonio"])) {
            $datos["fechadatospatrimonio"] = '';
        }

        if (!isset($datos["patrimonioesadl"])) {
            $datos["patrimonioesadl"] = 0;
        }
        $xml .= '<anodatospatrimonio>' . $datos["anodatospatrimonio"] . '</anodatospatrimonio>';
        $xml .= '<fechadatospatrimonio>' . $datos["fechadatospatrimonio"] . '</fechadatospatrimonio>';
        $xml .= '<patrimonioesadl>' . $datos["patrimonioesadl"] . '</patrimonioesadl>';

        $xml .= '<cantidadmujeres>' . $datos["cantidadmujeres"] . '</cantidadmujeres>';
        $xml .= '<cantidadmujerescargosdirectivos>' . $datos["cantidadmujerescargosdirectivos"] . '</cantidadmujerescargosdirectivos>';
        $xml .= '<cantidadcargosdirectivos>' . $datos["cantidadcargosdirectivos"] . '</cantidadcargosdirectivos>';
        $xml .= '<participacionmujeres>' . $datos["participacionmujeres"] . '</participacionmujeres>';
        $xml .= '<participacionetnia>' . $datos["participacionetnia"] . '</participacionetnia>';
        $xml .= '<ciiutamanoempresarial>' . $datos["ciiutamanoempresarial"] . '</ciiutamanoempresarial>';
        $xml .= '<ingresostamanoempresarial>' . $datos["ingresostamanoempresarial"] . '</ingresostamanoempresarial>';
        $xml .= '<anodatostamanoempresarial>' . $datos["anodatostamanoempresarial"] . '</anodatostamanoempresarial>';
        $xml .= '<fechadatostamanoempresarial>' . $datos["fechadatostamanoempresarial"] . '</fechadatostamanoempresarial>';

        if (isset($datos["f"])) {
            foreach ($datos["f"] as $fin) {
                // informacion financiera
                if (!isset($fin["personaltemp"])) {
                    $fin["personaltemp"] = '';
                }
                if (!isset($fin["actvin"])) {
                    $fin["actvin"] = '0';
                }
                if (!isset($fin["actcte"])) {
                    $fin["actcte"] = '0';
                }
                if (!isset($fin["actnocte"])) {
                    $fin["actnocte"] = '0';
                }
                if (!isset($fin["actfij"])) {
                    $fin["actfij"] = '0';
                }
                if (!isset($fin["fijnet"])) {
                    $fin["fijnet"] = '0';
                }
                if (!isset($fin["actotr"])) {
                    $fin["actotr"] = '0';
                }
                if (!isset($fin["actval"])) {
                    $fin["actval"] = '0';
                }
                if (!isset($fin["acttot"])) {
                    $fin["acttot"] = '0';
                }
                if (!isset($fin["invent"])) {
                    $fin["invent"] = '0';
                }
                if (!isset($fin["pascte"])) {
                    $fin["pascte"] = '0';
                }
                if (!isset($fin["paslar"])) {
                    $fin["paslar"] = '0';
                }
                if (!isset($fin["pastot"])) {
                    $fin["pastot"] = '0';
                }
                if (!isset($fin["pattot"])) {
                    $fin["pattot"] = '0';
                }
                if (!isset($fin["paspat"])) {
                    $fin["paspat"] = '0';
                }
                if (!isset($fin["balsoc"])) {
                    $fin["balsoc"] = '0';
                }
                if (!isset($fin["ingope"])) {
                    $fin["ingope"] = '0';
                }
                if (!isset($fin["ingnoope"])) {
                    $fin["ingnoope"] = '0';
                }
                if (!isset($fin["gtoven"])) {
                    $fin["gtoven"] = '0';
                }
                if (!isset($fin["gtoadm"])) {
                    $fin["gtoadm"] = '0';
                }
                if (!isset($fin["cosven"])) {
                    $fin["cosven"] = '0';
                }
                if (!isset($fin["depamo"])) {
                    $fin["depamo"] = '0';
                }
                if (!isset($fin["gasint"])) {
                    $fin["gasint"] = '0';
                }
                if (!isset($fin["gasimp"])) {
                    $fin["gasimp"] = '0';
                }
                if (!isset($fin["utiope"])) {
                    $fin["utiope"] = '0';
                }
                if (!isset($fin["utinet"])) {
                    $fin["utinet"] = '0';
                }
                if (!isset($fin["personal"])) {
                    $fin["personal"] = '0';
                }
                if (!isset($fin["fechadatos"])) {
                    $fin["fechadatos"] = date("Ymd");
                }
                if ($fin["anodatos"] != '') {
                    $xml .= '<financieraanteriores>';
                    $xml .= '<anodatos>' . $fin["anodatos"] . '</anodatos>';
                    $xml .= '<fechadatos>' . $fin["fechadatos"] . '</fechadatos>';
                    $xml .= '<personal>' . $fin["personal"] . '</personal>';
                    $xml .= '<personaltemp>' . $fin["personaltemp"] . '</personaltemp>';
                    $xml .= '<actvin>' . $fin["actvin"] . '</actvin>';
                    $xml .= '<actcte>' . $fin["actcte"] . '</actcte>';
                    $xml .= '<actnocte>' . $fin["actnocte"] . '</actnocte>';
                    $xml .= '<actfij>' . $fin["actfij"] . '</actfij>';
                    $xml .= '<fijnet>' . $fin["fijnet"] . '</fijnet>';
                    $xml .= '<actotr>' . $fin["actotr"] . '</actotr>';
                    $xml .= '<actval>' . $fin["actval"] . '</actval>';
                    $xml .= '<acttot>' . $fin["acttot"] . '</acttot>';
                    $xml .= '<actsinaju>' . $fin["acttot"] . '</actsinaju>';
                    $xml .= '<invent>' . $fin["invent"] . '</invent>';
                    $xml .= '<pascte>' . $fin["pascte"] . '</pascte>';
                    $xml .= '<paslar>' . $fin["paslar"] . '</paslar>';
                    $xml .= '<pastot>' . $fin["pastot"] . '</pastot>';
                    $xml .= '<pattot>' . $fin["pattot"] . '</pattot>';
                    $xml .= '<paspat>' . $fin["paspat"] . '</paspat>';
                    $xml .= '<balsoc>' . $fin["balsoc"] . '</balsoc>';
                    $xml .= '<ingope>' . $fin["ingope"] . '</ingope>';
                    $xml .= '<ingnoope>' . $fin["ingnoope"] . '</ingnoope>';
                    $xml .= '<gtoven>' . $fin["gtoven"] . '</gtoven>';
                    $xml .= '<gtoadm>' . $fin["gtoadm"] . '</gtoadm>';
                    $xml .= '<cosven>' . $fin["cosven"] . '</cosven>';
                    $xml .= '<depamo>' . $fin["depamo"] . '</depamo>';
                    $xml .= '<gasint>' . $fin["gasint"] . '</gasint>';
                    $xml .= '<gasimp>' . $fin["gasimp"] . '</gasimp>';
                    $xml .= '<utiope>' . $fin["utiope"] . '</utiope>';
                    $xml .= '<utinet>' . $fin["utinet"] . '</utinet>';
                    $xml .= '</financieraanteriores>';
                }
            }
        }

//
        if (!isset($datos["benley1780"])) {
            $datos["benley1780"] = '';
        }
        if (!isset($datos["ctrdepuracion1727"])) {
            $datos["ctrdepuracion1727"] = '';
        }
        if (!isset($datos["ctrfechadepuracion1727"])) {
            $datos["ctrfechadepuracion1727"] = '';
        }

        if (!isset($datos["ctrben658"])) {
            $datos["ctrben658"] = '';
        }

// Campos adicionados en mayo 20 de 2011
        $xml .= '<ctrmen>' . $datos["ctrmen"] . '</ctrmen>';
        $xml .= '<ctrmennot>' . $datos["ctrmennot"] . '</ctrmennot>';
        $xml .= '<ctrubi>' . $datos["ctrubi"] . '</ctrubi>';
        $xml .= '<ctrfun>' . $datos["ctrfun"] . '</ctrfun>';
        $xml .= '<art4>' . $datos["art4"] . '</art4>';
        $xml .= '<art7>' . $datos["art7"] . '</art7>';
        $xml .= '<art50>' . $datos["art50"] . '</art50>';
        $xml .= '<benley1780>' . $datos["benley1780"] . '</benley1780>';
        $xml .= '<cumplerequisitos1780>' . $datos["cumplerequisitos1780"] . '</cumplerequisitos1780>';
        $xml .= '<renunciabeneficios1780>' . $datos["renunciabeneficios1780"] . '</renunciabeneficios1780>';
        $xml .= '<cumplerequisitos1780primren>' . $datos["cumplerequisitos1780primren"] . '</cumplerequisitos1780primren>';
        $xml .= '<matriculaanterior>' . $datos["matriculaanterior"] . '</matriculaanterior>';
        $xml .= '<ctrcancelacion1429>' . $datos["ctrcancelacion1429"] . '</ctrcancelacion1429>';
        $xml .= '<ctrdepuracion1727>' . $datos["ctrdepuracion1727"] . '</ctrdepuracion1727>';
        $xml .= '<ctrfechadepuracion1727>' . $datos["ctrfechadepuracion1727"] . '</ctrfechadepuracion1727>';
        $xml .= '<ctrben658>' . $datos["ctrben658"] . '</ctrben658>';
        $xml .= '<personaltemp>' . $datos["personaltemp"] . '</personaltemp>';
        $xml .= '<ivcenvio>' . $datos["ivcenvio"] . '</ivcenvio>';
        $xml .= '<ivcsuelos>' . $datos["ivcsuelos"] . '</ivcsuelos>';
        $xml .= '<ivcarea>' . $datos["ivcarea"] . '</ivcarea>';
        $xml .= '<ivcver>' . $datos["ivcver"] . '</ivcver>';
        $xml .= '<ivccretip>' . $datos["ivccretip"] . '</ivccretip>';
        $xml .= '<ivcali>' . $datos["ivcali"] . '</ivcali>';
        $xml .= '<ivcqui>' . $datos["ivcqui"] . '</ivcqui>';
        $xml .= '<ivcriesgo>' . $datos["ivcriesgo"] . '</ivcriesgo>';
        $xml .= '<ctrbic>' . $datos["ctrbic"] . '</ctrbic>';

//
        foreach ($datos["codigoscae"] as $key => $dat) {
            $xml .= '<' . $key . '>' . trim((string) $dat) . '</' . $key . '>';
        }

//
        foreach ($datos["informacionadicional"] as $key => $dat) {
            $xml .= '<' . $key . '>' . trim((string) $dat) . '</' . $key . '>';
        }

        /*
          // Incluidas en mayo 23 de 2014
          $xml.='<alc_cedcat>' . $datos["alc_cedcat"] . '</alc_cedcat>';
          $xml.='<alc_feciniact>' . $datos["alc_feciniact"] . '</alc_feciniact>';
          $xml.='<alc_ingresos>' . $datos["alc_ingresos"] . '</alc_ingresos>';
          $xml.='<alc_codica1>' . $datos["alc_codica1"] . '</alc_codica1>';
          $xml.='<alc_codica2>' . $datos["alc_codica2"] . '</alc_codica2>';
          $xml.='<alc_codica3>' . $datos["alc_codica3"] . '</alc_codica3>';
          $xml.='<alc_codica4>' . $datos["alc_codica4"] . '</alc_codica4>';
          $xml.='<alc_codica5>' . $datos["alc_codica5"] . '</alc_codica5>';
          $xml.='<alc_areatot>' . $datos["alc_areatot"] . '</alc_areatot>';
          $xml.='<alc_areauti>' . $datos["alc_areauti"] . '</alc_areauti>';
          $xml.='<alc_respiyc>' . $datos["alc_respiyc"] . '</alc_respiyc>';
          $xml.='<alc_respretiyc>' . $datos["alc_respretiyc"] . '</alc_respretiyc>';
          $xml.='<alc_respmedmag>' . $datos["alc_respmedmag"] . '</alc_respmedmag>';
          $xml.='<alc_respotros>' . $datos["alc_respotros"] . '</alc_respotros>';
          $xml.='<alc_regimen>' . $datos["alc_regimen"] . '</alc_regimen>';
          $xml.='<alc_grancon>' . $datos["alc_grancon"] . '</alc_grancon>';
          $xml.='<alc_lugpre>' . $datos["alc_lugpre"] . '</alc_lugpre>';
          $xml.='<alc_estienda>' . $datos["alc_estienda"] . '</alc_estienda>';
          $xml.='<alc_tieneav>' . $datos["alc_tieneav"] . '</alc_tieneav>';
          $xml.='<alc_barrio>' . $datos["alc_barrio"] . '</alc_barrio>';
          $xml.='<alc_predio>' . $datos["alc_predio"] . '</alc_predio>';
         */

        // Representacion legal y administracion
        $xml .= '<representanteslegales>';
        if (!empty($datos["repleg"])) {
            foreach ($datos["replegal"] as $rep) {
                $xml .= '<representantelegal>';
                $xml .= '<idtipoidentificacionreplegal>' . $rep["idtipoidentificacionreplegal"] . '</idtipoidentificacionreplegal>';
                $xml .= '<identificacionreplegal>' . $rep["identificacionreplegal"] . '</identificacionreplegal>';
                if ($reemplazar == 'si') {
                    $xml .= '<nombrereplegal><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["nombrereplegal"]) . ']]></nombrereplegal>';
                    $xml .= '<cargoreplegal><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($rep["cargoreplegal"]) . ']]></cargoreplegal>';
                } else {
                    $xml .= '<nombrereplegal><![CDATA[' . $rep["nombrereplegal"] . ']]></nombrereplegal>';
                    $xml .= '<cargoreplegal><![CDATA[' . $rep["cargoreplegal"] . ']]></cargoreplegal>';
                }
                $xml .= '</representantelegal>';
            }
        }
        $xml .= '</representanteslegales>';

        // Administrador    
        $xml .= '<idtipoidentificacionadministrador>' . $datos["idtipoidentificacionadministrador"] . '</idtipoidentificacionadministrador>';
        $xml .= '<identificacionadministrador>' . $datos["identificacionadministrador"] . '</identificacionadministrador>';
        if ($reemplazar == 'si') {
            $xml .= '<nombreadministrador><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($datos["nombreadministrador"]) . ']]></nombreadministrador>';
        } else {
            $xml .= '<nombreadministrador><![CDATA[' . $datos["nombreadministrador"] . ']]></nombreadministrador>';
        }

        // Bienes
        $xml .= '<bienes>';
        if (!empty($datos["bienes"])) {
            foreach ($datos["bienes"] as $bien) {
                $xml .= '<bien>';
                $xml .= '<matinmo>' . $bien["matinmo"] . '</matinmo>';
                $xml .= '<dir><![CDATA[' . $bien["dir"] . ']]></dir>';
                $xml .= '<barrio><![CDATA[' . $bien["barrio"] . ']]></barrio>';
                $xml .= '<muni><![CDATA[' . $bien["muni"] . ']]></muni>';
                $xml .= '<dpto><![CDATA[' . $bien["dpto"] . ']]></dpto>';
                $xml .= '<pais><![CDATA[' . $bien["pais"] . ']]></pais>';
                $xml .= '</bien>';
            }
        }
        $xml .= '</bienes>';

        // Propietarios
        $xml .= '<propietarios>';
        if (
                ($datos["organizacion"] == '02')
        ) {
            if (!empty($datos["propietarios"])) {
                foreach ($datos["propietarios"] as $prop) {

                    if (!isset($prop["nom1propietario"])) {
                        $prop["nom1propietario"] = '';
                    }
                    if (!isset($prop["nom2propietario"])) {
                        $prop["nom2propietario"] = '';
                    }
                    if (!isset($prop["ape1propietario"])) {
                        $prop["ape1propietario"] = '';
                    }
                    if (!isset($prop["ape2propietario"])) {
                        $prop["ape2propietario"] = '';
                    }
                    if (!isset($prop["fecmatripropietario"])) {
                        $prop["fecmatripropietario"] = '';
                    }
                    if (!isset($prop["estadodatospropietario"])) {
                        $prop["estadodatospropietario"] = '';
                    }

                    $xml .= '<propietario>';

                    $xml .= '<idclase>' . $prop["idtipoidentificacionpropietario"] . '</idclase>';
                    $xml .= '<numid>' . ltrim((string) $prop["identificacionpropietario"], '0') . '</numid>';
                    $xml .= '<nit>' . ltrim((string) $prop["nitpropietario"], '0') . '</nit>';
                    if ($reemplazar == 'si') {
                        $xml .= '<nombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["nombrepropietario"]) . ']]></nombre>';
                    } else {
                        $xml .= '<nombre><![CDATA[' . $prop["nombrepropietario"] . ']]></nombre>';
                    }
                    $xml .= '<camara>' . $prop["camarapropietario"] . '</camara>';
                    $xml .= '<matricula>' . ltrim((string) $prop["matriculapropietario"], '0') . '</matricula>';

                    $xml .= '<camarapropietario>' . $prop["camarapropietario"] . '</camarapropietario>';
                    $xml .= '<matriculapropietario>' . ltrim((string) $prop["matriculapropietario"], '0') . '</matriculapropietario>';
                    $xml .= '<idtipoidentificacionpropietario>' . $prop["idtipoidentificacionpropietario"] . '</idtipoidentificacionpropietario>';
                    $xml .= '<identificacionpropietario>' . ltrim((string) $prop["identificacionpropietario"], '0') . '</identificacionpropietario>';
                    $xml .= '<nitpropietario>' . ltrim((string) $prop["nitpropietario"], '0') . '</nitpropietario>';
                    if ($reemplazar == 'si') {
                        $xml .= '<nombrepropietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["nombrepropietario"]) . ']]></nombrepropietario>';
                        $xml .= '<nom1propietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["nom1propietario"]) . ']]></nom1propietario>';
                        $xml .= '<nom2propietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["nom2propietario"]) . ']]></nom2propietario>';
                        $xml .= '<ape1propietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["ape1propietario"]) . ']]></ape1propietario>';
                        $xml .= '<ape2propietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["ape2propietario"]) . ']]></ape2propietario>';
                        $xml .= '<direccionpropietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(str_replace("#", "Nro.", $prop["direccionpropietario"])) . ']]></direccionpropietario>';
                        $xml .= '<direccionnotpropietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(str_replace("#", "Nro.", $prop["direccionnotpropietario"])) . ']]></direccionnotpropietario>';
                        $xml .= '<nomreplegpropietario><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($prop["nomreplegpropietario"]) . ']]></nomreplegpropietario>';
                    } else {
                        $xml .= '<nombrepropietario><![CDATA[' . $prop["nombrepropietario"] . ']]></nombrepropietario>';
                        $xml .= '<nom1propietario><![CDATA[' . $prop["nom1propietario"] . ']]></nom1propietario>';
                        $xml .= '<nom2propietario><![CDATA[' . $prop["nom2propietario"] . ']]></nom2propietario>';
                        $xml .= '<ape1propietario><![CDATA[' . $prop["ape1propietario"] . ']]></ape1propietario>';
                        $xml .= '<ape2propietario><![CDATA[' . $prop["ape2propietario"] . ']]></ape2propietario>';
                        $xml .= '<direccionpropietario><![CDATA[' . str_replace("#", "Nro.", $prop["direccionpropietario"]) . ']]></direccionpropietario>';
                        $xml .= '<direccionnotpropietario><![CDATA[' . str_replace("#", "Nro.", $prop["direccionnotpropietario"]) . ']]></direccionnotpropietario>';
                        $xml .= '<nomreplegpropietario><![CDATA[' . $prop["nomreplegpropietario"] . ']]></nomreplegpropietario>';
                    }
                    $xml .= '<municipiopropietario>' . $prop["municipiopropietario"] . '</municipiopropietario>';
                    $xml .= '<municipionotpropietario>' . $prop["municipionotpropietario"] . '</municipionotpropietario>';
                    $xml .= '<telefonopropietario>' . $prop["telefonopropietario"] . '</telefonopropietario>';
                    $xml .= '<telefono2propietario>' . $prop["telefono2propietario"] . '</telefono2propietario>';
                    $xml .= '<celularpropietario>' . $prop["celularpropietario"] . '</celularpropietario>';
                    $xml .= '<numidreplegpropietario>' . $prop["numidreplegpropietario"] . '</numidreplegpropietario>';
                    $xml .= '<tipoidreplegpropietario>' . $prop["tipoidreplegpropietario"] . '</tipoidreplegpropietario>';
                    $xml .= '<fecmatripropietario>' . $prop["fecmatripropietario"] . '</fecmatripropietario>';
                    $xml .= '<ultanorenpropietario>' . $prop["ultanorenpropietario"] . '</ultanorenpropietario>';
                    $xml .= '<organizacionpropietario>' . $prop["organizacionpropietario"] . '</organizacionpropietario>';
                    $xml .= '<estadodatospropietario>' . $prop["estadodatospropietario"] . '</estadodatospropietario>';
                    $xml .= '</propietario>';
                }
            }
        }
        $xml .= '</propietarios>';

        // Establecimientos
        if ($extendido == 'si') {
            $xml .= '<establecimientos>';
            if (
                    ($datos["organizacion"] != '02') &&
                    (($datos["categoria"] == '0') || ($datos["categoria"] == '1'))
            ) {
                if (!empty($datos["establecimientos"])) {
                    foreach ($datos["establecimientos"] as $est) {
                        $xml .= '<establecimiento>';
                        $xml .= '<matriculaestablecimiento>' . ltrim((string) $est["matriculaestablecimiento"], '0') . '</matriculaestablecimiento>';
                        if ($reemplazar == 'si') {
                            $xml .= '<nombreestablecimiento><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($est["nombreestablecimiento"]) . ']]></nombreestablecimiento>';
                        } else {
                            $xml .= '<nombreestablecimiento><![CDATA[' . $est["nombreestablecimiento"] . ']]></nombreestablecimiento>';
                        }
                        $xml .= '<estadodatosestablecimiento><![CDATA[' . $est["estadodatosestablecimiento"] . ']]></estadodatosestablecimiento>';
                        $xml .= '<estadomatricula><![CDATA[' . $est["estadomatricula"] . ']]></estadomatricula>';
                        $xml .= '<dircom><![CDATA[' . $est["dircom"] . ']]></dircom>';
                        $xml .= '<telcom1><![CDATA[' . $est["telcom1"] . ']]></telcom1>';
                        $xml .= '<telcom2><![CDATA[' . $est["telcom2"] . ']]></telcom2>';
                        $xml .= '<telcom3><![CDATA[' . $est["telcom3"] . ']]></telcom3>';
                        $xml .= '<muncom><![CDATA[' . $est["muncom"] . ']]></muncom>';
                        $xml .= '<emailcom><![CDATA[' . $est["emailcom"] . ']]></emailcom>';
                        $xml .= '<fechamatricula><![CDATA[' . $est["fechamatricula"] . ']]></fechamatricula>';
                        $xml .= '<fecharenovacion><![CDATA[' . $est["fecharenovacion"] . ']]></fecharenovacion>';
                        $xml .= '<ultanorenovado><![CDATA[' . $est["ultanoren"] . ']]></ultanorenovado>';
                        $xml .= '<actvin><![CDATA[' . $est["actvin"] . ']]></actvin>';
                        $xml .= '<ciiu1><![CDATA[' . $est["ciiu1"] . ']]></ciiu1>';
                        $xml .= '<ciiu2><![CDATA[' . $est["ciiu2"] . ']]></ciiu2>';
                        $xml .= '<ciiu3><![CDATA[' . $est["ciiu3"] . ']]></ciiu3>';
                        $xml .= '<ciiu4><![CDATA[' . $est["ciiu4"] . ']]></ciiu4>';
                        $xml .= '<embargado><![CDATA[' . $est["embargado"] . ']]></embargado>';
                        $xml .= '</establecimiento>';
                    }
                }
            }
            $xml .= '</establecimientos>';
        }

        // Sucuarsales y agencias
        if ($extendido == 'si') {
            $xml .= '<sucursalesagencias>';
            if (
                    ($datos["organizacion"] != '02') &&
                    (($datos["categoria"] == '0') || ($datos["categoria"] == '1'))
            ) {
                if (!empty($datos["sucursalesagencias"])) {
                    foreach ($datos["sucursalesagencias"] as $est) {
                        $xml .= '<sucage>';
                        $xml .= '<matriculasucage>' . ltrim((string) $est["matriculasucage"], '0') . '</matriculasucage>';
                        if ($reemplazar == 'si') {
                            $xml .= '<nombresucage><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($est["nombresucage"]) . ']]></nombresucage>';
                        } else {
                            $xml .= '<nombresucage><![CDATA[' . $est["nombresucage"] . ']]></nombresucage>';
                        }
                        $xml .= '<categoria><![CDATA[' . $est["categoria"] . ']]></categoria>';
                        $xml .= '<estado><![CDATA[' . $est["estado"] . ']]></estado>';
                        $xml .= '<dircom><![CDATA[' . $est["dircom"] . ']]></dircom>';
                        $xml .= '<telcom1><![CDATA[' . $est["telcom1"] . ']]></telcom1>';
                        $xml .= '<telcom2><![CDATA[' . $est["telcom2"] . ']]></telcom2>';
                        $xml .= '<telcom3><![CDATA[' . $est["telcom3"] . ']]></telcom3>';
                        $xml .= '<muncom><![CDATA[' . $est["muncom"] . ']]></muncom>';
                        $xml .= '<emailcom><![CDATA[' . $est["emailcom"] . ']]></emailcom>';
                        $xml .= '<fechamatricula><![CDATA[' . $est["fechamatricula"] . ']]></fechamatricula>';
                        $xml .= '<fecharenovacion><![CDATA[' . $est["fecharenovacion"] . ']]></fecharenovacion>';
                        $xml .= '<ultanorenovado><![CDATA[' . $est["ultanoren"] . ']]></ultanorenovado>';
                        $xml .= '<actvin><![CDATA[' . $est["actvin"] . ']]></actvin>';
                        $xml .= '<ciiu1><![CDATA[' . $est["ciiu1"] . ']]></ciiu1>';
                        $xml .= '<ciiu2><![CDATA[' . $est["ciiu2"] . ']]></ciiu2>';
                        $xml .= '<ciiu3><![CDATA[' . $est["ciiu3"] . ']]></ciiu3>';
                        $xml .= '<ciiu4><![CDATA[' . $est["ciiu4"] . ']]></ciiu4>';
                        $xml .= '<embargado><![CDATA[' . $est["embargado"] . ']]></embargado>';

                        $xml .= '</sucage>';
                    }
                }
            }
            $xml .= '</sucursalesagencias>';
        }

        // Referencia a casa principal
        if (
                ($datos["organizacion"] > '02') &&
                (($datos["categoria"] == '2') || ($datos["categoria"] == '3'))
        ) {
            if (!isset($datos["cpcodcam"])) {
                $datos["cpcodcam"] = '';
            }
            if (!isset($datos["cpnummat"])) {
                $datos["cpnummat"] = '';
            }
            if (!isset($datos["cprazsoc"])) {
                $datos["cprazsoc"] = '';
            }
            if (!isset($datos["cpnumnit"])) {
                $datos["cpnumnit"] = '';
            }
            if (!isset($datos["cpdircom"])) {
                $datos["cpdircom"] = '';
            }
            if (!isset($datos["cpdirnot"])) {
                $datos["cpdirnot"] = '';
            }
            if (!isset($datos["cpnumtel"])) {
                $datos["cpnumtel"] = '';
            }
            if (!isset($datos["cpnumfax"])) {
                $datos["cpnumfax"] = '';
            }
            if (!isset($datos["cpcodmun"])) {
                $datos["cpcodmun"] = '';
            }
            if (!isset($datos["cpmunnot"])) {
                $datos["cpmunnot"] = '';
            }
            if (!isset($datos["cptirepleg"])) {
                $datos["cptirepleg"] = '';
            }
            if (!isset($datos["cpirepleg"])) {
                $datos["cpirepleg"] = '';
            }
            if (!isset($datos["cptnrepleg"])) {
                $datos["cptnrepleg"] = '';
            }
            if (!isset($datos["cptelrepleg"])) {
                $datos["cptelrepleg"] = '';
            }
            if (!isset($datos["cpemailrepleg"])) {
                $datos["cpemailrepleg"] = '';
            }
            $xml .= '<cpcodcam>' . trim((string) $datos["cpcodcam"]) . '</cpcodcam>';
            $xml .= '<cpnummat>' . sprintf("%08s", trim((string) $datos["cpnummat"])) . '</cpnummat>';
            if ($reemplazar == 'si') {
                $xml .= '<cprazsoc><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim((string) $datos["cprazsoc"])) . ']]></cprazsoc>';
            } else {
                $xml .= '<cprazsoc><![CDATA[' . trim((string) $datos["cprazsoc"]) . ']]></cprazsoc>';
            }
            $xml .= '<cpnumnit>' . sprintf("%011s", trim((string) $datos["cpnumnit"])) . '</cpnumnit>';
            if ($reemplazar == 'si') {
                $xml .= '<cpdircom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales(trim((string) $datos["cpdircom"])) . ']]></cpdircom>';
            } else {
                $xml .= '<cpdircom><![CDATA[' . trim((string) $datos["cpdircom"]) . ']]></cpdircom>';
            }
            $xml .= '<cpdirnot><![CDATA[' . trim((string) $datos["cpdirnot"]) . ']]></cpdirnot>';
            $xml .= '<cpnumtel>' . trim((string) $datos["cpnumtel"]) . '</cpnumtel>';
            $xml .= '<cpnumfax>' . trim((string) $datos["cpnumfax"]) . '</cpnumfax>';
            $xml .= '<cpcodmun>' . sprintf("%05s", trim((string) $datos["cpcodmun"])) . '</cpcodmun>';
            $xml .= '<cpmunnot>' . sprintf("%05s", trim((string) $datos["cpmunnot"])) . '</cpmunnot>';
            $xml .= '<cptirepleg>' . trim((string) $datos["cptirepleg"]) . '</cptirepleg>';
            $xml .= '<cpirepleg>' . trim((string) $datos["cpirepleg"]) . '</cpirepleg>';
            $xml .= '<cpnrepleg>' . trim((string) $datos["cpnrepleg"]) . '</cpnrepleg>';
            $xml .= '<cptelrepleg>' . trim((string) $datos["cptelrepleg"]) . '</cptelrepleg>';
            $xml .= '<cpemailrepleg>' . trim((string) $datos["cpemailrepleg"]) . '</cpemailrepleg>';
        } else {
            $xml .= '<cpcodcam></cpcodcam>';
            $xml .= '<cpnummat></cpnummat>';
            $xml .= '<cprazsoc></cprazsoc>';
            $xml .= '<cpnumnit></cpnumnit>';
            $xml .= '<cpdircom></cpdircom>';
            $xml .= '<cpdirnot></cpdirnot>';
            $xml .= '<cpnumtel></cpnumtel>';
            $xml .= '<cpnumfax></cpnumfax>';
            $xml .= '<cpcodmun></cpcodmun>';
            $xml .= '<cpmunnot></cpmunnot>';
            $xml .= '<cptirepleg></cptirepleg>';
            $xml .= '<cpirepleg></cpirepleg>';
            $xml .= '<cpnrepleg></cpnrepleg>';
            $xml .= '<cptelrepleg></cptelrepleg>';
            $xml .= '<cpemailrepleg></cpemailrepleg>';
        }

        if ($extendido == 'si') {
            if (isset($datos["vinculos"])) {
                if (!empty($datos["vinculos"])) {
                    foreach ($datos["vinculos"] as $v) {
                        $xml .= '<vinculo>';
                        $xml .= '<idtipoidentificacionotros>' . $v["idtipoidentificacionotros"] . '</idtipoidentificacionotros>';
                        $xml .= '<identificacionotros>' . $v["identificacionotros"] . '</identificacionotros>';
                        if ($reemplazar == 'si') {
                            $xml .= '<nombreotros><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($v["nombreotros"]) . ']]></nombreotros>';
                        } else {
                            $xml .= '<nombreotros>' . $v["nombreotros"] . '</nombreotros>';
                        }
                        $xml .= '<vinculootros>' . $v["vinculootros"] . '</vinculootros>';
                        $xml .= '</vinculo>';
                    }
                }
            }
        }

        if ($extendido == 'si') {
            if (isset($datos["vincuprop"])) {
                if (!empty($datos["vincuprop"])) {
                    foreach ($datos["vincuprop"] as $v) {
                        $xml .= '<vp>';
                        $xml .= '<idclase>' . $v["idclase"] . '</idclase>';
                        $xml .= '<numid>' . $v["numid"] . '</numid>';
                        if ($reemplazar == 'si') {
                            $xml .= '<nombre><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($v["nombre"]) . ']]></nombre>';
                        } else {
                            $xml .= '<nombre><![CDATA[' . $v["nombre"] . ']]></nombre>';
                        }
                        $xml .= '<vinculo>' . $v["vinculo"] . '</vinculo>';
                        if ($reemplazar == 'si') {
                            $xml .= '<cargo><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($v["cargo"]) . ']]></cargo>';
                        } else {
                            $xml .= '<cargo><![CDATA[' . $v["cargo"] . ']]></cargo>';
                        }
                        $xml .= '</vp>';
                    }
                }
            }
        }



        // Inscripciones
        if ($extendido == 'si') {
            if (isset($datos["inscripciones"])) {
                if (!empty($datos["inscripciones"])) {
                    foreach ($datos["inscripciones"] as $ins) {
                        $xml .= '<li>';
                        foreach ($ins as $key => $valor) {
                            if ($key == 'not' || $key == 'not2' || $key == 'not3' || $key == 'txoridoc') {
                                $xml .= '<' . $key . '><![CDATA[' . trim((string) $valor) . ']]></' . $key . '>';
                            } else {
                                $xml .= '<' . $key . '>' . trim((string) $valor) . '</' . $key . '>';
                            }
                        }
                        $xml .= '</li>';
                    }
                }
            }
        }

        // Nombres anteriores
        // if ($extendido == 'si') {
        if (isset($datos["nomant"])) {
            if (!empty($datos["nomant"])) {
                foreach ($datos["nomant"] as $ins) {
                    $xml .= '<na>';
                    $xml .= '<sec>' . $ins["sec"] . "</sec>";
                    $xml .= '<lib>' . $ins["lib"] . "</lib>";
                    $xml .= '<nreg>' . $ins["nreg"] . "</nreg>";
                    $xml .= '<freg>' . $ins["freg"] . "</freg>";
                    $xml .= '<nom><![CDATA[' . \funcionesGenerales::reemplazarEspeciales($ins["nom"]) . "]]></nom>";
                    $xml .= '<ope>' . $ins["ope"] . "</ope>";
                    $xml .= '<fcre>' . $ins["fcre"] . "</fcre>";
                    $xml .= '</na>';
                }
            }
        }

        // Embargos
        if (isset($datos["ctrembargos"])) {
            if (!empty($datos["ctrembargos"])) {
                foreach ($datos["ctrembargos"] as $ins) {
                    $xml .= '<emb>';
                    foreach ($ins as $key => $valor) {
                        $xml .= '<' . $key . '>' . $valor . '</' . $key . '>';
                    }
                    $xml .= '</emb>';
                }
            }
        }
        $xml .= '</expediente>';
        $xml .= '</expedientes>';

        return $xml;
    }

}

?>
