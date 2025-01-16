<?php 
//*****************************************
// export_gestionBD.php
//*****************************************
// Created by Yann Laurent
// 2008-07-01 : creation
//*****************************************
// Ce programme lance le test de la base de référence et le nettoyage de la base de travail
//*****************************************
// Paramètres en entrée
// Paramètres en sortie
// aucun


// Mettre les noms des fichiers dans un fichier texte
session_start();
//**** initialisation de la variable de session
$_SESSION['s_status_export'] = 'ok';
set_time_limit(180);
// Variable de test (en fonctionnement production, les deux variables sont false)
$pasdetraitement = true;
$pasdefichier = false; // Variable de test pour linux. Meme valeur que dans comparaison.php
$erreurProcess = false; // indicateur d'erreur de process
$CRexecution ="";
$affichageDetail = false; // Pour afficher ou non le detail des traitements à l'écran
// Includes standard

include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/config.php';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/export/config.php';
// *********************************************
// ****** Recuperation des paramètres en entrée
// *********************************************
// On identifie si le traitement est exécutable ou non
if (isset($_GET['exec'])) {
	if ($_GET['exec'] == "false") {
		$pasdetraitement =  true;
		$Labelpasdetraitement ="non";
	} else {
		$pasdetraitement =  false;
		$Labelpasdetraitement ="oui";
	}
} 
// On récupère le type de pêche
if (isset($_GET['tp'])) {
	$typePeche =$_GET['tp'];
} else {
	$CRexecution .= "erreur pas de parametre tp <br/>";
	$erreurProcess = true;
}

if (isset($_GET['action'])) {
	$action =$_GET['action'];
} else {
	$CRexecution .= "erreur pas de parametre action <br/>";
	$erreurProcess = true;
}

if (isset($_GET['log'])) {
	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. 
	} else {
		$EcrireLogComp = true;
	}
}

// ****************** Connecteur ODBC *************************************
// Recupération des paramétres de connexion ODBC aux bases de references artisanales ou expermientales.
// Variables pour la sauvegarde de la base de reference (PPEAO)
// ***********************************************************


// Note: le nom de la connexion ODBC doit etre le meme que le nom du fichier .mdb
switch ($typePeche) { 
	case "exp":
		$BDrep = GetParam("nomRepBD",$PathFicConfAccess);
		$BDACCESS = GetParam("nomBDRefExp",$PathFicConfAccess);
		$nomPeche = "peches experimentales";
		break;
	case "art":
		$BDrep = GetParam("nomRepBD",$PathFicConfAccess);
		$BDACCESS = GetParam("nomBDRefArt",$PathFicConfAccess);
		$nomPeche = "peches artisanales";
		break;	
}
switch ($action) { 
	case "ctrl":
		$nomAction = "Controle base de donnees ".$nomPeche;
		$numFen = 1;
		$nomFenetre = "controleBase";
		break;
	case "vide":
		$BDrep = GetParam("nomRepBDtravail",$PathFicConfAccess);
		$nomAction = "Vidage base de travail ".$nomPeche;
		$BDACCESS = $BDACCESS."_travail";
		$numFen = 2;
		$nomFenetre = "vidage";
		break;	
}

// On récupère les valeurs des paramètres pour les fichiers log
$dirLog = GetParam("repLogAccess",$PathFicConfAccess);
$nomLogLien = "/".$dirLog; // pour créer le lien au fichier dans le cr ecran
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/".$dirLog;
$fileLogComp = GetParam("nomFicLogSuppAc",$PathFicConfAccess);

