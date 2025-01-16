
// scripts utilisés par le module d'édition des tables

/*
* Fonction qui affiche un SELECT listant les tables de parametrage suite au choix d'un domaine (pêche, biologie...)
*/

function showCodageTablesSelect(typePeche){
	// typePeche : le type de pêche concerné : 'artisanale' ou 'scientifique'
	//alert(typePeche);
	var theDomainSelect=$("codage_"+typePeche+"_select");
	// si une valeur autre que "-choisissez un domaine-" est sélectionnée
	// on insère un SELECT avec la liste des tables correspondantes
	if ((theDomainSelect.selectedIndex!=0)) {
	
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			theCodageTablesSelect = xhr.responseText;
			// checks whether the newlevel div already exists or not
			if (document.getElementById("codage_"+typePeche+"_tables_select")) {
				// if so, replaces its content
				document.getElementById("codage_"+typePeche+"_tables_select").innerHTML=theCodageTablesSelect;
				}
			else {
			// if not, creates a new div
			
			// we need to use the *&*$# createNamedElement custom function to create a named element in $#@*&* Internet Explorer
			var newDiv=createNamedElement("div", "codage_"+typePeche+"_tables_select")
			newDiv.id="codage_"+typePeche+"_tables_select";
			//this is to fix IE problem with newDiv.setAttribute("class","level");
			newDiv.className = "table_select";
			// using innerHTML to fill the SELECT
			newDiv.innerHTML = theCodageTablesSelect;
			// for juxtaposed divs
			// on ajoute le nouveau DIV à la fin du DIV "peche_"
			document.getElementById("peche_"+typePeche).appendChild(newDiv);
			}			
			
		}  
	}

	// using GET to send the request
	xhr.open("GET","/edition/edition_codage_ajax.php?domaine="+theDomainSelect.value,true);
	xhr.send(null);}
	
	// "else" si la valeur  "-choisissez un domaine-" est sélectionnée, on efface le SELECT des tables
	else {$("codage_"+typePeche+"_tables_select").remove();}
	
}


/**
* Fonction utilisée pour supprimer les DIVs suivants quand on change les valeurs d'un DIV parent
*/
function removedependentSelects(level) {
	
	// uses the "getElements" method from mootools.js
	var theSelects = $('selector_content').getElements('div[id^=select_]');
	var ln=theSelects.length;
	for (var i=0; i<ln; i++) {
		//cuts the id value after "level_" to get the level number
		theLevel=theSelects[i].id.substring(7);
		// removes the div from the dom if it level is higher than the level of the div that was onchanged
		// uses the "remove" method from mootools.js
		if (theLevel>level) {$(theSelects[i].innerHTML='');
		;}
	}
	
}


/*
* Fonction utilisée pour insérer un nouveau <div><select> correspondant à une nouvelle table
*/

function showNewLevel(newLevel,theParentTable) {
	// newLevel: the level of the new div to create (used in <div id="level_n">)
	// theParentTable: la table à partir de laquelle on crée le nouveau select
	level=parseInt(newLevel)-1; // les niveaux sont 1,2,3 etc alors que les tableaux sont indexés à partir de 0
	var theLevel='level_'+level;
	var theValues=$(theLevel);
	var select=$(theParentTable);

	
	// on teste d'abord si on n'est pas arrivé à la fin du sélecteur (nouvelle table à ajouter)
	var selecteurLength=$('selector_content').getElements('div[id^=level_]').length;
	// debug	alert('longueur='+selecteurLength+'next level='+newLevel);
	if (newLevel<=selecteurLength) {
	// si une valeur est sélectionnée
	if ((select.selectedIndex!=-1)) {
		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function(){
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (le SELECT)
				theNewDivCode = xhr.responseText;
				// debug				alert(theNewDivCode);
				// on sélectionne le DIV  :
				var theDiv=$('level_'+newLevel);
				theDiv.innerHTML = '';
				theDiv.innerHTML=theNewDivCode;
			;} // end ifxhr.readyState == 4
		} // end xhr.onreadystatechange
	
	// on met à jour le lien "edit_link"
	updateEditLink(level);
	// on appelle cette fonction pour supprimer d'éventuels SELECTs suivants	
	removedependentSelects(parseInt(newLevel)-1);
	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('selector_form').toQueryString();
		
	// using GET to send the request
	// on récupère les valeurs des paramètres de l'URL (fonction gup() définie dans basic.js)
	var targetTable=gup('targetTable');
	var editTable=gup('editTable');
	xhr.open("GET","addTableSelect_ajax.php?&parentTable="+theParentTable+"&editTable="+editTable+"&targetTable="+targetTable+"&level="+newLevel+theString,true);
	xhr.send(null);

	}// end xhr.onreadystatechange
	
	// else if no value is selected, we remove the next criteria select and update the edit link
	else {
		removedependentSelects(parseInt(newLevel)-1);
		updateEditLink(level)
		;}
	} // end if 
	else {
	// si on est à la fin du sélecteur, on se contente de mettre à jour le lien edit_link
	updateEditLink(level);
	}
}

