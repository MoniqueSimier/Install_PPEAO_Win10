<?php 
// Créé par Gaspard BERTRAND, 19/09/2011
// definit a quelle section appartient la page
$section="liens";
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
	<title>ppeao::liens</title>

	
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
	<br>

<div id="home_thumbs"><img src="/assets/home/1.jpg" width="120" height="70" alt="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)" title="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/2.jpg" width="120" height="70" alt="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)" title="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/3.jpg" width="120" height="70" alt="Paysage de mangrove, Estuaire de la Gambie" title="Paysage de mangrove, Estuaire de la Gambie">
<img src="/assets/home/4.jpg" width="120" height="70" alt="Lanche du Banc d'Arguin (Mauritanie)" title="Lanche du Banc d'Arguin (Mauritanie)">
<img src="/assets/home/5.jpg" width="120" height="70" alt="&Eacute;quipe de p&ecirc;che scientifique" title="&Eacute;quipe de p&ecirc;che scientifique">
<img src="/assets/home/6.jpg" width="120" height="70" alt="Pirogues de p&ecirc;che artisanale" title="Pirogues de p&ecirc;che artisanale"></div>
<br />
			
		<div id="main_contact">

			
				</div>
				<br/>
				<p class="contact">Ce projet a &eacute;t&eacute; rendu possible gr&acirc;ce au financement de la Direction du Système d'Information de l'IRD.
				<p class="contact">Il a &eacute;t&eacute; r&eacute;alis&eacute; par l&rsquo;Unit&eacute; de Recherches RAP (R&eacute;ponses adaptatives des populations et peuplements de poissons aux pressions de l&rsquo;environnement) de l&rsquo;<a href="http://www.ird.fr/" target="_blank">IRD</a> (Institut de Recherche pour le D&eacute;veloppement). La base de données est aujourd'hui sous la responsabilité de l'UMR <a href="http://www-iuem.univ-brest.fr/LEMAR" target="_blank"> LEMAR </a> (UBO-CNRS-IRD-Ifremer) et de l'UMR <a href="http://www.umr-marbec.fr/" target="_blank"> MARBEC </a> (IRD-Ifremer-UM-CNRS). </p>
				</p><br>
				</p><br>
				<p class="contact" style="font-weight:bold">Liens connexes :
				</p><br>
				<p class="contact" style="padding-left:35px">Cartographie de la répartition géographique des poissons d&rsquo;eaux douces et saum&acirc;tres en Afrique : <a href="http://www.poissons-afrique.ird.fr/faunafri/" target="_blank">Faunafri</a>.
			</div>
		</div>
	</div> <!-- end div id="main_container"-->
	
	
	<?php 
	// note : on termine la boucle testant si l'utilisateur a accès à la page demandée
	
// Lien vers RAP supprimé à la ligne 61 car pointe sur même page que equipe 3
//				<p class="contact">Il a &eacute;t&eacute; r&eacute;alis&eacute; par l&rsquo;Unit&eacute; de Recherches <a href="http://www-iuem.univ-brest.fr/UMR6539/recherche/equipe-3" target="_blank">RAP</a> (R&eacute;ponses adaptatives des populations et peuplements de poissons aux pressions de l&rsquo;environnement) de l&rsquo;<a href="http://www.ird.fr/" target="_blank">IRD</a> (Institut de Recherche pour le D&eacute;veloppement). Cette unit&eacute; est aujourd'hui int&eacute;gr&eacute;e au sein de <a href="http://www-iuem.univ-brest.fr/UMR6539/recherche/equipe-3" target="_blank">l'&eacute;quipe 3</a> du LEMAR (Laboratoire des sciences de l'Environnement MARin - UMR UBO-CNRS-IRD-Ifremer). </p>
	
// Lien vers Ecoscope enlevé à la ligne 66 car site indisponible
//					<p class="contact" style="padding-left:35px">Base de connaissances sur les écosystèmes marins exploitées : <a href="http://www.ecoscope.org" target="_blank">l'Ecoscope</a>.

// Lien vers reshal enlevé également
//				<p class="contact" style="padding-left:35px">Réseau halieutique et d'écologie aquatique en Afrique de l'Ouest : <a href="http://www.netvibes.com/reshal" target="_blank">reshal</a>.

// Lien vers DSI enlevé le 6/01/2017
//				<p class="contact">Ce projet a &eacute;t&eacute; rendu possible gr&acirc;ce au financement de la <a href="https://www.ird.fr/dsi/" target="_blank">Direction du Système d'Information de l'IRD</a>.

	
	;} // end if (userHasAccess($_SESSION['user_id'],$zone))
	
	// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
	else {userAccessDenied($zone);}
	?>
	
	<?php 
	include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';
	
	?>
</body>
</html>
