<?php
// ce script verifie si un fichier lock ou un fichier zip est present pour le protage et retourne le message adequat
$lockfile=$_SERVER["DOCUMENT_ROOT"].'work/portage/import.lock';

// si le fichier lock existe, une importation est en cours
if (file_exists($lockfile)) {$theReply='<div class="error">Une importation est actuellement en cours, merci de r&eacute;essayer dans un moment.<br /><br /><br /></div>';}
// sinon, on teste si le fichier .zip est present
else {
	$zipfile=$_SERVER["DOCUMENT_ROOT"].'work/portage/Sql_Access_Postgres.zip';
	if (file_exists($zipfile)) {
		// finding what the next multiple of 5 minutes is
			function minutes_ceil ($minutes = '5', $format = "H:i")
			{
			    // by Femi Hasani [www.vision.to]
			    $rounded = ceil(time() / ($minutes * 60)) * ($minutes * 60);
			    return date($format, $rounded);
			}
			$next_run=minutes_ceil();
			
			$theReply='<div class="error" style="margin-top:10px; margin-bottom:20px">Un fichier Sql_Access_Postgres.zip est d&eacute;j&agrave; pr&eacute;sent sur le serveur, il sera import&eacute; automatiquement par le script CRON &agrave; '.$next_run.'</div>';
	}
}

echo($theReply);

?>