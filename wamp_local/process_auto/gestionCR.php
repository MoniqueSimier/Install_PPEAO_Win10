<?php 
//*****************************************
// gestionCR.php
//*****************************************
// Created by Yann Laurent
// 2008-07-01 : creation
//*****************************************
// Cet include de comparaison.php gère les affichages des comptes-rendu à l'ecran
//*****************************************
	if (!$ArretTimeOut) {

	// ***************************
	// modification du CR pour l'integration dans un email
	$CRfichier = str_replace("<br/>","\r\n",$CRexecution);
	$CRfichier = str_replace ("<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>","*--> ",$CRfichier);
	$CRfichier = str_replace ("<b>","",$CRfichier);
	$CRfichier = str_replace ("</b>","",$CRfichier);
	// ***************************
	// Si on est dans le cas normal, on génère le compte rendu de fin de traitement.
		if ($_SESSION['s_erreur_process']) {
			$_SESSION['s_status_process_auto'] = 'ko';
			if ($typeAction == "comp" || $typeAction == "compinv") {
				$_SESSION['s_status_restauration'] = "no";
			} else {
				$_SESSION['s_status_restauration'] = "yes";
			}
		}
		echo "<form id=\"formtrt\"> 
			<input id=\"trtok".$numFen."\" type=\"hidden\" value=\"".$_SESSION['s_status_process_auto']."\"/>
			</form>";	
		// On met à jour la table des logs avec les données
		logWriteTo(7,"notice","**- Compte rendu traitement ".$nomAction,"","","0");
		logWriteTo(7,"notice","*-- Nombre total de tables lues = ".$_SESSION['s_cpt_table_total'],$_SESSION['s_cpt_table_total'],"","0");
		logWriteTo(7,"notice","*-- Nombre de tables identiques = ".$_SESSION['s_cpt_table_egal'],$_SESSION['s_cpt_table_egal'],"","0");		
		logWriteTo(7,"notice","*-- Nombre de tables avec uniquement des donnees differences = ".$_SESSION['s_cpt_table_diff'],$_SESSION['s_cpt_champ_diff'],"","0");
		logWriteTo(7,"notice","*-- Nombre de tables avec uniquement des donnees manquantes = ".$_SESSION['s_cpt_table_manquant'],$_SESSION['s_cpt_table_manquant'],"","0");
				logWriteTo(7,"notice","*-- Nombre de tables avec des donnees manquantes et differentes = ".$_SESSION['s_cpt_table_diff_manquant'],$_SESSION['s_cpt_table_diff_manquant'],"","0");
		logWriteTo(7,"notice","*-- Nombre de tables vides = ".$_SESSION['s_cpt_table_vide'],$_SESSION['s_cpt_table_vide'],"","0");
		logWriteTo(7,"notice","*-- Nombre de tables de references vides = ".$_SESSION['s_cpt_table_source_vide'],$_SESSION['s_cpt_table_source_vide'],"","0");
		logWriteTo(7,"notice","*-- Nombre d'erreur lors de la maj = ".$_SESSION['s_cpt_erreurs_sql'],$_SESSION['s_cpt_erreurs_sql'],"","0");
	
		if ($EcrireLogComp ) {
			// Si on a choisi de générer le log complémentaire, alors
			// gestion fin de traitement 
			WriteCompLog ($logComp,"******************************************",$pasdefichier);
			WriteCompLog ($logComp,"* Compte rendu traitement ".$nomAction,$pasdefichier);
			WriteCompLog ($logComp,"******************************************",$pasdefichier);	
			WriteCompLog ($logComp,"* Nombre total de tables lues = ".$_SESSION['s_cpt_table_total'],$pasdefichier);						
			if ($typeAction == "comp" || $typeAction == "compinv") {
				WriteCompLog ($logComp,"* Nombre de tables identiques = ".$_SESSION['s_cpt_table_egal'],$pasdefichier);
				WriteCompLog ($logComp,"* Nombre de tables avec uniquement des donnees differentes = ".$_SESSION['s_cpt_table_diff'],$pasdefichier);
				WriteCompLog ($logComp,"* Nombre de tables avec uniquement des donnees manquantes = ".$_SESSION['s_cpt_table_manquant'],$pasdefichier);
				WriteCompLog ($logComp,"* Nombre de tables avec des donnees manquantes et differentes = ".$_SESSION['s_cpt_table_diff_manquant'],$pasdefichier);
				WriteCompLog ($logComp,"* Nombre de tables vides = ".$_SESSION['s_cpt_table_vide'],$pasdefichier); 
				WriteCompLog ($logComp,"* Pour info Nombre de tables de references vides = ".$_SESSION['s_cpt_table_source_vide'],$pasdefichier); 
			} else {
				$FicCRexecution = str_replace ("<br/>","\r\n".date('y\-m\-d\-His')."- ",$CRexecution);
				$FicCRexecution = str_replace ("<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>","*--> ",$FicCRexecution);
				$FicCRexecution = str_replace ("<b>","",$FicCRexecution);
				$FicCRexecution = str_replace ("</b>","",$FicCRexecution);
				WriteCompLog ($logComp,$FicCRexecution,$pasdefichier);
			}
		}
		// Affichage d'avertissement si erreur dans le traitement
		if ($_SESSION['s_erreur_process']) {
			if ($typeAction == "comp" || $typeAction == "compinv") {
				// Avertissement dans le cas de la comparaison
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp,"*---------------------------------------------",$pasdefichier);
					WriteCompLog ($logComp,"* ATTENTION, des mises a jour sont requises pour les tables ou des enregitrements manquent ou sont differents",$pasdefichier);
					WriteCompLog ($logComp,"* Scripts SQL pour ces mises a jours presents dans ".date('y\-m\-d')."-".$nomFicSQL."-xxx.sql",$pasdefichier);
				}
				logWriteTo(7,"error","*** Enregistrements differents ou manquants : arret du traitement ==> lancer les mises a jour des enregistrements.","","","0");
				logWriteTo(7,"error","*** Scripts SQL pour ces mises a jours presents dans ".date('y\-m\-d')."-".$nomFicSQL."-xxx.sql","","","0");
			} else {
			// L'avertissement est différent pour la mise à jour
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp,"*---------------------------------------------",$pasdefichier);
					WriteCompLog ($logComp,"* ATTENTION, il y a eu des erreurs sur des ajouts / mises a jour de table.",$pasdefichier);
					WriteCompLog ($logComp,"* Merci de controler avec l'admin BD les integrites des donnees a copier.",$pasdefichier);
				}
				logWriteTo(7,"error","*** Erreurs dans l'ajout / mise a jour des donnees : arret du traitement ==> contacter l'admin BD pour un controle des enregistrements de la base a mettre a jour","","","0");
			}
				
		}
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp,"*---------------------------------------------",$pasdefichier);
			WriteCompLog ($logComp,"*- FIN TRAITEMENT ".$nomAction,$pasdefichier);
			WriteCompLog ($logComp,"*---------------------------------------------",$pasdefichier);
			logWriteTo(7,"notice","*-- Log plus complet disponible dans <a href=\"".$nomLogLien."\" target=\"log\">".$nomFicLogComp."</a>","","","0");
		}
		// Fin de traitement dans le log
		logWriteTo(7,"notice","**- Fin traitement de ".$nomAction,$cptTableVide,"","","0");
	
	
		// ***********************************************
		// On gére l'affichage a l'ecran d'un compte rendu
		// Notez que l'ajout du <div id=\"".$nomFenetre."_chk\">Exec= xxxx</div> permet d'avoir un compte rendu plus net (lié au css qui contient un display block pour le div id = nomfenete_txt
		if ($typeAction == "comp" || $typeAction == "compinv") {
			if (!$_SESSION['s_erreur_process']) {
					// Pas de différences
				echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/completed.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">".$nomAction." exec. avec succes = toutes tables identiques.";
				if ($EcrireLogComp ) {
					echo "<br/>Un compte rendu plus detaille est disponible dans le fichier de log : <a href=\"".$nomLogLien."\" target=\"log\">".$nomLogLien."</a>";
				}
				echo"</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
				//echo"<div class=\"marginCR\">Compte Rendu&nbsp;<a id=\"v_slidein".$numFen."\" href=\"#\"> Afficher </a>|<a id=\"v_slideout".$numFen."\" href=\"#\"> Fermer </a>| <strong>status</strong>: <span id=\"vertical_status".$numFen."\">open</span>				</div>";
				echo"<div id=\"vertical_slide".$numFen."\">".$CRexecution." ".$messageGen."</div>";
				
				
				
			} else {
				// Différences
				echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/dep.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">".$nomAction." executee avec succes <br/>Des tables sont <b>differentes et/ou vides</b>. ==> Mise a jour necessaires avant de relancer le traitement.<br/> (voir le fichier ".date('y\-m\-d')."-".$nomFicSQL."-xxx.sql contenant les mises a jour)";
				if ($EcrireLogComp ) {
				echo "<br/>Un compte rendu plus detaille est disponible dans le fichier de log : <a href=\"".$nomLogLien."\" target=\"log\">".$nomLogLien."</a>";
				}
				echo"</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
				//echo"<div class=\"marginCR\">Compte Rendu&nbsp;<a id=\"v_slidein".$numFen."\" href=\"#\"> Afficher </a>|<a id=\"v_slideout".$numFen."\" href=\"#\"> Fermer </a>| <strong>status</strong>: <span id=\"vertical_status".$numFen."\">open</span>				</div>";
				echo"<div id=\"vertical_slide".$numFen."\">".$CRexecution." ".$messageGen."</div>";
			}
		} else {
			if ( $_SESSION['s_erreur_process']) {
			// Erreur dans la mise à jour
				echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">".$nomAction." Erreur dans l'ajout ou la modification des donnees.";
				if ($EcrireLogComp ) {
					echo "<br/>Un compte rendu plus detaille est disponible dans le fichier de log : <a href=\"".$nomLogLien."\" target=\"log\">".$nomLogLien."</a>";
				}
				echo"</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
				//echo"<div class=\"marginCR\">Compte Rendu&nbsp;<a id=\"v_slidein".$numFen."\" href=\"#\"> Afficher </a>|<a id=\"v_slideout".$numFen."\" href=\"#\"> Fermer </a>| <strong>status</strong>: <span id=\"vertical_status".$numFen."\">open</span>				</div>";
				echo"<div id=\"vertical_slide".$numFen."\">".$CRexecution." ".$messageGen."</div>";			
			} else {
			// Aucune erreur dans la mise à jour
				echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/completed.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">".$nomAction." ; des tables ont ete ajoutees ou modifies avec succes.";
				if ($EcrireLogComp ) {
					echo "<br/>Un compte rendu plus detaille est disponible dans le fichier de log : <a href=\"".$nomLogLien."\" target=\"log\">".$nomLogLien."</a>";
				}
				echo"</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
				//echo"<div class=\"marginCR\">Compte Rendu&nbsp;<a id=\"v_slidein".$numFen."\" href=\"#\"> Afficher </a>|<a id=\"v_slideout".$numFen."\" href=\"#\"> Fermer </a>| <strong>status</strong>: <span id=\"vertical_status".$numFen."\">open</span>				</div>";
				echo"<div id=\"vertical_slide".$numFen."\">".$CRexecution." ".$messageGen."</div>";
			} // end of statement else of  if ( $ErreurProcess)
		} // end of statement else of  if ($typeAction = "comp")
		
	
		//Gestion du compte_rendu envoyé par mail
		// On le desactive
		$emailActiv = false;
		if ($emailActiv) {
			if (isset ($_GET['adresse']) && !$_GET['adresse']=="") {
				$to = $_GET['adresse'];
				// Subject
				$subject = $typeAction." pour la base de donnees ".pg_dbname($connectPPEAO);
				// Message
				$msg = "Vous trouverez ci-dessous le compte-rendu pour le traitement ".$nomAction."\r\n \r\n";
				
				$msg .="******************************************\r\n";
				$msg .="* Compte rendu traitement ".$nomAction." \r\n";
				$msg .="*- source : ".$nomBDSource." cible : ".$nomBDCible." \r\n";
				$msg .="******************************************\r\n";
				$msg .="* Nombre total de tables lues = ".$_SESSION['s_cpt_table_total']."\r\n";
				$msg .="* Nombre de tables identiques = ".$_SESSION['s_cpt_table_egal']."\r\n";
				$msg .="* Nombre de tables avec uniquement des donnees differentes = ".$_SESSION['s_cpt_table_diff']."\r\n";
				$msg .="* Nombre de tables avec uniquement des donnees manquantes = ".$_SESSION['s_cpt_table_manquant']."\r\n";
				$msg .="* Nombre de tables avec des donnees manquantes et differentes = ".$_SESSION['s_cpt_table_diff_manquant']."\r\n";
				$msg .="* Nombre de tables vides = ".$_SESSION['s_cpt_table_vide']."\r\n"; 
				$msg .="* Pour info Nombre de tables de references vides = ".$_SESSION['s_cpt_table_source_vide']."\r\n";
				$msg .="* ".$CRfichier."\r\n";
				// Affichage d'avertissement si erreur dans le traitement
				if ($_SESSION['s_erreur_process']) {
					if ($typeAction == "comp" || $typeAction == "compinv") {
						// Avertissement dans le cas de la comparaison
							$msg .="*---------------------------------------------/n";
							$msg .="* ATTENTION, des mises a jour sont requises pour les tables ou des enregitrements manquent ou sont differents\r\n";
							$msg .="* Scripts SQL pour ces mises a jours presents dans ".date('y\-m\-d')."-".$nomFicSQL."-xxx.sql\r\n";
		
					} else {
					// L'avertissement est différent pour la mise à jour
							$msg .="*---------------------------------------------\r\n";
							$msg .="* ATTENTION, il y a eu des erreurs sur des ajouts / mises a jour de table.\r\n";
							$msg .="* Merci de controler avec l'admin BD les integrites des donnees a copier.\r\n";
					}
				}
				$msg .="*---------------------------------------------\r\n";
				$msg .="*- FIN TRAITEMENT ".$typeAction." \r\n";
				$msg .="*---------------------------------------------\r\n";
				// Headers
				$headers = 'From: base_PPEAO'."\r\n";
				$headers .= "\r\n";
				// Function mail()
				mail($to, $subject, $msg, $headers);
			}
		} // fin du if ($emailActiv)
		// ************************************
		// Fin du traitement, on reinitialise les compteurs pour la prochaine utilisation de ce programme
		$_SESSION['s_cpt_champ_total'] = 0 ;
		$_SESSION['s_cpt_champ_diff'] = 0 ;
		$_SESSION['s_cpt_champ_egal'] = 0 ;
		$_SESSION['s_cpt_champ_vide'] = 0 ;
		$_SESSION['s_cpt_table_total'] = 0 ;
		$_SESSION['s_cpt_table_diff'] = 0 ;
		$_SESSION['s_cpt_table_egal'] = 0 ;
		$_SESSION['s_cpt_table_vide'] = 0 ;
		$_SESSION['s_cpt_table_manquant'] = 0 ; 
		$_SESSION['s_num_encours_fichier_SQL'] = 1;
		$_SESSION['s_cpt_erreurs_sql']= 0;
		
		
		
	} else { // End for statement ($ArretTimeOut)
	// Le traitement est relancé pour cause de timeout, on met a jour le(s) log(s)
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp,"Interruption gestion timeout pour la table ".$tableEnLecture." et Id = ".$IDEnLecture,$pasdefichier);
		}
		logWriteTo(7,"notice","Interruption gestion timeout pour la table ".$tableEnLecture." et Id = ".$IDEnLecture,"","","0");
		// test
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/dep.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Table ".$tableEnLecture." (".$_SESSION['s_cpt_table_total']." sur ".$NbrTableAlire." ) / enreg. ".$cptChampTotal." sur ".$totalLignes." <br/>".$nomAction." en cours </div>";
		echo"</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
		echo"<div id=\"vertical_slide".$numFen."\">";
		echo "<form id=\"formtest\"> 
		<input id=\"nomtable\" 	type=\"hidden\" value=\"".$tableEnLecture."\"/>
		<input id=\"numID\" 	type=\"hidden\" value=\"".$IDEnLecture."\"/>
		<input id=\"numproc\" 	type=\"hidden\" value=\"".$numProcess."\"/>
		<input id=\"execsql\" 	type=\"hidden\" value=\"".$ExecSQL."\"/>
		</form></div>";
	}

?>