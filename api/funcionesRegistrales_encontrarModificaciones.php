<?php

class funcionesRegistrales_encontrarModificaciones {

    public static function encontrarModificaciones($mysqli, $control, $tipoenvio = '1') {
        $camposExcluidos = array (
            'admondian',
            'aportantesegsocial',
            'anorenaflia',
            'codigobarras',
            'codigozonacom',
            'codigozonanot',
            'ctrbenart4',
            'ctrbenart7',
            'ctrbenart50',            
            'ctrbenley1780',
            'ctrcertificardesde',
            'ctrembargo',
            'ctrembargostramite',
            'ctrestdatos',
            'ctrlibroscomercio',
            'ctrnotsms',
            'ctrrecursostramite',
            'ctrubi',
            'cumplerequisitos1780',
            'cumplerequisitos1780primren',
            'fechanacimiento',
            'fecexpdoc',
            'fecrenaflia',
            'nacionalidad',
            'numrecibo',
            'paicom',
            'painot',
            'paisexpdoc',
            'renunciabeneficios1780',
            'sexo',
            'tipoaportantesegsocial',
            'tiposedeadm',
            'versionciiu'
        );
        
        //
        $xexps = array();
        $modificaciones = array();
        $condicion = "(fecha >= '" . $_SESSION["entrada"]["fechainicial"] . "' and fecha <= '" . $_SESSION["entrada"]["fechafinal"] . "')";
        $mats = retornarRegistrosMysqliApi($mysqli, 'mreg_campos_historicos_' . substr($_SESSION["entrada"]["fechainicial"], 0, 4), $condicion, "matricula", "matricula,fecha,hora");
        if ($mats && !empty($mats)) {
            foreach ($mats as $m) {
                if (($m["fecha"] == $_SESSION["entrada"]["fechainicial"] && $m["hora"] >= $_SESSION["entrada"]["horainicial"]) || $m["fecha"] > $_SESSION["entrada"]["fechainicial"]) {
                    if (($m["fecha"] == $_SESSION["entrada"]["fechafinal"] && $m["hora"] <= $_SESSION["entrada"]["horafinal"]) || $m["fecha"] < $_SESSION["entrada"]["fechafinal"]) {
                        if (!isset($xexps[$m["matricula"]])) {
                            $xexp = retornarRegistroMysqliApi($mysqli, 'mreg_est_inscritos', "matricula='" . $m["matricula"] . "'");
                            if ($xexp && !empty($xexp)) {
                                $xexps[$m["matricula"]] = $xexp["fecmatricula"];
                            }
                        }

                        //
                        $incluir = 'si';
                        if (in_array($m["campo"], $camposExcluidos)) {
                            $incluir = 'no';
                        }
                        
                        //
                        if ($incluir == 'si') {
                            if ($m["tipotramite"] == 'matriculapnat' ||
                            m["tipotramite"] == 'matriculaest' ||
                            $m["tipotramite"] == 'matriculapjur') {
                                $incluir = 'no';
                            } else {
                                if (substr($m["tipotramite"], 0, 10) == 'renovacion') {
                                    $incluir = 'si';
                                } else {
                                    if (!isset($xexps[$m["matricula"]]) || $xexps[$m["matricula"]]["fecmatricula"] == $m["fecha"]) {
                                        $incluir = 'no';
                                    }
                                }
                            }
                        }

                        //
                        if (!isset($xexps[$m["matricula"]]) || $xexps[$m["matricula"]]["fecmatricula"] != $m["fecha"]) {
                            $modificaciones[$m["matricula"]] = $m["matricula"];
                        }
                    }
                }
            }
        }
        return $modificaciones;
    }

}

?>
