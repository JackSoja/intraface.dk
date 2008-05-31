<?php
require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');

$kernel->module('accounting');

$page = new Intraface_Page($kernel);
$page->start('Hj�lp til bogf�ring');
?>
<h1>Om at lave regnskab</h1>

<h2>Dobbelt bogholderi</h2>

<p>Dette regnskab er et <em>dobbelt bogholderi</em>. Det betyder, at du bogf�rer alle bel�b flere gange. I praksis posterer du som regel f�rst p� en resultat-konto og derefter p� en status- og moms-konto.</p>

<h2>Kontoplan</h2>

<p>For at bogf�re skal du have en kontoplan. Kontoplanen er delt i to dele:</p>

<dl>
	<dt>Resultat</dt>
	<dd>Her beregnes den skattepligtige indkomst. Her skal alle skattepligtige indt�gter som fx salgsfakturaer, kontantsalg, renteindt�gter, modtagne rabatter bogf�res. Samtidig skal alle fradaragsberettigede udgifter som varek�b, l�nninger, kontorudgifter ogs� bogf�res. Forskellen mellem indt�gter og udgifter er periodens resultat.</dd>
	<dt>Status</dt>
	<dd>Her findes al v�rdi og g�ld i firmaet.</dd>
</dl>

<p>Status er igen delt op i to dele:</p>

<dl>
	<dt>Aktiver</dt>
	<dd>Her findes alle v�rdier i firmaet. Fx debitorernes udest�ende, kassens indhold, indest�ende i banken, varelageret og driftsmidler (fx biler, v�rdipapirer og ejendomme).</dd>
	<dt>Passiver</dt>
	<dd>Her findes g�lden og egenkapitalen. G�ld kan fx v�re kreditorer, skyldig skat og moms, g�ld i ejendomme.</dd>
</dl>

<h2>Egenkapital</h2>

<p>Egenkapitalen er forskellen mellem aktiver og passiver, og egenkapitalen st�r som et negativt tal i regnskabet. Egenkapitalen kan forklares som virksomhedens g�ld til indehaveren.</p>

<!--
DEBET/KREDIT kan virke lidt sv�rt i begyndelsen, men her er nogle enkle huskeregler: - - DEBET=Plus/Positiv og KREDIT=Minus/Negativ. I "RESULTAT" er Indt�gter=Kredit og Udgifter=Debet, og i "STATUS" er "AKTIVER"=Debet og "PASSIVER"=Kredit. Hvis du er i tvivl n�r du posterer i "RESULTAT" , s� t�nk p� hvor der skal modposteres i "STATUS", her er du ikke i tvivl om, at indg�et bel�b skal Debiteres/Plus i kassen, og at udg�et bel�b skal Krediteres/Minus i kassen, - -ja s� skal det naturligvis posteres modsat i "RESULTAT", her er et par eksempler

Finans Konteringer



Bem�rk!  - - - For at f� penge i kassen skal du s�lge noget, her har vi solgt for( Omsat for) kr. 100,00 i april m�ned,  - - bel�bet er "KREDITERET" konto nr. 1060, som er en salgskonto/RESULTATkonto,  bel�bet modkonteres p� konto 6810 som er Kassen/STATUS aktiver. Saldo p� konto 6810/kassen udviser nu et positivt bel�b kr. 100,00. I bogf�ringen er dette bel�b posteret i "DEBET" siden. - - - - Salget er posteret i "KREDIT" siden og salgskonto 1060 udviser nu en saldo Kr. 80,00, idet der er beregnet moms Kr. 20,00 af salgsbel�bet. Den post du bogf�rer bliver s�ledes posteret 3 gange. Du har ved oprettelse af kontoplaner bestemt hvilke konto momsen skal afl�ftes p� (her 8720). n�r du taster "KONTER", bliver konto 1060 krediteret Kr. 80,00 - - konto 6810 debeteres med kr. 100,00 og momskontoen som er en STATUS/PASSIVER konto krediteres med momsen kr. 20,00-----Nu er der Debetereet i alt kr. 100,00 og krediteret i alt kr. 100,00.

Den n�ste post hvor der er k�bt en fl. Whisky, posteres p� n�jagtig samme m�de, bare modsat idet et varek�b skal "DEBETERES".  du vil ogs� kunne se at Kassebeholdningen/Saldo p� konto 6810 nu igen g�r i 0 idet der er k�bt for det samme som du har solgt og du har s�ledes ikke tjent nogen penge til kassen, SE KONTOPLAN
-->

<h2>Kontoplan</h2>

<p>Inden du bogf�rer skal du bruge en kontoplan. Hvis du ikke har erfaring med kontoplaner, b�r du r�df�re dig med en autoriseret revisor. I hvert fald skal du t�nke dig rigtig grundigt om, inden du starter med at bogf�re.</p>

<h3>Momskonti</h2>

<p>Hvis du er momsregistreret, skal der v�re momskonti i dit regnskab. Som minium b�r du have en konto til indg�ende moms (k�bsmoms) og udg�ende moms (salgsmoms) og en konto til momsbetalinger (de indbetalinger der er foretaget til Skat). Disse konti b�r v�re grupperet sammen i regnskabet.</p>

<h2>Regler</h2>

<dl>
	<dt>Debet og kredit</dt>
		<dd>Bel�b indtastes med moms. Debet er altid venstre side, og kredit h�jre side. Debet er altid modtagersiden, og kredit er altid afgiversiden.</dd>
	<dt>Bogf�ringsfrekvens</dt>
		<dd>Som udgangspunkt har du pligt til at holde din bogf�ring ajour hver dag.</dd>
	<dt>Nummerering af bilag og fakturaer</dt>
		<dd>Bilag og fakturaer skal nummereres fortl�bende. De m� gerne have hver deres nummerserie. Nummereringen m� gerne starte forfra, n�r du starter et nyt �r.</dd>
	<dt>Fakturaer</dt>
		<dd>Fakturaer skal bogf�res p� fakturadatoen. Hvis kunden ikke betaler med det samme skal de modposteres p� en debitorkonto.</dd>
</dl>

<h2>Bogf�ring</h2>
<dl>
	<dt>Indt�gter</dt>
		<dd>Du skal lave en faktura p� alt salg - med mindre du har et kasseapparat. Hvis bel�bet er over 750 kroner, s� skal fakturaen indeholde modtagerens navn og adresse.</dd>
	<dt>Bogf�r indt�gter</dt>
		<dd>En indt�gt krediteres p� indt�gtskontoen, og debiteres p� den konto, hvor pengene modtages. Det kan fx v�re kassen, banken eller debitor (som er reserveret til folk der ikke betaler med det samme).</dd>
	<dt>Bogf�r udgifter</dt>
		<dd>Hvis du har en udgift, skal den debiteres p� den konto, som bedst beskriver, hvad du har k�bt, og den skal krediteres i fx banken eller kassen.</dd>
	<dt>Betalende debitorer</dt>
		<dd>Hvis du har en debitor, der betaler, skal pengene debiteres i kassen eller banken og krediteres p� debitorkontoen.</dd>
</dl>

<?php
$page->end();
?>