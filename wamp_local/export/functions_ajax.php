<?php 
//*****************************************
// functions_ajax.php
//*****************************************
// Created by Yann Laurent
// 2011-01-19 : creation
//*****************************************
// Ce fichier contient une serie de fonctions php utilisées dans l'export vers les bases ACCESS
	$listeTablesRef="ref_pays,ref_systeme,ref_secteur,ref_categorie_ecologique,ref_categorie_trophique,ref_ordre,ref_famille,ref_espece,ref_origine_kb,art_categorie_socio_professionnelle,art_etat_ciel";
	$listeTablesParamExp = "exp_contenu,exp_debris,exp_engin,exp_force_courant,exp_position,exp_qualite,exp_remplissage,exp_sediment,exp_sens_courant,exp_sexe,exp_stade,exp_vegetation,exp_station";
	$listeTablesParamArt = "art_grand_type_engin,art_millieu,art_type_activite,art_type_agglomeration,art_type_sortie,art_type_engin,art_vent,art_agglomeration";
	$listeTablesDonneesExp="exp_environnement,exp_campagne,exp_coup_peche,exp_fraction,exp_biologie,exp_trophique";
	$listeTablesDonneesArt="art_unite_peche,art_lieu_de_peche,art_debarquement,art_debarquement_rec,art_engin_peche,art_fraction,art_poisson_mesure,art_activite,art_engin_activite,art_fraction_rec,art_periode_enquete";
	$listeTablesDonneesStat="art_stat_totale,art_stat_gt,art_stat_gt_sp,art_stat_sp,art_taille_gt_sp,art_taille_sp,art_stat_effort";

function getNomPays ($IDPays,$connectPPEAO)  {
	$nomPays ="";
	// On récupère le nom du pays
	$SQLPays = "select nom from ref_pays where id ='".$IDPays."'";
	$SQLPaysResult = pg_query($connectPPEAO,$SQLPays);
	$erreurSQL = pg_last_error($connectPPEAO);
	if ( !$SQLPaysResult ) { 
		$nomPays = "erreur de lecture du pays pour l'id = ".$IDPays." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
		
	} else {
		$row = pg_fetch_row($SQLPaysResult);
		$nomPays = $row[0];
	}
	pg_free_result($SQLPaysResult);
	return $nomPays;
	
}

//*********************************************************************
// GetTableStructure : renvoie la structure de la table
function GetTableStructure($TableEnCours,$connectPPEAO) {
// Cette fonction permet de recuperer la structure de la table en cours sous forme d'une liste de champs
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $TableEnCours : contient le nom de la table en cours
//*********************************************************************
// En sortie : 
// La fonction renvoie une chaine contenant une chaine avec les champs de la table separés par une virgule
// Les variables global $SQLTypeChamps  et $nbChampTable sont aussi mises a jour, ainsi que la position de l'ID
//*********************************************************************
	global $SQLTypeChamps;
	global $nbChampTable;
	global $positionIDTable;
	$SQLChamps= "";
	//echo "<b>Structure de la table ".$TableEnCours." </b><br/>";
	$sql = "SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_name='".$TableEnCours."'";
	$Result = pg_query($connectPPEAO,$sql);
	$erreurSQL = pg_last_error($connectPPEAO);
	if (!$Result) {
		$erreur =  "erreur lecture structure table ".$TableEnCours." - erreur complete = ".$erreurSQL."<br/>";
	} else {
		if (pg_num_rows($Result) == 0) {
				echo "pas de resultat lecture structure table ".$TableEnCours." <br/>";
			} else {
				$nbChampTable=0;
				while ($Row = pg_fetch_row($Result) ) {
					$SQLTypeChamps[$nbChampTable] = $Row[2];	
					//echo $Row[0]." - ".$Row[1]." - ".$Row[2]."  <br/>";
					if ($TableEnCours == "art_stat_effort") { // toutes les tables ont un id SAUF art_stat_effort....
						if ($Row[1] == "effort_id") {
							$positionIDTable = $nbChampTable;
						}
					} else {
						if ($Row[1] == "id") {
							$positionIDTable = $nbChampTable;
						}
					}
					if ($SQLChamps == "") {
						$SQLChamps = $Row[1];
					} else {
						$SQLChamps .= ",".$Row[1];
					}
					$nbChampTable ++;
				}
			}
	}
	pg_free_result($Result);	
	return $SQLChamps;
	
}
							 


