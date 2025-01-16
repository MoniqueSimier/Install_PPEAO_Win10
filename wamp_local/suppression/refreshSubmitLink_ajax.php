<?php
// script appelé via Ajax par la fonction javascript updateSubmitLink() pour mettre a jour le lien permettant d'afficher les resultats, chaque fois que la selection change



session_start();



include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/suppression/suppression_functions.php';



global $tablesDefinitions;
global $suppression_cascades;
global $connectPPEAO;

// parametres a passer a la fonction
$domaine=$_GET["domaine"];
// le nombre total d'unites correspondant a la requete
$total=countMatchingUnits($domaine);


if ($domaine=='exp') {
if ($total==0) {$leLien='<div id="affiche_unites">il n&#x27;existe aucune campagne correspondante dans la base de donn&eacute;es</div>';}
else {
	$leLien='<div id="affiche_unites"><a href="/edition_supprimer.php?'.$_SERVER["QUERY_STRING"].'&mode=liste" id="affiche_unites_lien" class="next_step">afficher '.$total.' campagne(s) correspondante(s)</a></div>';
}

} // end if domaine==exp

if ($domaine=='art') {
if ($total==0) {$leLien='<div id="affiche_unites">il n&#x27;existe aucune p&eacute;riode d&#x27;enqu&ecirc;te correspondante dans la base de donn&eacute;es</div>';}
else {
	$leLien='<div id="affiche_unites"><a href="/edition_supprimer.php?'.$_SERVER["QUERY_STRING"].'&mode=liste" id="affiche_unites_lien" class="next_step">afficher '.$total.' p&eacute;riode(s) d&#x27;enqu&ecirc;te correspondante(s)</a></div>';
}

}

echo($leLien);

?>