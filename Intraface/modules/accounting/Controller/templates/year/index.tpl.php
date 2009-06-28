<h1>Regnskabs�r</h1>

<div class="message">
    <p><strong>Regnskabs�r</strong>. P� denne side kan du enten oprette et nyt regnskab eller v�lge hvilket regnskab, du vil begynde at indtaste poster i. Du v�lger regnskabet p� listen nedenunder.</p>
</div>

<ul class="options">
    <li><a class="new" href="<?php e(url('create')); ?>">Opret regnskabs�r</a></li>
</ul>

<?php if (!$context->getYearGateway()->getList()): ?>
    <p>Der er ikke oprettet nogen regnskabs�r. Du kan oprette et ved at klikke p� knappen ovenover.</p>
<?php else: ?>
    <form action="<?php e(url('./')); ?>" method="post">
    <table>
        <caption>Regnskabs�r</caption>
        <thead>
            <tr>
                <th></th>
                <th>�r</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($context->getYearGateway()->getList() as $y): ?>
        <tr>
            <td><input type="radio" name="id" value="<?php e($y['id']); ?>" <?php if ($context->getYear()->loadActiveYear() == $y['id']) { echo ' checked="checked"'; } ?>/></td>
            <td><a href="<?php e($context->url($y['id'])); ?>"><?php e($y['label']); ?></a></td>
            <td class="options">
                <a class="edit" href="<?php e($context->url($y['id'] . '/edit')); ?>">Ret</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" value="V�lg" />
    </form>
<?php endif; ?>