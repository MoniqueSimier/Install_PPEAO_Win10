<?php 
// Page de sélection des données à extraire
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="consulter";
$subsection="";

//$zone=8; // zone consultation (voir table admin_zones)
$zone=6;  //JME 012016

// FW 20160209 >>>
// Mode de sélection des espèce 0=Choix oui/non ou 1=continuer
	$modeSelectionEspece = 0 ;
//	$modeSelectionEspece = 1 ;
// <<< FW

// on réinitialise la selection stockee dans des variables de session
 unset ($_SESSION["selection_url"]);
 unset ($_SESSION["selection_xml"]);

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	include $_SERVER["DOCUMENT_ROOT"].'/extraction/selection/selection_functions.php';

?>
	<title>ppeao::consulter des donn&eacute;es::s&eacute;lection</title>
	
<script src="/extraction/selection/ex_selection.js" type="text/javascript"  charset="iso-8859-15"></script>


</head>

<body>

<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="selection">
<h2 style="text-align:center">consulter des donn&eacute;es : s&eacute;lection
<?php 

// si on a depasse la premiere etape, on affiche le lien permettant d'afficher ou masquer la selection
if (isset($_GET["step"])) {$step=$_GET["step"];} else {$step=0;}
if ($step>1) {
	echo('<span class="showHide"><a id="selection_precedente_toggle_haut" onclick="javascript:toggleSelection();return false;" title="afficher ou masquer la selection" href="#">[modifier ma s&eacute;lection]</a></span>');
}
?>
</h2>
<?php if ($step<1) { ?>
<br><p>Pour extraire des donn&eacute;es de p&ecirc;ches exp&eacute;rimentales ou artisanales ou des statistiques de p&ecirc;ches, suivez le processus de s&eacute;lection ci-dessous. Vous pourrez &agrave; tout moment modifier les valeurs choisies lors d&#x27;une des &eacute;tapes de votre s&eacute;lection.</p>
<?php } ?>
<!-- extraction de donnees et de stats -->
<?php

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
?>


<?php
echo('<div id="ex_selection">');

// on calcule les élements du compteur
$compteur=prepareCompteur();


// on en extrait la liste des campagnes et enquetes correspondant a la selection courante
$campagnes_ids=$compteur["campagnes_ids"];
$coups_ids=$compteur["coups_ids"];
$enquetes_ids=$compteur["enquetes_ids"];
$art_ids=$compteur["art_ids"];
$stats_ids=$compteur["stats_ids"];



//debug echo('<pre>');print_r($compteur);echo('</pre>');

// si on a depasse le step 1, on encapsule les selecteurs precedents dans un DIV id="selection_precedente"
// pour pouvoir les masquer
// le DIV est ferme dans l'une des fonctions afficheXXXXX(), selon le step
if ($_GET["step"]>1) {
	echo('<div id="selection_precedente">');
	echo('<div id="selection_precedente_contenu">');
	}
// on demande si l'utilisateur veut choisir des especes ou pas
afficheChoixEspeces( $modeSelectionEspece );
// on affiche le selecteur de taxonomie
afficheTaxonomie();
// on affiche le selecteur de geographie
afficheGeographie();
// on affiche le selecteur de periode
affichePeriode();


