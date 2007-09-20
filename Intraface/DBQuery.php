<?php

/**
 * Klasse til at lave filter og paging p� getList
 * I id�fasen. Ved endnu ikke om dette er metoden at g�re det p�
 *
 * @author: Sune
 * @version: 0.1
 */

require_once 'Intraface/3Party/Database/Db_sql.php';

class DBQuery {

    var $kernel;
    var $table;
    var $required_condition;
    var $error;

    var $join;

    var $use_stored = false;

    var $condition = array();
    var $sorting = array();
    var $filter = array();
    // var $group_by = array();
    // var $having_condition = array();

    var $character;
    var $character_var_name;
    var $find_character_from_field;
    var $use_character;

  var $extra_uri;

    var $keyword_ids = array();

    var $paging_var_name;
    var $rows_pr_page;
    var $recordset_num_rows;
    var $paging_start = NULL;

    var $store_var_name;
    var $store_name;
    var $store_toplevel;
    var $store_user_condition;

    function DBQuery($kernel, $table, $required_conditions = "") {
        if (!is_object($kernel)) {
            trigger_error('DBQuery needs kernel', E_USER_ERROR);
            return false;
        }
        $this->kernel = $kernel;
        $this->table = $table;
        $this->required_conditions = $required_conditions;

        $this->recordset_num_rows = 0;
        $this->error = new Error;
        $this->use_character = false;

        if(strtolower(get_class($this->kernel->user)) == 'user') {
            $this->rows_pr_page = $this->kernel->setting->get('user', 'rows_pr_page');
        }
        else {
            $this->rows_pr_page = 20; // Systemdefault!
        }
        if(is_object($this->kernel->user)) {
            $this->store_user_condition = "user_id = ".$this->kernel->user->get("id");
        }
        elseif(is_object($this->kernel->weblogin)) {
            $this->store_user_condition = "weblogin_session_id = \"".$this->kernel->weblogin->get("session_id")."\"";
        }
        else {
            trigger_error('Mangler weblogin eller user', E_USER_ERROR);
        }

        // print("weblogin_session_id = \"".$this->kernel->weblogin->get("session_id")."\"");

    }


    /**
     * Public
     * Denne funktion benyttes til at definere tabeller, som den skal joines med
     *
     */
    function setJoin($type, $table, $join_on, $required_condition) {
        $i = count($this->join);

        $this->join[$i]["type"] = $type;
        $this->join[$i]["table"] = $table;
        $this->join[$i]["join_on"] = $join_on;
        $this->join[$i]["required_condition"] = $required_condition;
    }

    /**
     * Private (kan benyttes public hvis man vil lave sin egen sql-streng)
     * Benyttes til at lave sql-streng med join tabeller
     */
    function getJoin() {


        $join["table"] = "";
        $join["condition"] = "";


        for($i = 0, $max = count($this->join); $i < $max; $i++) {
            $join["table"] .= " ".strtoupper($this->join[$i]["type"])." JOIN ".$this->join[$i]["table"]." ON ".$this->join[$i]["join_on"];

            if($this->join[$i]["required_condition"] != "") {
                $join["condition"] .= " AND (".$this->join[$i]["required_condition"].")";
            }
        }

        return $join;

    }

  function setExtraUri($extra_uri) {
        $this->extra_uri = $extra_uri;
  }

