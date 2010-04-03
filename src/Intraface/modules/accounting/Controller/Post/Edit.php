<?php
/**
 *
 * @deprecated
 *
 */
class Intraface_modules_accounting_Controller_Post_Edit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        if (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
            $post = Post::factory($this->getYear(), (int)$_GET['id']);
            $values = $post->get();
            $values['date'] = $post->get('date_dk');
            $values['debet'] = $post->get('debet');
            $values['credit'] = $post->get('credit');
        } elseif (!empty($_GET['voucher_id']) AND is_numeric($_GET['voucher_id'])) {
            $post = new Post(new Voucher($this->getYear(), $_GET['voucher_id']));
            $values['date'] = $post->voucher->get('date_dk');
        } else {
            // setting variables
            $post = Post::factory($this->getYear(), $this->context->name());
            $values['date'] = date('d-m-Y');
            $values['debet_account_number'] = '';
            $values['credit_account_number'] = '';
            $values['amount'] = '';
            $values['text'] = '';
            $values['id'] = '';
        }
        $account = new Account($this->getYear());
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/post/edit');
        return $smarty->render($this, array('post' => $post, 'account' => $account));
    }

    function postForm()
    {
        $year = $this->getYear();
        $this->getYear()->checkYear();

        // tjek om debet og credit account findes
        $post = new Post(new Voucher($this->getYear(), $_POST['voucher_id']), $_POST['id']);
        $account = Account::factory($this->getYear(), $_POST['account']);

        $date = new Intraface_Date($_POST['date']);
        $date->convert2db();

        $debet = new Intraface_Amount($_POST['debet']);
        if (!$debet->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $debet = $debet->get();

        $credit = new Intraface_Amount($_POST['credit']);
        if (!$credit->convert2db()) {
            $this->error->set('Bel�bet kunne ikke konverteres');
        }
        $credit = $credit->get();

        if ($id = $post->save($date->get(), $account->get('id'), $_POST['text'], $debet, $credit)) {
            return new k_SeeOther($this->url('../../../'));
        } else {
            $values = $_POST;
        }

    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear()
    {
        return new Year($this->getKernel());

    }

    function getAccount()
    {
        return new Account($this->getYear());
    }

    function getYearGateway()
    {
        return $this->context->getYearGateway();
    }
}