<?php

/**
 * WSI - 2017-08-25 
 * Se realiza actualización de capa funcional de homologación
 * a partir de la migración a este script de la totalidad de funciones empleadas 
 * en formularios, sincronizaciones u otros procesos.
 */

/**
 * Función que dado un código de servicio retorna un arreglo con todos sus campos
 * @param type $idserv
 * @return type
 */
function retornarHomologacionRues($idserv = '') {
    require_once ('../../librerias/funciones/persistencia.php');
    $retornar = array();
    $query = "select * from mreg_homologaciones_rue where cod_rue='" . $idserv . "'";
    $result = ejecutarQueryAsoc($query);
    if ($result === false) {
        $retornar = false;
    } else {
        $retornar = array();
        foreach ($result as $res) {
            $retornar = $res;
        }
    }
    unset($result);
    return $retornar;
}

/**
 * Función que obtiene el código RUES de organización específica empleado 
 * en formulario H1 y H2 según Circular 004 del 2017 (instrucciones)
 * @param type $org
 * @param type $clGen
 * @param type $clEsp
 * @param type $clEco
 * @return string
 */
function homologacionOrganizacionEspecificaRUES($org, $clGen, $clEsp, $clEco) {

    switch ($org) {
        case '01':
        case '02':
        case '03':
        case '04':
        case '05':
        case '06':
        case '07':
        case '08':
        case '09':
        case '16':
            $org_rues = $org;
            break;
        case '17':
            $org_rues = '11';
            break;
        case '10':
            $org_rues = '12';
            break;
        case '11':
            $org_rues = '10';
            break;
        case '15':
            $org_rues = '13';
            break;
        case '12':
            if (trim($clGen) != '') {
                switch ($clGen) {
                    case '1':
                        $org_rues = '32';
                        break;
                    case '3':
                        $org_rues = '31';
                        break;
                    case '0':
                        $org_rues = '34';
                        break;
                    default :
                        $org_rues = NULL;
                        break;
                }
            }
            if (!empty($org_rues)) {
                return $org_rues;
            }

            if (trim($clEsp) != '') {
                switch ($clEsp) {
                    case '20':
                        $org_rues = '21';
                        break;
                    case '25':
                        $org_rues = '26';
                        break;
                    case '26':
                        $org_rues = '27';
                        break;
                    case '29':
                        $org_rues = '29';
                        break;
                    case '41':
                        $org_rues = '30';
                        break;
                    case '60':
                        $org_rues = '34';
                        break;
                    case '62':
                        $org_rues = '34';
                        break;
                    default :
                        $org_rues = '33';
                        break;
                }
            } else {
                $org_rues = '33';
            }
            break;
        case '14':
            if (trim($clEco) != '') {
                switch ($clEco) {
                    case '03':
                        $org_rues = '25';
                        break;
                    case '05':
                        $org_rues = '23';
                        break;
                    case '07':
                        $org_rues = '24';
                        break;
                    default:
                        $org_rues = '22';
                        break;
                }
            } else {
                $org_rues = '22';
            }
            break;
        default :
            $org_rues = NULL;
            break;
    }

    if (!empty($org_rues)) {
        return $org_rues;
    } else {
        return '';
    }
}

/**
 * Función que obtiene el código RUES de organización general empleado 
 * en formulario H1 y H2 según Circular 004 del 2017 (instrucciones)
 * @param type $org
 * @param type $clEsp
 * @param type $arrCiiu
 * @return string
 */
