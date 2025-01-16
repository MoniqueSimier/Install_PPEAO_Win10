<?php 
//*****************************************
// functions.php
//*****************************************
// Created by Yann Laurent
// 2008-12-10 : creation
//*****************************************
// Ce fichier contient une serie de fonctions php utilisées dans l'export vers les bases ACCESS

//*********************************************************************
// getTableNamePostGRE : permet de recuperer juste le nom postgres de la table ACCESS
function getTableNamePostGRE($fichier,$nomTable) {
// Cette fonction permet de générer le code SQL en fonction de la table en entrée et du type d'action à mener.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $fichier : le nom du fichier xml a lire
// $nomTable : nom de la table pour filtrer les valeurs que l'on renvoie
//*********************************************************************
// En sortie : 
// La fonction renvoie un tableau contenant le nom de la table, le nom POSTGRE et les listes des champs 
// sous forme d'une portion d'XML
//*********************************************************************
$nomTablePostGRE = "";
if($chaine = @implode("",@file($fichier))) {
	// on explode sur <item>
	$tmp = preg_split("/<\/?table>/",$chaine);
	// pour chaque <item> 
	for($i=1;$i<sizeof($tmp)-1;$i+=2) {
		$majok = false;
		// on lit les champs demandés <champ> 
		$champs = array("tablename","postgretablename");
		foreach($champs as $champ) {
			$tmp2 = preg_split("/<\/?".$champ.">/",$tmp[$i]);
			// on ajoute l'élément au tableau
			if ($champ == "tablename" && ($tmp2[1] == $nomTable)) {
				$majok = true;
			} else {
				if ($majok) {
					$nomTablePostGRE = @$tmp2[1];
				} else {
					continue;
				}					
			}
		}
	}
}

return $nomTablePostGRE;
}


