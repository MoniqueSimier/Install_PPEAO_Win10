<?php 
// Créé par Gaspard BERTRAND, 30/11/2011
// definit a quelle section appartient la page
// Modifié pour version dynamique le 26/03/2014 (F.WOEHL)


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
	<title>ppeao::info_ecosystemes</title>

	
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
	<h2 style="text-align:center">Système d'informations sur les Peuplements de poissons et la P&ecirc;che artisanale<br> des Ecosyst&egrave;mes estuariens, lagunaires ou continentaux d&rsquo;Afrique de l&rsquo;Ouest</h2>

<?php 
/*
	<br>
	<h2>P&ecirc;che artisanale</h2>
	
<table cellpadding="5" border=1 >
		<th align="right">Pays</th>
		<th align="right">Ecosyst&egrave;me</th>
		<th align="right">Date d&eacute;but</th>
		<th align="right">Date fin</th>	</th>
	<tr align="right">
		<td>The Gambia</td>
		<td>Gambia River</td>
		<td>04/2001</td>
		<td>05/2002</td>
	</tr>
	<tr align="right">
		<td>Mali</td>
		<td>lac de Manantali</td>
		<td>06/1995</td>
		<td>08/1995</td>
	</tr>
	<tr align="right">
		<td>Mali</td>
		<td>lac de Manantali</td>
		<td>05/2002</td>
		<td>05/2003</td>
	</tr>
	<tr align="right">
		<td>Mali</td>
		<td>lac de Selingue</td>
		<td>08/1994</td>
		<td>10/1994</td>
	</tr>
	<tr align="right">
		<td>Mali</td>
		<td>lac de Selingue</td>
		<td>05/2002</td>
		<td>06/2003</td>
	</tr>
	<tr align="right">
		<td>Senegal</td>
		<td>Casamance</td>
		<td>04/2005</td>
		<td>08/2005</td>
	</tr>
</table>
	
<br>
<br>

<h2>P&ecirc;che scientifique</h2>
<table cellpadding="5" border=1 >
		<th align="right">Pays</th>
		<th align="right">Ecosyst&egrave;me</th>
		<th align="right">Campagnes</th>
		<th align="right">Date d&eacute;but</th>
		<th align="right">Date fin</th>	</th>
	<tr align="right">
		<td>The Gambia</td>
		<td>Gambia River</td>
		<td>13</td>
		<td>24/11/2000</td>
		<td>24/11/2003</td>
	</tr>
	<tr align="right">
		<td>Guinea</td>
		<td>Dangara</td>
		<td>7</td>
		<td>29/01/1993</td>
		<td>24/01/1994</td>
	</tr>
	<tr align="right">
		<td>Guinea</td>
		<td>Fatala</td>
		<td>13</td>
		<td>22/01/1993</td>
		<td>22/03/1994</td>
	</tr>
	<tr align="right">
		<td>Guinea-Bissau</td>
		<td>Bijagos</td>
		<td>1</td>
		<td>22/03/1993</td>
		<td>01/04/1993</td>
	</tr>
	<tr align="right">
		<td>Guinea-Bissau</td>
		<td>Rio Buba</td>
		<td>1</td>
		<td>03/04/1993</td>
		<td>07/04/1993</td>
	</tr>
	<tr align="right">
		<td>Guinea-Bissau</td>
		<td>Iles Urok</td>
		<td>2</td>
		<td>01/11/2011</td>
		<td>28/11/2012</td>
	</tr>
	<tr align="right">
		<td>Ivory Coast</td>
		<td>Ebrié</td>
		<td>73</td>
		<td>17/12/1979</td>
		<td>31/08/1982</td>
	</tr>
	<tr align="right">
		<td>Mali</td>
		<td>Manantali</td>
		<td>3</td>
		<td>19/06/2002</td>
		<td>06/10/2003</td>
	</tr>
	<tr align="right">
		<td>Mali</td>
		<td>Selingue</td>
		<td>3</td>
		<td>10/06/2002</td>
		<td>15/10/2003</td>
	</tr>
	<tr align="right">
		<td>Mauritanie</td>
		<td>Banc Arguin</td>
		<td>3</td>
		<td>07/05/2008</td>
		<td>28/05/2010</td>
	</tr>
	<tr align="right">
		<td>Senegal</td>
		<td>Sine Saloum</td>
		<td>63</td>
		<td>20/04/1990</td>
		<td>26/10/2007</td>
	</tr>
	<tr align="right">
		<td>Senegal</td>
		<td>Bamboung</td>
		<td>30</td>
		<td>11/03/2003</td>
		<td>16/10/2012</td>
	</tr>
</table>
*/
?>