/**
* Fonction qui met à jour le lien permettant d'éditer une table ou ses valeurs sous chaque sélecteur
*/
function updateEditLink(level) {
	// level : le niveau du sélecteur pour lequel mettre le lien à jour
	
	// on sélectionne le lien à mettre à jour
	var theLink=$('editlink_'+level);
	var theLevel=$('select_'+level);
	var theSelect=theLevel.firstChild;
	var targetTable=gup('targetTable');
	// on récupère les valeurs des tables déjà sélectionnées
	var theSelection='';
	for (var i = 1; i <= level; i++) {theSelection+='&'+$('select_'+i).toQueryString();}
	// on récupère le nom de la table
	var editTable=theSelect.name.replace("[]","");
	// on récupère les valeurs sélectionnées dans theSelect et on en faire une chaine pour URL
	var selectedValues=getMultipleSelect(theSelect);	
	// on met à jour le lien
	// si aucune valeur n'est sélectionnée on insère un lien "éditer la table"
	if (selectedValues=='') {
		var theUrl= "/edition/edition_table.php?targetTable="+gup('targetTable')+"&editTable="+editTable+theSelection;
		var theLinkText="&eacute;diter la table";
		}
	// si au moins une valeur est sélectionnée on insère un lien "éditer la sélection"
	else {
		var theUrl= "/edition/edition_table.php?targetTable="+gup('targetTable')+"&editTable="+editTable+theSelection;
		var theLinkText="&eacute;diter la s&eacute;lection";
		;}
		
	// on change le contenu de la balise <p id="editlink_n">
	theLink.innerHTML='<a id="edita_'+level+'" class="link_button" href="'+theUrl+'">'+theLinkText+'</a>';
	
	// on attire l'attention de l'utilisateur sur le fait que le lien a changé
	theEditA=$("edita_"+level);
	var backgroundChange = new Fx.Style(theEditA, 'background-color', {duration:1500, transition: Fx.Transitions.linear,onComplete:function() {theEditA.setStyle('background-color','');}});
	
	backgroundChange.start('#FF8000','#FFF');
	

}


/**
* Fonction qui permet de sélectionner ou désélectionner toutes les valeurs d'un SELECT dans le sélecteur de tables PPEAO
*/
function toggleSelect(level,select,what) {
	// level : le niveau du SELECT dans le sélecteur de tables PPEAO
	// select : l'id du SELECT à utiliser
	// what : que sélectionner ('all' ou 'none')
	
	// on sélectionne ou désélectionne les OPTIONS du SELECT
	toggleSelectSelection(select,what);
	
	
	// on provoque le rafraichissement des éventuels SELECT suivants dans le sélecteur de tables
	var theLevel=parseInt(level)+1;
	showNewLevel(theLevel,select);
}

/**
* Fonction qui permet de filtrer la table à éditer
*/
function filterTable(theUrl) {
	// the Url : l'URL de la page courante (sans les paramètres de pagination ou de filtre)
	// cette fonction retourne une Url composée de theUrl et des paramètres de filtre, et redirige la page courante
	// vers cette Url
	
	// l'URL de redirection
	var newUrl=theUrl;
	
	// on sélectionne tous les champs du filtre 
	var theParams=$('la_table').getElements('.filter_field');
		
	// pour chaque champ, si une valeur non nulle est sélectionnée, on l'ajoute à l'url newUrl
	
	var ln=theParams.length;
	for (var i=0; i<ln; i++) {
		// si on a affaire à un input
		theElement=theParams[i];
		if (theParams[i].nodeName=='INPUT') {
			if (theParams[i].value!='') {newUrl+='&'+theParams[i].name+'='+theParams[i].value;}
		}
		//si on a affaire à un select
		if (theParams[i].nodeName=='SELECT') {
			if (theParams[i].selectedIndex!=0) {newUrl+='&'+theParams[i].name+'='+theParams[i].value;}
		}
	}
	
	document.location=newUrl;
	
}


