<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="home";
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
	<title>ppeao::accueil</title>

	
</head>

<body>

<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="home">

<h1>PPEAO</h1>
<h2 style="text-align:center">Système d'informations sur les Peuplements de poissons et la P&ecirc;che artisanale<br> des Ecosyst&egrave;mes estuariens, lagunaires ou continentaux d&rsquo;Afrique de l&rsquo;Ouest</h2>
<br>
<?php

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
?>

<?php 

//echo(logDisplayShort('','','',"",5,""));
// cette fonction fait le ménage dans les vieux logs des modules d'exportation/extraction	
nettoieLogExport();
?>
<div id="home_thumbs"><img src="/assets/home/1.jpg" width="120" height="70" alt="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)" title="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/2.jpg" width="120" height="70" alt="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)" title="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/3.jpg" width="120" height="70" alt="Paysage de mangrove, Estuaire de la Gambie" title="Paysage de mangrove, Estuaire de la Gambie">
<img src="/assets/home/4.jpg" width="120" height="70" alt="Lanche du Banc d'Arguin (Mauritanie)" title="Lanche du Banc d'Arguin (Mauritanie)">
<img src="/assets/home/5.jpg" width="120" height="70" alt="&Eacute;quipe de p&ecirc;che scientifique" title="&Eacute;quipe de p&ecirc;che scientifique">
<img src="/assets/home/6.jpg" width="120" height="70" alt="Pirogues de p&ecirc;che artisanale" title="Pirogues de p&ecirc;che artisanale"></div>
<br />
<p class="texte">La base de données PPEAO archive des informations sur les poissons vivant dans <a href="/info_ecosystemes.php">diff&eacute;rents &eacute;cosyst&egrave;mes aquatiques</a> tant continentaux que lagunaires, estuariens ou c&ocirc;tiers de l&rsquo;Afrique de l&rsquo;Ouest. Les données collect&eacute;es concernent aussi bien l'&eacute;cologie des <a href='/info_especes.php'>esp&egrave;ces</a> que leur exploitation par la p&ecirc;che artisanale.<br>
<br>Ces informations sont le r&eacute;sultat de programmes de recherche men&eacute;s sur ces &eacute;cosyst&egrave;mes &agrave; partir de 1978.
</p>
<br>
<p class="texte">Cette base de donn&eacute;es a &eacute;t&eacute; con&ccedil;ue et r&eacute;alis&eacute;e par l&rsquo;Unit&eacute; de Recherches RAP (R&eacute;ponses adaptatives des populations et peuplements de poissons aux pressions de l&rsquo;environnement) de l&rsquo;<a href="http://www.ird.fr/" target="_blank">IRD</a> (Institut de Recherche pour le D&eacute;veloppement). Elle est aujourd'hui sous la responsabilité de l'UMR <a href="http://www-iuem.univ-brest.fr/LEMAR" target="_blank"> LEMAR </a> (UBO-CNRS-IRD-Ifremer) et de l'UMR <a href="http://www.umr-marbec.fr/" target="_blank"> MARBEC </a> (IRD-Ifremer-UM-CNRS).</p>


<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée
// ligne 56 avec lien RAP vers equipe 3 : 
//<p class="texte">Cette base de donn&eacute;es a &eacute;t&eacute; con&ccedil;ue et r&eacute;alis&eacute;e par l&rsquo;Unit&eacute; de Recherches <a href="http://www-iuem.univ-brest.fr/LEMAR" target="_blank">RAP</a> (R&eacute;ponses adaptatives des populations et peuplements de poissons aux pressions de l&rsquo;environnement) de l&rsquo;<a href="http://www.ird.fr/" target="_blank">IRD</a> (Institut de Recherche pour le D&eacute;veloppement). Cette unit&eacute; est aujourd'hui int&eacute;gr&eacute;e au LEMAR (UMR UBO-CNRS-IRD-Ifremer) au sein de <a href="http://www-iuem.univ-brest.fr/UMR6539/recherche/equipe-3" target="_blank">l'&eacute;quipe 3.</p>

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}
?>
<?php 
// debugage zones - groupes
// print_r( userGetAuthorizedZones( $_SESSION['s_ppeao_user_id'] )) ;
// fin
?>
</div> <!-- end div id="main_container"-->

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>

</body>
</html>
