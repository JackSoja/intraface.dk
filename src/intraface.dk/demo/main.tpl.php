<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da">
    <head>
        <title><?php e($context->document()->title()); ?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="alternate" type="application/rss+xml" title="Alle produkter" href="<?php e(url('/demo/shop/rss.php')); ?>" />

        <style type="text/css">
        <?php foreach ($context->document()->styles() as $style) : ?>
            @import "<?php e($style); ?>";
        <?php endforeach;?>
        </style>
     </head>

     <body>
        <div id="container">
            <div id="header"><?php e($context->document()->companyName()); ?></div>

            <div id="menu">
                <?php if ($context->document()->menu()) {
                    echo $context->document()->menu();
} ?>
            </div>
            <div id="content">
                <?php echo $content; ?>
            </div>
            <div style="clear:both; text-align: center">Intraface demo</div>
        </div>
    </body>
</html>
