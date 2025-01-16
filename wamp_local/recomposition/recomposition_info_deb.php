<?php
////////////////////////////////////////////////////////////////////////////////
//                                RECOMPOSITION INTRA FRACTION                            	    //
//                                   Traitements des 8 cas                                     			   //
//                        grâce au tableau recapitulatif $info_deb                             		  //
//                             (travail fraction par fraction)                                  		  //
/////////////////////////////////////////////////////////////////////////////
//$nume_prodgr=1/$nb_enr;
reset($info_deb);

foreach($info_deb as $key=>$val){
	
	$date = date("H:i:s");
	print_debug("D".$key."   ".$date);
	$NfdbqI=0;
	$WfdbqI=0;

	if(is_null($info_deb[$key][''])) {

		foreach($val as $key2=>$val2){			//pour chaque fraction
		//     calcul et ajout des Wdft et Ndft pour chaque fraction  dans le tableau recapitulatif $info_deb                //
		
			$Wfdbq = $info_deb[$key][$key2][8];
			$Nfdbq = $info_deb[$key][$key2][9];
			$Ndft = $info_deb[$key][$key2][11];
			$Wdft = $info_deb[$key][$key2][12];
			$Wm = $info_deb[$key][$key2][13];
			$info_deb=choix_cas_recomposition(true,$info_deb,$key,$key2,$Wfdbq,$Nfdbq,$Ndft,$Wdft,$Wm);
		

		
		//   COMPARAISON ENTRE LE POIDS TOTAL DE L'ENQUETE ET LA SOMME DES WFdbq     
			$Wt = $info_deb[$key][$key2][5];            //poids total du débarquement
			$Wfdbq = $info_deb[$key][$key2][8];
			$WfdbqI += $Wfdbq;			   				//somme des poids des fractions
			$NfdbqI += 1;								//compte nombre fractions JME 05 2009
	}
print_debug("A ".$WfdbqI);	
		if ($NfdbqI > 0) {
			$info_deb=comparaison_WT_SW($info_deb,$key,$val,$Wt,$WfdbqI);
		}
	}	// ajout du test pour les dbq sans fractions JME 05 2009
}

?>