<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="gerer";
$subsection="portage";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=2; // zone portage (voir table admin_zones) changement JME 03 2016

$zipfileimportlaunch=$_POST["$zipfileimportlaunch"];

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<title>ppeao::manipulation de donn&eacute;es</title>

<script src="/ckfinder/ckfinder.js" type="text/javascript"></script>
</head> 
 <body>
<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';

?>
<div id="main_container" class="home">

<script type="text/javascript" charset="utf-8">
	function BrowseFiles()
				{
						
					// You can use the "CKFinder" class to render CKFinder in a page:
					var finder = new CKFinder() ;
					finder.removePlugins = 'basket';
					finder.basePath = '/ckfinder/' ;
					finder.startupPath="Portage:portage/"
					finder.width=700;
					finder.height=350;
					finder.popup() ;
				}
</script>
<script type="text/javascript" charset="utf-8">

function triggerZipFileImport() {
	$("zipfileimport").submit();
}

</script>

<!-- on teste toutes les 10" si une importation est en cours ou si un fichier zip est present -->
<script type="text/javascript" charset="utf-8">
	function zipFileCheck(){

	var xhr = getXhr();


	// what to do when the response is received
	xhr.onreadystatechange = function(){
		// while waiting for the response, display the loading animation
		var theLoader='<div align="center">connexion...<img src="/assets/ajax-loader.gif" alt="..." title="..." valign="center"/></div>';
		
		var theMessage=document.getElementById("theMessage");
		
		if(xhr.readyState < 4) { theMessage.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			theReply = xhr.responseText;
			theMessage.innerHTML = theReply;

		}// end function()
	} // end ajaxLogin


	// using GET to send the request
					xhr.open("GET","portage-ajax.php",true);
	xhr.send(null);
}

zipFileCheck.periodical(10000);

</script>

<?php 
	if (isset($_SESSION['s_ppeao_user_id'])){ 
		$userID = $_SESSION['s_ppeao_user_id'];
	} else {
		$userID=null;
	}
	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {
	?>


		<!-- <div id="BDDetail">
		<?php  // enleve pour ne laisser que le portage automatique
		// $subsection="home"; include $_SERVER["DOCUMENT_ROOT"].'/left_navbar.inc'; ?>
		</div> -->
		<div id="subContent">
		<h2 style="text-align:center">Import / recalcul de donn&eacute;es <!-- en manuel ou : lancement en automatique--></h2>
		<br/>
		<p>Cette section vous permet de lancer les traitements sp&eacute;cifiques sur les bases de donn&eacute;es import&eacute;es.</p>
		<p>Ce portage est effectué en 3 étapes : Importation d'un fichier Sql_Access_Postgres.zip; Transfert des données vers bdpeche; Portage automatique des données de bdpeche vers bdppeao. </p>
		<p>Le portage automatique effectue un certain nombre de contr&ocirc;les pour assurer la qualit&eacute; des donn&eacute;es import&eacute;es et lance automatiquement les programmes de calcul de statistiques de pêche.</p>
		<!-- <p>Ces programmes peuvent &ecirc;tre aussi lanc&eacute;s &agrave; la demande depuis le portage manuel.  </p> -->
		<!--<p>Vous pouvez &eacute;galement acc&eacute;der au module d&#x27;importation de donn&eacute;es dans la base bdpeche (application &quot;SINTI&quot;).</p>-->
		<ul class="list">
			<li class="listitem"><a href="javascript:BrowseFiles();" ><b>Charger un fichier de Sql_Access_Postgres.zip sur le serveur <?php echo($_SERVER["SERVER_NAME"])?> pour qu'il soit importé par le script CRON</b></a></li>
			<!-- <li> pour afficher le message si une importation est en cours ou si le zip est present-->
			<li id="theMessage" style="list-style: none;"></li>
			
			<li class="listitem"><a href="/acces_sinti.php" ><b>Acc&eacute;der &agrave; l&#x27;application pour importer des donn&eacute;es dans la base bdpeche</b></a></li>
			<li class="listitem"><a href="/portage_auto.php" ><b>Lancer le portage automatique</b></a></li>
			<!--<li class="listitem"><a href="/portage_auto_partiel.php" ><b>R&eacute;aliser un portage sur des donn&eacute;es déjà import&eacute;es dans BDPPEAO.</b></a></li>-->
            <!-- <li class="listitem"><a href="/portage_manuel.php" ><b>Portage manuel</b></a></li> -->
			
			

		</ul>
		</div>	
	



<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</div> <!-- end div id="main_container"-->
</body>
</html>
