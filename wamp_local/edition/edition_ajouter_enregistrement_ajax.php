<?php
session_start();

// script affichant le formulaire d'ajout d'un nouvel enregistrement dans le module d'edition des tables
// appele via Ajax par la fonction JS  modalDialogAddRecord() (script JS edition.js) 
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';

global $tablesDefinitions;


// la table concernée
$editTable=$_GET["editTable"];
$level=$_GET["level"];


// on compile les informations sur les colonnes de la table $editTable
$cDetails=getTableColumnsDetails($connectPPEAO,$tablesDefinitions[$editTable]["table"]);
// la liste des colonnes concernées
$theHeads=array_keys($cDetails);


$theForm='<h2>Ajouter un nouvel enregistrement dans la table "'.iconv('ISO-8859-15','UTF-8',$tablesDefinitions[$editTable]["label"]).'"</h2>';

// on insère le formulaire d'ajout d'un nouvel enregistrement...
$theForm.='<form id="add_record_'.$level.'_form">';
$theForm.='<ul id="add_record_'.$level.'_ul">';
	$tabIndex=0;
	foreach ($theHeads as $oneHead) {
		//cas particulier de la table art_stat_effort
		// on doit saisir un ref_systeme_if OU un ref_secteur_id
		if ($editTable=='stat_effort' && $oneHead=='ref_systeme_id') {$theForm.='<li class="error small" style="list-style-type: none;">Veuillez saisir un syst&egrave;me OU un secteur.</li>';}
		$theForm.='<li class="small">';
		$theForm.='<p>'.$oneHead;
		if ($cDetails[$oneHead]["is_nullable"]=='NO') {$theForm.=' (<span class="error">obligatoire</span>)';}
		$theForm.='</p>';
		$theForm.=iconv('ISO-8859-15','UTF-8',makeField($cDetails,$editTable,$oneHead,'','add','',$tabIndex));
		$theForm.='</li>';
	$tabIndex++;
	}
$theForm.='</ul>';
$theForm.='</form>';




echo($theForm);
?>