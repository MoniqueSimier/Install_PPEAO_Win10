<?php
session_start();
// script appelé via Ajax par la fonction javascript makeEditable() et permettant de rendre un champ éditable
// 
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';



global $tablesDefinitions;


// la table concernée
$editTable=$_GET["editTable"];
// la colonne concernée
$editColumn=$_GET["editColumn"];
// l'enregistrement concerné (son ID)
$editRecord=$_GET["editRecord"];

// l'action à effectuer 
$editAction=$_GET["editAction"];

// on récupère la valeur du champ dans la base de données pour éviter les problèmes d'encodage...
$sql='	SELECT '.$editColumn.' FROM '.$tablesDefinitions[$editTable]["table"].' 
		WHERE '.$tablesDefinitions[$editTable]["id_col"].'=\''.$editRecord.'\' ';
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$values=pg_fetch_all($result);
pg_free_result($result);
$editValue=htmlspecialchars($values[0][$editColumn]);





// on compile les informations sur les colonnes de la table $editTable
$cDetails=getTableColumnsDetails($connectPPEAO,$tablesDefinitions[$editTable]["table"]);


// on encode la valeur "editValue" pour les caracteres spéciaux
//$editValue=htmlentities($editValue);

// selon l'action requise, on réagit différemment

switch ($editAction) {
	// on veut rendre le champ editable
	case 'edit':
	// le DIV contenant la zone d'édition
	$theField.='<div id="edit_'.$editColumn.'_'.$editRecord.'" name="edit_'.$editColumn.'_'.$editRecord.'" class="edit_field_container small">';
	
	
	// on ajoute le champ éditable
	//$theField.=iconv('ISO-8859-15','UTF-8',makeField($cDetails,$editTable,$editColumn,$editValue,'edit='.$editRecord,''));
	$theField.=makeField($cDetails,$editTable,$editColumn,$editValue,'edit='.$editRecord,'','','');
	

	// on ajoute les boutons "OK/ANNULER"
	$theField.='<div id="edit_buttons_'.$editColumn.'_'.$editRecord.'" name="edit_buttons_'.$editColumn.'_'.$editRecord.'" class="small edit_buttons">';
		$theField.='<a href="javascript:saveChange(\''.$editTable.'\',\''.$editColumn.'\',\''.$editRecord.'\',\'save\');"" class="edit_button" title="enregistrer la modification">enregistrer</a>';
		$theField.='<a href="javascript:makeEditable(\''.$editTable.'\',\''.$editColumn.'\',\''.$editRecord.'\',\'cancel\');" class="edit_button" title="annuler la modification">annuler</a>';

	$theField.='</div>';

	break;
	
	// on veut annuler les modifications faites au champ
	case 'cancel':
		//$theField=makeField($cDetails,$editTable,$editColumn,stripSlashes($editValue),'display='.$editRecord,'');
		$theField=makeField($cDetails,$editTable,$editColumn,stripSlashes($editValue),'display='.$editRecord,'','');
	break;


} // end switch $action


header("Content-Type: text/html; charset=utf-8", true);
//echo($theField);
echo(iconv('ISO-8859-15','UTF-8',$theField));


?>