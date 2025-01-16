<?php 
//*****************************************
// SQLDonneesPeche.php
//*****************************************
// Created by Yann Laurent
// 2008-10-30 : creation
//*****************************************
// Lancement des scripts SQL créés et sauvegardés dans le traitement précédement
// voir majDonneesPeches.php


//*****************************************
// Note : certains message a l'ecran sont laisses, le traitement etant long, ca permet d'avoir une 
//idee du traitement qui est en cours.

$cpt=0;
$tempType = "";
$nomAction = "Exec. SQL pour ".$nomAction;
$pasDeSQL = true;
$affichageDetail = false; // Pour afficher ou non le detail des traitements à l'écran

//****************************************************
// Traitement
// Etape 1 : générer les correspondances d'ID pour toutes les tables à importer
// On ne se pose pas la question de savoir si la table existe ou non.. On le testera apres
//****************************************************

if ($affichageDetail){
	echo "<b>Etape 3</b> : execution des SQL <br/>";
}
$CRexecution .="<b>Execution des SQL</b> <br/>";
// On récupère toutes les tables à mettre à jour
if (!$finmajDP) { // on ne lance pas le timer si on sort directement de majDonneesPeches.sql
	$start_while=timer(); // début du chronométrage du for
}

// Liste des tables a traiter
switch($typeAction){
	case "majsc":
		$listTableMajID = GetParam("listeTableMajsc",$PathFicConf);
		break;
	case "majrec" :
		// on rajoute la table art_periode_enquete qui est mise a jour uniquement lors du portage
		$listTableMajID = "art_periode_enquete,".GetParam("listeTableMajrec",$PathFicConf);
		break;
}
// pour TEST
//$listTableMajID ="exp_campagne";
//$listTableMajID ="exp_campagne,exp_coup_peche"; // test
//$listTableMajID ="exp_environnement,exp_coup_peche,exp_fraction,exp_biologie,exp_trophique";
//$listTableMajID ="art_unite_peche,art_lieu_de_peche,art_debarquement,art_fraction";
//$listTableMajID ="art_unite_peche";
//$listTableMajID ="art_stat_totale,art_stat_gt,art_stat_gt_sp,art_stat_sp,art_taille_gt_sp,art_taille_sp";
$NbrTableAlire = substr_count($listTableMajID,",");
if ($NbrTableAlire == 0) {
	$NbrTableAlire = 1;
} else {
	$NbrTableAlire += 1;
}
//echo "liste table SQL a exec =".$listTableMajID."<br/>";

$tableMajID = explode(",",$listTableMajID);
$nbtableMajID = count($tableMajID) - 1;

