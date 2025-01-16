<?php 
//*****************************************
// spec.php
//*****************************************
// Created by Yann Laurent
// 2009-06-18 : creation
//*****************************************
// Ce fichier contient une serie d'actions specifique a réaliser lors de l'export des données vers les bases ACCESS
if ($nomTableACCESS == "CoefficientKb") {
// on doit générer une ligne par système
// On va mettre à jour les variables LocListAttrIn1 et LocListAttrIn2
	if ($SQLAction=="insert") {
		// On n'a que ce cas pour l'instant...
		$SQLSystem = "select id from ref_systeme";
		$ResultSystem = pg_query($connPOST,$SQLSystem);
		$LocerreurSQL = pg_last_error($connPOST);
		if (!$ResultSystem) {
			echo "erreur lecture base post gre pour ref_systeme erreur complete = ".$LocerreurSQL."<br/>";
		} else {
			if (pg_num_rows($ResultSystem) == 0) {
				echo "pas de resultat lecture base post gre pour ref_system <br/>";
			} else {
				// On balaye tous les enregsitrements de ref_system
				$cptSQL = 0;
				while ($systemRow = pg_fetch_row($ResultSystem) ) {
					$contListAttrIn2 = $LocListAttrIn2;
					$cptSQL ++ ;
					if ($cptSQL == 1) {
						//On met la valeur conservée à true uniquement pour le premier enreg
							$listeTotAtt1 = explode(",",$LocListAttrIn1);
							$listeTotAtt2 = explode(",",$LocListAttrIn2);
							$contListAttrIn2 = "";
							$nbChamps = count($listeTotAtt1 ) - 1;
							for ($cptAtt = 0; $cptAtt <= $nbChamps; $cptAtt++) {
								//echo $cptAtt." ".$listeTotAtt1[$cptAtt]."<br/>";
								if (strpos($listeTotAtt1[$cptAtt],"ValeurConserve") === false) {
									// Liste des valeurs
									if ($contListAttrIn2 == "" ) {
										$contListAttrIn2 = $listeTotAtt2[$cptAtt];
									} else {
										$contListAttrIn2.=",".$listeTotAtt2[$cptAtt] ; 
									}		
								} else {
									// On change la valeur 
									if ($contListAttrIn2 == "" ) {
										$contListAttrIn2 = "1";
									} else {
										$contListAttrIn2.=",1" ; 
									}
								}
								//echo $cptAtt." ".$LocListAttrIn1." - ".$LocListAttrIn2." <br/>";
							}
					}
					if ($LocListAttrIn1 == "" ) {
						$TempListAttrIn1 = "IdSysteme";
					} else {
						$TempListAttrIn1 =$LocListAttrIn1.",IdSysteme"; 
					}
					// Liste des valeurs
					if ($LocListAttrIn2 == "" ) {
						$TempListAttrIn2 = $systemRow[0];
					} else {
						$TempListAttrIn2 = $contListAttrIn2.",".$systemRow[0] ; 
					}
					// Creation du script. autant de ligne par especes que de systeme.
					$LocScriptSQL =$LocScriptSQL." insert into coefficientKb (".$TempListAttrIn1.") values (".$TempListAttrIn2.")#;# ";

				}
			}
		}
	}

}





?>