/**
* Fonction permettant de limiter le nombre de caractères saisis dans un élément TEXTAREA
*/

function fieldTextLimiter(field,cntelement,maxlimit) {
// field : le champ de formulaire à controler
// cntspan : l'élément dans lequel on affiche le compteur de caractères restants
// maxlimit : le nombre maximum de caractères 
if (field.value.length > maxlimit) // si c'est trop long, on coupe!
field.value = field.value.substring(0, maxlimit);
// sinon on met à jour le compteur de caractères restants
else 
cntelement.innerHTML = (maxlimit - field.value.length)+' caract&egrave;re(s) restant(s)';

}

/**
* Fonction qui rend une valeur éditable dans le module d'édition des tables
*/
function makeEditable(table,column,record,action) {
// table : la table concernée (son pointeur dans le tableau $tablesDefinitions)
// column : la colonne concernée (son nom dans la base)
// record : l'identifiant unique de l'enregistrement concerné
// action : l'action à faire (edit/save/cancel)
	

// la cellule concernée
var theCell=$("edit_cell_"+column+"_"+record);
	
// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		// while waiting for the response, display the loading animation
	var theLoader=' <div align="center"><img src="/assets/ajax-loader.gif" alt="en cours..." title="en cours..." valign="center"/></div>';
	if(xhr.readyState < 4) { theCell.innerHTML = theLoader;}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		theCellContent = xhr.responseText;
		theCell.innerHTML=theCellContent;
		// si on passe en mode édition, on met toute la ligne <tr> en surbrillance
		var theRow=$("row_"+record); // la ligne
		if (action=='edit') {
			theRow.addClass("edit_field_row");
		}
		else {
			theRow.removeClass("edit_field_row");
		}
		
		
		// on evalue eventuellement les <script> retournes par le PHP
		var AllScripts=theCell.getElementsByTagName("script") 
		for (var i=0; i<AllScripts.length; i++) { 
			var s=AllScripts[i];  
				eval(s.innerHTML); 
		}

		
		
	}  
} // end xhr.onreadystatechange

// using GET to send the request
xhr.open("GET","/edition/edition_rendre_editable_ajax.php?editAction="+action+"&editTable="+table+"&editColumn="+column+"&editRecord="+record,true);
xhr.send(null);	
}


/**
* Fonction qui vérifie la valeur saisie et la stocke dans la base si elle est valide (dans le module d'édition des tables)
*/
function saveChange(table,column,record) {
// table : la table concernée (son pointeur dans le tableau $tablesDefinitions)
// column : la colonne concernée (son nom dans la base)

// record : l'identifiant unique dans la table de l'enregistrement concerné


// la cellule concernée
var theCell=$("edit_cell_"+column+"_"+record);
// le div contenant les boutons d'enregistrement/annulation
var theEditButtons=$("edit_buttons_"+column+"_"+record);
var theEditButtonsContent=theEditButtons.innerHTML;
// le champ concerné
var theField=$("e_"+column+"_"+record);
var newValue=theField.value;


// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
	var theLoader=' <div align="center"><img src="/assets/ajax-loader.gif" alt="validation en cours..." title="validation en cours..." valign="center"/></div>';
	if(xhr.readyState < 4) { theEditButtons.innerHTML = theLoader;}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		var theResponseNode = xhr.responseXML.documentElement;
		var isValid=theResponseNode.attributes.getNamedItem("valid").value;
		
		
		// si la valeur soumise est non valide, on affiche un message d'erreur
		if (isValid=='invalid') {
			// note : le firstChild est là car le contenu de l'élément <responseContent> est un TEXTNODE
			var theMessage=theResponseNode.getElementsByTagName('responseContent')[0].firstChild.nodeValue.replace(/^\[CDATA\[/,'')
			.replace(/\]\]$/,'');
			// si le message d'erreur existe déjà, on le remplace
			if ($('e_'+column+'_'+record+'_error')) {$('e_'+column+'_'+record+'_error').innerHTML=theMessage} else {
			// sinon crée l'élément pour afficher le message d'erreur
			var theMessageDiv= new Element('div', {'id':'e_'+column+'_'+record+'_error','class' : 'small error'});
			theMessageDiv.innerHTML=theMessage;
			// on injecte l'objet contenant le message
			theMessageDiv.injectInside(theCell);
			} // end if $('e_'+column+'_'+record+'_error')
			// on restaure les boutons enregistrer/annuler
			theEditButtons.innerHTML=theEditButtonsContent;
		}

		// sinon, on affiche le champ avec la nouvelle valeur
		else {
			var theNewCellContent=theResponseNode.getElementsByTagName('responseContent')[0].firstChild.nodeValue.replace(/^\[CDATA\[/,'')
			.replace(/\]\]$/,'');
			theCell.innerHTML=theNewCellContent;
			// on remet le style de la ligne éditée à sa valeur initiale
			var theRow=$("row_"+record); // la ligne
			theRow.removeClass("edit_field_row");
		}
		
	}  
} // end xhr.onreadystatechange


