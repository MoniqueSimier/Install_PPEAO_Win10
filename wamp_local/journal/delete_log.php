<?php 
// script appelé via Ajax par la fonction JS deleteLog() et utilisé pour archiver (format CSV zippé) et supprimer les anciens logs

include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';

session_start();

logDelete("");


?>