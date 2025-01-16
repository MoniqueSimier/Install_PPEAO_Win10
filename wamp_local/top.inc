<?php 

// on force l'affichage des erreurs (mais on ignore les avertissements)
// a supprimer lors de la mise en production du site
ini_set('display_errors',1);
error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(E_ALL);


// Mis à jour par Olivier ROUX, 29-07-2008
header("Content-Type: text/html; charset=iso-8859-15");
// démarrage de la session
session_start();


// Ajout YL 25-08-2008 compatibilité php
//$home="";
//$edition="";
//$portage="";
//$contact="";
//$journal="";



include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
// variables de configuration de l'application
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';

// fonctions generiques
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/session/login_forms.php';

// on stocke l'URL courante dans le tableau des variables superglobales sous la forme $_SERVER['FULL_URL']
storeUrl();


echo('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');


?>