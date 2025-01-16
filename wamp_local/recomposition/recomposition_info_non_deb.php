<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
//                                RECOMPOSITION INTRA FRACTION                                    //
//                                   Traitements des fractions non débarquées                           //
//                        grâce au tableau recapitulatif $info_non_deb                                //
//                             (travail fraction par fraction)                                                   //
////////////////////////////////////////////////////////////////////////////////////////////////////

reset($info_non_deb);

foreach($info_non_deb as $key=>$val){                      //pour tous les debarquements
	print_debug("ND".$key);
	foreach($val as $key2=>$val2){		  		//pour chaque fraction
		//Recomposition info_non_deb
		$Wfdbq = $info_non_deb[$key][$key2][8];
		$Nfdbq = $info_non_deb[$key][$key2][9];

		//VOIR si le calcul des recompositions se fait bien avec le tableazu info_non_deb
		//si oui, on peut mettre cette partie avant la recomposition de info_deb (en ajoutant un p'tit morceau de code concernant le fait qu'il ne faut pas traiter ces clefs (celles de info_non_deb, de cette boucle) dans la recomposition de info_deb
		//Et donc voir si on peut rajouter les INSERT directement dans la boucle de recomposition de info_deb=> une seule boucle
		
		
		$info_non_deb=choix_cas_recomposition(false,$info_non_deb,$key,$key2,$Wfdbq,$Nfdbq, 0, 0, 0);
		
		
	//                     CREATION DE LA  NOUVELLE FRACTION   dans $info_deb                     //
	//	reset($info_deb);
	//reset($info_non_deb);
		
		$info_deb[$key][$key2][8] = $info_non_deb[$key][$key2][8];
		$info_deb[$key][$key2][9] = $info_non_deb[$key][$key2][9];
		$info_deb[$key][$key2][7] = $info_non_deb[$key][$key2][7];
		unset($info_non_deb[$key][$key2]);
	}
}

?>