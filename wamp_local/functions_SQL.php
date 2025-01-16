<?php 
//*****************************************
// functions_SQL.php
//*****************************************
// Created by Yann Laurent
// 2008-07-11 : creation
//*****************************************
// Ce fichier contient une serie de fonctions php utilisées pour la génération de scripts SQL ou de leur manipulation



//*********************************************************************
// OpenFileReverseSQL : écrit dans le fichier de compte rendu de comparaison
function OpenFileReverseSQL ($howOpen,$direcLog,$PasAutorisation) {
// Cette fonction permet d'ouvrir le fichier contenant les scripts SQL pour réaliser les actions inverses.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// howOpen : comment ouvrir le fichier : en ajout (='ajout') ou en ecrasement (='ecras') ?
// direcLog : le répertoire ou se trouve le fichier log
// PasAutorisation : variable pour test linux a priori toujours vrai
//*********************************************************************
// En sortie : 
// - Renvoie la ressource  fichier ouvert
//*********************************************************************
	if (! $PasAutorisation) {
		if (! file_exists($direcLog)) {
			if (! mkdir($direcLog) ) {
				$messageGen = " erreur de création du répertoire de log";
				logWriteTo(4,"error","Erreur de creation du repertoire de log dans comparaison.php","","","0");
				return false;
			}
		}
		if ($howOpen =="ajout") {
			$ficOpen = fopen($direcLog."/CompReverseSQL.sql", "a+");
		} else {
			$ficOpen = fopen($direcLog."/CompReverseSQL.sql", "w");
		
		}
		if (! $ficOpen ) {
			logWriteTo(4,"error","Erreur d'ouverture du fichier SQL reverse (function OpenFileReverseSQL) ","","","0");
		}
		return $ficOpen;
	}
}

//*********************************************************************
// CloseFileReverseSQL : écrit dans le fichier de compte rendu de comparaison
function CloseFileReverseSQL ($fileRevSQL,$PasAutorisation) {
// Cette fonction permet d'ouvrir le fichier contenant les scripts SQL pour réaliser les actions inverses.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// fileRevSQL : fichier SQL
// PasAutorisation : variable pour test linux a priori toujours vrai
//*********************************************************************
// En sortie : 
// -
//*********************************************************************
	if (! $PasAutorisation) {
		fclose($fileRevSQL);
	}
}

//*********************************************************************
// WriteFileReverseSQL : écrit dans le fichier de compte rendu de comparaison
function WriteFileReverseSQL ($fileRevSQL,$script,$PasAutorisation) {
// Cette fonction permet d'ouvrir le fichier contenant les scripts SQL pour réaliser les actions inverses.
//*********************************************************************
// En entrée, les paramètres suivants sont :
// fileRevSQL : fichier SQL
// script : le script SQL à écrire
// PasAutorisation : variable pour test linux a priori toujours vrai
//*********************************************************************
// En sortie : 
// -
//*********************************************************************
// On teste si un ; est présent à la fin du script SQL, sinon le rajouter
	if (! $PasAutorisation) {
		$pos = strrpos($script, ";");
		if ($pos === false) { 
			$script.=";";
		} else {
			if ( ! $pos == strlen($script)) {
				$script.=";";
			}
		}
		if (! fwrite($fileRevSQL,$script."\r\n") ) {
			logWriteTo(4,"error","Erreur d'ajout dans le fichier SQL reverse (function OpenFileReverseSQL)","","","0");
		}	
	}	
}

//*********************************************************************
// retourne des métadonnées sur les colonnes d'une table postgresql
function getTableColumnsMetadata ($connection,$table) {
// $connection : la connexion à la base postgres
// $ table : le nom de la table
$sql='SELECT ordinal_position,
         column_name,
         data_type,
         column_default,
         is_nullable,
         character_maximum_length,
         numeric_precision
    FROM information_schema.columns
   WHERE table_name = \''.$table.'\'
ORDER BY ordinal_position;';

$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$meta=pg_fetch_all($result);

pg_free_result($result);

return $meta;

}



//*********************************************************************
// retourne la liste des contraintes d'une table postgresql
function getTableConstraints ($connection,$table) {
// $connection : la connexion à la base postgres
// $table : le nom de la table dans la base
$sql='SELECT DISTINCT tc.constraint_name, tc.constraint_type
    FROM information_schema.table_constraints tc
   	WHERE tc.table_name = \''.$table.'\'
	ORDER BY tc.constraint_name';

$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$constraints=pg_fetch_all($result);
pg_free_result($result);

// maintenant, il faut récupérer les détails sur ces contraintes
return $constraints;

}

