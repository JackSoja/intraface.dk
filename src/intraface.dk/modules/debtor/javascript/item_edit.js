/*

Denne virker desv�rre ikke. Kan ikke submitte.
M�ske er den bedste ide i virkeligheden ogs�, at
det gemmes i cookies, s� man har mulighed for at fortryde
igen?

Noget helt andet er, at s�dan nogle skifter b�r kunne
fungere ogs� for dem, der ikke har sl�et javascript til,
s� det er et sp�rgsm�l, om skifterne i stedet skal laves
med nogle selectbokse?

*/

var compatible = document.getElementById;

function prepareLinks() {
	if (!compatible) return;
	/*
	var oForm = document.getElementById('items');
	if (!oForm) {
		return;
	}
	*/
	var oNav = getElementsByClass('characterNav');
	if (!oNav) {
		return;
	}

	var count = oNav.length;
	
	for (var i = 0; i < count; i++) {
		oLinks = oNav[i].getElementsByTagName("A");
	}
	
	count = oLinks.length;
	
	for (var i = 0; i < count; i++) {
		oLinks[i].onclick = function () {
			document.getElementById('form_items').submit();
		}
	}
	
}

YAHOO.util.Event.addListener(window, "load", prepareLinks);