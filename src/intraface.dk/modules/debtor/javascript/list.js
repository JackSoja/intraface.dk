var list = {
	init: function() {
		var oInput = YAHOO.util.Dom.get('date-from');
		if (oInput) {
			YAHOO.util.Event.addListener(oInput, "click", list.showCalender);
		}
		
	},
	
	showCalender: function() {
		var calender = new YAHOO.widget.Calendar("calender", "calender");
		calender.render();
	}

}

/*
	LIGE NU KOPIERER KALENDEREN SIG SELV, N�R MAN KLIKKER P� DEN. - og det er ikke lavet s� den smider datoerne ind i datofelterne.
*/

//YAHOO.util.Event.addListener(window, "load", list.init);