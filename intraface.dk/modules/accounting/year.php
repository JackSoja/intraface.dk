<?php
require('../../include_first.php');

$module = $kernel->module('accounting');
$translation = $kernel->getTranslation('accounting');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['start']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        $year = new Year($kernel, $_POST['id']);
        $year->setYear();
        header('Location: daybook.php');
        exit;
    }
    if (!empty($_POST['primobalance']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        $year = new Year($kernel, $_POST['id']);
        $year->setYear();
        header('Location: primosaldo.php');
        exit;
    }
    elseif (!empty($_POST['manual_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        $year = new Year($kernel, $_POST['id']);
        $year->setYear();
        header('Location: accounts.php');
        exit;
    }
    elseif (!empty($_POST['standard_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {

        $year = new Year($kernel, $_POST['id']);
        $year->setYear();
        if (!$year->createAccounts('standard')) {
            trigger_error('Kunne ikke oprette standardkontoplanen', E_USER_ERROR);
        }

        $values = $year->get();

    }
    elseif (!empty($_POST['transfer_accountplan']) AND !empty($_POST['id']) AND is_numeric($_POST['id'])) {
        // kontoplanen fra sidste �r hentes
        $year = new Year($kernel, $_POST['id']);
        $year->setYear();
        if (empty($_POST['accountplan_year']) OR !is_numeric($_POST['accountplan_year'])) {
            $year->error->set('Du kan ikke oprette kontoplanen, for du har ikke valgt et �r at g�re det fra');
        }
        else {
            if (!$year->createAccounts('transfer_from_last_year', $_POST['accountplan_year'])) {
                trigger_error('Kunne ikke oprette standardkontoplanen', E_USER_ERROR);
            }
        }
        $values = $year->get();
    }
}
elseif (!empty($_GET['id']) AND is_numeric($_GET['id'])) {
    $year = new Year($kernel, (int)$_GET['id']);
    $values = $year->get();
}
else {
    $year = new Year($kernel);
    $values = $year->get();
}

if (!$year->isValid()) {
    trigger_error('�ret er ikke gyldigt', E_USER_ERROR);
}

$years = $year->getList();
$account = new Account($year);
$vat_period = new VatPeriod($year);

$page = new Intraface_Page($kernel);
$page->start('Regnskab');
?>
<h1>Regnskab <?php echo $year->get('label'); ?></h1>

<ul class="options">
    <li><a class="edit" href="year_edit.php?id=<?php echo $year->get('id'); ?>">Ret</a></li>
    <li><a class="setting" href="setting.php">Indstillinger</a></li>
</ul>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

    <?php echo $year->error->view(); ?>

    <input type="hidden" name="id" value="<?php echo $values['id']; ?>" />
<?php if ($account->anyAccounts()): ?>
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
                    echo $last_year->get('label');
                }
                else {
                    echo 'Ingen';
                }
            ?>
        </td>
    </tr>
    <tr>
        <th>L�st</th>
        <td>
            <?php
                if (!empty($values['locked']) AND $values['locked'] == 1) {
                    echo 'Ja';
                }
                else {
                    echo 'Nej';
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

<?php if (!$account->anyAccounts()): ?>
    <fieldset>
        <legend>Kontoplan</legend>
        <p>Du skal oprette en kontoplan for �ret. Du kan f�rst begynde at gemme poster i kassekladden, n�r du har oprettet en kontoplan.</p>

        <div>
            <input type="submit" name="manual_accountplan" value="Jeg vil oprette kontoplanen manuelt" class="confirm" />
        </div>

        <div>
            <input type="submit" name="standard_accountplan" value="Jeg vil bruge standardkontoplanen" class="confirm" />
        </div>
        <?php if (count($years) - 1 > 0): // der skal tr�kkes en fra, for man kan ikke oprette kontoplaner fra sig selv ?>
        <div>
            <label for="accountplan_years">Jeg vil overf�re kontoplanen fra</label>
            <select name="accountplan_year" id="accountplan_years">
                <option value="">V�lg...</option>
                <?php
                    foreach ($years AS $y) {
                        if ($y['id'] == $year->get('id')) continue;
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
    <p>Du kan finde dine konti under <a href="accounts.php">kontoplanen</a>.</p>

    <?php if (!$year->vatAccountIsSet()): ?>

        <p class="message-dependent">Du mangler at s�tte nogle indstillinger. <a href="setting.php">S�t indstillingerne</a>.</p>

    <?php elseif (!$vat_period->periodsCreated()): ?>
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

<?php
$page->end();
?>