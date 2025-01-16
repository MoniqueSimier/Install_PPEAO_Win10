<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// Mis à jour Yann LAURENT, 07-07-2008
// code commun à toutes les pages (demarrage de session, doctype etc.)
// definit a quelle section appartient la page
$section="portage";
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
$zone=3; // zone portage (voir table admin_zones)
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<?php 
	// les balises meta communes  toutes les pages
	//include $_SERVER["DOCUMENT_ROOT"].'meta.inc';
	?>
	<title>PPEAO Recomposition des donnees : choix BD</title>
	<link href="/styles/mainstyles.css" title="mainstyles" rel="stylesheet" type="text/css" />
	<script src="/js/basic.js" type="text/javascript" charset="utf-8"></script>
	<script src="/js/ajaxEfface.js"></script>
	</head>
	<body>
		<?php 
		// A virer a passer dans l'include commun connect.inc
		$bdd = $_POST['base'];
		if($bdd==""){
			$bdd=$db_default;
		}
		// le menu horizontal
		include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc'
		?>
		<div id="main_container" class="home">
			<div id="BDDetail">
			<?php  $subsection="manuel_recomp"; include $_SERVER["DOCUMENT_ROOT"].'/left_navbar.inc'; ?>
			</div>
			<div id="subContent">
			
			<?php  // Code original a mettre à jour ?>
			<div id='headerinfo'>
			<h1>Recomposition automatique des donnees d'enquetes.</h1>
			<br/>
			</div>
			Une enquete de peche est l'operation elementaire d'observation des debarquements. Dans un cas ideal, toutes les informations demandees sont relevees par l'enqueteur. Dans la plupart des cas, une partie de l'information manque.
			Le but de ce module est de recomposer toutes les enqu&etes, une par une, pour obtenir des enquetes ideales. Cette recomposition comprend 3 phases : 
			<ul>
				<li>une estimation du nombre et du poids des poissons d'une fraction dite debarquee</li>
				<li>une comparaison des poids des fractions debarquees avec le poids total du debarquement annonce par l'enqueteur</li>
				<li>la prise en compte eventuelle de fractions non observ&ees directement par l'enqueteur.</li>
				<li>Version JME 05 2009.<li>
			</ul>

			<br/><br/>
			<div id="dbinfo">
			<h2><?php echo "La Base ".$bdd." contient"; ?> :</h2>

			<?php 	include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';

			//////////////////////////////////////////////////////////////////////////////////////////////
			//                      Récupération du nb d'enregistrements à traiter                      //
			//                                 et du nb déjà traités                                    //
			//////////////////////////////////////////////////////////////////////////////////////////////
			// A virer, a remplacer par $connectPPEAO
			$connection =pg_connect ("host=".$host." dbname=".$bdd." user=".$user." password=".$passwd);
			if (!$connection) {  echo "Une erreur de connection au serveur s'est produite"; exit;}
			
			// Creation et envoi de la requete
			$query = "select count(art_debarquement.id) FROM art_debarquement";
			$result = pg_query($connection, $query);
			if (!$result) {  echo "Une erreur s'est produite";  exit;}
			// Recuperation du resultat
			$row= pg_fetch_row($result);
			$nb_enr = $row[0];
			print ("<div id='nbEnquete'>".$nb_enr . " enquetes a traiter dont");
			$query = "select count(id)
			 FROM art_debarquement_rec";
			$result = pg_query($connection, $query);
			$row= pg_fetch_row($result);
			$nb_deja_rec = $row[0];
			if ($nb_deja_rec == 0){print (" ".$nb_deja_rec);}
			else {print (" ".$nb_deja_rec);}
			print " enquetes deja recomposees. </div>";
			
			pg_close();
			?>
			<div id="formRecomp">
			<br/>
			<form name="form">
							

				
				<p>
				Vous pouvez entrer une adresse mail.<br/>
				<input type="text" name="adresse" id="adresse">
				<br/>
				
				Si vous rentrez une adresse valide, 
				il vous sera envoye un mail de confirmation a la fin de la recomposition des donnees.<br/>
				<br/>
				

				<input type="hidden" name="nb_enr" value="<?php  print($nb_enr);?>" />
				<input type="hidden" name="base" value="<?php  print($bdd);?>" />
				<input type="hidden" name="aff" value="1" >
				<!--<input type="submit" name="Recomposition" value="Recomposer les données" onClick="pop_it3(form);">-->
			 	<input type="button" name="Recomposition" value="Recomposer les donnees" onClick="runRecomp('<?php  print($nb_enr);?>','<?php  print($bdd);?>');">
			  	</p>
			</form>
			</div>
			
			<div id="formEfface">
			<form >
				<input type="button" name="Effacement" value="Effacer les donnees recomposees" onClick="runClear('<?php  print($bdd);?>')"/>
			</form>
			</div>
			<div id="formEffaceResult"> <?php  // Pour y mettre le resultat de l'effacement des données ! ?>

			</div>			
			
		</div>
	</div>

	<?php  
	// fin du code d'origine
	include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';
	
	?>
	</body>
</html>



