<?php

class funcionesRegistrales_matrizActividadEconomica {

    public static function matrizActividadEconomica($mysqli, $numcon, $condicion) {
        $query = "SELECT ciiu1, descripcion,PNAT,PJUR,EST,SUC,AGE,SUM(PNAT+PJUR+EST+SUC+AGE) as Subtotal,ESADL,SUM(PNAT+PJUR+EST+SUC+AGE + ESADL) AS grantotal from (
select matr.ciiu1,
ciius.descripcion,
sum(case when organizacion='01' then 1 else 0 end) as PNAT,
sum(case when organizacion>'02'  and organizacion not in(12,13,14) and categoria in(0,1) 
then 1 else 0 end) as PJUR,
sum(case when organizacion='02' then 1 else 0 end) as EST,
sum(case when categoria=2 then 1 else 0 end) as SUC,
sum(case when categoria=3 then 1 else 0 end) as AGE,
sum(case when organizacion in(12,14) and categoria=1 then 1 else 0 end) as ESADL
from mreg_est_matriculados as matr
INNER JOIN bas_ciius as ciius on 
matr.ciiu1=ciius.idciiu
where matr.muncom='" . $numcon . "'
and " . $condicion . "
group by matr.ciiu1,ciius.descripcion
order by matr.ciiu1) x
group by ciiu1,descripcion
order by ciiu1";
        try {
            $result = ejecutarQueryMysqliApi($mysqli, $query);
            return $result;
        } catch (Exception $ex) {
            $_SESSION["mensajeerror"] = 'Error en query ' . str_replace("'", "", $ex->getMessage());
            $result = $_SESSION["mensajeerror"];
            return $result;
        }
    }
}

?>
