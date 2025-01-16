<?php

// script appelé via Ajax par la fonction javascript refreshPeriode() pour rafraichir les <select> permettant de sélectionner l'année/mois de début/fin des données à extraire

include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/selection/selection_functions.php';

// démarrage de la session
session_start();

$selection=$_GET["selection"];
$debut=array("annee"=>$_GET["debut_annee"],"mois"=>$_GET["debut_mois"]);
$fin=array("annee"=>$_GET["fin_annee"],"mois"=>$_GET["fin_mois"]);



// on construit le <select> correspondant 
// si on a change d_a, on contruit d_m et on efface f_a et f_m
// si on a change d_m on construit f_a et on efface f_m
// si on a change f_a on construit f_m

switch ($selection) {
// si on doit raffraichir le <select> du mois de debut
case "d_a":
	$responseText='<select id="d_m" name="d_m" onchange="javascript:refreshPeriode(\'d_m\',\''.$debut["annee"].'\',\''.$debut["mois"].'\',\''.$fin["annee"].'\',\''.$fin["mois"].'\');"">';
	// la premiere ligne est "vide"
	$responseText.='<option value="-1" selected="selected">-mois-</option>';	
	$premier_mois=1;$dernier_mois=12;
	// cas particuliers des annees limites : il se peut que les douze mois de ces annees ne soient pas disponibles
	if ($_GET["d_a"]==$fin["annee"]) {$premier_mois=1;$dernier_mois=$fin["mois"];}
	if ($_GET["d_a"]==$debut["annee"]) {$premier_mois=$debut["mois"];$dernier_mois=12;}
	if ($_GET["d_a"]==$fin["annee"] && $_GET["d_a"]==$debut["annee"])
		{$premier_mois=$debut["mois"];$dernier_mois=$fin["mois"];}
	$i=$premier_mois;
	while ($i<=$dernier_mois) {
		$responseText.='<option value="'.$i.'">'.number_pad($i,2).'</option>';
		$i++;
	}
	$responseText.='</select>';
break;

// si on doit raffraichir le <select> de l'annee de fin
case "d_m":
	$responseText='<select id="f_a", name="f_a" onchange="javascript:refreshPeriode(\'f_a\',\''.$debut["annee"].'\',\''.$debut["mois"].'\',\''.$fin["annee"].'\',\''.$fin["mois"].'\');"">';
	
	// la premiere ligne est "vide"
	$responseText.='<option value="-1">-ann&eacute;e-</option>';
		$i=$_GET["d_a"];$end=$fin["annee"];
	while ($i<=$end) {
		$responseText.='<option value="'.$i.'">'.$i.'</option>';
		$i++;
	}
	$responseText.='</select>';

break;

// si on doit raffraichir le <select> du mois de fin
case "f_a":
	$responseText='<select id="f_m" name="f_m" onchange="refreshPeriode(\'f_m\',\'\',\'\',\'\',\'\');">';
	// la premiere ligne est "vide"
	$responseText.='<option value="-1">-mois-</option>';
	$premier_mois=1;$dernier_mois=12;
	// cas particuliers des annees limites : il se peut que les douze mois de ces annees ne soient pas disponibles
	if ($_GET["f_a"]==$fin["annee"]) {$premier_mois=1;$dernier_mois=$fin["mois"];}
	if ($_GET["f_a"]==$debut["annee"]) {$premier_mois=$debut["mois"];$dernier_mois=12;}
	if ($_GET["f_a"]==$fin["annee"] && $_GET["f_a"]==$debut["annee"])
		{$premier_mois=$_GET["d_m"];$dernier_mois=$fin["mois"];}
	if ($_GET["f_a"]==$_GET["d_a"])	
		{$premier_mois=$_GET["d_m"];}
	$i=$premier_mois;
	
	while ($i<=$dernier_mois) {
		$responseText.='<option value="'.$i.'">'.number_pad($i,2).'</option>';
		$i++;
	}
	$responseText.='</select>';
break;

// si la selection de periode est terminee (i.e. une valeur de f_m est choisie)
// on affiche le lien permettant de passer a la suite
case "f_m":
// si la selection de periode est terminee (i.e. une valeur de f_m est choisie)
	// on affiche le lien permettant de passer a la suite
	$url=$_SESSION["FULL_URL"];
	$url=removeQueryStringParam($url,'d_a');
	$url=removeQueryStringParam($url,'d_m');
	$url=removeQueryStringParam($url,'f_a');
	$url=removeQueryStringParam($url,'f_m');
	$responseText='<a href="#" class="next_step" onclick="javascript:goToNextStep(4,\''.$url.'\');return false;">ajouter et choisir un type d&#x27;exploitation &gt;&gt;</a>';
break;

}




echo($responseText);

?>