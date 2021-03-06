<h1>Primosaldo <?php e($year->get('label')); ?></h1>

<ul class="options">
    <li><a href="<?php e(url('../')); ?>"><?php e(t('Close')); ?></a></li>
    <li><a class="edit" href="<?php e(url(null, array('edit'))); ?>"><?php e(t('Edit')); ?></a></li>
</ul>

<table>
<caption>Primosaldo for statuskonti</caption>
<thead>
    <tr>
        <th>Kontonummer</th>
        <th>Kontonavn</th>
        <th>Debet</th>
        <th>Credit</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($accounts as $account) : ?>
    <tr>
        <td><?php e($account['number']); ?></td>
        <td><?php e($account['name']); ?></td>
        <td><?php e(amountToOutput($account['primosaldo_debet'])); ?></td>
        <td><?php e(amountToOutput($account['primosaldo_credit'])); ?></td>
    </tr>
    <?php
        // udregninger
        $total_debet += $account['primosaldo_debet'];
        $total_credit += $account['primosaldo_credit'];
    ?>

    <?php endforeach; ?>
    <tr>
        <td></td>
        <td>
            <strong>Balance</strong>
            <?php
            if ($total_debet != $total_credit) {
                echo '<strong class="warning">Balancen stemmer ikke</strong>';
            }
            ?>
        </td>
        <td><strong><?php e(amountToOutput($total_debet)); ?></strong></td>
        <td><strong><?php e(amountToOutput($total_credit)); ?></strong></td>
    </tr>
</tbody>
</table>

<?php if ($year->get('last_year_id') > 0) : ?>

<form method="post" action="<?php e(url()); ?>">
    <input type="hidden" name="id" value="<?php e($year->get('id')); ?>" />
    <fieldset>
        <legend>Oplysninger til primosaldo</legend>

        <p>Du kan hente primobalancen fra sidste års regnskab. Du skal bare være opmærksom på, at tallene i din nuværende primobalance overskrives - og at handlingen ikke kan fortrydes.</p>
        <div>
            <input type="submit" name="get_last_year" value="Hent saldoen fra sidste års regnskab" onclick="return confirm('Vær opmærksom på at denne funktion stadig er under udvikling, og sikkert ikke virker helt efter hensigten. \n\nEr du sikker på, at du vil opdatere din primobalance? Handlingen kan ikke fortrydes!')" />
        </div>

    </fieldset>
</form>

<?php endif; ?>