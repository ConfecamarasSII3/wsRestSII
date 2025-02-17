<?php

class funcionesRegistrales_grabarLiquidacionMreg {

    public static function grabarLiquidacionMreg($dbx = null, $datat = array()) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        if ($dbx === null) {
            $cerrarMysqli = 'si';
            $dbx = conexionMysqliApi();
        } else {
            $cerrarMysqli = 'no';
        }

        //
        if (empty($datat)) {
            $datat = $_SESSION["tramite"];
        }

        //
        $conteo = contarRegistrosMysqliApi($dbx, 'mreg_liquidacion', 'idliquidacion=' . $datat["numeroliquidacion"]);
        if ($conteo === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error al ubicar la liquidación en mreg_liquidacion';
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }

        //
        $datat["tramiteautomatico"] = (isset($datat["tramiteautomatico"])) ? $datat["tramiteautomatico"] : '';
        $datat["tipomatricula"] = (isset($datat["tipomatricula"])) ? $datat["tipomatricula"] : '';
        $datat["tipotramite"] = (isset($datat["tipotramite"])) ? $datat["tipotramite"] : '';
        $datat["subtipotramite"] = (isset($datat["subtipotramite"])) ? $datat["subtipotramite"] : '';
        $datat["origen"] = (isset($datat["origen"])) ? $datat["origen"] : '';
        $datat["sede"] = (isset($datat["sede"])) ? $datat["sede"] : '';
        $datat["sede"] = (isset($datat["sede"])) ? $datat["sede"] : '';
        if ($datat["sede"] == '') {
            if ($datat["idusuario"] == 'USUPUBXX') {
                $datat["sede"] = '99';
            } else {
                if ($datat["idusuario"] == 'RUE') {
                    $datat["sede"] = '90';
                } else {
                    if (!isset($_SESSION["generales"]["sedeusuario"])) {
                        $_SESSION["generales"]["sedeusuario"] = '01';
                    }
                    $datat["sede"] = $_SESSION["generales"]["sedeusuario"];
                }
            }
        }
        if ($datat["sede"] == '') {
            $datat["sede"] = '01';
        }
        $datat["matriculabase"] = (isset($datat["matriculabase"])) ? $datat["matriculabase"] : '';
        $datat["nombrebase"] = (isset($datat["nombrebase"])) ? $datat["nombrebase"] : '';
        $datat["nombrebasebase64"] = base64_encode($datat["nombrebase"]);
        if (!isset($datat["siglabase"])) {
            $datat["siglabase"] = '';
        }
        $datat["siglabasebase64"] = base64_encode((string) $datat["siglabase"]);
        $datat["nom1base"] = (isset($datat["nom1base"])) ? $datat["nom1base"] : '';
        $datat["nom2base"] = (isset($datat["nom2base"])) ? $datat["nom2base"] : '';
        $datat["ape1base"] = (isset($datat["ape1base"])) ? $datat["ape1base"] : '';
        $datat["ape2base"] = (isset($datat["ape2base"])) ? $datat["ape2base"] : '';
        $datat["tipoidentificacionbase"] = (isset($datat["tipoidentificacionbase"])) ? $datat["tipoidentificacionbase"] : '';
        $datat["identificacionbase"] = (isset($datat["identificacionbase"])) ? $datat["identificacionbase"] : '';
        $datat["organizacionbase"] = (isset($datat["organizacionbase"])) ? $datat["organizacionbase"] : '';
        $datat["categoriabase"] = (isset($datat["categoriabase"])) ? $datat["categoriabase"] : '';
        $datat["afiliadobase"] = (isset($datat["afiliadobase"])) ? $datat["afiliadobase"] : '';
        $datat["idexpedientebase"] = (isset($datat["idexpedientebase"])) ? $datat["idexpedientebase"] : '';
        $datat["idmatriculabase"] = (isset($datat["idmatriculabase"])) ? $datat["idmatriculabase"] : '';
        $datat["idproponentebase"] = (isset($datat["idproponentebase"])) ? $datat["idproponentebase"] : '';
        $datat["tipoproponente"] = (isset($datat["tipoproponente"])) ? $datat["tipoproponente"] : '';
        $datat["tipocliente"] = (isset($datat["tipocliente"])) ? $datat["tipocliente"] : '';
        $datat["razonsocialcliente"] = (isset($datat["razonsocialcliente"])) ? $datat["razonsocialcliente"] : '';
        $datat["apellidocliente"] = (isset($datat["apellidocliente"])) ? $datat["apellidocliente"] : '';
        $datat["apellido1cliente"] = (isset($datat["apellido1cliente"])) ? $datat["apellido1cliente"] : '';
        $datat["apellido2cliente"] = (isset($datat["apellido2cliente"])) ? $datat["apellido2cliente"] : '';
        $datat["nombre1cliente"] = (isset($datat["nombre1cliente"])) ? $datat["nombre1cliente"] : '';
        $datat["nombre2cliente"] = (isset($datat["nombre2cliente"])) ? $datat["nombre2cliente"] : '';
        $datat["particulacliente"] = (isset($datat["particulacliente"])) ? $datat["particulacliente"] : '';
        $datat["email"] = (isset($datat["email"])) ? $datat["email"] : '';
        $datat["zonapostal"] = (isset($datat["zonapostal"])) ? $datat["zonapostal"] : '';
        $datat["codigoregimen"] = (isset($datat["codigoregimen"])) ? $datat["codigoregimen"] : '';
        $datat["responsabilidadtributaria"] = (isset($datat["responsabilidadtributaria"])) ? $datat["responsabilidadtributaria"] : '';
        $datat["codigoimpuesto"] = (isset($datat["codigoimpuesto"])) ? $datat["codigoimpuesto"] : '';
        $datat["nombreimpuesto"] = (isset($datat["nombreimpuesto"])) ? $datat["nombreimpuesto"] : '';
        $datat["responsabilidadfiscal"] = (isset($datat["responsabilidadfiscal"])) ? $datat["responsabilidadfiscal"] : '';
        $datat["regimentributario"] = (isset($datat["regimentributario"])) ? $datat["regimentributario"] : '';
        $datat["pais"] = (isset($datat["pais"])) ? $datat["pais"] : 'CO';
        $datat["lenguaje"] = (isset($datat["lenguaje"])) ? $datat["lenguaje"] : 'es';
        $datat["direccionnot"] = (isset($datat["direccionnot"])) ? $datat["direccionnot"] : '';
        $datat["idmunicipionot"] = (isset($datat["idmunicipionot"])) ? $datat["idmunicipionot"] : '';
        $datat["codposcom"] = (isset($datat["codposcom"])) ? $datat["codposcom"] : '';
        $datat["codposnot"] = (isset($datat["codposnot"])) ? $datat["codposnot"] : '';
        $datat["tipopagador"] = (isset($datat["tipopagador"])) ? $datat["tipopagador"] : '';
        $datat["tipoidentificacionpagador"] = (isset($datat["tipoidentificacionpagador"])) ? $datat["tipoidentificacionpagador"] : '';
        $datat["identificacionpagador"] = (isset($datat["identificacionpagador"])) ? $datat["identificacionpagador"] : '';
        $datat["razonsocialpagador"] = (isset($datat["razonsocialpagador"])) ? $datat["razonsocialpagador"] : '';
        $datat["nombrepagador"] = (isset($datat["nombrepagador"])) ? $datat["nombrepagador"] : '';
        $datat["apellidopagador"] = (isset($datat["apellidopagador"])) ? $datat["apellidopagador"] : '';
        $datat["nombre1pagador"] = (isset($datat["nombre1pagador"])) ? $datat["nombre1pagador"] : '';
        $datat["nombre2pagador"] = (isset($datat["nombre2pagador"])) ? $datat["nombre2pagador"] : '';
        $datat["apellido1pagador"] = (isset($datat["apellido1pagador"])) ? $datat["apellido1pagador"] : '';
        $datat["apellido2pagador"] = (isset($datat["apellido2pagador"])) ? $datat["apellido2pagador"] : '';
        $datat["movilpagador"] = (isset($datat["movilpagador"])) ? $datat["movilpagador"] : '';
        $datat["telefonopagador"] = (isset($datat["telefonopagador"])) ? $datat["telefonopagador"] : '';
        $datat["direccionpagador"] = (isset($datat["direccionpagador"])) ? $datat["direccionpagador"] : '';
        $datat["municipiopagador"] = (isset($datat["municipiopagador"])) ? $datat["municipiopagador"] : '';
        $datat["emailpagador"] = (isset($datat["emailpagador"])) ? $datat["emailpagador"] : '';
        $datat["idsolicitudpago"] = ($datat["idsolicitudpago"] != '') ? $datat["idsolicitudpago"] : 0;
        $datat["alertaid"] = (isset($datat["alertaid"])) ? $datat["alertaid"] : 0;
        $datat["alertaservicio"] = (isset($datat["alertaservicio"])) ? $datat["alertaservicio"] : '';
        $datat["alertavalor"] = (isset($datat["alertavalor"])) ? $datat["alertavalor"] : 0;
        $datat["ctrcancelacion"] = (isset($datat["ctrcancelacion"])) ? $datat["ctrcancelacion"] : '';
        $datat["idasesor"] = (isset($datat["idasesor"])) ? $datat["idasesor"] : '';
        $datat["numeroempleados"] = (isset($datat["numeroempleados"])) ? $datat["numeroempleados"] : 0;
        $datat["pagoprepago"] = (isset($datat["pagoprepago"])) ? $datat["pagoprepago"] : 0;
        $datat["pagoafiliado"] = (isset($datat["pagoafiliado"])) ? $datat["pagoafiliado"] : 0;
        $datat["pagoacredito"] = (isset($datat["pagoacredito"])) ? $datat["pagoacredito"] : 0;
        $datat["pagoconsignacion"] = (isset($datat["pagoconsignacion"])) ? $datat["pagoconsignacion"] : 0;
        $datat["pagoqr"] = (isset($datat["pagoqr"])) ? $datat["pagoqr"] : 0;
        $datat["proyectocaja"] = (isset($datat["proyectocaja"])) ? $datat["proyectocaja"] : '001';
        $datat["cargoafiliacion"] = (isset($datat["cargoafiliacion"])) ? $datat["cargoafiliacion"] : 'NO';
        $datat["cargogastoadministrativo"] = (isset($datat["cargogastoadministrativo"])) ? $datat["cargogastoadministrativo"] : 'NO';
        $datat["cargoentidadoficial"] = (isset($datat["cargoentidadoficial"])) ? $datat["cargoentidadoficial"] : 'NO';
        $datat["cargoconsulta"] = (isset($datat["cargoconsulta"])) ? $datat["cargoconsulta"] : 'NO';
        $datat["actualizacionciiuversion4"] = (isset($datat["actualizacionciiuversion4"])) ? $datat["actualizacionciiuversion4"] : 'NO';
        $datat["domicilioorigen"] = (isset($datat["domicilioorigen"])) ? $datat["domicilioorigen"] : '';
        $datat["domiciliodestino"] = (isset($datat["domiciliodestino"])) ? $datat["domiciliodestino"] : '';
        $datat["reliquidacion"] = (isset($datat["reliquidacion"])) ? $datat["reliquidacion"] : '';
        $datat["pagoafiliacion"] = (isset($datat["pagoafiliacion"])) ? $datat["pagoafiliacion"] : '';
        $datat["numerofactura"] = (isset($datat["numerofactura"])) ? $datat["numerofactura"] : '';
        $datat["nrocontrolsipref"] = (isset($datat["nrocontrolsipref"])) ? $datat["nrocontrolsipref"] : '';
        $datat["tipoideradicador"] = (isset($datat["tipoideradicador"])) ? $datat["tipoideradicador"] : '';
        $datat["ideradicador"] = (isset($datat["ideradicador"])) ? $datat["ideradicador"] : '';
        $datat["fechaexpradicador"] = (isset($datat["fechaexpradicador"])) ? $datat["fechaexpradicador"] : '';
        $datat["nombreradicador"] = (isset($datat["nombreradicador"])) ? $datat["nombreradicador"] : '';
        $datat["nombrebanco"] = (isset($datat["nombrebanco"])) ? $datat["nombrebanco"] : '';

        $datat["orgpnat"] = (isset($datat["orgpnat"])) ? $datat["orgpnat"] : '';
        $datat["tipoidepnat"] = (isset($datat["tipoidepnat"])) ? $datat["tipoidepnat"] : '';
        $datat["idepnat"] = (isset($datat["idepnat"])) ? $datat["idepnat"] : '';
        $datat["nombrepnat"] = (isset($datat["nombrepnat"])) ? $datat["nombrepnat"] : '';
        $datat["actpnat"] = (isset($datat["actpnat"])) ? $datat["actpnat"] : '';
        $datat["actpnat"] = (ltrim((string) $datat["actpnat"], "0") != '') ? $datat["actpnat"] : 0;
        $datat["perpnat"] = (isset($datat["perpnat"])) ? $datat["perpnat"] : '';
        $datat["perpnat"] = (ltrim((string) $datat["perpnat"], "0") != '') ? $datat["perpnat"] : 0;
        $datat["munpnat"] = (isset($datat["munpnat"])) ? $datat["munpnat"] : '';
        $datat["numeromatriculapnat"] = (isset($datat["numeromatriculapnat"])) ? $datat["numeromatriculapnat"] : '';
        $datat["camarapnat"] = (isset($datat["camarapnat"])) ? $datat["camarapnat"] : '';
        $datat["nombreest"] = (isset($datat["nombreest"])) ? $datat["nombreest"] : '';
        $datat["actest"] = (isset($datat["actest"])) ? $datat["actest"] : '';
        $datat["actest"] = (ltrim((string) $datat["actest"], "0") != '') ? $datat["actest"] : 0;
        $datat["munest"] = (isset($datat["munest"])) ? $datat["munest"] : '';
        $datat["ultanoren"] = (isset($datat["ultanoren"])) ? $datat["ultanoren"] : '';
        $datat["incluirformularios"] = (isset($datat["incluirformularios"])) ? $datat["incluirformularios"] : '';
        $datat["incluircertificados"] = (isset($datat["incluircertificados"])) ? $datat["incluircertificados"] : '';
        $datat["incluirdiploma"] = (isset($datat["incluirdiploma"])) ? $datat["incluirdiploma"] : '';
        $datat["incluircartulina"] = (isset($datat["incluircartulina"])) ? $datat["incluircartulina"] : '';
        $datat["incluirafiliacion"] = (isset($datat["incluirafiliacion"])) ? $datat["incluirafiliacion"] : '';
        $datat["matricularpnat"] = (isset($datat["matricularpnat"])) ? $datat["matricularpnat"] : '';
        $datat["matricularest"] = (isset($datat["matricularest"])) ? $datat["matricularest"] : '';
        $datat["benart7"] = (isset($datat["benart7"])) ? $datat["benart7"] : '';
        $datat["benley1780"] = (isset($datat["benley1780"])) ? $datat["benley1780"] : '';
        $datat["fechanacimientopnat"] = (isset($datat["fechanacimientopnat"])) ? $datat["fechanacimientopnat"] : '';
        $datat["controlfirma"] = (isset($datat["controlfirma"])) ? $datat["controlfirma"] : '';

        $datat["modcom"] = (isset($datat["modcom"])) ? $datat["modcom"] : 'N';
        $datat["modnot"] = (isset($datat["modnot"])) ? $datat["modnot"] : 'N';
        $datat["modciiu"] = (isset($datat["modciiu"])) ? $datat["modciiu"] : 'N';
        $datat["modnombre"] = (isset($datat["modnombre"])) ? $datat["modnombre"] : 'N';

        $datat["nombrepjur"] = (isset($datat["nombrepjur"])) ? $datat["nombrepjur"] : '';
        $datat["perpjur"] = (isset($datat["perpjur"])) ? $datat["perpjur"] : '';
        $datat["perpjur"] = (ltrim((string) $datat["perpjur"], "0") != '') ? $datat["perpjur"] : 0;
        $datat["actpjur"] = (isset($datat["actpjur"])) ? $datat["actpjur"] : '';
        $datat["actpjur"] = (ltrim((string) $datat["actpjur"], "0") != '') ? $datat["actpjur"] : 0;
        $datat["munpjur"] = (isset($datat["munpjur"])) ? $datat["munpjur"] : '';
        $datat["orgpjur"] = (isset($datat["orgpjur"])) ? $datat["orgpjur"] : '';

        $datat["nombresuc"] = (isset($datat["nombresuc"])) ? $datat["nombresuc"] : '';
        $datat["actsuc"] = (isset($datat["actsuc"])) ? $datat["actsuc"] : '';
        $datat["actsuc"] = (ltrim((string) $datat["actsuc"], "0") != '') ? $datat["actsuc"] : 0;
        $datat["munsuc"] = (isset($datat["munsuc"])) ? $datat["munsuc"] : '';
        $datat["orgsuc"] = (isset($datat["orgsuc"])) ? $datat["orgsuc"] : '';

        $datat["nombreage"] = (isset($datat["nombreage"])) ? $datat["nombreage"] : '';
        $datat["actage"] = (isset($datat["actage"])) ? $datat["actage"] : '';
        $datat["actage"] = (ltrim((string) $datat["actage"], "0") != '') ? $datat["actage"] : 0;
        $datat["munage"] = (isset($datat["munage"])) ? $datat["munage"] : '';
        $datat["orgage"] = (isset($datat["orgage"])) ? $datat["orgage"] : '';

        $datat["matriculacambidom"] = (isset($datat["matriculacambidom"])) ? $datat["matriculacambidom"] : '';
        $datat["camaracambidom"] = (isset($datat["camaracambidom"])) ? $datat["camaracambidom"] : '';
        $datat["municipiocambidom"] = (isset($datat["municipiocambidom"])) ? $datat["municipiocambidom"] : '';
        $datat["fecmatcambidom"] = (isset($datat["fecmatcambidom"])) ? $datat["fecmatcambidom"] : '';
        $datat["fecrencambidom"] = (isset($datat["fecrencambidom"])) ? $datat["fecrencambidom"] : '';
        $datat["fecrencambidom"] = (isset($datat["fecrencambidom"])) ? $datat["fecrencambidom"] : '';

        $datat["capital"] = (isset($datat["capital"])) ? $datat["capital"] : 0;
        $datat["capital"] = (ltrim((string) $datat["capital"], "0") != '') ? $datat["capital"] : 0;

        $datat["tipodoc"] = (isset($datat["tipodoc"])) ? $datat["tipodoc"] : '';
        $datat["numdoc"] = (isset($datat["numdoc"])) ? $datat["numdoc"] : '';
        $datat["fechadoc"] = (isset($datat["fechadoc"])) ? $datat["fechadoc"] : '';
        $datat["origendoc"] = (isset($datat["origendoc"])) ? $datat["origendoc"] : '';
        $datat["mundoc"] = (isset($datat["mundoc"])) ? $datat["mundoc"] : '';
        $datat["organizacion"] = (isset($datat["organizacion"])) ? $datat["organizacion"] : '';
        $datat["categoria"] = (isset($datat["categoria"])) ? $datat["categoria"] : '';

        $datat["tipoiderepleg"] = (isset($datat["tipoiderepleg"])) ? $datat["tipoiderepleg"] : '';
        $datat["iderepleg"] = (isset($datat["iderepleg"])) ? $datat["iderepleg"] : '';
        $datat["nombrerepleg"] = (isset($datat["nombrerepleg"])) ? $datat["nombrerepleg"] : '';
        $datat["nombre1repleg"] = (isset($datat["nombre1repleg"])) ? $datat["nombre1repleg"] : '';
        $datat["nombre2repleg"] = (isset($datat["nombre2repleg"])) ? $datat["nombre2repleg"] : '';
        $datat["apellido1repleg"] = (isset($datat["apellido1repleg"])) ? $datat["apellido1repleg"] : '';
        $datat["apellido2repleg"] = (isset($datat["apellido2repleg"])) ? $datat["apellido2repleg"] : '';
        $datat["cargorepleg"] = (isset($datat["cargorepleg"])) ? $datat["cargorepleg"] : '';
        $datat["emailrepleg"] = (isset($datat["emailrepleg"])) ? $datat["emailrepleg"] : '';
        $datat["firmorepleg"] = (isset($datat["firmorepleg"])) ? $datat["firmorepleg"] : '';
        $datat["celularrepleg"] = (isset($datat["celularrepleg"])) ? $datat["celularrepleg"] : '';

        $datat["tipoideradicador"] = (isset($datat["tipoideradicador"])) ? $datat["tipoideradicador"] : '';
        $datat["ideradicador"] = (isset($datat["ideradicador"])) ? $datat["ideradicador"] : '';
        $datat["nombreradicador"] = (isset($datat["nombreradicador"])) ? $datat["nombreradicador"] : '';
        $datat["emailradicador"] = (isset($datat["emailradicador"])) ? $datat["emailradicador"] : '';
        $datat["telefonoradicador"] = (isset($datat["telefonoradicador"])) ? $datat["telefonoradicador"] : '';
        $datat["celularradicador"] = (isset($datat["celularradicador"])) ? $datat["celularradicador"] : '';

        $datat["tipolibro"] = (isset($datat["tipolibro"])) ? $datat["tipolibro"] : '';
        $datat["codigolibro"] = (isset($datat["codigolibro"])) ? $datat["codigolibro"] : '';
        $datat["primeravez"] = (isset($datat["primeravez"])) ? $datat["primeravez"] : '';
        $datat["confirmadigital"] = (isset($datat["confirmadigital"])) ? $datat["confirmadigital"] : '';

        $datat["iderevfis"] = (isset($datat["iderevfis"])) ? $datat["iderevfis"] : '';
        $datat["nombre1revfis"] = (isset($datat["nombre1revfis"])) ? $datat["nombre1revfis"] : '';
        $datat["nombre2revfis"] = (isset($datat["nombre2revfis"])) ? $datat["nombre2revfis"] : '';
        $datat["apellido1revfis"] = (isset($datat["apellido1revfis"])) ? $datat["apellido1revfis"] : '';
        $datat["apellido2revfis"] = (isset($datat["apellido2revfis"])) ? $datat["apellido2revfis"] : '';
        $datat["cargorevfis"] = (isset($datat["cargorevfis"])) ? $datat["cargorevfis"] : '';
        $datat["emailrevfis"] = (isset($datat["emailrevfis"])) ? $datat["emailrevfis"] : '';
        $datat["firmorevfis"] = (isset($datat["firmorevfis"])) ? $datat["firmorevfis"] : '';
        $datat["celularrevfis"] = (isset($datat["celularrevfis"])) ? $datat["celularrevfis"] : '';

