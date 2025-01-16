<?php
////////////////////////////////////////////////////////////////////////////////////////////
//                                   Prétraitement                                         //
//                                                                                         //
//                             Fafrication du tableau coef_esp                             //
//                     receuillant les informations de k et b par espèce                   //
/////////////////////////////////////////////////////////////////////////////////////////////
$query = "select id, coefficient_k, coefficient_b, ref_espece_id FROM ref_espece order by id";
//print "query ===".$query."<br/>";
$result = pg_query($connection, $query);

while($row = pg_fetch_row($result)){
	$esp = $row[0];            //espece
	$k = $row[1];              //coef k
	$b = $row[2];              //coef b
	$ref = $row[3];            //ref
	
	$coef_esp[$esp][0]= $k;
	$coef_esp[$esp][1]= $b;
	$coef_esp[$esp][2]= $ref;
	
}
//pg_close();
//remise à zéro du pointeur
reset($coef_esp);
while (list($key, $val) = each($coef_esp)){    
	// si k et b non renseignés ou renseignés avec les deux valeurs à 0 
	if ( (($val[0] == 0)&&($val[1] == 0))  ||  (($val[0] == "")&&($val[1] == ""))   ){
		//si il existe une espèce référence
		if ($val[2] != "") { 
			$new = $val[2];
			$coef_esp[$key][0]=$coef_esp[$new][0];
			$coef_esp[$key][1]=$coef_esp[$new][1];
			}
		//sinon k=1, b=3
		else{
			$coef_esp[$key][0]= 1;
			$coef_esp[$key][1]= 3;
		}
	}
	
}// fin du while
?>