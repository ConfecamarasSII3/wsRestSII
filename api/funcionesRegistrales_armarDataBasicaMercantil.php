<?php

class funcionesRegistrales_armarDataBasicaMercantil {

    public static function armarDataBasicaMercantil() {
//
        $restringiracceso = 'no';
        if ($_SESSION["generales"]["tipousuario"] == '00') {
            if (!isset($_SESSION["generales"]["tipousuariocontrol"])) {
                $restringiracceso = 'si';
            } else {
                if ($_SESSION["generales"]["tipousuariocontrol"] == 'usuarioanonimo') {
                    $restringiracceso = 'si';
                } else {
                    if ($_SESSION["generales"]["tipousuariocontrol"] == 'usuarioregistrado') {
                        if (defined('RESTRICCION_ACCESO_USUARIOS_REGISTRADOS') && RESTRICCION_ACCESO_USUARIOS_REGISTRADOS == 'SI') {
                            $restringiracceso = 'si';
                        }
                    }
                }
            }
        }

//
        $arreglo = array();
        $arreglo[] = array('Nombre o razón social', $_SESSION["expediente"]["nombre"]);
        if (trim($_SESSION["expediente"]["nom1"]) != '') {
            $arreglo[] = array('Primer nombre', $_SESSION["expediente"]["nom1"]);
        }
        if (trim($_SESSION["expediente"]["nom2"]) != '') {
            $arreglo[] = array('Segundo nombre', $_SESSION["expediente"]["nom2"]);
        }
        if (trim($_SESSION["expediente"]["ape1"]) != '') {
            $arreglo[] = array('Primer apellido', $_SESSION["expediente"]["ape1"]);
        }
        if (trim($_SESSION["expediente"]["ape2"]) != '') {
            $arreglo[] = array('Segundo apellido', $_SESSION["expediente"]["ape2"]);
        }
        if (trim($_SESSION["expediente"]["sigla"]) != '') {
            $arreglo[] = array('Sigla', $_SESSION["expediente"]["sigla"]);
        }
        if ($restringiracceso == 'no') {
            if (trim($_SESSION["expediente"]["identificacion"]) != '') {
                $arreglo[] = array('Identificación', $_SESSION["expediente"]["tipoidentificacion"] . ' - ' . $_SESSION["expediente"]["identificacion"]);
            }
            if (trim($_SESSION["expediente"]["fecexpdoc"]) != '') {
                $arreglo[] = array('Fecha de expedición', $_SESSION["expediente"]["fecexpdoc"]);
            }
            if (trim($_SESSION["expediente"]["idmunidoc"]) != '') {
                $arreglo[] = array('Municipio de expedición', $_SESSION["expediente"]["idmunidoc"]);
            }
            if (trim($_SESSION["expediente"]["paisexpdoc"]) != '') {
                $arreglo[] = array('País de expedición', $_SESSION["expediente"]["paisexpdoc"]);
            }
            if (trim($_SESSION["expediente"]["nacionalidad"]) != '') {
                $arreglo[] = array('Nacionalidad', $_SESSION["expediente"]["nacionalidad"]);
            }
            if (trim($_SESSION["expediente"]["prerut"]) != '') {
                $arreglo[] = array('Pre-Rut', $_SESSION["expediente"]["prerut"]);
            }
            if (trim($_SESSION["expediente"]["nit"]) != '') {
                $arreglo[] = array('Nit', $_SESSION["expediente"]["nit"]);
            }
            if (trim($_SESSION["expediente"]["admondian"]) != '') {
                $arreglo[] = array('Administración', $_SESSION["expediente"]["admondian"]);
            }
            if (trim($_SESSION["expediente"]["idetripaiori"]) != '') {
                $arreglo[] = array('Ident. Trib en el exterior', $_SESSION["expediente"]["idetripaiori"]);
            }
            if (trim($_SESSION["expediente"]["paiori"]) != '') {
                $arreglo[] = array('Pais Ident. Trib.', $_SESSION["expediente"]["paiori"]);
            }
            if (trim($_SESSION["expediente"]["idetriextep"]) != '') {
                $arreglo[] = array('Ident. Trib Per. Jurídica', $_SESSION["expediente"]["idetriextep"]);
            }
            $t1 = '';
            switch ($_SESSION["expediente"]["afiliado"]) {
                case "1" : $t1 = 'Afiliado activo';
                    break;
                case "2" : $t1 = 'Ex afiliado';
                    break;
                case "3" : $t1 = 'Aceptado/Pendiente de pago';
                    break;
                case "5" : $t1 = 'Desafiliado temporalmente';
                    break;
                case "9" : $t1 = 'Potencial afiliado';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('Afiliación?', $t1);
            }
            $arreglo[] = array('Estado de la matrícula', $_SESSION["expediente"]["estadomatricula"] . ' - ' . retornarRegistroMysqliApi(null, 'mreg_estadomatriculas', "id='" . $_SESSION["expediente"]["estadomatricula"] . "'", "descripcion"));
            if (trim($_SESSION["expediente"]["organizacion"]) != '') {
                $arreglo[] = array('Organización jurídica', retornarRegistroMysqliApi(null, 'bas_organizacionjuridica', "id='" . $_SESSION["expediente"]["organizacion"] . "'", "descripcion"));
            }
            if (ltrim(trim($_SESSION["expediente"]["categoria"]), "0") != '') {
                $arreglo[] = array('Categoría', retornarRegistroMysqliApi(null, 'bas_categorias', "id='" . $_SESSION["expediente"]["categoria"] . "'", "descripcion"));
            }
            if ($_SESSION["expediente"]["organizacion"] == '01' || ($_SESSION["expediente"]["organizacion"] > '02' && $_SESSION["expediente"]["organizacion"] != '12' && $_SESSION["expediente"]["organizacion"] != '14' && $_SESSION["expediente"]["categoria"] == '1')) {
                if (trim($_SESSION["expediente"]["naturaleza"]) != '') {
                    $arreglo[] = array('Naturaleza', retornarRegistroMysqliApi(null, 'bas_naturalezas', "id='" . $_SESSION["expediente"]["naturaleza"] . "'", "descripcion"));
                }
            }
            $arreglo[] = array('Estado de los datos', $_SESSION["expediente"]["estadodatosmatricula"]);
            $arreglo[] = array('Fecha matrícula', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fechamatricula"]));
            $arreglo[] = array('Fecha renovación', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fecharenovacion"]));
            $arreglo[] = array('Último año renovado', $_SESSION["expediente"]["ultanoren"]);
            if (trim($_SESSION["expediente"]["fechadisolucion"]) != '') {
                $arreglo[] = array('Fecha disolución', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fecharenovacion"]));
            }
            if (trim($_SESSION["expediente"]["fechaliquidacion"]) != '') {
                $arreglo[] = array('Fecha liquidación', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fechaliquidacion"]));
            }
            if (trim($_SESSION["expediente"]["estadotipoliquidacion"]) != '') {
                $arreglo[] = array('Tipo liquidación', retornarRegistroMysqliApi(null, 'mreg_tipos_liquidacion', "id='" . $_SESSION["expediente"]["estadotipoliquidacion"] . "'", "descripcion"));
            }
            if (trim($_SESSION["expediente"]["fechacancelacion"]) != '') {
                $arreglo[] = array('Fecha cancelación', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fechacancelacion"]));
            }
            if (trim($_SESSION["expediente"]["motivocancelacion"]) != '') {
                $arreglo[] = array('Motivo cancelación', $_SESSION["expediente"]["motivocancelacion"]);
            }
            if (trim($_SESSION["expediente"]["fecperj"]) != '') {
                $arreglo[] = array('Fecha personería', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fecperj"]));
            }
            if (trim($_SESSION["expediente"]["numperj"]) != '') {
                $arreglo[] = array('Número de personería', $_SESSION["expediente"]["numperj"]);
            }
            if (trim($_SESSION["expediente"]["idorigenperj"]) != '') {
                $arreglo[] = array('Otorgó personería', $_SESSION["expediente"]["idorigenperj"]);
            }
            if (trim($_SESSION["expediente"]["fechaconstitucion"]) != '') {
                $arreglo[] = array('Fecha constitución', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fechaconstitucion"]));
            }
            if (trim($_SESSION["expediente"]["origendocconst"]) != '') {
                $arreglo[] = array('Origen', $_SESSION["expediente"]["origendocconst"]);
            }
            if (trim($_SESSION["expediente"]["fechavencimiento"]) != '' && $_SESSION["expediente"]["fechavencimiento"] != '99999999') {
                $arreglo[] = array('Fecha vencimiento', \funcionesGenerales::mostrarFecha($_SESSION["expediente"]["fechavencimiento"]));
            }
            if (trim($_SESSION["expediente"]["fechavencimiento"]) != '' && $_SESSION["expediente"]["fechavencimiento"] == '99999999') {
                $arreglo[] = array('Fecha vencimiento', 'Indefinida');
            }

            $t1 = '';
            switch ($_SESSION["expediente"]["impexp"]) {
                case "1":$t1 = 'Importador';
                    break;
                case "2":$t1 = 'Exportador';
                    break;
                case "3":$t1 = 'Importador y Exportador';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('Importa o exporta?', $t1);
            }
            $t1 = '';
            switch ($_SESSION["expediente"]["codaduaneros"]) {
                case "1" : $t1 = 'Usuario aduanero';
                    break;
                case "S" : $t1 = 'Usuario aduanero';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('Control aduanero', $t1);
            }
            $t1 = '';
            switch ($_SESSION["expediente"]["tipolocal"]) {
                case "1" : $t1 = 'Local propio';
                    break;
                case "2" : $t1 = 'Local arrendado';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('El local es?', $t1);
            }
            $t1 = '';
            switch ($_SESSION["expediente"]["tipopropiedad"]) {
                case "1" : $t1 = 'Propietario único';
                    break;
                case "2" : $t1 = 'Sociedad de hecho';
                    break;
                case "3" : $t1 = 'Coopropiedad';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('Tipo de propiedad', $t1);
            }
            $t1 = '';
            switch ($_SESSION["expediente"]["empresafamiliar"]) {
                case "1" : $t1 = 'Es empresa familiar';
                    break;
                case "S" : $t1 = 'Es empresa familiar';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('Empresa familiar', $t1);
            }
            $t1 = '';
            switch ($_SESSION["expediente"]["procesosinnovacion"]) {
                case "1" : $t1 = 'Si tiene';
                    break;
                case "S" : $t1 = 'Si tiene';
                    break;
            }
            if ($t1 != '') {
                $arreglo[] = array('Procesos de innovación?', $t1);
            }
            if ($_SESSION["expediente"]["vigcontrol"] != '') {
                $arreglo[] = array('Entidad de vigilancia', $_SESSION["expediente"]["vigcontrol"]);
            }
        }
        return $arreglo;
    }
}

?>
