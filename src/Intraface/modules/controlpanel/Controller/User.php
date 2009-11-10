<?php
class Intraface_modules_controlpanel_Controller_User extends k_Component
{
    protected $user;

    function map($name)
    {
        if ($name == 'preferences') {
            return 'Intraface_modules_controlpanel_Controller_UserPreferences';
        } elseif ($name == 'changepassword') {
            return 'Intraface_modules_controlpanel_Controller_ChangePassword';
        }
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/user.tpl.php');
        return $smarty->render($this);
    }

    function renderHtmlEdit()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/useredit.tpl.php');
        return $smarty->render($this);
    }

    function getUser()
    {
        if (is_object($this->user)) {
            return $this->user;
        }
        return $this->user = new Intraface_User($this->getKernel()->user->getId());

    }

    function putForm()
    {
        $value = $_POST;
        $address_value = $_POST;
        $address_value['name'] = $_POST['address_name'];
        $address_value['email'] = $_POST['address_email'];

        // @todo hvis man �ndrer e-mail skal man have en e-mail som en sikkerhedsforanstaltning
        // p� den gamle e-mail
        if ($this->getUser()->update($value)) {
            if ($this->getUser()->getAddress()->validate($address_value) && $this->getUser()->getAddress()->save($address_value)) {
                return new k_SeeOther($this->url(null));
            }
        }

        return $this->render();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function getValues()
    {
        if ($this->body()) {
            return $this->body();
        }
        $user = $this->getUser();
        $value = $user->get();
        $address_value = $user->getAddress()->get();
        $address_value['address_name'] = $address_value['name'];
        $address_value['address_email'] = $address_value['email'];

        return array_merge($value, $address_value);
    }
}