<?php
session_start();

// script appelé par la fonction javascript showNewLevel
// 

include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';


global $tablesDefinitions;

$editTable=$_GET["editTable"];
$targetTable=$_GET["targetTable"];
$thisLevel=$_GET["level"];

$tablesList=explode(",",$tablesDefinitions[$targetTable]["selector_cascade"]);

$thisTable=$tablesList[$thisLevel-1];
$parentTable=$tablesList[$thisLevel-2];
$selectedValues='';


// on construit la clause SQL pour filtrer les valeurs de la nouvelle table en fonction de celles de la table précédente
$theList='\'';
$theList.=implode($_GET[$parentTable],"','");
$theList.='\'';
$whereClause=' AND '.$tablesDefinitions[$parentTable]["table"].'_id IN ('.$theList.') ';

// on génère le SELECT
echo(iconv('ISO-8859-15','UTF-8',createTableSelect($thisTable,$selectedValues,$thisLevel,$whereClause)));

?>