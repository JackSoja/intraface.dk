<?php
$values = $context->getYear()->get();
?>

<h1>Regnskab <?php e($context->getYear()->get('label')); ?></h1>

<ul class="options">
    <li><a class="edit" href="<?php e(url('edit')); ?>">Ret</a></li>
    <li><a class="setting" href="<?php e('../settings'); ?>">Indstillinger</a></li>
</ul>

<form action="<?php e(url('./')); ?>" method="post">

    <?php echo $context->getYear()->error->view(); ?>

    <input type="hidden" name="id" value="<?php e($values['id']); ?>" />
<?php if ($context->getAccount()->anyAccounts()): ?>
    <fieldset>
        <legend>V�lg og g� til regnskabet</legend>
        <div>
            <input type="submit" name="start" id="start" value="V�lg og g� til regnskabet" />
        </div>
    </fieldset>
<?php endif; ?>
<table>
    <caption>Oplysninger om regnskabs�ret</caption>
    <tr>
        <th>Navn</th>
        <td><?php echo htmlentities($values['label']); ?></td>
    </tr>
    <tr>
        <th>Fra dato</th>
        <td><?php echo htmlentities($values['from_date_dk']); ?></td>
    </tr>
    <tr>
        <th>Til dato</th>
        <td><?php echo htmlentities($values['to_date_dk']); ?></td>
    </tr>
    <tr>
        <th>Sidste �rs regnskab</th>
        <td>
            <?php
                if (!empty($values['last_year_id']) AND $values['last_year_id'] > 0) {
                    $last_year = new Year($kernel, $values['last_year_id']);
                    e($last_year->get('label'));
                } else {
                    e('Ingen');
                }
            ?>
        </td>
    </tr>
    <tr>
        <th>L�st</th>
        <td>
            <?php
                if (!empty($values['locked']) AND $values['locked'] == 1) {
                    e('Ja');
                } else {
                    e('Nej');
                }
            ?>

        </td>
    </tr>
    <tr>
        <th>Moms</th>
        <td>
            <?php
                if (!empty($values['vat']) AND $values['vat'] == 1) {
                    echo 'Ja';
                }
                else {
                    echo 'Nej';
                }
            ?>
        </td>
    </tr>
</table>

<?php if (!$context->getAccount()->anyAccounts()): ?>
    <fieldset>
        <legend>Kontoplan</legend>
        <p>Du skal oprette en kontoplan for �ret. Du kan f�rst begynde at gemme poster i kassekladden, n�r du har oprettet en kontoplan.</p>

        <div>
            <input type="submit" name="manual_accountplan" value="Jeg vil oprette kontoplanen manuelt" class="confirm" />
        </div>

        <div>
            <input type="submit" name="standard_accountplan" value="Jeg vil bruge standardkontoplanen" class="confirm" />
        </div>
        <?php if (count($context->getYearGateway()->getList()) - 1 > 0): // der skal tr�kkes en fra, for man kan ikke oprette kontoplaner fra sig selv ?>
        <div>
            <label for="accountplan_years">Jeg vil overf�re kontoplanen fra</label>
            <select name="accountplan_year" id="accountplan_years">
                <option value="">V�lg...</option>
                <?php
                    foreach ($context->getYearGateway()->getList() as $y) {
                        if ($y['id'] == $context->getYear()->get('id')) continue;
                        echo '<option ';
                        if (!empty($values['accountplan_years']) AND $values['accountplan_years'] == $y['id']) echo ' selected="selected"';
                        echo 'value="'.$y['id'].'">'.$y['label'].'</option>';
                    }
                ?>
            </select>
            <input type="submit" name="transfer_accountplan" value="Hent" class="confirm" />
        </div>
        <?php endif; ?>
    </fieldset>
<?php else: ?>
    <h2>Kontoplan</h2>
    <p>Du kan finde dine konti under <a href="<?php e(url('../accounts')); ?>">kontoplanen</a>.</p>

    <?php if (!$context->getYear()->vatAccountIsSet()): ?>

        <p class="message-dependent">Du mangler at s�tte nogle indstillinger. <a href="<?php (url('../settings')); ?>">S�t indstillingerne</a>.</p>

    <?php elseif (!$context->getVatPeriod()->periodsCreated()): ?>
        <!--
        <p class="message-dependent">Du skal oprette momsperioder for �ret. <a href="vat_period.php">Opret momsperioder</a>.</p>
        -->
    <?php endif; ?>

    <h2>Primobalance</h2>
    <fieldset>
    <legend>Primobalance</legend>
    <p>P� primobalancen kan du s�tte de bel�b, dit regnskab starter med. Listen v�lger automatisk alle dine statuskonti fra kontoplanen. Statuskonti er de konti, som ikke nulstilles ved �rets udl�b.</p>
    <div>
        <input type="submit" name="primobalance" id="primobalance" value="Rediger primobalancen" />
    </div>
    </fieldset>
<?php endif; ?>
</form>
