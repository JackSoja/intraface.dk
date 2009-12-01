<h1><?php e(t('Procurement')); if (!empty($contact) AND is_object($contact)) e(": ".$contact->address->get('name')); ?></h1>

<ul class="options">
    <li><a class="new" href="<?php e(url(NULL, array('create'))); ?>"><?php e(t('Create procurement')); ?></a></li>
    <?php if (!empty($contact) AND is_object($contact)): ?>
        <li><a href="<?php e(t('../')); ?>"><?php e(t('Go to contact')); ?></a></li>
    <?php endif; ?>
</ul>

<?php if (!$gateway->any()): ?>
    <p><?php e(t('No procurements has been created')); ?>. <a href="<?php e(url(NULL, array('create'))); ?>"><?php e(t('Create procurement')); ?></a>.</p>
<?php else: ?>

    <form method="get" action="<?php e(url()); ?>">
    <fieldset>
        <legend><?php e(t('Search', 'common')); ?></legend>
        <label><?php e(t('Text', 'common')); ?>
            <input type="text" name="text" value="<?php e($gateway->dbquery->getFilter("text")); ?>" />
        </label>
        <label><?php e(t('Status', 'common')); ?>
        <select name="status">
            <?php 
            $status_list = array(
                '-1' => 'All',
                '-2' => 'Open',
                '0' => 'Created',
                '1' => 'Recieved',
                '3' => 'Canceled'
            );
            ?>
            <?php foreach($status_list AS $status => $text): ?>
                <option value="<?php e($status); ?>" <?php if ($gateway->dbquery->getFilter("status") == $status) echo ' selected="selected"';?>><?php e(t($text, 'common'))?></option>
            <?php endforeach; ?>
         </select>
        </label>
        <label><?php e(t('From date')); ?>
            <input type="text" name="from_date" id="date-from" value="<?php e($gateway->dbquery->getFilter("from_date")); ?>" /> <span id="calender"></span>
        </label>
        <label><?php e(t('To date')); ?>
            <input type="text" name="to_date" value="<?php e($gateway->dbquery->getFilter("to_date")); ?>" />
        </label>
        <span>
        <input type="submit" name="search" value="<?php e(t('Find', 'common')); ?>" />
        </span>
    </fieldset>
    </form>

    <table class="stripe">
        <caption><?php e(t('Procurement')); ?></caption>
        <thead>
            <tr>
                <th><?php e(t('No.')); ?></th>
                <th><?php e(t('Description')); ?></th>
                <th><?php e(t('From')); ?></th>
                <th><?php e(t('Invoice date')); ?></th>
                <th><?php e(t('Delivery date')); ?></th>
                <th><?php e(t('Payment date')); ?></th>
                <th><?php e(t('Price')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($procurements as $procurement): ?>
                <tr>
                    <td><?php e($procurement["number"]); ?></td>
                    <td><a href="<?php e(url($procurement["id"])); ?>"><?php e($procurement["description"]); ?></a></td>
                    <td>
                        <?php if ($context->getKernel()->user->hasModuleAccess('contact') && $procurement["contact_id"] != 0): ?>
                            <?php $ModuleContact = $context->getKernel()->getModule('contact'); ?>
                            <a href="<?php e($ModuleContact->getPath()."contact.php?id=".$procurement["contact_id"]); ?>"><?php e($procurement["contact"]); ?></a>
                        <?php else: ?>
                            <?php e($procurement["vendor"]); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php e($procurement["dk_invoice_date"]); ?></td>
                    <td>
                        <?php if ($procurement["status"] == "recieved" || $procurement["status"] == "canceled"): ?>
                            <?php e(t(ucfirst($procurement["status"]))); ?>
                        <?php elseif ($procurement["delivery_date"] != "0000-00-00"): ?>
                            <?php e($procurement["dk_delivery_date"]); ?>
                        <?php else: ?>
                            <?php e(t('Not given')); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($procurement["status"] == "canceled"): ?>
                            <?php e("-"); ?>
                        <?php elseif ($procurement['paid_date'] != '0000-00-00'): ?>
                            <?php e(t('Paid')); ?>
                        <?php elseif ($procurement["payment_date"] != "0000-00-00"): ?>
                            <?php e($procurement["dk_payment_date"]); ?>
                        <?php else: ?>
                            <?php e(t('Not given')); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php e(number_format($procurement["total_price"], 2, ',', '.')); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php echo $gateway->dbquery->display('paging'); ?>

<?php endif; ?>