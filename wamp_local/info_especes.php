<?php 
// Créé par 



$section="apropos";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=0; // zone publique (voir table admin_zones)
?>


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
	<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<title>ppeao::info_especes</title>

	
</head>

<body>

	<?php 
	// le menu horizontal
	include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';


	if (isset($_SESSION['s_ppeao_user_id'])){ // a implementer partout + deploiement de loginform_s.php et function_ppeao.php
		$userID = $_SESSION['s_ppeao_user_id'];
	} else {
		$userID=null;
	}
	
	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {
	?>

	<div id="main_container" class="home">
	<h1>PPEAO</h1>

	<h2 style="text-align:center">Tableau de présence des espèces (groupes d'espèces) observées par pays<br> A- observation lors d'enquêtes sur la pêche artisanale<br> S- observation lors de pêches scientifiques</h2>

<?php 


	$sql = "SELECT  pays, systeme, famille, nom, nbexp, nbart
		FROM (

		SELECT  systeme, nom

		FROM (SELECT  ref_systeme.libelle AS systeme, ref_espece.libelle AS nom
		FROM ref_systeme
		INNER JOIN ref_pays on ref_systeme.ref_pays_id=ref_pays.id
		INNER JOIN exp_campagne on exp_campagne.ref_systeme_id=ref_systeme.id
		INNER JOIN exp_coup_peche on exp_coup_peche.exp_campagne_id=exp_campagne.id 
		INNER JOIN exp_fraction on exp_fraction.exp_coup_peche_id=exp_coup_peche.id
		INNER JOIN ref_espece on exp_fraction.ref_espece_id=ref_espece.id
		INNER JOIN ref_famille on ref_espece.ref_famille_id=ref_famille.id
		INNER JOIN ref_ordre on ref_famille.ref_ordre_id=ref_ordre.id
		GROUP BY ref_systeme.libelle, ref_espece.libelle
		) A

		UNION
		SELECT  systeme, nom
		FROM (SELECT  ref_systeme.libelle AS systeme, ref_espece.libelle AS nom
		FROM ref_systeme
		INNER JOIN ref_pays on ref_systeme.ref_pays_id=ref_pays.id
		INNER JOIN ref_secteur on ref_secteur.ref_systeme_id=ref_systeme.id
		INNER JOIN art_agglomeration on art_agglomeration.ref_secteur_id=ref_secteur.id
		INNER JOIN art_debarquement on art_debarquement.art_agglomeration_id=art_agglomeration.id
		INNER JOIN art_fraction on art_fraction.art_debarquement_id=art_debarquement.id
		INNER JOIN ref_espece on art_fraction.ref_espece_id=ref_espece.id
		INNER JOIN ref_famille on ref_espece.ref_famille_id=ref_famille.id
		INNER JOIN ref_ordre on ref_famille.ref_ordre_id=ref_ordre.id
		GROUP BY ref_systeme.libelle, ref_espece.libelle
		) B ) C

		LEFT JOIN (SELECT ref_systeme.libelle AS expsysteme, ref_espece.libelle AS expespece, count(exp_fraction.id) AS nbexp
		FROM ref_systeme
		INNER JOIN ref_pays on ref_systeme.ref_pays_id=ref_pays.id
		INNER JOIN exp_campagne on exp_campagne.ref_systeme_id=ref_systeme.id
		INNER JOIN exp_coup_peche on exp_coup_peche.exp_campagne_id=exp_campagne.id 
		INNER JOIN exp_fraction on exp_fraction.exp_coup_peche_id=exp_coup_peche.id
		INNER JOIN ref_espece on exp_fraction.ref_espece_id=ref_espece.id
		INNER JOIN ref_famille on ref_espece.ref_famille_id=ref_famille.id
		INNER JOIN ref_ordre on ref_famille.ref_ordre_id=ref_ordre.id
		GROUP BY ref_systeme.libelle, ref_espece.libelle
		) exp ON systeme=expsysteme AND nom=expespece

		LEFT JOIN (SELECT ref_systeme.libelle AS artsysteme, ref_espece.libelle AS artespece, count(art_fraction.id) AS nbart
		FROM ref_systeme
		INNER JOIN ref_pays on ref_systeme.ref_pays_id=ref_pays.id
		INNER JOIN ref_secteur on ref_secteur.ref_systeme_id=ref_systeme.id
		INNER JOIN art_agglomeration on art_agglomeration.ref_secteur_id=ref_secteur.id
		INNER JOIN art_debarquement on art_debarquement.art_agglomeration_id=art_agglomeration.id
		INNER JOIN art_fraction on art_fraction.art_debarquement_id=art_debarquement.id
		INNER JOIN ref_espece on art_fraction.ref_espece_id=ref_espece.id
		INNER JOIN ref_famille on ref_espece.ref_famille_id=ref_famille.id
		INNER JOIN ref_ordre on ref_famille.ref_ordre_id=ref_ordre.id
		GROUP BY ref_systeme.libelle,  ref_espece.libelle
		) art ON systeme=artsysteme AND nom=artespece  

		LEFT JOIN ( SELECT ref_pays.id, ref_pays.nom AS pays, ref_systeme.libelle AS refs FROM ref_pays INNER JOIN ref_systeme ON ref_systeme.ref_pays_id = ref_pays.id ) jpays
		 ON systeme = refs

		LEFT JOIN ( SELECT ref_famille.id, ref_famille.libelle AS famille, ref_espece.libelle AS reff FROM ref_famille INNER JOIN ref_espece ON ref_espece.ref_famille_id = ref_famille.id ) jfamille
		 ON nom = reff

		ORDER BY famille ASC, nom ASC, pays ASC
		; " ;
		
		$result = pg_query( $connectPPEAO, $sql ) or die( pg_error( ) ) ;
		if( $row = pg_fetch_array( $result ) ) {
			$html .= "<div class='espece_tbl'>" ;

			$html .= "<table>" ;
			$html .= "<tr>" ;
			$html .= "<th class='tht' ></th>" ;
			$html .= "<th class='tht' ></th>" ;
			do {
				$aPays = $row[ 'pays' ] ;
				$pays[] = $aPays ; 
			} while( $row = pg_fetch_array( $result ) ) ;
			$pays = array_unique( $pays ) ;
			sort( $pays ) ;
			foreach( $pays as $aPays ) {
				$html .= "<th colspan='2' class='thp' >$aPays</th>" ;
			}
			$html .= "</tr>" ;

			$html .= "<tr>" ;
			$html .= "<th class='tht' >Famille</th>" ;
			$html .= "<th class='tht' >Espece</th>" ;
			foreach( $pays as $aPays ) {
				$html .= "<th class='as' >A</th>" ;
				$html .= "<th class='as' >S</th>" ;
			} 
			$html .= "</tr>" ;
		}

	$result = pg_query( $connectPPEAO, $sql ) or die( pg_error( ) ) ;
	if( $row = pg_fetch_array( $result ) ) {
		$currentEspece = $row[ 'nom' ] ;
		$line[ 'famille' ] = $row[ 'famille' ] ;
		$line[ 'espece' ] = $currentEspece ;
		foreach( $pays as $aPays ) {
			$line[ "$aPays-nbart" ] = 0 ;
			$line[ "$aPays-nbexp" ] = 0 ;
		} 
		do {
			$espece = $row[ 'nom' ] ;
			if( $espece != $currentEspece ) {
				foreach( $pays as $aPays ) {
					if( empty( $line[ "$aPays-nbart" ] ) ) {
						$line[ "$aPays-nbart" ] = "<td class='tdea' ></td>" ;
					} else {
						$line[ "$aPays-nbart" ] = "<td class='tdea' >*</td>" ;
					}
					if( empty( $line[ "$aPays-nbexp" ] ) ) {
						$line[ "$aPays-nbexp" ] = "<td class='tdee' ></td>" ;
					} else {
						$line[ "$aPays-nbexp" ] = "<td class='tdee' >*</td>" ;
					}
				} 
				if( substr( $line[ 'famille' ], 0, 8 ) !== "<td>Inco" ) {
					$html .= "<tr>" ;
					foreach( $line as $item ) {
						if( !empty( $item ) ) {
							$html .= $item ;
						} else {
							$html .= "<td></td>" ;
						}
					}
					$html .= "</tr>" ;
				}
				foreach( $pays as $aPays ) {
					$line[ "$aPays-nbart" ] = 0 ;
					$line[ "$aPays-nbexp" ] = 0 ;
				} 
			}
			$currentEspece = $row[ 'nom' ] ;
			$line[ 'famille' ] = '<td>'.$row[ 'famille' ].'</td>' ;
			$line[ 'espece' ] = "<td>$currentEspece</td>" ;
			
			$aPays = $row[ 'pays' ] ;
			$line[ "$aPays-nbart" ] += $row[ 'nbart' ] ;
			$line[ "$aPays-nbexp" ] += $row[ 'nbexp' ] ;
			
		} while( $row = pg_fetch_array( $result ) ) ;
	}
	$html .= "</table>" ;
	$html .= "</div>" ;
	

	echo $html ;

?>
	
	</div> <!-- end div id="main_container"-->
	
	
	<?php 
	// note : on termine la boucle testant si l'utilisateur a accès à la page demandée
	
	;} // end if (userHasAccess($_SESSION['user_id'],$zone))
	
	// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
	else {userAccessDenied($zone);}
	?>
	
	<?php 
	include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';
	
	?>
</body>
</html>
