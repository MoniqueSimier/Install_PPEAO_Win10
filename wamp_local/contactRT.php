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
				<p class="contact">La base PPEAO a &eacute;t&eacute; r&eacute;alis&eacute;e techniquement par: </p>

				<br/>
				<div id="referenceRT">
				<br/>
				<table align="center" cellspacing="12px">
					<tr>
						<td align="center"><img src="/assets/sinti-logo.jpg" alt="SINTI"/></td>
						<td align="center"><img src="/assets/asa-logo.jpg" alt="ASA"/></td>
						<td align="center"><img src="/assets/otolithe_logo.gif" alt="otolithe"/>
					</tr>
					<tr>
						<td></td>
						<td align="center">Advanced Solutions Accelerator</td>
						<td align="center">otolithe</td>
					</tr>
					<tr>
						<td></td>
						<td align="center">199 rue de l'Oppidum<br>34170 CASTELNAU LE LEZ</td>
						<td align="center"><a href="http://www.otolithe.com/" target="_blank">www.otolithe.com</a></td>
					</tr>
					<tr>
						<td></td>
						<td align="center">tel : +33 5 67 59 36 40<br>cell : +33 6 73 19 70 17</td>
						<td align="center">tel : +33 4 67 85 75 88<br>cell : +33 6 75 77 12 31</td>
					</tr>
					<tr>
						<td></td>
						<td align="center">Contact : <br>info@advancedsolutionsaccelerator.com</td>
						<td align="center">Contact : <a href="#" onClick="o='@';o='&#111;&#108;&#105;&#118;&#105;&#101;&#114;'+o;o='mailto:'+o;o+='otolithe.fr';this.href=o;"><script type="text/javascript"> <!-- o='@';o=''+o;o+='otolithe.fr';document.write(o);//--> </script>Olivier Roux</a></td>
					</tr>
				</table>
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
