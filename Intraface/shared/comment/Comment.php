<?php

/**
 * Messages
 *
 * Denne klasse skal bruges af cms og products, s� man kan kommentere
 * sider og produkter.
 *
 * @author Lars Olesen <lars@legestue.net>
 */
class Comment extends Intraface_Standard
{
    public $value;
    private $object;
    private $contact;
    private $error;
    private $id;

    /**
     * Konstrukt�r
     */
    function __construct($object, $id = 0)
    {
        if (!is_object($object)) {
            trigger_error('Comment::Comment kr�ver et object', E_USER_ERROR);
        }

        switch (strtolower(get_class($object))) {
            case 'product' :
                $this->type = 'product';
                $this->object = $object;
                break;
            case 'cms_page' :
                $this->type = 'cms_page';
                $this->object = $object;
                break;
            default :
                trigger_error('Keyword::Keyword kr�ver et gyldigt object', E_USER_ERROR);
                break;
        }

        if ($this->object->get('id') == 0) {
            trigger_error('Comment::Comment. Objektet har ikke nogen id', E_USER_ERROR);
        }

        $comment_shared = $this->object->kernel->useShared('comment');
        $this->types = $comment_shared->getSetting('types');

        $this->error = new Intraface_Error;

        $this->id = (int) $id;

        $this->object->kernel->useModule('contact');

        if ($this->id > 0) {
            $this->load();
        }
    }

    /**
     */

    function factory($type, $kernel, $value)
    {
        switch ($type) {
            case 'id' :

                $id = (int) $value;

                $db = new DB_Sql;
                $db->query("SELECT id, type_key, contact_id, belong_to_id FROM comment WHERE id = " . $id . " AND intranet_id=" . $kernel->intranet->get('id'));

                if (!$db->nextRecord()) {
                    return 0;
                }

                $comment_shared = $kernel->useShared('comment');
                $types = $comment_shared->getSetting('types');

                $class = $types[$db->f('type_key')];

                switch (strtolower($class)) {
                    case 'cms_page' :
                        $module = 'cms';
                        break;
                    default :
                        trigger_error('Comment::factory: Ugyldig klasse', E_USER_ERROR);
                        break;
                }

                $kernel->useModule($module);
                $kernel->useModule('contact');

                $belong_to_id = $db->f('belong_to_id');

                //
                // HACK HACK HACK HACK HACK
                // Jeg skal have fundet ud af hvordan jeg kan bruge denne notation
                // s� jeg kan f� �bnet de rigtige klasser
                //
                // $object = $class::factory($kernel, 'id', $belong_to_id);

                $object = CMS_Page :: factory($kernel, 'id', $belong_to_id);
                return new Comment($object, $db->f('id'));

                break;
            default :
                trigger_error('Comment::factory: ikke gyldig type');
                break;
        }

    }

    /**
     * Loader de enkelte beskeder
     *
     * Denne funktion skal automatisk filtrere links, s� der s�ttes attributten rel="nofollow" p�
     * s� spammere ikke f�r noget ud af at l�gge links.
     */
    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id FROM comment WHERE type_key=" . array_search($this->type, $this->types) . " AND intranet_id=" . $this->object->kernel->intranet->get('id') . " AND id =" . $this->id . " LIMIT 1");
        if (!$db->nextRecord()) {
            return 0;
        }
        $this->value['id'] = $db->f('id');
        $this->value['contact_id'] = $db->f('contact_id');
        $this->value['headline'] = $db->f('headline');
        $this->value['text'] = $db->f('text');
        $this->value['belong_to_id'] = $db->f('belong_to_id');
        $this->value['answer_to_id'] = $db->f('answer_to_id');
        $this->value['approved'] = $db->f('approved');

        $this->value['code'] = $db->f('code');
        $this->value['date_updated'] = $db->f('date_updated');
        $this->value['date_created'] = $db->f('date_created');
        $this->value['type_key'] = $db->f('type_key');
        $this->value['type'] = array_search($db->f('type_key'), $this->types);