<?php 
// VERSION DYNAMIQUE...

	$html = "<div class='info_tbl'>" ;

	$sql = "SELECT COUNT(D.*),P.nom as pays,SY.libelle as systeme,SE.nom as secteur,D.art_agglomeration_id,D.annee,D.mois FROM art_debarquement D " ;
	$sql .= "INNER JOIN art_agglomeration A ON A.id = D.art_agglomeration_id " ;
	$sql .= "INNER JOIN ref_secteur SE ON SE.id = A.ref_secteur_id " ;
	$sql .= "INNER JOIN ref_systeme SY ON SY.id = SE.ref_systeme_id " ;
	$sql .= "INNER JOIN ref_pays P ON P.id = SY.ref_pays_id " ;
	$sql .= "GROUP BY P.nom,SY.libelle,SE.nom,D.art_agglomeration_id,D.annee, D.mois " ;
	$sql .= "ORDER BY pays,systeme,D.annee, D.mois " ;
	$result = pg_query( $connectPPEAO, $sql ) or die( pg_error( ) ) ;
	if( $row = pg_fetch_array( $result ) ) {
		$paysCurrent = $row[ 'pays' ] ;
		$systemeCurrent = $row[ 'systeme' ] ;
		$moisCurrent = $row[ 'mois' ] ;
		$anneeCurrent = $row[ 'annee' ] ;
		$moisDebut = $moisCurrent ;
		$anneeDebut = $anneeCurrent ;
		
		$html .= "<br/><br/>" ;
		$html .= "<h2>Pêche artisanale</h2>" ;
		$html .= "<table border='1'>" ;
		$html .= "<tr>" ;
		$html .= "<th>Pays</th>" ;
		$html .= "<th>Ecosystème</th>" ;
		$html .= "<th>Début</th>" ;
		$html .= "<th>Fin</th>" ;
		$html .= "</tr>" ;
		
		do {
			$pays = $row[ 'pays' ] ;
			$systeme = $row[ 'systeme' ] ;
			$mois = $row[ 'mois' ] ;
			$annee = $row[ 'annee' ] ;

			$break = false ;			
			
			if( $systeme != $systemeCurrent ) {
				$break = true ;
			}
			$dateCurrent = ( $anneeCurrent * 12 ) + $moisCurrent ;
			$date = ( $annee * 12 ) + $mois ;
			$deltaMois = $date - $dateCurrent ;
			if( $deltaMois > 6 ) {
				$break = true ;
			}
			if( $break ) {
				$html .= "<tr>" ;
				$html .= "<td>$paysCurrent</td>" ;
				$html .= "<td>$systemeCurrent</td>" ;
				$html .= "<td>$moisDebut/$anneeDebut</td>" ;
				$html .= "<td>$moisCurrent/$anneeCurrent</td>" ;
				$html .= "</tr>" ;

				$paysCurrent = $pays ;
				$moisDebut = $mois ;
				$anneeDebut = $annee ;
			}
			$systemeCurrent = $systeme ;
			$moisCurrent = $mois ;
			$anneeCurrent = $annee ;
				
		} while( $row = pg_fetch_array( $result ) ) ;
		$html .= "<tr>" ;
		$html .= "<td>$paysCurrent</td>" ;
		$html .= "<td>$systemeCurrent</td>" ;
		$html .= "<td>$moisDebut/$anneeDebut</td>" ;
		$html .= "<td>$moisCurrent/$anneeCurrent</td>" ;
		$html .= "</tr>" ;
		$html .= "</table>" ;
	}	
	
	$html .= "<br/><br/>" ;
	
	$sql = "SELECT COUNT(C.*) as nb,P.nom as pays,SY.libelle as systeme,EXTRACT(YEAR FROM C.date_debut) as annee,EXTRACT( MONTH FROM C.date_debut) as mois FROM exp_campagne C " ;
	$sql .= "INNER JOIN ref_systeme SY ON SY.id = C.ref_systeme_id " ;
	$sql .= "INNER JOIN ref_pays P ON P.id = SY.ref_pays_id " ;
	$sql .= "GROUP BY P.nom,SY.libelle,annee,mois " ;
	$sql .= "ORDER BY pays,systeme,annee,mois " ;
	$result = pg_query( $connectPPEAO, $sql ) or die( pg_error( ) ) ;
	if( $row = pg_fetch_array( $result ) ) {
		$paysCurrent = $row[ 'pays' ] ;
		$systemeCurrent = $row[ 'systeme' ] ;
		$moisCurrent = $row[ 'mois' ] ;
		$anneeCurrent = $row[ 'annee' ] ;
		$moisDebut = $moisCurrent ;
		$anneeDebut = $anneeCurrent ;
		
		$html .= "<h2>P&ecirc;che scientifique</h2>" ;
		$html .= "<table border='1'>" ;
		$html .= "<tr>" ;
		$html .= "<th>Pays</th>" ;
		$html .= "<th>Ecosystème</th>" ;
		$html .= "<th>NB Campagnes</th>" ;
		$html .= "<th>Début</th>" ;
		$html .= "<th>Fin</th>" ;
		$html .= "</tr>" ;
		
		do {
			$pays = $row[ 'pays' ] ;
			$systeme = $row[ 'systeme' ] ;
			$mois = $row[ 'mois' ] ;
			$annee = $row[ 'annee' ] ;

			$break = false ;
				
			if( $systeme != $systemeCurrent ) {
				$break = true ;
			}
			$dateCurrent = ( $anneeCurrent * 12 ) + $moisCurrent ;
			$date = ( $annee * 12 ) + $mois ;
			$deltaMois = $date - $dateCurrent ;
			if( $deltaMois > 12 ) {
				$break = true ;
			}
			if( $break ) {
				$html .= "<tr>" ;
				$html .= "<td>$paysCurrent</td>" ;
				$html .= "<td>$systemeCurrent</td>" ;
				$html .= "<td>$nbCurrent</td>" ;
				$html .= "<td>$moisDebut/$anneeDebut</td>" ;
				$html .= "<td>$moisCurrent/$anneeCurrent</td>" ;
				$html .= "</tr>" ;

				$nbCurrent = $row[ 'nb' ] ;
				$paysCurrent = $pays ;
				$moisDebut = $mois ;
				$anneeDebut = $annee ;
			} else {
				$nbCurrent += $row[ 'nb'] ;
			}
			$systemeCurrent = $systeme ;
			$moisCurrent = $mois ;
			$anneeCurrent = $annee ;
		
		} while( $row = pg_fetch_array( $result ) ) ;
		$html .= "<tr>" ;
		$html .= "<td>$paysCurrent</td>" ;
		$html .= "<td>$systemeCurrent</td>" ;
		$html .= "<td>$nbCurrent</td>" ;
		$html .= "<td>$moisDebut/$anneeDebut</td>" ;
		$html .= "<td>$moisCurrent/$anneeCurrent</td>" ;
		$html .= "</tr>" ;
		$html .= "</table>" ;
	}	
	
	$html .= "</div>" ;	
	
	echo $html ;

// VERSION DYNAMIQUE...
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
