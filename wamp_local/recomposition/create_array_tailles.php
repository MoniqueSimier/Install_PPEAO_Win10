<?php
//////////////////////////////////////////////////////////////////////////////////
//                          Pour les tailles:                                   //
//                       création du tableau $FT                                //
//////////////////////////////////////////////////////////////////////////////////
$query = "select AF.id, APM.taille 
	from art_fraction as AF, art_poisson_mesure as APM 
	where APM.art_fraction_id = AF.id 
	and AF.debarquee = 1 
	order by AF.id";
$result = pg_query($connection, $query);
while($row = pg_fetch_row($result)){
	$id = $row[0];                             //clé = identifiant fraction
	$FT[$id][] = $row[1];                      //tailles incrementés auto dans tableau :
}                                    //$FT[$id][0] = taille 1, $FT[$id][1] = taille 2...
pg_free_result($result);//19 09
//pg_close();


?>