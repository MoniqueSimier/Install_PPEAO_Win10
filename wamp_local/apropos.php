<?php 
// Modifié le 30-10-2017 Monique SIMIER
// Mis à jour Monique SIMIER 17-02-2017
// Accès aux documents techniques et notices
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
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
	<br>
	<div id="home_thumbs"><img src="/assets/home/1.jpg" width="120" height="70" alt="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)" title="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/2.jpg" width="120" height="70" alt="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)" title="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/3.jpg" width="120" height="70" alt="Paysage de mangrove, Estuaire de la Gambie" title="Paysage de mangrove, Estuaire de la Gambie">
<img src="/assets/home/4.jpg" width="120" height="70" alt="Lanche du Banc d'Arguin (Mauritanie)" title="Lanche du Banc d'Arguin (Mauritanie)">
<img src="/assets/home/5.jpg" width="120" height="70" alt="&Eacute;quipe de p&ecirc;che scientifique" title="&Eacute;quipe de p&ecirc;che scientifique">
<img src="/assets/home/6.jpg" width="120" height="70" alt="Pirogues de p&ecirc;che artisanale" title="Pirogues de p&ecirc;che artisanale"></div>
<br />
<div id="main_contact">
<div id="contentcontact">
<p class="texte">Les donn&eacute;es consultables dans le Syst&egrave;me d&rsquo;Informations PPEAO ont &eacute;t&eacute; recueillies soit par des techniques de <a href="/work/documentation/notices/Notice_generale_PPEAO_Peches_Experimentales.pdf">p&ecirc;che expérimentale</a>, soit par un suivi des <a href="/work/documentation/notices/Notice_generale_PPEAO_Peches_Artisanales.pdf">p&ecirc;ches artisanales</a> d&eacute;ploy&eacute;es sur les &eacute;cosyst&egrave;mes &eacute;tudi&eacute;s.  
</p><br>
<p class="texte">Le principe de l&rsquo;archivage est de conserver l&rsquo;information telle qu&rsquo;elle a &eacute;t&eacute; collect&eacute;e et valid&eacute;e sur le terrain. Cependant, pour diminuer les temps de consultation, plusieurs &eacute;tapes de calcul concernant les statistiques de p&ecirc;che artisanale ont été effectu&eacute;es de fa&ccedil;on automatique.
</p><br>
<p class="texte">La description de chaque <a href="/datasets.php"> jeu de donn&eacute;es par &eacute;cosyst&egrave;me et par type de collecte</a> est disponible.
</p><br>
<p class="texte">Plusieurs notices techniques d&eacute;crivent la base de donn&eacute;es et son organisation. Il est possible de consulter :
</p> <p class="texte">  - une notice sur <a href="/work/documentation/notices/PPEAO_DocTech01_Installation_PPEAO_sur_un_ordinateur.pdf">l&rsquo;installation du syst&egrave;me d&rsquo;informations PPEAO sur un PC en local</a>
</p> <p class="texte">  - une notice sur <a href="/work/documentation/notices/PPEAO_DocTech02_Acquisition_Donnees_PPEAO.pdf">l&rsquo;acquisition de nouvelles donn&eacute;es</a>
</p> <p class="texte">  - une description du <a href="/work/documentation/notices/PPEAO_DocTech03_Tables_et_MCD.pdf">mod&egrave;le conceptuel de donn&eacute;es et un dictionnaire des tables et variables</a> <!--,
// </p> <p class="texte">  - une description des <a href="/work/documentation/notices/6-PPEAO_Estimation_Stats_Pechart.pdf">calculs automatiques permettant l&rsquo;estimation des statistiques de p&ecirc;che</a> -->
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