// *********************************************
// ****** Traitements préliminaires  
// *********************************************
//	Contrôle des répertoires et fichiers log
// 		Controle répertoire
if (! $pasdefichier) { // Pour test sur serveur linux
	if (! file_exists($dirLog)) {
		if (! mkdir($dirLog) ) {
			$messageGen = " erreur de cr&eacute;ation du r&eacute;pertoire de log";
			logWriteTo(8,"error","Erreur de creation du repertoire de log dans comparaison.php","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
			exit;
		}
	}
//	Controle fichiers
//	Resultat de la comparaison
	if ($EcrireLogComp ) {
		$nomFicLogComp = $dirLog."/".date('y\-m\-d')."-".$fileLogComp;
		$nomLogLien = $nomLogLien."/".date('y\-m\-d')."-".$fileLogComp;
		$logComp = fopen($nomFicLogComp , "a+");
		if (! $logComp ) {
			$messageGen = " erreur de cr&eacute;ation du fichier de log";
			logWriteTo(8,"error","Erreur de creation du fichier de log ".$dirLog."/".date('y\-m\-d')."-".$fileLogComp." dans comparaison.php","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
			exit;		
		}
	}
}	

if (! $pasdetraitement ) { // test pour debug lors du lancement de la chaine complète de traitement automatique (saute cette etape)
	// Début des traitements
	if ($EcrireLogComp && $action=="ctrl") {
		WriteCompLog ($logComp, "#",$pasdefichier);
		WriteCompLog ($logComp, "#",$pasdefichier);
		WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
		WriteCompLog ($logComp, "*- EXPORT ACCESS ".date('y\-m\-d\-His'),$pasdefichier);
		WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
		WriteCompLog ($logComp, "#",$pasdefichier);
		WriteCompLog ($logComp, "#",$pasdefichier);
	}
	// On ne teste pas la date de la base ACCESS EXP car on n'importe rien depuis la base ACCESS
	if ($action == "ctrl" && $typePeche =="exp") {
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/completed.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\"> Pas de traitement pour les peches experimentales.</div>" ;
			exit;
	}

	// Initialisation des logs
	logWriteTo(8,"notice","**- Debut lancement ".$nomAction." ","","","0");
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "*******************************************************",$pasdefichier);
		WriteCompLog ($logComp, "*- DEBUT lancement ".$nomAction." ",$pasdefichier);
		WriteCompLog ($logComp, "*------------------------------------------------------",$pasdefichier);
	}
	// test d'existence du fichier
	if ($typePeche == 'art') {
		$BDfic = $_SERVER["DOCUMENT_ROOT"]."/".$BDrep."/".$BDACCESS.".mdb";
		if (!file_exists($BDfic)) {
			$CRexecution .= "ERREUR : le fichier .mdb de references n'existe pas. (".$BDfic.")<br/>";
			if ($EcrireLogComp ) {
					WriteCompLog ($logComp,"*- ERREUR : le fichier .mdb de references n'existe pas. (".$BDfic.")",$pasdefichier);
			}		
			$erreurProcess = true;
		}
	}
	// test d'existence du fichier de lock ACCESS : si il est présent, arrêt du traitement et action de l'admin BD
	$BDficLock = $_SERVER["DOCUMENT_ROOT"]."/".$BDrep."/".$BDACCESS.".ldb";
	if (file_exists($BDficLock)) {
		$CRexecution .= "ERREUR : un fichier de lock (".$BDACCESS.".ldb)pour la base de donnees est present dans ".$BDrep.". <br/>Merci de reparer le probleme.<br/>Le traitement s'arrete.";
		if ($EcrireLogComp ) {
				WriteCompLog ($logComp,"*- ERREUR : un fichier de lock (".$BDACCESS.".ldb)pour la base de donnees est present dans ".$BDrep.".",$pasdefichier);
		}
		$erreurProcess = true;
	}

	// Si pas d'erreur avant sur les tests preliminaires, on lance le traitement.
	if (! $erreurProcess ) {
		// quelle est l'action à mener ?
		switch ($action) {
			case "ctrl" : 
				// ************** Controle de la dernière mise a jour de la base de ref *********************
				// Si le fichier de reference .mdb a plus de 2 semaines on conseille de mettre à jour le fichier.
				// Est-ce qu'on le met en erreur ?
				// ********************************
				if ($typePeche == 'art') {
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp,"* Compte rendu traitement ".$nomAction,$pasdefichier);
						WriteCompLog ($logComp,"******************************************",$pasdefichier);
					}
					$weekRef = date("W");
					$yearRef = date("Y");
					$weekControle = date("W", filectime($BDfic));
					$yearControle = date("Y", filectime($BDfic));
					if ($yearRef == $yearControle ){
						$diffDate = (intval($weekRef) - intval($weekControle));
					} else {
						$diffDate = 52 - intval($weekControle) + intval($weekRef);
					}
					$CRexecution .= $BDACCESS." a &eacute;t&eacute; modifi&eacute; pour la derni&egrave;re fois le : ".date("F d Y H:i:s.", filectime($BDfic)).".<br/>";
					// Attention, le calcul doit prendre en compte l'année.
					if ( $diffDate  > 2) {
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"La derniere mise a jour du fichier .mdb date de plus de 15 jours ==> Mise a jour de la base ACCESS de reference requise",$pasdefichier);
						}
						$CRexecution .= "La derni&egrave;re mise &agrave; jour du fichier .mdb date de plus de 15 jours <br/> ==> Mise &agrave; jour de la base ACCESS de r&eacute;f&eacute;rence requise";
						// A faire UPLOAD
					} else {
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"La derniere mise a jour du fichier date de moins de 15 jours. Pas de maj necessaire.",$pasdefichier);
						}
						$CRexecution .= "La derni&egrave;re mise &agrave; jour du fichier date de moins de 15 jours <br/> Pas de maj n&eacute;cessaire.";
					}
				}
				break;
				// ************** fin du case "ctrl"; ************	

			case "vide" :
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp,"* Compte rendu traitement ".$nomAction,$pasdefichier);
					WriteCompLog ($logComp,"* Base ".$BDACCESS." va etre videe",$pasdefichier);
					WriteCompLog ($logComp,"*------------------------------------------------------",$pasdefichier);
				}
				// ************** vidage de la base de travail  *********************
				// Test connection ODBC	
				//$lev=error_reporting (8); //Pour eviter les avertissements si la base n'existe pas.	
				$connectAccess = odbc_connect($BDACCESS,'','',SQL_CUR_USE_ODBC);
				// test nunux
				//echo "test DSN <br/>";
				//$result = @odbc_data_source( $connectAccess, SQL_FETCH_FIRST );
				//while($result)
				//{
				//	echo "DSN: " . $result['server'] . " - " . $result['description'] . "<br>\n";
				//	$result = @odbc_data_source( $connectAccess, SQL_FETCH_NEXT );
				//}

				//echo "fin test DSN <br/>";
				// fin test nunux
				// affichage test PB lock ACCESS
				if ($EcrireLogComp && $affichageDetail) {
					WriteCompLog ($logComp,"*- INFO : apres connexion",$pasdefichier);
				}
				if (! $connectAccess ) {
					$CRexecution .= "Erreur de la connection &agrave; la base ACCESS ".$BDACCESS."<br/>";
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp,"*- ERREUR : Erreur de la connection &agrave; la base ACCESS ".$BDACCESS,$pasdefichier);
					}
					$erreurProcess = true;
				} else {
					// affichage test PB lock ACCESS
					if ($EcrireLogComp && $affichageDetail) {
						WriteCompLog ($logComp,"*- INFO : Connexion ok",$pasdefichier);
					}
					if ($affichageDetail) {
						$CRexecution .= "Connection avec succ&egrave;s &agrave; la base ACCESS ".$BDACCESS."<br/>";
					}
					switch ($typePeche) { 
						case "exp":
							// Tables avec correspondance PPEAO	
							$listTable = GetParam("listeaViderExpACCESS",$PathFicConfAccess);
							// Tables specifiques ACCESS			
							$listTable2 = GetParam("listeaViderExpPPEAO",$PathFicConfAccess);
							break;
						case "art":
							// Tables avec correspondance PPEAO	
							$listTable = GetParam("listeaViderArtACCESS",$PathFicConfAccess);
							// Tables specifiques ACCESS			
							$listTable2 = GetParam("listeaViderArtPPEAO",$PathFicConfAccess);
							break;	
					}
					if ($listTable == "" && $listTable2 =="") {
						$CRexecution .= "ERREUR : dans le fichier de conf, les listes des tables a supprimer sont vides...";				if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"*- ERREUR : dans le fichier de conf, les listes des tables a supprimer sont vides... Arret traitement.",$pasdefichier);
						
						}
						$erreurProcess = true;
					} else {
						if ($listTable =="") {
							$ListeTableAVider = $listTable2;
						} else {
							//listTable2 est jamais vide
							$ListeTableAVider = $listTable.",".$listTable2;
						}
					}
					// affichage test PB lock ACCESS
					if ($EcrireLogComp && $affichageDetail) {
						WriteCompLog ($logComp,"*- INFO : liste table a vider = ".$ListeTableAVider,$pasdefichier);
					}
					// Test nunux
					//echo "Debut ".$BDACCESS."<br/>";
					//$scriptTest = "select count(IdEnqueteur) from Enqueteur";
					//$SQLTestResult = odbc_prepare($connectAccess, $scriptTest);
					//$SQLexec = odbc_execute($SQLTestResult);
					//$erreurSQL = odbc_errormsg($connectAccess); // 
					//if (!$SQLexec) {
						//$CRexecution .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur lecture Enqueteur(erreur compl&egrave;te = ".$erreurSQL.")<br/>";
					//} else	{ 
						//$compTestC = odbc_fetch_row($SQLTestResult);
						//echo "print_r = ";
						//print_r($compTestC);
						//echo "<br/>";
						//$totalLignesTest = odbc_result($SQLTestResult,1);
						//echo "nombre total ligne = ".$totalLignesTest."<br/>";
						//if ($totalLignesTest > 0) {
							//$scriptTest2 = "select IdEnqueteur from Enqueteur";
							//$SQLTestResult2 = odbc_exec($connectAccess,$scriptTest2);
							//$erreurSQL = odbc_errormsg($connectAccess); // 
							//if (!$SQLTestResult2) {
								//$CRexecution .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur lecture Enqueteur(erreur compl&egrave;te = ".$erreurSQL.")<br/>";
							//} else	{ 
								//$compTest = array();
								//while ($compTest = odbc_fetch_array($SQLTestResult2)) {
									//echo $compTest['IdEnqueteur']."<br/>";
									
								//}
							//}
						//}
					//}

					// Fin test nunux
					if (!$erreurProcess) {					
					$tables = explode(",",$ListeTableAVider);
					$nbTables = count($tables) - 1;
					logWriteTo(8,"notice"," Nb tables = ".$nbTables ,"","","1");
					// Début du traitement de suppression par table.
					// *********************************************
					$nbSuppOk = 0;
					$nbSuppErr = 0;
					$start_while=timer(); // début du chronométrage du for
					// test nunux 
					$continuetest = true;
					if ($continuetest) {

						for ($cpt = 0; $cpt <= $nbTables; $cpt++) {
							$scriptDelete = "delete * from ".$tables[$cpt];
							$SQLDeleteResult = odbc_exec($connectAccess,$scriptDelete);
							$erreurSQL = odbc_errormsg($connectAccess); // 
							if (!$SQLDeleteResult) {
								$CRexecution .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur de suppression de la table ".$tables[$cpt]."(erreur compl&egrave;te = ".$erreurSQL.")<br/>";
								if ($EcrireLogComp ) {
									WriteCompLog ($logComp,"*- erreur de suppression de la table ".$tables[$cpt]."(erreur compl&egrave;te = ".$erreurSQL.")",$pasdefichier);
								}
								$erreurProcess = true;
								$nbSuppErr++;
							} else	{ 
								$nbSuppOk ++;
								if ($affichageDetail) {
									$CRexecution .= $tables[$cpt]." vid&eacute;e .<br/>";
									if ($EcrireLogComp ) {
										WriteCompLog ($logComp,"*- ".$tables[$cpt]." videe .",$pasdefichier);
									}
								}
							} 
						}// fin du for	
					}
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp,"* Nb tables videes ok     = ".$nbSuppOk,$pasdefichier);
						WriteCompLog ($logComp,"* Nb tables erreur vidage = ".$nbSuppErr,$pasdefichier);
					}	
					} else {
						odbc_close($BDACCESS);
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"*- Erreur arret du traitement",$pasdefichier);
						}
					}//fin du if (!$erreurProcess)	
				} // fin du if (!$connectAccess)
				//error_reporting ($lev); // retour au avertissements par defaut
				break;
			// ************** fin du case "vide"; ************
		} // fin du switch
	} // fin du if (! $erreurProcess )

	// ************ Gestion des erreurs de process *****************
	if ($erreurProcess) {
			$_SESSION['s_status_export'] = 'ko';
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Traitement en erreur (voir d&eacute;tail ci-dessous)</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
			echo"<div id=\"vertical_slide".$numFen."\">".$CRexecution."</div>";
			logWriteTo(8,"error","**- Traitement en erreur : ".$CRexecution."","","","0");
	} 
	else {
		if ($action=="vide") {
			odbc_close($connectAccess);
		}
		if ($EcrireLogComp ) {
				WriteCompLog ($logComp,"*------------------------------------------------------",$pasdefichier);
				WriteCompLog ($logComp,"*- FIN TRAITEMENT ".$nomAction,$pasdefichier);
				WriteCompLog ($logComp,"*******************************************************",$pasdefichier);
			}
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/completed.png\" alt=\"\"/></div><div //id=\"".$nomFenetre."_txt\">".$nomAction." ex&eacute;cut&eacute;e avec succ&egrave;s </div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
			echo"<div id=\"vertical_slide".$numFen."\">".$CRexecution."</div>";
	}

		
} else { // else du if (! $pasdetraitement )
	echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Etape de ".$nomAction." non ex&eacute;cut&eacute;e par choix de l'utilisateur</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
	logWriteTo(8,"error","**- EXPORT Etape ".$nomAction." non executee par choix de l'utilisateur","","","0");
} // fin du if (! $pasdetraitement )




exit;

?>
