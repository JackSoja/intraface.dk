<?php

/**
 * Bruges til at konvertere bel�b til og fra database
 * B�r den ikke kunne bruges de enkelte funktioner
 * direkte. Det vil i hvert fald g�re den mere anvendelig
 * i selve processen?
 * @author Sune
 * @version 001
 */
class Amount
{
    private $amount;

    /**
     * indskriv det bel�b det drejer sig om
     * @param amount double bel�b
     */
    function amount($amount) {
        $this->amount = $amount;
    }

    /**
     * Public: Konvertere et dansk bel�b til engelsk
     * B�r der ikke v�re noget der validerer?
     * @return 1
     */
    function convert2db() {
        $this->amount = str_replace(".", "", $this->amount);
        $this->amount = str_replace(",", ".", $this->amount);
        settype($this->amount, "double");
        return(1);
    }


    /**
     * Public: konvertere et engelsk bel�b til et dansk
     * B�r vist skrives om. Den returnerer jo 1 uanset?
     * @ return 1
     */
    function convert2dk() {
        //if(is_double($this->amount)) {
            $this->amount = number_format($this->amount, 2, ",", ".");
        //}
        return(1);
    }

    /**
     * Public: henter bel�bet efter konvertering
     * @return bel�b
     */
    function get() {
        return($this->amount);
    }
}


/**
 *
 */
class NewAmount
{
    /**
     * Amount in smallest possible enhed
     *
     * @var integer
     */
    private $amount;

    /**
     * Constructor
     *
     * @param integer $amount Amount (does not matter whether the comma or dot is included)
     *
     * @return void
     */
    public function __construct($amount)
    {
        // @todo there should probably be a sanity check
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '', $amount);

        $this->amount = (int)$amount;
    }

    /**
     * Returns the amount in database format
     *
     * @return double
     */
    function database($type = 'double')
    {
        return (double) $this->amount / 100;
    }

    /**
     * Returns the amount in a specific locale
     *
     * @param string $locale Which local to output to
     *
     * @return string
     */
    public function format()
    {
        return sprintf("%.2f", $this->amount / 100);
        //return $this->number_format($this->amount / 100, 2);
        //return $this->strtonumber($this->amount / 100);
    }

    /**
     * Returns the raw amount
     *
     * @return integer
     */
    function getRawAmount()
    {
        return $this->amount;
    }
}