    /**
     * Public
     * Returnere et array med bogstaver til alfabetisering. Hvis $view = "HTML" returnere den array med HTML link
     */
    function getCharacters($view = "") {
        // Denne funktion kan optimeres med, at hvis den kaldes 2 gange, s� benytter den bare det gamle resultat igen.

        $chars = array();
        if($this->character_var_name != "") {


            $i = 0;
            $tmp = clone $this;

            $tmp->clearAll();
            $tmp->setSorting("bogstav");
            $db = $tmp->getRecordset("distinct(LEFT(".$this->find_character_from_field.", 1)) AS bogstav", "full");

            // Hvis der ikke er mere end et bogstav, s� er der ingen grund til character og vi returnere intet
            if($db->numRows() <= 1) {
                return array();
            }

            while ($db->nextRecord()) {


                $bogstav = $db->f('bogstav');

                if (empty($bogstav)) {
                    continue;
                }

                // Hvis det er et mellemrum tager vi den ud
                if(trim($bogstav) == "") {
                    CONTINUE;
                }

                if($view == 'html') {
                    $bogstav = $db->f('bogstav');

                    if($this->character == strtolower($bogstav)) {
                        $chars[$i] = '<strong>'.strtolower($bogstav).'</strong>';
                    }
                    else {
                        $chars[$i] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->character_var_name."=".strtolower($bogstav)."&amp;".$this->extra_uri."\">".strtolower($bogstav)."</a>";
                    }
                }
                else {
                    $chars[$i] = strtolower($bogstav);
                }
                $i++;
            }
        }



        return $chars;
    }

    /**
     * Public
     * Returnere et array med tal til paging. Hvis $view = "HTML" returneres et array med links
     */
    function getPaging($view = "") {

        $but = array();
        $j = 1;

        if($this->store_name != "") {
            $url = "&amp;".$this->store_var_name."=true";
        }
        elseif($this->character_var_name != "" && isset($_GET[$this->character_var_name])) {
            $url = "&amp;".$this->character_var_name."=".$_GET[$this->character_var_name];
        }
        else {
            $url = "";
        }

        // print($this->recordset_num_rows.' <= '.$this->rows_pr_page);

        if($this->recordset_num_rows <= $this->rows_pr_page) {
            // Der er f�rre poster end pr. side. Paging kan ikke betale sig
            return array();
        }

        for($i = 0; $i * $this->rows_pr_page < $this->recordset_num_rows; $i++) {

            if($view == "html") {
                if($this->paging_start == $i*$this->rows_pr_page) {
                    $but[$j] = "<strong>".$j."</strong>";
                }
                else {
        $but[$j] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->paging_var_name."=".($i*$this->rows_pr_page).$url."&amp;".$this->extra_uri."\">".$j."</a>";
                }
            }
            else {
                $but['offset'][$j] = $i * $this->rows_pr_page;
            }
            $j++;
        }
        /*
        if(!isset($_GET[$this->paging_var_name])) {
            $_GET[$this->paging_var_name] = 0;
        }
        settype($_GET[$this->paging_var_name], "integer");
        */
        if(count($but) > 0) {
            if($this->paging_start > 0) { // $_GET[$this->paging_var_name]
                if($view == "html") {
                    $but[0] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->paging_var_name."=".($this->paging_start - $this->rows_pr_page).$url."&amp;".$this->extra_uri."\">Forrige</a>"; // $_GET[$this->paging_var_name]
                }
                else {
                    $but['next'] = $this->paging_start - $this->rows_pr_page; // $_GET[$this->paging_var_name]
                }
            }
            else {
                $but['previous'] = 0;
            }

            if($this->paging_start < $this->recordset_num_rows - $this->rows_pr_page) { // $_GET[$this->paging_var_name]
                if($view == "html") {
                    $but[$j] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->paging_var_name."=".($this->paging_start + $this->rows_pr_page).$url."&amp;".$this->extra_uri."\">N�ste</a>"; // $_GET[$this->paging_var_name]
                }
                else {
                    $but['next'] = $this->paging_start + $this->rows_pr_page; // $_GET[$this->paging_var_name]
                }
            }
        }


        return $but;

    }


    /*
     * Returnere st�rrelsen p� recordsettet, samt hvorfra og hvormange
     *
     */
    function getRecordsetSize() {
        if(!isset($_GET[$this->paging_var_name])) {
            $show_from = 0;
        }
        else {
            $show_from = intval($_GET[$this->paging_var_name]);
        }

        $show_to = $show_from + $this->rows_pr_page;
        if($show_to > $this->recordset_num_rows) {
            $show_to = $this->recordset_num_rows;
        }
        $show_from = $show_from + 1;


        return array('number_of_rows' => $this->recordset_num_rows, 'rows_pr_page' => $this->rows_pr_page, 'show_from' => $show_from, 'show_to' => $show_to);
    }

