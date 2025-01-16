<?php 
//*****************************************
// functions.php
//*****************************************
// Created by Yann Laurent
// 2008-07-07 : creation
//*****************************************
// Ce fichier contient une serie de fonctions php utilisées dans le portage automatique des bases de données



//*********************************************************************
// WriteCompLog : écrit dans le fichier de compte rendu de comparaison
function WriteCompLog ($fichierlog,$message,$PasAutorisation) {
// Cette fonction permet d'écrire le compte rendu de comparaison dans le fichier spécifique.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $fichierlog : le fichier log (la variable issue du fopen(flog)
// $message : le texte à écrire dans le fichier log
// PasAutorisation : variable pour test linux a priori toujours vrai
//*********************************************************************
// En sortie : 
// La fonction écrit le texte dans le fichier préfixé de la date et l'heure, suffixé d'un saut de ligne
//*********************************************************************
	if (! $PasAutorisation) {
		if (! fwrite($fichierlog,date('y\-m\-d\-His')."- ".$message."\r\n") ) {
			logWriteTo(7,"error","Erreur d'ajout dans le fichier de compte rendu (comparaison.php)","","","0");
		}
	}
}

//*********************************************************************
// WriteCompLog : écrit dans le fichier de script le script SQL
function WriteCompSQL ($fichierSQL,$script,$PasAutorisation) {
// Cette fonction permet de générer un fichier de script SQL lors de la comparaison des données.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $fichierSQL : le fichier log (la variable issue du fopen(flog)
// $script : le script à écrire dans le fichier log (attention, doit contenir le ";" en fin de script
// PasAutorisation : variable pour test linux a priori toujours vrai
//*********************************************************************
// En sortie : 
// La fonction écrit le texte dans le fichier le script, suffixé d'un saut de ligne
//*********************************************************************
	if (! $PasAutorisation) {
		if (! fwrite($fichierSQL,$script."\r\n") ) {
			logWriteTo(7,"error","Erreur d'ajout de script dans le fichier de script (comparaison.php)","","","0");
		} 
	}
}



//*********************************************************************
// GetSQL : génère le code SQL pour mettre à jour la table
function formatSQL($value,$fieldType) {
// Cette fonction permet de générer le code SQL en fonction de la table en entrée et du type d'action à mener.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $value : la valeur en entrée
// $fieldType : le type de la valeur
//*********************************************************************
// En sortie : 
// la valeur formatée pour le script SQL
//*********************************************************************
// Le SQL généré sera de la forme :
// Principalement si la valeur est du texte, alors, on ajoute des apostrophes autour.
$formattedValue = "";
if ( $fieldType == "integer" or $fieldType == "real") {
	if ($value == null) {
		$formattedValue = "NULL";
	} else {
		$formattedValue = $value;
	}
} else {
	if ($value == null) {
		$formattedValue = "NULL";
	} else {
		$value = str_replace("'","''",$value);
		$formattedValue = "'".$value."'";
	}
}	

return $formattedValue;
}