// using GET to send the request
xhr.open("GET","/edition/edition_enregistrer_modification_ajax.php?&editTable="+table+"&editColumn="+column+"&editRecord="+record+"&newValue="+encodeURIComponent(newValue),true);
xhr.send(null);	

}

/**
* Fonction qui bascule la visibilité d'un élement entre affiché et masqué
* theHideClass : le nom de la classe permettant de masquer l'elément
* theElementId : attribut id de l'élément DOM à afficher/masquer
* theLinkId : attribut id de l'élément DOM qui contient le lien permettant d'afficher/masquer
* theDisplayText : texte à afficher dans le lien quand l'élément est masqué
* theHideText : texte à afficher dans le lien quand l'élément est affiché

*/
function toggleElementVisibility(theHideClass,theElementId,theDisplayLinkId,theHideLinkId,theDisplayText,theHideText) {
	var theElement=$(theElementId);
	theElement.toggleClass(theHideClass);
	var theDisplayLink=$(theDisplayLinkId);
	var theHideLink=$(theHideLinkId);
	if (theElement.hasClass(theHideClass)) {
	theDisplayLink.innerHTML=theDisplayText;
	theHideLink.innerHTML='';}
	else {
	theHideLink.innerHTML=theHideText;
	theDisplayLink.innerHTML='';	
	}
}
	
	
/**
* Fonction qui affiche la fenetre de dialogue modale pour ajouter un nouvel enregistrement
* table : la table à laquelle on veut ajouter un enregistrement
*/
function modalDialogAddRecord(theLevel,theTable) {
		
	// on crée le nouvel élément
	
	var theOverlay=new Element ('div', {
		'id': "overlay_"+theLevel,
		'class': "overlay",
		'style': "z-index:"+theLevel*1000
	}
	);
	
	var theOverlayWindow= new  Element ('div', {
		'id': "overlay_"+theLevel+"_window",
		'class': "overlay_window"
	}
	);
	
	var theOverlayContent= new  Element ('div', {
		'id': "overlay_"+theLevel+"_content",
		'class': "overlay_content"
	}
	);
		
	var theOverlayButtons= new  Element ('div', {
		'id': "overlay_"+theLevel+"_buttons",
		'class': "overlay_buttons"
	}
	);
	
	var theOverlayLoaderDiv =new  Element ('div', {
		'id': "overlay_"+theLevel+"_loader",
		'class': "overlay_loader"
	}
	); 
	
	theOverlayButtons.innerHTML='<a id="overlay_'+theLevel+'_close" href="#" onclick="javascript:modalDialogClose(\'overlay_'+theLevel+'\',\'\');return false;" class="small link_button">annuler</a>';
	
	
	theOverlay.injectInside($E('body'));
	theOverlayWindow.injectInside(theOverlay);
	theOverlayLoaderDiv.injectInside(theOverlayWindow);
	theOverlayContent.injectInside(theOverlayWindow);
	theOverlayButtons.injectInside(theOverlayWindow);
	
	
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
			// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
		var theLoader='<div align="center" id="the_loader_'+theLevel+'"><img src="/assets/ajax-loader.gif" alt="chargement en cours..." title="chargement en cours..." valign="center"/></div>';
		if(xhr.readyState < 4) { theOverlayContent.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			var theResponseText = xhr.responseText;
			
			// on affiche les champs de saisie pour le nouvel enregistrement
			
			theOverlayContent.innerHTML=theResponseText;
			
			// on affiche le bouton "enregistrer"			
			var theSaveButton=new Element('a', {
			    'class': 'small link_button',
			    'href': '#',
				'id': "overlay_"+theLevel+"_save",
				'onclick': 'sendRecordToSave(\'add_record_'+theLevel+'_form\',\'add_field\','+theLevel+',\''+theTable+'\')'
			});
			theSaveButton.innerHTML="enregistrer";
			theSaveButton.injectBefore("overlay_"+theLevel+"_close");
			
			// on met à jour la hauteur de l'overlay au cas où le dialogue soit plus haut que l'écran
			// si on ne fait pas ça, l'overlay ne couvre qu'une partie de la page
			window.addEvent('domready', function(){
			// il faudrait trouver comment declarer correctement la hauteur de l'overlay
			//	var theHeight=$E('body').height;
			//	alert(theHeight);
				$('overlay'+"_"+theLevel).setStyle('height','100%');
				});
			
			}  
	} // end xhr.onreadystatechange

	// using GET to send the request
	xhr.open("GET","/edition/edition_ajouter_enregistrement_ajax.php?&editTable="+theTable+"&level="+theLevel,true);
	xhr.send(null);
}