//*********************************************************************
// initializeTempExtraction : vide la table temp_extraction
function initializeTempExtraction($connectPPEAO) {
// Cette fonction permet de vider la table temp_extraction pour preparer la creation des SQL
//*********************************************************************
// En entrée, les paramètres suivants sont :
// N/A
//*********************************************************************
// En sortie : 
// La fonction renvoie une chaine contenant une erreur sinon vide si pas d'erreur
//*********************************************************************
	$erreur = "";
	$SQLDel = "delete from temp_extraction";
		$SQLDelresult = pg_query($connectPPEAO,$SQLDel);
		$erreurSQL = pg_last_error($connectPPEAO);
		if ( !$SQLDelresult ) { 
			$erreur = "erreur Erreur delete temp_extraction , cette table n'existe peut etre pas dans votre base (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
		} 
		pg_free_result($SQLDelresult);
	return $erreur;
}

//*********************************************************************
// createSQLFile : creation du fichier SQL pour la creation de la base
function createSQLFile($fileSQLname, $connectPPEAO,$genereDelete) {
// Cette fonction permet de creer le fichier SQL a partir du contenu de la table temp_extraction
//*********************************************************************
// En entrée, les paramètres suivants sont :
// N/A
//*********************************************************************
// En sortie : 
// La fonction renvoie une chaine contenant une erreur sinon vide si pas d'erreur
//*********************************************************************
	global $zipFilelien;
	global $logComp;
	global $EcrireLogComp;
	global $pasdefichier;
	include $_SERVER["DOCUMENT_ROOT"].'/zip/archive.php';
	$erreur = "";
	if ($genereDelete =="y") {
		$fileSQLname = $fileSQLname.".sql"; // Dans le cas du traitement pour le portage partiel, comme le fichier n'est pas exporté, on ne le date pas, ni on ne le zip
	}else {
		$fileSQLname = $fileSQLname."_".date('y\-m\-d-H-i').".sql";
		$zipFilelien = $fileSQLname.".zip";
	}
	
	$fileSQL=$_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/".$fileSQLname;
	if (!(file_exists($fileSQL)) ) {
		$erreurDir = creeDirTemp($_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/");
		if (strpos($erreurDir,"erreur") === false ){
		} else {
			$erreur = $erreurDir;
		}
	} 
	$fileSQLopen=fopen($fileSQL,'w');
	rewind($fileSQLopen);	
	if ($genereDelete =="y") {
		// Creation demandée en parallele d'un fichier contenant la selection a supprimer.
		$fileSQLDEL=$_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/DEL-".$fileSQLname;
		if (!(file_exists($fileSQLDEL)) ) {
			$erreurDir = creeDirTemp($_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/");
			if (strpos($erreurDir,"erreur") === false ){
			} else {
				$erreur = $erreurDir;
			}
		} 
		$fileSQLDELopen=fopen($fileSQLDEL,'w');	
		rewind($fileSQLDELopen);
	}	
	if ($erreur =="") {
		$sql = "SELECT key1,key3,valeur_ligne FROM temp_extraction order by key1,key3";
		$Result = pg_query($connectPPEAO,$sql);
		$erreurSQL = pg_last_error($connectPPEAO);
		if (!$Result) {
			$erreur =  "erreur lecture temp_extraction - erreur complete = ".$erreurSQL."<br/>";
		} else {
			if (pg_num_rows($Result) == 0) {
				echo "Table temp_extraction vide<br/>";
			} else {
				while ($Row = pg_fetch_row($Result) ) {
					//echo $Row[0]." - ".$Row[1]." - ".$Row[2]." <br/>";
					$script = str_replace("##quot##","'",$Row[2]);
					if ($Row[1] == "DEL") {
						fwrite($fileSQLDELopen,$script."\r\n");
					} else {
						fwrite($fileSQLopen,$script."\r\n");
					}
				}
			}
		}
		pg_free_result($Result);
	}
	fclose($fileSQLopen);
	if ($genereDelete =="n") {
		// creation de l'archive 
		$zipFilename = $_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/".$zipFilelien;
		if (file_exists($zipFilename)) {
		// pas forcement necessaire, verifier que le x+ vide le fichier
			unlink($zipFilename);
		}
		$theZipFile=new zip_file($zipFilename);	
		//setting the zip options: write to disk, do not recurse directories, do not store path and do not compress
		$theZipFile->set_options(array('inmemory' => 0, 'recurse' => 0, 'storepaths' => 0, 'method'=>1));			
		
		$theZipFile->add_files($fileSQL);
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Ajout de ".$fileSQL." dans l'archive ".$zipFilename ,$pasdefichier);
		}	
		$theZipFile->create_archive();
	}
	
	
	return $erreur;
}

//*********************************************************************
// getSQLforRefParam : renvoie un script SQL contenant l'ensemble des données pour le référentiel et le paramétrage
function getSQLExport($restrictionPays,$restrictionSysteme,$typeAction,$connectPPEAO,$genereDelete) {
// Cette fonction permet de générer le code SQL contenant toutes les données de référentiel et de paramétrage de la base bdppeao
// Ce code est stocké dans la table temporaire tem_extraction
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $restrictionPays = id du pays sur lequel on effectue la selection. Si "", pas de restrictions
// $restrictionSysteme = id(s) du (es) systeme(s) du pays à extraire. Si "", pas de restrictions
// $typeAction = action: 'Tout'; extraire referentiel, parametrage et données
//						 'RefParam':  extraire referentiel et parametrage
// $connectPPEAO = la connexion a la base PPEAO
// $genereDelete = le SQL contenant la suppression des donnees dans le cas d'un portage partiel.
//*********************************************************************
// En sortie : 
// La fonction renvoie une chaine contenant une erreur sinon vide si pas d'erreur
//*********************************************************************
	global $listeTablesRef;
	global $listeTablesParamExp;
	global $listeTablesParamArt;
	global $listeTablesDonneesArt;
	global $listeTablesDonneesStat;
	global $listeTablesDonneesExp;
	global $compteRendu;
	global $SQLTypeChamps;
	global $nbChampTable;
	global $positionIDTable;
	global $logComp;
	global $nomLogLien;
	global $EcrireLogComp;
	global $pasdefichier;
	
	$erreur = "";
	$listeTablesDEL="";
	$nbTablesExportees = 0;
	$nbEnregLus = 0;
	$nbEnregExportes = 0;
	
	// Pour les tests on ecrit les erreurs dans un fichier log
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "***** Debut Lecture table et ajout dans temp_extraction données",$pasdefichier);
	}

	switch ($typeAction) {
		case "Tout": 
			set_time_limit(0);
			$listeTables= $listeTablesRef.",".$listeTablesParamExp.",".$listeTablesParamArt.",".$listeTablesDonneesExp.",".$listeTablesDonneesArt.",".$listeTablesDonneesStat;
			break;
		case "pechart": 
			set_time_limit(0);
			$listeTables= $listeTablesRef.",".$listeTablesParamArt.",".$listeTablesDonneesArt.",".$listeTablesDonneesStat;
			$listeTablesDEL=$listeTablesDonneesArt.",".$listeTablesDonneesStat;
			//$listeTables="exp_campagne";
			break;
		case "RefParam": 
			set_time_limit(90);
			$listeTables= $listeTablesRef.",".$listeTablesParamExp.",".$listeTablesParamArt;
			//$listeTables="ref_espece";
			break;
	}
	// tout d'abord on ajoute la desactivation des contraintes dans les sql
	//echo "1- desactivation triggers <br/>";
	$TableEnCours = explode(",",$listeTables);
	$nbrTable = count($TableEnCours)-1;
	for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
		$sql ="ALTER TABLE ".$TableEnCours[$cptTable]." DISABLE TRIGGER ALL;";
		$SQLInsert = "insert into temp_extraction (key1,key3,valeur_ligne) values ('1-REM-CONSTRAINTS','INSERT','".$sql."');";
		if ($genereDelete == "y") {
			$SQLInsert .= "insert into temp_extraction (key1,key3,valeur_ligne) values ('4-REM-CONSTRAINTS','DEL','".$sql."');";
		}
		$SQLInsertresult = pg_query($connectPPEAO,$SQLInsert);
		$erreurSQL = pg_last_error($connectPPEAO);
		if ( !$SQLInsertresult ) { 
			echo " Erreur insertion dans temp_extraction - sql = ".$SQLInsertresult." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
		} 
		pg_free_result($SQLInsertresult);
	}
	
	// On commence le traitement proprement dit
	$nbrTable = count($TableEnCours)-1;
	for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
		$SQLTypeChamps = array();
		$SQLChamps = "";
		$nbChampTable=0;
		$nbTablesExportees++;
		$nbEnregLus = 0;
		$nbEnregExportes = 0;
		$positionIDTable = 0;
		// Etape 1 : on recupere la structure de la table
		$SQLChamps = GetTableStructure($TableEnCours[$cptTable],$connectPPEAO);
		//echo "pos id = ".$positionIDTable."<br/>";
		// Etape 2 : on lit le contenu de la table et on cree les SQL
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Export de la table ".$TableEnCours[$cptTable],$pasdefichier);
		}
		//echo "Export de la table ".$TableEnCours[$cptTable]." <br/>";
		$restrictionEcosysteme ="";
		include $_SERVER["DOCUMENT_ROOT"].'/export/gestion_restriction_export.php';
		$sql = "SELECT ".$SQLChamps." FROM ".$TableEnCours[$cptTable]." ".$restrictionEcosysteme;
		//echo $sql."<br/>";
		$Result = pg_query($connectPPEAO,$sql);
		$erreurSQL = pg_last_error($connectPPEAO);
		if (!$Result) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "** Erreur lecture ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL,$pasdefichier);
			}
			$erreur =  "erreur lecture ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL."<br/>";
		} else {
			if (pg_num_rows($Result) == 0) {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "** info : Table ".$TableEnCours[$cptTable]." vide",$pasdefichier);
				} else {
					echo "Table ".$TableEnCours[$cptTable]." vide<br/>";
				}
			} else {
				while ($Row = pg_fetch_row($Result) ) {
					$SQLValues = "";
					$valeurID = $Row[1];
					$nbEnregLus++;
					for ($cptElement = 0;$cptElement < $nbChampTable;$cptElement++) {
							//echo $Row[$cptElement]." type = ".$SQLTypeChamps[$cptElement]." - ";
							$valAAjouter = "";
							// Analyse des types de variables et du contenu
							if (strpos($SQLTypeChamps[$cptElement],"char") === false && 
								strpos($SQLTypeChamps[$cptElement],"text") === false && 
								strpos($SQLTypeChamps[$cptElement],"date") === false && 
								strpos($SQLTypeChamps[$cptElement],"time") === false) {
								if ($genereDelete =="y" && $cptElement == $positionIDTable ) {
									// on prepare la suppression des données.
									if ($TableEnCours[$cptTable]=="art_stat_effort") {
										$SQLInsertDel = "delete from ".$TableEnCours[$cptTable]." where effort_id = ".$Row[$cptElement].";##FL##";
									} else {
										$SQLInsertDel = "delete from ".$TableEnCours[$cptTable]." where id = ".$Row[$cptElement].";##FL##";
									}
								
								}

								if ($Row[$cptElement] == "" || $Row[$cptElement] == null) {
									$valAAjouter = "null"; // Pour eviter des valeurs vides dans le script SQL qui donneraient values (,,,)
								} else {
									$valAAjouter = $Row[$cptElement];
								}
							} else {
								if ($genereDelete =="y" && $cptElement == $positionIDTable ) {
									// on prepare la suppression des données.
									$SQLInsertDel = "delete from ".$TableEnCours[$cptTable]." where id = ''".$Row[$cptElement]."'';##FL##";
								}
								if ($Row[$cptElement] == null ) {
									$valAAjouter = "null";
								} else {
									if ($Row[$cptElement] == "") {
										if (strpos($SQLTypeChamps[$cptElement],"date") === false && 
										strpos($SQLTypeChamps[$cptElement],"time") === false) {
											$valAAjouter = "''";
										} else {
											$valAAjouter = "null";
										}
									} else {
										$valQuot = str_replace("'","''",$Row[$cptElement]); // On remplace les quotes qui pourraient etre presentes dans la chaine
										$valAAjouter = "'".$valQuot."'";// une chaine est entouree de quotes dans le SQL
									}
								}
							}
							if ($SQLValues == "") {
								$SQLValues = $valAAjouter;
							} else {
								$SQLValues .= ",".$valAAjouter;
							}
					}
					//echo "<br/>insert into ".$TableEnCours[$cptTable]." (".$SQLChamps.") values (".$SQLValues.");<br/>";
					$valeurSQL= "insert into ".$TableEnCours[$cptTable]." (".$SQLChamps.") values (".$SQLValues.");##FL##";
					$valeurSQL = str_replace ("'","##quot##",$valeurSQL); // ca va eviter les problemes de quot dans le sql qu'on stocke. Ne pas oublier de les remplacer apres !!!!
					//if ($EcrireLogComp ) {
					//	WriteCompLog ($logComp, "** DEBUG ".$valeurSQL,$pasdefichier);
					//}
					$valeurID = str_replace ("'","",$valeurID); // pour avoir une insertion clean dans la table temp. Pas necessaire de garder les quotes, juste pour test.
					//  Etape 3: On stocke le SQL dans TempExtraction
					$SQLInsert = "insert into temp_extraction (key1,key2,key3,valeur_ligne) values ('2-".$TableEnCours[$cptTable]."','".$valeurID."','INSERT','".$valeurSQL."');";
					if (! strpos($listeTablesDEL,$TableEnCours[$cptTable]) === false) {
						$SQLInsert .= "insert into temp_extraction (key1,key2,key3,valeur_ligne) values ('5-".$TableEnCours[$cptTable]."','".$valeurID."','DEL','".$SQLInsertDel."');";
					}
					
					//echo $SQLInsert."<br/>";
					//if ($EcrireLogComp ) {
					//	WriteCompLog ($logComp, "** DEBUG ".$SQLInsert,$pasdefichier);
					//}
					$SQLInsertresult = pg_query($connectPPEAO,$SQLInsert);
					$erreurSQL = pg_last_error($connectPPEAO);
					if ( !$SQLInsertresult ) { 
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp, "** Erreur insertion dans temp_extraction - sql = ".$SQLInsertresult." (erreur compl&egrave;te = ".$erreurSQL.")",$pasdefichier);
						}
						echo " Erreur insertion dans temp_extraction - sql = ".$SQLInsertresult." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
					} else {
						$nbEnregExportes ++;
					}
					pg_free_result($SQLInsertresult);
				}
			}
		}
		pg_free_result($Result);		
		// compte rendu traitement pour la table		
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "   - Enregs lus dans la table             : ".$nbEnregLus,$pasdefichier);
			WriteCompLog ($logComp, "   - Enregs exportes dans temp_extraction : ".$nbEnregExportes,$pasdefichier);
		}
	} // fin du for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++)
	// A la fin de l'execution du SQL on reactive les contraintes:
	//echo "fin- reactivation triggers <br/>";
	$nbrTable = count($TableEnCours)-1;
	for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
		$sql ="ALTER TABLE ".$TableEnCours[$cptTable]." ENABLE TRIGGER ALL;";
		$SQLInsert = "insert into temp_extraction (key1,key3,valeur_ligne) values ('3-ADD-CONSTRAINTS','INSERT','".$sql."');";
		if ($genereDelete == "y") {
			// A nouveau insertion de la reactivation des contraintes pour finir la suppression des données
			$SQLInsert .= "insert into temp_extraction (key1,key3,valeur_ligne) values ('6-ADD-CONSTRAINTS','DEL','".$sql."');";
		}
		$SQLInsertresult = pg_query($connectPPEAO,$SQLInsert);
		$erreurSQL = pg_last_error($connectPPEAO);
		if ( !$SQLInsertresult ) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "** Erreur insertion dans temp_extraction - sql = ".$SQLInsertresult." (erreur compl&egrave;te = ".$erreurSQL.")",$pasdefichier);
			}
			echo " Erreur insertion dans temp_extraction - sql = ".$SQLInsertresult." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
		} 
		pg_free_result($SQLInsertresult);
	}

	return $erreur;
}
//*********************************************************************
// getSQLforRefParam : renvoie un script SQL contenant l'ensemble des données pour le référentiel et le paramétrage
function emptyDB($typeAction,$connectPPEAO) {
// Cette fonction permet de générer le code SQL contenant toutes les données de référentiel et de paramétrage de la base bdppeao
// Ce code est stocké dans la table temporaire tem_extraction
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $typeAction = action: 'Tout'; extraire referentiel, parametrage et données
//						 'RefParam':  extraire referentiel et parametrage
// $connectPPEAO = la connexion de la bd a supprimer (permet de choisir un nom different au besoin)

//*********************************************************************
// En sortie : 
// La fonction renvoie une chaine contenant une erreur sinon vide si pas d'erreur
//*********************************************************************
	global $listeTablesRef;
	global $listeTablesParamExp;
	global $listeTablesParamArt;
	global $listeTablesDonneesArt;
	global $listeTablesDonneesStat;
	global $listeTablesDonneesExp;
	global $compteRendu;
	global $logComp;
	global $nomLogLien;
	global $EcrireLogComp;
	global $pasdefichier;
	
	$nbTablesVidees = 0;
	
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "***** Debut vidage données de la base ".pg_dbname($connectPPEAO),$pasdefichier);
	}

	$erreur = "";

	switch ($typeAction) {
		case "Tout": 
			set_time_limit(0);
			$listeTables= $listeTablesRef.",".$listeTablesParamExp.",".$listeTablesParamArt.",".$listeTablesDonneesExp.",".$listeTablesDonneesArt.",".$listeTablesDonneesStat;
			//$listeTables="art_activite";
			break;
		case "RefParam": 
			$listeTables= $listeTablesRef.",".$listeTablesParamExp.",".$listeTablesParamArt;
			break;
	}
	// Etape 1 : on recupere la structure de la table
	$TableEnCours = explode(",",$listeTables);
	$nbrTable = count($TableEnCours)-1;
	//echo $listeTables."<br/>";
	// pour plus de sécurité on vire les contraintes
	for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
		$sql ="ALTER TABLE ".$TableEnCours[$cptTable]." DISABLE TRIGGER ALL;";
		$Result = pg_query($connectPPEAO,$sql);
		$erreurSQL = pg_last_error($connectPPEAO);
		if (!$Result) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "erreur disable trigger pour ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL,$pasdefichier);
			}
			$erreur =  "erreur disable trigger pour ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL."<br/>";
		} else {
			$compteRendu.=$TableEnCours[$cptTable]." vid&eacute; <br/>"; 
		}
		pg_free_result($Result);
	}
	
	for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
		//echo "<b>Table ".$TableEnCours[$cptTable]." videe</b><br/>";
		$nbTablesVidees++;
		$sql = "delete from ".$TableEnCours[$cptTable];
		$Result = pg_query($connectPPEAO,$sql);
		$erreurSQL = pg_last_error($connectPPEAO);
		if (!$Result) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "erreur vidage table ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL,$pasdefichier);
			}
			$erreur =  "erreur vidage table ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL."<br/>";
		} else {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "  - table ".$TableEnCours[$cptTable]." videe",$pasdefichier);
			}
			$compteRendu.=$TableEnCours[$cptTable]." vid&eacute; <br/>"; 
		}
		pg_free_result($Result);
	}
	// On rajoute ensuite les contraintes
	for ($cptTable = 0;$cptTable <= $nbrTable;$cptTable++) {
		$sql="ALTER TABLE ".$TableEnCours[$cptTable]." ENABLE TRIGGER ALL;";
		$Result = pg_query($connectPPEAO,$sql);
		$erreurSQL = pg_last_error($connectPPEAO);
		if (!$Result) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "erreur enable trigger pour ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL,$pasdefichier);
			}
			$erreur =  "erreur enable trigger pour ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL."<br/>";
		} else {
			$compteRendu.=$TableEnCours[$cptTable]." vid&eacute; <br/>"; 
		}
		pg_free_result($Result);
	}
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "***** Fin vidage données de la base ".pg_dbname($connectPPEAO),$pasdefichier);
		WriteCompLog ($logComp, "***** Nombre total tables videes =  ".$nbTablesVidees,$pasdefichier);
		WriteCompLog ($logComp, "***** erreur :  ".$erreur,$pasdefichier);
	}
	return $erreur;
}

