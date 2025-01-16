<?php
// script appelé via Ajax par la fonction javascript showNextSelect() et servant à insérer le <div><select> correspondant à la table systeme

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
$thisLevel=$_GET["level"];
	// on recupere les valeurs selectionnees dans le <select> precedent
$previousSelection='\''.arrayToList($_GET[$suppression_cascades[$domaine][$thisLevel-2]],'\',\'','\'');


// on génère le SELECT
$theSelectCode=insertDeleteSelect($domaine,$thisLevel,$previousSelection);
echo($theSelectCode);

?>