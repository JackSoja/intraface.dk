<?php
/**
 * OnlinePayment Server version 0.1.0
 * Class named with version XXYY from version numbering XX.YY.ZZ
 *
 * @category XMLRPC_Server
 * @package  Intraface_XMLRPC_OnlinePayment
 * @author   Sune Jensen <sune@intraface.dk>
 * @version  @package-version@
 */
class Intraface_XMLRPC_OnlinePayment_Server0001 extends Intraface_XMLRPC_Server
{
    /**
     * Returns target to perform payment on
     *
     * @param struct $credentials    Credentials to use the server
     * @param string $identifier_key Debtor identifier key
     *
     * @return array debtor
     */
    public function getPaymentTarget($credentials, $identifier_key)
    {
        $this->checkCredentials($credentials);

        $identifier_key = $this->processRequestData($identifier_key);

        if (trim($identifier_key) == '') {
            throw new XML_RPC2_FaultException('No valid identifier key was given', -4);
        }

        $debtor = $this->debtorFactory($identifier_key);

        if ($debtor->get('id') == 0) {
            throw new XML_RPC2_FaultException('No valid debtor was found from the identifier key', -4);
        }

        $onlinepayment = $this->onlinePaymentFactory();
        $onlinepayment->getDBQuery()->setFilter('belong_to', $debtor->get("type"));
        $onlinepayment->getDBQuery()->setFilter('belong_to_id', $debtor->get('id'));
        $onlinepayment->getDBQuery()->setFilter('status', 2);

        $parameter['payment_online'] = 0;
        foreach ($onlinepayment->getlist() as $p) {
            $parameter['payment_online'] += $p["amount"];
        }

        return $this->prepareResponseData(
            array(
                'type' => $debtor->get('type'),
                'id' => $debtor->get('id'),
                'number' => $debtor->get('number'),
                'this_date' => $debtor->get('this_date'),
                'description' => $debtor->get('description'),
                'total_price' => $debtor->get('total'),
                'arrears' => $debtor->get('arrears'),
                'payment_online' => $parameter['payment_online']
            )
        );
    }

    /**
     * Saves details for a processed onlinepayment
     *
     * @param struct $credentials Credentials to use the server
     * @param string $identifier_key Debtor identifier key
     * @param integer $transaction_number Transaction Number
     * @param string $transaction_status Transaction Status
     * @param string $pbs_status Status from PBS
     * @param float $amount Amount
     * @param string $text A short description to the payment
     * @param integer $id Id on payment if wanted to update existing payment
     *
     * @return integer $payment_id
     */
    public function saveOnlinePayment($credentials, $identifier_key, $transaction_number, $transaction_status, $pbs_status, $amount, $text = '', $id = 0)
    {
        $this->checkCredentials($credentials);

        $id = $this->processRequestData($id);
        $onlinepayment = $this->onlinePaymentFactory(intval($id));
        if ($onlinepayment->get('id') != $id) {
            throw new XML_RPC2_FaultException('The given payment id '.$id.' is not valid', -4);
        }

        $identifier_key = $this->processRequestData($identifier_key);
        $debtor = $this->debtorFactory($identifier_key);
        if ($debtor->get('id') == 0) {
            throw new XML_RPC2_FaultException('No valid debtor was found from the identifier key when trying to save onlinepayment', -4);
        }

        $values['belong_to'] = $debtor->get('type');
        $values['belong_to_id'] = $debtor->get('id');
        $values['transaction_number'] = $this->processRequestData($transaction_number);
        $values['transaction_status'] = $this->processRequestData($transaction_status);
        $values['amount'] = number_format($this->processRequestData($amount), 2, ',', '');
        $values['text'] = $this->processRequestData($text);
        $values['pbs_status'] = $this->processRequestData($pbs_status);

        if (!$payment_id = $onlinepayment->save($values)) {
            // this is probably a little to hard reaction.
            throw new XML_RPC2_FaultException('Onlinebetaling kunne ikke blive gemt ' . strtolower(implode(', ', $onlinepayment->error->getMessage())), -4);
        }

        if ($onlinepayment->get('status') == 'authorized') {
            $this->sendEmailOnOnlinePayment($debtor, $payment_id);
        }

        return $this->prepareResponseData($payment_id);
    }

    private function sendEmailOnOnlinePayment($debtor, $payment_id)
    {
        $this->kernel->useShared('email');
        $email = new Email($this->kernel);

        $subject = 'Payment confirmation / betalingsbekræftelse (#' . $payment_id . ')';
        $body    = 'We have received your payment for order #' .$debtor->getId(). '.' . "\n\n";
        $body   .= 'Vi har modtaget din betaling for ordre #' .$debtor->getId(). '.';
        $body   .= "\n\nYours sincerely / Venlig hilsen\n".  $this->kernel->intranet->address->get('name');

        $data = array('contact_id' => $debtor->getContact()->getId(),
                      'subject'    => $subject,
                      'body'       => $body,
                      'from_email' => $this->kernel->intranet->address->get('email'),
                      'from_name'  => $this->kernel->intranet->address->get('name'),
                      'type_id'    => 13, // onlinepayment
                      'belong_to'  => $payment_id);

        if (!$email->save($data)) {
            throw new Exception('Could not save email to onlinepayment');
            ;
            return false;
        }

        if (!$email->queue()) {
            $this->error->merge($email->error->getMessage());
            throw new Exception('Could not send email to ' . $debtor->getContact()->getId());
            ;
            return false;
        }

        return true;
    }



    /**
     * Returns an onlinepayment id to be processed to the id can be used in payment
     *
     * @param struct $credentials Credentials to use the server
     *
     * @return integer $payment_id
     */
    public function createOnlinePayment($credentials)
    {
        $this->checkCredentials($credentials);
        $onlinepayment = $this->onlinePaymentFactory();

        if (!$payment_id = $onlinepayment->create()) {
            // this is probably a little to hard reaction
            throw new XML_RPC2_FaultException('onlinepayment could not be created' . strtolower(implode(', ', $onlinepayment->error->getMessage())), -4);
        }

        return $this->prepareResponseData($payment_id);
    }

    /**
     * Initialize the webshop
     *
     * @return void
     */
    private function onlinePaymentFactory($id = 0)
    {

        if (!$this->kernel->intranet->hasModuleAccess('onlinepayment')) {
            throw new XML_RPC2_FaultException('The intranet did not have access to OnlinePayment', -4);
        }

        /**
         * This is needed to load the modules settings.
         */
        $this->kernel->useModule('onlinepayment');

        require_once 'Intraface/modules/onlinepayment/OnlinePayment.php';
        if (!empty($id)) {
            return OnlinePayment::factory($this->kernel, 'id', intval($id));
        } else {
            return OnlinePayment::factory($this->kernel);
        }
    }

    /**
     * Initialize Debtor
     *
     * @param string $identifier_key debtor identifier key
     * @return object Debtor
     */
    private function debtorFactory($identifier_key)
    {
        if (!$this->kernel->intranet->hasModuleAccess('debtor')) {
            throw new XML_RPC2_FaultException('The intranet did not have access to Debtor', -4);
        }

        require_once 'Intraface/modules/debtor/Debtor.php';
        return Debtor::factory($this->kernel, $identifier_key);
    }
}