/**
* Fonction qui ferme la fenetre de dialogue modale 
*/
function modalDialogClose(theDialogOverlay,refresh) {
	//theDialogOverlay : id de l'élément "overlay"
	// refresh : si "refresh", on force le refresh de la page
	$(theDialogOverlay).remove();
	if (refresh=='refresh') {
		window.location.reload(true);
	}
}

/**
* Fonction pour enregistrer les valeurs lors de l'ajout d'un nouvel enregistrement
*/
function sendRecordToSave(theFormId,theFormFieldClass,theLevel,theTable) {
 var theUrl=formToUrl(theFormId,theFormFieldClass);


var theSaveButton=$('overlay_'+theLevel+'_save');
var theLoader=$("overlay_"+theLevel+"_loader");
// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		
	if(xhr.readyState < 4) {
		theSaveButton.setStyle("visibility","hidden");
		// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
		theLoader.innerHTML='<img src="/assets/ajax-loader.gif" alt="validation en cours..." title="validation en cours..." valign="center"/>';
	}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		theLoader.innerHTML='';
		var theResponseNode = xhr.responseXML.documentElement;
		var theNodes=theResponseNode.childNodes;
		// la validité des valeurs saisies
		var isValid=theResponseNode.attributes.getNamedItem("validity").value;
		// si la saisie n'est pas valide, on réactive le bouton enregistrer
		if (isValid=='invalid') {
			theSaveButton.setStyle("visibility","visible");
			// et on affiche les messages d'erreur sous chaque champ non valide
			for (var i=0; i<theNodes.length; i++) {
				var theNode=theNodes[i];
				var theKey=theNode.attributes.getNamedItem("key").value;
				var theValue=$('add_record_'+theLevel+'_'+theKey).value;
				var theValidity=theNode.attributes.getNamedItem("valid").value;
				if (theNode.firstChild) {
					var theMessage=theNode.firstChild.nodeValue.replace(/^\[CDATA\[/,'').replace(/\]\]$/,'');
					}
				
				// si la valeur n'est pas valide et que on a un message d'erreur
				if (theValidity==0 && theMessage!='') {
					if (theError==$('add_record_'+theLevel+'_'+theKey+'_error')) {
						theError.innerHTML=theMessage;
						} 
					else {
						var theError=new Element('div',{
						'id' : 'add_record_'+theLevel+'_'+theKey+'_error',
						'class' : 'small error'
						}
						);
						theError.innerHTML=theMessage;
						theError.injectAfter('add_record_'+theLevel+'_'+theKey);
					} // end else theError
				} // end if theValidity==0
				
				} // end for
				
				
			} // end if isValid=='invalid'
			else {
				// on change le texte du <h1>
				var theH1=$('overlay_'+theLevel).getElement('h1');
				theConfirmation='enregistrement ajouté dans la table "'+theResponseNode.attributes.getNamedItem("table").value+'"';
				theH1.innerHTML=theConfirmation;
				for (var i=0; i<theNodes.length; i++) {
					var theNode=theNodes[i];
					var theKey=theNode.attributes.getNamedItem("key").value;
					// on traite différemment les valeurs "normales" et les "sequences"
					if (theNode.attributes.getNamedItem("sequence").value=="sequence") {
						var theValue=$('add_record_'+theLevel+'_'+theKey).innerHTML;
					} else 
					{
						var theValue=$('add_record_'+theLevel+'_'+theKey).value;
					}
					var theValidity=theNode.attributes.getNamedItem("valid").value;
					// on enlève le message d'erreur si il existe
					if ($('add_record_'+theLevel+'_'+theKey+'_error')) {$('add_record_'+theLevel+'_'+theKey+'_error').remove();}
					// on crée un nouvel élément contenant seulement la valeur à afficher
					var theNewElement=new Element('div',{
						'class' : 'small',
						'id' : 'add_record_'+theLevel+'_'+theKey
					}
					);
					theNewElement.innerHTML=theValue;
					// on sélectionne l'élément de formulaire qu'il doit remplacer
					var theOldElement=$('add_record_'+theLevel+'_'+theKey);
					// on remplace l'ancien élément par le nouveau
					theOldElement.replaceWith(theNewElement);
					// on efface les éventuels éléments de message ou les selecteurs en cascade
					// message d'erreur de validation
					if ($('add_record_'+theLevel+'_'+theKey+'_error')) {$('add_record_'+theLevel+'_'+theKey+'_error').remove();};
					// compteur du nombre de caracteres restants
					if ($('add_record_'+theLevel+'_'+theKey+'_counter'))
					{$('add_record_'+theLevel+'_'+theKey+'_counter').remove();};
					// selecteur en cascade
					if ($('add_record_'+theLevel+'_'+theKey+'_foreign_key_cascade'))
					{$('add_record_'+theLevel+'_'+theKey+'_foreign_key_cascade').remove();};
				}// end for
				
			// maintenant, on change le comportement du bouton "fermer"
			var theCloseButton=$('overlay_'+theLevel+'_close');
			var over='overlay_'+theLevel;
			theCloseButton.setProperty("onclick","javascript:modalDialogClose(\'"+over+"\',\'refresh\')");
			theCloseButton.innerHTML='fermer';
			} // end else isValid
		} // end if xhr.readyState == 4 && xhr.status == 200
	
} // end xhr.onreadystatechange