        return 1;
    }

    /**
     * Validerer
     */
    function validate($var)
    {
        $validator = new Validator($this->error);
        $validator->isString($var['name'], 'Navn', '');
        $validator->isEmail($var['email'], 'E-mail', '');
        $validator->isString($var['headline'], 'Headline', '');
        $validator->isString($var['text'], 'Teksten er ikke gyldig', '');
        $validator->isString($this->type, 'Typen er ikke en tekststreng. Fik ' . $this->type);

        if (!in_array($this->type, $this->types)) {
            $this->error->set('Ugyldig type. Fik ' . $this->type);
        }

        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    /**
     * Gemmer et keyword
     */
    function save($var)
    {
        $var = safeToDb($var);
        $var = array_map('strip_tags', $var);
        // skal tjekke om oplysningerne om indtasteren allerede findes.
        // derefte der oprettes oplysninger - eller den g�ldende indtaster skal
        // bruges

        // b�r nok ogs� gemme ip

        // andre brugere skal kunne blive oplyst om nye svar i gruppen, hvis de har �nsket det (bare p� en af meddelelserne i gruppen).
        // dette skal man naturligvis kunne sl� fra i sit login!

        $contact = Contact :: factory($this->object->kernel, 'email', $var['email']);

        if ($contact->get('id') == 0) {
            if (!$contact->save($var)) {
                $contact->error->view();
            }
        }

        if (!$this->validate($var)) {
            return 0;
        }

        $db = new DB_Sql;

        if (!empty ($var['date_created'])) {
            $date_created = ", date_created = '" . $var['date_created'] . "'";
        } else {
            $date_created = ', date_created = NOW()';
        }

        if ($this->id > 0) {
            $sql_type = 'UPDATE ';
            $sql_end = ' WHERE id = ' . $this->id . '
                            AND intranet_id = ' . $this->object->kernel->intranet->get('id');
        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = $date_created . ", intranet_id = " . $this->object->kernel->intranet->get('id') . ", type_key = '" . array_search($this->type, $this->types) . "', 	belong_to_id = " . $this->object->id;
        }

        $sql = $sql_type . "comment SET
                    answer_to_id = " . (int) $var['answer_to_id'] . ",
                    contact_id = " . $contact->get('id') . ",
                    headline = '" . $var['headline'] . "',
                    text = '" . $var['text'] . "',
                    ip = '" . $var['ip'] . "',
                    code = '" . md5($var['text'] . date('Y-m-d H:i:s') . $contact->get('id')) . "',
                    date_updated = NOW()" . $sql_end;
        $db->query($sql);

        if ($this->id == 0) {
            return $db->insertedId();
        }
        $this->load();
        return $this->id;

    }
    /**
     * Denne metode sletter et n�gleord i n�gleordsdatabasen
     */
    function delete()
    {
        if ($this->id == 0) {
            return 0;
        }
        $db = new DB_Sql;
        $db->query("UPDATE comment SET active = 0
                    WHERE intranet_id = " . $this->object->kernel->intranet->get('id') . "
                        AND id = " . $this->id);
        return 1;
    }

    /**
     * M�ske skal denne sikres yderligere. Dog tror jeg ikke det er muligt at to
     * koder p� noget tidspunkt kan v�re det samme?
     *
     */
    function approve($code)
    {
        $code = safeToDb($code);
        // her skal besked confirmes
        $db = new DB_Sql;
        $db->query("UPDATE comment SET approved = 1 WHERE code = '" . $code . "'");
        return $db->affectedRows();
    }

    /**
     * Denne funktion henter poster i objektet som h�rer til et n�gleord
     * @param
     */
    function getList($type = 'contact', $kernel, $id = 0)
    {
        // vi skal have tilf�jet til denne, at hvis der ikke er et userobjekt, s�
        // vises kun approved comments

        switch ($type) {
            case 'cmspage' :
                $sql_type = " AND type_key = 3"; // cmspage
                $sql_end = " AND belong_to_id = " . $id . " ORDER BY date_created DESC";
                break;
            case 'contact' :
                /*
                    $sql .= " AND contact_id = " . $contact_id;
                */
            case 'all' :
                $sql_type = "";
                $sql_end = " ORDER BY date_created DESC";
                break;
            default :
                trigger_error('Message::getList Type ikke underst�ttet', FATAL);
                break;

        }

        $module = $kernel->useModule('contact');

        $db = new DB_Sql;
        $sql = "SELECT id, headline, text, approved, contact_id FROM comment WHERE active = 1 AND intranet_id = " . $kernel->intranet->get('id') . $sql_type . $sql_end;

        $db->query($sql);
        $messages = array ();
        $i = 0;
        while ($db->nextRecord()) {
            $messages[$i]['id'] = $db->f('id');
            $messages[$i]['headline'] = $db->f('headline');
            $messages[$i]['text'] = $db->f('text');
            $messages[$i]['approved'] = $db->f('approved');

            $messages[$i]['contact_id'] = $db->f('contact_id');
            $contact = new Contact($kernel, $db->f('contact_id'));
            $messages[$i]['contact_id'] = $contact->get('id');
            $messages[$i]['contact_name'] = $contact->get('name');
            $messages[$i]['contact_email'] = $contact->address->get('email');

            if ($kernel->setting->get('intranet', 'comment.gravatar') == 'show') {
                $default = $kernel->setting->get('intranet', 'comment.gravatar.default_url');
                $size = $kernel->setting->get('intranet', 'comment.gravatar.default_size');
                $messages[$i]['gravatar_url'] = "http://www.gravatar.com/avatar.php?gravatar_id=" . md5($messages[$i]['contact_email']) . "&amp;rating=R&amp;default=" . urlencode($default) . "&amp;size=" . $size;
                $messages[$i]['gravatar_size'] = $size;
            }
            $i++;
        }
        return $messages;
    }
}