//*********************************************************************
// litXML : lit un fichier XML contenant la descritpion des tables ACCESS et leur contrepartie POSTGRESQL
function litXML($fichier,$item,$champs,$nomTable) {
// Cette fonction permet de générer le code SQL en fonction de la table en entrée et du type d'action à mener.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $fichier : le nom du fichier xml a lire
// $item : l'element a partir duquel lire l'arbre XML
// $champs : la liste des champs a lire
// $nomTable : nom de la table pour filtrer les valeurs que l'on renvoie
//*********************************************************************
// En sortie : 
// La fonction renvoie un tableau contenant le nom de la table, le nom POSTGRE et les listes des champs 
// sous forme d'une portion d'XML
//*********************************************************************
	$tmp3= array();
	$Jaitrouvelatable = false;
	// on lit le fichier
	if($chaine = @implode("",@file($fichier))) {
		// on explode sur <item>
		$tmp = preg_split("/<\/?".$item.">/",$chaine);
		// pour chaque <item> 
		for($i=1;$i<sizeof($tmp)-1;$i+=2) {
			$majok = false;
			// on lit les champs demandés <champ> 
			foreach($champs as $champ) {
				$tmp2 = preg_split("/<\/?".$champ.">/",$tmp[$i]);
				// on ajoute l'élément au tableau
			
				if ($champ == "tablename" && ($tmp2[1] == $nomTable)) {
					$tmp3[$i-1][] = @$tmp2[1];
					$majok = true;
					$Jaitrouvelatable = true;
				} else {
					if ($majok) {
						$tmp3[$i-1][] = @$tmp2[1];
					} else {
						continue;
					}					
				}
			}
		}
		// et on retourne le tableau dans la fonction
		if (!$Jaitrouvelatable) {

			$tempArray[0][0] = "*-ERREUR*- pas de definition de la structure de la table ".$nomTable." dans le fichier XML /conf/AccessConv.xml";
			return $tempArray;
		} else {
			foreach($tmp3 as $row) {
				$tableNamePostGRE = $row[1];
				$ListAttr = $row[2];
			}
			// On reconstruit la liste des champs a partir de la partie d'xml renvoyé par la fonction litXML.
			$champ = "champ";
			$tempval = preg_split("/<\/?champ>/",$ListAttr);
			$cptArr = 0;
			for($cpt=1;$cpt<sizeof($tempval)-1;$cpt+=2) {
				$tempval2 = preg_split("/<\/?name>/",$tempval[$cpt]);
				$tempArray[$cptArr][0] = $tempval2[1];
				$tempval2 = preg_split("/<\/?type>/",$tempval[$cpt]);
				$tempArray[$cptArr][1] = $tempval2[1];
				$tempval2 = preg_split("/<\/?postgrename>/",$tempval[$cpt]);
				$tempArray[$cptArr][2] = $tempval2[1];	
				$tempval2 = preg_split("/<\/?postgretype>/",$tempval[$cpt]);	
				$tempArray[$cptArr][3] = $tempval2[1];
				// Gestion d'une valeur par defaut
				if (strpos($tempval[$cpt],"valdefaut") === false) {
					$tempArray[$cptArr][4] = "rien";
				} else {
					$tempval2 = preg_split("/<\/?valdefaut>/",$tempval[$cpt]);	
					$tempArray[$cptArr][4] = $tempval2[1];
				}
				// Gestion d'une lecture d'une valeur venant d'une autre table
				// Le nom de la table alternative
				if (strpos($tempval[$cpt],"autretablename") === false) {
					$tempArray[$cptArr][5] = "rien";
				} else {
					$tempval2 = preg_split("/<\/?autretablename>/",$tempval[$cpt]);	
					$tempArray[$cptArr][5] = $tempval2[1];
				}
				// Le nom de la clé unique pour la recherche de la donnée dans la base access
				if (strpos($tempval[$cpt],"autretableidAccess") === false) {
					$tempArray[$cptArr][6] = "rien";
				} else {
					$tempval2 = preg_split("/<\/?autretableidAccess>/",$tempval[$cpt]);	
					$tempArray[$cptArr][6] = $tempval2[1];
				}
				// Le nom de la clé unique pour la recherche de la donnée dans la base postgre
				if (strpos($tempval[$cpt],"autretableidPostgre") === false) {
					$tempArray[$cptArr][7] = "rien";
				} else {
					$tempval2 = preg_split("/<\/?autretableidPostgre>/",$tempval[$cpt]);	
					$tempArray[$cptArr][7] = $tempval2[1];
				}
				// Le nom du champ de la table alternative a partir duquel on va lire la donnée
				if (strpos($tempval[$cpt],"autrenom") === false) {
					$tempArray[$cptArr][8] = "rien";
				} else {
					$tempval2 = preg_split("/<\/?autrenom>/",$tempval[$cpt]);	
					$tempArray[$cptArr][8] = $tempval2[1];
				}				

				$cptArr++;
			}
			return $tempArray;
		}
	}
}

