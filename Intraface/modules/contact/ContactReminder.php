<?php
/**
 * Create and maintain reminders for contacts.
 *
 * @package Contact
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 * @since 0.1.0
 * @version @package-version@
 */


/*

Princip:

Det skal best� af 2 dele:

1) Engangs reminder
2) Tilbagevendende reminder

Engangsreminder s�ttes med en dato (og klokkes�t) ud i fremtiden. Der kan v�re et emne og
en beskrivelse af handlingen. Reminderen skal vises p� forsiden i en tid (en m�ned m�ske)
i forvejen. P� dagen kan man bestemme at der bliver sendt en e-mail, og p� sigt en
daglig/ugelig/m�nedlig summary. En reminder kan enten markeres som Set, eller uds�ttes til
en ny dato (Datoen �ndres blot). Samt man kan klikke p� Opret ny, p� reminderen, n�r den er
set kan man let oprette en ny, fx et �r efter.

P� en reminder kan der s�ttes en faktura-/ordreskabelon (P� sigt) (Skal laves som en
del af debtor). Ved et enkelt klik bliver fakturaen oprettet/p� sigt automatisk oprettet
og sendt.

Tabelstruktur (udkast):
id
intranet_id
contact_id
[tilbagevendene]_reminder_id
debtor_template_id (kommende)
date
status (created, seen, cancelled)
date_created
date_seen
date_changed
date_cancelled
subject
description
active

Tilbagevendende reminder:
Jeg er lidt i tvivl om hvordan vi lettest laver et tilbagevendende reminder system, som
g�r det rimelig let at bestemme en periode, og som ikke kr�ver alt for meget databasearbejde.
En mulighed er m�ske at kigge lidt p� cron - den er meget fleksibel, men kr�ver m�ske lidt
for meget databasearbejde (Det skal gerne kunne lade sig g�re bare med et enkelt databasekald
at hente alle remindere inden for en tidsperiode). Tilbagevende reminder opretter
engangsreminder efterh�nden som de bliver efterpurgt (efterh�nden som man n�rmer sig tiden,
eller man eftersp�rger remindere ud i fremtiden), b�de ved natlig k�rsel, og ved konkrete
eftersp�rgsler (fx alle remindere hos en contact det n�ste �r). Engangsreminderne bliver
knyttet til den "tilbagevendende reminder" som har oprettet den. Derved kan engangsreminderne
blive �ndret, hvis "Tilbagevende reminder" �ndres.

Debtor template kan ogs� tilknyttes tilbagevendende reminder.

F�rst udkast til hvordan tilbagevende reminder gemmes:
Tabeludkast:
id
intranet_id
contact_id
debtor_template_id
date_created
date_changed
subject
description
active

(Udkast til hvordan gentagende reminder gemmes:)
reminder_day (0: hver dag, >0: dag i m�neden hvor reminder aktiveres)
reminder_month (0: hver m�ned, >0: m�ned hvor reminder aktiveres)
reminder_week (0: hver uge, >0: uge den skal aktiveres)
date_start
date_end

Denne m�de er rimelig let at finde fremtidige poster, men den er ikke s�rlig fleksibel. Det
kan fx ikke lade sig g�re at lave en reminder det k�rer b�de den 1. og 15 i en m�ned. Det skal
laves som 2 forskellige tilbagevendende remindere. M�ske cron metoden er hver at udforske.

Arbejdsgang:
Fordelen ved denne metode er at vi kan starte med at lave en rimelig simpel reminder, blot
med engangsreminder.

1) Engangsreminder med beskrivelse, og mulighed for at markere som set, og uds�ttelse af reminder.
2) E-mail notification
3) Gengtagende reminder
4) Fakturaskabelon
5) Automatisk udsendelse af faktura.

 */

class ContactReminder extends Standard {

	private $id;
	private $contact;
	public $value;

	/**
	 * @param 	object contact: Class contact
	 * @param 	int id: id of reminder.
	 */
	function __construct($contact, $id = 0) {
		$this->contact = $contact;
		$this->id = intval($id);
		$this->value['id'] = $this->id;
	}

	/**
	 * @return boolean true or false
	 */
	public function load() {

	}

	/**
	 * @param 	array $input: array of data to store/update
	 * @return	boolean true or false
	 */
	public function save($input) {

	}

}
?>