function homologacionOrganizacionGeneralRUES($org, $clEsp, $arrCiiu) {

    switch ($org) {
        case '01':
        case '02':
        case '03':
        case '04':
        case '05':
        case '06':
        case '07':
        case '09':
        case '16':
            $soc_rues = '02';
            break;
        case '17':
            $soc_rues = '11';
            break;
        case '10':
            $soc_rues = '01';
            break;
        case '15':
            $soc_rues = '03';
            break;
        case '12':
            $soc_rues = '04';
            break;
        case '14':
            $soc_rues = '08';
            break;
        default :
            $soc_rues = NULL;
            break;
    }

    if (!empty($soc_rues)) {
        return $soc_rues;
    }


    switch ($clEsp) {
        case '61':
            $soc_rues = '05';
            break;
        case '60':
        case '62':
            $soc_rues = '07';
            break;
        default :
            $soc_rues = NULL;
            break;
    }


    if (!empty($soc_rues)) {
        return $soc_rues;
    }

    if (($org != '12') && ($org != '14')) {

        if (!empty($arrCiiu)) {
            if (!in_array("R9200", $arrCiiu)) {
                $soc_rues = '02';
                return $soc_rues;
            }
        } else {
            $soc_rues = NULL;
        }
    }

    if (!empty($soc_rues)) {
        return $soc_rues;
    }


    if (in_array("R9200", $arrCiiu)) {
        $soc_rues = '06';
        return $soc_rues;
    }
    return '';
}

function homologacionOrganizacionEsadlRUES($dbx,$clase = '') {
    $res = retornarRegistroMysqli2($dbx,'mreg_clase_esadl',"mostrar='S' and id='" . $clase . "'");
    if ($res && !empty ($res)) {
        return $res["codigorues"];
    } else {
        return '33';
    }
}

/**
 * Función que cambia el prefijo de Esadl y Solidarias al formato RUES de matrícula
 * @param type $valorMatricula
 * @return string
 */
function homologacionMatriculaRUES($valorMatricula) {

    $valor = trim($valorMatricula);

    $valorSinN = str_replace('N', '800', $valor);
    $valorSinS = str_replace('S', '900', $valorSinN);
    $valorMatriculaSalida = str_pad($valorSinS, 10, "0", STR_PAD_LEFT);
    if (is_numeric($valorMatriculaSalida)) {
        return $valorMatriculaSalida;
    } else {
        return '';
    }
}

/**
 * Función que obtiene el código RUES de organización implementado en Sincronizaciones hacia RUES
 * @param type $org
 * @param type $clGen
 * @param type $clEsp
 * @param type $clEco
 * @return type
 */
function homologacionOrganizacionRUES($org, $clGen, $clEsp, $clEco) {

    switch ($org) {
        case '01':
        case '02':
        case '03':
        case '04':
        case '05':
        case '06':
        case '07':
        case '08':
        case '09':
        case '16':
            $tipoOrgRues = $org;
            break;
        case '10':
            $tipoOrgRues = '12';
            break;
        case '11':
            $tipoOrgRues = '10';
            break;
        case '15':
            $tipoOrgRues = '13';
            break;
        case '17':
            $tipoOrgRues = '11';
            break;
        case '12':
            if (trim($clGen) != '') {
                switch ($clGen) {
                    case '1':
                        $tipoOrgRues = '32';
                        break;
                    case '3':
                        $tipoOrgRues = '31';
                        break;
                    case '0':
                        $tipoOrgRues = '34';
                        break;
                    default :
                        $tipoOrgRues = NULL;
                        break;
                }
            }
            if (!empty($tipoOrgRues)) {
                return $tipoOrgRues;
            }

            if (trim($clEsp) != '') {
                switch ($clEsp) {
                    case '20':
                        $tipoOrgRues = '21';
                        break;
                    case '25':
                        $tipoOrgRues = '26';
                        break;
                    case '26':
                        $tipoOrgRues = '27';
                        break;
                    case '29':
                        $tipoOrgRues = '29';
                        break;
                    case '41':
                        $tipoOrgRues = '30';
                        break;
                    case '60':
                        $tipoOrgRues = '34';
                        break;
                    case '62':
                        $tipoOrgRues = '34';
                        break;
                    default :
                        $tipoOrgRues = '33';
                        break;
                }
            } else {
                $tipoOrgRues = '33';
            }
            break;
        case '14':
            if (trim($clEco) != '') {
                switch ($clEco) {
                    case '03':
                        $tipoOrgRues = '25';
                        break;
                    case '05':
                        $tipoOrgRues = '23';
                        break;
                    case '07':
                        $tipoOrgRues = '24';
                        break;
                    default:
                        $tipoOrgRues = '22';
                        break;
                }
            } else {
                $tipoOrgRues = '22';
            }
            break;
        default :
            $tipoOrgRues = NULL;
            break;
    }

    if (!empty($tipoOrgRues)) {
        return $tipoOrgRues;
    } else {
        return $tipoOrgRues;
    }
}