        $datat["idepreasa"] = (isset($datat["idepreasa"])) ? $datat["idepreasa"] : '';
        $datat["nombre1preasa"] = (isset($datat["nombre1preasa"])) ? $datat["nombre1preasa"] : '';
        $datat["nombre2preasa"] = (isset($datat["nombre2preasa"])) ? $datat["nombre2preasa"] : '';
        $datat["apellido1preasa"] = (isset($datat["apellido1preasa"])) ? $datat["apellido1preasa"] : '';
        $datat["apellido2preasa"] = (isset($datat["apellido2preasa"])) ? $datat["apellido2preasa"] : '';
        $datat["cargopreasa"] = (isset($datat["cargopreasa"])) ? $datat["cargopreasa"] : '';
        $datat["emailpreasa"] = (isset($datat["emailpreasa"])) ? $datat["emailpreasa"] : '';
        $datat["firmopreasa"] = (isset($datat["firmopreasa"])) ? $datat["firmopreasa"] : '';
        $datat["celularpreasa"] = (isset($datat["celularpreasa"])) ? $datat["celularpreasa"] : '';

        $datat["idesecasa"] = (isset($datat["idesecasa"])) ? $datat["idesecasa"] : '';
        $datat["nombre1secasa"] = (isset($datat["nombre1secasa"])) ? $datat["nombre1secasa"] : '';
        $datat["nombre2secasa"] = (isset($datat["nombre2secasa"])) ? $datat["nombre2secasa"] : '';
        $datat["apellido1secasa"] = (isset($datat["apellido1secasa"])) ? $datat["apellido1secasa"] : '';
        $datat["apellido2secasa"] = (isset($datat["apellido2secasa"])) ? $datat["apellido2secasa"] : '';
        $datat["cargosecasa"] = (isset($datat["cargosecasa"])) ? $datat["cargosecasa"] : '';
        $datat["emailsecasa"] = (isset($datat["emailsecasa"])) ? $datat["emailsecasa"] : '';
        $datat["firmosecasa"] = (isset($datat["firmosecasa"])) ? $datat["firmosecasa"] : '';
        $datat["celularsecasa"] = (isset($datat["celularsecasa"])) ? $datat["celularsecasa"] : '';

        $datat["tipoidentificacionaceptante"] = (isset($datat["tipoidentificacionaceptante"])) ? $datat["tipoidentificacionaceptante"] : '';
        $datat["identificacionaceptante"] = (isset($datat["identificacionaceptante"])) ? $datat["identificacionaceptante"] : '';
        $datat["nombre1aceptante"] = (isset($datat["nombre1aceptante"])) ? $datat["nombre1aceptante"] : '';
        $datat["nombre2aceptante"] = (isset($datat["nombre2aceptante"])) ? $datat["nombre2aceptante"] : '';
        $datat["apellido1aceptante"] = (isset($datat["apellido1aceptante"])) ? $datat["apellido1aceptante"] : '';
        $datat["apellido2aceptante"] = (isset($datat["apellido2aceptante"])) ? $datat["apellido2aceptante"] : '';
        $datat["direccionaceptante"] = (isset($datat["direccionaceptante"])) ? $datat["direccionaceptante"] : '';
        $datat["municipioaceptante"] = (isset($datat["municipioaceptante"])) ? $datat["municipioaceptante"] : '';
        $datat["emailaceptante"] = (isset($datat["emailaceptante"])) ? $datat["emailaceptante"] : '';
        $datat["telefonoaceptante"] = (isset($datat["telefonoaceptante"])) ? $datat["telefonoaceptante"] : '';
        $datat["celularaceptante"] = (isset($datat["celularaceptante"])) ? $datat["celularaceptante"] : '';
        $datat["cargoaceptante"] = (isset($datat["cargoaceptante"])) ? $datat["cargoaceptante"] : '';
        $datat["fechadocideaceptante"] = (isset($datat["fechadocideaceptante"])) ? $datat["fechadocideaceptante"] : '';

        $datat["motivocorreccion"] = (isset($datat["motivocorreccion"])) ? $datat["motivocorreccion"] : '';
        $datat["tipoerror1"] = (isset($datat["tipoerror1"])) ? $datat["tipoerror1"] : '';
        $datat["tipoerror2"] = (isset($datat["tipoerror2"])) ? $datat["tipoerror2"] : '';
        $datat["tipoerror3"] = (isset($datat["tipoerror3"])) ? $datat["tipoerror3"] : '';
        $datat["tipoidentificacioncor"] = (isset($datat["tipoidentificacioncor"])) ? $datat["tipoidentificacioncor"] : '';
        $datat["identificacioncor"] = (isset($datat["identificacioncor"])) ? $datat["identificacioncor"] : '';
        $datat["nombre1cor"] = (isset($datat["nombre1cor"])) ? $datat["nombre1cor"] : '';
        $datat["nombre2cor"] = (isset($datat["nombre2cor"])) ? $datat["nombre2cor"] : '';
        $datat["apellido1cor"] = (isset($datat["apellido1cor"])) ? $datat["apellido1cor"] : '';
        $datat["apellido2cor"] = (isset($datat["apellido2cor"])) ? $datat["apellido2cor"] : '';
        $datat["direccioncor"] = (isset($datat["direccioncor"])) ? $datat["direccioncor"] : '';
        $datat["municipiocor"] = (isset($datat["municipiocor"])) ? $datat["municipiocor"] : '';
        $datat["emailcor"] = (isset($datat["emailcor"])) ? $datat["emailcor"] : '';
        $datat["telefonocor"] = (isset($datat["telefonocor"])) ? $datat["telefonocor"] : '';
        $datat["celularcor"] = (isset($datat["celularcor"])) ? $datat["celularcor"] : '';

        $datat["descripcionembargo"] = (isset($datat["descripcionembargo"])) ? $datat["descripcionembargo"] : '';
        $datat["descripciondesembargo"] = (isset($datat["descripciondesembargo"])) ? $datat["descripciondesembargo"] : '';
        $datat["tipoidentificaciondemandante"] = (isset($datat["tipoidentificaciondemandante"])) ? $datat["tipoidentificaciondemandante"] : '';
        $datat["identificaciondemandante"] = (isset($datat["identificaciondemandante"])) ? $datat["identificaciondemandante"] : '';
        $datat["nombredemandante"] = (isset($datat["nombredemandante"])) ? $datat["nombredemandante"] : '';
        $datat["libro"] = (isset($datat["libro"])) ? $datat["libro"] : '';
        $datat["numreg"] = (isset($datat["numreg"])) ? $datat["numreg"] : '';

        $datat["descripcionpqr"] = (isset($datat["descripcionpqr"])) ? $datat["descripcionpqr"] : '';
        $datat["tipoidentificacionpqr"] = (isset($datat["tipoidentificacionpqr"])) ? $datat["tipoidentificacionpqr"] : '';
        $datat["identificacionpqr"] = (isset($datat["identificacionpqr"])) ? $datat["identificacionpqr"] : '';
        $datat["nombre1pqr"] = (isset($datat["nombre1pqr"])) ? $datat["nombre1pqr"] : '';
        $datat["nombre2pqr"] = (isset($datat["nombre2pqr"])) ? $datat["nombre2pqr"] : '';
        $datat["apellido1pqr"] = (isset($datat["apellido1pqr"])) ? $datat["apellido1pqr"] : '';
        $datat["apellido2pqr"] = (isset($datat["apellido2pqr"])) ? $datat["apellido2pqr"] : '';
        $datat["direccionpqr"] = (isset($datat["direccionpqr"])) ? $datat["direccionpqr"] : '';
        $datat["municipiopqr"] = (isset($datat["municipiopqr"])) ? $datat["municipiopqr"] : '';
        $datat["emailpqr"] = (isset($datat["emailpqr"])) ? $datat["emailpqr"] : '';
        $datat["telefonopqr"] = (isset($datat["telefonopqr"])) ? $datat["telefonopqr"] : '';
        $datat["celularpqr"] = (isset($datat["celularpqr"])) ? $datat["celularpqr"] : '';

        $datat["descripcionrr"] = (isset($datat["descripcionrr"])) ? $datat["descripcionrr"] : '';
        $datat["inscripcionrr"] = (isset($datat["inscripcionrr"])) ? $datat["inscripcionrr"] : '';
        $datat["tipoidentificacionrr"] = (isset($datat["tipoidentificacionrr"])) ? $datat["tipoidentificacionrr"] : '';
        $datat["identificacionrr"] = (isset($datat["identificacionrr"])) ? $datat["identificacionrr"] : '';
        $datat["nombre1rr"] = (isset($datat["nombre1rr"])) ? $datat["nombre1rr"] : '';
        $datat["nombre2rr"] = (isset($datat["nombre2rr"])) ? $datat["nombre2rr"] : '';
        $datat["apellido1rr"] = (isset($datat["apellido1rr"])) ? $datat["apellido1rr"] : '';
        $datat["apellido2rr"] = (isset($datat["apellido2rr"])) ? $datat["apellido2rr"] : '';
        $datat["direccionrr"] = (isset($datat["direccionrr"])) ? $datat["direccionrr"] : '';
        $datat["municipiorr"] = (isset($datat["municipiorr"])) ? $datat["municipiorr"] : '';
        $datat["emailrr"] = (isset($datat["emailrr"])) ? $datat["emailrr"] : '';
        $datat["telefonorr"] = (isset($datat["telefonorr"])) ? $datat["telefonorr"] : '';
        $datat["celularrr"] = (isset($datat["celularrr"])) ? $datat["celularrr"] : '';
        $datat["subsidioapelacionrr"] = (isset($datat["subsidioapelacionrr"])) ? $datat["subsidioapelacionrr"] : '';
        $datat["soloapelacionrr"] = (isset($datat["soloapelacionrr"])) ? $datat["soloapelacionrr"] : '';
        $datat["tipocertificado"] = (isset($datat["tipocertificado"])) ? $datat["tipocertificado"] : '';
        $datat["explicacion"] = (isset($datat["explicacion"])) ? $datat["explicacion"] : '';
        $datat["textolibre"] = (isset($datat["textolibre"])) ? $datat["textolibre"] : '';

        $datat["ant_dircom"] = (isset($datat["ant_dircom"])) ? $datat["ant_dircom"] : '';
        $datat["ant_telcom1"] = (isset($datat["ant_telcom1"])) ? $datat["ant_telcom1"] : '';
        $datat["ant_telcom2"] = (isset($datat["ant_telcom2"])) ? $datat["ant_telcom2"] : '';
        $datat["ant_faxcom"] = (isset($datat["ant_faxcom"])) ? $datat["ant_faxcom"] : '';
        $datat["ant_celcom"] = (isset($datat["ant_celcom"])) ? $datat["ant_celcom"] : '';
        $datat["ant_barriocom"] = (isset($datat["ant_barriocom"])) ? $datat["ant_barriocom"] : '';
        $datat["ant_muncom"] = (isset($datat["ant_muncom"])) ? $datat["ant_muncom"] : '';
        $datat["ant_emailcom"] = (isset($datat["ant_emailcom"])) ? $datat["ant_emailcom"] : '';
        $datat["ant_emailcom2"] = (isset($datat["ant_emailcom2"])) ? $datat["ant_emailcom2"] : '';
        $datat["ant_emailcom3"] = (isset($datat["ant_emailcom3"])) ? $datat["ant_emailcom3"] : '';
        $datat["ant_numpredial"] = (isset($datat["ant_numpredial"])) ? $datat["ant_numpredial"] : '';
        $datat["dircom"] = (isset($datat["dircom"])) ? $datat["dircom"] : '';
        $datat["telcom1"] = (isset($datat["telcom1"])) ? $datat["telcom1"] : '';
        $datat["telcom2"] = (isset($datat["telcom2"])) ? $datat["telcom2"] : '';
        $datat["faxcom"] = (isset($datat["faxcom"])) ? $datat["faxcom"] : '';
        $datat["celcom"] = (isset($datat["celcom"])) ? $datat["celcom"] : '';
        $datat["barriocom"] = (isset($datat["barriocom"])) ? $datat["barriocom"] : '';
        $datat["muncom"] = (isset($datat["muncom"])) ? $datat["muncom"] : '';
        $datat["emailcom"] = (isset($datat["emailcom"])) ? $datat["emailcom"] : '';
        $datat["emailcom2"] = (isset($datat["emailcom2"])) ? $datat["emailcom2"] : '';
        $datat["emailcom3"] = (isset($datat["emailcom3"])) ? $datat["emailcom3"] : '';
        $datat["numpredial"] = (isset($datat["numpredial"])) ? $datat["numpredial"] : '';

        $datat["ant_dirnot"] = (isset($datat["ant_dirnot"])) ? $datat["ant_dirnot"] : '';
        $datat["ant_telnot1"] = (isset($datat["ant_telnot1"])) ? $datat["ant_telnot1"] : '';
        $datat["ant_telnot2"] = (isset($datat["ant_telnot2"])) ? $datat["ant_telnot2"] : '';
        $datat["ant_faxnot"] = (isset($datat["ant_faxnot"])) ? $datat["ant_faxnot"] : '';
        $datat["ant_celnot"] = (isset($datat["ant_celnot"])) ? $datat["ant_celnot"] : '';
        $datat["ant_barrionot"] = (isset($datat["ant_barrionot"])) ? $datat["ant_barrionot"] : '';
        $datat["ant_munnot"] = (isset($datat["ant_munnot"])) ? $datat["ant_munnot"] : '';
        $datat["ant_emailnot"] = (isset($datat["ant_emailnot"])) ? $datat["ant_emailnot"] : '';
        $datat["dirnot"] = (isset($datat["dirnot"])) ? $datat["dirnot"] : '';
        $datat["telnot1"] = (isset($datat["telnot1"])) ? $datat["telnot1"] : '';
        $datat["telnot2"] = (isset($datat["telnot2"])) ? $datat["telnot2"] : '';
        $datat["faxnot"] = (isset($datat["faxnot"])) ? $datat["faxnot"] : '';
        $datat["celnot"] = (isset($datat["celnot"])) ? $datat["celnot"] : '';
        $datat["barrionot"] = (isset($datat["barrionot"])) ? $datat["barrionot"] : '';
        $datat["munnot"] = (isset($datat["munnot"])) ? $datat["munnot"] : '';
        $datat["emailnot"] = (isset($datat["emailnot"])) ? $datat["emailnot"] : '';

        $datat["ant_versionciiu"] = (isset($datat["ant_versionciiu"])) ? $datat["ant_versionciiu"] : '';
        $datat["ant_ciiu11"] = (isset($datat["ant_ciiu11"])) ? $datat["ant_ciiu11"] : '';
        $datat["ant_ciiu12"] = (isset($datat["ant_ciiu12"])) ? $datat["ant_ciiu12"] : '';
        $datat["ant_ciiu13"] = (isset($datat["ant_ciiu13"])) ? $datat["ant_ciiu13"] : '';
        $datat["ant_ciiu14"] = (isset($datat["ant_ciiu14"])) ? $datat["ant_ciiu14"] : '';
        $datat["ant_ciiu21"] = (isset($datat["ant_ciiu21"])) ? $datat["ant_ciiu21"] : '';
        $datat["ant_ciiu22"] = (isset($datat["ant_ciiu22"])) ? $datat["ant_ciiu22"] : '';
        $datat["ant_ciiu23"] = (isset($datat["ant_ciiu23"])) ? $datat["ant_ciiu23"] : '';
        $datat["ant_ciiu24"] = (isset($datat["ant_ciiu24"])) ? $datat["ant_ciiu24"] : '';
        $datat["versionciiu"] = (isset($datat["versionciiu"])) ? $datat["versionciiu"] : '';
        $datat["ciiu11"] = (isset($datat["ciiu11"])) ? $datat["ciiu11"] : '';
        $datat["ciiu12"] = (isset($datat["ciiu12"])) ? $datat["ciiu12"] : '';
        $datat["ciiu13"] = (isset($datat["ciiu13"])) ? $datat["ciiu13"] : '';
        $datat["ciiu14"] = (isset($datat["ciiu14"])) ? $datat["ciiu14"] : '';
        $datat["ciiu21"] = (isset($datat["ciiu21"])) ? $datat["ciiu21"] : '';
        $datat["ciiu22"] = (isset($datat["ciiu22"])) ? $datat["ciiu22"] : '';
        $datat["ciiu23"] = (isset($datat["ciiu23"])) ? $datat["ciiu23"] : '';
        $datat["ciiu24"] = (isset($datat["ciiu24"])) ? $datat["ciiu24"] : '';

        $datat["nombreanterior"] = (isset($datat["nombreanterior"])) ? $datat["nombreanterior"] : '';
        $datat["nombrenuevo"] = (isset($datat["nombrenuevo"])) ? $datat["nombrenuevo"] : '';
        $datat["nuevonombre"] = (isset($datat["nuevonombre"])) ? $datat["nuevonombre"] : '';

        $datat["matriculaafiliado"] = (isset($datat["matriculaafiliado"])) ? $datat["matriculaafiliado"] : '';
        $datat["opcionafiliado"] = (isset($datat["opcionafiliado"])) ? $datat["opcionafiliado"] : '';
        $datat["saldoafiliado"] = (isset($datat["saldoafiliado"])) ? $datat["saldoafiliado"] : 0;
        $datat["ultanorenafi"] = (isset($datat["ultanorenafi"])) ? $datat["ultanorenafi"] : '';

        $datat["rues_empleados"] = (isset($datat["rues_empleados"])) ? $datat["rues_empleados"] : '';
        $datat["rues_numerointerno"] = (isset($datat["rues_numerointerno"])) ? $datat["rues_numerointerno"] : '';
        $datat["rues_numerounico"] = (isset($datat["rues_numerounico"])) ? $datat["rues_numerounico"] : '';
        $datat["rues_camarareceptora"] = (isset($datat["rues_camarareceptora"])) ? $datat["rues_camarareceptora"] : '';
        $datat["rues_camararesponsable"] = (isset($datat["rues_camararesponsable"])) ? $datat["rues_camararesponsable"] : '';
        $datat["rues_matricula"] = (isset($datat["rues_matricula"])) ? $datat["rues_matricula"] : '';
        $datat["rues_proponente"] = (isset($datat["rues_proponente"])) ? $datat["rues_proponente"] : '';
        $datat["rues_nombreregistrado"] = (isset($datat["rues_nombreregistrado"])) ? $datat["rues_nombreregistrado"] : '';
        $datat["rues_claseidentificacion"] = (isset($datat["rues_claseidentificacion"])) ? $datat["rues_claseidentificacion"] : '';
        $datat["rues_numeroidentificacion"] = (isset($datat["rues_numeroidentificacion"])) ? $datat["rues_numeroidentificacion"] : '';
        $datat["rues_dv"] = (isset($datat["rues_dv"])) ? $datat["rues_dv"] : '';
        $datat["rues_estado_liquidacion"] = (isset($datat["rues_estado_liquidacion"])) ? $datat["rues_estado_liquidacion"] : '';
        $datat["rues_estado_transaccion"] = (isset($datat["rues_estado_transaccion"])) ? $datat["rues_estado_transaccion"] : '';
        $datat["rues_nombrepagador"] = (isset($datat["rues_nombrepagador"])) ? $datat["rues_nombrepagador"] : '';
        $datat["rues_origendocumento"] = (isset($datat["rues_origendocumento"])) ? $datat["rues_origendocumento"] : '';
        $datat["rues_fechadocumento"] = (isset($datat["rues_fechadocumento"])) ? $datat["rues_fechadocumento"] : '';
        $datat["rues_fechapago"] = (isset($datat["rues_fechapago"])) ? $datat["rues_fechapago"] : '';
        $datat["rues_numerofactura"] = (isset($datat["rues_numerofactura"])) ? $datat["rues_numerofactura"] : '';
        $datat["rues_referenciaoperacion"] = (isset($datat["rues_referenciaoperacion"])) ? $datat["rues_referenciaoperacion"] : '';
        $datat["rues_totalpagado"] = (isset($datat["rues_totalpagado"])) ? $datat["rues_totalpagado"] : 0;
        $datat["rues_formapago"] = (isset($datat["rues_formapago"])) ? $datat["rues_formapago"] : '';
        $datat["rues_indicadororigen"] = (isset($datat["rues_indicadororigen"])) ? $datat["rues_indicadororigen"] : '';
        $datat["rues_indicadorbeneficio"] = (isset($datat["rues_indicadorbeneficio"])) ? $datat["rues_indicadorbeneficio"] : 0;
        $datat["rues_fecharespuesta"] = (isset($datat["rues_fecharespuesta"])) ? $datat["rues_fecharespuesta"] : '';
        $datat["rues_codigoservicioradicar"] = (isset($datat["rues_codigoservicioradicar"])) ? $datat["rues_codigoservicioradicar"] : '';
        $datat["rues_horarespuesta"] = (isset($datat["rues_horarespuesta"])) ? $datat["rues_horarespuesta"] : '';
        $datat["rues_codigoerror"] = (isset($datat["rues_codigoerror"])) ? $datat["rues_codigoerror"] : '';
        $datat["rues_mensajeerror"] = (isset($datat["rues_mensajeerror"])) ? $datat["rues_mensajeerror"] : '';
        $datat["rues_firmadigital"] = (isset($datat["rues_firmadigital"])) ? $datat["rues_firmadigital"] : '';
        $datat["rues_caracteres_por_linea"] = (isset($datat["rues_caracteres_por_linea"])) ? $datat["rues_caracteres_por_linea"] : '';
        $datat["rues_texto"] = (isset($datat["rues_texto"])) ? $datat["rues_texto"] : array();

        $datat["tipoidefirmante"] = (isset($datat["tipoidefirmante"])) ? $datat["tipoidefirmante"] : '';
        $datat["identificacionfirmante"] = (isset($datat["identificacionfirmante"])) ? $datat["identificacionfirmante"] : '';
        $datat["fechaexpfirmante"] = (isset($datat["fechaexpfirmante"])) ? $datat["fechaexpfirmante"] : '';
        $datat["apellido1firmante"] = (isset($datat["apellido1firmante"])) ? $datat["apellido1firmante"] : '';
        $datat["apellido2firmante"] = (isset($datat["apellido2firmante"])) ? $datat["apellido2firmante"] : '';
        $datat["nombre1firmante"] = (isset($datat["nombre1firmante"])) ? $datat["nombre1firmante"] : '';
        $datat["nombre2firmante"] = (isset($datat["nombre2firmante"])) ? $datat["nombre2firmante"] : '';
        $datat["emailfirmante"] = (isset($datat["emailfirmante"])) ? $datat["emailfirmante"] : '';
        $datat["emailfirmanteseguimiento"] = (isset($datat["emailfirmanteseguimiento"])) ? $datat["emailfirmanteseguimiento"] : '';
        $datat["celularfirmante"] = (isset($datat["celularfirmante"])) ? $datat["celularfirmante"] : '';
        $datat["direccionfirmante"] = (isset($datat["direccionfirmante"])) ? $datat["direccionfirmante"] : '';
        $datat["municipiofirmante"] = (isset($datat["municipiofirmante"])) ? $datat["municipiofirmante"] : '';
        $datat["firmadoelectronicamente"] = (isset($datat["firmadoelectronicamente"])) ? $datat["firmadoelectronicamente"] : '';
        $datat["firmadomanuscrita"] = (isset($datat["firmadomanuscrita"])) ? $datat["firmadomanuscrita"] : '';

