<?php

class funcionesRegistrales_docXflowConsultarExpediente {

    public static function docXflowConsultarExpediente($mysqli, $mat = '', $prop = '', $rad = '') {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');

        $_SESSION["generales"]["docxflowtoken"] = \funcionesRegistrales::docXflowSolicitarToken();

        //
        if (substr($mat, 0, 1) == 'S') {
            $insc = $mat;
            $mat = '';
        } else {
            $insc = '';
        }
        $data = array(
            "Matricula" => $mat,
            "Inscripcion" => $insc,
            "Proponente" => $prop
        );

        //
        $json_data = json_encode($data);
        \logApi::general2('docXflowConsultarExpediente' . date("Ymd"), $cb, 'Petición a docxflow :' . $json_data);
        $authorization = "Authorization: Bearer " . $_SESSION["generales"]["docxflowtoken"];

        //
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, DOCXFLOW_SERVER . "/srd/consultaDocumentosExpedienteRegistro");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        $result = curl_exec($ch);
        curl_close($ch);
        $dataResp = json_decode($result, true);
        \logApi::general2('docXflowConsultarExpediente' . date("Ymd"), $cb, 'Respuesta docxflow :' . $result);

        //
        if (isset($dataResp["codigoError"])) {
            if ($dataResp["codigoError"] == '0000') {
                $_SESSION["generales"]["mensajeerror"] = '';
                return $dataResp["respuesta"];
            } else {
                $_SESSION["generales"]["mensajeerror"] = $dataResp["codigoError"] . ' - ' . $dataResp["mensajeError"];
                \logApi::general2('docXflowConsultarExpediente' . date("Ymd"), $cb, 'Respuesta docxflow :' . $dataResp["codigoError"] . ' - ' . $dataResp["mensajeError"]);
                return false;
            }
        } else {
            $_SESSION["generales"]["mensajeerror"] = 'No se obtuvo respuesta del servicio rest de DocXflow';
            \logApi::general2('docxflowReportarCambioEstado_' . date("Ymd"), $cb, 'No se obtuvo respuesta del servicio rest de DocXflow');
            return false;
        }
    }

}

?>
