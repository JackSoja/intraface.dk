<?php
class Intraface_modules_contact_Controller_Choosecontact extends k_Component
{
    protected $contact;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("contact");

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/choosecontact');
        return $smarty->render($this, array('contacts' => $this->getContacts()));
    }

    function postForm()
    {
        $contact_module = $this->getKernel()->module("contact");
        $contact_module->includeFile('ContactReminder.php');

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if ($this->body('eniro_phone')) {
            $contact = $this->getContact();

            $eniro = new Services_Eniro();
            $value = $_POST;

            if ($oplysninger = $eniro->query('telefon', $_POST['eniro_phone'])) {
                // skal kun bruges s� l�nge vi ikke er utf8
                // $oplysninger = array_map('utf8_decode', $oplysninger);
                $address['name'] = $oplysninger['navn'];
                $address['address'] = $oplysninger['adresse'];
                $address['postcode'] = $oplysninger['postnr'];
                $address['city'] = $oplysninger['postby'];
                $address['phone'] = $_POST['eniro_phone'];
            }
        } else {
            // for a new contact we want to check if similar contacts alreade exists
            if (empty($_POST['id'])) {
                $contact = $this->getContact();
                if (!empty($_POST['phone'])) {
                    $contact->getDBQuery()->setCondition("address.phone = '".$_POST['phone']."' AND address.phone <> ''");
                    $similar_contacts = $contact->getList();
                }
            } else {
                $contact = new Contact($this->getKernel(), $_POST['id']);
            }

            // checking if similiar contacts exists
            if (!empty($similar_contacts) and count($similar_contacts) > 0 and empty($_POST['force_save'])) {
            } elseif ($id = $contact->save($_POST)) {
                // $redirect->addQueryString('contact_id='.$id);
                if ($redirect->get('id') != 0) {
                    $redirect->setParameter('contact_id', $id);
                }
                return new k_SeeOther($this->getRedirectUrl($id));

                //$contact->lock->unlock_post($id);
            }

            $value = $_POST;
            $address = $_POST;
            $delivery_address = array();
            $delivery_address['name'] = $_POST['delivery_name'];
            $delivery_address['address'] = $_POST['delivery_address'];
            $delivery_address['postcode'] = $_POST['delivery_postcode'];
            $delivery_address['city'] = $_POST['delivery_city'];
            $delivery_address['country'] = $_POST['delivery_country'];
        }

        return $this->render();
    }

    function renderHtmlCreate()
    {
        $contact_module = $this->getKernel()->module("contact");
        $contact_module->includeFile('ContactReminder.php');

        $this->document->addScript('contact/contact_edit.js');

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/edit');
        return $smarty->render($this);
    }

    function putForm()
    {
        $module = $this->getKernel()->module('contact');

        $contact = new Contact($this->getKernel(), intval($this->body('selected')));
        if ($contact->get('id') != 0) {
            return new k_SeeOther($this->getRedirectUrl($contact->get('id')));
        } else {
            $contact->error->set("Du skal vælge en kontakt");
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getContact()
    {
        if (is_object($this->contact)) {
            return $this->contact;
        }
        return $this->contact = new Contact($this->getKernel());
    }

    function getContacts()
    {
        if ($this->query('contact_id')) {
            $this->getContact()->getDBQuery()->setCondition("contact.id = ".intval($this->query('contact_id')));
        } elseif ($this->query('query') || $this->query('keyword_id')) {
            if ($this->query('query')) {
                $this->getContact()->getDBQuery()->setFilter('search', $this->query('query'));
            }
            if ($this->query('keyword_id')) {
                $this->getContact()->getDBQuery()->setKeyword($this->query('keyword_id'));
            }
        } else {
            $this->getContact()->getDBQuery()->useCharacter();
        }

        $this->getContact()->getDBQuery()->defineCharacter('character', 'address.name');
        $this->getContact()->getDBQuery()->usePaging('paging');
        $this->getContact()->getDBQuery()->storeResult('use_stored', 'select_contact', 'sublevel');
        $this->getContact()->getDBQuery()->setUri($this->url());

        if (intval($this->query('contact_id')) != 0) {
            $this->getContact()->getDBQuery()->setExtraUri("&last_contact_id=".intval($this->query('contact_id')));
        } elseif (intval($this->query('last_contact_id')) != 0) {
            $this->getContact()->getDBQuery()->setExtraUri("&last_contact_id=".intval($this->query('last_contact_id')));
        }

        return $contacts = $this->getContact()->getList();
    }

    function getRedirect()
    {
        return $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getUsedKeywords()
    {
        $keywords = $this->getContact()->getKeywordAppender();
        return $used_keywords = $keywords->getUsedKeywords();
    }

    function getContactModule()
    {
        return $this->getKernel()->module("contact");
    }

    function getValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return array('number' => $this->getContact()->getMaxNumber() + 1);
    }

    function getAddressValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return array();
    }

    function getDeliveryAddressValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        return array();
    }

    function getRedirectUrl($contact_id = 0)
    {
        return $this->context->getReturnUrl($contact_id);
    }
}
