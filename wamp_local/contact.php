<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="contacter";
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
	<title>ppeao::contact</title>

	
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
			<h2 style="text-align:center">Syst&egrave;me d&#x27;informations sur les Peuplements de poissons et la P&ecirc;che artisanale<br> des &Eacute;cosyst&egrave;mes estuariens, lagunaires ou continentaux d&rsquo;Afrique de l&rsquo;Ouest</h2><br>
	<div id="home_thumbs"><img src="/assets/home/1.jpg" width="120" height="70" alt="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)" title="Pirogue de p&ecirc;che artisanale sur le lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/2.jpg" width="120" height="70" alt="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)" title="Nasses de p&ecirc;che artisanale, lac de S&eacute;lingu&eacute; (Mali)">
<img src="/assets/home/3.jpg" width="120" height="70" alt="Paysage de mangrove, Estuaire de la Gambie" title="Paysage de mangrove, Estuaire de la Gambie">
<img src="/assets/home/4.jpg" width="120" height="70" alt="Lanche du Banc d'Arguin (Mauritanie)" title="Lanche du Banc d'Arguin (Mauritanie)">
<img src="/assets/home/5.jpg" width="120" height="70" alt="&Eacute;quipe de p&ecirc;che scientifique" title="&Eacute;quipe de p&ecirc;che scientifique">
<img src="/assets/home/6.jpg" width="120" height="70" alt="Pirogues de p&ecirc;che artisanale" title="Pirogues de p&ecirc;che artisanale"></div>
<br />
<div id="main_contact">

			<div id="contentcontact">
				<p class="contact">Pour pouvoir acc&eacute;der librement &agrave; tout ou partie de la base de donn&eacute;s PPEAO, contactez les gestionnaires de la base qui vous fourniront des codes d&rsquo;acc&egrave;s.</p><br/>
				<div id="listcontact">
				<ul class="contact"> 
					<li class="contact"> Contacter PPEAO : <a href="#" onclick="o='@';o='&#112;&#112;&#101;&#97;&#111;'+o;o='mailto:'+o;o+='ird.fr'+'?subject=PPEAO';this.href=o;"><script language="JavaScript"> 
o='@';o='&#112;&#112;&#101;&#97;&#111;'+o;o+='ird.fr';document.write(o);
</script></a> </li>
				</ul>
				</div>
				<br/>
				<p class="contact">L'application PPEAO a &eacute;t&eacute; r&eacute;alis&eacute;e gr&acirc;ce au financement de la DSI (Direction du Syst&egrave;me d&rsquo;Information) de l&rsquo;IRD, via les projets Spirales. Elle a &eacute;t&eacute; d&eacute;velopp&eacute;e par <a href="/contactRT.php" >plusieurs partenaires techniques</a>.
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

<!-- 17/02/2017 : Suppression des adresses personnelles Simier et Ecoutin au profit de l'adresse générique ppeao
				<div id="listcontact"><h2>contact</h2>
				<ul class="contact">
					<li class="contact">Monique Simier : <a href="#" onclick="o='@';o='&#109;&#111;&#110;&#105;&#113;&#117;&#101;&#46;&#115;&#105;&#109;&#105;&#101;&#114;'+o;o='mailto:'+o;o+='ird.fr'+'?subject=PPEAO';this.href=o;"><script language="JavaScript"> <!--
o='@';o='&#109;&#111;&#110;&#105;&#113;&#117;&#101;&#46;&#115;&#105;&#109;&#105;&#101;&#114;'+o;o+='ird.fr';document.write(o);
</script></a> </li>
					<li class="contact">Jean-Marc &Eacute;coutin : <a href="#" onclick="o='@';o='&#106;&#101;&#97;&#110;-&#109;&#97;&#114;&#99;&#46;&#101;&#99;&#111;&#117;&#116;&#105;&#110;'+o;o='mailto:'+o;o+='ird.fr'+'?subject=PPEAO';this.href=o;"><script language="JavaScript"> <!--
o='@';o='&#106;&#101;&#97;&#110;-&#109;&#97;&#114;&#99;&#46;&#101;&#99;&#111;&#117;&#116;&#105;&#110;'+o;o+='ird.fr';document.write(o);
</script></a> </li> 
				</ul>
				</div> -->