//*********************************************************************
// retourne des informations détaillées sur une contrainte postgresql
function getConstraintDetails ($connection,$table,$constraint) {
// $connection : la connexion à la base postgres
// $table : le nom de la table
// $constraint : le nom de la contrainte

$sql='	SELECT tc.constraint_name, tc.constraint_type, tc.table_name, kcu.column_name, tc.is_deferrable, tc.initially_deferred, rc.match_option AS match_type, rc.update_rule AS on_update, rc.delete_rule AS on_delete, ccu.table_name AS references_table, ccu.column_name AS references_field , cc.check_clause
		FROM information_schema.table_constraints tc 
			LEFT JOIN information_schema.check_constraints cc ON tc.constraint_catalog=cc.constraint_catalog AND tc.constraint_schema=cc.constraint_schema AND tc.constraint_name=cc.constraint_name
			LEFT JOIN information_schema.key_column_usage kcu ON tc.constraint_catalog = kcu.constraint_catalog AND tc.constraint_schema = kcu.constraint_schema AND tc.constraint_name = kcu.constraint_name 
			LEFT JOIN information_schema.referential_constraints rc ON tc.constraint_catalog = rc.constraint_catalog AND tc.constraint_schema = rc.constraint_schema AND tc.constraint_name = rc.constraint_name 
			LEFT JOIN information_schema.constraint_column_usage ccu ON rc.unique_constraint_catalog = ccu.constraint_catalog AND rc.unique_constraint_schema = ccu.constraint_schema AND rc.unique_constraint_name = ccu.constraint_name  
		WHERE tc.table_name = \''.$table.'\' AND tc.constraint_name = \''.$constraint.'\''; 

$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$details=pg_fetch_assoc($result);
pg_free_result($result);

return ($details);

}


//*********************************************************************
// retourne des informations détaillées sur toutes les contraintes d'une table postgresql
function getTableConstraintDetails ($connection,$table) {

// $connection : la connexion à la base postgres
// $table : le nom de la table

// on liste les contraintes sur la table
$constraints=getTableConstraints($connection,$table);

// on récupere les détails sur chaque contrainte
$allConstraintDetails=array();
foreach ($constraints as $constraint) {
	$allConstraintDetails[$constraint["constraint_name"]] = getConstraintDetails($connection,$table,$constraint["constraint_name"]);
	//note : dans le cas d'une contrainte de type CHECK, il faut "extraire" les informations de la check_clause
	$theDetail=$allConstraintDetails[$constraint["constraint_name"]];
	
	if ($theDetail["constraint_type"]=='CHECK') {
		// ***dirty hack***... pour les CHECK simulant un ENUM : on teste si la clause contient un "OR"
		if(strpos($theDetail["check_clause"],'OR')==TRUE) {
			//alors on considère que l'on a affaire à une énumération
			// et on extrait des chaines de type "(column = value)"
			$clause=$theDetail["check_clause"];
			

			$clause=str_replace(")", "", $clause);
			$clause=str_replace("(", "", $clause);
			$clause=str_replace(" ", "", $clause);
			$theClauses=explode("OR",$clause);
			$theColumn=substringBefore($theClauses[0],'=');
			// on supprime les eventuels ::text si on a affaire a un champ char
			$theColumn=str_replace('::text','',$theColumn);
			foreach ($theClauses as $theClause) {
				$theValues[]=substringAfter($theClause,'=');
			}
			$newClause.=arrayToList($theValues,',','');
			// on supprime les eventuels ::text si on a affaire a un champ char
			$newClause=str_replace('::text','',$newClause);
			// on supprime les eventuels ' si on a affaire a un champ char
			$newClause=str_replace('\'','',$newClause);
			
			// on ajoute ces informations dans les détails sur la contrainte
			$allConstraintDetails[$constraint["constraint_name"]]["constraint_type"]='ENUM';
			$allConstraintDetails[$constraint["constraint_name"]]["column_name"]=$theColumn;
			$allConstraintDetails[$constraint["constraint_name"]]["check_clause"]=$newClause;
			
		}
	}
}

// on retourne le tableau contenant les informations détaillées sur les contraintes
return $allConstraintDetails;

}


//*********************************************************************
// retourne des informations détaillées sur toutes les contraintes sur une colonne d'une table postgresql
function getColumnConstraintDetails ($allConstraintDetails,$column) {
// $allConstraintDetails : le résultat retourné par la fonction getTableConstraintDetails() i.e. les détails des contraintes d'une table
// $column : le nom de la colonne pour laquelle on veut avoir la liste des contraintes

// on sélectionne les contraintes relatives à la colonne voulue
$columnConstraints=array();
foreach ($allConstraintDetails as $oneConstraintDetails) {
	if ($oneConstraintDetails["column_name"]==$column) {
		$columnConstraints[$oneConstraintDetails["constraint_name"]]=$oneConstraintDetails;
		}
}

return $columnConstraints;

}