//*********************************************************************
// GetSQL : génère le code SQL pour mettre à jour la table
function GetSQL($SQLAction, $tableName, $whereStatement,$value,$connectionBD,$nomBD,$typeProcess,$PathFichierConf,$newID,$majID,$ListeTableIDPasNum,$debugBool,$start_time,$EcrireLogComp,$logComp,$pasdefichier) {
// Cette fonction permet de générer le code SQL en fonction de la table en entrée et du type d'action à mener.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLAction : quelle est l'action à faire : INSERT ou UPDATE
// $tableName : nom de la table qui subit l'action
// $whereStatement : quelle est la condition where à ajouter à l'action d'update ?
// $value : valeurs à maj (c'est un tableau issu d'un pg_fetch_row
// $connectionBD : connexion BD sur laquelle exécuter la requete pour le dico
// $nomBD : nom de la BD pour les logs
// $typeProcess : nom du processus qui appelle cette fonction pour decider si l'ID doit etre mis à jour
// $PathFichierConf : nom du fichier de conf
//*********************************************************************
// En sortie : 
// La fonction renvoie le code SQL prêt à être exécuté.
//*********************************************************************

// La liste des tables pour lesquelles l'ID peut etre modifie
$locTableMajID = GetParam("listeTableMajsc",$PathFichierConf);
$locTableMajID = $locTableMajID.",".GetParam("listeTableMajrec",$PathFichierConf);

$LocScriptSQL = "";
// Deux listes de noms de champs Up pour les updates, In pour les insert.
$LocListAttrUp = "";
$LocListAttrIn1 = "";
$LocListAttrIn2 = "";
$locNouvelID = 0;

$numChamp = 0;
// Etape 1 - on récupère tous les champs de la table à ajouter ou à mettre à jour
if ($debugBool) {
	$debugTimer = number_format(timer()-$start_time,4);
	echo "getSQL avant requete attr :".$debugTimer."<br/>";
}
$ListAttr="
select c.relname,a.attname,a.attnum,
pg_catalog.format_type(a.atttypid, a.atttypmod) as type
from pg_class as c, pg_attribute as a
where relname = '".$tableName."' and c.oid = a.attrelid and a.attnum > 0;";
// Lance la requete dans la base de reference (base source)
if (!$connectionBD) {
 	logWriteTo(7,"error","Erreur connexion ".$nomBD." dans la fonction getSQL de comparaison.php","","","0");
 }
$getAttrBD = pg_query($connectionBD,$ListAttr) or die('erreur dans la requete : '.pg_last_error());
if (pg_num_rows($getAttrBD) == 0) {
 	logWriteTo(7,"error","Erreur dans la lecture definition de la table ".$tableName." dans la BD ".$nomBD." (function // GetSQL portage automatique)","","","0");
} else {
	if ($debugBool) {
		$debugTimer = number_format(timer()-$start_time,4);
		echo "getSQL debut trt attr :".$debugTimer."<br/>";
	}
	// On recupere si necessaire l'ID
	while ($getAttrBDRow = pg_fetch_row($getAttrBD)) {
		// On n'ajoute pas le champs ID pour l'update
		if ($getAttrBDRow[1] =="id" && $SQLAction == "update") {
			continue;
		}
		// numChamp stocke le numéro d'ordre du champs
		$numChamp = $getAttrBDRow[2] - 1;

		// construit la liste des champs pour l'insert
		// Liste des colonnes
		$IDdanschp = strpos($getAttrBDRow[1],"_id");
		//echo $tableName." strpos = ".$IDdanschp."<br/>";
		//if (strpos($getAttrBDRow[1],"_id") > 0) {
		//	echo $tableName." strpos = ".$IDdanschp." ".$getAttrBDRow[1]."<br/>";
		//}
		//if ($tableName == "exp_coup_peche"){
		//echo $getAttrBDRow[1]." <br/>";
		//}
		if (($typeProcess == "majsc" || $typeProcess == "majrec") 
			&& ( strpos($getAttrBDRow[1],"_id") > 0 ) ){
			//echo "col = ".$getAttrBDRow[1]."<br/>";
			// On a fait un premier filtre sur tous les champs qui contiennent id 
			// MAIS qui ne sont pas l'ID de la table en cours
			// On controle ensuite que le champs n'est pas une reference a une table dont il faut changer l'ID
			$tableAControler = str_replace("_id","",$getAttrBDRow[1]);
			//
			$testID = strpos($locTableMajID ,$tableAControler);
			//echo $SQLAction." nomtable = ".$tableAControler." testID = ".$testID."<br/>";
			if ($testID === false) {
				// On garde la valeur du champ
				$valChamp = $value[$numChamp];
			} else {
				// il faut recuperer le nouvel ID
				//echo "faut recuperer l'ID<br/>";
				if (! $value[$numChamp] == null) {
				
					//echo "controle table = ".$tableAControler." value = ".$value[$numChamp]."<br/>";
					// Attention, on peut avoir des ID en char....
					$testTtypeVarID = strpos($ListeTableIDPasNum ,$tableAControler);
					if ($testTtypeVarID === false) {
						// L'ID est bien un numérique
					$recIDSQL = " select idcible from temp_recomp_id where nomtable = '".$tableAControler."' and Idsource = ".$value[$numChamp] ;	
					} else {
						// L'ID est une chaine
						$recIDSQL = " select idcibleChar from temp_recomp_id where nomtable = '".$tableAControler."' and IdsourceChar = '".$value[$numChamp]."'" ;
					}
					$recIDSQLResult = pg_query($connectionBD,$recIDSQL) or die('erreur dans la requete : '.pg_last_error());
					if (pg_num_rows($recIDSQLResult) == 0) {
						// on garde le meme ID
						$locNouvelID = $value[$numChamp];
						if ($EcrireLogComp ) { 
							WriteCompLog ($logComp,$tableAControler." - erreur lecture nouvel ID / on garde l'ancien ID = ".$value[$numChamp],$pasdefichier);
						} else {
						echo $tableAControler." - erreur lecture nouvel ID / on garde l'ancien ID = ".$value[$numChamp]."<br/>"; }
					} else {
						$recIDRow = pg_fetch_row($recIDSQLResult);
						$valChamp = $recIDRow[0];
						//echo "Ancienne valeur de l'ID pour ".$getAttrBDRow[1]." = ".$value[$numChamp]." nouvelle valeur = ".$recIDRow[0]."<br/>";
					}
					pg_free_result($recIDSQLResult);
				} else {
					$valChamp = $value[$numChamp]; // sera toujours NULL !
				}
			}
		} else {
			$valChamp = $value[$numChamp];
		}
		// On recupere si necessaire l'ID
		if ($majID == "y" && $getAttrBDRow[1] =="id") {
			if ($newID == null) {
				// on garde le meme ID
				$locNouvelID = $value[$numChamp];
			} else {

				$valChamp = $newID;

			}

		}
		if ($SQLAction=="insert") {
			if ($LocListAttrIn1 == "" ) {
				$LocListAttrIn1 = $getAttrBDRow[1];
			} else {
				$LocListAttrIn1.=",".$getAttrBDRow[1] ; 
			}
			// Liste des valeurs
			if ($LocListAttrIn2 == "" ) {
				$LocListAttrIn2 = formatSQL($valChamp,$getAttrBDRow[3]);
			} else {
				$LocListAttrIn2.=",".formatSQL($valChamp,$getAttrBDRow[3]) ; 
			}
		}
		// construit la liste des champs pour l'update
		if ($SQLAction=="update") {
			if ($LocListAttrUp == "" ) {
				$LocListAttrUp = $getAttrBDRow[1]."=".formatSQL($valChamp,$getAttrBDRow[3]) ;
			} else {
				$LocListAttrUp.=",".$getAttrBDRow[1]."=".formatSQL($valChamp,$getAttrBDRow[3]) ; 
			}	
		}
	}
	//logWriteTo(7,"notice",$SQLAction." pour ".$tableName." LocListAttr = ".$LocListAttrUp,"","","1");
	if ($debugBool) {
		$debugTimer = number_format(timer()-$start_time,4);
		echo "getSQL fin trt attr :".$debugTimer."<br/>";
	}


} 
//	echo $LocListAttrIn2."<br/>";
// Etape 2 - on construit l'instruction SQL complète.
switch ($SQLAction) {
	case "update":
		$LocScriptSQL ="update ".$tableName." set ".$LocListAttrUp." ".$whereStatement ;
		break;
	case "insert":
		$LocScriptSQL ="insert into ".$tableName." (".$LocListAttrIn1.") values (".$LocListAttrIn2.")";
		break;
	case "delete":
		$LocScriptSQL ="delete from ".$tableName." ".$whereStatement ;
		break;
} 

pg_free_result($getAttrBD);
return $LocScriptSQL;
}