        $datat["emailcontactoasesoria"] = (isset($datat["emailcontactoasesoria"])) ? $datat["emailcontactoasesoria"] : '';
        $datat["comentariosasesoria"] = (isset($datat["comentariosasesoria"])) ? $datat["comentariosasesoria"] : '';
        $datat["pedirbalance"] = (isset($datat["pedirbalance"])) ? $datat["pedirbalance"] : '';
        $datat["quienasesora"] = (isset($datat["quienasesora"])) ? $datat["quienasesora"] : '';

        $datat["incrementocupocertificados"] = (isset($datat["incrementocupocertificados"])) ? $datat["incrementocupocertificados"] : 0;
        $datat["aceptadoprepago"] = (isset($datat["aceptadoprepago"])) ? $datat["aceptadoprepago"] : 'NO';
        $datat["propcamaraorigen"] = (isset($datat["propcamaraorigen"])) ? $datat["propcamaraorigen"] : '';
        $datat["propidmunicipioorigen"] = (isset($datat["propidmunicipioorigen"])) ? $datat["propidmunicipioorigen"] : '';
        $datat["propidmunicipiodestino"] = (isset($datat["propidmunicipiodestino"])) ? $datat["propidmunicipiodestino"] : '';
        $datat["propproponenteorigen"] = (isset($datat["propproponenteorigen"])) ? $datat["propproponenteorigen"] : '';
        $datat["propfechaultimainscripcion"] = (isset($datat["propfechaultimainscripcion"])) ? $datat["propfechaultimainscripcion"] : '';
        $datat["propfechaultimarenovacion"] = (isset($datat["propfechaultimarenovacion"])) ? $datat["propfechaultimarenovacion"] : '';
        $datat["propdircom"] = (isset($datat["propdircom"])) ? $datat["propdircom"] : '';
        $datat["propmuncom"] = (isset($datat["propmuncom"])) ? $datat["propmuncom"] : '';
        $datat["proptelcom1"] = (isset($datat["proptelcom1"])) ? $datat["proptelcom1"] : '';
        $datat["proptelcom2"] = (isset($datat["proptelcom2"])) ? $datat["proptelcom2"] : '';
        $datat["proptelcom3"] = (isset($datat["proptelcom3"])) ? $datat["proptelcom3"] : '';
        $datat["propemailcom"] = (isset($datat["propemailcom"])) ? $datat["propemailcom"] : '';
        $datat["propdirnot"] = (isset($datat["propdirnot"])) ? $datat["propdirnot"] : '';
        $datat["propmunnot"] = (isset($datat["propmunnot"])) ? $datat["propmunnot"] : '';
        $datat["proptelnot1"] = (isset($datat["proptelnot1"])) ? $datat["proptelnot1"] : '';
        $datat["proptelnot2"] = (isset($datat["proptelnot2"])) ? $datat["proptelnot2"] : '';
        $datat["proptelnot3"] = (isset($datat["proptelnot3"])) ? $datat["proptelnot3"] : '';
        $datat["propemailnot"] = (isset($datat["propemailnot"])) ? $datat["propemailnot"] : '';

        $datat["enviara"] = (isset($datat["enviara"])) ? $datat["enviara"] : '';
        $datat["emailenviocertificados"] = (isset($datat["emailenviocertificados"])) ? $datat["emailenviocertificados"] : '';

        $datat["reingresoautomatico"] = (isset($datat["reingresoautomatico"])) ? $datat["reingresoautomatico"] : '';
        $datat["cantidadfolios"] = (isset($datat["cantidadfolios"])) ? $datat["cantidadfolios"] : '';
        $datat["cantidadhojas"] = (isset($datat["cantidadhojas"])) ? $datat["cantidadhojas"] : '';
        $datat["vueltas"] = (isset($datat["vueltas"])) ? $datat["vueltas"] : '';
        $datat["emailcontrol"] = (isset($datat["emailcontrol"])) ? $datat["emailcontrol"] : '';
        if ($datat["emailcontrol"] == '') {
            if (isset($_SESSION["generales"]["emailusuariocontrol"]) && $_SESSION["generales"]["emailusuariocontrol"] != '') {
                $datat["emailcontrol"] = $_SESSION["generales"]["emailusuariocontrol"];
            }
        }

        $datat["cumplorequisitosbenley1780"] = (isset($datat["cumplorequisitosbenley1780"])) ? $datat["cumplorequisitosbenley1780"] : '';
        $datat["mantengorequisitosbenley1780"] = (isset($datat["mantengorequisitosbenley1780"])) ? $datat["mantengorequisitosbenley1780"] : '';
        $datat["renunciobeneficiosley1780"] = (isset($datat["renunciobeneficiosley1780"])) ? $datat["renunciobeneficiosley1780"] : '';
        $datat["controlactividadaltoimpacto"] = (isset($datat["controlactividadaltoimpacto"])) ? $datat["controlactividadaltoimpacto"] : '';
        $datat["multadoponal"] = (isset($datat["multadoponal"])) ? $datat["multadoponal"] : '';
        $datat["tramitepresencial"] = (isset($datat["tramitepresencial"])) ? $datat["tramitepresencial"] : '';
        if ($datat["tramitepresencial"] == '') {
            if (!isset($datat["cajero"]) || $datat["cajero"] == 'USUPUBXX') {
                $datat["tramitepresencial"] = '1'; // Trámite virtual
            } else {
                $datat["tramitepresencial"] = '4'; // Trámite presencial
            }
        }
        $datat["cobrarmutacion"] = (isset($datat["cobrarmutacion"])) ? $datat["cobrarmutacion"] : '';
        $datat["anodeposito"] = (isset($datat["anodeposito"])) ? $datat["anodeposito"] : '';
        $datat["factorfirmado"] = (isset($datat["factorfirmado"])) ? $datat["factorfirmado"] : '';
        $datat["firmante"] = (isset($datat["firmante"])) ? $datat["firmante"] : '';
        $datat["exigeverificado"] = (isset($datat["exigeverificado"])) ? $datat["exigeverificado"] : '';
        $datat["idmotivocancelacion"] = (isset($datat["idmotivocancelacion"])) ? $datat["idmotivocancelacion"] : '';
        $datat["motivocancelacion"] = (isset($datat["motivocancelacion"])) ? $datat["motivocancelacion"] : '';
        $datat["ctrrenovartodos"] = (isset($datat["ctrrenovartodos"])) ? $datat["ctrrenovartodos"] : '';
        $datat["urlretorno"] = (isset($datat["urlretorno"])) ? $datat["urlretorno"] : '';
        $datat["webhook"] = (isset($datat["webhook"])) ? $datat["webhook"] : '';
        $datat["tramitedevuelto"] = (isset($datat["tramitedevuelto"])) ? $datat["tramitedevuelto"] : '';
        $datat["botonesauxiliares"] = (isset($datat["botonesauxiliares"])) ? $datat["botonesauxiliares"] : '';
        $datat["gateway"] = (isset($datat["gateway"])) ? $datat["gateway"] : '';
        $datat["iddirectlink"] = (isset($datat["iddirectlink"])) ? $datat["iddirectlink"] : '';
        $datat["datosfijados"] = (isset($datat["datosfijados"])) ? $datat["datosfijados"] : '';
        $datat["tamanoempresarial957"] = (isset($datat["tamanoempresarial957"])) ? $datat["tamanoempresarial957"] : '';
        $datat["nombrebaseliquidacion"] = (isset($datat["nombrebaseliquidacion"])) ? $datat["nombrebaseliquidacion"] : '';
        $datat["tipdocbaseliquidacion"] = (isset($datat["tipdocbaseliquidacion"])) ? $datat["tipdocbaseliquidacion"] : '';
        $datat["numdocbaseliquidacion"] = (isset($datat["numdocbaseliquidacion"])) ? $datat["numdocbaseliquidacion"] : '';
        $datat["fecdocbaseliquidacion"] = (isset($datat["fecdocbaseliquidacion"])) ? $datat["fecdocbaseliquidacion"] : '';
        $datat["mundocbaseliquidacion"] = (isset($datat["mundocbaseliquidacion"])) ? $datat["mundocbaseliquidacion"] : '';
        $datat["oridocbaseliquidacion"] = (isset($datat["oridocbaseliquidacion"])) ? $datat["oridocbaseliquidacion"] : '';
        $datat["tiporegistrobaseliquidacion"] = (isset($datat["tiporegistrobaseliquidacion"])) ? $datat["tiporegistrobaseliquidacion"] : '';
        $datat["forzardescuento1756"] = (isset($datat["forzardescuento1756"])) ? $datat["forzardescuento1756"] : '';
        $datat["asignodatosfacturacion"] = (isset($datat["asignodatosfacturacion"])) ? $datat["asignodatosfacturacion"] : '';
        $datat["sistemacreacion"] = (isset($datat["sistemacreacion"])) ? $datat["sistemacreacion"] : '';
        $datat["numerounicoreciboexterno"] = (isset($datat["numerounicoreciboexterno"])) ? $datat["numerounicoreciboexterno"] : '';
        $datat["totalrecibo"] = (isset($datat["totalrecibo"])) ? $datat["totalrecibo"] : 0;
        $datat["numerorecibogob"] = (isset($datat["numerorecibogob"])) ? $datat["numerorecibogob"] : '';
        $datat["numerooperaciongob"] = (isset($datat["numerooperaciongob"])) ? $datat["numerooperaciongob"] : '';
        $datat["fecharecibogob"] = (isset($datat["fecharecibogob"])) ? $datat["fecharecibogob"] : '';
        $datat["horarecibogob"] = (isset($datat["horarecibogob"])) ? $datat["horarecibogob"] : '';
        $datat["totalrecibogob"] = (isset($datat["totalrecibogob"])) ? $datat["totalrecibogob"] : 0;

        $datat["motivoevidenciafotografica"] = (isset($datat["motivoevidenciafotografica"])) ? $datat["motivoevidenciafotografica"] : '';
        $datat["tramiteradicar"] = (isset($datat["tramiteradicar"])) ? $datat["tramiteradicar"] : '';
        $datat["domiciliobase"] = (isset($datat["domiciliobase"])) ? $datat["domiciliobase"] : '';
        $datat["tiposasbase"] = (isset($datat["tiposasbase"])) ? $datat["tiposasbase"] : '';
        $datat["activosbase"] = (isset($datat["activosbase"])) ? $datat["activosbase"] : 0;
        $datat["capitalbase"] = (isset($datat["capitalbase"])) ? $datat["capitalbase"] : 0;
        $datat["ingresosbase"] = (isset($datat["ingresosbase"])) ? $datat["ingresosbase"] : 0;
        $datat["ciiubase"] = (isset($datat["ciiubase"])) ? $datat["ciiubase"] : '';
        $datat["pagoirbase"] = (isset($datat["pagoirbase"])) ? $datat["pagoirbase"] : '';
        $datat["boletairbase"] = (isset($datat["boletairbase"])) ? $datat["boletairbase"] : '';
        $datat["fechaboletairbase"] = (isset($datat["fechaboletairbase"])) ? $datat["fechaboletairbase"] : '';
        $datat["gobernacionirbase"] = (isset($datat["gobernacionirbase"])) ? $datat["gobernacionirbase"] : '';
        $datat["beneficio1780base"] = (isset($datat["beneficio1780base"])) ? $datat["beneficio1780base"] : '';
        $datat["certificadosbase"] = (isset($datat["certificadosbase"])) ? $datat["certificadosbase"] : '';
        $datat["libroactasbase"] = (isset($datat["libroactasbase"])) ? $datat["libroactasbase"] : '';
        $datat["libroactascodigolfbase"] = (isset($datat["libroactascodigolfbase"])) ? $datat["libroactascodigolfbase"] : '';
        $datat["libroactasnombrelfbase"] = (isset($datat["libroactasnombrelfbase"])) ? $datat["libroactasnombrelfbase"] : '';
        $datat["libroactashojaslfbase"] = (isset($datat["libroactashojaslfbase"])) ? $datat["libroactashojaslfbase"] : '';
        $datat["libroactascodigolebase"] = (isset($datat["libroactascodigolebase"])) ? $datat["libroactascodigolebase"] : '';
        $datat["libroactasnombrelebase"] = (isset($datat["libroactasnombrelebase"])) ? $datat["libroactasnombrelebase"] : '';
        $datat["libroaccionistasbase"] = (isset($datat["libroaccionistasbase"])) ? $datat["libroaccionistasbase"] : '';
        $datat["libroaccionistascodigolfbase"] = (isset($datat["libroaccionistascodigolfbase"])) ? $datat["libroaccionistascodigolfbase"] : '';
        $datat["libroaccionistasnombrelfbase"] = (isset($datat["libroaccionistasnombrelfbase"])) ? $datat["libroaccionistasnombrelfbase"] : '';
        $datat["libroaccionistashojaslfbase"] = (isset($datat["libroaccionistashojaslfbase"])) ? $datat["libroaccionistashojaslfbase"] : '';
        $datat["libroaccionistascodigolebase"] = (isset($datat["libroaccionistascodigolebase"])) ? $datat["libroaccionistascodigolebase"] : '';
        $datat["libroaccionistasnombrelebase"] = (isset($datat["libroaccionistasnombrelebase"])) ? $datat["libroaccionistasnombrelebase"] : '';
        $datat["liquidair"] = (isset($datat["liquidair"])) ? $datat["liquidair"] : '';
        $datat["horainicioliquidacion"] = (isset($datat["horainicioliquidacion"])) ? $datat["horainicioliquidacion"] : '';
        $datat["horafinalliquidacion"] = (isset($datat["horafinalliquidacion"])) ? $datat["horafinalliquidacion"] : '';
        $datat["fecharenaplicable"] = (isset($datat["fecharenaplicable"])) ? $datat["fecharenaplicable"] : '';
        $datat["fecharecibogenerar"] = (isset($datat["fecharecibogenerar"])) ? $datat["fecharecibogenerar"] : '';
        $datat["fecharenovacion"] = (isset($datat["fecharenovacion"])) ? $datat["fecharenovacion"] : '';
        $datat["totalcamara"] = (isset($datat["totalcamara"])) ? $datat["totalcamara"] : 0;
        $datat["totalcamara1"] = (isset($datat["totalcamara1"])) ? $datat["totalcamara1"] : 0;
        $datat["totalgobernacion"] = (isset($datat["totalgobernacion"])) ? $datat["totalgobernacion"] : 0;
        $datat["totalgobernacion1"] = (isset($datat["totalgobernacion1"])) ? $datat["totalgobernacion1"] : 0;
        $datat["tipogasto"] = (isset($datat["tipogasto"])) ? $datat["tipogasto"] : '';
        $datat["procesartodas"] = (isset($datat["procesartodas"])) ? $datat["procesartodas"] : '';
        $datat["tiporegistro"] = (isset($datat["tiporegistro"])) ? $datat["tiporegistro"] : '';
        $datat["tipotramiterue"] = (isset($datat["tipotramiterue"])) ? $datat["tipotramiterue"] : '';
        $datat["facturableelectronicamente"] = (isset($datat["facturableelectronicamente"])) ? $datat["facturableelectronicamente"] : '';
        $datat["solicitaractivopropietario"] = (isset($datat["solicitaractivopropietario"])) ? $datat["solicitaractivopropietario"] : '';

        $existeHoraInicioliquidacion = '';
        $result = ejecutarQueryMysqliApi($dbx, "SHOW COLUMNS FROM mreg_liquidacion WHERE Field = 'horainicioliquidacion'");
        if ($result && !empty($result)) {
            $existeHoraInicioliquidacion = 'si';
        }

        //        
        if (!isset($datat["facturableelectronicamente"]) || $datat["facturableelectronicamente"] == '') {
            $datat["facturableelectronicamente"] = 'no';
            if (isset($datat) && is_array($datat) && !empty($datat["liquidacion"])) {
                foreach ($datat["liquidacion"] as $lliq) {
                    if ($datat["facturableelectronicamente"] == 'no') {
                        $serv = retornarRegistroMysqliApi($dbx, 'mreg_servicios', "idservicio='" . $lliq["idservicio"] . "'");
                        if ($serv["facturable_electronicamente"] == 'SI') {
                            $datat["facturableelectronicamente"] = 'si';
                        }
                    }
                }
            }
        }

        //    
        $arrCampos = array(
            'idliquidacion',
            'fecha',
            'hora',
            'fechaultimamodificacion',
            'idusuario',
            'sede',
            'tipotramite',
            'iptramite',
            'idestado',
            'idexpedientebase',
            'idmatriculabase',
            'idproponentebase',
            'tipoidentificacionbase',
            'identificacionbase',
            'nombrebase',
            'organizacionbase',
            'categoriabase',
            'tipoidepnat',
            'idepnat',
            'nombrepnat',
            'actpnat',
            'perpnat',
            'numeromatriculapnat',
            'camarapnat',
            'nombreest',
            'actest',
            'ultanoren',
            'domicilioorigen',
            'domiciliodestino',
            'idtipoidentificacioncliente',
            'identificacioncliente',
            'nombrecliente',
            'apellidocliente',
            'email',
            'direccion',
            'idmunicipio',
            'telefono',
            'movil',
            'nombrepagador',
            'apellidopagador',
            'tipoidentificacionpagador',
            'identificacionpagador',
            'direccionpagador',
            'telefonopagador',
            'movilpagador',
            'municipiopagador',
            'emailpagador',
            'valorbruto',
            'valorbaseiva',
            'valoriva',
            'valortotal',
            'idsolicitudpago',
            'pagoefectivo',
            'pagocheque',
            'pagoconsignacion',
            'pagoqr',
            'pagovisa',
            'pagoach',
            'pagomastercard',
            'pagoamerican',
            'pagocredencial',
            'pagodiners',
            'pagotdebito',
            'pagoprepago',
            'pagoafiliado',
            'pagoacredito',
            'idformapago',
            'numerorecibo',
            'numerooperacion',
            'fecharecibo',
            'horarecibo',
            'totalrecibo',
            'numerorecibogob',
            'numerooperaciongob',
            'fecharecibogob',
            'horarecibogob',
            'totalrecibogob',
            'idfranquicia',
            'nombrefranquicia',
            'numeroautorizacion',
            'idcodban',
            'nombrebanco',
            'numerocheque',
            'numerorecuperacion',
            'numeroradicacion',
            'alertaid',
            'alertaservicio',
            'alertavalor',
            'ctrcancelacion',
            'idasesor',
            'numeroempleados',
            'pagoafiliacion',
            'numerofactura',
            'incluirformularios',
            'incluircertificados',
            'incluirdiploma',
            'incluircartulina',
            'matricularpnat',
            'matricularest',
            'regimentributario',
            'tipomatricula',
            'camaracambidom',
            'matriculacambidom',
            'municipiocambidom',
            'fecmatcambidom',
            'benart7',
            'controlfirma',
            'proyectocaja',
            'cargoafiliacion',
            'cargogastoadministrativo',
            'cargoentidadoficial',
            'cargoconsulta',
            'actualizacionciiuversion4',
            'reliquidacion',
            'capital',
            'tipodoc',
            'numdoc',
            'fechadoc',
            'origendoc',
            'mundoc',
            'organizacion',
            'categoria',
            'tipoiderepleg',
            'iderepleg',
            'nombrerepleg',
            'tipoideradicador',
            'ideradicador',
            'fechaexpradicador',
            'nombreradicador',
            'emailradicador',
            'telefonoradicador',
            'celularradicador',
            'nrocontrolsipref',
            'tramitepresencial',
            'firmadoelectronicamente',
            'firmadomanuscrita',
            'gateway',
            'emailcontrol',
            'cumplorequisitosbenley1780',
            'mantengorequisitosbenley1780',
            'renunciobeneficiosley1780',
            'controlactividadaltoimpacto',
            'multadoponal',
            'sistemacreacion'
        );