    /************************** FILTER FUNKTIONER *******************************/

    /**
     * S�tter en filter parameter
     * Kan f.eks. v�re key: "seacrh";  value: $_POST["search"]
     * For at filteret kan bruges til noget, skal det kombineres med setCondition inde i getList funktionen
     */
    function setFilter($key, $value) {
        $this->filter[$key] = $value;
    }

    /**
     * Checker om et filter er sat
     */
    function checkFilter($key) {
        if(isset($this->filter[$key])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Returnere v�rdien af filteret
     */
    function getFilter($key) {
        if(isset($this->filter[$key])) {
            return $this->filter[$key];
        }
        else {
            return "";
        }
    }


    /*************************** FUNKTIONER TIL AT DEFINGERE S�GNINGEN ***********************/

    /**
     * Public
     * Bruges til at s�tte where felterne
     * F.eks.: "date > '12-12-2004' OR paid = 0"
     * Flere setConditions kan kaldes, og s� vil hver sql-s�tning s�ttes sammen med et AND
     */
    function setCondition($string) {
        $this->condition[] = $string;
    }

    /**
     * Fjerner alle condition, sortings, og keywords
     */
    function clearAll() {
        $this->condition = array();
        $this->sorting = array();
        $this->keyword_ids = array();
    }

    /**
     * Bruges til at s�tte order by
     * F.eks. "number, date ASC"
     * Flere sorting kan s�ttes, og vil blive sat sammen i r�kkenf�lgen de er sat med et komma.
     */
    function setSorting($string) {
        $this->sorting[] = $string;
    }


    /**
     * Tjekker om sorting er sat
     */
    function checkSorting() {
        return count($this->sorting);
    }


    /**
     * Aktivere alfabetisering. Bruges til at vise poster der starter med character
     */
    function defineCharacter($character_var_name, $field) {
        if($character_var_name != "" && $field != "") {

            $this->character_var_name = $character_var_name;
            $this->find_character_from_field = $field;
        }
    }

    /**
     * Benytter character
     */
    function useCharacter() {
        $this->use_character = true;
    }


    /**
     * Aktivere paging.
     */
    function usePaging($paging_var_name, $rows_pr_page = 0) {
        if($paging_var_name != "") {
            $this->paging_var_name = $paging_var_name;
            if((int)$rows_pr_page > 0) {
                $this->rows_pr_page = $rows_pr_page;
            }
        }

        // Hvis den er med i get-strengen, s� s�tter vi den med det samme.
        if($this->paging_var_name != "" && isset($_GET[$this->paging_var_name])) {
            $this->setPagingOffset($_GET[$this->paging_var_name]);
        }
    }


    /**
     * Til manuelt at s�tte paging offset.
     */
    function setPagingOffset($offset) {
        $this->paging_start = intval($offset);
    }


    /**
     * Til manuelt at s�tte hvormange der skal v�re pr. side
     */
    function setRowsPerPage($number) {
        $this->rows_pr_page = (int)$number;
    }


    /**
     * V�lger keywords som kun poster med disse skal vises
     */
    function setKeyword($keyword) {

        if(is_array($keyword)) {
            $this->keyword_ids = $keyword;
        }
        else {
            $this->keyword_ids = array(intval($keyword));
        }
    }

    function getKeyword($key = -1) {
        if((int)$key >= 0) {
            if(isset($this->keyword_ids[$key])) {
                return $this->keyword_ids[$key];
            }
            else {
                return 0;
            }
        }
        else {
            return $this->keyword_ids;
        }
    }

    /**************************** ANDRE FUNKTIONER *****************************/

    /**
     * Importer en anden error klasse
     */
    function useErrorObject(&$error) {
        $this->error = &$error;
    }


    /**
     * Aktiver gemningen af s�geresultat
     * level: Toplevel benyttes til de prim�re lister som products, contacts, debtor osv.
     *   Sublevel benyttes n�r man f.eks. under debtor skal benytte en liste over produkter til at s�tte p� faktura.
     *   Der vil kun blive stored �n toplevel result, mens der vil blive gemt alle sublevel.
     *   Det skyldes at hver gang man har �bnet en toplevel liste, skal skal man ikke se tildigere toplevel s�gninger mere.
     */
    function storeResult($store_var_name, $store_name, $level) {
        $this->store_var_name = $store_var_name;
        $this->store_name = $store_name;

        $levels = array(0 => "sublevel", 1 => "toplevel");
        $toplevel = array_search($level, $levels);
        if($toplevel === false) {
            trigger_error("Ugydlig niveau. Skal enten v�re 'toplevel' eller 'sublevel'", FATAL);
        }

        $this->store_toplevel = $toplevel;

        // Hvis get-variablen er sat, s� kan vi lige s� godt s�tte den med det samme
        if($this->store_name != "" && isset($_GET[$this->store_var_name]) && $_GET[$this->store_var_name] == "true") {
            $this->useStored();
        }
    }

    function useStored($value = true) {
        if(!in_array($value, array(true, false))) {
            trigger_error("F�rste parameter til DBQuery->useStored() er ikke ente true eller false", E_USER_ERROR);
        }

        $this->use_stored = $value;
    }


    /*********************** FUNKTIONER TIL AT RETURNERE SQL-STRENG *************************/

    /**
     * Public
     * Returnere db object med recordset
     *@param fields: fieldst from the tabel you will recive
     *@param use: 'full': without any paging, '' with paging
     *@param print: true will show sql query
     */
    function getRecordset($fields, $use = "", $print = false) {

        $db = new DB_sql;
        $csql = ""; //Definere variable
        $stored_character = false;

        // Henter stored result, hvis det er aktiveret og hvis det bliver efterspurgt.
        // hack use_stored = 1 LO Bruges i webshop. Ved ikke om det er tilt�nkt s�dan
        if($use != "full" && $this->use_stored)  { // $this->store_name != "" && (isset($_GET[$this->store_var_name]) && $_GET[$this->store_var_name] == "true")

            $db->query("SELECT dbquery_condition, joins, keyword, paging, sorting, filter, first_character FROM dbquery_result WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND ".$this->store_user_condition." AND name = \"".$this->store_name."\"");

            if($db->nextRecord()) {
                $this->condition = unserialize(base64_decode($db->f("dbquery_condition")));
                $this->join = unserialize(base64_decode($db->f("joins")));
                $this->keyword_ids = unserialize(base64_decode($db->f("keyword")));
                if($this->paging_start === NULL) {
                    // Kun hvis den ikke manuelt er blevet sat, s� skal den s�ttes.
                    $this->paging_start = $db->f("paging");
                }
                $this->sorting = unserialize(base64_decode($db->f("sorting")));
                $this->filter = unserialize(base64_decode($db->f("filter")));
                // $this->group_by = unserialize(base64_decode($db->f("group_by")));
                // $this->having_condition = unserialize(base64_decode($db->f("having_condition")));


                $stored_character = $db->f("first_character");
                // Hvis character er sat, s� benyttes character
                if($stored_character != "") {
                    $this->use_character = true;
                }
            }
        }

        if($this->paging_start === NULL) {
            // Hvis paging ikke er sat, s� skal den bare v�re 0
            $this->paging_start = 0;
        }

        // S�tter character p�
        if($use != "full" && $this->use_character) {

            if($this->character_var_name == "") {
                trigger_error("For at benytte useCharacter(), skal du ogs� benytte defineCharacter()", FATAL);
            }

            if(isset($_GET[$this->character_var_name]) && $_GET[$this->character_var_name] != "") {
                $this->character = $_GET[$this->character_var_name];
            }
            elseif($stored_character !== false) {
                $this->character = $stored_character;

                // keep it that way
            }
            else {
                // $tmp_dbquery = clone $this;
                $tmp = $this->getCharacters();
                // Vi tager det f�rste character
                if(array_key_exists(0, $tmp) AND $tmp[0] != "") {
                    $this->character = $tmp[0];
                }
                else {
                    $this->character = "";
                }
            }

            if($this->character != "") {
                $csql = " AND LEFT(".$this->find_character_from_field.", 1) = \"".$this->character."\"";
            }
        }

        // Henter join s�tninger
        $join = $this->getJoin();

        $extra_condition = "";

        // S�tter keyword p� joins�tninger
        if($use != "full" && count($this->keyword_ids) != 0) {
            $ksql = "";
            for($i = 0, $max = count($this->keyword_ids); $i < $max; $i++) {
                if($this->keyword_ids[$i] != 0) {
                    if($ksql != '') {
                        $ksql .= " OR";
                    }

                    $ksql .= " keyword_x_object.keyword_id = ".$this->keyword_ids[$i];
                }
            }

            if($ksql != "") {
                $join["table"] .= " LEFT JOIN keyword_x_object ON keyword_x_object.belong_to = ".$this->table.".id";
                $join["condition"] .= " AND (".$ksql.")";
                $extra_condition = "GROUP BY ".$this->table.".id HAVING COUNT(keyword_x_object.keyword_id) = ".count($this->keyword_ids);
            }
        }

        $sql = "FROM ".$this->table."".$join["table"]." WHERE 1 = 1 ";

        if($this->required_conditions != "") {
            $sql .= "AND (".$this->required_conditions.") ";
        }
        $sql .= $join["condition"];

        $sql_end = $this->getSQLString($extra_condition);

        // Tjekker antallet af poster for at se om character er n�dvendigt!
        if($csql != "") {
            $db->query("SELECT COUNT(".$this->table.".id) AS num_rows ".$sql.$sql_end);
            $db->nextRecord() OR trigger_error("Kunne ikke eksekvere SQL-s�tning", FATAL);

            if($db->f("num_rows") > $this->rows_pr_page) {
                $sql .= $csql; // tilf�jer charater
            }
            else {
                // S� er der ikke nogen grund til at benyttes character
                $this->character_var_name = "";
            }
        }


        $sql .= $sql_end;

        // Laver paging
        if($use != "full" && $this->paging_var_name != "") {

            $db->query("SELECT COUNT(DISTINCT(".$this->table.".id)) AS num_rows ".$sql);
            if($db->nextRecord()) { // Dette er vist lige lovlig dr�stisk: OR trigger_error("Kunne ikke eksekvere SQL-s�tning", FATAL);
                $this->recordset_num_rows = $db->f("num_rows");
            }
            else {
                $this->recordset_num_rows = 0;
            }

            if($this->recordset_num_rows > $this->rows_pr_page) {
                $sql .= " LIMIT ".$this->paging_start.", ".$this->rows_pr_page;
            }
        }
        // echo $sql;
        // Gemmer s�geresultatet
        // Skal ikke gemmes n�r det er et fuldt resultat.
        if($use != "full" && $this->store_name != "") {

            $store_sql = "name = \"".$this->store_name."\",
                dbquery_condition = \"".base64_encode(serialize($this->condition))."\",
                joins = \"".base64_encode(serialize($this->join))."\",
                keyword = \"".base64_encode(serialize($this->keyword_ids))."\",
                paging = ".$this->paging_start.",
                first_character = \"".$this->character."\",
                sorting = \"".base64_encode(serialize($this->sorting))."\",
                filter = \"".base64_encode(serialize($this->filter))."\",
                date_time = NOW()";

                // group_by = \"".base64_encode(serialize($this->group_by))."\",
                // having_condition = \"".base64_encode(serialize($this->having_condition))."\",



            if($this->store_toplevel == 1) {
                $db->query("SELECT id FROM dbquery_result WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND ".$this->store_user_condition." AND toplevel = 1");
                if($db->nextRecord()) {

                    $db->query("UPDATE dbquery_result SET ".$store_sql." WHERE id = ".$db->f("id"));
                }
                else {

                    $db->query("INSERT INTO dbquery_result SET intranet_id = ".$this->kernel->intranet->get("id").", ".$this->store_user_condition.", toplevel = 1, ".$store_sql);
                }
            }
            else {
                $db->query("SELECT id FROM dbquery_result WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND ".$this->store_user_condition." AND toplevel = 0 AND name = \"".$this->store_name."\"");
                if($db->nextRecord()) {
                    $db->query("UPDATE dbquery_result SET ".$store_sql." WHERE id = ".$db->f("id"));
                }
                else {

                    $db->query("INSERT INTO dbquery_result SET intranet_id = ".$this->kernel->intranet->get("id").", ".$this->store_user_condition.", toplevel = 0, ".$store_sql);
                }
            }
        }


        $sql = "SELECT ".$fields." ".$sql;

        if($print) {
            print($sql);
        }

        $db->query($sql);

        return $db;

    }

        /**
     * Retunere streng der bruges i sql-s�tning
     */
    function getSqlString($extra_condition = "") {

        $where = $this->getConditionString();
        $order_by = $this->getSortingString();
        $sql = "";

        if($this->error->isError()) {
            // Hvis der er fejl returnere den default streng
            // Den skal ikke trigger error. Det giver problemer hvis der er fejl i betaling af faktura. Men hvad skal den s�? /Sune (29/6 2005)
            //trigger_error("Der er opst�et en fejl i dbquery->getSqlString()", ERROR);
        }

        if($where != "") {
            $sql .= " AND ".$where;
        }

        if($extra_condition != '') {
            $sql .= ' '.$extra_condition.' ';
        }

        if($order_by != "") {
            $sql .= " ORDER BY ".$order_by;
        }

        return($sql);
    }


    /**
     * Private
     *
     * Bruges til at sammens�tte condition-strengene
     */

    function getConditionString() {

        $condition = $this->condition;

        $sql = "";

        for($i = 0, $mi = count($condition); $i < $mi; $i++) {

            if($i != 0) {
                // Alle andre end den f�rste s�ttes der et AND f�r.
                $sql .= " AND ";
            }
            $sql .= "(".$condition[$i].")";
        }

        return $sql;
    }

    /**
     * Benyttes til at sammens�tte sorting-strengene.
     */
    function getSortingString() {

        $sorting = $this->sorting;

        $sql = "";

        for($i = 0, $mi = count($sorting); $i < $mi; $i++) {

            if($i != 0) {
                // Alle andre end den f�rste s�ttes der et , f�r.
                $sql .= ", ";
            }
            $sql .= $sorting[$i];
        }
        return $sql;
    }

    function display($type) {
        switch ($type) {
            case 'paging':
                $paging = $this->getPaging('html');
                if(empty($paging)) return '';
                $links = "";
                for($i = 0, $max = count($paging); $i < $max; $i++) {
                    if(array_key_exists($i, $paging) AND $paging[$i] != "") {
                        $links .= $paging[$i]." | ";
                    }
                }

                $size = $this->getRecordsetSize();

                return '<div class="pagingNav">Side: '.$links.'<br />Viser: '.$size['show_from'].' til '.$size['show_to'].' af '.$size['number_of_rows'].'. </div>';
              break;
       case 'character':
                   if (count($this->getCharacters("html")) > 0) {
              $links = implode(" - ", $this->getCharacters("html"));
              return '<div class="characterNav">- ' . $links . ' -</div>';
          }
          else {
              return '';
          }
           break;
    }
  }
}

/*

    function getConditionString($use = "") {
        // Her skal hele condition strengen splittes op mellem hvert AND og OR
        // Felterne skal kontrolleres af de er defineret i defineConditionField
        // Felternes type kontrolleres. Hvis det er date, laves datoen om til uk-date (2004-12-12)
        // sql-strengen konstrueres ud fra de validerede og evt. �ndrede v�rdier

        if($use == "default") {
            $condition = $this->default_condition;
        }
        else {
            $condition = $this->condition;
        }

        $sql = "";

        for($i = 0, $mi = count($condition); $i < $mi; $i++) {

            if($i != 0) {
                // Alle andre end den f�rste s�ttes der et AND f�r.
                $sql .= " AND ";
            }

            // opdeler strengen ved " s� vi kan sortere strenge v�k.
            $strings = split('\"', $condition[$i]);

            for($j = 0, $mj = count($strings); $j < $mj; $j++) {

                $strings[$j] = trim($strings[$j]);
                while($strings[$j] != "") {
                    // '(payed=2)AND description = '

                    if(substr($strings[$j], 0, 1) == ")") {
                        // Hvis strengen start med ) tager vi den ud og putter i $sql. Det kan ske i f�rste array efter en streng
                        $sql .= ")";
                        $strings[$j] = trim(substr($strings[$j], 1));

                        if($strings[$j] == "") {
                            // Det kan v�re at det eneste der er i strengen er en ) s� hvis der ikke er mere, k�re vi videre til n�ste.
                            continue;
                        }

                    }

                    if(substr($strings[$j], 0, 3) == "AND") {
                        $sql .= " AND ";
                        $strings[$j] = trim(substr($strings[$j], 3));
                    }
                    elseif(substr($strings[$j], 0, 2) == "OR") {
                        $strings[$j] = trim(substr($strings[$j], 2));
                        $sql .= " OR ";
                    }
                    else {
                        // Det er kan v�re starten p� en streng s� det er OK.
                    }

                    if(substr($strings[$j], 0, 1) == "(") {
                        // Hvis strengen starter med ( tager vi den ud og putter i $sql
                        $sql .= "(";
                        $strings[$j] = substr($strings[$j], 1);
                        $strings[$j] = trim($strings[$j]);
                    }

                    if(ereg("^([a-z0-9_\.]+) *(=|<|>|<=|>=|!=|LIKE) *$", $strings[$j], $parts)) {
                        // Hvis det er den sidste f�r der kommer en streng

                        $field = $parts[1];
                        $operator = $parts[2];
                        $operator_after = "";
                        $j++; // Vi tager den n�ste der er en streng; M�ske lige et tjek her at der findes en streng.
                        $value = $strings[$j];
                    }
                    elseif(ereg("^([a-z0-9_\.]+) *(=|<|>|<=|>=|!=) *(-?[0-9]+|NOW\(\))(.*)$", $strings[$j], $parts)) {
                        // Alle andre strenge skulle gerne kunne d�kkes af denne
                        $field = $parts[1];
                        $operator = $parts[2];
                        $operator_after = "";
                        $value = $parts[3];
                    }
                    elseif(ereg("^([a-z0-9_\.]+) *(IN\()([0-9, ]+)\)(.*)$", $strings[$j], $parts)) {
                        // Alle andre strenge skulle gerne kunne d�kkes af denne
                        $field = $parts[1];
                        $operator = $parts[2];
                        $operator_after = ")";
                        $value = $parts[3];
                    }
                    else {
                        trigger_error("Fejl: '".$strings[$j]."' opfylder ikke m�nstret field = [int] / NOW() / IN([int1],[int2],...)", FATAL);
                    }

                    if(isset($this->condition_field[$field])) {
                        if($this->condition_field[$field]["type"] == "date") {
                            if($value == "NOW()") {
                                $value = "NOW()";
                            }
                            else {
                                $date = new Intraface_Date($value);
                                if($date->convert2db()) {
                                    $value = "\"".$date->get()."\"";
                                }
                                else {
                                    $this->error->set("Ugyldig datoformat '".$value."'");
                                }
                            }
                        }
                        elseif($this->condition_field[$field]["type"] == "string") {
                            $value = "\"".$value."\"";
                        }
                        else {
                            // integer
                            if($operator != "IN(") {
                                if($value != intval($value)) {
                                    $this->error->set("Dette skal v�re et tal '".$value."'");
                                }
                            }
                            $value = intval($value);
                        }

                        $sql .= $field." ".$operator." ".$value.$operator_after;
                    }
                    else {
                        trigger_error("Feltet '".$field."' er ikke defineret", FATAL);
                    }

                    $strings[$j] = trim($parts[4]);
                    if(substr($strings[$j], 0, 1) == ")") {
                        // Hvis strengen slutter med ) tager vi den ud og putter i $sql
                        $sql .= ")";
                        $strings[$j] = substr($strings[$j], 1);
                        $strings[$j] = trim($strings[$j]);
                    }
                }
            }
        }

        return($sql);
    }
}

*/

?>