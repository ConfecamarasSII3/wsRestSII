<?php
session_start();
$_SESSION["generales"] = array();
$_SESSION["generales"]["pathabsoluto"] = $argv[1];
require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRegistrales.php');
require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/retornarHomologaciones.php');
ini_set('default_socket_timeout', 14400);
ini_set('set_time_limit', 14400);
ini_set('memory_limit', '3072M');
ini_set('display_errors', '1');
$_SESSION["generales"]["codigoempresa"] = $argv[2];
require_once ($_SESSION["generales"]["pathabsoluto"] . '/configuracion/common' . $_SESSION["generales"]["codigoempresa"] . '.php');
$mysqli = conexionMysqliApi('P-' . $_SESSION["generales"]["codigoempresa"]);
$condicion = "ultanoren = '2024' and fecrenovacion < '20240100'";
$regs = retornarRegistrosMysqliApi($mysqli, 'mreg_est_inscritos', $condicion, "matricula","matricula,ultanoren,fecrenovacion");
echo "codigo empresa : " . $argv[2] . "\r\n";
echo "cantidad : " . count($regs) . "\r\n";
if (!empty($regs)) {
    foreach ($regs as $rg) {
        echo $rg["matricula"] . ' -  ' . $rg["ultanoren"] . ' - ' . $rg["fecrenovacion"] . ' / ';
        $exp = \funcionesRegistrales::retornarExpedienteMercantil($mysqli, $rg["matricula"]);
        echo $exp["ultanoren"] . ' -  ' . $exp["fecharenovacion"] . "\r\n";
        if ($rg["ultanoren"] != $exp["ultanoren"] || $rg["fecrenovacion"] != $exp["fecharenovacion"]) {
            $detalle = 'Ajuste fecha renovacion : ' . $rg["matricula"] . ' -  ' . $rg["ultanoren"] . ' - ' . $rg["fecrenovacion"] . ' / ' . $exp["ultanoren"] . ' -  ' . $exp["fecharenovacion"];
            actualizarLogMysqliApi($mysqli, 'XXX', 'JINT', '', '', '', '', $detalle, $rg["matricula"]);
        }        
    }
}
$mysqli->close();
?>