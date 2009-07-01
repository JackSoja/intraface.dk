<?php
/**
 * Bruges til at konvertere bel�b til og fra database
 * B�r den ikke kunne bruges de enkelte funktioner
 * direkte. Det vil i hvert fald g�re den mere anvendelig
 * i selve processen?
 * @author Sune
 * @version 001
 */
class Intraface_Amount
{
    private $amount;

    /**
     * indskriv det bel�b det drejer sig om
     * @param amount double bel�b
     */
    function __construct($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Public: Konvertere et dansk bel�b til engelsk
     * B�r der ikke v�re noget der validerer?
     * @return 1
     */
    function convert2db()
    {
        $this->amount = str_replace(".", "", $this->amount);
        $this->amount = str_replace(",", ".", $this->amount);
        settype($this->amount, "double");
        return true;
    }


    /**
     * Public: konvertere et engelsk bel�b til et dansk
     * B�r vist skrives om. Den returnerer jo 1 uanset?
     * @ return 1
     */
    function convert2dk()
    {
        //if (is_double($this->amount)) {
            $this->amount = number_format($this->amount, 2, ",", ".");
        //}
        return true;
    }

    /**
     * Public: henter bel�bet efter konvertering
     * @return bel�b
     */
    function get()
    {
        return($this->amount);
    }
}