//*********************************************************************
// GetSQL : génère le code SQL pour mettre à jour la table
function GetSQLACCESS($SQLAction, $tableName, $whereStatement,$value,$BDType,$nomTableACCESS,$connACCESS,$connPOST,$TypePeche) {
// Cette fonction permet de générer le code SQL en fonction de la table en entrée et du type d'action à mener.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLAction : quelle est l'action à faire : INSERT ou UPDATE
// $tableName : nom de la table qui subit l'action
// $whereStatement : quelle est la condition where à ajouter à l'action d'update ?
// $value : valeurs à maj (c'est un tableau issu d'un pg_fetch_row)
// $BDType : soit pour maj ACCESS soit POSTGRES
// $nomTableACCESS: nom de la table pour la recherche dans le fichier XML
// $connACCESS: connection a la base ACCESS de travail
// $connPOST: connection a la base PPEAO de reference
//*********************************************************************
// En sortie : 
// La fonction renvoie le code SQL prêt à être exécuté.
//*********************************************************************

$LocScriptSQL = "";
// Deux listes de noms de champs Up pour les updates, In pour les insert.
$LocListAttrUp = "";
$LocListAttrIn1 = "";
$LocListAttrIn2 = "";
$locNouvelID = 0;
$LocFlagSPEC = false;
$numChamp = 0;
// Etape 1 - on récupère tous les champs de la table à ajouter ou à mettre à jour

// Lecture du du fichier XML et recuperation de la liste des champs (champ1 ACCESS, nom champ1 dans la table POSTGRE,
// champ2 ACCESS, nom champ2 dans la table POSTGRE, etc...)

$ficXMLDef = $_SERVER["DOCUMENT_ROOT"]."/conf/AccessConv".$TypePeche.".xml";
$tempArray = litXML($ficXMLDef,"table",array("tablename","postgretablename","champs"),$nomTableACCESS);
$testPos = strpos($tempArray[0][0] ,"*-ERREUR*-" );
;
if ($testPos === false ) {

} else {
	return $tempArray[0][0];
}
$Attr = $tempArray;
$nbAttr = count($Attr) - 1;
for ($cpt = 0; $cpt <= $nbAttr; $cpt++) {
	// on recupère le nom des champs 
	$NomChampACCESS = $Attr[$cpt][0];
	$typeChampACCESS = $Attr[$cpt][1];
	$NomChampPOSTGRE = $Attr[$cpt][2];
	$typeChampPOSTGRE = $Attr[$cpt][3];
	$valeurDefaut = $Attr[$cpt][4];
	// selon la base a maj, ce ne sont pas les memes noms de champs
	switch ($BDType) {
		case "ACCESS" : 
			$nomChampSQL = $NomChampACCESS;
			break;
		case "POSTGRE" : 
			$nomChampSQL = $NomChampPOSTGRE;
			break;
	}
	switch ($NomChampPOSTGRE) {
		case "CALCULAUTO" :
			// On doit generer un ID a partir de la derniere valeur de la table ACCESS
			$countSQL = "select max(cint(".$NomChampACCESS.")) from ".$nomTableACCESS;
			$countSQLResult = odbc_exec($connACCESS,$countSQL);
			$erreurSQL = odbc_errormsg($connACCESS); //
			if (! $countSQLResult) {
				echo "erreur dans ".$countSQL." recacul ID dans GetSQLACCESS<br/>";
				$cptSQLErreur ++;
			} else {
				$row = odbc_fetch_row($countSQLResult);
				$valChampSQL = intval(odbc_result($countSQLResult,1)) + 1 ;
			}
			break;
		case "AUTRETABLE" :
			// On doit recuperer la valeur dans une autre table (uniquement valable pour POSTGRE)
			//	<postgreinfocomp>
			//		<autretablename>ref_famille</autretablename>				==> 5 	[nom table]
			//		<autretableidAccess>CodeFamille</autretableidAccess>		==> 6	[nom ID ACCESS]
			//		<autretableidPostgre>ref_famille_id</autretableidPostgre>	==> 7	[nom ID postgre]
			//		<autrenom>non_poisson</autrenom>							==> 8	[nom champ]
			//	</postgreinfocomp>
			if ($SQLAction=="insert") {
				// On va recuperer dans la liste des attributs la position du champs de reference pour ensuite lire la valeur. C'est un peu rsustre comme evaluation mais c'est fait assez peu souvent.			
				$listeChamps = explode(",",$LocListAttrIn1);
				$nbChamps = count($listeChamps ) - 1;
				for ($cpt1 = 0; $cpt1 <= $nbChamps; $cpt1++) {
					if ($listeChamps[$cpt1] == $Attr[$cpt][6]) {
						break;
					}
				}
				$listeValeur = explode(",",$LocListAttrIn2);
				$valeurID = $listeValeur[$cpt1];
				$LocSQL = "select ".$Attr[$cpt][8]." from ".$Attr[$cpt][5]." where id = ".$valeurID ;
				$LocResult = pg_query($connPOST,$LocSQL);
				$LocerreurSQL = pg_last_error($connPOST);
				if (!$LocResult) {
					echo $Attr[$cpt][6]." <br/>";
					echo "erreur lecture base post gre pour table ".$Attr[$cpt][5]." (SQL = ".$LocSQL.") - erreur complete = ".$LocerreurSQL."<br/>";
				} else {
					if (pg_num_rows($LocResult) == 0) {
						echo "pas de resultat lecture base post gre pour table ".$Attr[$cpt][5]."<br/>";
					} else {
						$LocRow = pg_fetch_row($LocResult);
						$valChampSQL = $LocRow[0];
					}
				}
			} 
			break;
		case "SPEC" :
			// On met à jour le flag SPEC ==> un traitement particulier doit être fait.
				$LocFlagSPEC = true;
			break;
		default:
			if ($valeurDefaut == "rien") {
				if ($nomTableACCESS == "CoefficientKb") {
				//echo "valeur ".$nomChampSQL." = ".$value[$nomChampSQL]."<br/>";
				// Gestion du cas particulier des coefficients (on aurait du mettre un champ de plus dans le fichier XML pour le gerer de facon global, mais pas le temps pour l'instant....
				// Si null alors on met a zéro
					switch ($nomChampSQL) {
						case "coefficient_k": 
							if ($value[$nomChampSQL] == null ) {
								$valChamp = 0;
								$valChampSQL = 0;
							} else  {
								$valChamp = $value[$nomChampSQL];
								$valChampSQL = formatSQL($valChamp,$typeChampACCESS);
							}
							break;
						case "coefficient_b":
							if ($value[$nomChampSQL] == null ) {
								$valChamp = 0;
								$valChampSQL = 0;
							} else  {
								$valChamp = $value[$nomChampSQL];
								$valChampSQL = formatSQL($valChamp,$typeChampACCESS);
							}
							break;
	
						case "ref_origine_kb_id": 
							if ($value[$nomChampSQL] == null ) {
								$valChamp = 0;
								$valChampSQL = 0;
							} else  {
								$valChamp = $value[$nomChampSQL];
								$valChampSQL = formatSQL($valChamp,$typeChampACCESS);
							}
							break;
						default :
							$valChamp = $value[$nomChampSQL];
							$valChampSQL = formatSQL($valChamp,$typeChampACCESS);
					}

				} else {
					$valChamp = $value[$nomChampSQL];
					$valChampSQL = formatSQL($valChamp,$typeChampACCESS);
				}
			} else {
				switch ($valeurDefaut) {
					case "null": 
						$valChampSQL = "NULL";
						break;
					case "vide" : 
						if ($typeChampACCESS == "integer" || $typeChampACCESS == "real") {
							$valChampSQL = 0; 
						} else {
							$valChampSQL = "' '";
						}
						break;
					default : 

						$valChampSQL = $valeurDefaut;
						break;	
				}
				//echo "val defaut SQL = ".$valChampSQL."<br/>";
			}
			break;
	}		

	//echo $NomChampACCESS." ".$valChamp."<br/>";	
	if ($SQLAction=="insert" && !($NomChampPOSTGRE=="SPEC")) {
		if ($LocListAttrIn1 == "" ) {
			$LocListAttrIn1 = $NomChampACCESS;
		} else {
			$LocListAttrIn1.=",".$NomChampACCESS ; 
		}
		// Liste des valeurs
		// Petite astuce pour protéger les virgules
		// C'est dans le cas ou on a besoin de reconstruire les valeurs (le explode sur la virgule juste avant peut merder.
		$valChampSQL = str_replace(",","#-#",$valChampSQL);
		if ($LocListAttrIn2 == "" ) {
			$LocListAttrIn2 = $valChampSQL;
		} else {
			$LocListAttrIn2.=",".$valChampSQL ; 
		}
	}
	// construit la liste des champs pour l'update
	// Pas appeler....
	
	if ($SQLAction=="update" && !($NomChampPOSTGRE=="SPEC")) {
		if ($LocListAttrUp == "" ) {
			$LocListAttrUp = $NomChampACCESS."=".$valChampSQL ;
		} else {
			$LocListAttrUp.=",".$NomChampACCESS."=".$valChampSQL ; 
		}	
	}
}
// Etape 2 - on construit l'instruction SQL complète.
// On purge d'eventuelles champs _TEMP_ que l'on a stocké pour faire des recherches complémentaires.
$flagTemp = false;
if (strpos($LocListAttrIn1,"_TEMP_") === false) {

} else {
	$flagTemp = true;
	$listeTotAtt1 = explode(",",$LocListAttrIn1);
	$LocListAttrIn1 = "";
	$listeTotAtt2 = explode(",",$LocListAttrIn2);
	$LocListAttrIn2 = "";
	$nbChamps = count($listeTotAtt1 ) - 1;
	for ($cptAtt = 0; $cptAtt <= $nbChamps; $cptAtt++) {
		//echo $cptAtt." ".$listeTotAtt1[$cptAtt]."<br/>";
		if (strpos($listeTotAtt1[$cptAtt],"_TEMP_") === false) {
			// on reconstruit la liste
			if ($LocListAttrIn1 == "" ) {
				$LocListAttrIn1 = $listeTotAtt1[$cptAtt];
			} else {
				$LocListAttrIn1.=",".$listeTotAtt1[$cptAtt] ; 
			}
			// Liste des valeurs
			if ($LocListAttrIn2 == "" ) {
				$LocListAttrIn2 = $listeTotAtt2[$cptAtt];
			} else {
				$LocListAttrIn2.=",".$listeTotAtt2[$cptAtt] ; 
			}		
		}
		//echo $cptAtt." ".$LocListAttrIn1." - ".$LocListAttrIn2." <br/>";
	}

}
// On restore les virgules dans les valeurs
$LocListAttrIn2 = str_replace("#-#",",",$LocListAttrIn2);
if ($LocFlagSPEC) {
	include $_SERVER["DOCUMENT_ROOT"].'/export/spec.php';
} else {
	switch ($SQLAction) {
		case "insert":
			$LocScriptSQL ="insert into ".$nomTableACCESS." (".$LocListAttrIn1.") values (".$LocListAttrIn2.")";
			break;
		// On laisse comme ca meme si c'est moche pour montrer qu'on ne gere que le cas de l'insert
		default : 
			break;
	} 
}
if ($flagTemp) {
	//echo "SCRIPT = <b>".$LocScriptSQL."</b> <br/>";
}
return $LocScriptSQL;
}

