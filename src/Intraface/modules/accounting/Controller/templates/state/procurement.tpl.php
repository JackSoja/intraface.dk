<h1>Bogf�r indk�b #<?php e($procurement->get('number')); ?></h1>

<ul class="options">
    <li><a href="view.php?id=<?php e($procurement->get("id")); ?>">Luk</a></li>
    <li><a href="index.php?id=<?php e($procurement->get("id")); ?>&amp;use_stored=true">Tilbage til indk�bslisten</a></li>
</ul>

<div class="message">
    <p>Du bedes manuelt kontrollere at indk�bet bliver bogf�rt korrekt.</p>
</div>

<?php if (!$year->readyForState($procurement->get('paid_date'))): ?>
    <?php echo $year->error->view(); ?>
    <p>G� til <a href="<?php e($accounting_module->getPath().'years.php'); ?>">regnskabet</a></p>

<?php else: ?>

    <?php echo $procurement->error->view(); ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    <input type="hidden" value="<?php e($value['id']); ?>" name="id" />

    <fieldset>
        <legend><?php e(t('procurement')); ?></legend>
        <table>
            <tr>
                <th><?php e(__("number")); ?></th>
                <td><?php e($procurement->get("number")); ?></td>
            </tr>
            <tr>
                <th><?php e(t('description')) ?></th>
                <td><?php autohtml($procurement->get("description")); ?></td>
            </tr>
            <tr>
                <th><?php e(t('date recieved')) ?></th>
                <td><?php e($procurement->get("dk_date_recieved")); ?></td>
            </tr>
            <?php
            /*
            <tr>
                 <th><?php e(t('price for items')) ?></th>
                <td><?php e($procurement->get("dk_price_items")); ?> kroner</td>
            </tr>
            <tr>
                <th><?php e(t('price for shipment etc')) ?></th>
                <td><?php e($procurement->get("dk_price_shipment_etc")); ?> kroner</td>
            </tr>

            <tr>
                <th><?php e(t('vat')) ?></th>
                <td><?php e($procurement->get("dk_vat")); ?> kroner</td>
            </tr>
            */
            ?>
            <tr>
                <th><?php e(t('total price')) ?></th>
                <td><?php e($procurement->get("dk_total_price")); ?> kroner</td>
            </tr>
            <tr>
                <td><?php e(t('buy from')) ?></td>
                <td><?php e(__($procurement->get('from_region'), 'procurement')); ?>
            </td>
        </tr>

        </table>
    </fieldset>



    <fieldset>
        <legend>Oplysninger der bogf�res</legend>

        <div class="formrow">
            <label for="voucher_number"><?php e(t('voucher number')) ?></label>
            <input type="text" name="voucher_number" id="voucher_number" value="<?php e($voucher->getMaxNumber() + 1); ?>" />
        </div>

        <div class="formrow">
            <label for="voucher_date"><?php e(t('state on date')) ?></label>
            <input type="text" name="voucher_date" id="voucher_date" value="<?php e($procurement->get("dk_paid_date")); ?>" />
        </div>

        <div class="formrow">
            <label for="credit_account_id"><?php e(t('paid from account')) ?></label>
            <select name="credit_account_number">
                <option value="">V�lg</option>
                <?php
                $account = new Account($year);
                $accounts = $account->getList('finance');
                foreach ($accounts as $account):
                    if ($year->getSetting('debtor_account_id') == $account['id']) continue;
                    ?>
                    <option value="<?php e($account['number']); ?>"
                    <?php if (isset($value['credit_account_number']) && $account['number'] == $value['credit_account_number']) echo ' selected="selected"'; ?>
                    ><?php e($account['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </fieldset>

    <table class="stripe">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Beskrivelse</th>
                <th>Bel�b</th>
                <th>Bogf�res p�</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($value['debet_account']) && is_array($value['debet_account'])) {

                $account = new Account($year);

                foreach ($value['debet_account'] AS $key => $line) {
                    ?>
                    <tr>
                        <td><?php e($key+1); ?></td>
                        <td><?php e($procurement->get('description')); ?> - <input type="text" name="debet_account[<?php e($key); ?>][text]" value="<?php e($line["text"]); ?>" /></td>
                        <td><input type="text" name="debet_account[<?php e($key); ?>][amount]" value="<?php e($line["amount"]); ?>" size="8" /> <?php e('('.t('excl. vat').')'); ?></td>
                        <td>
                            <?php
                            $accounts =  $account->getList('expenses');
                            ?>
                            <select id="state_account" name="debet_account[<?php e($key); ?>][state_account_id]">
                                <option value="">V�lg...</option>
                                <?php
                                foreach ($accounts AS $a):
                                    if (strtolower($a['type']) == 'sum') continue;
                                    if (strtolower($a['type']) == 'headline') continue;
                                    ?>
                                    <option value="<?php e($a['number']); ?>"
                                    <?php if (isset($line['state_account_id']) && $line['state_account_id'] == $a['number']) echo ' selected="selected"'; ?>
                                    ><?php e($a['name'].' ('.t('vat', 'accounting').': '.t($a['vat_shorthand'], 'accounting').')'); ?></option>

                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="submit" name="remove_line[<?php e($key); ?>]" value="<?php e(t('remove')); ?>" /></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <?php if ($procurement->get('vat') > 0): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td><?php e($procurement->get('description'). ' - '.t('vat')); ?></td>
                    <td><?php e($procurement->get('dk_vat')); ?></td>
                    <td>
                        <?php
                        $account = new Account($year, $year->getSetting('vat_in_account_id'));
                        e($account->get('number') . ' ' . $account->get('name'));
                        ?>
                    </td>
                    <td>&nbsp;</td>
                 </tr>
             <?php endif; ?>

        </tbody>
    </table>
    <div>
        <input type="submit" name="add_line" value="<?php e(t('add line')); ?>" />
    </div>

    <div>
         <input type="submit" name="state" value="<?php e(t('state')); ?>" /> eller
         <a href="view.php?id=<?php e($value['id']); ?>">fortryd</a>
    </div>

</form>

<?php endif; ?>
