<?php 
// Mis à jour Yann LAURENT, 01-07-2008

include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
$bdd = $_GET['base'];
if($bdd==""){
	$bdd=$db_default;
}
$connection =pg_connect ("host=".$host." dbname=".$bdd." user=".$user." password=".$passwd);
if (!$connection) {  echo "pas de connection"; exit;}

print("<br/>travail sur la base : ".$bdd);

//////////////////////////////////////////////////////////////////////////////////////////////
//                                   EFFACEMENT DES DONNEES DE                              //
//                                     ART_DEBARQUEMENT_REC ET                              //
//                                      DE ART_FRACTION_REC                                 //
//////////////////////////////////////////////////////////////////////////////////////////////
$traitement1 = "pasok";
$traitement2 = "pasok";


$query = "delete from art_fraction_rec;";
$result = pg_exec($connection, $query);

if (!$result) {  
	echo "Attention Une erreur s'est produite lors de la suppression de art_fraction_rec "; print($query);  exit;
} else {
	$traitement1 = "ok";
}

//pg_close();

//$connection =pg_connect ("host=".$host." dbname=".$bdd." user=".$user." password=".$passwd);
//if (!$connection) {  echo "Attention pas de connection"; exit;}

$query = "delete from art_debarquement_rec;";
//print "$query<br/>";

$result = pg_exec($connection, $query);
if (!$result) {
  	echo "<br/>Attention une erreur s'est produite lors de la suppression de art_debarquement_rec "; print($query);  
} else {
	$traitement2 = "ok";
	
}
if ( $traitement1 == "ok" &&  $traitement2 == "ok" ) {
	echo "<br/>les donn&eacute;es ont &eacute;t&eacute; supprim&eacute;es avec succ&egrave;s.";
}
// Creation et envoi de la requete
$query = "select count(art_debarquement.id) FROM art_debarquement";
$result = pg_query($connection, $query);
if (!$result) {  echo "Une erreur s'est produite";  exit;}
// Recuperation du resultat
$row= pg_fetch_row($result);
$nb_enr = $row[0];
print ("<div id='nbEnquete'>".$nb_enr . " enqu&ecirc;tes &agrave; traiter dont");
$query = "select count(id)
	FROM art_debarquement_rec";
$result = pg_query($connection, $query);
$row= pg_fetch_row($result);
$nb_deja_rec = $row[0];
if ($nb_deja_rec == 0){print (" ".$nb_deja_rec);}
else {print (" ".$nb_deja_rec);}
print " enqu&ecirc;tes d&eacute;j&agrave; recompos&eacute;es. </div>";
			
//pg_close();


?>
