<?php

class funcionesRegistrales_retornarExpedienteMercantilCodigosBarrasPendientes {

    public static function retornarExpedienteMercantilCodigosBarrasPendientes($dbx = null, $mat = '') {
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/log.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesGenerales.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/funcionesRues.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/mysqli.php');
        require_once($_SESSION["generales"]["pathabsoluto"] . '/api/myErrorHandler.php');
        set_error_handler('myErrorHandler');

        //
        if (!isset($_SESSION["generales"]["codestadosrutamercantil"]) || empty($_SESSION["generales"]["codestadosrutamercantil"])) {
            $arrX = retornarRegistrosMysqliApi($dbx, 'mreg_codestados_rutamercantil', "1=1","id");
            foreach ($arrX as $ax) {
                $_SESSION["generales"]["codestadosrutamercantil"][$ax["id"]] = $ax;
            }
            unset ($arrX);
        }
        
        //
        if (!isset($_SESSION["generales"]["rutas"]) || empty($_SESSION["generales"]["rutas"])) {
            $arrX = retornarRegistrosMysqliApi($dbx, 'mreg_codrutas', "1=1","id");
            foreach ($arrX as $ax) {
                $_SESSION["generales"]["rutas"][$ax["id"]] = $ax;
            }
            unset ($arrX);
        }
        
        
        $retorno = array ();
        $retorno["cantidad"] = 0;
        $retorno["embargos"] = 0;
        $retorno["recursos"] = 0;
        $retorno["codigosbarras"] = array();
        $ix = 0;
        $arrX = retornarRegistrosMysqliApi($dbx, 'mreg_est_codigosbarras', "matricula='" . $mat . "'", "codigobarras");
        if ($arrX && !empty($arrX)) {
            foreach ($arrX as $x) {
                if (!isset($_SESSION["generales"]["rutas"][$x["actoreparto"]]) || 
                $_SESSION["generales"]["rutas"][$x["actoreparto"]]["tipo"] == 'ME' || 
                $_SESSION["generales"]["rutas"][$x["actoreparto"]]["tipo"] == 'ES' ||
                $_SESSION["generales"]["rutas"][$x["actoreparto"]]["tipo"] == 'RR'
                ) {
                    if (!isset($_SESSION["generales"]["codestadosrutamercantil"][$x["estadofinal"]]) || 
                    $_SESSION["generales"]["codestadosrutamercantil"][$x["estadofinal"]]["estadoterminal"] != 'S') {
                        $ix++;
                        $retorno["codigosbarras"][$ix]["cbar"] = $x["codigobarras"];
                        $retorno["codigosbarras"][$ix]["frad"] = $x["fecharadicacion"];
                        $retorno["codigosbarras"][$ix]["ttra"] = $x["actoreparto"];
                        $retorno["codigosbarras"][$ix]["esta"] = $x["estadofinal"];
                        $retorno["codigosbarras"][$ix]["nesta"] = '';
                        $retorno["codigosbarras"][$ix]["ntra"] = $_SESSION["generales"]["rutas"][$x["actoreparto"]]["descripcion"];
                        $retorno["codigosbarras"][$ix]["sist"] = $_SESSION["generales"]["rutas"][$x["actoreparto"]]["tipo"];
                        $retorno["codigosbarras"][$ix]["nesta"] = $_SESSION["generales"]["codestadosrutamercantil"][$x["estadofinal"]]["descripcion"];
                        $retorno["cantidad"]++;

                        // Si la ruta es un embargo
                        if ($x["actoreparto"] == '07') {
                            $retorno["embargos"]++;
                        }
                        
                        // SI la ruta es un recurso
                        if ($_SESSION["generales"]["rutas"][$x["actoreparto"]]["tipo"] == 'RR') {
                            $retorno["recursos"]++;
                        }
                    }
                }
            }
        }
        
        return $retorno;
    }

}
