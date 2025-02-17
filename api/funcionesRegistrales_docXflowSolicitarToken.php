<?php

class funcionesRegistrales_docXflowSolicitarToken {

    public static function docXflowSolicitarToken() {
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once ($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        $data = array("_username" => DOCXFLOW_USERNAME, "_password" => DOCXFLOW_PASSWORD);
        $json_data = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, DOCXFLOW_SERVER . "/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);
        $dataResp = json_decode($result, true);
        \logApi::general2('docxflowSolicitarToken_' . date("Ymd"), '', 'Respuesta docxflow solicitar token :' . $result);
        return $dataResp["token"];
    }

}

?>