//*********************************************************************
// GetSQL : génère le code SQL pour mettre à jour la table
function executeSQLFichier($fileSQLopen,$connectAccessTravail,$EcrireLogComp,$logComp,$pasdefichier) {
// Cette fonction permet de générer le code SQL en fonction de la table en entrée et du type d'action à mener.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $fileSQLopen : le fichier à lire
// $connectAccessTravail : la connexion a la base access

//*********************************************************************
// En sortie : 
// La fonction renvoie le code SQL prêt à être exécuté.
//*********************************************************************
	// lecture des lignes et execution de la commande SQL
	$erreurSQLSup = "";
	$cptLigne = 0;
	while ( ($line = fgets($fileSQLopen)) !== false) {
		if (strpos($line,"UPDATE") === false && 
			strpos($line,"DELETE") === false && 
			strpos($line,"INSERT") === false ) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "Ligne ".$cptLigne." rejetee car SQL = '".$line."' errone ",$pasdefichier);
			}								
		} else {
			$cptLigne ++;
			$compReadResultC = odbc_exec($connectAccessTravail,$line);
			$erreurSQL = odbc_errormsg($connectAccessTravail); //
			if (!$compReadResultC) {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "Ligne ".$cptLigne." Erreur execution SQL ".$line." - erreur complete = ".$erreurSQL."",$pasdefichier);
				}
				$erreurSQLSup.="Ligne ".$cptLigne." Erreur execution SQL ".$line." - erreur complete = ".$erreurSQL."<br/>";
			} else {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "Execution du sql : ".$line,$pasdefichier);
				}	
			}
			odbc_free_result($compReadResultC);
		}
	}
 return $erreurSQLSup;
}


?>