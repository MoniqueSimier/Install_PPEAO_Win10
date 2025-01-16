<?php
session_start();

// script appelé via Ajax par la fonction javascript modalDialogDeleteRecord() qui affiche un dialogue permettant de supprimer un enregistrement dasn une table
// 
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';



global $tablesDefinitions;

// la table concernée
$table=$_GET["table"];
// l'enregistrement concerné
$record=$_GET["record"];
$level=$_GET["level"];

// on récupère le label de l'enregistrement concerné
$primaryKey=getTablePrimaryKey($connectPPEAO,$table);
$key=$primaryKey["column"];
$labelColumn=getDictionaryTableEntry($connectPPEAO,'noms_col',$table);
$labelSql='SELECT '.$labelColumn.' FROM '.$table.' WHERE '.$key.'=\''.$record.'\'';
$labelResult=pg_query($connectPPEAO,$labelSql);
$labelArray=pg_fetch_all($labelResult);
$label=$labelArray[0][$labelColumn];
pg_free_result($labelResult);

// on détermine les enregistrements affectés par la suppression de l'enregistrement (on se limite au premier niveau de cascade)
$affectedTables=countPrimaryKeyReferencedRows($connectPPEAO, $table,$key, $record);

$theMessage.='<div align="center"><h2 id="delete_title">supprimer l&#x27;enregistrement &quot;'.$label.'&quot; ('.$key.'=&quot;'.$record.'&quot;)</h2><br /></div>';

// si on ne trouve aucun enregistrement affecté
if (my_empty($affectedTables)) {
	$theMessage.="<p>Cet enregistrement n&#x27;est pas utilis&eacute; comme cl&eacute; &eacute;trang&egrave;re par d&#x27;autres tables, le supprimer n&#x27;entra&icirc;nera pas de suppression en cascade.</p>";
}

// si on trouve au moins un enregistrement affecté
else {
	$theMessage.="<p>Cet enregistrement est utilis&eacute; comme cl&eacute; &eacute;trang&egrave;re par d&#x27;autres tables, le supprimer entra&icirc;nera la suppression d&#x27;autres enregistrements&nbsp;:</p>";
	$theMessage.='<ul>';
	foreach($affectedTables as $key=>$value) {
		$theMessage.='<li>';
		$theMessage.='table&nbsp; "'.$key.'" : '.count($value).' enregistrements';
		$theMessage.='</li>';
	}
	$theMessage.='</ul>';

$theMessage.='<span class="error">note</span> La suppression des enregistrements des tables mentionn&eacute;es ci-dessus pourra entra&icirc;ner &agrave; son tour la suppression d&#x27;autres enregistrements : cela peut prendre un temps assez long si le nombre d&#x27;enregistrements &agrave; supprimer est important &ndash; ne pas fermer cette fen&ecirc;tre avant la fin.';
}

echo($theMessage);
?>