//*********************************************************************
// runQuery : exécute une requete SQL en captant les erreurs
function runQuery($scriptSQLToRun,$connectionBD) {
// Cette fonction permet d'exécuter un script SQL en récupérant les erreurs dans le log.
// ELle va renvoyer l'état d'exécution de la requête. Aucun warning SQL ne sera affiché à l'écran.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $scriptSQLToRun : le script à exécuter.
// $connectionBD : la connection de BD sur laquelle lancer le script
//*********************************************************************
// En sortie : 
// La fonction renvoie si la requete a ete correctement exécutée.
//*********************************************************************
$runQueryOK = true;

$lev=error_reporting (8); //NO WARNING!!
$compINSResult = pg_query($connectionBD,$scriptSQLToRun);
error_reporting ($lev); //DEFAULT!!
if (strlen ($r=pg_last_error ($connectionBD))) {
	$runQueryOK = false;
	logWriteTo(7,"error","erreur execution : '".$scriptSQLToRun."'" ,"message = ".$r,"","0");
}

return $runQueryOK;

}

//*********************************************************************
// ExecQueryDG : exécute une requete SQL en captant les erreurs
function ExecQueryDG($LocScriptSQL,$LocconnectionBD,$LocdebugAff,$Locstart_while,$LocEcrireLogComp,$Locpasdefichier,$LocnomTable) {
// Cette fonction permet d'exécuter un script SQL avec la possibilite de debugger.
// 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $LocScriptSQL : le script à exécuter.
// $LocconnectionBD : la connection de BD sur laquelle lancer le script
// $LocdebugAff : flag du debug
// $Locstart_while : temps de depart du timer
// $LocEcrireLogComp : est-ce qu'on ecrit dans le fichier log comp
// $Locpasdefichier : param supp de debug
// $LocnomTable : nom de la table en cours
//*********************************************************************
// En sortie : 
// La fonction renvoie si la requete a ete correctement exécutée.
//*********************************************************************
	$ErreurQuery = false;
	if ($LocdebugAff==true) {
		$LocdebugTimer = number_format(timer()-$Locstart_while,4);
		echo "Appartenance avant requete ".$LocnomTable." :".$LocdebugTimer."<br/>";
	}

	$LocscriptSQLResult = pg_query($LocconnectionBD,$LocScriptSQL);
	$LocErreurSQL = pg_last_error($LocconnectionBD);
	if (!$LocscriptSQLResult) {
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp,"Erreur execution requete ".$LocScriptSQL." (erreur =".$LocErreurSQL.")",$Locpasdefichier);
		}
		logWriteTo(7,"error","erreur execution : '".$LocScriptSQL."'" ,"message = ".$LocErreurSQL,"","0");
		$ErreurQuery = true;
	}
	if ($LocdebugAff==true) {
		$LocdebugTimer = number_format(timer()-$Locstart_while,4);
		echo "Appartenance apres requete ".$LocnomTable." :".$LocdebugTimer."<br/>";
	}
	return $ErreurQuery;
}