// using GET to send the request
xhr.open("GET","/edition/edition_ajouter_enregistrement_validation_ajax.php?&table="+theTable+"&level="+theLevel+theUrl,true);
xhr.send(null);

}


/**
* Fonction qui affiche la fenetre de dialogue modale pour supprimer un enregistrement
* theTable : la table dont on veut supprimer un enregistrement
* theRecord : l'identifiant unique de l'enregistrement à supprimer
* theLevel  : le niveau de l'overlay (utilie si on veut afficher plusieurs overlays les uns devant les autres)
*/
function modalDialogDeleteRecord(theLevel,theTable,theRecord) {
		
	// on crée le nouvel élément
	
	var theOverlay=new Element ('div', {
		'id': "overlay_"+theLevel,
		'class': "overlay",
		'style': "z-index:"+theLevel*1000
	}
	);
	
	var theOverlayWindow= new  Element ('div', {
		'id': "overlay_"+theLevel+"_window",
		'class': "overlay_window"
	}
	);
	
	var theOverlayContent= new  Element ('div', {
		'id': "overlay_"+theLevel+"_content",
		'class': "overlay_content"
	}
	);
	theOverlayContent.innerHTML='<div align="center"><h2 id="delete_title">supprimer l&#x27;enregistrement &quot;'+theRecord+'&quot;</h2><h2>recherche des enregistrements utilisant l&#x27;enregistrement &agrave; supprimer comme cl&eacute; &eacute;trang&egrave;re</h2></div>';
		
	var theOverlayButtons= new  Element ('div', {
		'id': "overlay_"+theLevel+"_buttons",
		'class': "overlay_buttons"
	}
	);
	
	var theOverlayLoaderDiv =new  Element ('div', {
		'id': "overlay_"+theLevel+"_loader",
		'class': "overlay_loader"
	}
	); 
	
	theOverlayButtons.innerHTML='<a id="overlay_'+theLevel+'_close" href="#" onclick="javascript:modalDialogClose(\'overlay_'+theLevel+'\',\'\');return false;" class="small link_button">annuler</a>';
	
	
	theOverlay.injectInside($E('body'));
	theOverlayWindow.injectInside(theOverlay);
	theOverlayLoaderDiv.injectInside(theOverlayWindow);
	theOverlayContent.injectInside(theOverlayWindow);
	theOverlayButtons.injectInside(theOverlayWindow);
	
	
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
			// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
		var theLoader='<div align="center" id="the_loader_'+theLevel+'"><img src="/assets/ajax-loader.gif" alt="chargement en cours..." title="chargement en cours..." valign="center"/></div>';
		if(xhr.readyState < 4) { theOverlayLoaderDiv.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			var theResponseText = xhr.responseText;
			
			// on affiche le message de confirmation de la suppression de l' enregistrement
			theOverlayLoaderDiv.innerHTML='';
			theOverlayContent.innerHTML=theResponseText;
			
			// on affiche le bouton "supprimer"			
			var theDeleteButton=new Element('a', {
			    'class': 'small link_button',
			    'href': '#',
				'id': "overlay_"+theLevel+"_delete",
				'onclick': 'sendRecordToDelete('+theLevel+',\''+theTable+'\',\''+theRecord+'\')'
			});
			theDeleteButton.innerHTML="supprimer";
			theDeleteButton.injectBefore("overlay_"+theLevel+"_close");
			
			// on met à jour la hauteur de l'overlay au cas où le dialogue soit plus haut que l'écran
			// si on ne fait pas ça, l'overlay ne couvre qu'une partie de la page
			window.addEvent('domready', function(){
			// il faudrait trouver comment declarer correctement la hauteur de l'overlay
			//	var theHeight=$E('body').height;
			//	alert(theHeight);
				$('overlay'+"_"+theLevel).setStyle('height','100%');
				});
			
			}  
	} // end xhr.onreadystatechange

	// using GET to send the request	
	xhr.open("GET","/edition/edition_supprimer_enregistrement_ajax.php?&table="+theTable+"&level="+theLevel+"&record="+theRecord,true);
	xhr.send(null);
}


