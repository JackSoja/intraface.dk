<?php
class Intraface_modules_newsletter_SubscribersGateway
{
    protected $dbquery;

    function getDBQuery()
    {
        return $this->dbquery;
    }

    function getAllUnsubscribersForList($list)
    {
        $this->dbquery = new Intraface_DBQuery($list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $list->get('id') . " AND newsletter_subscriber.intranet_id = " . $list->kernel->intranet->get('id'));
        $this->dbquery->setFilter('optin', 1);
        $this->dbquery->setFilter('active', 0);
        $this->dbquery->setSorting('date_submitted DESC');
        $this->getDBQuery()->setCondition('newsletter_subscriber.optin = '.$this->getDBQuery()->getFilter('optin'));
        $this->getDBQuery()->setCondition('newsletter_subscriber.active = '.$this->getDBQuery()->getFilter('active'));

        return $this->getDBQuery()->getRecordset("id, date_unsubscribe, contact_id, DATE_FORMAT(date_unsubscribe, '%d-%m-%Y %H-%i-%s') AS dk_date_unsubscribe", "", false);
    }

    function getByContactId($list, $id)
    {
        $this->dbquery = new Intraface_DBQuery($list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $list->get('id') . " AND newsletter_subscriber.intranet_id = " . $list->kernel->intranet->get('id'));
        $this->dbquery->setFilter('contact_id', $id);
        $this->getDBQuery()->setCondition('newsletter_subscriber.contact_id = '.$this->getDBQuery()->getFilter('contact_id'));

        return $this->getDBQuery()->getRecordset("id, date_unsubscribe, contact_id, DATE_FORMAT(date_unsubscribe, '%d-%m-%Y %H-%i-%s') AS dk_date_unsubscribe", "", false);
    }

    function setNewContactId($old_id, $new_id)
    {
        // @todo - make sure to delete old ones
        $db = MDB2::singleton();
        $db->query('UPDATE newsletter_subscriber SET contact_id = ' . $new_id . ' WHERE contact_id = ' . $old_id);
    }
}