//*********************************************************************
// runQuery : exécute une requete SQL en captant les erreurs
function stockQuery($scriptSQLToRun,$ScriptComplet) {
// Cette fonction permet d'exécuter un script SQL en récupérant les erreurs dans le log.
// On utilise le double ; comme séparateur pour eviter lors de l'execution pas a pas du SQL
// par un explode les cas ou un ; est present dans le champ memo
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $scriptSQLToRun : le script à exécuter.
// $ScriptComplet : le script contenant l'ensemble des requetes
//*********************************************************************
// En sortie : 
// La fonction renvoie la variable contenant l'ensemble des requetes a executer
//*********************************************************************

$locScript =$ScriptComplet.$scriptSQLToRun.";;";

return $locScript;
}



/**
 * Print out debug info (including arrays)
 */
function print_debug($dbgstr0){
    ob_start();
    print_r($dbgstr0);
    $dbgstr = ob_get_contents();
    ob_end_clean();   
    
    $fpOut = fopen("error.log2", "a+");
    fwrite($fpOut, "\n$dbgstr");
    fclose($fpOut);
}

function getTime() {
    static $timer = false, $start;
    if ($timer === false) {
        $start = array_sum(explode(' ',microtime()));
        $timer = true;
        return NULL;
    } else {
        $timer = false;
        $end = array_sum(explode(' ',microtime()));
        return round(($end - $start) * 1000, 3);
    }
}

// Function timer() : pour gérer un chronomètre 
function timer(){ //chronomètre - http://www.phpcs.com/code.aspx?ID=32471
	$time=explode(' ',microtime());
	return $time[0] + $time[1];
} 

//*********************************************************************
// GetParam : recupere dans le fichier de configuration la valeur d'un parametre
function GetParam($nomParam,$nomFichier) {
// Cette fonction permet de lire le fichier de parametrage et de renvoyer la valeur du parametre
// Si le parametre n'existe pas dans le fichier, on renvoie false.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $nomParam : nom du paramètre à extraire.
// $nomFichier : nom du fichier de paramètre à ouvrir
//*********************************************************************
// En sortie : 
// La fonction renvoie la valeur du paramètre (string) sinon false si le paramètre n'est pas trouvée
//*********************************************************************

if ( file_exists($nomFichier)) {

	$ficParam = fopen($nomFichier,"r");
	while (!feof($ficParam)) {
   		$ficLine = fgets($ficParam);
		if (substr  ( $ficLine  , 0,1  ) ==";") {
			continue;
		} ;
		$posParam = strpos($ficLine,$nomParam);
		if ( $posParam === false ){
		// Le paramètre n'est pas trouvé dans le fichier
			$ParamValue = false;
		} else {
		// On a trouve le paramètre, on récupère sa valeur
		// Cette valeur est comprise en le signe = et ;
			$longNomParam = strlen($nomParam) +1;
			$valeurParam = substr  ( $ficLine  , $longNomParam  );
			$ParamValue = str_replace(";","",$valeurParam);
			$ParamValue = trim( $ParamValue);
			break;
		}
	} 
	fclose($ficParam);
} else {
	$ParamValue = false;
}
return $ParamValue;
}