// on n'affiche la suite que si l'utilisateur est connecté
if (isset($_SESSION['s_ppeao_login_status']) && $_SESSION['s_ppeao_login_status']=='good') {



// on affiche le choix du type d'exploitation si il reste des campagnes ou des enquetes
if ($compteur["campagnes_total"]!=0 || $compteur["enquetes_total"]!=0) {
afficheTypeExploitation();} 

set_time_limit( 0 ) ; // FW 20170223 disable PHP time limit for large export

// on poursuit la selection en fonction du type d'exploitation choisi
switch($_GET["exploit"]) {
	// extraction de donnees
	case "donnees":
		// on doit poursuivre la selection par le type de donnees
		afficheTypeDonnees();
		// maintenant on travaille selon les peches exp ou art (parametre &donnee=)
		switch ($_GET["donnees"]) {
			// peche experimentale : on continue la selection par secteurs>campagnes>engins de peche
			case "exp":
			// secteurs
			afficheSecteurs("exp");
			// campagnes
			afficheCampagnes();
			// engins de peche 
			afficheEngins();
			break;
			
			// peche artisanale : on continue la selection par
			// secteurs>agglomerations>periodes d'enquete> grands types d'engins de peche
			case "art":
			//secteurs
			afficheSecteurs("art");
			// agglomerations 
			afficheAgglomerations();
			// periodes d'enquetes
			affichePeriodeEnquetes();
			// grands types d'engins
			afficheGrandsTypesEngins($_GET["exploit"]);
			break;
		}
		
		
	break; // end case "donnees"
	
	// statistiques de peche
	case "stats":
		// on doit poursuivre la selection par le type de stats
		afficheTypeStats();
		switch ($_GET["stats"]) {
			// statistiques par agglomerations
			case "agglo":
				//secteurs
				afficheSecteurs("art");
				// agglomerations 
				afficheAgglomerations();
				// periodes d'enquetes
				affichePeriodeEnquetes();
				// grands types d'engins
				afficheGrandsTypesEngins($_GET["exploit"]);
			break;
			//statistiques generales
			case "gen":
				// systemes ou secteurs
				afficheSecteurs2();
				//grands types d'engins
				afficheGrandsTypesEngins($_GET["exploit"]);
			break;
		}
	break; // end case "stats"
	
	
	// graphes
	case "graphes":
	break;
	
	//indicateurs ecologiques
	case "indics":
	break;
	
	// si aucun type n'est passe, on ne fait rien
	default:
	break;
}

echo('</div>'); // find div id=ex_selection


} // end if isset($_SESSION['s_ppeao_login_status']) && $_SESSION['s_ppeao_login_status']=='good'
// si l'utilisateur n'est pas connecte, on le lui signale et on affiche le formulaire de login
else {
	if ($_GET["step"]!='' && $_GET["step"]>4) {
	echo('<div  class="alerte"><p class="error">Vous devez &ecirc;tre connect&eacute; pour pouvoir poursuivre l&#x27;extraction des donn&eacute;es.<br /> Si vous avez un compte, connectez-vous en utilisant le formulaire ci-dessus.</p>');
		
	echo('<p class="error">Si vous n&#x27;avez pas de compte, vous pouvez en demander un en <a href="/contact.php">contactant les responsables du site</a>.</p></div>');}
}

// le script pour afficher ou masquer la selection
?> 
<script type="text/javascript" charset="utf-8">
	

	var mySlider = new Fx.Slide('selection_precedente', {duration: 500});
	// si on passe une valeur de 1 du parametre d'url open, on affiche la selection precedente, sinon on la masque
	if (gup('open')!=1) {mySlider.hide();}
	// affiche ou masque le DIV contenant la selection precedente
	function toggleSelection() {
		mySlider.toggle() //toggle the slider up and down.
	}

</script>
<?php 

// on affiche le compteur de campagnes / enquetes
echo('<div id="ex_compteur">');
echo($compteur["texte"]);
echo('</div>');
?>

<?php

// si on a exclu des campagnes ou enquetes, on ajoute le script pour afficher ou masquer les infos correspondantes
if (isset($compteur["filtrees"])) {
	?>
	<script type="text/javascript" charset="utf-8">
	
	var mySlider2 = new Fx.Slide('infos_filtre_contenu', {duration: 500});
	mySlider2.hide();
	// affiche ou masque le DIV contenant la selection precedente
	function toggleInfosFiltre() {
		mySlider2.toggle() //toggle the slider up and down.
	}
</script>
	<?php 
}
?>

<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

set_time_limit( 30 ) ; // FW 20170223 re-enable PHP time limit (large export)

?>
</div> <!-- end div id="main_container"-->
<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</body>
</html>
