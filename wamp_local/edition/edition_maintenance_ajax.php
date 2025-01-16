<?php

// script appele via Ajax par la fonction javascript doMaintenance() et qui permet de realiser des operations de maintenance sur la base de donnees (page edition_maintenance.php)

// parametres de connexion a la base de donnees
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';
// on recupere l'action de maintenance a realiser
$action=$_GET["action"];
// on suppose que l'action a ete realisee avec succes
global $erreurSQL;
$success=true;
$operation="";
$erreurSQL="";
// Liste des tables de la BD // Ajout YL 07/02/2011
$listeTablesRef="ref_pays,ref_systeme,ref_secteur,ref_categorie_ecologique,ref_categorie_trophique,ref_ordre,ref_famille,ref_espece,ref_origine_kb,art_categorie_socio_professionnelle,art_etat_ciel";
$listeTablesParamExp = "exp_contenu,exp_debris,exp_engin,exp_force_courant,exp_position,exp_qualite,exp_remplissage,exp_sediment,exp_sens_courant,exp_sexe,exp_stade,exp_vegetation,exp_station";
$listeTablesParamArt = "art_grand_type_engin,art_millieu,art_type_activite,art_type_agglomeration,art_type_sortie,art_type_engin,art_vent,art_agglomeration, art_param_type_effort";
$listeTablesDonneesExp="exp_environnement,exp_campagne,exp_coup_peche,exp_fraction,exp_biologie,exp_trophique";
$listeTablesDonneesArt="art_unite_peche,art_lieu_de_peche,art_debarquement,art_debarquement_rec,art_engin_peche,art_fraction,art_poisson_mesure,art_activite,art_engin_activite,art_fraction_rec,art_periode_enquete";
$listeTablesDonneesStat="art_stat_totale,art_stat_gt,art_stat_gt_sp,art_stat_sp,art_taille_gt_sp,art_taille_sp,art_stat_effort";
// Liste des tables pour les bases postgre au format access
$sql="";
$infoComp="";
switch ($action) {
	case 'sequences_ref_param':
		$operation="de mise &agrave; jour des s&eacute;quences des tables de r&eacute;f&eacute;rence et de param&eacute;trage";

		// on sélectionne les sequences, leurs tables et leurs colonnes
		// pour les tables de ref (type_table_id=2) et de param (type_table_id=3)
		$sql='	SELECT ads.sequence_name, ads.column_name, ads.table_db
				FROM admin_sequences ads, admin_dictionary_tables addt 
				WHERE (ads.table_db=addt.table_db) AND (addt.type_table_id=2 OR addt.type_table_id=3)';
		$result=pg_query($connectPPEAO,$sql) or  die('erreur dans la requete : '.$sql. pg_last_error());;
		$seqArray=pg_fetch_all($result);
		
		// on boucle sur chaque sequence `
		foreach ($seqArray as $seq) {
			// on recupere la plus grande valeur de la colonne correspondant a la sequence
			$sqlMax='	SELECT max('.$seq["column_name"].') as maxval
						FROM '.$seq["table_db"].'
						';
			$resultMax=pg_query($connectPPEAO,$sqlMax);
			$maxArray=pg_fetch_row($resultMax);
			$maxVal=$maxArray[0];
			if ($maxVal<>"") {
			// on met a jour la valeur maximale de la sequence concernee
			$sqlUpdate='SELECT pg_catalog.setval(\''.$seq["sequence_name"].'\','.$maxVal.',true);';
			if ($resultUpdate=pg_query($connectPPEAO,$sqlUpdate)) {$ok=true;} else {$success=false;}
			}
			
		}
		
		
	break;
	case 'sequences_donnees':
		$operation="de mise &agrave; jour des s&eacute;quences des tables de donn&eacute;es";
		// on sélectionne les sequences, leurs tables et leurs colonnes
		// pour les tables de donnees (type_table_id=4)
		$sql='	SELECT ads.sequence_name, ads.column_name, ads.table_db 
				FROM admin_sequences ads, admin_dictionary_tables addt 
				WHERE (ads.table_db=addt.table_db) AND (addt.type_table_id=4)';
		$result=pg_query($connectPPEAO,$sql);
		$seqArray=pg_fetch_all($result);
		
		// on boucle sur chaque sequence `
		foreach ($seqArray as $seq) {
			// on recupere la plus grande valeur de la colonne correspondant a la sequence
			$sqlMax='	SELECT max('.$seq["column_name"].') as maxval
						FROM '.$seq["table_db"].'
						';
			$resultMax=pg_query($connectPPEAO,$sqlMax);
			$maxArray=pg_fetch_row($resultMax);
			$maxVal=$maxArray[0];
			if ($maxVal<>"") {
			// on met a jour la valeur maximale de la sequence concernee
			$sqlUpdate='SELECT pg_catalog.setval(\''.$seq["sequence_name"].'\','.$maxVal.',true);';
			if ($resultUpdate=pg_query($connectPPEAO,$sqlUpdate)) {$ok=true;} else {$success=false;}
			}
		}

	break;
	case 'vacuum':
		$sql='VACUUM ANALYZE';
		if ($result=pg_query($connectPPEAO,$sql)) {$success=true;} else {$success=false;}
		$operation="&quot;VACUUM ANALYSE&quot;";
	break;
	case 'reindex':
		$sql='REINDEX DATABASE '.$base_principale.'';
		if ($result=pg_query($connectPPEAO,$sql)) {$success=true;} else {$success=false;}
		$operation="de r&eacute;indexation de la base";
	break;
	case 'disable_trigger':
		$operation=" de suppression des contraintes";
		$connectBDPECHE =pg_connect ("host=".$host." dbname=".$base_portage." user=".$user." password=".$passwd);
		if ($connectBDPECHE) {$success=true;} else {$success=false;$erreurSQL ="(erreur = erreur connexion bdpeche)";}
		if ($success) {
			$sql="ALTER TABLE ref_categorie_ecologique DISABLE TRIGGER ALL;
				ALTER TABLE ref_categorie_trophique DISABLE TRIGGER ALL;
				ALTER TABLE ref_espece DISABLE TRIGGER ALL;
				ALTER TABLE ref_famille DISABLE TRIGGER ALL;
				ALTER TABLE ref_ordre DISABLE TRIGGER ALL;
				ALTER TABLE ref_origine_kb DISABLE TRIGGER ALL;
				ALTER TABLE ref_pays DISABLE TRIGGER ALL;
				ALTER TABLE ref_secteur DISABLE TRIGGER ALL;
				ALTER TABLE ref_systeme DISABLE TRIGGER ALL;
				ALTER TABLE exp_contenu DISABLE TRIGGER ALL;
				ALTER TABLE exp_debris DISABLE TRIGGER ALL;
				ALTER TABLE exp_engin DISABLE TRIGGER ALL;
				ALTER TABLE exp_force_courant DISABLE TRIGGER ALL;
				ALTER TABLE exp_position DISABLE TRIGGER ALL;
				ALTER TABLE exp_qualite DISABLE TRIGGER ALL;
				ALTER TABLE exp_remplissage DISABLE TRIGGER ALL;
				ALTER TABLE exp_sediment DISABLE TRIGGER ALL;
				ALTER TABLE exp_sens_courant DISABLE TRIGGER ALL;
				ALTER TABLE exp_sexe DISABLE TRIGGER ALL;
				ALTER TABLE exp_stade DISABLE TRIGGER ALL;
				ALTER TABLE exp_station DISABLE TRIGGER ALL;
				ALTER TABLE exp_vegetation DISABLE TRIGGER ALL;
				ALTER TABLE exp_contenu DISABLE TRIGGER ALL;
				ALTER TABLE exp_debris DISABLE TRIGGER ALL;
				ALTER TABLE exp_engin DISABLE TRIGGER ALL;
				ALTER TABLE exp_force_courant DISABLE TRIGGER ALL;
				ALTER TABLE exp_position DISABLE TRIGGER ALL;
				ALTER TABLE exp_qualite DISABLE TRIGGER ALL;
				ALTER TABLE exp_remplissage DISABLE TRIGGER ALL;
				ALTER TABLE exp_sediment DISABLE TRIGGER ALL;
				ALTER TABLE exp_sens_courant DISABLE TRIGGER ALL;
				ALTER TABLE exp_sexe DISABLE TRIGGER ALL;
				ALTER TABLE exp_stade DISABLE TRIGGER ALL;
				ALTER TABLE exp_station DISABLE TRIGGER ALL;
				ALTER TABLE exp_vegetation DISABLE TRIGGER ALL;
				ALTER TABLE art_agglomeration DISABLE TRIGGER ALL;
				ALTER TABLE art_categorie_socio_professionnelle DISABLE TRIGGER ALL;
				ALTER TABLE art_etat_ciel DISABLE TRIGGER ALL;
				ALTER TABLE art_grand_type_engin DISABLE TRIGGER ALL;
				ALTER TABLE art_millieu DISABLE TRIGGER ALL;
				ALTER TABLE art_type_activite DISABLE TRIGGER ALL;
				ALTER TABLE art_type_agglomeration DISABLE TRIGGER ALL;
				ALTER TABLE art_type_engin DISABLE TRIGGER ALL;
				ALTER TABLE art_type_sortie DISABLE TRIGGER ALL;
				ALTER TABLE art_vent DISABLE TRIGGER ALL;
				ALTER TABLE exp_biologie DISABLE TRIGGER ALL;
				ALTER TABLE exp_campagne DISABLE TRIGGER ALL;
				ALTER TABLE exp_coup_peche DISABLE TRIGGER ALL;
				ALTER TABLE exp_environnement DISABLE TRIGGER ALL;
				ALTER TABLE exp_fraction DISABLE TRIGGER ALL;
				ALTER TABLE exp_trophique DISABLE TRIGGER ALL;
				ALTER TABLE art_activite DISABLE TRIGGER ALL;
				ALTER TABLE art_debarquement DISABLE TRIGGER ALL;
				ALTER TABLE art_engin_activite DISABLE TRIGGER ALL;
				ALTER TABLE art_engin_peche DISABLE TRIGGER ALL;
				ALTER TABLE art_fraction DISABLE TRIGGER ALL;
				ALTER TABLE art_lieu_de_peche DISABLE TRIGGER ALL;
				ALTER TABLE art_poisson_mesure DISABLE TRIGGER ALL;
				ALTER TABLE art_unite_peche DISABLE TRIGGER ALL;
				ALTER TABLE art_debarquement_rec DISABLE TRIGGER ALL;
				ALTER TABLE art_fraction_rec DISABLE TRIGGER ALL;
				ALTER TABLE art_stat_gt DISABLE TRIGGER ALL;
				ALTER TABLE art_stat_gt_sp DISABLE TRIGGER ALL;
				ALTER TABLE art_stat_sp DISABLE TRIGGER ALL;
				ALTER TABLE art_stat_totale DISABLE TRIGGER ALL;
				ALTER TABLE art_taille_gt_sp DISABLE TRIGGER ALL;
				ALTER TABLE art_taille_sp DISABLE TRIGGER ALL;";
			$result = pg_query($connectBDPECHE,$sql);
			$erreurSQL = pg_last_error($connectBDPECHE);
			$erreurSQL = "(<b>erreur </b>= ".$erreurSQL.")";
			if (!$result) {
				$success=false;
			} else {
				$success=true;
			}
		}// fin du if ($success)
	break;
	case 'enable_trigger':
			$operation=" d''activation des contraintes";
			$connectBDPECHE =pg_connect ("host=".$host." dbname=".$base_portage." user=".$user." password=".$passwd);
			if ($connectBDPECHE) {$success=true;} else {$success=false;$erreurSQL ="(erreur = erreur connexion bdpeche)";}
			if ($success) {
				$sql="
					ALTER TABLE ref_categorie_ecologique ENABLE  TRIGGER ALL;
					ALTER TABLE ref_categorie_trophique ENABLE  TRIGGER ALL;
					ALTER TABLE ref_espece ENABLE  TRIGGER ALL;
					ALTER TABLE ref_famille ENABLE  TRIGGER ALL;
					ALTER TABLE ref_ordre ENABLE  TRIGGER ALL;
					ALTER TABLE ref_origine_kb ENABLE  TRIGGER ALL;
					ALTER TABLE ref_pays ENABLE  TRIGGER ALL;
					ALTER TABLE ref_secteur ENABLE  TRIGGER ALL;
					ALTER TABLE ref_systeme ENABLE  TRIGGER ALL;
					ALTER TABLE exp_contenu ENABLE  TRIGGER ALL;
					ALTER TABLE exp_debris ENABLE  TRIGGER ALL;
					ALTER TABLE exp_engin ENABLE  TRIGGER ALL;
					ALTER TABLE exp_force_courant ENABLE  TRIGGER ALL;
					ALTER TABLE exp_position ENABLE  TRIGGER ALL;
					ALTER TABLE exp_qualite ENABLE  TRIGGER ALL;
					ALTER TABLE exp_remplissage ENABLE  TRIGGER ALL;
					ALTER TABLE exp_sediment ENABLE  TRIGGER ALL;
					ALTER TABLE exp_sens_courant ENABLE  TRIGGER ALL;
					ALTER TABLE exp_sexe ENABLE  TRIGGER ALL;
					ALTER TABLE exp_stade ENABLE  TRIGGER ALL;
					ALTER TABLE exp_station ENABLE  TRIGGER ALL;
					ALTER TABLE exp_vegetation ENABLE  TRIGGER ALL;
					ALTER TABLE exp_contenu ENABLE  TRIGGER ALL;
					ALTER TABLE exp_debris ENABLE  TRIGGER ALL;
					ALTER TABLE exp_engin ENABLE  TRIGGER ALL;
					ALTER TABLE exp_force_courant ENABLE  TRIGGER ALL;
					ALTER TABLE exp_position ENABLE  TRIGGER ALL;
					ALTER TABLE exp_qualite ENABLE  TRIGGER ALL;
					ALTER TABLE exp_remplissage ENABLE  TRIGGER ALL;
					ALTER TABLE exp_sediment ENABLE  TRIGGER ALL;
					ALTER TABLE exp_sens_courant ENABLE  TRIGGER ALL;
					ALTER TABLE exp_sexe ENABLE  TRIGGER ALL;
					ALTER TABLE exp_stade ENABLE  TRIGGER ALL;
					ALTER TABLE exp_station ENABLE  TRIGGER ALL;
					ALTER TABLE exp_vegetation ENABLE  TRIGGER ALL;
					ALTER TABLE art_agglomeration ENABLE  TRIGGER ALL;
					ALTER TABLE art_categorie_socio_professionnelle ENABLE  TRIGGER ALL;
					ALTER TABLE art_etat_ciel ENABLE  TRIGGER ALL;
					ALTER TABLE art_grand_type_engin ENABLE  TRIGGER ALL;
					ALTER TABLE art_millieu ENABLE  TRIGGER ALL;
					ALTER TABLE art_type_activite ENABLE  TRIGGER ALL;
					ALTER TABLE art_type_agglomeration ENABLE  TRIGGER ALL;
					ALTER TABLE art_type_engin ENABLE  TRIGGER ALL;
					ALTER TABLE art_type_sortie ENABLE  TRIGGER ALL;
					ALTER TABLE art_vent ENABLE  TRIGGER ALL;
					ALTER TABLE exp_biologie ENABLE  TRIGGER ALL;
					ALTER TABLE exp_campagne ENABLE  TRIGGER ALL;
					ALTER TABLE exp_coup_peche ENABLE  TRIGGER ALL;
					ALTER TABLE exp_environnement ENABLE  TRIGGER ALL;
					ALTER TABLE exp_fraction ENABLE  TRIGGER ALL;
					ALTER TABLE exp_trophique ENABLE  TRIGGER ALL;
					ALTER TABLE art_activite ENABLE  TRIGGER ALL;
					ALTER TABLE art_debarquement ENABLE  TRIGGER ALL;
					ALTER TABLE art_engin_activite ENABLE  TRIGGER ALL;
					ALTER TABLE art_engin_peche ENABLE  TRIGGER ALL;
					ALTER TABLE art_fraction ENABLE  TRIGGER ALL;
					ALTER TABLE art_lieu_de_peche ENABLE  TRIGGER ALL;
					ALTER TABLE art_poisson_mesure ENABLE  TRIGGER ALL;
					ALTER TABLE art_unite_peche ENABLE  TRIGGER ALL;
					ALTER TABLE art_debarquement_rec ENABLE  TRIGGER ALL;
					ALTER TABLE art_fraction_rec ENABLE  TRIGGER ALL;
					ALTER TABLE art_stat_gt ENABLE  TRIGGER ALL;
					ALTER TABLE art_stat_gt_sp ENABLE  TRIGGER ALL;
					ALTER TABLE art_stat_sp ENABLE  TRIGGER ALL;
					ALTER TABLE art_stat_totale ENABLE  TRIGGER ALL;
					ALTER TABLE art_taille_gt_sp ENABLE  TRIGGER ALL;
					ALTER TABLE art_taille_sp ENABLE  TRIGGER ALL;";
				$result = pg_query($connectBDPECHE,$sql);
				$erreurSQL = pg_last_error($connectBDPECHE);
				$erreurSQL = "(<b>erreur </b> = ".$erreurSQL.")";
				if (!$result) {
					$success=false;
				} else {
					$success=true;
				}
			}
		break;
	case 'empty_bdpeche':
		$operation=" de vidage de bdpeche";

		$connectBDPECHE =pg_connect ("host=".$host." dbname=".$base_portage." user=".$user." password=".$passwd);
		if ($connectBDPECHE) {$success=true;} else {$success=false;$erreurSQL ="(erreur = erreur connexion bdpeche)";}
		if ($success) {
			$listeTables= $listeTablesRef.",".$listeTablesParamExp.",".$listeTablesParamArt.",".$listeTablesDonneesExp.",".$listeTablesDonneesArt.",".$listeTablesDonneesStat;
			$TableEnCours = explode(",",$listeTables);
			$nbrTable = count($TableEnCours)-1;
			for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
				$sql.="ALTER TABLE ".$TableEnCours[$cptTable]." DISABLE TRIGGER ALL;";
			}
			for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
				$sql.="DELETE  FROM ".$TableEnCours[$cptTable]." ;";
			}
			//echo $sql;
			$result = pg_query($connectBDPECHE,$sql);
			$erreurSQL = pg_last_error($connectBDPECHE);
			$erreurSQL = "(<b>erreur </b>= ".$erreurSQL.")";
			if (!$result) {
				$success=false;
			} else {
				$success=true;
			}
		}// fin du if ($success)
	break;
	case 'empty_ACCESS':
		$PathFicConfAccess = $_SERVER["DOCUMENT_ROOT"]."/conf/exportACCESS.txt";
		include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
		$operation=" de vidage des bases ACCESS exp2003_bdd, pechart et pays.";
		$success = true;
		// On recupere les noms des tables a vider.
		// exp2003_bdd
		$go = true;
		if ($go) {
			//echo "vidage de exp2003_bdd<br/>";
			$connectbaseavider = pg_connect ("host=".$host." dbname=exp2003_bdd user=".$user." password=".$passwd);
			$listeTables = GetParam("listePostAccessExp2003_bdd",$PathFicConfAccess);
			$success = viderBase($listeTables,$connectbaseavider);
			if ($success) {
				$infoComp .=" exp2003_bdd vid&eacute;e avec succ&egrave;s<br/>";
			}
		}
		//pechart
		if ($success) {
			//echo "vidage de pechart<br/>";
			$connectbaseavider = pg_connect ("host=".$host." dbname=pechart user=".$user." password=".$passwd);
			$listeTables = GetParam("listePostAccessPechart",$PathFicConfAccess);
			//echo $listeTables."<br/>";
			$success = viderBase($listeTables,$connectbaseavider);
			if ($success) {
				$infoComp .=" pechart vid&eacute;e avec succ&egrave;s<br/>";
			}
			//echo $sql."<br/>";
		}
		// Base pays
		// pour l'instant on teste avec togo
		if ($success) {
			//echo "vidage de pechart<br/>";
			$connectbaseavider = pg_connect ("host=".$host." dbname=togo user=".$user." password=".$passwd);
			$listeTables = GetParam("listePostAccessPays",$PathFicConfAccess);
			//echo $listeTables."<br/>";
			$success = viderBase($listeTables,$connectbaseavider);
			if ($success) {
				$infoComp .=" togo vid&eacute;e avec succ&egrave;s<br/>";
			}
			//echo $sql."<br/>";
		}		
		break;
	default: 
		break;
	
}


// le début du message de fin de traitement
$message='<h5>maintenance de la base</h5>';

// on indique si l'operation a eu lieu avec succes ou pas
if ($success) {
	$message.="<p style='font-size:10px'>l'op&eacute;ration ".$operation." a &eacute;t&eacute; r&eacute;alis&eacute;e avec succ&egrave;s.".$infoComp."</p>";
}
	else {
		$message.="<p>une erreur est survenue lors de l'op&eacute;ration de ".$operation." ".$erreurSQL.".</p>";

	}
echo($message);

?>