//*********************************************************************
// RestoreBD : script de restauation d'un base de données à partir de sa sauvegarde
function RestoreBD($CRexecution,$connectRestaure,$baseRestaure,$baseBackup,$host,$user,$passwd,$port) {
// Cette fonction permet de restaurer à partir d'une base de sauvegarde
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $CRexecution : le compte rendu de traitement
// $connectRestaure : la connexion a la base a restaurer
// $baseRestaure : la base à restaurer
// $baseBackup : la base de sauvegarde
// $host,$user,$passwd : les paramètres de connection aux bases de données
//*********************************************************************
// En sortie : 
// La fonction renvoie le statut de l'execution de la sauvegarde
//*********************************************************************
	$ErreurProcess = false;

	set_time_limit(0);
	// On attend un peu pour eviter les blocages sur les bases a copier...
	// Le create database with template est assez sensible aux locks et aux transactions
	// encore en cours sur la base a copier (le create with template n'est pas forcement
	// concu pour ca initialement...)
	sleep(20);
	//$lev=error_reporting (8); //Pour eviter les avertissements si la base n'existe pas.
	if (! pg_close($connectRestaure) ) {
	
		$traitementfin = false;
		$CRexecution .= "Erreur fermeture connexion a PPEAO. <br/>La restauration devra se faire a la main.<br/>";
		$ErreurProcess = true;
	} else {
		$connectBackup =pg_connect ("host=".$host." port=".$port." dbname=".$baseBackup." user=".$user." password=".$passwd);
		$dropBDSQL = "drop database \"".$baseRestaure."\"";
		$dropBDResult = pg_query($connectBackup,$dropBDSQL);
		$erreurQuery = pg_last_error($connectBackup);
		if ($dropBDResult) {
			pg_free_result($dropBDResult);
			$traitementfin = false;
			$CRexecution .= "Base principale ".$baseRestaure." supprimee.<br/>";
			// On continue le traitement
			// Copie de la base backup en base principale
			$createBDSQL = "create database \"".$baseRestaure."\" with template \"".$baseBackup."\"";
			$createBDResult = pg_query($connectBackup,$createBDSQL);
			$erreurQuery = pg_last_error($connectBackup);
			if (!$createBDResult) {
				// c'est certainement un probleme de lock...
				// On attend encore plus
				sleep(40);
				$createBDSQL = "create database \"".$baseRestaure."\" with template \"".$baseBackup."\"";
				$createBDResult = pg_query($connectBackup,$createBDSQL);
				$erreurQuery = pg_last_error($baseBackup);
				if (!$createBDResult) {
					$ErreurProcess = true;
					$CRexecution .= "La base principale ".$baseRestaure." n'a pas pu etre recree au deuxieme essai.<br/> Contactez votre administrateur pour recreer la base.<br/>";
				} else {
					$CRexecution .= "Base principale ".$baseRestaure." recr&eacute;e au deuxieme essai.<br/>";
					$traitementfin = true;
				}
			} else {
				// On autorise le dernier traitement
				$CRexecution .= "Base principale ".$baseRestaure." recree.<br/>";
				$traitementfin = true;
			}
			// Lancement du dernier traitement : drop de la base PPEAO
			if ($traitementfin) {
				$connectRestaure = pg_connect("host=".$host." port=".$port." dbname=".$baseRestaure." user=".$user." password=".$passwd."") or die('Connexion impossible a la base : ' . pg_last_error());
				if (! pg_close($connectBackup) ) {
					$traitementfin = false;
					$CRexecution .= "Erreur fermeture connexion a BACKUP. <br/>La suppression de ".$baseBackup." devra se faire a la main.<br/>";
					$ErreurProcess = true;
				} else {
					$dropBDSQL = "drop database \"".$baseBackup."\"";
					$dropBDResult = pg_query($connectRestaure,$dropBDSQL);
					$erreurQuery = pg_last_error($connectRestaure);
					if ($dropBDResult) {
						$CRexecution .= "Fin du process de restauration : suppression de ".$baseBackup.".<br/>";
					} else {
						$CRexecution .= "Erreur suppression ".$baseBackup.". La suppression de la base de sauvegarde devra se faire a la main. (erreur complete = ".$erreurQuery .")<br/>";
						$ErreurProcess = true;
					}
				}
			} // fin du if ($traitementfin)
		} else {
			$CRexecution .= "Erreur suppression ".$baseRestaure.". La restauration devra se faire a la main. (erreur complete = ".$erreurQuery .")<br/>";
			$ErreurProcess = true;
		} // fin du if ($dropBDResult)
		// Meme chose pour la base de portage... 
		
	} // fin du if (! pg_close($connectRestaure) )
	//error_reporting ($lev); // retour au avertissements par defaut
return $ErreurProcess;

}

?>