        $arrValues = array(
            $datat["numeroliquidacion"],
            "'" . $datat["fecha"] . "'",
            "'" . $datat["hora"] . "'",
            "'" . date("Ymd") . "'",
            "'" . $datat["idusuario"] . "'",
            "'" . $datat["sede"] . "'",
            "'" . $datat["tipotramite"] . "'",
            "'" . str_replace(",", "", $datat["iptramite"]) . "'",
            "'" . $datat["idestado"] . "'",
            "'" . ltrim($datat["idexpedientebase"], '0') . "'",
            "'" . ltrim($datat["idmatriculabase"], '0') . "'",
            "'" . ltrim($datat["idproponentebase"], '0') . "'",
            "'" . ltrim($datat["tipoidentificacionbase"], '0') . "'",
            "'" . ltrim($datat["identificacionbase"], '0') . "'",
            "'" . addslashes($datat["nombrebase"]) . "'",
            "'" . $datat["organizacionbase"] . "'",
            "'" . $datat["categoriabase"] . "'",
            "'" . $datat["tipoidepnat"] . "'",
            "'" . $datat["idepnat"] . "'",
            "'" . addslashes($datat["nombrepnat"]) . "'",
            doubleval($datat["actpnat"]),
            doubleval($datat["perpnat"]),
            "'" . $datat["numeromatriculapnat"] . "'",
            "'" . $datat["camarapnat"] . "'",
            "'" . addslashes($datat["nombreest"]) . "'",
            doubleval($datat["actest"]),
            "'" . $datat["ultanoren"] . "'",
            "'" . $datat["domicilioorigen"] . "'",
            "'" . $datat["domiciliodestino"] . "'",
            "'" . $datat["idtipoidentificacioncliente"] . "'",
            "'" . $datat["identificacioncliente"] . "'",
            "'" . addslashes($datat["nombrecliente"]) . "'",
            "'" . addslashes($datat["apellidocliente"]) . "'",
            "'" . addslashes($datat["email"]) . "'",
            "'" . addslashes($datat["direccion"]) . "'",
            "'" . $datat["idmunicipio"] . "'",
            "'" . $datat["telefono"] . "'",
            "'" . $datat["movil"] . "'",
            "'" . addslashes($datat["nombrepagador"]) . "'",
            "'" . addslashes($datat["apellidopagador"]) . "'",
            "'" . $datat["tipoidentificacionpagador"] . "'",
            "'" . $datat["identificacionpagador"] . "'",
            "'" . addslashes($datat["direccionpagador"]) . "'",
            "'" . $datat["telefonopagador"] . "'",
            "'" . $datat["movilpagador"] . "'",
            "'" . $datat["municipiopagador"] . "'",
            "'" . addslashes($datat["emailpagador"]) . "'",
            doubleval($datat["valorbruto"]),
            doubleval($datat["valorbaseiva"]),
            doubleval($datat["valoriva"]),
            doubleval($datat["valortotal"]),
            doubleval($datat["idsolicitudpago"]),
            doubleval($datat["pagoefectivo"]),
            doubleval($datat["pagocheque"]),
            doubleval($datat["pagoconsignacion"]),
            doubleval($datat["pagoqr"]),
            doubleval($datat["pagovisa"]),
            doubleval($datat["pagoach"]),
            doubleval($datat["pagomastercard"]),
            doubleval($datat["pagoamerican"]),
            doubleval($datat["pagocredencial"]),
            doubleval($datat["pagodiners"]),
            doubleval($datat["pagotdebito"]),
            doubleval($datat["pagoprepago"]),
            doubleval($datat["pagoafiliado"]),
            doubleval($datat["pagoacredito"]),
            "'" . $datat["idformapago"] . "'",
            "'" . $datat["numerorecibo"] . "'",
            "'" . $datat["numerooperacion"] . "'",
            "'" . $datat["fecharecibo"] . "'",
            "'" . $datat["horarecibo"] . "'",
            doubleval($datat["totalrecibo"]),
            "'" . $datat["numerorecibogob"] . "'",
            "'" . $datat["numerooperaciongob"] . "'",
            "'" . $datat["fecharecibogob"] . "'",
            "'" . $datat["horarecibogob"] . "'",
            doubleval($datat["totalrecibogob"]),
            "'" . $datat["idfranquicia"] . "'",
            "'" . $datat["nombrefranquicia"] . "'",
            "'" . $datat["numeroautorizacion"] . "'",
            "'" . $datat["idcodban"] . "'",
            "'" . $datat["nombrebanco"] . "'",
            "'" . $datat["numerocheque"] . "'",
            "'" . $datat["numerorecuperacion"] . "'",
            "'" . ltrim($datat["numeroradicacion"], '0') . "'",
            intval($datat["alertaid"]),
            "'" . $datat["alertaservicio"] . "'",
            doubleval($datat["alertavalor"]),
            "'" . $datat["ctrcancelacion"] . "'",
            "'" . $datat["idasesor"] . "'",
            intval($datat["numeroempleados"]),
            "'" . $datat["pagoafiliacion"] . "'",
            "'" . $datat["numerofactura"] . "'",
            "'" . $datat["incluirformularios"] . "'",
            "'" . $datat["incluircertificados"] . "'",
            "'" . $datat["incluirdiploma"] . "'",
            "'" . $datat["incluircartulina"] . "'",
            "'" . $datat["matricularpnat"] . "'",
            "'" . $datat["matricularest"] . "'",
            "'" . $datat["regimentributario"] . "'",
            "'" . $datat["tipomatricula"] . "'",
            "'" . $datat["camaracambidom"] . "'",
            "'" . $datat["matriculacambidom"] . "'",
            "'" . $datat["municipiocambidom"] . "'",
            "'" . $datat["fecmatcambidom"] . "'",
            "'" . $datat["benart7"] . "'",
            "'" . $datat["controlfirma"] . "'",
            "'" . $datat["proyectocaja"] . "'",
            "'" . $datat["cargoafiliacion"] . "'",
            "'" . $datat["cargogastoadministrativo"] . "'",
            "'" . $datat["cargoentidadoficial"] . "'",
            "'" . $datat["cargoconsulta"] . "'",
            "'" . $datat["actualizacionciiuversion4"] . "'",
            "'" . $datat["reliquidacion"] . "'",
            doubleval($datat["capital"]),
            "'" . $datat["tipodoc"] . "'",
            "'" . $datat["numdoc"] . "'",
            "'" . $datat["fechadoc"] . "'",
            "'" . addslashes($datat["origendoc"]) . "'",
            "'" . $datat["mundoc"] . "'",
            "'" . $datat["organizacion"] . "'",
            "'" . $datat["categoria"] . "'",
            "'" . $datat["tipoiderepleg"] . "'",
            "'" . $datat["iderepleg"] . "'",
            "'" . addslashes($datat["nombrerepleg"]) . "'",
            "'" . $datat["tipoideradicador"] . "'",
            "'" . $datat["ideradicador"] . "'",
            "'" . $datat["fechaexpradicador"] . "'",
            "'" . addslashes($datat["nombreradicador"]) . "'",
            "'" . addslashes($datat["emailradicador"]) . "'",
            "'" . $datat["telefonoradicador"] . "'",
            "'" . $datat["celularradicador"] . "'",
            "'" . addslashes($datat["nrocontrolsipref"]) . "'",
            "'" . $datat["tramitepresencial"] . "'",
            "'" . $datat["firmadoelectronicamente"] . "'",
            "'" . $datat["firmadomanuscrita"] . "'",
            "'" . $datat["gateway"] . "'",
            "'" . addslashes($datat["emailcontrol"]) . "'",
            "'" . $datat["cumplorequisitosbenley1780"] . "'",
            "'" . $datat["mantengorequisitosbenley1780"] . "'",
            "'" . $datat["renunciobeneficiosley1780"] . "'",
            "'" . $datat["controlactividadaltoimpacto"] . "'",
            "'" . $datat["multadoponal"] . "'",
            "'" . $datat["sistemacreacion"] . "'",
        );

