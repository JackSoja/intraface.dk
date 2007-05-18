<?php
function isAjax() {
    if (!empty($_REQUEST['ajax']) AND $_REQUEST['ajax'] == true) {
        return 1;
    }

    if (!empty($_SERVER['HTTP_ACCEPT']) AND $_SERVER['HTTP_ACCEPT'] == 'message/x-jl-formresult') {
        return 1;
    }

    if (!empty($_SERVER['X-Requested-With']) AND $_SERVER['X-Requested-With'] == 'XMLHttpRequest') {
        return 1;
    }

    return 0;
}



/**
 * Funktion til at outputte et bel�b landespecifik notation
 * Det kunne jo v�re gavnligt om metoden ogs� indeholdte noget om,
 * hvilket land der er tale om.
 */

function amountToOutput($amount) {
    return number_format($amount, 2, ',', '.');
}

/**
 * Funktion til at outputte et bel�b landespecifik notation i en formular
 */

function amountToForm($amount) {
    return number_format($amount, 2, ',', '');
}

/**
 * Funktion til at konvertere bel�b s� de kan gemmes i databasen
 *
 * Funktionen skal konvertere til den mindste enhed af bel�bet
 * i vores tilf�lde ofte �rer
 */
function amountToDb($amount) {
    ## dette konverterer fra dansk til engelsk format - men s� b�r den ogs� v�re landespecifik
    ## sp�rgsm�let er hvordan vi g�r dem landespecifikke p� en smart m�de?
    $amount = str_replace(".", "", $amount);
    $amount = str_replace(",", ".", $amount);

    return $amount;

}

function autoop($text) {
    require_once 'markdown.php';
    require_once 'smartypants.php';

    $text = MarkDown($text);
    $text = SmartyPants($text);
    return $text;
}


if(!function_exists('mime_content_type')) {
    // mime_content_type f�rst fra PHP 4.3
    // Taget fra http://dk.php.net/manual/en/function.mime-content-type.php
    function mime_content_type($f) {
        return exec(trim('file -bi '.escapeshellarg($f)));
    }
}

/**
 * Function to be called before putting data in the database
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safeToDb($data) {
    if(is_array($data)){
        return array_map('safeToDb',$data);
    }

    if (get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }

    return mysql_escape_string(trim($data));
}

/**
 * Function to be called before outputting data to a form
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safeToForm($data) {

    // return 'safeToForm'; // for debugging of use of safeToForm

    return safeToHtml($data);


}

/**
 * Function to be called before putting data into a form
 *
 * Metoden skal i �vrigt skrives om hvis den skal fungere s�dan her til den
 * der findes i vores subversion.
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safeToHtml($data) {
    // denne bruges i forbindelse med translation - kan sikkert fjernes n�r alt er implementeret
    if (is_object($data)) return $data->getMessage();

    // egentlig b�r den her vel ikke v�re rekursiv. Man skal kun bruge den n�r man skriver direkte ud.
    if(is_array($data)){
        return array_map('safeToHtml',$data);
    }

    if (get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }

    // return 'safeToHtml'; // For debugging of use of safeToHtml
    return htmlspecialchars($data);
}

?>