<?php

class funcionesRegistrales_alertarNoComerciales {

    public static function alertarNoComerciales($mysqli, $data) {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        if (defined('EMAIL_NOTIFICACION_NO_COMERCIALES') && trim(EMAIL_NOTIFICACION_NO_COMERCIALES) != '') {
            if ($data["matricula"] != '') {
                if ($data["organizacion"] == '01' && ($data["estadomatricula"] == 'MA' || $data["estadomatricula"] == 'MI' || $data["estadomatricula"] == 'MR')) {
                    $totalciius = 0;
                    $totalciiusnocom = 0;
                    for ($iciiu = 1; $iciiu <= 4; $iciiu++) {
                        if ($data["ciius"][$iciiu] != '') {
                            $totalciius++;
                            $regciiu = retornarRegistroMysqliApi($mysqli, 'bas_ciius', "idciiu='" . $data["ciius"][$iciiu] . "'");
                            if ($regciiu && !empty($regciiu)) {
                                if ($regciiu["actividadcomercial"] == 'NO') {
                                    $totalciiusnocom++;
                                }
                            }
                        }
                    }
                    if ($totalciius != 0 && $totalciius == $totalciiusnocom) {
                        if (contarRegistrosMysqliApi($mysqli, 'mreg_control_ejecucion_expedientes', "expediente='" . $data["matricula"] . "' and proceso='notificar-ciius-no-comerciales-" . date ("Ymd") . "'") == 0) {
                            if (TIPO_AMBIENTE == 'PRUEBAS') {
                                if (defined('EMAIL_NOTIFICACION_PRUEBAS') && trim(EMAIL_NOTIFICACION_PRUEBAS) != '') {
                                    $emx = EMAIL_NOTIFICACION_PRUEBAS;
                                } else {
                                    $emx = 'jint@confecamaras.org.co';
                                }
                            } else {
                                $emx = EMAIL_NOTIFICACION_NO_COMERCIALES;
                            }
                            $txtMensaje = 'Nos permitimos informarle que la matrícula No. ' . $data["matricula"] . ' perteneciente a ' . $data["nombre"] . ' se ha actualizado y ';
                            $txtMensaje .= 'solo contiene Códigos Ciius considerados de actividades no comerciales.<br><br>';
                            $txtMensaje .= 'Se envía este mensaje como alerta de control<br><br>';
                            $txtMensaje .= 'ADMINISTRADOR SII';
                            \funcionesGenerales::enviarEmail(SERVER_SMTP, SMTP_PORT, REQUIERE_SMTP_AUTENTICACION, SMTP_TIPO_ENCRIPCION, CUENTA_ADMIN_PORTAL, CLAVE_ADMIN_PORTAL, EMAIL_ADMIN_PORTAL, NOMBRE_ADMIN_PORTAL, $emx, 'Alerta expedientes con solo actividades no comerciales', $txtMensaje);
                            $arrCampos = array(
                                'expediente',
                                'proceso',
                                'fecha_hora',
                                'contenido',
                                'destino',
                                'estado'
                            );
                            $arrValores = array(
                                "'" . $data["matricula"] . "'",
                                "'notificar-ciius-no-comerciales-" . date ("Ymd") . "'",
                                "'" . date("Ymd") . " " . date("His") . "'",
                                "'" . addslashes($txtMensaje) . "'",
                                "'" . addslashes($emx) . "'",
                                "'OK'"
                            );
                            insertarRegistrosMysqliApi($mysqli, 'mreg_control_ejecucion_expedientes', $arrCampos, $arrValores);
                        }
                    }
                }
            }
        }
        return true;
    }

}

?>