/**
* Fonction pour supprimer l'enregistrement choisi
*/
function sendRecordToDelete(theLevel,theTable,theRecord) {

var theDeleteButton=$('overlay_'+theLevel+'_delete');
var theLoader=$("overlay_"+theLevel+"_loader");
var theOverlayContent=$("overlay_"+theLevel+"_content");
var theCloseButton=$('overlay_'+theLevel+'_close');
var theTitle=$('delete_title').innerHTML;
// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		
	if(xhr.readyState < 4) {
		theDeleteButton.setStyle("visibility","hidden");
		theCloseButton.setStyle("visibility","hidden");
		// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
		theLoader.innerHTML='<h2>'+theTitle+'</h2><h2>suppression de l&#x27;enregistrement en cours</h2><br /><p>merci de patienter</p><br /><img src="/assets/ajax-loader.gif" alt="suppression en cours..." title="suppression en cours..." valign="center"/>';
		theOverlayContent.innerHTML='';
	}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		theLoader.innerHTML='';
		var theResponseText = xhr.responseText;
		// on affiche le résultat de la suppression
		theOverlayContent.innerHTML=theResponseText;
				
			// maintenant, on change le comportement du bouton "fermer" : on raffraichit l'affichage
			var over='overlay_'+theLevel;
			theCloseButton.setStyle("visibility","visible");
			theCloseButton.innerHTML='fermer';
			theCloseButton.setProperty("onclick","javascript:modalDialogClose(\'"+over+"\',\'refresh\')");
		} // end if xhr.readyState == 4 && xhr.status == 200
	
} // end xhr.onreadystatechange


// using GET to send the request
xhr.open("GET","/edition/edition_supprimer_enregistrement_validation_ajax.php?&table="+theTable+"&record="+theRecord,true);
xhr.send(null);

}




/**
* Fonction qui déclenche une opération de maintenance sur la base et en retourne le résultat
*/
function doMaintenance(action) {
// action : l'action à faire (sequences_ref_param, sequences_donnees, vacuum, reindex)

var outputDiv=$('maintenance_output');
// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		// while waiting for the response, display the loading animation
	var theLoader='<div align="left"><h2>maintenance de la base...<img src="/assets/ajax-loader.gif" alt="maintenance en cours..." title="maintenance en cours..." valign="center"/></h2></div>';
	if(xhr.readyState < 4) { outputDiv.innerHTML = theLoader;}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		maintenanceResult = xhr.responseText;
		outputDiv.innerHTML=maintenanceResult;
	}  
} // end xhr.onreadystatechange

// using GET to send the request
xhr.open("GET","/edition/edition_maintenance_ajax.php?action="+action,true);
xhr.send(null);	
}