        //
        if ($conteo == 0) {
            $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValues);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando liquidacion en mreg_liquidacion (' . $_SESSION["generales"]["mensajeerror"] . ')';
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return false;
            }
        }

        //
        if ($conteo > 0) {
            $condicion = 'idliquidacion=' . $datat["numeroliquidacion"];
            $result = regrabarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValues, $condicion);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error actualizando liquidacion en mreg_liquidacion (' . $_SESSION["generales"]["mensajeerror"] . ')';
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return false;
            }
        }

        //
        if ($datat["horainicioliquidacion"] != '') {
            if ($existeHoraInicioliquidacion == 'si') {
                $arrCampos = array(
                    'horainicioliquidacion',
                    'horafinalliquidacion'
                );
                $arrValues = array(
                    "'" . $datat["horainicioliquidacion"] . "'",
                    "'" . $datat["horafinalliquidacion"] . "'"
                );
                $result = regrabarRegistrosMysqliApi($dbx, 'mreg_liquidacion', $arrCampos, $arrValues, 'idliquidacion=' . $datat["numeroliquidacion"]);
            }
        }


        // Campos adicionales de la liquidación
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_campos', "idliquidacion=" . $datat["numeroliquidacion"]);

        //

        $res = array();
        $arrCampos = array(
            'idliquidacion',
            'campo',
            'contenido'
        );
        $ix = 0;
        $arrValores = array();

        if (trim($datat["tramiteautomatico"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tramiteautomatico'", "'" . addslashes($datat["tramiteautomatico"]) . "'");
        }
        if (trim($datat["particulacliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'particulacliente'", "'" . addslashes($datat["particulacliente"]) . "'");
        }
        if (trim($datat["zonapostal"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'zonapostal'", "'" . $datat["zonapostal"] . "'");
        }
        if (trim($datat["codigoregimen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'codigoregimen'", "'" . $datat["codigoregimen"] . "'");
        }
        if (trim($datat["responsabilidadtributaria"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'responsabilidadtributaria'", "'" . $datat["responsabilidadtributaria"] . "'");
        }
        if (trim($datat["codigoimpuesto"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'codigoimpuesto'", "'" . $datat["codigoimpuesto"] . "'");
        }
        if (trim($datat["nombreimpuesto"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombreimpuesto'", "'" . addslashes($datat["nombreimpuesto"]) . "'");
        }
        if (trim($datat["responsabilidadfiscal"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'responsabilidadfiscal'", "'" . $datat["responsabilidadfiscal"] . "'");
        }
        if (trim($datat["pais"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'pais'", "'" . $datat["pais"] . "'");
        }
        if (trim($datat["lenguaje"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'lenguaje'", "'" . $datat["lenguaje"] . "'");
        }
        if (trim($datat["direccionnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'direccionnot'", "'" . addslashes($datat["direccionnot"]) . "'");
        }
        if (trim($datat["idmunicipionot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'idmunicipionot'", "'" . $datat["idmunicipionot"] . "'");
        }
        if (trim($datat["codposcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'codposcom'", "'" . $datat["codposcom"] . "'");
        }
        if (trim($datat["codposnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'codposnot'", "'" . $datat["codposnot"] . "'");
        }

        if (trim($datat["anodeposito"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'anodeposito'", "'" . $datat["anodeposito"] . "'");
        }

        if (trim($datat["procesartodas"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'procesartodas'", "'" . $datat["procesartodas"] . "'");
        }

        if (trim($datat["origen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'origen'", "'" . addslashes($datat["origen"]) . "'");
        }

        if (trim($datat["subtipotramite"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'subtipotramite'", "'" . $datat["subtipotramite"] . "'");
        }

        if (trim($datat["tipoproponente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoproponente'", "'" . $datat["tipoproponente"] . "'");
        }

        if (trim($datat["matriculabase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'matriculabase'", "'" . $datat["matriculabase"] . "'");
        }

        if (trim($datat["nom1base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nom1base'", "'" . addslashes($datat["nom1base"]) . "'");
        }

        if (trim($datat["nom2base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nom2base'", "'" . addslashes($datat["nom2base"]) . "'");
        }

        if (trim($datat["ape1base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ape1base'", "'" . addslashes($datat["ape1base"]) . "'");
        }

        if (trim($datat["ape2base"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ape2base'", "'" . addslashes($datat["ape2base"]) . "'");
        }

        if (trim($datat["identificacionbase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificacionbase'", "'" . $datat["identificacionbase"] . "'");
        }

        if (trim($datat["organizacionbase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'organizacionbase'", "'" . $datat["organizacionbase"] . "'");
        }

        if (trim($datat["categoriabase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'categoriabase'", "'" . $datat["categoriabase"] . "'");
        }

        if (trim($datat["afiliadobase"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'afiliadobase'", "'" . $datat["afiliadobase"] . "'");
        }

        if (trim($datat["tipocliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipocliente'", "'" . $datat["tipocliente"] . "'");
        }

        if (trim($datat["razonsocialcliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'razonsocialcliente'", "'" . addslashes($datat["razonsocialcliente"]) . "'");
        }

        if (trim($datat["apellido1cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1cliente'", "'" . addslashes($datat["apellido1cliente"]) . "'");
        }

        if (trim($datat["apellido2cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2cliente'", "'" . addslashes($datat["apellido2cliente"]) . "'");
        }

        if (trim($datat["nombre1cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1cliente'", "'" . addslashes($datat["nombre1cliente"]) . "'");
        }

        if (trim($datat["nombre2cliente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2cliente'", "'" . addslashes($datat["nombre2cliente"]) . "'");
        }

        if (trim($datat["tipopagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipopagador'", "'" . $datat["tipopagador"] . "'");
        }
        if (trim($datat["razonsocialpagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'razonsocialpagador'", "'" . addslashes($datat["razonsocialpagador"]) . "'");
        }
        if (trim($datat["apellido1pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1pagador'", "'" . addslashes($datat["apellido1pagador"]) . "'");
        }
        if (trim($datat["apellido2pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2pagador'", "'" . addslashes($datat["apellido2pagador"]) . "'");
        }
        if (trim($datat["nombre1pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1pagador'", "'" . addslashes($datat["nombre1pagador"]) . "'");
        }
        if (trim($datat["nombre2pagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2pagador'", "'" . addslashes($datat["nombre2pagador"]) . "'");
        }

        if (trim($datat["modcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'modcom'", "'" . $datat["modcom"] . "'");
        }
        if (trim($datat["modnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'modnot'", "'" . $datat["modnot"] . "'");
        }
        if (trim($datat["modciiu"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'modciiu'", "'" . $datat["modciiu"] . "'");
        }
        if (trim($datat["modnombre"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'modnombre'", "'" . $datat["modnombre"] . "'");
        }
        if (trim($datat["fecrencambidom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fecrencambidom'", "'" . $datat["fecrencambidom"] . "'");
        }

        if (trim($datat["nombre1repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1repleg'", "'" . addslashes($datat["nombre1repleg"]) . "'");
        }
        if (trim($datat["nombre2repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2repleg'", "'" . addslashes($datat["nombre2repleg"]) . "'");
        }
        if (trim($datat["apellido1repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1repleg'", "'" . addslashes($datat["apellido1repleg"]) . "'");
        }
        if (trim($datat["apellido2repleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2repleg'", "'" . addslashes($datat["apellido2repleg"]) . "'");
        }
        if (trim($datat["cargorepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cargorepleg'", "'" . addslashes($datat["cargorepleg"]) . "'");
        }
        if (trim($datat["emailrepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailrepleg'", "'" . addslashes($datat["emailrepleg"]) . "'");
        }
        if (trim($datat["firmorepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'firmorepleg'", "'" . $datat["firmorepleg"] . "'");
        }
        if (trim($datat["celularrepleg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularrepleg'", "'" . $datat["celularrepleg"] . "'");
        }

        if (trim($datat["tipolibro"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipolibro'", "'" . $datat["tipolibro"] . "'");
        }
        if (trim($datat["codigolibro"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'codigolibro'", "'" . $datat["codigolibro"] . "'");
        }
        if (trim($datat["primeravez"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'primeravez'", "'" . $datat["primeravez"] . "'");
        }
        if (trim($datat["confirmadigital"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'confirmadigital'", "'" . $datat["confirmadigital"] . "'");
        }

        if (trim($datat["iderevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'iderevfis'", "'" . $datat["iderevfis"] . "'");
        }
        if (trim($datat["nombre1revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1revfis'", "'" . addslashes($datat["nombre1revfis"]) . "'");
        }
        if (trim($datat["nombre2revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2revfis'", "'" . addslashes($datat["nombre2revfis"]) . "'");
        }
        if (trim($datat["apellido1revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1revfis'", "'" . addslashes($datat["apellido1revfis"]) . "'");
        }
        if (trim($datat["apellido2revfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2revfis'", "'" . addslashes($datat["apellido2revfis"]) . "'");
        }
        if (trim($datat["cargorevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cargorevfis'", "'" . addslashes($datat["cargorevfis"]) . "'");
        }
        if (trim($datat["emailrevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailrevfis'", "'" . addslashes($datat["emailrevfis"]) . "'");
        }
        if (trim($datat["firmorevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'firmorevfis'", "'" . $datat["firmorevfis"] . "'");
        }
        if (trim($datat["celularrevfis"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularrevfis'", "'" . $datat["celularrevfis"] . "'");
        }

        if (trim($datat["idepreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'idepreasa'", "'" . $datat["idepreasa"] . "'");
        }
        if (trim($datat["nombre1preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1preasa'", "'" . addslashes($datat["nombre1preasa"]) . "'");
        }
        if (trim($datat["nombre2preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2preasa'", "'" . addslashes($datat["nombre2preasa"]) . "'");
        }
        if (trim($datat["apellido1preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1preasa'", "'" . addslashes($datat["apellido1preasa"]) . "'");
        }
        if (trim($datat["apellido2preasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2preasa'", "'" . addslashes($datat["apellido2preasa"]) . "'");
        }
        if (trim($datat["cargopreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cargopreasa'", "'" . addslashes($datat["cargopreasa"]) . "'");
        }
        if (trim($datat["emailpreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailpreasa'", "'" . addslashes($datat["emailpreasa"]) . "'");
        }
        if (trim($datat["firmopreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'firmopreasa'", "'" . addslashes($datat["firmopreasa"]) . "'");
        }
        if (trim($datat["celularpreasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularpreasa'", "'" . $datat["celularpreasa"] . "'");
        }

        if (trim($datat["idesecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'idesecasa'", "'" . addslashes($datat["idesecasa"]) . "'");
        }
        if (trim($datat["nombre1secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1secasa'", "'" . addslashes($datat["nombre1secasa"]) . "'");
        }
        if (trim($datat["nombre2secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2secasa'", "'" . addslashes($datat["nombre2secasa"]) . "'");
        }
        if (trim($datat["apellido1secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1secasa'", "'" . addslashes($datat["apellido1secasa"]) . "'");
        }
        if (trim($datat["apellido2secasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2secasa'", "'" . addslashes($datat["apellido2secasa"]) . "'");
        }
        if (trim($datat["cargosecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cargosecasa'", "'" . addslashes($datat["cargosecasa"]) . "'");
        }
        if (trim($datat["emailsecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailsecasa'", "'" . addslashes($datat["emailsecasa"]) . "'");
        }
        if (trim($datat["firmosecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'firmosecasa'", "'" . addslashes($datat["firmosecasa"]) . "'");
        }
        if (trim($datat["celularsecasa"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularsecasa'", "'" . $datat["celularsecasa"] . "'");
        }

        if (trim($datat["tipoidentificacionaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoidentificacionaceptante'", "'" . addslashes($datat["tipoidentificacionaceptante"]) . "'");
        }
        if (trim($datat["identificacionaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificacionaceptante'", "'" . addslashes($datat["identificacionaceptante"]) . "'");
        }
        if (trim($datat["nombre1aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1aceptante'", "'" . addslashes($datat["nombre1aceptante"]) . "'");
        }
        if (trim($datat["nombre2aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2aceptante'", "'" . addslashes($datat["nombre2aceptante"]) . "'");
        }
        if (trim($datat["apellido1aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1aceptante'", "'" . addslashes($datat["apellido1aceptante"]) . "'");
        }
        if (trim($datat["apellido2aceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2aceptante'", "'" . addslashes($datat["apellido2aceptante"]) . "'");
        }
        if (trim($datat["direccionaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'direccionaceptante'", "'" . addslashes($datat["direccionaceptante"]) . "'");
        }
        if (trim($datat["emailaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailaceptante'", "'" . addslashes($datat["emailaceptante"]) . "'");
        }
        if (trim($datat["municipioaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'municipioaceptante'", "'" . addslashes($datat["municipioaceptante"]) . "'");
        }
        if (trim($datat["telefonoaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telefonoaceptante'", "'" . addslashes($datat["telefonoaceptante"]) . "'");
        }
        if (trim($datat["celularaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularaceptante'", "'" . addslashes($datat["celularaceptante"]) . "'");
        }
        if (trim($datat["cargoaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cargoaceptante'", "'" . addslashes($datat["cargoaceptante"]) . "'");
        }
        if (trim($datat["fechadocideaceptante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fechadocideaceptante'", "'" . addslashes($datat["fechadocideaceptante"]) . "'");
        }

        if (trim($datat["motivocorreccion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'motivocorreccion'", "'" . addslashes($datat["motivocorreccion"]) . "'");
        }
        if (trim($datat["tipoerror1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoerror1'", "'" . addslashes($datat["tipoerror1"]) . "'");
        }
        if (trim($datat["tipoerror2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoerror2'", "'" . addslashes($datat["tipoerror2"]) . "'");
        }
        if (trim($datat["tipoerror3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoerror3'", "'" . addslashes($datat["tipoerror3"]) . "'");
        }
        if (trim($datat["tipoidentificacioncor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoidentificacioncor'", "'" . addslashes($datat["tipoidentificacioncor"]) . "'");
        }
        if (trim($datat["identificacioncor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificacioncor'", "'" . addslashes($datat["identificacioncor"]) . "'");
        }

        if (trim($datat["nombre1cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1cor'", "'" . addslashes($datat["nombre1cor"]) . "'");
        }
        if (trim($datat["nombre2cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2cor'", "'" . addslashes($datat["nombre2cor"]) . "'");
        }
        if (trim($datat["apellido1cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1cor'", "'" . addslashes($datat["apellido1cor"]) . "'");
        }
        if (trim($datat["apellido2cor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2cor'", "'" . addslashes($datat["apellido2cor"]) . "'");
        }
        if (trim($datat["direccioncor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'direccioncor'", "'" . addslashes($datat["direccioncor"]) . "'");
        }
        if (trim($datat["municipiocor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'municipiocor'", "'" . addslashes($datat["municipiocor"]) . "'");
        }
        if (trim($datat["emailcor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailcor'", "'" . addslashes($datat["emailcor"]) . "'");
        }
        if (trim($datat["telefonocor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telefonocor'", "'" . addslashes($datat["telefonocor"]) . "'");
        }
        if (trim($datat["celularcor"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularcor'", "'" . addslashes($datat["celularcor"]) . "'");
        }

        if (trim($datat["descripcionembargo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'descripcionembargo'", "'" . addslashes($datat["descripcionembargo"]) . "'");
        }
        if (trim($datat["descripciondesembargo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'descripciondesembargo'", "'" . addslashes($datat["descripciondesembargo"]) . "'");
        }
        if (trim($datat["tipoidentificaciondemandante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoidentificaciondemandante'", "'" . addslashes($datat["tipoidentificaciondemandante"]) . "'");
        }
        if (trim($datat["identificaciondemandante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificaciondemandante'", "'" . addslashes($datat["identificaciondemandante"]) . "'");
        }
        if (trim($datat["nombredemandante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombredemandante'", "'" . addslashes($datat["nombredemandante"]) . "'");
        }
        if (trim($datat["libro"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libro'", "'" . addslashes($datat["libro"]) . "'");
        }
        if (trim($datat["numreg"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'numreg'", "'" . addslashes($datat["numreg"]) . "'");
        }

        if (trim($datat["descripcionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'descripcionpqr'", "'" . addslashes($datat["descripcionpqr"]) . "'");
        }
        if (trim($datat["tipoidentificacionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoidentificacionpqr'", "'" . addslashes($datat["tipoidentificacionpqr"]) . "'");
        }
        if (trim($datat["identificacionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificacionpqr'", "'" . addslashes($datat["identificacionpqr"]) . "'");
        }
        if (trim($datat["nombre1pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1pqr'", "'" . addslashes($datat["nombre1pqr"]) . "'");
        }
        if (trim($datat["nombre2pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2pqr'", "'" . addslashes($datat["nombre2pqr"]) . "'");
        }
        if (trim($datat["apellido1pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1pqr'", "'" . addslashes($datat["apellido1pqr"]) . "'");
        }
        if (trim($datat["apellido2pqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2pqr'", "'" . addslashes($datat["apellido2pqr"]) . "'");
        }
        if (trim($datat["direccionpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'direccionpqr'", "'" . addslashes($datat["direccionpqr"]) . "'");
        }
        if (trim($datat["municipiopqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'municipiopqr'", "'" . addslashes($datat["municipiopqr"]) . "'");
        }
        if (trim($datat["emailpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailpqr'", "'" . addslashes($datat["emailpqr"]) . "'");
        }
        if (trim($datat["telefonopqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telefonopqr'", "'" . addslashes($datat["telefonopqr"]) . "'");
        }
        if (trim($datat["celularpqr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularpqr'", "'" . addslashes($datat["celularpqr"]) . "'");
        }

        if (trim($datat["descripcionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'descripcionrr'", "'" . addslashes($datat["descripcionrr"]) . "'");
        }
        if (trim($datat["inscripcionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'inscripcionrr'", "'" . addslashes($datat["inscripcionrr"]) . "'");
        }
        if (trim($datat["subsidioapelacionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'subsidioapelacionrr'", "'" . addslashes($datat["subsidioapelacionrr"]) . "'");
        }
        if (trim($datat["soloapelacionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'soloapelacionrr'", "'" . addslashes($datat["soloapelacionrr"]) . "'");
        }

        if (trim($datat["tipoidentificacionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoidentificacionrr'", "'" . addslashes($datat["tipoidentificacionrr"]) . "'");
        }
        if (trim($datat["identificacionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificacionrr'", "'" . addslashes($datat["identificacionrr"]) . "'");
        }
        if (trim($datat["nombre1rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1rr'", "'" . addslashes($datat["nombre1rr"]) . "'");
        }
        if (trim($datat["nombre2rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2rr'", "'" . addslashes($datat["nombre2rr"]) . "'");
        }
        if (trim($datat["apellido1rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1rr'", "'" . addslashes($datat["apellido1rr"]) . "'");
        }
        if (trim($datat["apellido2rr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2rr'", "'" . addslashes($datat["apellido2rr"]) . "'");
        }
        if (trim($datat["direccionrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'direccionrr'", "'" . addslashes($datat["direccionrr"]) . "'");
        }
        if (trim($datat["municipiorr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'municipiorr'", "'" . addslashes($datat["municipiorr"]) . "'");
        }
        if (trim($datat["emailrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailrr'", "'" . addslashes($datat["emailrr"]) . "'");
        }
        if (trim($datat["telefonorr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telefonorr'", "'" . addslashes($datat["telefonorr"]) . "'");
        }
        if (trim($datat["celularrr"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularrr'", "'" . addslashes($datat["celularrr"]) . "'");
        }

        if (trim($datat["tipocertificado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipocertificado'", "'" . addslashes($datat["tipocertificado"]) . "'");
        }
        if (trim($datat["explicacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'explicacion'", "'" . addslashes($datat["explicacion"]) . "'");
        }

        if (trim($datat["textolibre"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'textolibre'", "'" . addslashes($datat["textolibre"]) . "'");
        }

        if (trim($datat["matriculaafiliado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'matriculaafiliado'", "'" . addslashes($datat["matriculaafiliado"]) . "'");
        }

        if (trim($datat["opcionafiliado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'opcionafiliado'", "'" . addslashes($datat["opcionafiliado"]) . "'");
        }

        if ($datat["saldoafiliado"] != '' && doubleval($datat["saldoafiliado"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'saldoafiliado'", "'" . addslashes($datat["saldoafiliado"]) . "'");
        }

        if (trim($datat["ultanorenafi"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ultanorenafi'", "'" . addslashes($datat["ultanorenafi"]) . "'");
        }

        if (trim($datat["nombrepjur"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombrepjur'", "'" . addslashes($datat["nombrepjur"]) . "'");
        }

        if (trim($datat["nombresuc"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombresuc'", "'" . addslashes($datat["nombresuc"]) . "'");
        }

        if (trim($datat["nombreage"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombreage'", "'" . addslashes($datat["nombreage"]) . "'");
        }

        if (trim($datat["orgpnat"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'orgpnat'", "'" . addslashes($datat["orgpnat"]) . "'");
        }

        if (trim($datat["orgpjur"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'orgpjur'", "'" . addslashes($datat["orgpjur"]) . "'");
        }

        if (trim($datat["orgsuc"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'orgsuc'", "'" . addslashes($datat["orgsuc"]) . "'");
        }

        if (trim($datat["orgage"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'orgage'", "'" . addslashes($datat["orgage"]) . "'");
        }

        if (trim($datat["munpnat"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'munpnat'", "'" . addslashes($datat["munpnat"]) . "'");
        }

        if (trim($datat["munest"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'munest'", "'" . addslashes($datat["munest"]) . "'");
        }

        if (trim($datat["munpjur"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'munpjur'", "'" . addslashes($datat["munpjur"]) . "'");
        }

        if (trim($datat["munsuc"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'munsuc'", "'" . addslashes($datat["munsuc"]) . "'");
        }

        if (trim($datat["munage"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'munage'", "'" . addslashes($datat["munage"]) . "'");
        }

        if (trim($datat["perpjur"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'perpjur'", "'" . addslashes($datat["perpjur"]) . "'");
        }

        if (trim($datat["actpjur"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'actpjur'", "'" . addslashes($datat["actpjur"]) . "'");
        }

        if (trim($datat["actsuc"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'actsuc'", "'" . addslashes($datat["actsuc"]) . "'");
        }

        if (trim($datat["actage"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'actage'", "'" . addslashes($datat["actage"]) . "'");
        }

        if (trim($datat["ant_dircom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_dircom'", "'" . addslashes($datat["ant_dircom"]) . "'");
        }

        if (trim($datat["ant_telcom1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_telcom1'", "'" . $datat["ant_telcom1"] . "'");
        }

        if (trim($datat["ant_telcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_telcom2'", "'" . $datat["ant_telcom2"] . "'");
        }

        if (trim($datat["ant_faxcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_faxcom'", "'" . $datat["ant_faxcom"] . "'");
        }

        if (trim($datat["ant_celcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_celcom'", "'" . $datat["ant_celcom"] . "'");
        }

        if (trim($datat["ant_numpredial"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_numpredial'", "'" . $datat["ant_numpredial"] . "'");
        }

        if (trim($datat["ant_barriocom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_barriocom'", "'" . $datat["ant_barriocom"] . "'");
        }

        if (trim($datat["ant_muncom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_muncom'", "'" . $datat["ant_muncom"] . "'");
        }

        if (trim($datat["ant_emailcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_emailcom'", "'" . addslashes($datat["ant_emailcom"]) . "'");
        }

        if (trim($datat["ant_emailcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_emailcom2'", "'" . addslashes($datat["ant_emailcom2"]) . "'");
        }

        if (trim($datat["ant_emailcom3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_emailcom3'", "'" . addslashes($datat["ant_emailcom3"]) . "'");
        }

        if (trim($datat["dircom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'dircom'", "'" . addslashes($datat["dircom"]) . "'");
        }

        if (trim($datat["telcom1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telcom1'", "'" . $datat["telcom1"] . "'");
        }

        if (trim($datat["telcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telcom2'", "'" . $datat["telcom2"] . "'");
        }

        if (trim($datat["faxcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'faxcom'", "'" . $datat["faxcom"] . "'");
        }

        if (trim($datat["celcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celcom'", "'" . $datat["celcom"] . "'");
        }

        if (trim($datat["barriocom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'barriocom'", "'" . $datat["barriocom"] . "'");
        }

        if (trim($datat["muncom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'muncom'", "'" . $datat["muncom"] . "'");
        }

        if (trim($datat["numpredial"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'numpredial'", "'" . $datat["numpredial"] . "'");
        }

        if (trim($datat["emailcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailcom'", "'" . addslashes($datat["emailcom"]) . "'");
        }

        if (trim($datat["emailcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailcom2'", "'" . addslashes($datat["emailcom2"]) . "'");
        }

        if (trim($datat["emailcom3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailcom3'", "'" . addslashes($datat["emailcom3"]) . "'");
        }

        if (trim($datat["ant_dirnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_dirnot'", "'" . addslashes($datat["ant_dirnot"]) . "'");
        }

        if (trim($datat["ant_telnot1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_telnot1'", "'" . $datat["ant_telnot1"] . "'");
        }

        if (trim($datat["ant_telnot2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_telnot2'", "'" . $datat["ant_telnot2"] . "'");
        }

        if (trim($datat["ant_faxnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_faxnot'", "'" . $datat["ant_faxnot"] . "'");
        }

        if (trim($datat["ant_celnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_celnot'", "'" . $datat["ant_celnot"] . "'");
        }

        if (trim($datat["ant_barrionot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_barrionot'", "'" . $datat["ant_barrionot"] . "'");
        }

        if (trim($datat["ant_munnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_munnot'", "'" . $datat["ant_munnot"] . "'");
        }

        if (trim($datat["ant_emailnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_emailnot'", "'" . addslashes($datat["ant_emailnot"]) . "'");
        }

        if (trim($datat["dirnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'dirnot'", "'" . addslashes($datat["dirnot"]) . "'");
        }

        if (trim($datat["telnot1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telnot1'", "'" . $datat["telnot1"] . "'");
        }

        if (trim($datat["telnot2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'telnot2'", "'" . $datat["telnot2"] . "'");
        }

        if (trim($datat["faxnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'faxnot'", "'" . $datat["faxnot"] . "'");
        }

        if (trim($datat["celnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celnot'", "'" . $datat["celnot"] . "'");
        }

        if (trim($datat["barrionot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'barrionot'", "'" . $datat["barrionot"] . "'");
        }

        if (trim($datat["munnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'munnot'", "'" . $datat["munnot"] . "'");
        }

        if (trim($datat["emailnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailnot'", "'" . addslashes($datat["emailnot"]) . "'");
        }

// En caso de mutaciones - actividad
        if (trim($datat["ant_versionciiu"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_versionciiu'", "'" . $datat["ant_versionciiu"] . "'");
        }

        if (trim($datat["ant_ciiu11"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu11'", "'" . $datat["ant_ciiu11"] . "'");
        }

        if (trim($datat["ant_ciiu12"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu12'", "'" . $datat["ant_ciiu12"] . "'");
        }

        if (trim($datat["ant_ciiu13"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu13'", "'" . $datat["ant_ciiu13"] . "'");
        }

        if (trim($datat["ant_ciiu14"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu14'", "'" . $datat["ant_ciiu14"] . "'");
        }

        if (trim($datat["ant_ciiu21"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu21'", "'" . $datat["ant_ciiu21"] . "'");
        }

        if (trim($datat["ant_ciiu22"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu22'", "'" . $datat["ant_ciiu22"] . "'");
        }

        if (trim($datat["ant_ciiu23"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu23'", "'" . $datat["ant_ciiu23"] . "'");
        }

        if (trim($datat["ant_ciiu24"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ant_ciiu24'", "'" . $datat["ant_ciiu24"] . "'");
        }

        if (trim($datat["nombreanterior"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombreanterior'", "'" . addslashes($datat["nombreanterior"]) . "'");
        }

        if (trim($datat["nombrenuevo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombrenuevo'", "'" . addslashes($datat["nombrenuevo"]) . "'");
        }

        if (trim($datat["nuevonombre"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nuevonombre'", "'" . addslashes($datat["nuevonombre"]) . "'");
        }

        if (trim($datat["versionciiu"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'versionciiu'", "'" . $datat["versionciiu"] . "'");
        }

        if (trim($datat["ciiu11"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu11'", "'" . $datat["ciiu11"] . "'");
        }

        if (trim($datat["ciiu12"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu12'", "'" . $datat["ciiu12"] . "'");
        }

        if (trim($datat["ciiu13"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu13'", "'" . $datat["ciiu13"] . "'");
        }

        if (trim($datat["ciiu14"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu14'", "'" . $datat["ciiu14"] . "'");
        }

        if (trim($datat["ciiu21"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu21'", "'" . $datat["ciiu21"] . "'");
        }

        if (trim($datat["ciiu22"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu22'", "'" . $datat["ciiu22"] . "'");
        }

        if (trim($datat["ciiu23"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu23'", "'" . $datat["ciiu23"] . "'");
        }

        if (trim($datat["ciiu24"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiu24'", "'" . $datat["ciiu24"] . "'");
        }

        if (trim($datat["nombreanterior"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombreanterior'", "'" . addslashes($datat["nombreanterior"]) . "'");
        }

        if (trim($datat["nombrenuevo"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombrenuevo'", "'" . addslashes($datat["nombrenuevo"]) . "'");
        }

        if (trim($datat["rues_numerointerno"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_numerointerno'", "'" . addslashes($datat["rues_numerointerno"]) . "'");
        }

        if (trim($datat["rues_numerounico"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_numerounico'", "'" . addslashes($datat["rues_numerounico"]) . "'");
        }

        if (trim($datat["rues_camarareceptora"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_camarareceptora'", "'" . addslashes($datat["rues_camarareceptora"]) . "'");
        }

        if (trim($datat["rues_camararesponsable"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_camararesponsable'", "'" . addslashes($datat["rues_camararesponsable"]) . "'");
        }

        if (trim($datat["rues_codigoservicioradicar"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_codigoservicioradicar'", "'" . addslashes($datat["rues_codigoservicioradicar"]) . "'");
        }

        if (trim($datat["rues_matricula"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_matricula'", "'" . addslashes($datat["rues_matricula"]) . "'");
        }

        if (trim($datat["rues_proponente"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_proponente'", "'" . addslashes($datat["rues_proponente"]) . "'");
        }

        if (trim($datat["rues_nombreregistrado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_nombreregistrado'", "'" . addslashes($datat["rues_nombreregistrado"]) . "'");
        }

        if (trim($datat["rues_claseidentificacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_claseidentificacion'", "'" . $datat["rues_claseidentificacion"] . "'");
        }

        if (trim($datat["rues_numeroidentificacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_numeroidentificacion'", "'" . $datat["rues_numeroidentificacion"] . "'");
        }

        if (trim($datat["rues_dv"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_dv'", "'" . $datat["rues_dv"] . "'");
        }

        if (trim($datat["rues_estado_liquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_estado_liquidacion'", "'" . $datat["rues_estado_liquidacion"] . "'");
        }

        if (trim($datat["rues_estado_transaccion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_estado_transaccion'", "'" . $datat["rues_estado_transaccion"] . "'");
        }

        if (trim($datat["rues_nombrepagador"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_nombrepagador'", "'" . addslashes($datat["rues_nombrepagador"]) . "'");
        }

        if (trim($datat["rues_origendocumento"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_origendocumento'", "'" . addslashes($datat["rues_origendocumento"]) . "'");
        }

        if (trim($datat["rues_fechadocumento"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_fechadocumento'", "'" . addslashes($datat["rues_fechadocumento"]) . "'");
        }

        if (trim($datat["rues_fechapago"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_fechapago'", "'" . addslashes($datat["rues_fechapago"]) . "'");
        }

        if (trim($datat["rues_numerofactura"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_numerofactura'", "'" . addslashes($datat["rues_numerofactura"]) . "'");
        }

        if (trim($datat["rues_referenciaoperacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_referenciaoperacion'", "'" . addslashes($datat["rues_referenciaoperacion"]) . "'");
        }

        if (doubleval($datat["rues_totalpagado"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_totalpagado'", "'" . addslashes($datat["rues_totalpagado"]) . "'");
        }

        if (trim($datat["rues_formapago"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_formapago'", "'" . addslashes($datat["rues_formapago"]) . "'");
        }

        if (trim($datat["rues_indicadororigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_indicadororigen'", "'" . addslashes($datat["rues_indicadororigen"]) . "'");
        }

        if (doubleval($datat["rues_empleados"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_empleados'", "'" . addslashes($datat["rues_empleados"]) . "'");
        }

        if (trim($datat["rues_indicadorbeneficio"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_indicadorbeneficio'", "'" . addslashes($datat["rues_indicadorbeneficio"]) . "'");
        }

        if (trim($datat["rues_fecharespuesta"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_fecharespuesta'", "'" . addslashes($datat["rues_fecharespuesta"]) . "'");
        }

        if (trim($datat["rues_horarespuesta"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_horarespuesta'", "'" . addslashes($datat["rues_horarespuesta"]) . "'");
        }

        if (trim($datat["rues_codigoerror"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_codigoerror'", "'" . addslashes($datat["rues_codigoerror"]) . "'");
        }

        if (trim($datat["rues_mensajeerror"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_mensajeerror'", "'" . addslashes($datat["rues_mensajeerror"]) . "'");
        }

        if (trim($datat["rues_firmadigital"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_firmadigital'", "'" . addslashes($datat["rues_firmadigital"]) . "'");
        }

        if (doubleval($datat["rues_caracteres_por_linea"]) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'rues_caracteres_por_linea'", "'" . addslashes($datat["rues_caracteres_por_linea"]) . "'");
        }

        if (trim($datat["tipoidefirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipoidefirmante'", "'" . addslashes($datat["tipoidefirmante"]) . "'");
        }

        if (trim($datat["identificacionfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'identificacionfirmante'", "'" . addslashes($datat["identificacionfirmante"]) . "'");
        }

        if (trim($datat["fechaexpfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fechaexpfirmante'", "'" . addslashes($datat["fechaexpfirmante"]) . "'");
        }

        if (trim($datat["apellido1firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido1firmante'", "'" . addslashes($datat["apellido1firmante"]) . "'");
        }

        if (trim($datat["apellido2firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'apellido2firmante'", "'" . addslashes($datat["apellido2firmante"]) . "'");
        }

        if (trim($datat["nombre1firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre1firmante'", "'" . addslashes($datat["nombre1firmante"]) . "'");
        }

        if (trim($datat["nombre2firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombre2firmante'", "'" . addslashes($datat["nombre2firmante"]) . "'");
        }

        if (trim($datat["emailfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailfirmante'", "'" . addslashes($datat["emailfirmante"]) . "'");
        }

        if (trim($datat["emailfirmanteseguimiento"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailfirmanteseguimiento'", "'" . addslashes($datat["emailfirmanteseguimiento"]) . "'");
        }

        if (trim($datat["celularfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'celularfirmante'", "'" . addslashes($datat["celularfirmante"]) . "'");
        }

        if (trim($datat["direccionfirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'direccionfirmante'", "'" . addslashes($datat["direccionfirmante"]) . "'");
        }

        if (trim($datat["municipiofirmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'municipiofirmante'", "'" . addslashes($datat["municipiofirmante"]) . "'");
        }

        if (trim($datat["emailcontactoasesoria"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailcontactoasesoria'", "'" . addslashes($datat["emailcontactoasesoria"]) . "'");
        }

        if (trim($datat["comentariosasesoria"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'comentariosasesoria'", "'" . addslashes($datat["comentariosasesoria"]) . "'");
        }

        if (trim($datat["pedirbalance"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'pedirbalance'", "'" . addslashes($datat["pedirbalance"]) . "'");
        }

        if (trim($datat["quienasesora"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'quienasesora'", "'" . addslashes($datat["quienasesora"]) . "'");
        }

        if (trim($datat["benley1780"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'benley1780'", "'" . addslashes($datat["benley1780"]) . "'");
        }

        if (trim($datat["cumplorequisitosbenley1780"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cumple1780'", "'" . addslashes($datat["cumplorequisitosbenley1780"]) . "'");
        }

        if (trim($datat["mantengorequisitosbenley1780"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'mantiene1780'", "'" . addslashes($datat["mantengorequisitosbenley1780"]) . "'");
        }

        if (trim($datat["renunciobeneficiosley1780"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'renuncia1780'", "'" . addslashes($datat["renunciobeneficiosley1780"]) . "'");
        }

        if (trim($datat["fechanacimientopnat"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fechanacimientopnat'", "'" . addslashes($datat["fechanacimientopnat"]) . "'");
        }

        if (intval(trim($datat["incrementocupocertificados"])) != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'incrementocupocertificados'", "'" . $datat["incrementocupocertificados"] . "'");
        }

        if ($datat["cobrarmutacion"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cobrarmutacion'", "'" . $datat["cobrarmutacion"] . "'");
        }

        if (trim($datat["aceptadoprepago"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'aceptadoprepago'", "'" . $datat["aceptadoprepago"] . "'");
        }

        if (trim($datat["propcamaraorigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propcamaraorigen'", "'" . $datat["propcamaraorigen"] . "'");
        }

        if (trim($datat["propidmunicipioorigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propidmunicipioorigen'", "'" . $datat["propidmunicipioorigen"] . "'");
        }

        if (trim($datat["propidmunicipiodestino"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propidmunicipiodestino'", "'" . $datat["propidmunicipiodestino"] . "'");
        }

        if (trim($datat["propproponenteorigen"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propproponenteorigen'", "'" . $datat["propproponenteorigen"] . "'");
        }

        if (trim($datat["propfechaultimainscripcion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propfechaultimainscripcion'", "'" . $datat["propfechaultimainscripcion"] . "'");
        }

        if (trim($datat["propfechaultimarenovacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propfechaultimarenovacion'", "'" . $datat["propfechaultimarenovacion"] . "'");
        }

        if (trim($datat["propdircom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propdircom'", "'" . addslashes($datat["propdircom"]) . "'");
        }

        if (trim($datat["propmuncom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propmuncom'", "'" . $datat["propmuncom"] . "'");
        }

        if (trim($datat["proptelcom1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'proptelcom1'", "'" . $datat["proptelcom1"] . "'");
        }

        if (trim($datat["proptelcom2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'proptelcom2'", "'" . $datat["proptelcom2"] . "'");
        }

        if (trim($datat["proptelcom3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'proptelcom3'", "'" . $datat["proptelcom3"] . "'");
        }

        if (trim($datat["propemailcom"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propemailcom'", "'" . addslashes($datat["propemailcom"]) . "'");
        }

        if (trim($datat["propdirnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propdirnot'", "'" . addslashes($datat["propdirnot"]) . "'");
        }
        if (trim($datat["propmunnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propmunnot'", "'" . $datat["propmunnot"] . "'");
        }

        if (trim($datat["proptelnot1"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'proptelnot1'", "'" . $datat["proptelnot1"] . "'");
        }

        if (trim($datat["proptelnot2"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'proptelnot2'", "'" . $datat["proptelnot2"] . "'");
        }

        if (trim($datat["proptelnot3"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'proptelnot3'", "'" . $datat["proptelnot3"] . "'");
        }

        if (trim($datat["propemailnot"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'propemailnot'", "'" . addslashes($datat["propemailnot"]) . "'");
        }

        if (isset($datat["totalmatriculasrenovar"]) && trim($datat["totalmatriculasrenovar"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'totalmatriculasrenovar'", "'" . ($datat["totalmatriculasrenovar"]) . "'");
        }

        if (isset($datat["totalmatriculasrenovadas"]) && trim($datat["totalmatriculasrenovadas"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'totalmatriculasrenovadas'", "'" . ($datat["totalmatriculasrenovadas"]) . "'");
        }

        if (trim($datat["cantidadfolios"]) != '' && $datat["cantidadfolios"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cantidadfolios'", "'" . ($datat["cantidadfolios"]) . "'");
        }

        if (trim($datat["cantidadhojas"]) != '' && $datat["cantidadhojas"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cantidadhojas'", "'" . ($datat["cantidadhojas"]) . "'");
        }

        if (trim($datat["vueltas"]) != '' && $datat["vueltas"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'vueltas'", "'" . ($datat["vueltas"]) . "'");
        }

        if (trim($datat["enviara"]) != '' && $datat["enviara"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'enviara'", "'" . ($datat["enviara"]) . "'");
        }

        if (trim($datat["emailenviocertificados"]) != '' && $datat["emailenviocertificados"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'emailenviocertificados'", "'" . ($datat["emailenviocertificados"]) . "'");
        }

        if (trim($datat["reingresoautomatico"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'reingresoautomatico'", "'" . ($datat["reingresoautomatico"]) . "'");
        }

        if (trim($datat["factorfirmado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'factorfirmado'", "'" . ($datat["factorfirmado"]) . "'");
        }

        if (trim($datat["firmante"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'firmante'", "'" . ($datat["firmante"]) . "'");
        }

        if (trim($datat["exigeverificado"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'exigeverificado'", "'" . ($datat["exigeverificado"]) . "'");
        }

        if (trim($datat["idmotivocancelacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'idmotivocancelacion'", "'" . ($datat["idmotivocancelacion"]) . "'");
        }

        if (trim($datat["motivocancelacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'motivocancelacion'", "'" . ($datat["motivocancelacion"]) . "'");
        }

        if (trim($datat["ctrrenovartodos"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ctrrenovartodos'", "'" . ($datat["ctrrenovartodos"]) . "'");
        }

        if (trim($datat["urlretorno"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'urlretorno'", "'" . ($datat["urlretorno"]) . "'");
        }

        if (trim($datat["webhook"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'webhook'", "'" . ($datat["webhook"]) . "'");
        }

        if (trim($datat["tramitedevuelto"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tramitedevuelto'", "'" . ($datat["tramitedevuelto"]) . "'");
        }

        if (trim($datat["botonesauxiliares"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'botonesauxiliares'", "'" . ($datat["botonesauxiliares"]) . "'");
        }

        if (trim($datat["iddirectlink"]) != '' && $datat["iddirectlink"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'iddirectlink'", "'" . ($datat["iddirectlink"]) . "'");
        }

        if (trim($datat["datosfijados"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'datosfijados'", "'" . $datat["datosfijados"] . "'");
        }

        if (trim($datat["tamanoempresarial957"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tamanoempresarial957'", "'" . $datat["tamanoempresarial957"] . "'");
        }

        if (trim($datat["tipogasto"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipogasto'", "'" . $datat["tipogasto"] . "'");
        }

        if (trim($datat["nombrebaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombrebaseliquidacion'", "'" . addslashes($datat["nombrebaseliquidacion"]) . "'");
        }

        if (trim($datat["tipdocbaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipdocbaseliquidacion'", "'" . $datat["tipdocbaseliquidacion"] . "'");
        }

        if (trim($datat["numdocbaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'numdocbaseliquidacion'", "'" . $datat["numdocbaseliquidacion"] . "'");
        }

        if (trim($datat["fecdocbaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fecdocbaseliquidacion'", "'" . $datat["fecdocbaseliquidacion"] . "'");
        }

        if (trim($datat["mundocbaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'mundocbaseliquidacion'", "'" . $datat["mundocbaseliquidacion"] . "'");
        }

        if (trim($datat["oridocbaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'oridocbaseliquidacion'", "'" . $datat["oridocbaseliquidacion"] . "'");
        }

        if (trim($datat["tiporegistrobaseliquidacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tiporegistrobaseliquidacion'", "'" . $datat["tiporegistrobaseliquidacion"] . "'");
        }

        if (trim($datat["forzardescuento1756"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'forzardescuento1756'", "'" . $datat["forzardescuento1756"] . "'");
        }

        if (trim($datat["asignodatosfacturacion"]) != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'asignodatosfacturacion'", "'" . $datat["asignodatosfacturacion"] . "'");
        }

        if (isset($datat["cf_identificacionfactura"]) && $datat["cf_identificacionfactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_identificacionfactura'", "'" . $datat["cf_identificacionfactura"] . "'");
        }

        if (isset($datat["cf_numerofactura"]) && $datat["cf_numerofactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_numerofactura'", "'" . $datat["cf_numerofactura"] . "'");
        }

        if (isset($datat["cf_cuentafactura"]) && $datat["cf_cuentafactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_cuentafactura'", "'" . $datat["cf_cuentafactura"] . "'");
        }

        if (isset($datat["cf_tipofactura"]) && $datat["cf_tipofactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_tipofactura'", "'" . $datat["cf_tipofactura"] . "'");
        }

        if (isset($datat["cf_fuenteoriginalfactura"]) && $datat["cf_fuenteoriginalfactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_fuenteoriginalfactura'", "'" . $datat["cf_fuenteoriginalfactura"] . "'");
        }

        if (isset($datat["cf_numerofuenteoriginalfactura"]) && $datat["cf_numerofuenteoriginalfactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_numerofuenteoriginalfactura'", "'" . $datat["cf_numerofuenteoriginalfactura"] . "'");
        }

        if (isset($datat["cf_cuentaclientefactura"]) && $datat["cf_cuentaclientefactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_cuentaclientefactura'", "'" . $datat["cf_cuentaclientefactura"] . "'");
        }

        if (isset($datat["cf_periodofactura"]) && $datat["cf_periodofactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_periodofactura'", "'" . $datat["cf_periodofactura"] . "'");
        }

        if (isset($datat["cf_ccosfactura"]) && $datat["cf_ccosfactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_ccosfactura'", "'" . $datat["cf_ccosfactura"] . "'");
        }

        if (isset($datat["cf_fondofactura"]) && $datat["cf_fondofactura"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'cf_fondofactura'", "'" . $datat["cf_fondofactura"] . "'");
        }

        if (isset($datat["facturableelectronicamente"]) && $datat["facturableelectronicamente"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'facturableelectronicamente'", "'" . $datat["facturableelectronicamente"] . "'");
        }

        if (isset($datat["numerounicoreciboexterno"]) && $datat["numerounicoreciboexterno"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'numerounicoreciboexterno'", "'" . $datat["numerounicoreciboexterno"] . "'");
        }

        if (isset($datat["motivoevidenciafotografica"]) && $datat["motivoevidenciafotografica"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'motivoevidenciafotografica'", "'" . addslashes($datat["motivoevidenciafotografica"]) . "'");
        }

        if (isset($datat["siglabase"]) && $datat["siglabase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'siglabase'", "'" . addslashes($datat["siglabase"]) . "'");
        }

        if (isset($datat["nombrebasebase64"]) && $datat["nombrebasebase64"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'nombrebasebase64'", "'" . addslashes($datat["nombrebasebase64"]) . "'");
        }

        if (isset($datat["siglabasebase64"]) && $datat["siglabasebase64"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'siglabasebase64'", "'" . addslashes($datat["siglabasebase64"]) . "'");
        }

        if (isset($datat["tramiteradicar"]) && $datat["tramiteradicar"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tramiteradicar'", "'" . addslashes($datat["tramiteradicar"]) . "'");
        }

        if (isset($datat["domiciliobase"]) && $datat["domiciliobase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'domiciliobase'", "'" . addslashes($datat["domiciliobase"]) . "'");
        }

        if (isset($datat["tiposasbase"]) && $datat["tiposasbase"] != '' && $datat["tiposasbase"] != 'no') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tiposasbase'", "'" . addslashes($datat["tiposasbase"]) . "'");
        }

        if (isset($datat["activosbase"]) && $datat["activosbase"] != '' && $datat["activosbase"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'activosbase'", "'" . addslashes($datat["activosbase"]) . "'");
        }

        if (isset($datat["capitalbase"]) && $datat["capitalbase"] != '' && $datat["capitalbase"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'capitalbase'", "'" . addslashes($datat["capitalbase"]) . "'");
        }

        if (isset($datat["ingresosbase"]) && $datat["ingresosbase"] != '' && $datat["ingresosbase"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ingresosbase'", "'" . addslashes($datat["ingresosbase"]) . "'");
        }

        if (isset($datat["ciiubase"]) && $datat["ciiubase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'ciiubase'", "'" . addslashes($datat["ciiubase"]) . "'");
        }

        if (isset($datat["pagoirbase"]) && $datat["pagoirbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'pagoirbase'", "'" . addslashes($datat["pagoirbase"]) . "'");
        }

        if (isset($datat["boletairbase"]) && $datat["boletairbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'boletairbase'", "'" . addslashes($datat["boletairbase"]) . "'");
        }

        if (isset($datat["fechaboletairbase"]) && $datat["fechaboletairbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fechaboletairbase'", "'" . addslashes($datat["fechaboletairbase"]) . "'");
        }

        if (isset($datat["gobernacionirbase"]) && $datat["gobernacionirbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'gobernacionirbase'", "'" . addslashes($datat["gobernacionirbase"]) . "'");
        }

        if (isset($datat["beneficio1780base"]) && $datat["beneficio1780base"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'beneficio1780base'", "'" . addslashes($datat["beneficio1780base"]) . "'");
        }

        if (isset($datat["certificadosbase"]) && $datat["certificadosbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'certificadosbase'", "'" . addslashes($datat["certificadosbase"]) . "'");
        }

        if (isset($datat["libroactasbase"]) && $datat["libroactasbase"]) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroactasbase'", "'" . addslashes($datat["libroactasbase"]) . "'");
        }

        if (isset($datat["libroactascodigolfbase"]) && $datat["libroactascodigolfbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroactascodigolfbase'", "'" . addslashes($datat["libroactascodigolfbase"]) . "'");
        }

        if (isset($datat["libroactasnombrelfbase"]) && $datat["libroactasnombrelfbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroactasnombrelfbase'", "'" . addslashes($datat["libroactasnombrelfbase"]) . "'");
        }

        if (isset($datat["libroactashojaslfbase"]) && ltrim((string) $datat["libroactashojaslfbase"], "0") != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroactashojaslfbase'", "'" . addslashes($datat["libroactashojaslfbase"]) . "'");
        }

        if (isset($datat["libroactascodigolebase"]) && $datat["libroactascodigolebase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroactascodigolebase'", "'" . addslashes($datat["libroactascodigolebase"]) . "'");
        }

        if (isset($datat["libroactasnombrelebase"]) && $datat["libroactasnombrelebase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroactasnombrelebase'", "'" . addslashes($datat["libroactasnombrelebase"]) . "'");
        }

        //
        if (isset($datat["libroaccionistasbase"]) && $datat["libroaccionistasbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroaccionistasbase'", "'" . addslashes($datat["libroaccionistasbase"]) . "'");
        }

        if (isset($datat["libroaccionistascodigolfbase"]) && $datat["libroaccionistascodigolfbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroaccionistascodigolfbase'", "'" . addslashes($datat["libroaccionistascodigolfbase"]) . "'");
        }

        if (isset($datat["libroaccionistasnombrelfbase"]) && $datat["libroaccionistasnombrelfbase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroaccionistasnombrelfbase'", "'" . addslashes($datat["libroaccionistasnombrelfbase"]) . "'");
        }

        if (isset($datat["libroaccionistashojaslfbase"]) && ltrim((string) $datat["libroaccionistashojaslfbase"], "0") != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroaccionistashojaslfbase'", "'" . addslashes($datat["libroaccionistashojaslfbase"]) . "'");
        }

        if (isset($datat["libroaccionistascodigolebase"]) && $datat["libroaccionistascodigolebase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroaccionistascodigolebase'", "'" . addslashes($datat["libroaccionistascodigolebase"]) . "'");
        }

        if (isset($datat["libroaccionistasnombrelebase"]) && $datat["libroaccionistasnombrelebase"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'libroaccionistasnombrelebase'", "'" . addslashes($datat["libroaccionistasnombrelebase"]) . "'");
        }

        if (isset($datat["liquidarir"]) && $datat["liquidarir"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'liquidarir'", "'" . $datat["liquidarir"] . "'");
        }

        if (isset($datat["fecharecibogenerar"]) && $datat["fecharecibogenerar"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fecharecibogenerar'", "'" . $datat["fecharecibogenerar"] . "'");
        }

        if (isset($datat["fecharenaplicable"]) && $datat["fecharenaplicable"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fecharenaplicable'", "'" . $datat["fecharenaplicable"] . "'");
        }

        if (isset($datat["fecharenovacion"]) && $datat["fecharenovacion"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'fecharenovacion'", "'" . $datat["fecharenovacion"] . "'");
        }

        if (isset($datat["totalcamara"]) && $datat["totalcamara"] != '' && $datat["totalcamara"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'totalcamara'", "'" . $datat["totalcamara"] . "'");
        }

        if (isset($datat["totalcamara1"]) && $datat["totalcamara1"] != '' && $datat["totalcamara1"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'totalcamara1'", "'" . $datat["totalcamara1"] . "'");
        }

        if (isset($datat["totalgobernacion"]) && $datat["totalgobernacion"] != '' && $datat["totalgobernacion"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'totalgobernacion'", "'" . $datat["totalgobernacion"] . "'");
        }

        if (isset($datat["totalgobernacion1"]) && $datat["totalgobernacion1"] != '' && $datat["totalgobernacion1"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'totalgobernacion1totalgobernacion1'", "'" . $datat["totalgobernacion1"] . "'");
        }

        if (isset($datat["tiporegistro"]) && $datat["tiporegistro"] != '' && $datat["tiporegistro"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tiporegistro'", "'" . $datat["tiporegistro"] . "'");
        }

        if (isset($datat["tipotramiterue"]) && $datat["tipotramiterue"] != '' && $datat["tipotramiterue"] != 0) {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'tipotramiterue'", "'" . $datat["tipotramiterue"] . "'");
        }
        
        if (isset($datat["solicitaractivopropietario"]) && $datat["solicitaractivopropietario"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'solicitaractivopropietario'", "'" . $datat["solicitaractivopropietario"] . "'");
        }
        
         if (isset($datat["incluirafiliacion"]) && $datat["incluirafiliacion"] != '') {
            $ix++;
            $arrValores[$ix] = array($datat["numeroliquidacion"], "'incluirafiliacion'", "'" . $datat["incluirafiliacion"] . "'");
        }

        //
        $res = insertarRegistrosBloqueMysqliApi($dbx, 'mreg_liquidacion_campos', $arrCampos, $arrValores);
        if ($res === false) {
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }

        $result = borrarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle', 'idliquidacion=' . $datat["numeroliquidacion"]);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidaciondetalle : ' . $_SESSION["generales"]["mensajeerror"];
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }

        //
        $existeServicioOrigen = 'no';
        $columnName = 'servicioorigen';
        $result = ejecutarQueryMysqliApi($dbx, "SHOW COLUMNS FROM mreg_liquidaciondetalle WHERE Field = '$columnName'");
        if ($result && !empty($result)) {
            $existeServicioOrigen = 'si';
        }

        //
        $exp1 = '';
        $nom1 = '';
        $i = 0;
        foreach ($datat["liquidacion"] as $liq) {
            if (!isset($liq["idsec"])) {
                $liq["idsec"] = '';
            }
            if (!isset($liq["idservicio"])) {
                $liq["idservicio"] = '';
            }
            if (!isset($liq["expediente"])) {
                $liq["expediente"] = '';
            }
            if (!isset($liq["nombre"])) {
                $liq["nombre"] = '';
            }
            if (!isset($liq["ano"])) {
                $liq["ano"] = '';
            }
            if (!isset($liq["cantidad"])) {
                $liq["cantidad"] = 0;
            }
            if (!isset($liq["valorbase"])) {
                $liq["valorbase"] = 0;
            }
            if (!isset($liq["porcentaje"])) {
                $liq["porcentaje"] = 0;
            }
            if (!isset($liq["valorservicio"])) {
                $liq["valorservicio"] = 0;
            }
            if (!isset($liq["benart7"])) {
                $liq["benart7"] = '';
            }
            if (!isset($liq["benley1780"])) {
                $liq["benley1780"] = '';
            }

            if (!isset($liq["reliquidacion"])) {
                $liq["reliquidacion"] = '';
            }
            if (!isset($liq["serviciobase"])) {
                $liq["serviciobase"] = '';
            }
            if (!isset($liq["pagoafiliacion"])) {
                $liq["pagoafiliacion"] = '';
            }
            if (!isset($liq["ir"])) {
                $liq["ir"] = '';
            }
            if (!isset($liq["iva"])) {
                $liq["iva"] = '';
            }
            if (!isset($liq["idalerta"])) {
                $liq["idalerta"] = '';
            }
            if (!isset($liq["expedienteafiliado"])) {
                $liq["expedienteafiliado"] = '';
            }
            if (!isset($liq["porcentajeiva"])) {
                $liq["porcentajeiva"] = 0;
            }
            if (!isset($liq["valoriva"])) {
                $liq["valoriva"] = 0;
            }
            if (!isset($liq["servicioiva"])) {
                $liq["servicioiva"] = '';
            }
            if (!isset($liq["porcentajedescuento"])) {
                $liq["porcentajedescuento"] = 0;
            }
            if (!isset($liq["valordescuento"])) {
                $liq["valordescuento"] = 0;
            }
            if (!isset($liq["serviciodescuento"])) {
                $liq["serviciodescuento"] = '';
            }
            if (!isset($liq["clavecontrol"])) {
                $liq["clavecontrol"] = '';
            }
            if (!isset($liq["servicioorigen"])) {
                $liq["servicioorigen"] = '';
            }
            if (!isset($liq["diasmora"])) {
                $liq["diasmora"] = 0;
            }

            if (trim($liq["idservicio"]) != '') {
                $i++;
                if ($i == 1) {
                    $exp1 = $liq["expediente"];
                    $nom1 = $liq["nombre"];
                }

//
                $arrCampos = array(
                    'idliquidacion',
                    'secuencia',
                    'idsec',
                    'idservicio',
                    'cc',
                    'expediente',
                    'nombre',
                    'ano',
                    'cantidad',
                    'valorbase',
                    'porcentaje',
                    'valorservicio',
                    'benart7',
                    'benley1780',
                    'reliquidacion',
                    'serviciobase',
                    'pagoafiliacion',
                    'ir',
                    'iva',
                    'idalerta',
                    'expedienteafiliado',
                    'porcentajeiva',
                    'valoriva',
                    'servicioiva',
                    'porcentajedescuento',
                    'valordescuento',
                    'serviciodescuento',
                    'clavecontrol'
                );

                if ($existeServicioOrigen == 'si') {
                    $arrCampos[] = 'servicioorigen';
                    $arrCampos[] = 'diasmora';
                }

                if (!isset($liq["cc"])) {
                    $liq["cc"] = '';
                }

                $arrValues = array(
                    $datat["numeroliquidacion"],
                    $i,
                    "'" . sprintf("%03s", $liq["idsec"]) . "'",
                    "'" . $liq["idservicio"] . "'",
                    "'" . $liq["cc"] . "'",
                    "'" . $liq["expediente"] . "'",
                    "'" . addslashes($liq["nombre"]) . "'",
                    "'" . $liq["ano"] . "'",
                    intval($liq["cantidad"]),
                    doubleval($liq["valorbase"]),
                    doubleval($liq["porcentaje"]),
                    doubleval($liq["valorservicio"]),
                    "'" . $liq["benart7"] . "'",
                    "'" . $liq["benley1780"] . "'",
                    "'" . $liq["reliquidacion"] . "'",
                    "'" . $liq["serviciobase"] . "'",
                    "'" . $liq["pagoafiliacion"] . "'",
                    "'" . $liq["ir"] . "'",
                    "'" . $liq["iva"] . "'",
                    intval($liq["idalerta"]),
                    "'" . $liq["expedienteafiliado"] . "'",
                    doubleval($liq["porcentajeiva"]),
                    doubleval($liq["valoriva"]),
                    "'" . $liq["servicioiva"] . "'",
                    doubleval($liq["porcentajedescuento"]),
                    doubleval($liq["valordescuento"]),
                    "'" . $liq["serviciodescuento"] . "'",
                    "'" . $liq["clavecontrol"] . "'"
                );
                if ($existeServicioOrigen == 'si') {
                    $arrValues[] = "'" . $liq["servicioorigen"] . "'";
                    $arrValues[] = $liq["diasmora"];
                }

                //
                $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle', $arrCampos, $arrValues);
                if ($result === false) {
                    $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_liquidaciondetalle (' . $_SESSION ["generales"] ["mensajeerror"] . ')';
                    if ($cerrarMysqli == 'si') {
                        $dbx->close();
                    }
                    return false;
                }
            }
        }

        // Graba liquidacion RUES
        $result = borrarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle_rues', 'idliquidacion=' . $datat["numeroliquidacion"]);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidaciondetalle_rues';
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }
        $exp1 = '';
        $nom1 = '';
        $i = 0;

//
        if (!isset($datat["rues_servicios"])) {
            $datat["rues_servicios"] = array();
        }

//
        foreach ($datat["rues_servicios"] as $liq) {
            if (!isset($liq["codigo_servicio"])) {
                $liq["codigo_servicio"] = '';
            }
            if (!isset($liq["descripcion_servicio"])) {
                $liq["descripcion_servicio"] = '';
            }
            if (!isset($liq["orden_servicio"])) {
                $liq["orden_servicio"] = 0;
            }
            if (!isset($liq["orden_servicio_asociado"])) {
                $liq["orden_servicio_asociado"] = 0;
            }
            if (!isset($liq["nombre_base"])) {
                $liq["nombre_base"] = '';
            }
            if (!isset($liq["valor_base"])) {
                $liq["valor_base"] = 0;
            }
            if (!isset($liq["valor_liquidacion"])) {
                $liq["valor_liquidacion"] = 0;
            }
            if (!isset($liq["cantidad_servicio"])) {
                $liq["cantidad_servicio"] = 0;
            }
            if (!isset($liq["indicador_base"])) {
                $liq["indicador_base"] = '';
            }
            if (!isset($liq["indicador_renovacion"])) {
                $liq["indicador_renovacion"] = '';
            }
            if (!isset($liq["matricula_servicio"])) {
                $liq["matricula_servicio"] = '';
            }
            if (!isset($liq["nombre_matriculado"])) {
                $liq["nombre_matriculado"] = '';
            }
            if (!isset($liq["ano_renovacion"])) {
                $liq["ano_renovacion"] = '';
            }
            if (!isset($liq["valor_activos_sin_ajustes"])) {
                $liq["valor_activos_sin_ajustes"] = 0;
            }

            $i++;

            $arrCampos = array(
                'idliquidacion',
                'secuencia',
                'codigo_servicio',
                'descripcion_servicio',
                'orden_servicio',
                'orden_servicio_asociado',
                'nombre_base',
                'valor_base',
                'valor_liquidacion',
                'cantidad_servicio',
                'indicador_base',
                'indicador_renovacion',
                'matricula_servicio',
                'nombre_matriculado',
                'ano_renovacion',
                'valor_activos_sin_ajustes'
            );

            $arrValues = array(
                $datat["numeroliquidacion"],
                $i,
                "'" . $liq["codigo_servicio"] . "'",
                "'" . \funcionesGenerales::utf8_decode($liq["descripcion_servicio"]) . "'",
                "'" . $liq["orden_servicio"] . "'",
                "'" . $liq["orden_servicio_asociado"] . "'",
                "'" . addslashes($liq["nombre_base"]) . "'",
                "'" . $liq["valor_base"] . "'",
                "'" . $liq["valor_liquidacion"] . "'",
                "'" . $liq["cantidad_servicio"] . "'",
                "'" . $liq["indicador_base"] . "'",
                "'" . $liq["indicador_renovacion"] . "'",
                "'" . $liq["matricula_servicio"] . "'",
                "'" . addslashes($liq["nombre_matriculado"]) . "'",
                "'" . $liq["ano_renovacion"] . "'",
                "'" . $liq["valor_activos_sin_ajustes"] . "'"
            );

            $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidaciondetalle_rues', $arrCampos, $arrValues);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_liquidaciondetalle_rues (' . $_SESSION ["generales"] ["mensajeerror"] . ')';
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return false;
            }
        }

//
        if (!isset($datat["rues_textos"])) {
            $datat["rues_textos"] = array();
        }

//
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_textos_rues', "idliquidacion=" . $datat["numeroliquidacion"]);

//
        $ix = 0;
        foreach ($datat["rues_textos"] as $tx) {
            $ix++;

//
            $arrCampos = array(
                'idliquidacion',
                'secuencia',
                'texto',
            );

//
            $arrValues = array(
                $datat["numeroliquidacion"],
                $ix,
                "'" . addslashes(\funcionesGenerales::utf8_decode($tx)) . "'"
            );

//
            $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidacion_textos_rues', $arrCampos, $arrValues);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros en mreg_liquidaciontextos_rues (' . $_SESSION ["generales"] ["mensajeerror"] . ')';
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return false;
            }
        }


        // Graba expedientes
        $result = borrarRegistrosMysqliApi($dbx, 'mreg_liquidacionexpedientes', 'idliquidacion=' . $datat["numeroliquidacion"]);
        if ($result === false) {
            $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidacionexpedientes';
            if ($cerrarMysqli == 'si') {
                $dbx->close();
            }
            return false;
        }

        $existeProtegerActivos = '';
        $result = ejecutarQueryMysqliApi($dbx, "SHOW COLUMNS FROM mreg_liquidacionexpedientes WHERE Field = 'protegeractivos'");
        if ($result && !empty($result)) {
            $existeProtegerActivos = 'si';
        }
        
        if (!empty($datat["expedientes"])) {
            $i = 0;
            foreach ($datat["expedientes"] as $mat) {
                if (!isset($mat["cc"])) {
                    $mat["cc"] = '';
                }
                if (!isset($mat["matricula"])) {
                    $mat["matricula"] = '';
                }
                if (!isset($mat["proponente"])) {
                    $mat["proponente"] = '';
                }
                if (!isset($mat["numrue"])) {
                    $mat["numrue"] = '';
                }
                if (!isset($mat["idtipoidentificacion"])) {
                    $mat["idtipoidentificacion"] = '';
                }
                if (!isset($mat["identificacion"])) {
                    $mat["identificacion"] = '';
                }
                if (!isset($mat["razonsocial"])) {
                    $mat["razonsocial"] = '';
                }
                if (!isset($mat["ape1"])) {
                    $mat["ape1"] = '';
                }
                if (!isset($mat["ape2"])) {
                    $mat["ape2"] = '';
                }
                if (!isset($mat["nom1"])) {
                    $mat["nom1"] = '';
                }
                if (!isset($mat["nom2"])) {
                    $mat["nom2"] = '';
                }
                if (!isset($mat["organizacion"])) {
                    $mat["organizacion"] = '';
                }
                if (!isset($mat["categoria"])) {
                    $mat["caetgoria"] = '';
                }
                if (!isset($mat["afiliado"])) {
                    $mat["afiliado"] = '';
                }
                if (!isset($mat["propietariojurisdiccion"])) {
                    $mat["propietariojurisdiccion"] = '';
                }
                if (!isset($mat["primeranorenovado"])) {
                    $mat["primeranorenovado"] = '';
                }
                if (!isset($mat["ultimoanoafiliado"])) {
                    $mat["ultimoanoafiliado"] = '';
                }
                if (!isset($mat["ultimoanorenovado"])) {
                    $mat["ultimoanorenovado"] = '';
                }
                if (!isset($mat["ultimosactivos"])) {
                    $mat["ultimosactivos"] = 0;
                }
                if (!isset($mat["nuevosactivos"])) {
                    $mat["nuevosactivos"] = 0;
                }
                if (!isset($mat["actividad"])) {
                    $mat["actividad"] = '';
                }
                if (!isset($mat["registrobase"])) {
                    $mat["registrobase"] = '';
                }
                if (!isset($mat["benart7"])) {
                    $mat["benart7"] = '';
                }
                if (!isset($mat["benley1780"])) {
                    $mat["benley1780"] = '';
                }

                if (!isset($mat["renovaresteano"])) {
                    $mat["renovaresteano"] = '';
                }
                if (!isset($mat["fechanacimiento"])) {
                    $mat["fechanacimiento"] = '';
                }
                if (!isset($mat["fechamatricula"])) {
                    $mat["fechamatricula"] = '';
                }
                if (!isset($mat["fecmatant"])) {
                    $mat["fecmatant"] = '';
                }
                if (!isset($mat["reliquidacion"])) {
                    $mat["reliquidacion"] = '';
                }
                if (!isset($mat["dircom"])) {
                    $mat["dircom"] = '';
                }
                if (!isset($mat["muncom"])) {
                    $mat["muncom"] = '';
                }
                if (!isset($mat["protegeractivos"])) {
                    $mat["protegeractivos"] = '';
                }
                
                if ($mat["ultimosactivos"] == '') {
                    $mat["ultimosactivos"] = 0;
                }
                if ($mat["nuevosactivos"] == '') {
                    $mat["nuevosactivos"] = 0;
                }

//                
                $i++;
                $arrCampos = array(
                    'idliquidacion',
                    'secuencia',
                    'cc',
                    'matricula',
                    'proponente',
                    'numrue',
                    'idtipoidentificacion',
                    'identificacion',
                    'razonsocial',
                    'ape1',
                    'ape2',
                    'nom1',
                    'nom2',
                    'organizacion',
                    'categoria',
                    'afiliado',
                    'propietariojurisdiccion',
                    'ultimoanoafiliado',
                    'ultimoanorenovado',
                    'primeranorenovado',
                    'ultimosactivos',
                    'nuevosactivos',
                    'actividad',
                    'registrobase',
                    'benart7',
                    'benley1780',
                    'renovaresteano',
                    'fechanacimiento',
                    'fechamatricula',
                    'fecmatant',
                    'reliquidacion',
                    'controlpot',
                    'dircom',
                    'muncom'
                );
                
                if ($existeProtegerActivos == 'si') {
                    $arrCampos[] = 'protegeractivos';
                }
                
                if (!isset($mat["primeranorenovado"])) {
                    $mat["primeranorenovado"] = '';
                }
                if (!isset($mat["controlpot"])) {
                    $mat["controlpot"] = '';
                }
                
                
                $arrValues = array(
                    $datat["numeroliquidacion"],
                    sprintf("%03s", $i),
                    "'" . $mat["cc"] . "'",
                    "'" . $mat["matricula"] . "'",
                    "'" . $mat["proponente"] . "'",
                    "'" . $mat["numrue"] . "'",
                    "'" . $mat["idtipoidentificacion"] . "'",
                    "'" . $mat["identificacion"] . "'",
                    "'" . addslashes($mat["razonsocial"]) . "'",
                    "'" . addslashes($mat["ape1"]) . "'",
                    "'" . addslashes($mat["ape2"]) . "'",
                    "'" . addslashes($mat["nom1"]) . "'",
                    "'" . addslashes($mat["nom2"]) . "'",
                    "'" . $mat["organizacion"] . "'",
                    "'" . $mat["categoria"] . "'",
                    "'" . $mat["afiliado"] . "'",
                    "'" . $mat["propietariojurisdiccion"] . "'",
                    "'" . $mat["ultimoanoafiliado"] . "'",
                    "'" . $mat["ultimoanorenovado"] . "'",
                    "'" . $mat["primeranorenovado"] . "'",
                    $mat["ultimosactivos"],
                    $mat["nuevosactivos"],
                    "'" . $mat["actividad"] . "'",
                    "'" . $mat["registrobase"] . "'",
                    "'" . $mat["benart7"] . "'",
                    "'" . $mat["benley1780"] . "'",
                    "'" . $mat["renovaresteano"] . "'",
                    "'" . $mat["fechanacimiento"] . "'",
                    "'" . $mat["fechamatricula"] . "'",
                    "'" . $mat["fecmatant"] . "'",
                    "'" . $mat["reliquidacion"] . "'",
                    "'" . $mat["controlpot"] . "'",
                    "'" . addslashes($mat["dircom"]) . "'",
                    "'" . $mat["muncom"] . "'"
                );
                
                if ($existeProtegerActivos == 'si') {
                    $arrValues[] = "'" . $mat["protegeractivos"] . "'";
                }
                
                $result = insertarRegistrosMysqliApi($dbx, 'mreg_liquidacionexpedientes', $arrCampos, $arrValues);
                if ($result === false) {
                    $_SESSION["generales"]["mensajeerror"] = 'Error insertando registros de mreg_liquidacionexpedientes : ' . $_SESSION["generales"]["mensajeerror"];
                    if ($cerrarMysqli == 'si') {
                        $dbx->close();
                    }
                    return false;
                }
            }
        }

        // Graba transacciones generales
        if (existeTablaMysqliApi($dbx, 'mreg_liquidacion_transacciones_generales')) {
            $result = borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones_generales', 'idliquidacion=' . $datat["numeroliquidacion"]);
            if ($result === false) {
                $_SESSION["generales"]["mensajeerror"] = 'Error borrando registros de mreg_liquidacion_transacciones_generales';
                if ($cerrarMysqli == 'si') {
                    $dbx->close();
                }
                return false;
            }
            if (isset($datat["transaccionesgenerales"]) && !empty($datat["transaccionesgenerales"])) {
                foreach ($datat["transaccionesgenerales"] as $tragen) {
                    $arrCampos = array(
                        'idliquidacion',
                        'idsecuencia',
                        'campo',
                        'contenido'
                    );
                    foreach ($tragen as $key => $valor) {
                        if ($key != 'idliquidacion' && $key != 'secuencia') {
                            if ($valor != '') {
                                $arrValores = array(
                                    $datat["numeroliquidacion"],
                                    intval($tragen["secuencia"]),
                                    "'" . $key . "'",
                                    "'" . addslashes((string) $valor) . "'"
                                );
                                insertarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones_generales', $arrCampos, $arrValores);
                            }
                        }
                    }
                }
            }
        }

        // Graba transacciones
        borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones', 'idliquidacion=' . $datat["numeroliquidacion"]);
        if (existeTablaMysqliApi($dbx, 'mreg_liquidacion_transacciones_campos')) {
            borrarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones_campos', "idliquidacion=" . $datat["numeroliquidacion"]);
        }

        //
        $exp1 = '';
        $nom1 = '';
        $i = 0;

//
        $retornar = true;
        $mensajeRetornar = '';
        $existeRazonSocialBase64 = '';
        $result = ejecutarQueryMysqliApi($dbx, "SHOW COLUMNS FROM mreg_liquidacion_transacciones WHERE Field = 'razonsocialbase64'");
        if ($result && !empty($result)) {
            $existeRazonSocialBase64 = 'si';
        }

        if (!empty($datat["transacciones"])) {
            foreach ($datat["transacciones"] as $tra) {
                if (!isset($tra["ultimoanorenovadoanterior"])) {
                    $tra["ultimoanorenovadoanterior"] = '';
                }

//
                if (!isset($tra["benart7"])) {
                    $tra["benart7"] = '';
                }
                if (!isset($tra["benley1780"])) {
                    $tra["benley1780"] = '';
                }
                if (!isset($tra["fechanacimientopnat"])) {
                    $tra["fechanacimientopnat"] = '';
                }

//
                if (!isset($tra["patrimonio"])) {
                    $tra["patrimonio"] = 0;
                }
                if (!isset($tra["capitalsocial"])) {
                    $tra["capitalsocial"] = 0;
                }
                if (!isset($tra["capitalautorizado"])) {
                    $tra["capitalautorizado"] = 0;
                }
                if (!isset($tra["capitalsuscrito"])) {
                    $tra["capitalsuscrito"] = 0;
                }
                if (!isset($tra["capitalpagado"])) {
                    $tra["capitalpagado"] = 0;
                }
                if (!isset($tra["aportedinero"])) {
                    $tra["aportedinero"] = 0;
                }
                if (!isset($tra["aporteactivos"])) {
                    $tra["aporteactivos"] = 0;
                }
                if (!isset($tra["aportelaboral"])) {
                    $tra["aportelaboral"] = 0;
                }
                if (!isset($tra["aportelaboraladicional"])) {
                    $tra["aportelaboraladicional"] = 0;
                }
                if (!isset($tra["capitalasignado"])) {
                    $tra["capitalasignado"] = 0;
                }
                if (!isset($tra["pornaltot"])) {
                    $tra["pornaltot"] = 0;
                }
                if (!isset($tra["pornalpub"])) {
                    $tra["pornalpub"] = 0;
                }
                if (!isset($tra["pornalpri"])) {
                    $tra["pornalpri"] = 0;
                }
                if (!isset($tra["porexttot"])) {
                    $tra["porexttot"] = 0;
                }
                if (!isset($tra["porextpub"])) {
                    $tra["porextpub"] = 0;
                }
                if (!isset($tra["porextpri"])) {
                    $tra["porextpri"] = 0;
                }

                if (!isset($tra["tipoidentificacionvendedor"])) {
                    $tra["tipoidentificacionvendedor"] = '';
                }
                if (!isset($tra["identificacionvendedor"])) {
                    $tra["identificacionvendedor"] = '';
                }
                if (!isset($tra["nombrevendedor"])) {
                    $tra["nombrevendedor"] = '';
                }
                if (!isset($tra["camaravendedor"])) {
                    $tra["camaravendedor"] = '';
                }
                if (!isset($tra["matriculavendedor"])) {
                    $tra["matriculavendedor"] = '';
                }
                if (!isset($tra["nombre1vendedor"])) {
                    $tra["nombre1vendedor"] = '';
                }
                if (!isset($tra["nombre2vendedor"])) {
                    $tra["nombre2vendedor"] = '';
                }
                if (!isset($tra["apellido1vendedor"])) {
                    $tra["apellido1vendedor"] = '';
                }
                if (!isset($tra["apellido2vendedor"])) {
                    $tra["apellido2vendedor"] = '';
                }
                if (!isset($tra["emailvendedor"])) {
                    $tra["emailvendedor"] = '';
                }
                if (!isset($tra["celularvendedor"])) {
                    $tra["celularvendedor"] = '';
                }
                if (!isset($tra["cancelarvendedor"])) {
                    $tra["cancelarvendedor"] = '';
                }

                if (!isset($tra["tipoidentificacioncomprador"])) {
                    $tra["tipoidentificacioncomprador"] = '';
                }
                if (!isset($tra["identificacioncomprador"])) {
                    $tra["identificacioncomprador"] = '';
                }
                if (!isset($tra["nombrecomprador"])) {
                    $tra["nombrecomprador"] = '';
                }
                if (!isset($tra["camaracomprador"])) {
                    $tra["camaracomprador"] = '';
                }
                if (!isset($tra["matriculacomprador"])) {
                    $tra["matriculacomprador"] = '';
                }
                if (!isset($tra["organizacioncomprador"])) {
                    $tra["organizacioncomprador"] = '';
                }
                if (!isset($tra["municipiocomprador"])) {
                    $tra["municipiocomprador"] = '';
                }
                if (!isset($tra["nombre1comprador"])) {
                    $tra["nombre1comprador"] = '';
                }
                if (!isset($tra["nombre2comprador"])) {
                    $tra["nombre2comprador"] = '';
                }
                if (!isset($tra["apellido1comprador"])) {
                    $tra["apellido1comprador"] = '';
                }
                if (!isset($tra["apellido2comprador"])) {
                    $tra["apellido2comprador"] = '';
                }
                if (!isset($tra["emailcomprador"])) {
                    $tra["emailcomprador"] = '';
                }
                if (!isset($tra["celularcomprador"])) {
                    $tra["celularcomprador"] = '';
                }
                if (!isset($tra["activoscomprador"])) {
                    $tra["activoscomprador"] = 0;
                }
                if (!isset($tra["personalcomprador"])) {
                    $tra["personalcomprador"] = 0;
                }

                if (!isset($tra["municipioanterior"])) {
                    $tra["municipioanterior"] = '';
                }
                if (!isset($tra["matriculaanterior"])) {
                    $tra["matriculaanterior"] = '';
                }
                if (!isset($tra["camaraanterior"])) {
                    $tra["camaraanterior"] = '';
                }
                if (!isset($tra["fecharenovacionanterior"])) {
                    $tra["fecharenovacionanterior"] = '';
                }
                if (!isset($tra["benart7anterior"])) {
                    $tra["benart7anterior"] = '';
                }
                if (!isset($tra["municipiodestino"])) {
                    $tra["municipiodestino"] = '';
                }
                if (!isset($tra["camaradestino"])) {
                    $tra["camaradestino"] = '';
                }
                if (!isset($tra["prerut"])) {
                    $tra["prerut"] = '';
                }
                if (!isset($tra["acreditapagoir"])) {
                    $tra["acreditapagoir"] = '';
                }
                if (!isset($tra["nroreciboacreditapagoir"])) {
                    $tra["nroreciboacreditapagoir"] = '';
                }
                if (!isset($tra["fechareciboacreditapagoir"])) {
                    $tra["fechareciboacreditapagoir"] = '';
                }
                if (!isset($tra["gobernacionreciboacreditapagoir"])) {
                    $tra["gobernacionreciboacreditapagoir"] = '';
                }
                if (!isset($tra["tipodisolucion"])) {
                    $tra["tipodisolucion"] = '';
                }
                if (!isset($tra["tipoliquidacion"])) {
                    $tra["tipoliquidacion"] = '';
                }
                if (!isset($tra["motivoliquidacion"])) {
                    $tra["motivoliquidacion"] = '';
                }
                if (!isset($tra["motivocancelacion"])) {
                    $tra["motivocancelacion"] = '';
                }
                if (!isset($tra["ciiu1"])) {
                    $tra["ciiu1"] = '';
                }
                if (!isset($tra["ciiu2"])) {
                    $tra["ciiu2"] = '';
                }
                if (!isset($tra["ciiu3"])) {
                    $tra["ciiu3"] = '';
                }
                if (!isset($tra["ciiu4"])) {
                    $tra["ciiu4"] = '';
                }
                if (!isset($tra["entidadvigilancia"])) {
                    $tra["entidadvigilancia"] = '';
                }
                if (!isset($tra["objetosocial"])) {
                    $tra["objetosocial"] = '';
                }
                if (!isset($tra["facultades"])) {
                    $tra["facultades"] = '';
                }
                if (!isset($tra["limitaciones"])) {
                    $tra["limitaciones"] = '';
                }
                if (!isset($tra["entidadvigilancia"])) {
                    $tra["entidadvigilancia"] = '';
                }
                if (!isset($tra["poderespecial"])) {
                    $tra["poderespecial"] = '';
                }

                if (!isset($tra["clase_libro"])) {
                    $tra["clase_libro"] = '';
                }
                if (!isset($tra["tipo_libro"])) {
                    $tra["tipo_libro"] = '';
                }
                if (!isset($tra["codigo_libro"])) {
                    $tra["codigo_libro"] = '';
                }
                if (!isset($tra["nombre_libro"]) || is_array($tra["nombre_libro"])) {
                    $tra["nombre_libro"] = '';
                }
                if (!isset($tra["email_libro"])) {
                    $tra["email_libro"] = '';
                }
                if (!isset($tra["emailconfirmacion_libro"])) {
                    $tra["emailconfirmacion_libro"] = '';
                }
                if (!isset($tra["paginainicial_libro"]) || ltrim($tra["paginainicial_libro"], "0") == '') {
                    $tra["paginainicial_libro"] = 0;
                }
                if (!isset($tra["paginafinal_libro"]) || ltrim($tra["paginafinal_libro"], "0") == '') {
                    $tra["paginafinal_libro"] = 0;
                }
                if (!isset($tra["incluirrotulado_libro"])) {
                    $tra["incluirrotulado_libro"] = '';
                }
                if (!isset($tra["incluir_costo_hojas"])) {
                    $tra["incluir_costo_hojas"] = '';
                }
                if (!isset($tra["incluir_costo_envio"])) {
                    $tra["incluir_costo_envio"] = '';
                }

                if (!isset($tra["actanro_libro"])) {
                    $tra["actanro_libro"] = '';
                }
                if (!isset($tra["fechaacta_libro"])) {
                    $tra["fechaacta_libro"] = '';
                }
                if (!isset($tra["horaacta_libro"])) {
                    $tra["horaacta_libro"] = '';
                }
                if (!isset($tra["fechaini_libroelectronico"])) {
                    $tra["fechaini_libroelectronico"] = '';
                }
                if (!isset($tra["fechafin_libroelectronico"])) {
                    $tra["fechafin_libroelectronico"] = '';
                }
                if (!isset($tra["fechainiinscripciones_libroelectronico"])) {
                    $tra["fechainiinscripciones_libroelectronico"] = '';
                }
                if (!isset($tra["fechafininscripciones_libroelectronico"])) {
                    $tra["fechafininscripciones_libroelectronico"] = '';
                }

//
                if (!isset($tra["libeleanot_libro"])) {
                    $tra["libeleanot_libro"] = '';
                }
                if (!isset($tra["libeleanot_registro"])) {
                    $tra["libeleanot_registro"] = '';
                }
                if (!isset($tra["libeleanot_dupli"])) {
                    $tra["libeleanot_dupli"] = '';
                }

                if (!isset($tra["libeleanot_nroacta"])) {
                    $tra["libeleanot_nroacta"] = '';
                }
                if (!isset($tra["libeleanot_fechaacta"])) {
                    $tra["libeleanot_fechaacta"] = '';
                }
                if (!isset($tra["libeleanot_nropaginas"])) {
                    $tra["libeleanot_nropaginas"] = 0;
                }
                if (!isset($tra["libeleanot_fechainiinscripciones"])) {
                    $tra["libeleanot_fechainiinscripciones"] = '';
                }
                if (!isset($tra["libeleanot_fechafininscripciones"])) {
                    $tra["libeleanot_fechafininscripciones"] = '';
                }
                if (!isset($tra["libeleanot_nroregistros"])) {
                    $tra["libeleanot_nroregistros"] = 0;
                }


//
                if (!isset($tra["embargo"])) {
                    $tra["embargo"] = '';
                }
                if (!isset($tra["tipoideembargante"])) {
                    $tra["tipoideembargante"] = '';
                }
                if (!isset($tra["ideembargante"])) {
                    $tra["ideembargante"] = '';
                }
                if (!isset($tra["nom1embargante"])) {
                    $tra["nom1embargante"] = '';
                }
                if (!isset($tra["nom2embargante"])) {
                    $tra["nom2embargante"] = '';
                }
                if (!isset($tra["ape1embargante"])) {
                    $tra["ape1embargante"] = '';
                }
                if (!isset($tra["ape2embargante"])) {
                    $tra["ape2embargante"] = '';
                }

//
                if (!isset($tra["desembargo"])) {
                    $tra["desembargo"] = '';
                }
                if (!isset($tra["librocruce"])) {
                    $tra["librocruce"] = '';
                }
                if (!isset($tra["inscripcioncruce"])) {
                    $tra["inscripcioncruce"] = '';
                }

                if (!isset($tra["cantidad"])) {
                    $tra["cantidad"] = 1;
                }

                if (!isset($tra["cantidadadicional"])) {
                    $tra["cantidadadicional"] = 0;
                }

                if (!isset($tra["tipocontrolante"])) {
                    $tra["tipocontrolante"] = '';
                }

                if (!isset($tra["tipocontrolantemotivo"])) {
                    $tra["tipocontrolantemotivo"] = '';
                }

                if (!isset($tra["tipoidentificacioncontrolante"])) {
                    $tra["tipoidentificacioncontrolante"] = '';
                }

                if (!isset($tra["identificacioncontrolante"])) {
                    $tra["identificacioncontrolante"] = '';
                }

                if (!isset($tra["nombrecontrolante"])) {
                    $tra["nombrecontrolante"] = '';
                }

                if (!isset($tra["tipoidentificacionsocio"])) {
                    $tra["tipoidentificacionsocio"] = '';
                }

                if (!isset($tra["identificacionsocio"])) {
                    $tra["identificacionsocio"] = '';
                }

                if (!isset($tra["nombresocio"])) {
                    $tra["nopmbresocio"] = '';
                }

                if (!isset($tra["direccionnotificacionsocio"])) {
                    $tra["direccionnotificacionsocio"] = '';
                }

                if (!isset($tra["domiciliosocio"])) {
                    $tra["domiciliosocio"] = '';
                }

                if (!isset($tra["nacionalidadsocio"])) {
                    $tra["nacionalidadsocio"] = '';
                }

                if (!isset($tra["actividadsocio"])) {
                    $tra["actividadsocio"] = '';
                }

                if (!isset($tra["cantidadcermat"])) {
                    $tra["cantidadcermat"] = 0;
                }

                if (!isset($tra["cantidadcerexi"])) {
                    $tra["cantidadcerexi"] = 0;
                }

                if (!isset($tra["cantidadcerlib"])) {
                    $tra["cantidadcerlib"] = 0;
                }

                if (!isset($tra["codgenesadl"])) {
                    $tra["codgenesadl"] = '';
                }

                if (!isset($tra["codespeesadl"])) {
                    $tra["codespeesadl"] = '';
                }

                if (!isset($tra["condiespecialley2219"])) {
                    $tra["condiespecialley2219"] = '';
                }

                if (!isset($tra["anadirrazonsocial"])) {
                    $tra["anadirrazonsocial"] = '';
                }

                //
                $i++;
                $arrCampos = array(
                    'idliquidacion',
                    'idsecuencia',
                    'idtransaccion',
                    'matriculaafectada',
                    'tipodoc',
                    'numdoc',
                    'fechadoc',
                    'origendoc',
                    'mundoc',
                    'camaravendedor',
                    'matriculavendedor',
                    'tipoidentificacionvendedor',
                    'identificacionvendedor',
                    'nombrevendedor',
                    'nombre1vendedor',
                    'nombre2vendedor',
                    'apellido1vendedor',
                    'apellido2vendedor',
                    'emailvendedor',
                    'celularvendedor',
                    'cancelarvendedor',
                    'camaracomprador',
                    'matriculacomprador',
                    'organizacioncomprador',
                    'municipiocomprador',
                    'tipoidentificacioncomprador',
                    'identificacioncomprador',
                    'nombrecomprador',
                    'nombre1comprador',
                    'nombre2comprador',
                    'apellido1comprador',
                    'apellido2comprador',
                    'emailcomprador',
                    'celularcomprador',
                    'activoscomprador',
                    'personalcomprador',
                    'municipioanterior',
                    'matriculaanterior',
                    'camaraanterior',
                    'fechamatriculaanterior',
                    'fecharenovacionanterior',
                    'ultimoanorenovadoanterior',
                    'benart7anterior',
                    'municipiodestino',
                    'camaradestino',
                    'organizacion',
                    'categoria',
                    'razonsocial',
                    'sigla',
                    'tipoidentificacion',
                    'identificacion',
                    'nit',
                    'prerut',
                    'ape1',
                    'ape2',
                    'nom1',
                    'nom2',
                    'cargo',
                    'idvinculo',
                    'idrenglon',
                    'aceptacion',
                    'identificacionrepresentada',
                    'razonsocialrepresentada',
                    'pornaltot',
                    'pornalpub',
                    'pornalpri',
                    'porexttot',
                    'porextpub',
                    'porextpri',
                    'personal',
                    'activos',
                    'ingresos',
                    'ciiu',
                    'costotransaccion',
                    'patrimonio',
                    'benart7',
                    'benley1780',
                    'fechanacimientopnat',
                    'capitalsocial',
                    'capitalautorizado',
                    'capitalsuscrito',
                    'capitalpagado',
                    'aporteactivos',
                    'aportedinero',
                    'aportelaboral',
                    'aportelaboraladicional',
                    'capitalasignado',
                    'acreditapagoir',
                    'nroreciboacreditapagoir',
                    'fechareciboacreditapagoir',
                    'gobernacionacreditapagoir',
                    'dircom',
                    'municipio',
                    'fechaduracion',
                    'tipodisolucion',
                    'motivodisolucion',
                    'tipoliquidacion',
                    'motivoliquidacion',
                    'motivocancelacion',
                    // 'anadirrazonsocial',
                    'codgenesadl',
                    'codespeesadl',
                    'condiespecialley2219',
                    'ciiu1',
                    'ciiu2',
                    'ciiu3',
                    'ciiu4',
                    'clase_libro',
                    'tipo_libro',
                    'codigo_libro',
                    'nombre_libro',
                    'email_libro',
                    'emailconfirmacion_libro',
                    'paginainicial_libro',
                    'paginafinal_libro',
                    'incluirrotulado_libro',
                    'incluir_costo_hojas',
                    'incluir_costo_envio',
                    'libeleanot_libro',
                    'libeleanot_registro',
                    'libeleanot_dupli',
                    'libeleanot_nroacta',
                    'libeleanot_fechaacta',
                    'libeleanot_nropaginas',
                    'libeleanot_fechainiinscripciones',
                    'libeleanot_fechafininscripciones',
                    'libeleanot_nroregistros',
                    'actanro_libro',
                    'fechaacta_libro',
                    'horaacta_libro',
                    'fechaini_libroelectronico',
                    'fechafin_libroelectronico',
                    'fechainiinscripciones_libroelectronico',
                    'fechafininscripciones_libroelectronico',
                    'embargo',
                    'tipoideembargante',
                    'ideembargante',
                    'nom1embargante',
                    'nom2embargante',
                    'ape1embargante',
                    'ape2embargante',
                    'desembargo',
                    'librocruce',
                    'inscripcioncruce',
                    'entidadvigilancia',
                    'objetosocial',
                    'facultades',
                    'limitaciones',
                    'poderespecial',
                    'texto',
                    'cantidad',
                    'tipocontrolante',
                    'tipocontrolantemotivo',
                    'tipoidentificacioncontrolante',
                    'identificacioncontrolante',
                    'nombrecontrolante',
                    'tipoidentificacionsocio',
                    'identificacionsocio',
                    'nombresocio',
                    'direccionnotificacionsocio',
                    'domiciliosocio',
                    'nacionalidadsocio',
                    'actividadsocio',
                    'cantidadcermat',
                    'cantidadcerexi',
                    'cantidadcerlib'
                );

                //
                if (!isset($tra["sigla"])) {
                    $tra["sigla"] = '';
                }
                if (!isset($tra["fechamatriculaanterior"])) {
                    $tra["fechamatriculaanterior"] = '';
                }
                if (!isset($tra["tipoidentificacion"])) {
                    $tra["tipoidentificacion"] = '';
                }
                if (!isset($tra["ape1"])) {
                    $tra["ape1"] = '';
                }
                if (!isset($tra["ape2"])) {
                    $tra["ape2"] = '';
                }
                if (!isset($tra["nom1"])) {
                    $tra["nom1"] = '';
                }
                if (!isset($tra["nom2"])) {
                    $tra["nom2"] = '';
                }
                if (!isset($tra["cargo"])) {
                    $tra["cargo"] = '';
                }
                if (!isset($tra["idvinculo"])) {
                    $tra["idvinculo"] = '';
                }
                if (!isset($tra["idrenglon"])) {
                    $tra["idrenglon"] = '';
                }
                if (!isset($tra["aceptacion"])) {
                    $tra["aceptacion"] = '';
                }
                if (!isset($tra["identificacionrepresentada"])) {
                    $tra["identificacionrepresentada"] = '';
                }
                if (!isset($tra["razonsocialrepresentada"])) {
                    $tra["razonsocialrepresentada"] = '';
                }
                if (!isset($tra["ciiu"])) {
                    $tra["ciiu"] = '';
                }
                if (!isset($tra["dircom"])) {
                    $tra["dircom"] = '';
                }
                if (!isset($tra["fechaduracion"])) {
                    $tra["fechaduracion"] = '';
                }
                if (!isset($tra["motivodisolucion"])) {
                    $tra["motivodisolucion"] = '';
                }
                if (!isset($tra["nombresocio"])) {
                    $tra["nombresocio"] = '';
                }

                if (!isset($tra["activos"]) || doubleval($tra["activos"]) == 0) {
                    $tra["activos"] = 0;
                }
                if (!isset($tra["ingresos"]) || doubleval($tra["ingresos"]) == 0) {
                    $tra["ingresos"] = 0;
                }
                if (!isset($tra["personal"]) || doubleval($tra["personal"]) == 0) {
                    $tra["personal"] = 0;
                }
                if (!isset($tra["costotransaccion"]) || doubleval($tra["costotransaccion"]) == 0) {
                    $tra["costotransaccion"] = 0;
                }
                if (!isset($tra["patrimonio"]) || doubleval($tra["patrimonio"]) == 0) {
                    $tra["patrimonio"] = 0;
                }
                if (!isset($tra["capitalsocial"]) || doubleval($tra["capitalsocial"]) == 0) {
                    $tra["capitalsocial"] = 0;
                }
                if (!isset($tra["capitalautorizado"]) || doubleval($tra["capitalautorizado"]) == 0) {
                    $tra["capitalautorizado"] = 0;
                }
                if (!isset($tra["capitalsuscrito"]) || doubleval($tra["capitalsuscrito"]) == 0) {
                    $tra["capitalsuscrito"] = 0;
                }
                if (!isset($tra["capitalpagado"]) || doubleval($tra["capitalpagado"]) == 0) {
                    $tra["capitalpagado"] = 0;
                }
                if (!isset($tra["aportedinero"]) || doubleval($tra["aportedinero"]) == 0) {
                    $tra["aportedinero"] = 0;
                }
                if (!isset($tra["aporteactivos"]) || doubleval($tra["aporteactivos"]) == 0) {
                    $tra["aporteactivos"] = 0;
                }
                if (!isset($tra["aportelaboral"]) || doubleval($tra["aportelaboral"]) == 0) {
                    $tra["aportelaboral"] = 0;
                }
                if (!isset($tra["aportelaboraladicional"]) || doubleval($tra["aportelaboraladicional"]) == 0) {
                    $tra["aportelaboraladicional"] = 0;
                }
                if (!isset($tra["capitalasignado"]) || doubleval($tra["capitalasignado"]) == 0) {
                    $tra["capitalasignado"] = 0;
                }
                if (!isset($tra["pornaltot"]) || doubleval($tra["pornaltot"]) == 0) {
                    $tra["pornaltot"] = 0;
                }
                if (!isset($tra["pornalpub"]) || doubleval($tra["pornalpub"]) == 0) {
                    $tra["pornalpub"] = 0;
                }
                if (!isset($tra["pornalpri"]) || doubleval($tra["pornalpri"]) == 0) {
                    $tra["pornalpri"] = 0;
                }
                if (!isset($tra["porexttot"]) || doubleval($tra["porexttot"]) == 0) {
                    $tra["porexttot"] = 0;
                }
                if (!isset($tra["porextpub"]) || doubleval($tra["porextpub"]) == 0) {
                    $tra["porextpub"] = 0;
                }
                if (!isset($tra["porextpri"]) || doubleval($tra["porextpri"]) == 0) {
                    $tra["porextpri"] = 0;
                }
                if (!isset($tra["nit"])) {
                    $tra["nit"] = '';
                }
                if (!isset($tra["nombre_libro"])) {
                    $tra["nombre_libro"] = '';
                }

                if (!isset($tra["idsecuencia"]) || $tra["idsecuencia"] == '' || $tra["idsecuencia"] == 0) {
                    $tra["idsecuencia"] = 0;
                }

                if ($tra['razonsocial'] != '') {
                    $tra['razonsocialbase64'] = base64_encode($tra['razonsocial']);
                }
                if ($tra['sigla'] != '') {
                    $tra['siglabase64'] = base64_encode($tra['sigla']);
                }
                
                $arrValores = array(
                    $datat["numeroliquidacion"],
                    $tra["idsecuencia"],
                    "'" . $tra['idtransaccion'] . "'",
                    "'" . $tra['matriculaafectada'] . "'",
                    "'" . $tra['tipodoc'] . "'",
                    "'" . $tra['numdoc'] . "'",
                    "'" . $tra['fechadoc'] . "'",
                    "'" . $tra['origendoc'] . "'",
                    "'" . $tra['mundoc'] . "'",
                    "'" . $tra['camaravendedor'] . "'",
                    "'" . $tra['matriculavendedor'] . "'",
                    "'" . $tra['tipoidentificacionvendedor'] . "'",
                    "'" . $tra['identificacionvendedor'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nombrevendedor'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nombre1vendedor'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nombre2vendedor'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['apellido1vendedor'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['apellido2vendedor'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['emailvendedor'])) . "'",
                    "'" . addslashes($tra['celularvendedor']) . "'",
                    "'" . addslashes($tra['cancelarvendedor']) . "'",
                    "'" . $tra['camaracomprador'] . "'",
                    "'" . $tra['matriculacomprador'] . "'",
                    "'" . $tra['organizacioncomprador'] . "'",
                    "'" . $tra['municipiocomprador'] . "'",
                    "'" . $tra['tipoidentificacioncomprador'] . "'",
                    "'" . $tra['identificacioncomprador'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nombrecomprador'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nombre1comprador'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nombre2comprador'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['apellido1comprador'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['apellido2comprador'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['emailcomprador'])) . "'",
                    "'" . addslashes($tra['celularcomprador']) . "'",
                    $tra['activoscomprador'],
                    $tra['personalcomprador'],
                    "'" . $tra['municipioanterior'] . "'",
                    "'" . $tra['matriculaanterior'] . "'",
                    "'" . $tra['camaraanterior'] . "'",
                    "'" . $tra['fechamatriculaanterior'] . "'",
                    "'" . $tra['fecharenovacionanterior'] . "'",
                    "'" . $tra['ultimoanorenovadoanterior'] . "'",
                    "'" . $tra['benart7anterior'] . "'",
                    "'" . $tra['municipiodestino'] . "'",
                    "'" . $tra['camaradestino'] . "'",
                    "'" . $tra['organizacion'] . "'",
                    "'" . $tra['categoria'] . "'",
                    "'" . addslashes(($tra['razonsocial'])) . "'",
                    "'" . addslashes(($tra['sigla'])) . "'",
                    "'" . $tra['tipoidentificacion'] . "'",
                    "'" . $tra['identificacion'] . "'",
                    "'" . $tra['nit'] . "'",
                    "'" . $tra['prerut'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['ape1'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['ape2'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nom1'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nom2'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['cargo'])) . "'",
                    "'" . $tra['idvinculo'] . "'",
                    "'" . $tra['idrenglon'] . "'",
                    "'" . $tra['aceptacion'] . "'",
                    "'" . $tra['identificacionrepresentada'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['razonsocialrepresentada'])) . "'",
                    $tra['pornaltot'],
                    $tra['pornalpub'],
                    $tra['pornalpri'],
                    $tra['porexttot'],
                    $tra['porextpub'],
                    $tra['porextpri'],
                    $tra['personal'],
                    $tra['activos'],
                    $tra["ingresos"],
                    "'" . $tra["ciiu"] . "'",
                    $tra['costotransaccion'],
                    $tra['patrimonio'],
                    "'" . addslashes((string) $tra['benart7']) . "'",
                    "'" . addslashes((string) $tra['benley1780']) . "'",
                    "'" . addslashes((string) $tra['fechanacimientopnat']) . "'",
                    $tra['capitalsocial'],
                    $tra['capitalautorizado'],
                    $tra['capitalsuscrito'],
                    $tra['capitalpagado'],
                    $tra['aporteactivos'],
                    $tra['aportedinero'],
                    $tra['aportelaboral'],
                    $tra['aportelaboraladicional'],
                    $tra['capitalasignado'],
                    "'" . $tra['acreditapagoir'] . "'",
                    "'" . $tra['nroreciboacreditapagoir'] . "'",
                    "'" . $tra['fechareciboacreditapagoir'] . "'",
                    "'" . $tra['gobernacionacreditapagoir'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode((string) $tra['dircom'])) . "'",
                    "'" . $tra['municipio'] . "'",
                    "'" . $tra['fechaduracion'] . "'",
                    "'" . $tra['tipodisolucion'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode((string) $tra['motivodisolucion'])) . "'",
                    "'" . $tra['tipoliquidacion'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode((string) $tra['motivoliquidacion'])) . "'",
                    "'" . $tra['motivocancelacion'] . "'",
                    // "'" . $tra['anadirrazonsocial'] . "'",
                    "'" . $tra['codgenesadl'] . "'",
                    "'" . $tra['codespeesadl'] . "'",
                    "'" . $tra['condiespecialley2219'] . "'",
                    "'" . $tra['ciiu1'] . "'",
                    "'" . $tra['ciiu2'] . "'",
                    "'" . $tra['ciiu3'] . "'",
                    "'" . $tra['ciiu4'] . "'",
                    "'" . $tra['clase_libro'] . "'",
                    "'" . $tra['tipo_libro'] . "'",
                    "'" . $tra['codigo_libro'] . "'",
                    "'" . addslashes((string) $tra['nombre_libro']) . "'",
                    "'" . addslashes((string) $tra['email_libro']) . "'",
                    "'" . addslashes((string) $tra['emailconfirmacion_libro']) . "'",
                    $tra['paginainicial_libro'],
                    $tra['paginafinal_libro'],
                    "'" . $tra['incluirrotulado_libro'] . "'",
                    "'" . $tra['incluir_costo_hojas'] . "'",
                    "'" . $tra['incluir_costo_envio'] . "'",
                    "'" . $tra['libeleanot_libro'] . "'",
                    "'" . $tra['libeleanot_registro'] . "'",
                    "'" . $tra['libeleanot_dupli'] . "'",
                    "'" . $tra['libeleanot_nroacta'] . "'",
                    "'" . $tra['libeleanot_fechaacta'] . "'",
                    intval($tra['libeleanot_nropaginas']),
                    "'" . $tra['libeleanot_fechainiinscripciones'] . "'",
                    "'" . $tra['libeleanot_fechafininscripciones'] . "'",
                    intval($tra['libeleanot_nroregistros']),
                    "'" . $tra['actanro_libro'] . "'",
                    "'" . $tra['fechaacta_libro'] . "'",
                    "'" . $tra['horaacta_libro'] . "'",
                    "'" . $tra['fechaini_libroelectronico'] . "'",
                    "'" . $tra['fechafin_libroelectronico'] . "'",
                    "'" . $tra['fechainiinscripciones_libroelectronico'] . "'",
                    "'" . $tra['fechafininscripciones_libroelectronico'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['embargo'])) . "'",
                    "'" . $tra['tipoideembargante'] . "'",
                    "'" . $tra['ideembargante'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nom1embargante'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['nom2embargante'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['ape1embargante'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['ape2embargante'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['desembargo'])) . "'",
                    "'" . $tra['librocruce'] . "'",
                    "'" . $tra['inscripcioncruce'] . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['entidadvigilancia'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['objetosocial'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['facultades'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['limitaciones'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['poderespecial'])) . "'",
                    "'" . addslashes(\funcionesGenerales::utf8_decode($tra['texto'])) . "'",
                    intval($tra["cantidad"]),
                    "'" . $tra['tipocontrolante'] . "'",
                    "'" . addslashes($tra['tipocontrolantemotivo']) . "'",
                    "'" . $tra['tipoidentificacioncontrolante'] . "'",
                    "'" . $tra['identificacioncontrolante'] . "'",
                    "'" . addslashes($tra['nombrecontrolante']) . "'",
                    "'" . $tra['tipoidentificacionsocio'] . "'",
                    "'" . $tra['identificacionsocio'] . "'",
                    "'" . addslashes($tra['nombresocio']) . "'",
                    "'" . addslashes($tra['direccionnotificacionsocio']) . "'",
                    "'" . addslashes($tra['domiciliosocio']) . "'",
                    "'" . addslashes($tra['nacionalidadsocio']) . "'",
                    "'" . addslashes($tra['actividadsocio']) . "'",
                    intval($tra["cantidadcermat"]),
                    intval($tra["cantidadcerexi"]),
                    intval($tra["cantidadcerlib"]),
                );

                $res = insertarRegistrosMysqliApi($dbx, 'mreg_liquidacion_transacciones', $arrCampos, $arrValores);
                if ($res === false) {
                    $retornar = false;
                    $mensajeRetornar = $_SESSION["generales"]["mensajeerror"];
                }

                if (existeTablaMysqliApi($dbx, 'mreg_liquidacion_transacciones_campos')) {
                    if (isset($tra['razonsocialbase64']) && $tra['razonsocialbase64'] != '') {
                        $arrCampos = array (
                            'idliquidacion',
                            'idsecuencia',
                            'campo',
                            'contenido'
                        );
                        $arrValores = array (
                            $datat["numeroliquidacion"],
                            $tra["idsecuencia"],
                            "'razonsocialbase64'",
                            "'" . $tra['razonsocialbase64'] . "'"
                        );
                        insertarRegistrosMysqliApi($dbx,'mreg_liquidacion_transacciones_campos', $arrCampos, $arrValores);
                    }
                    if (isset($tra['siglabase64']) && $tra['siglabase64'] != '') {
                        $arrCampos = array (
                            'idliquidacion',
                            'idsecuencia',
                            'campo',
                            'contenido'
                        );
                        $arrValores = array (
                            $datat["numeroliquidacion"],
                            $tra["idsecuencia"],
                            "'siglabase64'",
                            "'" . $tra['siglabase64'] . "'"
                        );
                        insertarRegistrosMysqliApi($dbx,'mreg_liquidacion_transacciones_campos', $arrCampos, $arrValores);
                    }
                }
            }
        }

        $_SESSION["generales"]["mensajeerror"] = $mensajeRetornar;
        if ($cerrarMysqli == 'si') {
            $dbx->close();
        }

        \logApi::general2('grabarLiquidacionMreg_' . date("Ymd"), $datat["numeroliquidacion"], 'Grabo liquidacion');
        return $retornar;
    }

}

?>
