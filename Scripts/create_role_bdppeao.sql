/*	Database PPEAO role declaration
 *	Date : 25/07/2024
 *	Author : Laurent FLOC'H
 *	Institut de Recherche pour le Developpement,
 *  Sete, France
 */

DROP ROLE IF EXISTS devppeao;
CREATE ROLE devppeao LOGIN ENCRYPTED PASSWORD '2devppe!!' SUPERUSER INHERIT CREATEDB CREATEROLE;
ALTER ROLE devppeao WITH ENCRYPTED PASSWORD '2devppe!!';

