<?php
session_start();

// script appelé via Ajax par la fonction javascript sendRecordToDelete() et qui effectue la suppression d'un enregistrement d'une table et retourne un message de confirmation ou d'erreur
// 
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions_generic.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_SQL.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions_ppeao.php';
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

$theMessage.='<div align="center"><h2 id="delete_title">supprimer l&#x27;enregistrement &quot;'.$label.'&quot; ('.$key.'="'.$record.'")</h2><br /></div>';

	// on prépare le calcul du nombre total d'enregistrements supprimés
	// d'abord on calcule le nombre total d'enregistrements dans les tables utilisateur
	/*// on fait un VACUUM ANALYZE de la base pour être sûr que le compte est correct
	$vacuumSql='VACUUM ANALYZE';
	$vacuumResult=pg_query($connectPPEAO,$vacuumSql);
	pg_free_result($vacuumResult);*/
	$beforeSql='select sum(n_live_tup) from pg_catalog.pg_stat_user_tables where relname NOT LIKE \'admin_log\'';
	$beforeResult=pg_query($connectPPEAO,$beforeSql);
	$beforeArray=pg_fetch_all($beforeResult);
	$before=$beforeArray[0]["sum"];
	pg_free_result($beforeResult);
	
	$deleteSql='DELETE FROM '.$table.' WHERE '.$key.'=\''.$record.'\';';
	// si la suppression a bien eu lieu
	if($deleteResult=pg_query($connectPPEAO,$deleteSql)) {$success='yes';} else {$success='no';}
	pg_free_result($deleteResult);
	
	if ($success=='yes') {
	// on renvoie un message positif
	$theMessage.='<p>enregistrement &quot;'.$label.'&quot; ('.$key.'=&quot;'.$record.'&quot;) supprim&eacute; de la table "'.$table.'".</p><br />';
	// et on fait un VACUUM ANALYZE de la base
	$vacuumSql='VACUUM ANALYZE';
	$vacuumResult=pg_query($connectPPEAO,$vacuumSql);
	pg_free_result($vacuumResult);
	
	// maintenant on recalcule le nombre total d'enregistrements dans les tables utilisateur
	$afterSql='select sum(n_live_tup) from pg_catalog.pg_stat_user_tables where relname NOT LIKE \'admin_log\'';
	$afterResult=pg_query($connectPPEAO,$afterSql);
	$afterArray=pg_fetch_all($afterResult);
	$after=$afterArray[0]["sum"];
	pg_free_result($afterResult);
	
	// on a donc supprimé $before-$after enregistrements
	$deletedRows=$before-$after-1;
	
	// on le signale si le résultat n'est pas nul ou negatif (bug...)
	if ($deletedRows>=0) {
	$theMessage.='<br /><p>'.$deletedRows.' enregistrement(s) d&eacute;pendant(s) supprim&eacute;(s) au total.</p>';}
	
	// on inscrit la suppression dans le journal
	logWriteTo(1,'notice','enregistrement &quot;'.$label.'&quot; ('.$key.'=&quot;'.$record.'&quot;) supprim&eacute; de la table "'.$table.'" et '.$deletedRows.' enregistrement(s) d&eacute;pendant(s) supprim&eacute;(s) au total.',$deleteSql,'',0);
	}
	
	else {
		$theMessage.= '<p>Une erreur est survenue lors de la suppression de l\'enregistrement "'.$record.'" de la table "'.$table.'" :</p><p> '.pg_last_error().'</p>';
	}

echo($theMessage);


?>