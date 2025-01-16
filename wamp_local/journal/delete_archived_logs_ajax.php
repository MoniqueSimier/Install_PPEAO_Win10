<?php

// script appele via Ajax pour supprimer physiquement les scripts archives du journal
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';

session_start();


// on initialise la varaible de resultat
$resultat='ok';


// on ouvre le dossier dans lequel sont stockees les archives
if ($handle = opendir($_SERVER["DOCUMENT_ROOT"].$logArchivePath)) {
	
// ensuite on boucle dans le dossier pour stocker la liste des fichiers presents
while (false !== ($file = readdir($handle))) {
	$pathInfo=pathinfo(strtolower(str_replace('//','/',$_SERVER["DOCUMENT_ROOT"].$logArchivePath.$file)));
	// on ne traite par securite que les archives .gz

	if (strtolower($pathInfo["extension"])=='gz') {
	if (!unlink(strtolower(str_replace('//','/',$_SERVER["DOCUMENT_ROOT"].$logArchivePath.$file))))  {
		$resultat='erreur: un ou plusieurs fichiers n&#x27;ont pas pu &ecirc;tre supprim&eacute;s';
	};
}
	
}
}
else {
	$resultat='erreur : le dossier des archives du journal est introuvable';
}


if ($resultat=='ok') {logWriteTo(4,"notice","archives du journal effac&eacute;es","","",0);}
else {logWriteTo(4,"error","erreur lors de la suppression des archives du journal","","",0);}


echo($resultat);

?>