/**
* Fonction pour rafraichir les SELECT dependants en mode edition d'une cle etrangere avec cascade
*/
function updateEditSelects(theId,theLevel,theTable,theKey,theCascade) {
	
// theId : l'id du SELECT dont on veut sauver la valeur
// theLevel : le niveau du SELECT declenchant le refresh dans la cascade (p.e. : 1 pour systeme dans pays,systeme,secteur)
// theTable : la table du SELECT declencheur
// theKey : la colonne du SELECT declencheur
// theCascade : la cascade de SELECT comme définie dans la table de dictionnaire 

var theSelects=$$('select[name^='+theId+']');
// la valeur selectionnee dans le SELECT declencheur
var theKeyValue=theSelects[theLevel].value;

var ln=theSelects.length;
//alert(ln);

// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		
	if(xhr.readyState < 4) {
		// en attendant la réponse, on "vide" les SELECTs suivants
		for (i=parseInt(theLevel)+1;i<ln;i++) {
		//alert(i);
		theSelects[i].innerHTML = '<option value="">&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;</option>';
		}
		
	}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		var theResponseText = xhr.responseText;
		theSelects[parseInt(theLevel)+1].innerHTML=theResponseText;

		} // end if xhr.readyState == 4 && xhr.status == 200
	
} // end xhr.onreadystatechange


// using GET to send the request
xhr.open("GET","/edition/edition_rafraichir_cascade_ajax.php?theId="+theId+"&theLevel="+theLevel+"&theTable="+theTable+"&theKey="+theKey+"&theKeyValue="+theKeyValue+"&theCascade="+theCascade,true);
xhr.send(null);

}


function replaceQueryString(url,param,value) {
    var re = new RegExp("([?|&])" + param + "=.*?(&|$)","i");
    if (url.match(re))
        return url.replace(re,'$1' + param + "=" + value + '$2');
    else
        return url + '&' + param + "=" + value;
}


// fonction qui effectue une requete AJAX pour mettre a jour le selecteur de systemes quand on change le pays selectionne
function updateSystemes() {
	var paysSelect=$("pays");
	var systemesSelect=$("systemes");
	// si une valeur est sélectionnée
	if ((paysSelect.selectedIndex!=-1)) {
		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function(){
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (les <options> du select)
				var theNewOptions = xhr.responseText;
				// debug 	alert(theNewOptions);
				// on remplace le contenu du <select id=systemes>  :
				systemesSelect.innerHTML=theNewOptions;
			;} // end if xhr.readyState == 4
		} // end xhr.onreadystatechange
	
	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('droits_acces').toQueryString();

	// using GET to send the request
	xhr.open("GET","/edition/edition_droits_acces_reload_systemes_ajax.php?"+theString,true);
	xhr.send(null);

	}// end if ((currentSelect.selectedIndex!=-1)
	
	// else if no value is selected, we remove the next criteria select and update the edit link
	else {
		systemesSelect.innerHTML='';
		
		;}
}

// fonction qui met a jour le lien d'ajout de droits sur des systemes quand on change les systemes selectionnes
function refreshAddSystemLink(type) {
	// type: u pour utilisateur ou g pour groupe
	if (type=='u') {var typeString='cet utilisateur';} else {var typeString='ce groupe';}
	var systemesSelect=$("systemes");
	var thePtag=$('add_systemes');
		if ((systemesSelect.selectedIndex!=-1)) {
		var theUrl='#';
	var theLink='<a href="'+theUrl+'" onclick="javascript:droits_acces.submit();return false;" class="next_step">autoriser '+typeString+' &agrave; consulter les donn&eacute;es de ce(s) syst&egrave;me(s) </a>';
	// on met a jour le lien
	thePtag.innerHTML=theLink;


	}// end if ((currentSelect.selectedIndex!=-1)
	
	// else if no value is selected, we remove the next criteria select and update the edit link
	else {
		thePtag.innerHTML='';
		
		;}
}


// fonction qui permet d'enregistrer les changements de droits d'acces d'un acteur
function enregistrerDroits() {
	$("enregistrer").setProperty('value','oui');
	$("droits_acces").submit();
	
}


				
function BrowseServer(inputId)
				{
					// You can use the "CKFinder" class to render CKFinder in a page:
					var finder = new CKFinder() ;
					finder.removePlugins = 'basket';
					finder.basePath = '/ckfinder/' ;
					finder.selectActionFunction = SetFileField ;
					finder.selectActionData = inputId
					finder.popup() ;
				}

function SetFileField( fileUrl,data )
{
	var filePath=fileUrl.replace('/work/documentation/metadata/','');
	
	document.getElementById(data["selectActionData"] ).value = filePath ;
}
