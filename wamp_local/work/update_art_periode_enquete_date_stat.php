<?php 
// Page de sélection des données à extraire
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';


$zone=0; // zone libre (voir table admin_zones)

 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	include $_SERVER["DOCUMENT_ROOT"].'/extraction/selection/selection_functions.php';
	
	// on récupère le nom de la zone concernée
$sql='SELECT *
			FROM art_periode_enquete
			WHERE true ORDER BY id
			';
$result = pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array=pg_fetch_all($result);

//debug echo('<pre>');print_r($array);echo('</pre>');

foreach ($array as $row) {
	$date=$row["annee"].'-'.number_pad($row["mois"],2).'-01';
	$sqlup='UPDATE art_periode_enquete SET date_stat=CAST(\''.$date.'\' as date) WHERE id='.$row["id"];
	//debug 
	echo('<pre>');print_r($sqlup);echo('</pre>');
	
	$resultup = pg_query($connectPPEAO,$sqlup) or die('erreur dans la requete : '.$sqlup. pg_last_error());
}


?>