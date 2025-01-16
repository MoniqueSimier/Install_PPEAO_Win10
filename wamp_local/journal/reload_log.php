<?php 
// script appelé via Ajax par la fonction reloadLog() et permettant de rafraichir l'affichage du journal une fois que celui-ci  a été archivé et vidé

include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';

echo(logDisplayFull('','','',"","",""));


?>