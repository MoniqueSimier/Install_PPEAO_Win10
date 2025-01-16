<?php 
// Modifié le 30-10-2017 Monique SIMIER
// Créé par Monique SIMIER 17-02-2017
// Accès aux notices descriptives par écosystème
$section="sinformer";
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
	<title>ppeao::&agrave; propos de PPEAO</title>

	
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
	
	<div id="home_thumbs"><img src="/assets/home/1.jpg" width="120" height="70" alt="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)" title="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/2.jpg" width="120" height="70" alt="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)" title="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/3.jpg" width="120" height="70" alt="Paysage de mangrove, Estuaire de la Gambie" title="Paysage de mangrove, Estuaire de la Gambie">
<img src="/assets/home/4.jpg" width="120" height="70" alt="Lanche du Banc d'Arguin (Mauritanie)" title="Lanche du Banc d'Arguin (Mauritanie)">
<img src="/assets/home/5.jpg" width="120" height="70" alt="&Eacute;quipe de p&ecirc;che scientifique" title="&Eacute;quipe de p&ecirc;che scientifique">
<img src="/assets/home/6.jpg" width="120" height="70" alt="Pirogues de p&ecirc;che artisanale" title="Pirogues de p&ecirc;che artisanale"></div>

<div id="main_contact">
<div id="contentcontact">
<p class="texte">Donn&eacute;es concernant les p&ecirc;ches scientifiques :
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Ebrie_1979_1982_peches_experimentales.pdf">Lagune Ebri&eacute; (C&ocirc;te d&rsquo;Ivoire) 1979-1982</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Saloum_1990_1997_peches_experimentales.pdf">Delta du Sine-Saloum (S&eacute;n&eacute;gal) 1990-1997</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Guinee_Bissau_1993_peches_experimentales.pdf">Archipel des Bijagos et Rio Grande de Buba (Guin&eacute;e Bissau) 1993</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Guinee_1993_1994_peches_experimentales.pdf">Estuaire de la Fatala et bras de mer de Dangara (Guin&eacute;e) 1993-1994</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Gambie_2000_2003_peches_experimentales.pdf">Estuaire de la Gambie (The Gambia) 2000-2003</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Saloum_2001_2007_peches_experimentales.pdf">Delta du Sine-Saloum (S&eacute;n&eacute;gal) 2001-2007</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Mali_2000_2003_peches_experimentales.pdf">Lacs de Manantali et Selingue (Mali) 2002-2003</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Bamboung_2003_2012_peches_experimentales.pdf">AMP du bolon de Bamboung (S&eacute;n&eacute;gal) 2003-2012</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Arguin_2008_2010_peches_experimentales.pdf">Parc National du Banc d'Arguin (Mauritanie) 2008-2010</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Urok_2011_2013_peches_experimentales.pdf">AMP des Iles Urok (Guin&eacute;e Bissau) 2011-2013</a>.
</p><br>
<p class="texte">Donn&eacute;es concernant les p&ecirc;ches artisanales :
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Ebrie_1978_1985_peches_artisanales.pdf">Lagune Ebri&eacute; (C&ocirc;te d&rsquo;Ivoire) 1978-1985</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Lac_Togo_1983_1995_peches_artisanales.pdf">Lac Togo (Togo) 1983-1984, 1989 et 1995</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Delta_Central_Niger_1990_1992_peches_artisanales.pdf">Delta Central du Niger (Mali) 1990-1992</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Saloum_1990_1993_peches_artisanales.pdf">Delta du Sine-Saloum (S&eacute;n&eacute;gal) 1990-1993</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Lacs_Mali_1994_2003_peches_artisanales.pdf">Lacs de Manantali et Selingue (Mali) 1994-1995 et 2002-2003</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Gambie_2001_2002_peches_artisanales.pdf">Estuaire de la Gambie (The Gambia) 2001-2002</a>,
</p> <p class="texte">  - <a href="/work/documentation/metadata/files/Descriptif_Casamance_2005_peches_artisanales.pdf">Estuaire de la Casamance (S&eacute;n&eacute;gal) 2005</a>.

</p>
</div>
</div>
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