//*********************************************************************
// retourne des informations détaillées (metadata et contraintes) sur toutes les colonnes d'une table postgresql
function getTableColumnsDetails($connection,$table) {

// $connection : la connexion à la base postgres
// $table : le nom de la table dans la base de données

// on collecte les metadonnees sur les colonnes
$meta=getTableColumnsMetadata($connection,$table);
// on collecte la liste des contraintes
$details=getTableConstraintDetails ($connection,$table);
// on collecte le detail de chaque contrainte
$columnDetails=getColumnConstraintDetails ($details,$column);


// on récupère les commentaires sur les colonnes de la table
$dbname=pg_dbname($connection);
$sql_comments='select cols.column_name,
(select pg_catalog.obj_description(oid) from pg_catalog.pg_class c where c.relname=cols.table_name) as table_comment
,(select pg_catalog.col_description(oid,cols.ordinal_position::int) from pg_catalog.pg_class c where c.relname=cols.table_name) as column_comment
from information_schema.columns cols
where cols.table_catalog=\''.$dbname.'\' and cols.table_name=\''.$table.'\'';
$result_comments=pg_query($connection,$sql_comments) or die('erreur dans la requete : '.$sql_comments. pg_last_error());
$array_comments=pg_fetch_all($result_comments);
pg_free_result($result_comments);
$comments=array();
foreach($array_comments as $row) {
	$comments[$row["column_name"]]=$row["column_comment"];
}

// on construit un tableau contenant les metadata et les contraintes sur chaque colonne de la table
$columnsDetails=array();
foreach($meta as $column) {
	
	$columnsDetails[$column["column_name"]]=$column;
	//on ajoute au tableau les éventuelles contraintes sur cette colonne
	$columnsDetails[$column["column_name"]]["constraints"]=getColumnConstraintDetails ($details,$column["column_name"]);
	// on ajoute au tableau le commentaire sur cette colonne
	$columnsDetails[$column["column_name"]]["commentaire"]=$comments[$column["column_name"]];
	
}


return $columnsDetails;
}


//*********************************************************************
// teste si une colonne est une cle entrangere et, si oui, retourne les infos sur la colonne parent 
function getTableColumnForeignReference($connection,$table,$column) {
// $connection : la connection a la base
// $table : le nom de la table dasn la base
// $column : le nom de la colonne a verifier
	$allConstraints=getTableConstraintDetails ($connection,$table);
	
	$constraints=getColumnConstraintDetails ($allConstraints,$column);
	$foreignReference=array();
	if (!my_empty($constraints)) {
		foreach($constraints as $constraint) {
			if ($constraint["constraint_type"]=="FOREIGN KEY") {
				$foreignReference=array(
				"is_foreign"=>true,
				"table_name"=>$constraint["table_name"],
				"column_name"=>$constraint["column_name"],
				"references_table"=>$constraint["references_table"],
				"references_field"=>$constraint["references_field"]
				);}
		} // end foreach($constraints as $constraint)
	} // end if (!my_empty($constraints))
	return $foreignReference;
}