//*********************************************************************
// getSQLforRefParam : renvoie un script SQL contenant l'ensemble des données pour le référentiel et le paramétrage
function readAndRunSQL($fileSQLname,$connectPPEAO,$runTestVide) {
// Cette fonction permet de générer le code SQL contenant toutes les données de référentiel et de paramétrage de la base bdppeao
// Ce code est stocké dans la table temporaire tem_extraction
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $fileName = Le fichier contenant les SQL
// $connectPPEAO= la connexion de la bd a supprimer (permet de choisir un nom different au besoin)
//*********************************************************************
// En sortie : 
// La fonction renvoie une chaine contenant une erreur sinon vide si pas d'erreur
//*********************************************************************
	global $logComp;
	global $nomLogLien;
	global $EcrireLogComp;
	global $pasdefichier;
	
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "***** Debut lecture fichier SQL données et import dans ".pg_dbname($connectPPEAO),$pasdefichier);
	}
	// Debut du traitement: 
	// test pour savoir si la base est vide
	if ($runTestVide == "y") {
		$sql = "SELECT * FROM ref_espece";
		//echo $sql."<br/>";
		$Result = pg_query($connectPPEAO,$sql);
		$erreurSQL = pg_last_error($connectPPEAO);
		if (!$Result) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "** Erreur lecture ref_espece pour test - erreur complete = ".$erreurSQL,$pasdefichier);
			}
			$erreur =  "erreur lecture ".$TableEnCours[$cptTable]." - erreur complete = ".$erreurSQL."<br/>";
		} else {
			if (pg_num_rows($Result) == 0) {
				
			} else {
				$erreur = "<br/>Attention, la base cible n'est pas vide. Merci de la vider avant de lancer l'int&eacute;gration de ce fichier";
				return $erreur;
				exit;			
			}
		}
	}
	$erreur="";
	$fileSQL=$_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/".$fileSQLname;
	if (!(file_exists($fileSQL)) ) {
		$erreur = "Le fichier ".$_SERVER["DOCUMENT_ROOT"]."/work/export/SQL-bdppeao/".$fileSQLname." n'est pas pr&eacute;sent sur le serveur. Merci de le copier dans le bon r&eacute;pertoire.<br/>";
		return $erreur;
		exit;
	} 
	$fileSQLopen=fopen($fileSQL,'r');
	$cptLigne = 0;
	$ligneEnConstruction = false;
	$NomTableEnCours = "";
	$nbTablesLues = 0;
	$nbEnregsLus = 0;
	$nbEnregsErreurs = 0;

	// Traitement du fichier
	while ( ($line = fgets($fileSQLopen)) !== false) {
		
		$cptLigne ++;
		// On peut avoir des instruction sur 2 lignes...
		if (strpos($line,"##FL##") === false && strpos($line,"ALL;") === false) {
			$sql .= $line;
			// On n'a pas la fin de la ligne
			$ligneEnConstruction = true;
			//echo "<b>En cours de construction</b> ".$sql."  <br/>";
		} else {
			if ($ligneEnConstruction) {
				// On finit de contruire la ligne
				$ligneEnConstruction = false;
				$sql .= $line;
				$cptLEC = 0;
			} else {
				$sql = $line;
			}
			
			//echo "1".$sql." fin SQL a excuter <br/>";
			$sql = str_replace("##FL##","",$sql);
			// pour l'instant, on n'a que du insert ou du delete
			if (strpos ($sql,"into") === false) { // c'est du DEL
				$extraitNomTable= strstr($sql,"(",true);
				$extraitNomTable= strstr($extraitNomTable,"from ");
				$extraitNomTable = str_replace("from ","",$extraitNomTable);			
			} else { // c'est de l'insert
				$extraitNomTable= strstr($sql,"(",true);
				$extraitNomTable= strstr($extraitNomTable,"into ");
				$extraitNomTable = str_replace("into ","",$extraitNomTable);
			}
			
			//echo "nom table lue = ".$extraitNomTable." tableEC = ".$NomTableEnCours." <br/>";
			if ( $NomTableEnCours == "") {
				$NomTableEnCours = $extraitNomTable;
			} else {
				if ($NomTableEnCours == $extraitNomTable) {
					$nbEnregsLus ++;
				} else {
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp, "  -  nb enregs lus = ".$nbEnregsLus." pour ".$NomTableEnCours."  (en erreur = ".$nbEnregsErreurs.")",$pasdefichier);
					}
					$nbEnregsLus=0;
					$nbEnregsErreurs=0;
					$nbTablesLues++;
					$NomTableEnCours = $extraitNomTable;
				}
			}
			//echo "2".$sql." fin SQL a excuter <br/>";
			$Result = pg_query($connectPPEAO,$sql);
			$erreurSQL = pg_last_error($connectPPEAO);
			if (!$Result) {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "Ligne ".$cptLigne." Erreur execution SQL ".$sql." - erreur complete = ".$erreurSQL."",$pasdefichier);
				}
				$nbEnregsErreurs ++;
				$erreur .=  "erreur execution SQL ".$sql." - erreur complete = ".$erreurSQL."<br/>";
			}
			$sql ="";
			pg_free_result($Result);
		}
		//  test
		//if ($cptLigne == 40) {
		//exit;	
		//}
	  //echo $line."<br>";
	}
	// A la fin de l'execution du SQL on ajoute un petit vaccuum pour eviter les problemes d'insertion massif d'enregs.:
	$sql = "VACUUM ANALYZE";
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "***** Excecution d'un VACUUM ANALYZE pour ".pg_dbname($connectPPEAO),$pasdefichier);
	}
	$Result = pg_query($connectPPEAO,$sql);
	$erreurSQL = pg_last_error($connectPPEAO);
	if (!$Result) {
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Erreur lors du vaccuum analyse - erreur complete = ".$erreurSQL."",$pasdefichier);
		}
		$nbEnregsErreurs ++;
		$erreur .=  "Erreur lors du vaccuum analyse  - erreur complete = ".$erreurSQL."<br/>";
	}
	pg_free_result($Result);
	
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "***** Fin lecture fichier SQL données et import dans ".pg_dbname($connectPPEAO),$pasdefichier);
		WriteCompLog ($logComp, "***** Nombre total tables lues et importees = ".$nbTablesLues,$pasdefichier);
		$erreurFic = str_replace ("<br/>","\r\n".date('y\-m\-d\-His')."- ",$erreur);
		WriteCompLog ($logComp, "***** erreur : ".$erreurFic,$pasdefichier);
	}
	return $erreur;
}
?>