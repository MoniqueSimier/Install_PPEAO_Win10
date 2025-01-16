<?php 
//*****************************************
// appartenance.php
//*****************************************
// Created by Yann Laurent
// 2008-10-31 : creation
//*****************************************
// Cet include permet de recuperer l'appartenance a une campagne ou une peche artisanale 
// En gros pour chacune des tables, on execute une a deux requetes pour revenir a la campagne ou la peche artisanale
// pour savoir si cette donnée correspond a une nouvelle entite ou une entite deja existante
// ****************************************
// Issu du programme appelant:
// $nomTable = nom de la table en cours de lecture
// $idNomTable = num de l'id en cours
// Renvoie le type d'action (a ajouter ou a mettre a jour)
// ****************************************



if (strpos ($nomTable,"exp_") > 0 ) {
	$typeDonnees = "exp";
} else {
	$typeDonnees = "art";
}
$enregAmaj = false;

$pasDeRequete = false;

switch ($nomTable) {
// *************************************************
// Gestion des tables pour les peches experimentales
// *************************************************
case "exp_campagne" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='exp' and id =".$idNomTable ;
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	
	break;
	
case "exp_environnement" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='exp' and id in (select exp_campagne_id from exp_coup_peche where exp_environnement_id=".$idNomTable.")" ;
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
	
case "exp_coup_peche" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='exp' and id = (select exp_campagne_id from exp_coup_peche where id=".$idNomTable.")";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
case "exp_fraction" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='exp' and id = (select exp_campagne_id from exp_coup_peche where id=(select exp_coup_peche_id from exp_fraction where id=".$idNomTable."))";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
	
case "exp_biologie" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='exp' and id = (select exp_campagne_id from exp_coup_peche where id=(select exp_coup_peche_id from exp_fraction where id=(select exp_fraction_id from exp_biologie where id=".$idNomTable.")))";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
	
case "exp_trophique" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='exp' and id = (select exp_campagne_id from exp_coup_peche where id=(select exp_coup_peche_id from exp_fraction where id=(select exp_fraction_id from exp_biologie where id=(select exp_biologie_id from exp_trophique where id=".$idNomTable."))))";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
// **********************************************
// Gestion des tables pour les peches artisanales
// **********************************************
case "art_unite_peche" :
	$continueOK = true;
	$quelleActivite = "";
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_activite where art_unite_peche_id = ".$idNomTable;
	
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// On controle si l'unite de peche est liée à un art_debarquement
		pg_free_result($scriptSQLResultini);
		$scriptSQLini = "select art_agglomeration_id,annee,mois from art_debarquement where art_unite_peche_id = ".$idNomTable;
	
		$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
		if (pg_num_rows($scriptSQLResultini) == 0) {
			// Message d'erreur
			if ($EcrireLogComp ) { 
				WriteCompLog ($logComp," Requete (select art_agglomeration_id,annee,mois from art_activite / art_debarquement where art_unite_peche_id = ".$idNomTable.") ne renvoie pas de resultat ==> pas de debarquement / activite associe. Ligne ignoree" ,$pasdefichier);
			} else {
				echo "Pas de resultat pour la requete select art_agglomeration_id,annee,mois from art_activite where art_unite_peche_id = ".$idNomTable."<br/>";
			}
			$pasDeRequete = true;
			$tempetatAction = "";
			$continueOK = false;
		} else {
			$quelleActivite = "artDeb";
		} 
	} else {
		$quelleActivite = "artAct";
	}
	if ($continueOK) {
		if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete 2 ".$nomTable." :".$debugTimer."<br/>";
	}
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='".$quelleActivite."' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
	
case "art_lieu_de_peche" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id in (select id from art_debarquement where art_lieu_de_peche_id=".$idNomTable.")";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;

case "art_debarquement" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id =".$idNomTable ;
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;

case "art_debarquement_rec" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id = (select art_debarquement_id from art_debarquement_rec where id=".$idNomTable.")";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;

case "art_fraction_rec" :
	// Pour art_fraction_rec, l'id est le meme que art_fraction, on applique le meme controle que art_fraction
	// Et on recupère le nouvel ID de art_fraction
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id = (select art_debarquement_id from art_fraction where id=".$idNomTable.")";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
	
case "art_stat_totale" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_stat_totale where id = ".$idNomTable;
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select id,art_agglomeration_id,annee,mois from art_stat_totale where id = ".$idNomTable.") ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select id,art_agglomeration_id,annee,mois from art_stat_totale where id = ".$idNomTable.".<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;
	
	
case "art_stat_gt" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id =".$idNomTable.")";
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id =".$idNomTable.") ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id =".$idNomTable."<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;

case "art_stat_gt_sp" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id = (select art_stat_gt_id from art_stat_gt_sp where id =".$idNomTable."))";
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id = (select art_stat_gt_id from art_stat_gt_sp where id =".$idNomTable.")) ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id = (select art_stat_gt_id from art_stat_gt_sp where id =".$idNomTable.").<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;

case "art_stat_sp" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
		$scriptSQLini = "select art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_sp where id =".$idNomTable.")";
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_sp where id =".$idNomTable.")) ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_sp where id =".$idNomTable.").<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;

