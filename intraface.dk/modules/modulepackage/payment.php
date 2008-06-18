<?php
require('../../include_first.php');
$module = $kernel->module('modulepackage');
$module->includeFile('Action.php');
$module->includeFile('ActionStore.php');

$translation = $kernel->getTranslation('modulepackage');

$action_store = new Intraface_ModulePackage_ActionStore($kernel->intranet->get('id'));
$action = $action_store->restore((int)$_GET['action_store_id']);
        
if(!is_object($action)) {
    trigger_error("Problem restoring action from order_id ".$_GET['action_store_id'], E_USER_ERROR);
    exit;
}

$page = new Intraface_Page($kernel);
$page->start(safeToHtml($translation->get('you are now ready to pay your order')));
?>
<h1><?php echo safeToHtml($translation->get('you are now ready to pay your order')); ?></h1>

<?php if(!empty($_GET['payment_error'])): ?>
    <div class="message">
        <?php echo safeToHtml($translation->get('an error occured under your online payment. Please try again. If this keeps happening, feel free to contact us.')); ?>
    </div>
<?php endif; ?>   


<p><?php echo safeToHtml($translation->get('we have registered your order, and you are ready to pay for it.')); ?></p>

<p><strong><?php echo safeToHtml($translation->get('your payment is')); ?> DKK <?php echo safeToHtml($action->getTotalPrice()); ?></strong></p>

<p><?php echo safeToHtml($translation->get('you have 2 options:')); ?></p>

<ul style="padding-left: 30px; list-style: square outside url(/images/icons/silk/accept.png);">
    <li><h2><?php echo safeToHtml($translation->get('pay online')); ?></h2>
        <p><?php echo safeToHtml($translation->get('you can choose to pay the order with creditcard. this will process your order instantly.')); ?></p>
        <p><?php echo safeToHtml($translation->get('the paymend is carried out on a secure connection.')); ?></p>
        
        <?php
        $payment_html = new Ilib_Payment_Html(INTRAFACE_ONLINEPAYMENT_PROVIDER, INTRAFACE_ONLINEPAYMENT_MERCHANT, INTRAFACE_ONLINEPAYMENT_MD5SECRET, session_id());
        $payment_prepare = $payment_html->getPrepare();
        $lang = $translation->getLang(); 
        $language = (isset($lang) && $lang == 'dansk') ? 'da' : 'en';
        $payment_prepare->setPaymentValues(
            $action->getOrderId(), 
            $action->getTotalPrice(), 
            "DKK",
            $language,
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'modules/modulepackage/index.php?status=success',
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'modules/modulepackage/payment.php?action_store_id='.$action_store->getId().'&payment_error=true',
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'modules/modulepackage/process.php',
            NET_SCHEME.NET_HOST.NET_DIRECTORY.'payment/html/cci.php?language='.$language);
        
        $optional = array(
            'action_store_id' => $action_store->getId(),
            'intranet_public_key' => $kernel->intranet->get('public_key')
            );
        
        $payment_prepare->setOptionalValues($optional);
        
        $post_destination = $payment_prepare->getPostDestination();
        // If it is not a complete path (containing slashes) it is a fake server and we add index.php to make the post variable get posted to the page.
        if(!strpos($post_destination, '/')) $post_destination .= '/index.php';
        
        ?>
        <form action="<?php echo safeToHtml($post_destination); ?>" method="POST"> <!-- https://secure.quickpay.dk/quickpay.php -->
        
        <?php echo $payment_prepare->getPostFields(); ?>
        
        <input type="submit" name="submit" value="<?php echo safeToHtml($translation->get('pay the order now')); ?>" />
        
       </form>
    </li>
    <li><h2><?php echo safeToHtml($translation->get('pay by bank transfer')); ?></h2>
        <p><?php echo safeToHtml($translation->get('you can choose to pay the order by bank transfer.')); ?></p>
        <p><?php echo safeToHtml($translation->get('please notice that your order will first be processed when we have recieved your payment.')); ?></p>
        <p><?php echo safeToHtml($translation->get('as we have already registered your order, you do not need to do anymore for now. you will recieve the payment information on your e-mail with the order confirmation.')); ?></p>
        <p><a href=""><?php echo safeToHtml($translation->get('back to the frontpage')); ?></a></p>
    </li>
</ul>

<p><?php echo safeToHtml($translation->get('if you have any problems or questions, do not hesitate to contact us.')); ?> <a href="mailto:support@intraface.dk">support@intraface.dk</a></p>

<?php
$page->end();
?>