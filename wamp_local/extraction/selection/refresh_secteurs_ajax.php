<?php

// script appelé via Ajax par la fonction javascript refreshSecteurs() pour rafraichir le <select> permettant de sélectionner les secteurs des données à extraire


include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/selection/selection_functions.php';

$liste_enquetes=$_GET["enquetes"];
$enquetes_ids=explode(',',$liste_enquetes);

$systemes2=$_GET["systemes2"];

	
$array_secteurs=listSelectSecteurs($systemes2,$enquetes_ids);
	
	// on genere la liste des <options> pour raffraichir le <select>
	$options='';
	// si il n'y a pas de valeurs correspondantes, on renvoie un message
	if (empty($array_secteurs)) {
		$options='<option value="">aucun secteur</option>';
	}  // end if empty
	else {
		foreach ($array_secteurs as $secteur) {
		$options.='<option value="'.$secteur["id"].'">('.$secteur["systeme"].') '.$secteur["secteur"].'</option>';
		}
	} // end else


echo($options);

?>