for ($cptID = 0; $cptID <= $nbtableMajID; $cptID++) {
	// controle de la table en cours si besoin (gestion TIMEOUT)
	if ((!$tableEnCours == "" && $tableEnCours == $tableMajID[$cptID]) || $tableEnCours == "") {
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Exec SQL lecture table :".$debugTimer."<br/>";
		}
		if ($affichageDetail){
			echo $cptID." / ".$nbtableMajID." Execution SQL table en cours = ".$tableMajID[$cptID]." <br/>";
		}
		if ($EcrireLogComp ) { WriteCompLog ($logComp,"Exec SQL pour ".$tableMajID[$cptID],$pasdefichier);}

		//Pour gestion timeout, la valeur de la table en cours
		$tableEnLecture = $tableMajID[$cptID];
		// Compteur 
		$compReadSqlC = " select count(nomtable) from temp_recomp_id where nomtable='".$tableMajID[$cptID]."'";
		$compReadResultC = pg_query(${$BDSource},$compReadSqlC) or die('erreur dans la requete : '.pg_last_error());
		$compRowC = pg_fetch_row($compReadResultC);
		$totalLignes = $compRowC[0];
		pg_free_result($compReadResultC);
		// Reinitialisation des compteurs
		$cptChampTotal = 0;
		$cptSQLErreur = 0 ;
		$cptAjoutMaj = 0;
		if ($tableEnCours == "") {
			$cptTableTotal++;
			$_SESSION['s_cpt_champ_total'] 	= 0;
			$_SESSION['s_en_erreur'] 		= false;
			$_SESSION['s_cpt_erreurs_sql'] 	= 0;
			$_SESSION['s_cpt_maj'] 	= 0; 
		} else {
			// on reinitialise les valeurs avec les variables de session mise à jour lors du traitement précédent
			$CRexecution 	= $_SESSION['s_CR_processAuto'];
			$cptChampTotal 	= $_SESSION['s_cpt_champ_total'];
			$cptAjoutMaj = $_SESSION['s_cpt_maj'];
			// On reinitialise pour eviter de compter deux fois les memes donnees
			$_SESSION['s_CR_processAuto'] 	= "";
			$_SESSION['s_cpt_champ_total'] 	= 0;
			$_SESSION['s_cpt_erreurs_sql'] 	= 0; 
			$_SESSION['s_cpt_maj'] 	= 0; 
		}
		// Gestion des problemes d'ID non numerique
		$ListeTableIdpasRang0 = "art_type_activite";
		$ListeTablepasRang0ID = "3";
		$testTtypeID = strpos($ListeTableIdpasRang0 ,$tableMajID[$cptID]);
		if ($testTtypeID === false) {
			$RangId = 0; 
		} else {
			$RangId = 2; /// pour l'instant qu'une table, on code un peu a la hussarde...
		}

		// Gestion TIMEOUT : on reprend la ou on s'etait arrete
		// Comme on trie par ID, on ne va pas en perdre en route
		$testTtypeIDChar = strpos($ListeTableIDPasNum ,$tableMajID[$cptID]);
		if ($tableEnCours == "") {
			$condWhere = " where nomtable='".$tableMajID[$cptID]."'";
		} else {
			if ($testTtypeIDChar === false) {
				// L'ID est bien un numérique
				$condWhere = " where nomtable='".$tableMajID[$cptID]."' and Idsource > ".$IDEnCours;
			} else {
				// L'ID est une chaine
				$condWhere = " where nomtable='".$tableMajID[$cptID]."' and IdsourceChar > ".$IDEnCours;
			}	
		}
		// La condition de tri doit aussi être évaluée.
		if ($testTtypeIDChar === false) {
			// L'ID est bien un numérique
			$condOrder = " order by Idsource ASC ";
		} else {
			// L'ID est une chaine
			$condOrder = " order by IdsourceChar ASC ";
		}

		// Lecture de la table qui contient les SQL / exécuti
		$envSrcSql = " select * from temp_recomp_id ".$condWhere. " ".$condOrder; 
		//echo $envSrcSql."<br/>";
		$envSrcResult = pg_query(${$BDSource},$envSrcSql) or die('erreur dans la requete : '.pg_last_error());
		if (pg_num_rows($envSrcResult) == 0) {
		// Message d'erreur
		} else {
			//echo "nb enreg ".$tableMajID[$cptID]." = ".pg_num_rows($envSrcResult)." where = ".$condWhere."<br/>";
			while ($envRow = pg_fetch_row($envSrcResult) ) {
				if ($debugAff==true) {
					$debugTimer = number_format(timer()-$start_while,4);
					echo "xxx Exec SQL debut trt enreg :".$debugTimer."<br/>";
				}
				// Gestion TIMEOUT
				$ourtime = (int)number_format(timer()-$start_while,7);
				$seuiltemps= ceil(0.9*$max_time);
				// On prend un peu de marge par rapport au temps max.
				if ($ourtime >= ceil(0.9*$max_time)) {
					$delai=number_format(timer() - $start_while,7);
					$ArretTimeOut =true;
					if ($affichageDetail){
						echo "timeout table en lecture = ".$tableEnLecture." id en lecture = ".$IDEnLecture."<br/>";
					}
					break;
				}
				if ($testTtypeIDChar === false) {
					// L'ID est bien un numérique
					$IDEnLecture = $envRow[1];
				} else {
					// L'ID est une chaine
					$IDEnLecture = $envRow[2];
				}	
						
				// Compteur 
				$cptChampTotal++;
	
				if ($debugAff==true) {
					$debugTimer = number_format(timer()-$start_while,4);
					echo "Exec SQL avant execution SQL :".$debugTimer."<br/>";
				}
				// On récupère le SQL
				//echo $envRow[7]."<br/>";
				$insertSQL = stripslashes($envRow[7]);
				// Execution du SQL
				$insertSQLResult = pg_query(${$BDCible},$insertSQL) ;
				$pasDeSQL = false;
				$errorSQL = pg_last_error(${$BDCible});
				if ($insertSQLResult) {
					$cptAjoutMaj ++;
					
				} else {
					if ($EcrireLogComp ) { 
					WriteCompLog ($logComp,"Erreur execution pour table = ".$envRow[0]."  script = ".$envRow[7]. " erreur complete = ".$errorSQL,$pasdefichier);}
	
					$cptSQLErreur ++;
					$_SESSION['s_erreur_process'] = true;
				}
				pg_free_result($insertSQLResult);
				if ($debugAff==true) {
					$debugTimer = number_format(timer()-$start_while,4);
					echo "xxx Exec SQL fin trt enreg :".$debugTimer."<br/>";
				}  
			}// End while ($envRow = pg_fetch_row($envSrcResult) )
			// On effectue ici tous les traitements liés à la fin de la lecture d'une table
			if ($ArretTimeOut) {
				break;
			}
			// TIMEOUT, reinitialisation des variables EnCours
			$IDEnCours = 0;
			$tableEnCours = "";
			
			// On gère le compte-rendu
			if (!$ArretTimeOut) {
				$CRexecution = $CRexecution." *-".$tableMajID[$cptID]." : ".$cptChampTotal." lus";
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp,"FIN ".$nomAction." pour ".$tableMajID[$cptID]." : ajout reel total=".$cptAjoutMaj,$pasdefichier);
				}
				$CRexecution = $CRexecution." (maj/ajout reel total=".$cptAjoutMaj.") -* <br/>" ;
			}
				
			pg_free_result($envSrcResult);
		}
	} //end ((!$tableEnCours == "" && $tableEnCours == $$tableMajID[$cptID]) ...
	
	
} // end for ($cptID = 0; $cptID <= $nbtableMajID; $cptID++)
if (!$ArretTimeOut) {
	// Dernier traitement après l'execution des SQL : 
	// Mise à jour des sequences.
	// Insertion du script d'olivier
	// on sélectionne les sequences, leurs tables et leurs colonnes
	
	$sql='	SELECT ads.sequence_name, ads.column_name, ads.table_db
				FROM admin_sequences ads, admin_dictionary_tables addt 
				WHERE (ads.table_db=addt.table_db) AND (addt.type_table_id=2 OR addt.type_table_id=3 OR addt.type_table_id=4)';
		$result=pg_query($connectPPEAO,$sql) or  die('erreur dans la requete : '.$sql. pg_last_error());;
		$seqArray=pg_fetch_all($result);
		// on boucle sur chaque sequence `
		foreach ($seqArray as $seq) {
			// on recupere la plus grande valeur de la colonne correspondant a la sequence
			$sqlMax='	SELECT max('.$seq["column_name"].') as maxval
						FROM '.$seq["table_db"].'
						';
			//debug 			echo($sqlMax);
			$resultMax=pg_query($connectPPEAO,$sqlMax);
			$maxArray=pg_fetch_row($resultMax);
			$maxVal=$maxArray[0];
			if ($maxVal<>"") {
				$success = true;
				// on met a jour la valeur maximale de la sequence concernee
				$sqlUpdate='SELECT pg_catalog.setval(\''.$seq["sequence_name"].'\','.$maxVal.',true);';
				if ($resultUpdate=pg_query($connectPPEAO,$sqlUpdate)) {$ok=true;} else {$success=false;}			
				if ($success) {
					$CRexecution = $CRexecution." mise &agrave; jour avec succ&egrave;s des s&eacute;quences des tables de donn&eacute;es <br/>";
				} else {
					$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp; erreur sur la mise &agrave; jour des s&eacute;quences des tables de donn&eacute;es <br/>";
				}
			}
		}

}
if ($pasDeSQL) {
	$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/> Pas de scripts SQL &agrave; ex&eacute;cuter. <br/>";
}
?>