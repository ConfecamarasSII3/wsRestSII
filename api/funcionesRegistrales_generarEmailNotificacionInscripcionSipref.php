<?php

class funcionesRegistrales_generarEmailNotificacionInscripcionSipref {

    public static function generarEmailNotificacionInscripcionSipref($mysqli = null, $t = null) {
        $tiporeg = '';
        $tipolibro = '';
        switch (substr($t["libro"], 0, 2)) {
            case "RM" :
            case "ME" :
                $tiporeg = 'REGISTRO PUBLICO MERCANTIL';
                if (substr($t["libro"], 2, 2) == '22') {
                    $tiporeg = 'REGISTRO DE COMERCIANTES QUE EJERCEN LA ACTIVIDAD DE APUESTAS Y JUEGOS DE AZAR';
                }
                $tipolibro = substr($t["libro"], 2, 2);
                break;
            case "RE" :
            case "ES" :
                $tiporeg = 'REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO';
                if (substr($t["libro"], 2, 2) == '51') {
                    $tipolibro = '01';
                }
                if (substr($t["libro"], 2, 2) == '52') {
                    $tipolibro = '02';
                }
                if (substr($t["libro"], 2, 2) == '53') {
                    $tiporeg = 'REGISTRO DE LA ECONOMIA SOLIDARIA';
                    $tipolibro = '03';
                }
                if (substr($t["libro"], 2, 2) == '54') {
                    $tiporeg = 'REGISTRO DE VEEDURIAS';
                    $tipolibro = '04';
                }
                if (substr($t["libro"], 2, 2) == '55') {
                    $tiporeg = 'REGISTRO DE ENTIDADES SIN ANIMO DE LUCRO DE CAPITAL PRIVADO EXTRANJERO';
                    $tipolibro = '05';
                }
                break;
        }

        $msg = '';
        $msg .= 'LA ' . RAZONSOCIAL . ' le informa que el dia ' . \funcionesGenerales::mostrarFecha($t["fecharegistro"]) . ' a las ' . \funcionesGenerales::mostrarHora($t["horaregistro"]) . ' ';
        $msg .= 'fue inscrito en el ' . $tiporeg . ', en el libro ' . $tipolibro . ' bajo el numero ' . $t["registro"] . ' la siguiente actuacion: <br><br>';

        if (trim($t["recibo"]) != '') {
            $msg .= 'Recibo de Caja No. ' . $t["recibo"] . '<br>';
        }
        if (trim($t["numerooperacion"]) != '') {
            $msg .= 'Numero Operacion: ' . $t["numerooperacion"] . '<br>';
        }
        if (isset($t["matricula"]) && ltrim($t["matricula"], "0") != '') {
            $msg .= 'Matricula: ' . $t["matricula"] . '<br>';
        }
        if (ltrim($t["identificacion"], "0") != '') {
            $msg .= 'Identificacion: ' . $t["identificacion"] . '<br>';
        }
        $msg .= 'Nombre: ' . \funcionesGenerales::utf8_decode($t["nombre"]) . '<br>';

//
        $esmat = '';
        $txtx = '';
        if (substr($t["libro"], 0, 2) == 'RM' || substr($t["libro"], 0, 2) == 'ME') {
            $regActo = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $t["libro"] . "' and idacto='" . $t["acto"] . "'");
            $msg .= 'Acto: ' . $regActo["nombre"] . '<br>';
            if ($regActo["idgrupoacto"] == '001') {
                if ($t["matricula"] != '') {
                    $exp1 = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $t["matricula"] . "'", "matricula,organizacion,categoria");
                    if ($exp1 && $exp1["organizacion"] == '01' || ($exp1["organizacion"] > '02' && $exp1["categoria"] == '1' && $exp1["organizacion"] != '12' && $exp1["organizacion"] != '14')) {
                        $esmat = 'si';
                    }
                }
            }
        }

        if (substr($t["libro"], 0, 2) == 'RE' || substr($t["libro"], 0, 2) == 'ES') {
            $regActo = retornarRegistroMysqliApi($mysqli, 'mreg_actos', "idlibro='" . $t["libro"] . "' and idacto='" . $t["acto"] . "'");
            $msg .= 'Acto: ' . $regActo["nombre"] . '<br>';
        }

        //
        $msg .= 'Noticia: ' . $t["noticia"] . '<br><br>';

        // 2020-02-21: JINT: Se incluye nota si es matricula 
        if ($esmat == 'si') {
            $msg .= 'Señor empresario, por matricularse en la Cámara de Comercio puede acceder a diversos beneficios. Lo invitamos a que consulte el portafolio ';
            $msg .= 'de servicios y programas en la página web de su Cámara de Comercio<br><br>';
        }

        //
        $msg .= 'Antes de proceder con la solicitud del certificado en la C&aacute;mara de Comercio le recomendamos validar a través de nuestra página web ';
        $msg .= 'el estado de su trámite y confirmar que haya terminado su proceso de digitaci&oacute;n y control de calidad.';
        // $msg .= '<a href="' . TIPO_HTTP . HTTP_HOST . '/librerias/proceso/mregConsultaRutaDocumentos.php?accion=traerruta&_empresa=' . $_SESSION["generales"]["codigoempresa"] . '&_recibo=' . $t["recibo"] . '&session_parameters=' . \funcionesGenerales::armarVariablesPantalla() . '">Verificar</a>';
        $msg .= '<br><br>';

    //
        if (!defined('NOTIFICAR_TELEFONO')) {
            defined('NOTIFICAR_TELEFONO', 'NO');
        }
        if (NOTIFICAR_TELEFONO == 'SI') {
            $msg .= 'Para mayores informes por favor comunicarse al numero ';
            $msg .= TELEFONO_ATENCION_USUARIOS . ' en la ciudad de ' . retornarRegistroMysqliApi($mysqli, "bas_municipios", "codigomunicipio='" . MUNICIPIO . "'", "ciudad") . '.<br><br>';
        }
        
        //
        if (defined('ENLACE_ENCUESTAS_INSCRIPCIONES') && trim(ENLACE_ENCUESTAS_INSCRIPCIONES) != '') {
            $msg .= 'Lo invitamos a diligenciar la siguiente encuesta de satisfacción. Su opinión nos ayudará a mejorar nuestros servicios<br>';
            $msg .= '<a href="' . ENLACE_ENCUESTAS_INSCRIPCIONES . '">Diligenciar encuesta de satisfacción</a><br><br>';
        }
        
        //
        $msg .= 'Este mensaje se envia en forma automatica por el Sistema de Registro de LA ' . RAZONSOCIAL . ' y tiene por objeto informar, ';
        $msg .= 'en cumplimiento a lo contemplado en el Codigo de Procedimiento Administrativo y de lo Contencioso Administrativo.';
        $msg .= '<br><br>';
        $msg .= 'Correo desatendido: Por favor no responda a la direccion de correo electronico que envia este mensaje, dicha cuenta ';
        $msg .= 'no es revisada por ningun funcionario de nuestra entidad. Este mensaje es informativo.';
        $msg .= '<br><br>';
        $msg .= 'Los acentos y tildes de este correo han sido omitidos intencionalmente con el objeto de evitar inconvenientes en la lectura del mismo.';
        return $msg;
    }

}

?>
