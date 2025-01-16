
// fonctions javascripts de base, utilisées dans de nombreux scripts

/*
* preloading of images (used for preloading the "loading..." animated GIF displayed while AJAX is doing its thing)
*/
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}


/*
* function used to create an AJAX object
*/
function getXhr(){
                    var xhr = null; 
	if(window.XMLHttpRequest) // Firefox et autres
	   xhr = new XMLHttpRequest(); 
	else if(window.ActiveXObject){ // Internet Explorer 
	   try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest not supported by browser 
	   xhr = false; 
	} 
                    return xhr;
}

/* 
* cette fonction retourne le contenu d'un élément CDATA dans un document XML
*/

function getCDATA(element){

var ie = (typeof window.ActiveXObject != 'undefined');
var returnText;

if(ie){

if(element.hasChildNodes){
returnText = element.childNodes[0].nodeValue;
}
}
else{

if(element.hasChildNodes){
returnText = element.childNodes[1].nodeValue;
}


}

return returnText;

}


/* 
* function that allows to create named elements in Internet Explorer
* IE doesn't allow to name a created element using the standard theElement.setAttribute("name","theElementName");
* so you need to try it first the IE way using the IE-only createElement('<input name="">...')
*/
function createNamedElement(type, name) {
   var element = null;
   // Try the IE way; this fails on standards-compliant browsers
   try {
      element = document.createElement('<'+type+' name="'+name+'">');
   } catch (e) {
   }
   if (!element || element.nodeName != type.toUpperCase()) {
      // Non-IE browser; use canonical method to create named element
      element = document.createElement(type);
      element.name = name;
   }
   return element;
}


/**
* fonction appelée lorsque l'utilisateur se connecte via le formulaire de login
*/
function ajaxLogin(){

	var xhr = getXhr();


	// what to do when the response is received
	xhr.onreadystatechange = function(){
		// while waiting for the response, display the loading animation
		var theLoader='<div align="center">connexion...<img src="/assets/ajax-loader.gif" alt="connexion..." title="connexion..." valign="center"/></div>';
		
		var theLoginForm=document.getElementById("smalloginform");
		
		if(xhr.readyState < 4) { theLoginForm.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			theMessage = xhr.responseText;
			// si le login est réussi, on rafraichit la page
			if(theMessage == 'success') {document.location.reload(true);}
			// sinon on affiche le formulaire de login avec un message d'erreur
			else {theLoginForm.innerHTML = theMessage;}

		}// end function()
	} // end ajaxLogin


	// using GET to send the request
					xhr.open("GET","/session/login.php?login="+document.getElementById("slogin").value+"&pass="+document.getElementById("spass").value,true);
	xhr.send(null);
}


/**
* fonction appelée lorsque'l'utilisateur se déconnecte via le formulaire de login
*/
function ajaxLogout(){

	var xhr2 = getXhr();


	// what to do when the response is received
	xhr2.onreadystatechange = function(){
		// while waiting for the response, display the loading animation
		var theLoader=' <div align="center">d&eacute;connexion...<img src="/assets/ajax-loader.gif" alt="d&eacute;connexion..." title="d&eacute;connexion..." valign="center"/></div>';

		var theLoginForm=document.getElementById("smalloginform");

		if(xhr2.readyState < 4) { theLoginForm.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr2.readyState == 4 && xhr2.status == 200){
			theMessage = xhr2.responseText;
			// une fois la déconnexion faite, on rafraichit la page
			if(theMessage == 'success') {document.location="/";}
		}// end function()
	} // end ajaxLogout


	// using GET to send the request
	xhr2.open("GET","/session/logout.php",true);
	xhr2.send(null);
}



/**
* Fonction utilisée pour récupérer la valeur d'un paramètre dans une URL
*/

function gup( urlParam ) {
// urlParam : le nom du paramètre d'URL dont on veut récupérer la valeur
  var regexS = "[\\?&]"+urlParam+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var tmpURL = window.location.href;
  var results = regex.exec( tmpURL );
  if( results == null )
    return "";
  else
    return results[1];
}

/**
* Fonction utilisée pour récupérer les valeurs des OPTIONS sélectionnées dans un SELECT
*/

function getMultipleSelect(theSelect) {
// theSelect : objet DOM de type SELECT dont on veut récupérer les valeurs sélectionnées
var selected = new Array(); 
for (var i = 0; i < theSelect.options.length; i++) if (theSelect.options[i].selected) selected.push(theSelect.options[i].value);
return selected;
}



/**
* Fonction générique qui permet de constuire une chaine de type url à partir d'un formulaire
* récupère l'ensemble des champs d'un formulaire et construit une chaine du type &param1=value1&param2=value2
*/

function formToUrl(theFormId,theClass) {
// theFormId : l'id du formulaire
// theClass : la classe des éléments du formulaire dont on veut récupérer les valeurs
	// on sélectionne tous les champs du filtre 
	var daClass;
	if (theClass=='') {daClass='';} else {daClass='.'+theClass}
	var theParams=$(theFormId).getElements(daClass);

	var ln=theParams.length;
	var theUrl='';
	// pour chaque champ, si une valeur non nulle est sélectionnée, on l'ajoute à l'url theUrl
	for (var i=0; i<ln; i++) {
	
		theElement=theParams[i];
		// si on a affaire à un input ou une textarea
		if (theParams[i].nodeName=='INPUT' || theParams[i].nodeName=='TEXTAREA') {
			if (theParams[i].value!='') {theUrl+='&'+theParams[i].name+'='+theParams[i].value;}
		}
		
		//si on a affaire à un select
		if (theParams[i].nodeName=='SELECT') {
			if (theParams[i].selectedIndex!=-1) {theUrl+='&'+theParams[i].name+'='+theParams[i].value;}
		}
		}
		return theUrl;			
}

/**
* Fonction qui permet de remplacer la valeur d'un parametre dans une url
* ex. : &param=valeur1 -> &param=valeur2
*/
function replaceQueryString(url,param,value) {
	var re = new RegExp("([?|&])" + param + "=.*?(&|$)","i");
    if (url.match(re))
        return url.replace(re,'$1' + param + "=" + value + '$2');
    else
        return url + '&' + param + "=" + value;
}