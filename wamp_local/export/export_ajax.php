<?php

// script appele via Ajax par la fonction javascript doExport() et qui permet de realiser des operations preparatoires a l'export des données en ACCESS (script export.php)
// Créé le 19/01/2011 Yann Laurent

// parametres de connexion a la base de donnees
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/export/functions_ajax.php';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
// on recupere l'action de maintenance a realiser
$action=$_GET["action"];
if (isset($_GET["choixExport"])){
	$choixExport=$_GET["choixExport"];	
} else {
$choixExport="";
}
global $nomLogLien;
global $zipFilelien;
// fichier log pour les test
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/log";
$fileLogComp = "export.log";
$logComp="";
$nomLogLien="";
$EcrireLogComp = false;
$pasdefichier = false; // vieux residu du portage....
ouvreFichierLog($dirLog,$fileLogComp);

// on suppose que l'action a ete realisee avec succes
$success=true;
$operation="";
$erreurSQL="";
$erreur ="";
$actionSimple=true;
$compteRendu = "";
switch ($action) {

	case "exportBaseRef":
		$actionSimple=false;
		if ($choixExport =="") {
			// Selection complementaire pour savoir quel type d'export de la base bdppeao l'utilisateur veut faire
		$contenu = "<div id=\"selComp\"><form ><h2>Que voulez-vous faire ?</h2><br/>
		<input id=\"choixExport2\" checked=\"checked\" name=\"choixExport\" type=\"radio\" value=\"2\" />Exporter une base pour la saisie de nouvelles informations ?<br/>		
		<input id=\"choixExport1\"  name=\"choixExport\" type=\"radio\" value=\"1\" />Exporter la base bdppeao compl&egrave;te (<b>attention, l'op&eacute;ration pourra prendre un certain temps !</b>) ?<br/>
		<input id=\"choixExport3\" name=\"choixExport\" type=\"radio\" value=\"3\" />Exporter une base partielle apr&egrave;s avoir s&eacute;lectionn&eacute; le ou les &eacute;cosyst&egrave;me(s) &agrave; extraire ?<br/>
		<br/>
		&nbsp;<input type=\"button\" value=\"Valider mon choix\" onclick=\"javascript:doExportSelect('exportBaseRef','non');\"/>
		<br/>
		</form>
		</div>
		";
		} else {
			// L'utilisateur a choisi l'action a mener
			switch ($choixExport) {
				case  "1": // pg_dump de la base
					// Initialisation de la table de donnée
					$timer_debut = timer();
					$erreur  = initializeTempExtraction($connectPPEAO);
					// Extraction du referentiel, du parametrage et des données
					if ($erreur == "") {
						$erreur  = getSQLExport("","","Tout",$connectPPEAO,"n");
					} 
					// Création du fichier SQL
					if ($erreur == "") {
						$erreur  = createSQLFile("bdppeao_a_importer",$connectPPEAO,"n");
					}
					$delaiExec = number_format(timer() - $timer_debut,1);
					if ($erreur == "") {
						$contenu = "<div id=\"expResultat\">L'export complet de la base a &eacute;t&eacute; r&eacute;alis&eacute; avec succ&egrave;s en ".$delaiExec." secondes.<br/>(Le fichier est disponible au t&eacute;l&eacute;chargement <a href=\"/work/export/SQL-bdppeao/".$zipFilelien."\">ici</a>.) ";
						if ($EcrireLogComp ) {
							$contenu .="<br/>(le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>) ";
						}
						$contenu .="</div>";
					} else {
						$contenu = "<div id=\"expResultat\">Erreur lors de l'export complet de la base : ".$erreur.".</div>";
					}
					break;
				case  "2": // export uniquement du ref / parametrage
					// Creation du fichier SQL
					// Initialisation de la table de donnée
					$timer_debut = timer();
					$erreur  = initializeTempExtraction($connectPPEAO);
					// Extraction du referentiel et du parametrage
					if ($erreur == "") {
						$erreur  = getSQLExport("","","RefParam",$connectPPEAO,"n");
					} 
					// Création du fichier SQL
					if ($erreur == "") {
						$erreur  = createSQLFile("bdppeao_a_importer",$connectPPEAO,"n");
					}
					$delaiExec = number_format(timer() - $timer_debut,1);
					if ($erreur == "") {
						$contenu = "<div id=\"expResultat\">L'export de la base de saisie a &eacute;t&eacute; r&eacute;alis&eacute; avec succ&egrave;s en ".$delaiExec." secondes.<br/>(Le fichier est disponible au t&eacute;l&eacute;chargement <a href=\"/work/export/SQL-bdppeao/".$zipFilelien."\">ici</a>.)<br/>";
						if ($EcrireLogComp ) {
							$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>). ";
						}
						$contenu .="</div>";
					} else {
						$contenu = "<div id=\"expResultat\">Erreur lors de l'export de la base de saisie : ".$erreur.".</div>";
					}
					break;
				case  "3": // sélection d'un ou plusieurs ecosytèmes
					
					if (isset($_GET["choixPays"])){
						if (isset($_GET["choixSysteme"])){
							$timer_debut = timer();
							$afficheNomSysteme = "";
							$choixPays=$_GET["choixPays"];
							$nomPays = getNomPays($choixPays,$connectPPEAO);
							// Les noms des systèmes
							$choixSysteme = $_GET["choixSysteme"];
							$SQLSysteme = "select libelle from ref_systeme where id in (".$choixSysteme.")";
							$SQLSystemeResult = pg_query($connectPPEAO,$SQLSysteme);
							$erreurSQL = pg_last_error($connectPPEAO);
							if ( !$SQLSystemeResult ) { 
								$erreur = "Erreur de lecture des systemes pour le pays  ".$row[1]." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
							} else {
								if (pg_num_rows($SQLSystemeResult) == 0) {
									// message d'avertissement ?
								} else {
									while ($row = pg_fetch_row($SQLSystemeResult) ) {
										if ($afficheNomSysteme =="")  {
											$afficheNomSysteme = $row[0];
										} else {
											$afficheNomSysteme .= ", ".$row[0];
										}
									}
								}
							}
							pg_free_result($SQLSystemeResult);
							
							// Lancement de la creation des SQL maintenant que le pays et les ecosystemes sont sélectionnés
							$contenu = "<div id=\"selComp\"><form ><h2>Vous voulez s&eacute;lectionner un ou plusieurs &eacute;cosyst&egrave;mes</h2><br/>
									Les donn&eacute;es ont &eacute;t&eacute; extraites pour les &eacute;cosyst&egrave;mes ".$afficheNomSysteme." du pays ".$nomPays.".
									<br/>";
									
							// Début du traitement effectif
							// Initialisation de la table de donnée
							$erreur  = initializeTempExtraction($connectPPEAO);
							// Extraction du referentiel et du parametrage
							if ($erreur == "") {
								$erreur  = getSQLExport($choixPays,$choixSysteme,"Tout",$connectPPEAO,"n");
							} 
							// Création du fichier SQL
							if ($erreur == "") {
								$erreur  = createSQLFile("bdppeao_a_importer",$connectPPEAO,"n");
							}
							// Calcul du temps d'execution
							$delaiExec = number_format(timer() - $timer_debut,1);
							if ($erreur == "") {
								$contenu .= "<div id=\"expResultat\">L'export de la base partielle pour le pays ".$nomPays." et les &eacute;cosyst&egrave;mes ".$afficheNomSysteme."  a &eacute;t&eacute; r&eacute;alis&eacute; avec succ&egrave;s en ".$delaiExec." secondes. <br/>(Le fichier est disponible au t&eacute;l&eacute;chargement <a href=\"/work/export/SQL-bdppeao/".$zipFilelien."\" target=\"blank\">ici</a>.)";
								if ($EcrireLogComp ) {
									$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>.) ";
								}
								$contenu .="<br/></div>";
							} else {
								$contenu .= "<div id=\"expResultat\">Erreur lors de l'export de la base partielle : ".$erreur;
								if ($EcrireLogComp ) {
									$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>.) ";
								}
								$contenu .="<br/></div>";
							}
						} else {
							// Le pays est choisie, on lance le choix de l'ecosystème						
							$choixPays=$_GET["choixPays"];
							$nomPays = getNomPays($choixPays,$connectPPEAO);
							if ($erreur == "") {
								// Choix des écosystèmes
								$contenu = "<div id=\"selComp\"><form ><h2>Vous voulez extraire une base restreinte :</h2>
									<h3>&Eacute;tape 2 - choix de ou des &eacute;cosyst&egrave;me(s) pour le pays ".$nomPays."</h3>
									<br/>";
								$SQLSysteme = "select id,libelle from ref_systeme where ref_pays_id ='".$choixPays."'";
								$SQLSystemeResult = pg_query($connectPPEAO,$SQLSysteme);
								$erreurSQL = pg_last_error($connectPPEAO);
								if ( !$SQLSystemeResult ) { 
									$erreur = "Erreur de lecture des systemes pour le pays  ".$nomPays." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
									
								} else {
									$cptSysteme=0;
									while ($row = pg_fetch_row($SQLSystemeResult) ) {
										if (strtoupper($row[1]) != "AUCUN" && strtoupper($row[1]) != "INCONNU" ) {
											$cptSysteme++;
											$contenu .="<input id=\"choixSysteme".$cptSysteme."\"  name=\"choixSysteme\" type=\"checkbox\" value=\"".$row[0]."\" />".$row[1]."	<br/>";		
										}
									}
									if ($cptSysteme ==0) {
										$contenu .="Il n'y a pas d'&eacute;cosyst&egrave;me pour le (la) : ".$nomPays.".";
									}
								}
								pg_free_result($SQLSystemeResult);
								$contenu .="<br/>&nbsp;<input type=\"button\" value=\"Valider mon choix\" onclick=\"javascript:doExportSelect('exportBaseRef','non');\"/>&nbsp;<input type=\"button\" value=\"Changer mon choix\" onclick=\"javascript:doExportSelect('exportBaseRef','oui');\"/>
								<br/>
								<input type=\"hidden\" id = \"choixPaysEC\" value=\"".$choixPays."\" />
								<input type=\"hidden\" id = \"choixExportEC\" value=\"".$choixExport."\" />
								<input type=\"hidden\" id = \"nbSysteme\" value=\"".$cptSysteme."\" />
								</form>
								</div>
								";	
							}
						}
					} else {
						// Choix du pays
						$contenu = "<div id=\"selComp\"><form ><h2>Vous voulez extraire une base restreinte :</h2>
								<h3>&Eacute;tape 1 - choix du pays</h3>
								<br/>";
						$SQLPays = "select id,nom from ref_pays ";
						$SQLPaysResult = pg_query($connectPPEAO,$SQLPays);
						$erreurSQL = pg_last_error($connectPPEAO);
						if ( !$SQLPaysResult ) { 
							$erreur = "Erreur de lecture de la table pays pour construire la liste des pays (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
							$contenu .=$erreur;
						} else {
							$cptPays=0;
							while ($row = pg_fetch_row($SQLPaysResult) ) {
								// On vérifie q'il y ait bien des écosystèmes pour ce pays.
								$SQLSysteme = "select id,libelle from ref_systeme where ref_pays_id ='".$row[0]."'";
								$SQLSystemeResult = pg_query($connectPPEAO,$SQLSysteme);
								$erreurSQL = pg_last_error($connectPPEAO);
								if ( !$SQLSystemeResult ) { 
									$erreur = "Erreur de lecture des systemes pour le pays  ".$row[1]." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
									
								} else {
									if (pg_num_rows($SQLSystemeResult) == 0) {
										// message d'avertissement ?
									} else {
										if (strtoupper($row[1]) != "AUCUN" && strtoupper($row[1]) != "INCONNU" ) {
											$cptPays++;
											$contenu .="<input id=\"choixPays".$cptPays."\"  name=\"choixPays\" type=\"radio\" value=\"".$row[0]."\" />".$row[1]."	<br/>";		
										}
									}
								}
								pg_free_result($SQLSystemeResult);
							}
						}
						pg_free_result($SQLPaysResult);
						$contenu .= "<br/>&nbsp;<input type=\"button\" value=\"Valider mon choix\" onclick=\"javascript:doExportSelect('exportBaseRef','non');\"/>
						<input type=\"hidden\" id = \"nbPays\" value=\"".$cptPays."\" />
						<input type=\"hidden\" id = \"choixExportEC\" value=\"".$choixExport."\" />
						<br/>
						</form>
						</div>
						";
					}
					break;
			}
		}
	break;
	case "videbdppeaoPC":
		$timer_debut = timer();
		//$connectPPEAOtest = pg_connect("host=".$hostname." port=".$port." dbname=bdppeao_test user=".$username." password=".$password."") or die('Connexion impossible a la base : ' . pg_last_error());
		$erreur = emptyDB("Tout",$connectPPEAO);
		$delaiExec = number_format(timer() - $timer_debut,1);
		if ($erreur == "") {
			$contenu = "<div id=\"videResultat\">La base ".$base_principale." a &eacute;t&eacute; vid&eacute;e avec succ&egrave;s en ".$delaiExec." secondes.";
			if ($EcrireLogComp ) {
				$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>.) ";
			}
			$contenu .="<br/></div>";
		} else {
			$contenu = "<div id=\"videResultat\">Erreur lors du vidage de la base ".$base_principale." : ".$erreur;
			if ($EcrireLogComp ) {
				$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>.) ";
			}
			$contenu .="<br/></div>";
		}
	break;
	case "majbdppeaoPC":
			set_time_limit(0);
			$timer_debut = timer();
			//$connectPPEAOtest = pg_connect("host=".$hostname." port=".$port." dbname=bdppeao_test user=".$username." password=".$password."") or die('Connexion impossible a la base : ' . pg_last_error());
			$fileSQLname = "bdppeao_a_importer.sql";
			$erreur = readAndRunSQL($fileSQLname,$connectPPEAO,"y");
			$delaiExec = number_format(timer() - $timer_debut,1);
			if ($erreur == "") {
				$contenu = "<div id=\"videResultat\">La base a &eacute;t&eacute; mise &agrave; jour avec succ&egrave;s en ".$delaiExec." secondes.";
				if ($EcrireLogComp ) {
					$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>.) ";
				}
				$contenu .="<br/></div>";
			} else {
				$contenu = "<div id=\"videResultat\">Erreur lors de la mise &agrave; jour de la base  : ".$erreur.".";
				if ($EcrireLogComp ) {
					$contenu .="<br/>(Le log se trouve <a href=\"/log/".$nomLogLien."\" target=\"log\">ici</a>.) ";
				}
				$contenu .="<br/></div>";
			}
	break;
	default: 
		break;
	
}

echo ($contenu);	


?>