/**
 * Función que obtiene el código RUES de Categoria implementado en Sincronizaciones hacia RUES
 * @param type $org
 * @param type $cat
 * @return string
 */
function homologacionCategoriaRUES($org, $cat) {
    if ($org == '01') {
        return '00';
    }
    if ($org == '02') {
        return '04';
    }
    if ($cat == '2') {
        return '02';
    }
    if ($cat == '3') {
        return '03';
    }
    if (($org > '02') && ($cat == '1')) {
        return '01';
    }
}

/**
 * Función que obtiene el código RUES de Sociedad implementado en Sincronizaciones hacia RUES
 * @param type $org
 * @param type $clEsp
 * @param type $arrCiiu
 * @return string
 */
function homologacionSociedadRUES($org, $clEsp, $arrCiiu) {

    switch ($org) {
        case '01':
        case '02':
        case '03':
        case '04':
        case '05':
        case '06':
        case '07':
        case '09':
        case '16':
            $tipoSociedadRues = '02';
            break;
        case '10':
            $tipoSociedadRues = '01';
            break;
        case '15':
            $tipoSociedadRues = '03';
            break;
        case '12':
            $tipoSociedadRues = '04';
            break;
        case '14':
            $tipoSociedadRues = '08';
            break;
        default :
            $tipoSociedadRues = NULL;
            break;
    }

    if (!empty($tipoSociedadRues)) {
        return $tipoSociedadRues;
    }


    switch ($clEsp) {
        case '61':
            $tipoSociedadRues = '05';
            break;
        case '60':
        case '62':
            $tipoSociedadRues = '07';
            break;
        default :
            $tipoSociedadRues = NULL;
            break;
    }


    if (!empty($tipoSociedadRues)) {
        return $tipoSociedadRues;
    }

    if (($org != '12') && ($org != '14')) {

        if (!empty($arrCiiu)) {
            if (!in_array("R9200", $arrCiiu)) {
                $tipoSociedadRues = '02';
                return $tipoSociedadRues;
            }
        } else {
            $tipoSociedadRues = NULL;
        }
    }

    if (!empty($tipoSociedadRues)) {
        return $tipoSociedadRues;
    }


    if (in_array("R9200", $arrCiiu)) {
        $tipoSociedadRues = '06';
        return $tipoSociedadRues;
    }



    return NULL;
}

/**
 * Función que obtiene el código RUES de Estado Matrícula implementado en Sincronizaciones hacia RUES
 * @param type $valorEstado
 * @return string
 */
function homologacionEstadoMatriculaRUES($valorEstado) {

    $valor = trim($valorEstado);

    $arrEM = array(
        'MA' => '01', //activa
        'IA' => '01', //activa
        'MI' => '01', //activa para rues
        //Se vuelve a asignar MG=03 solicitud jint 2017-07-05
        'MG' => '03', //cancelada
        'MC' => '03', //cancelada
        'IC' => '03', //cancelada
        'MF' => '09',
        'IF' => '09',
        'NA' => '05',
        'NM' => '06'
    );

    if (isset($arrEM[$valor])) {
        return $arrEM[$valor];
    } else {
        return '';
    }
}

/**
 * Función que obtiene el código RUES de Tipo Identificación implementado en Sincronizaciones hacia RUES
 * @param type $valorTipoIde
 * @return string
 */
function homologacionTipoIdentificacionRUES($valorTipoIde) {

    $valor = trim($valorTipoIde);

    if (!empty($valor)) {
        switch ($valor) {
            case '7':
                $valor = '06';
                break;
            case '0':
                $valor = '06';
                break;
            case 'E':
                $valor = '08';
                break;
            case 'R':
                $valor = '07';
                break;
            default:
                $valor = str_pad($valor, 2, "0", STR_PAD_LEFT);
                break;
        }
        return $valor;
    } else {
        return '06';
    }
}