//*********************************************************************
// retourne l'éventuelle séquence associée à une colonne d'une table postgresql
function getTableColumnSequence($connection,$table,$column) {

// $connection : la connexion à la base postgres
// $table : le nom de la table dans la base de données
// note : dirty hack, il doit y avoir une meilleure façon de faire
$seqSql='SELECT relname
  FROM pg_class
 WHERE relkind = \'S\' and relname = \''.$table.'_'.$column.'_seq\' 
   AND relnamespace IN (
        SELECT oid
          FROM pg_namespace
         WHERE nspname NOT LIKE \'pg_%\'
           AND nspname != \'information_schema\');'
;

$seqResult=pg_query($connection,$seqSql) or die('erreur dans la requete : '.$seqSql. pg_last_error());
$seqCount=pg_num_rows($seqResult);
if ($seqCount>0) {$ifSequence=TRUE;} else {$ifSequence=FALSE;}

return $ifSequence;
}

//*********************************************************************
// retourne la clé primaire d'une table
function getTablePrimaryKey ($connection,$tableName) {
// $connection : la connexion à la base postgres
// $table : le nom de la table
$sql='SELECT DISTINCT tc.constraint_name, tc.constraint_type
    FROM information_schema.table_constraints tc
   	WHERE tc.table_name = \''.$tableName.'\' AND tc.constraint_type =\'PRIMARY KEY\' 
	ORDER BY tc.constraint_name';

$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$constraints=pg_fetch_all($result);
pg_free_result($result);


$primaryKeyDetails=getConstraintDetails($connection,$tableName,$constraints[0]["constraint_name"]);

$primaryKey=array("table"=>$primaryKeyDetails["table_name"],"column"=>$primaryKeyDetails["column_name"]);

return $primaryKey;

}

//*********************************************************************
// retourne la liste des références à une clé primaire
function getPrimaryKeyReferences($connection,$tableName,$primaryKey) {
// $connection : la connexion à la base
// $tableName : le nom de la table dont fait partie la clé primaire
// $primaryKey : le nom de la colonne de la clé primaire
// $references : un tableau qui liste les tables qui utilisent la clé primaire comme clé étrangère

if (my_empty($primaryKey)) {$primaryKeyArray=getTablePrimaryKey ($connection,$tableName); $primaryKey=$primaryKeyArray["column"];} 

$sql='	SELECT DISTINCT tc.constraint_name, tc.constraint_type, tc.table_name, kcu.column_name, ccu.table_name AS references_table, ccu.column_name AS references_field
		FROM information_schema.table_constraints tc 
			LEFT JOIN information_schema.check_constraints cc ON tc.constraint_catalog=cc.constraint_catalog AND tc.constraint_schema=cc.constraint_schema AND tc.constraint_name=cc.constraint_name
			LEFT JOIN information_schema.key_column_usage kcu ON tc.constraint_catalog = kcu.constraint_catalog AND tc.constraint_schema = kcu.constraint_schema AND tc.constraint_name = kcu.constraint_name 
			LEFT JOIN information_schema.referential_constraints rc ON tc.constraint_catalog = rc.constraint_catalog AND tc.constraint_schema = rc.constraint_schema AND tc.constraint_name = rc.constraint_name 
			LEFT JOIN information_schema.constraint_column_usage ccu ON rc.unique_constraint_catalog = ccu.constraint_catalog AND rc.unique_constraint_schema = ccu.constraint_schema AND rc.unique_constraint_name = ccu.constraint_name  
		WHERE ccu.table_name = \''.$tableName.'\' AND ccu.column_name = \''.$primaryKey.'\''; 

$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$references=pg_fetch_all($result);
pg_free_result($result);

return $references;

}

//*********************************************************************
// compte le nombre d'enregistrements dans la base qui font référence à la valeur de la clé primaire spécifiée
function countPrimaryKeyReferencedRows($connection, $tableName, $primaryKey, $primaryKeyRecord) {
// $connection : la connexion à la base
// $tableName : le nom de la table dont fait partie la clé primaire
// $primaryKey : le nom de la colonne de la clé primaire
// $primaryKeyRecord : la valeur de la clé primaire (i.e. l'identifiant de l'enregistrement dans la base)
// impacted : un tableau qui indique, pour chaque table qui utilise $primaryKey comme clé étrangère, le nombre d'enregistrements
// qui font référence à la valeur $primaryRecord de $primaryKey
// (utilisé pour compter le nombre d'enregistrement impactés en cascade lors de la suppression d'un enregistrement servant de clé étrangère)

if (my_empty($primaryKey)) {$primaryKeyArray=getTablePrimaryKey ($connection,$tableName); $primaryKey=$primaryKeyArray["column"];} 


$references=getPrimaryKeyReferences($connection,$tableName,$primaryKey);

if (!my_empty($references)) {
foreach ($references as $reference) {

$localPrimary=getTablePrimaryKey ($connection,$reference["table_name"]);
if (my_empty($localPrimary["column"])) {$localPrimary["column"]="*";}

$sql='SELECT '.$localPrimary["column"].'
		FROM '.$reference["table_name"].'
		WHERE '.$reference["column_name"].'=\''.$primaryKeyRecord.'\'';

$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$temp=pg_fetch_all($result);
pg_free_result($result);

if (!my_empty($temp)) {
$impacted[$reference["table_name"]]=$temp;
}
}
} // end if !my_empty($references)
else {$impacted=array();}

return $impacted;
}
//*********************************************************************
// retourne la valeur d'une colonne pour une table données dans la table admin_dictionary_tables
function getDictionaryTableEntry($connection,$column,$table) {
// $connection : la connexion à la base
//$column : la colonne dont on veut la valeur
// $table : la table dont on veut la valeur (nom de la table dans la base)

$sql='SELECT '.$column.' FROM admin_dictionary_tables WHERE table_db=\''.$table.'\'';
$result=pg_query($connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$temp=pg_fetch_all($result);
pg_free_result($result);

$return=$temp[0][$column];

return $return;


}
?>
