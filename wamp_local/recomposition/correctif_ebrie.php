<?php 
// FW 20180213
// Correctif pêche artisanale Ebrié (Pnd)
//
// correctif_ebrie.php
//
// S'appelle de la manière suivante :
// http://........../recomposition/correctif_ebrie.php
// Tolérant aux executions multiples (test de count)
//
//
	include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
	include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
	set_time_limit( 0 ) ; // be sure we have enought time to execute
	$connection = pg_connect( "host=".$host." dbname=".$db_default." user=".$user." password=".$passwd ) ;
	if( $connection) {
		$query = "select max( cast( a.id as integer ) ) as max FROM art_debarquement_rec as a"; // Prendre le dernier index utilisé dans art_debarquement
		$result = pg_query( $connection, $query ) ;
		if( $result) {
			$row = pg_fetch_row( $result ) ;
			$idRecDebarquement = $row[0];
			$idRecDebarquement++ ; // Le prochain enregistrement sera celui là (Max + 1)
			echo "Next ID rec debarquement: $idRecDebarquement<br/>" ;
			
			$query = "select max( cast( a.id as integer ) ) as max FROM art_fraction_rec as a"; // Prendre le dernier index utilisé dans art_fraction
			$result = pg_query( $connection, $query ) ;
			if( $result) {
				$row = pg_fetch_row( $result ) ;
				$idRecFraction = $row[0];
				$idRecFraction++ ; // Le prochain enregistrement sera celui là (Max + 1)
				echo "Next ID rec fraction : $idRecFraction<br/>" ;
			
// On sait maintenant quel doit être le prochain index dans art_debarquement et dans art_fraction...

				// A-t-on des enregistrements à considérer?
				// C'est à dire : des débarquements qui n'ont pas été recomposés.
				// Cela concerne les enquêtes de pêche artisanale individuelle ébrié 1978-1984 (Les 7 agglomérations considérées)
				$query = "select count( a.* ) as nb FROM art_debarquement AS a " ;
				$query .= "where not exists ( " ; 
				$query .= "  select b.art_debarquement_id from art_debarquement_rec as b " ;
				$query .= "  where b.art_debarquement_id = a.id " ;
				$query .= ") " ;
				$query .= "and a.art_agglomeration_id in ( 77,79,80,82,83,84,88 ) " ;
				$result = pg_query( $connection, $query ) ;
				if( $result) {
					$row = pg_fetch_row( $result ) ;
					$count = $row[0];
					// on en a un certain nombre ! SINON le programme a été exécuté préalablement et il n'y a plus de données à corriger
					echo "count : $count<br/>" ; 
					
					// Liste des enregistrements à considérer.
					// Pour chaque enregistrement on a besoin de :
					// - Les ids
					// - Le poids total
					// - Les espèces
					// - Le poids par espèces
					// - Le nombre de poissons
					$query = "select b.id, a.id, a.poids_total, a.art_agglomeration_id, b.nbre_poissons, b.ref_espece_id, b.poids FROM art_fraction AS b " ;
					$query .= "RIGHT JOIN art_debarquement AS a ON a.id = b.art_debarquement_id " ;
					$query .= "where not exists ( " ; 
					$query .= "  select b.art_debarquement_id from art_debarquement_rec as b " ;
					$query .= "  where b.art_debarquement_id = a.id " ;
					$query .= ") " ;
					$query .= "and a.art_agglomeration_id in ( 77,79,80,82,83,84,88 ) " ;
					$query .= "order by a.id asc " ;
					$result = pg_query( $connection, $query ) ;
					if( $result) {
						$nbLine = 0 ;
						$idDebarquementCurrent = 0 ; // On se souvient du débarquement en cours 
						while( $row = pg_fetch_row( $result ) ) {
							$idB = $row[ 0 ] ;
							$idA = $row[ 1 ] ;
							$poidsTotal = $row[ 2 ] ;
							$agglo = $row[ 3 ] ;
							$nbPoisson = $row[ 4 ] ;
							$espece = $row[ 5 ] ;
							$poidsFraction= $row[ 6 ] ;
							
							if( $idB == 0 ) { 
								//echo "id debarquement=$idA agglo=$agglo PT=$poidsTotal ... Pas de fraction !<br/> " ;
							} else {
								//echo "id debarquement=$idA agglo=$agglo PT=$poidsTotal id fraction=$idB espece=$espece nb poisson=$nbPoisson poids de la fraction $poidsFraction<br/> " ;
							}
							
							$nbLine++ ;
							
							// On a un débarquement puis des fractions et on recommence pour le débarquement suivant.
							if( $idA != $idDebarquementCurrent ) {
								// On a un nouveau débarquement
								$query = "INSERT INTO art_debarquement_rec( id, poids_total, art_debarquement_id ) " ;
								$query .= "VALUES( '$idRecDebarquement', $poidsTotal, $idA ) " ;
								pg_query( $connection, $query ) ;
								$idRecDebarquement++ ;
								$idDebarquementCurrent = $idA ;
							}
							if( $idB != 0 ) {
								// on insère la fraction...
								$query = "INSERT INTO art_fraction_rec( id, poids, nbre_poissons, ref_espece_id ) " ;
								$query .= "VALUES( '$idRecFraction', $poidsFraction, $nbPoisson, '$espece' ) " ;
								pg_query( $connection, $query ) ;
								$idRecFraction++ ;
							}
						}
					}
				} // Pas de count : Le programme a été exécuté préalablement et il n'y a plus de données à corriger
			}
		}
		pg_close();
	}
?>