/**
 * Función que obtiene el código RUES de Motivo Cancelación implementado en Sincronizaciones hacia RUES
 * @param type $valor
 * @return string
 */
function homologacionMotivoCancelacionRUES($valor) {

    //BAS_MOTIVOS_CANCELACION
    //MC - IC 
    //PENDIENTE PARA REVISAR HOMOLOGACION

    $valorCampo = ltrim(trim($valor), "0");

    $arrMC = array(
        '0' => '00', //NORMAL
        '1' => '00', //NORMAL
        '2' => '00', //NORMAL
        '3' => '00', //NORMAL
        '4' => '01', //CANCELADA POR CAMBIO DOMICILIO
        '5' => '00', //NORMAL 
        '50' => '06'//DEPURACION
    );

    if (isset($arrMC[$valorCampo])) {
        return $arrMC[$valorCampo];
    } else {
        return '00';
    }
}

/**
 * Función que obtiene el código RUES de Estado Liquidación implementado en Sincronizaciones hacia RUES
 * @param type $valor
 * @return string
 */
function homologacionEstadoLiquidacionRUES($valor) {

    //BAS_CODIGOS_LIQUIDACION
    //PENDIENTE PARA REVISAR HOMOLOGACION

    $valorCampo = trim($valor);

    $arrCL = array(
        '0' => '0', //NO LIQUIDADA
        '1' => '1', //LIQUIDADA
        '2' => '2', //EN LIQUIDACION
        '3' => '3', //INSCRIPCION ACTA DE REESTRUCTURACION
        '4' => '4', //INSCRIPCION PROCESO DE REESTRUCTURACION
        '5' => '5' //INSCRIPCION DE LA TERMINACION DEL PROCESO DE REESTRUCTURACION
    );

    if (isset($arrCL[$valorCampo])) {
        return $arrCL[$valorCampo];
    } else {
        return '';
    }
}

/**
 * Función que obtiene el código RUES de Código de Vinculos implementado en Sincronizaciones hacia RUES
 * @param type $org
 * @param type $valor
 * @return string
 */