case "art_taille_gt_sp" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id = (select art_stat_gt_id from art_stat_gt_sp where id = (select art_stat_gt_sp_id from art_taille_gt_sp where id =".$idNomTable.")))";
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id = (select art_stat_gt_id from art_stat_gt_sp where id = (select art_stat_gt_sp_id from art_taille_gt_sp where id =".$idNomTable.")))) ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_gt where id = (select art_stat_gt_id from art_stat_gt_sp where id = (select art_stat_gt_sp_id from art_taille_gt_sp where id =".$idNomTable."))).<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;

case "art_taille_sp" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_sp where id = (select art_stat_sp_id from art_taille_sp where id =".$idNomTable."))";
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_sp where id = (select art_stat_sp_id from art_taille_sp where id =".$idNomTable."))) ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select id,art_agglomeration_id,annee,mois from art_stat_totale where id = (select art_stat_totale_id from art_stat_sp where id = (select art_stat_sp_id from art_taille_sp where id =".$idNomTable.")).<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;

case "art_engin_peche" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id = (select art_debarquement_id from art_engin_peche where id=".$idNomTable.")";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;

case "art_fraction" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id = (select art_debarquement_id from art_fraction where id=".$idNomTable.")";
	//echo $scriptSQL."<br/>";
	$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;

case "art_poisson_mesure" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_fraction_id from art_poisson_mesure where id = ".$idNomTable;
	
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select art_fraction_id from art_poisson_mesure where id = ".$idNomTable.") ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select art_fraction_id from art_poisson_mesure where id = ".$idNomTable."<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete 2 ".$nomTable." :".$debugTimer."<br/>";
	}
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and id = (select art_debarquement_id from art_fraction where id='".$scriptSQLIniRow[0]."')";
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	break;

case "art_activite" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_activite where id = ".$idNomTable;
	
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select art_agglomeration_id,annee,mois from art_activite where id = ".$idNomTable.") ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select art_agglomeration_id,annee,mois from art_activite where id = ".$idNomTable.".<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete 2 ".$nomTable." :".$debugTimer."<br/>";
	}
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;

case "art_engin_activite" :
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete ".$nomTable." :".$debugTimer."<br/>";
	}
	$scriptSQLini = "select art_agglomeration_id,annee,mois from art_activite where id = (select art_activite_id from art_engin_activite where id=".$idNomTable.")";
	
	$scriptSQLResultini = pg_query(${$BDSource},$scriptSQLini) or die('erreur dans la requete : '.pg_last_error());
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete 1 ".$nomTable." :".$debugTimer."<br/>";
	}
	if (pg_num_rows($scriptSQLResultini) == 0) {
		// Message d'erreur
		if ($EcrireLogComp ) { 
			WriteCompLog ($logComp," Requete (select art_agglomeration_id,annee,mois from art_activite where id = (select art_activite_id from art_engin_activite where id=".$idNomTable.")) ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete select art_agglomeration_id,annee,mois from art_activite where id = (select art_activite_id from art_engin_activite where id=".$idNomTable.").<br/>";
		}
		$pasDeRequete = true;
		$tempetatAction = "";
	} else {
		if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance avant requete 2 ".$nomTable." :".$debugTimer."<br/>";
	}
		$scriptSQLIniRow = pg_fetch_row($scriptSQLResultini);	
		$scriptSQL = "select exist,supp,newid,id from temp_exist_peche where type ='artDeb' and cle1 =".$scriptSQLIniRow[0]." and cle2 =".$scriptSQLIniRow[1]." and cle3=".$scriptSQLIniRow[2];
		$scriptSQLResult = pg_query(${$BDSource},$scriptSQL) or die('erreur dans la requete : '.pg_last_error());
		if ($debugAff==true) {
			$debugTimer = number_format(timer()-$start_while,4);
			echo "Appartenance apres requete 2 ".$nomTable." :".$debugTimer."<br/>";
		}
	}
	if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "Appartenance apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
	break;
}

//******************************
// Analyse de la requete
//******************************

if (!$pasDeRequete) {
	if (pg_num_rows($scriptSQLResult) == 0) {
	// Message d'erreur
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp," Requete (".$scriptSQL.") ne renvoie pas de resultat. Ligne ignoree" ,$pasdefichier);
		} else {
			echo "Pas de resultat pour la requete ".$scriptSQL." <br/>";
		}
	} else {
		if ($debugAff==true) {
		$debugTimer = number_format(timer()-$start_while,4);
		echo "traitement apres requete ".$nomTable." :".$debugTimer."<br/>";
	}
		$scriptSQLRow = pg_fetch_row($scriptSQLResult);	
		$testTtypeID = strpos($ListeTableIDPasNum ,$nomTable);

		$tempExist = $scriptSQLRow[0] ;
		$tempNewID = $scriptSQLRow[2];
		$tempID = $scriptSQLRow[3];
		// L'enreg existe et on met a jour
		if ($scriptSQLRow[1] == "y" && $scriptSQLRow[0]=="y" ) {
			$tempetatAction = "m" ;
		} 
		// L'enreg n'existe pas
		if ($scriptSQLRow[0] == "n") {
			$tempetatAction = "a";
		}
		// L'enreg existe mais pas de maj
		if ($scriptSQLRow[1] == "n" && $scriptSQLRow[0]=="y" ) {
			$tempetatAction = "r" ;
		} 		
	}
}


?>
