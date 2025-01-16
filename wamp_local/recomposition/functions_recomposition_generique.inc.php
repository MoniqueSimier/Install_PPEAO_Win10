<?php
/*
fonction récursive pour la recomposition des enquêtes cas 3 et 4
@param array $datas : tableau contenant toutes les enregistrements à recomposer
				art_debarquement.id => $key (index 1)
				art_fraction.id => $key2 (index 2)
				0=>ref_pays_id (pays)
				1=>nom secteur
				2=>nom agglomération
				3=>mois
				4=>annee
				5=>poids_total poids total du débarquement
				6=>art_grand_type_engin_id engin de pêche
				7=>ref_espece_id espèce de la fraction
				8=>poids poids de la fraction = Wfdbq
				9=>nbre_poissons nb de poissons de la fraction = Nfdbq

 */
function recomposition_cas_3_4($cas,$datas,$key,$key2,$strate,$round,$deb,$Wfdbq,$Nfdbq,$Wm){
	global $connection;
	//global $info_deb;
	//global $key;
	//global $key2;
	getTime();
	//print_debug($deb);
	//print_debug("cas = ".$cas." strate= ".$strate." round= ".$round);
	switch($strate."_".$round){
		case "ste_0":$query=query_from_ste($datas,$key,$key2,"0");break;
		case "ste_plus_0":$query=query_from_ste_plus($datas,$key,$key2,"0");break;
		case "se_0":$query=query_from_se($datas,$key,$key2,"0");break;
		case "e_0":$query=query_from_e($datas,$key,$key2,"0");break;
		case "e_plus_0":$query=query_from_e_plus($datas,$key,$key2,"0");break;
		case "ste_1":$query=query_from_ste($datas,$key,$key2,"1");break;
		case "ste_plus_1":$query=query_from_ste_plus($datas,$key,$key2,"1");break;
		case "se_1":$query=query_from_se($datas,$key,$key2,"1");break;
		case "e_1":$query=query_from_e($datas,$key,$key2,"1");break;
		case "e_plus_1":$query=query_from_e_plus($datas,$key,$key2,"1");
	}
	//print_debug($query);
	$result = pg_query($connection, $query);
	//print_debug(getTime()."ms");

	
	
	$WdftI = 0;
	$NdftI = 0;
	$nb_enleve=0;
	$Wm_i=0;
	
	
	//si aucun resultat, on fait une nouvelle requete qui donne 1 seul resultat pour rentrer dans la boucle suivante
	$nb = pg_num_rows($result);
	//print_debug("ss");
	//print_debug($nb);
	if($nb>=5){
		//$compteur=0;
		while($row = pg_fetch_row($result)){
				//$compteur++;
				$nb_frac = $row[0];
				$nb_deb = $row[1];
				// A vérifier avec M et JM pour info_non_deb => on prend les valeurs de info_deb
				if($deb){
						if($round=="1"){
							$Wfdbq = $datas[$nb_deb][$nb_frac][9];
							$Nfdbq = $datas[$nb_deb][$nb_frac][8];
						}else{
							$Ndft = $datas[$nb_deb][$nb_frac][11];
							$Wdft = $datas[$nb_deb][$nb_frac][12];
						}
				}else{
						global $info_deb;
						if($round=="1"){
							$Wfdbq = $info_deb[$nb_deb][$nb_frac][9];
							$Nfdbq = $info_deb[$nb_deb][$nb_frac][8];
						}else{
							$Ndft = $info_deb[$nb_deb][$nb_frac][11];
							$Wdft = $info_deb[$nb_deb][$nb_frac][12];
						}
				}
				if ($round=="0" && $cas=="3"){
					
					//$Ndft = $datas[$deb_concerné][$frac_concernées][11];
					
					$WdftI += $Wdft;
					$NdftI += $Ndft;
					
					//TODO
					if($NdftI!=0 || $NdftI!=""){
						$Wm = ($WdftI / $NdftI);
					}
					$Nfdbq = round( (($Wfdbq * 1000)/$Wm) , 0);
					$datas[$key][$key2][9] = $Nfdbq;
//print_debug($Wm);
//print_debug($Nfdbq);
//print_debug($datas[$key][$key2][9] );
//print_debug( $key);
//print_debug( $key2);
	//print_debug("bb");
	//print_debug($Wm);
	//print_debug($datas[$key][$key2][9]);
	return $datas;
	
				}elseif($round=="1" && $cas=="3"){
					
					//$Wfdbq = $datas[$deb_concerné][$frac_concernées][9];
					//$Nfdbq = $datas[$deb_concerné][$frac_concernées][8];
					
					if (($Wfdbq == "") || ($Nfdbq == "")){
						$nb_enlev ++;
					}else	{
						$Wm_i = $Wfdbq / $Nfdbq ;
						$Wm += $Wm_i / ($nb-$nb_enlev);
						$Nfdbq = round( (($Wfdbq * 1000)/$Wm) , 0);
						$datas[$key][$key2][9] = $Nfdbq;
						return $datas;	
					}
					
				}elseif($round=="0" && $cas=="4"){
					// Avérifier
						
					//$Ndft = $datas[$deb_concerné][$frac_concernées][11];
					//$Wdft = $datas[$deb_concerné][$frac_concernées][12];
					
				
					$WdftI += $Wdft;
					$NdftI += $Ndft;
					
					//TODO
					if($NdftI!=0 || $NdftI!=""){
						$Wm = ($WdftI / $NdftI);
					}
					$Wfdbq = round( (($Wm * $Nfdbq)/1000) , 2);  //en kg
					$datas[$key][$key2][8] = $Wfdbq;
	//print_debug("cc");
	//print_debug($Wm);
	//print_debug($datas[$key][$key2][8]);
					return $datas;
				
				}elseif($round=="1" && $cas=="4"){
					//$Wfdbq = $datas[$deb_concerné][$frac_concernées][9];
					//$Nfdbq = $datas[$deb_concerné][$frac_concernées][8];
					
					if (($Wfdbq == "") || ($Nfdbq == "")){
						$nb_enlev ++;
					}else{
						$Wm_i = $Wfdbq / $Nfdbq ;
						$Wm += $Wm_i / ($nb-$nb_enlev);
						$Wfdbq = round( (($Wm * $Nfdbq)/1000) , 2);  //en kg
						$datas[$key][$key2][8] = $Wfdbq;
						return $datas;						
				
					}
				}
		}
	}else{	
		switch($strate."_".$round){
				case "ste_0" : $datas = recomposition_cas_3_4($cas,$datas,$key,$key2,"ste_plus","0",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "ste_plus_0" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"se","0",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "se_0" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"e","0",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "e_0" : $datas = recomposition_cas_3_4($cas,$datas,$key,$key2,"e_plus","0",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "e_plus_0" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"ste","1",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "ste_1" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"ste_plus","1",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "ste_plus_1" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"se","1",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "se_1" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"e","1",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "e_1" : $datas =  recomposition_cas_3_4($cas,$datas,$key,$key2,"e_plus","1",$deb,$Wfdbq,$Nfdbq,$Wm);break;	
				case "e_plus_1" : $datas = query_e_plus_1($datas, $key, $key2, "1", $Wfdbq,$Nfdbq,$Wm);break;
				return $datas;			
// Correction des appels des fonctions récursives pour forcer la sortie de la fonction    JME 12 2008						
		}
	}
	//print_debug("aa");
	//print_debug($Wm);
	//print_debug($datas[$key][$key2][9]);
	return $datas;
}

/*
fonction query_e_plus_1
fonction créé 12 2008 pour éviter les conséquences de la récursivité
*/
function query_e_plus_1($values, $key, $key2, $round, $Wfdbq, $Nfdbq, $Wm){
	
	$Wm=0;
	if ($values[$key][$key2][7]=='PDU')$Wm = 10;
	elseif ($values[$key][$key2][7]=='SEP')$Wm = 125;
	elseif ($values[$key][$key2][7]=='CAL')$Wm = 40;
	elseif ($values[$key][$key2][7]=='CAA')$Wm = 40;
	elseif ($values[$key][$key2][7]=='CMB')$Wm = 600;
	elseif ($values[$key][$key2][7]=='OVU')$Wm = 125;
	else $Wm = 0;
	
	if ($Wm != 0) $Nfdbq = round( (($Wfdbq * 1000)/$Wm) , 0); 
	else $nfdbq = 0;
	$values[$key][$key2][9] = $Nfdbq;
	return $values;
//						
}
						
/*
fonction recomposition cas 1
@param $datas tableau à traiter
@param $Wfdbq Poids
@return $datas 
*/
function recomposition_cas_1($datas,$key,$key2,$Nfdbq,$Wdft,$Wm){
	$Wfdbq = $Wm * $Nfdbq;
	if ($Wfdbq < $Wdft) {$Wfdbq = $Wdft;}
	$datas[$key][$key2][8] = round(($Wfdbq /1000) , 2);

	return $datas;
}

/*
fonction recomposition cas 1
@param $datas tableau à traiter
@param $Wfdbq Poids
@return $datas 
*/
function recomposition_cas_2($datas,$key,$key2,$Wfdbq,$Ndft,$Wdft,$Wm){
	$Nfdbq = round((($Wfdbq *1000) / $Wm),0);		//wfdbq en kg
	if ($Nfdbq < $Ndft) {$Nfdbq = $Ndft;}
	$datas[$key][$key2][9] = $Nfdbq;
	return $datas;
}

/*
fonction recomposition cas 1
@param $datas tableau à traiter
@param $Wfdbq Poids
@return $datas 
*/
function recomposition_cas_5($datas,$key,$key2,$Ndft,$Wdft){
	$Nfdbq = $Ndft; 
	$Wfdbq = $Wdft/1000;
	$datas[$key][$key2][8] = round ($Wfdbq, 2);
	$datas[$key][$key2][9] = $Nfdbq; 
	return $datas;
}

/*
fonction choix du cas pour la recomposition
@param boolean $deb débarqué ou non débarqué 
@param array $datas tableau à traiter
@param string $key index du tableau parcouru
@param string $key2 index 2 du tableau parcouru
@param reel $Wfdbq Poids
@return array $datas 
*/
//ATTENTION changement de passge de parametre   JME
function choix_cas_recomposition($deb,$datas,$key,$key2,$Wfdbq,$Nfdbq,$Ndft,$Wdft,$Wm){
	//function choix_cas_recomposition($deb,$datas,$key,$key2,$Wfdbq,$Nfdbq,$Ndft=0,$Wdft=0,$Wm=0)
	//print_debug($key." - ".$key2);
	//cas n°1 :   Wfdbq = 0 , Nfdbq > 0, DFT existe   //

	if ( (($Wfdbq == 0)||($Wfdbq == "")) && ($Nfdbq>0) && ($Ndft>0)){

		//print_debug("CAS 1");
		return recomposition_cas_1($datas,$key,$key2,$Nfdbq,$Wdft,$Wm);
	}elseif ( ($Wfdbq>0) && (($Nfdbq == 0)||($Nfdbq == "")) && ($Ndft>0)){//cas n°2 : Wfdbq > 0 , Nfdbq = 0, DFT existe   //
		//print_debug("CAS 2") ;
		return recomposition_cas_2($datas,$key,$key2,$Wfdbq,$Ndft,$Wdft,$Wm);
	}elseif ( ($Wfdbq>0) && (($Nfdbq == 0)||($Nfdbq == "")) && (($Ndft == 0)||($Ndft == "")) ){//cas n°3 : Wfdbq >0  , Nfdbq = 0, pas de DFT   //
		//print_debug("CAS 3");
		return recomposition_cas_3_4("3",$datas,$key,$key2,"ste","0",$deb,$Wfdbq,$Nfdbq,$Wm);
	}elseif ( (($Wfdbq == 0)||($Wfdbq == "") || ($Wfdbq == "0")) && ($Nfdbq>0) && (($Ndft == 0)||($Ndft == "")||($Ndft == "0")) ){//cas n°4 : Wfdbq =0  , Nfdbq > 0, pas de DFT   //
		//print_debug("CAS 4");
		return recomposition_cas_3_4("4",$datas,$key,$key2,"ste","0",$deb,$Wfdbq,$Nfdbq,$Wm);
	}elseif ( (($Wfdbq == 0)||($Wfdbq == "")) && (($Nfdbq == 0)||($Nfdbq == "")) && ($Ndft>0) ){//cas n°5 : Wfdbq =0  , Nfdbq = 0, DFT       //
		//print_debug("CAS 5");
		return recomposition_cas_5($datas,$key,$key2,$Ndft,$Wdft);
	}elseif ( ($Wfdbq >0) && ($Nfdbq > 0) ){//cas n°6 et 7 : Wfdbq >0  et Nfdbq > 0        //
		//print_debug("CAS 6 et 7");
		//print ("<br>cas 6 et 7 Wfdbq =".$Wfdbq." , Nfdbq =".$Nfdbq);
	}elseif ( (($Wfdbq == 0)||($Wfdbq == "")) && (($Nfdbq == 0)||($Nfdbq == "")) && (($Ndft == 0)||($Ndft == "")) ){//cas n°8 :Wfdbq =0, Nfdbq=0, pas de DFT     //
		//print_debug("CAS 8");
		//unset($datas[$key][$key2]);        // correction JME 12 2008
	}else{
		//print_debug("AUTRE CAS PB!!!!!!!!!!!!");
	
	}
	return $datas;
}

/*
function calul  calcul et ajout des Wdft et Ndft pour chaque fraction 
 @param array $adatas
 @param string $key
@param string $key2
@parma array $FT tableau des tailles
@param array $coef_esp tableau des 
*/

function calcul_Wdft_Ndft_par_fraction($datas,$FT,$coef_esp){
	reset($datas);
	
	foreach($datas as $key=>$val){
		foreach($val as $key2=>$val2){
			$Ndft = 0;
			if(isset($FT[$key2] ))$Ndft = count($FT[$key2]);
			$datas[$key][$key2][11] = $Ndft;                    //Ndft ds tableau récap		
			$esp = $val2[7];

			$Wdft = 0;                                             //mise à zéro de la variable
			for($i=0; $i<$Ndft ; $i++){
				
				$Wdft += ($coef_esp[$esp][0] * pow(10, -5) * pow($FT[$key2][$i],$coef_esp[$esp][1]));
			}
			$datas[$key][$key2][12] = round($Wdft, 1);		//Wdft ds tableau récap
			// avec precision = 0.1
			if ($Ndft != 0){
				$Wm = round(($Wdft / $Ndft) , 1);
			}else{
				$Wm = 0;
			}
		//print_debug( $Ndft );
		//print_debug( $Wdft );
		//print_debug( $Wm );      //JME 12 2008
		
		$datas[$key][$key2][13] = $Wm;			//Wm ds tableau recap
			
			// avec précision = 0.1
		}
	}
	return $datas;

}
/*
Fonction de comparaison entre le poids total et la somme des poids des fractions
@param array $datas tableau des enquêtes débarquées
@param array $val aleurs de $datas
@param double $Wt poids total
@param double $Wfdbq Somme des poids des fractions
@return $datas

test JME 05 2009 sur le test valeur du rapport des poids; mise à -1 de poids total ($datas[$key][$key2][5]) 
qui sert de temoin si une enquete doit etre supprimée
*/
function comparaison_WT_SW($datas,$key,$val,$Wt,$WfdbqI){

	reset($val);
	$rapport = 0 ;          //JME 12 2008
	if ($Wt == 0){
//print_debug("Wt=0".$WfdbqI);	
		foreach($val as $key2=>$val2){			//pour chaque fraction
			$datas[$key][$key2][5] = round($WfdbqI,2);
		}

	}else{
		$rapport= round(($WfdbqI / $Wt),2);

		//cas (somme Wfdbq / Wt) <0.95 :
		if ($rapport < 0.95000){
	
			foreach($val as $key2=>$val2){			//pour chaque fraction
				
				$Wfdbq = $datas[$key][$key2][8];
				$Wfdbq = $Wfdbq * ($Wt/$WfdbqI);
				$datas[$key][$key2][8] = round($Wfdbq,2);
				$Nfdbq = $datas[$key][$key2][9];
				$Nfdbq = $Nfdbq * ($Wt/$WfdbqI);
				$datas[$key][$key2][9] = round($Nfdbq,0);
			}
		}elseif (($rapport  >= 0.949999) && ($rapport  < 1.049999)){	//cas (somme Wfdbq / Wt) >= 0.95 et < 1.05:
			
			foreach($val as $key2=>$val2){			//pour chaque fraction
				$datas[$key][$key2][5] = round($WfdbqI,2);		//Wt = somme(Wfdbq)
			}
		}elseif (($rapport  >= 1.050000) && ($rapport  < 1.99999)){	//cas (somme Wfdbq / Wt) >= 1.05 et < 2:

			foreach($val as $key2=>$val2){			//pour chaque fraction
				$datas[$key][$key2][5] = round($WfdbqI,2);		//Wt = somme(Wfdbq)
			}
		}elseif ($rapport  >= 2.00000){//cas (somme Wfdbq / Wt) >= 2:

			foreach($val as $key2=>$val2){			//pour chaque fraction
				//unset($datas[$key][$key2]);
				$datas[$key][$key2][5] = -1 ;
			}
		}
	}//fin du else Wt=0
	
	return $datas;
}
?>