function homologacionCodigoVinculoRUES($org, $valor) {

    $valorCampo = trim($valor);

    if ($org > '01') {

        $arrCV = array(
            '2170' => '01', //REPRESENTANTE LEGAL - PRINCIPAL        
            '2171' => '02', //REPRESENTANTE LEGAL - 1 SUPLENTE
            '2172' => '02', //REPRESENTANTE LEGAL - 2 SUPLENTE
            '2173' => '02', //REPRESENTANTE LEGAL - 3 SUPLENTE
            '1100' => '03', //SOCIO CAPITALISTA
            '1101' => '03', //SOCIO CAPITALISTA - SUPLENTE
            '1110' => '03', //SOCIO INDUSTRIAL
            '1120' => '03', //SOCIO GESTOR
            '1121' => '03', //PRIMER SUPLENTE DEL SOCIO GESTOR 
            '1122' => '03', //SEGUNDO SUPLENTE DEL SOCIO GESTOR 
            '1126' => '03', //SOCIO GESTOR ADMINISTRADOR 
            '1130' => '03', //SOCIO COMANDITARIO
            '1140' => '03', //SOCIO ACCIONISTA
            '1150' => '03', //SOCIO ADMINISTRADOR
            '1151' => '03', //PRIMER SUPLENTE DEL SOCIO ADMINISTRADOR
            '1152' => '03', //SEGUNDO SUPLENTE DEL SOCIO ADMINISTRADOR
            '1160' => '03', //SOCIO COLECTIVO
            '1170' => '03', //ASOCIADO
            '3110' => '03', //SOCIO EMPRESARIO (EU) 
            '2160' => '04', //REVISOR FISCAL - PRINCIPAL
            '2161' => '05', //REVISOR FISCAL - SUPLENTE
            '2100' => '06', //MIEMBRO DE LA JUNTA DIRECTIVA - PRINCIPAL
            '2101' => '07', //MIEMBRO DE LA JUNTA DIRECTIVA - 1 SUPLENTE
            '2101' => '07', //MIEMBRO DE LA JUNTA DIRECTIVA - 2 SUPLENTE
            '2101' => '07' //MIEMBRO DE LA JUNTA DIRECTIVA - 3 SUPLENTE
        );
    }

    if ($org == '12' || $org == '14') {

        $arrCV = array(
            '4170' => '01', //REPRESENTANTE LEGAL - PRINCIPAL        
            '4270' => '02', //REPRESENTANTE LEGAL - SUPLENTE
            '1100' => '03', //SOCIO CAPITALISTA
            '1101' => '03', //SOCIO CAPITALISTA - SUPLENTE
            '1110' => '03', //SOCIO INDUSTRIAL
            '1120' => '03', //SOCIO GESTOR
            '1121' => '03', //PRIMER SUPLENTE DEL SOCIO GESTOR 
            '1122' => '03', //SEGUNDO SUPLENTE DEL SOCIO GESTOR 
            '1126' => '03', //SOCIO GESTOR ADMINISTRADOR 
            '1130' => '03', //SOCIO COMANDITARIO
            '1140' => '03', //SOCIO ACCIONISTA
            '1150' => '03', //SOCIO ADMINISTRADOR
            '1151' => '03', //PRIMER SUPLENTE DEL SOCIO ADMINISTRADOR
            '1152' => '03', //SEGUNDO SUPLENTE DEL SOCIO ADMINISTRADOR
            '1160' => '03', //SOCIO COLECTIVO
            '1170' => '03', //ASOCIADO
            '3110' => '03', //SOCIO EMPRESARIO (EU) 
            '5160' => '04', //REVISOR FISCAL - PRINCIPAL
            '5260' => '05', //REVISOR FISCAL - SUPLENTE
            '4140' => '06', //MIEMBRO DE LA JUNTA DIRECTIVA - PRINCIPAL
            '4240' => '07' //MIEMBRO DE LA JUNTA DIRECTIVA - 3 SUPLENTE
        );
    }


    if (isset($arrCV[$valorCampo])) {
        return $arrCV[$valorCampo];
    } else {
        return '00';
    }
}

/**
 * Función que obtiene el código RUES de Tipo Propietario implementado en Sincronizaciones hacia RUES
 * @param type $valor
 * @return string
 */
function homologacionTipoPropietarioRUES($valor) {

    $valorCampo = trim($valor);

    $arrTP = array(
        '0' => '1', //PROPIETARIO UNICO
        '1' => '2', //SOCIEDAD DE HECHO
        '2' => '3' //COPROPIETARIO
    );

    if (isset($arrTP[$valorCampo])) {
        return $arrTP[$valorCampo];
    } else {
        return '';
    }
}

/**
 * Función que obtiene el código RUES de Tipo de Pago implementado en Sincronizaciones hacia RUES
 * @param type $valor
 * @return string
 */
function homologacionTipoPagoRUES($valor) {

    $valorCampo = trim($valor);

    $arrTP = array(
        '010201' => '01', //MATRICULA
        '010202' => '02', //RENOVACION
        '060100' => '03', //AFILIACION
        '010901' => '04' //BENEFICIO
    );

    if (isset($arrTP[$valorCampo])) {
        return $arrTP[$valorCampo];
    } else {
        return '';
    }
}

/**
 * Función que obtiene el código RUES de Naturaleza de ESADL implementado en Sincronizaciones hacia RUES
 * @param type $org
 * @param type $valor
 * @return string
 */
function homologacionNaturalezaEsadlRUES($org, $valor) {

    if ($org == '14') {
        $valorCampo = '4';
    } else {

        $valorCampo = trim($valor);

        $arrNT = array(
            '2' => '1', //ASOCIACION
            '3' => '2', //CORPORACION
            '1' => '3' //FUNDACION
        );
    }

    if (isset($arrNT[$valorCampo])) {
        return $arrNT[$valorCampo];
    } else {
        return '';
    }
}

?>