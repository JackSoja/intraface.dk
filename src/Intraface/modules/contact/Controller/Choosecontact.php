<?php
class Intraface_modules_contact_Controller_Choosecontact extends k_Component
{
    protected $contact;

    function renderHtml()
    {
        $module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        /*
        $redirect = $this->getRedirect();

        if (!empty($_GET['add'])) {
        	$add_redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
        	$url = $add_redirect->setDestination($module->getPath()."contact_edit.php", NET_SCHEME . NET_HOST . $this->url(null, array($redirect->get('redirect_query_string'))));
        	$add_redirect->askParameter("contact_id");
        	//$add_redirect->setParameter("selected_contact_id", intval($_GET['add']));
        	return new k_SeeOther($url);
        } elseif (!empty($_GET['return_redirect_id'])) {
            $return_redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($return_redirect->getParameter('contact_id') != 0) {
                $redirect->setParameter('contact_id', $return_redirect->getParameter('contact_id'));
                return new k_SeeOther($redirect->getRedirect($this->url('../')));
            }
        }
        */

        $smarty = new k_Template(dirname(__FILE__) . '/templates/choosecontact.tpl.php');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getContact()
    {
        if (is_object($this->contact)) return $this->contact;
        return $this->contact = new Contact($this->getKernel());
    }

    function getContacts()
    {
        $contact = $this->getContact();
        if (isset($_GET['contact_id'])) {
        	$contact->getDBQuery()->setCondition("contact.id = ".intval($_GET['contact_id']));
        } elseif (isset($_GET['query']) || isset($_GET['keyword_id'])) {

        	if (isset($_GET['query'])) {
        		$contact->getDBQuery()->setFilter('search', $_GET['query']);
        	}

        	if (isset($_GET['keyword_id'])) {
        		$contact->getDBQuery()->setKeyword($_GET['keyword_id']);
        	}
        } else {
        	$contact->getDBQuery()->useCharacter();
        }

        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->usePaging('paging');
        $contact->getDBQuery()->storeResult('use_stored', 'select_contact', 'sublevel');

        if (isset($_GET['contact_id']) && intval($_GET['contact_id']) != 0) {
        	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['contact_id']));
        } elseif (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0) {
        	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['last_contact_id']));
        }

        return $contacts = $contact->getList();
    }

    function getRedirect()
    {
        return $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function getUsedKeywords()
    {
        $keywords = $this->getContact()->getKeywordAppender();
        return $used_keywords = $keywords->getUsedKeywords();
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_contact_Controller_Show';
        }
    }

    function getRedirectUrl($contact_id = 0)
    {
        return $this->context->getReturnUrl($contact_id);
    }

    function postForm()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if (!empty($_POST['eniro']) AND !empty($_POST['eniro_phone'])) {
            $contact = $this->getContact();

            $eniro = new Services_Eniro();
            $value = $_POST;

            if ($oplysninger = $eniro->query('telefon', $_POST['eniro_phone'])) {
                // skal kun bruges s� l�nge vi ikke er utf8
                // $oplysninger = array_map('utf8_decode', $oplysninger);
                $address['name'] = $oplysninger['navn'];
                $address['address'] = $oplysninger['adresse'];
                $address['postcode'] = $oplysninger['postnr'];
                $address['city'] = $oplysninger['postby'];
                $address['phone'] = $_POST['eniro_phone'];
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // for a new contact we want to check if similar contacts alreade exists
            if (empty($_POST['id'])) {
                $contact = $this->getContact();
                if (!empty($_POST['phone'])) {
                    $contact->getDBQuery()->setCondition("address.phone = '".$_POST['phone']."' AND address.phone <> ''");
                    $similar_contacts = $contact->getList();
                }

            } else {
                $contact = new Contact($this->getKernel(), $_POST['id']);
            }

            // checking if similiar contacts exists
            if (!empty($similar_contacts) AND count($similar_contacts) > 0 AND empty($_POST['force_save'])) {
            } elseif ($id = $contact->save($_POST)) {

                // $redirect->addQueryString('contact_id='.$id);
                if ($redirect->get('id') != 0) {
                    $redirect->setParameter('contact_id', $id);
                }
                return new k_SeeOther($this->url('../', array('contact_id' => $id)));

                //$contact->lock->unlock_post($id);
            }

            $value = $_POST;
            $address = $_POST;
            $delivery_address = array();
            $delivery_address['name'] = $_POST['delivery_name'];
            $delivery_address['address'] = $_POST['delivery_address'];
            $delivery_address['postcode'] = $_POST['delivery_postcode'];
            $delivery_address['city'] = $_POST['delivery_city'];
            $delivery_address['country'] = $_POST['delivery_country'];
        }

        return $this->render();
    }

    function getContactModule()
    {
        return $this->getKernel()->module("contact");
    }

    function getValues()
    {
        if ($this->body()) return $this->body();
        return array('number' => $this->getContact()->getMaxNumber() + 1);
    }

    function getAddressValues()
    {
        if ($this->body()) return $this->body();
        return array();
    }

    function getDeliveryAddressValues()
    {
        if ($this->body()) return $this->body();
        return array();
    }

    function renderHtmlCreate()
    {
        $contact_module = $this->getKernel()->module("contact");
        $translation = $this->getKernel()->getTranslation('contact');
        $contact_module->includeFile('ContactReminder.php');

        $smarty = new k_Template(dirname(__FILE__) . '/templates/edit.tpl.php');
        return $smarty->render($this);

    }

    function putForm()
    {
        $module = $this->getKernel()->module('contact');

        $contact = new Contact($this->getKernel(), intval($_POST['selected']));
    	if ($contact->get('id') != 0) {
    	    return new k_SeeOther($this->getRedirectUrl($contact->get('id')));
    	} else {
    		$contact->error->set("Du skal vælge en kontakt");
    	}

    	return $this->render();
    }
}
/*
require '../../include_first.php';

$module = $kernel->module('contact');
$translation = $kernel->getTranslation('contact');

$redirect = Intraface_Redirect::factory($kernel, 'receive');

if (!empty($_GET['add'])) {

	$add_redirect = Intraface_Redirect::factory($kernel, 'go');
	$url = $add_redirect->setDestination($module->getPath()."contact_edit.php", $module->getPath()."select_contact.php?".$redirect->get('redirect_query_string'));
	$add_redirect->askParameter("contact_id");
	//$add_redirect->setParameter("selected_contact_id", intval($_GET['add']));
	header("Location: ".$url);
	exit;
}

if (!empty($_GET['return_redirect_id'])) {
    $return_redirect = Intraface_Redirect::factory($kernel, 'return');
    if ($return_redirect->getParameter('contact_id') != 0) {
        $redirect->setParameter('contact_id', $return_redirect->getParameter('contact_id'));
        header("Location: ".$redirect->getRedirect('index.php'));
        exit;
    }
}

if (isset($_POST['submit'])) {

	$contact = new Contact($kernel, intval($_POST['selected']));
	if ($contact->get('id') != 0) {
		$redirect->setParameter("contact_id", $contact->get('id'));
		header("Location: ".$redirect->getRedirect('index.php'));
		exit;
	} else {
		$contact->error->set("Du skal v�lge en kontakt");
	}
} else {
	$contact = new Contact($kernel);
}

// hente liste med kunder

$keywords = $contact->getKeywordAppender();
$used_keywords = $keywords->getUsedKeywords();

if (isset($_GET['contact_id'])) {
	$contact->getDBQuery()->setCondition("contact.id = ".intval($_GET['contact_id']));
} elseif (isset($_GET['query']) || isset($_GET['keyword_id'])) {

	if (isset($_GET['query'])) {
		$contact->getDBQuery()->setFilter('search', $_GET['query']);
	}

	if (isset($_GET['keyword_id'])) {
		$contact->getDBQuery()->setKeyword($_GET['keyword_id']);
	}
} else {
	$contact->getDBQuery()->useCharacter();
}

$contact->getDBQuery()->defineCharacter('character', 'address.name');
$contact->getDBQuery()->usePaging('paging');
$contact->getDBQuery()->storeResult('use_stored', 'select_contact', 'sublevel');

if (isset($_GET['contact_id']) && intval($_GET['contact_id']) != 0) {
	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['contact_id']));
} elseif (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0) {
	$contact->getDBQuery()->setExtraUri("&last_contact_id=".intval($_GET['last_contact_id']));
}


$contacts = $contact->getList();

$page = new Intraface_Page($kernel);
$page->start('V�lg kontakt');
?>
<h1><?php e(t('Choose contact')); ?></h1>

<?php echo $contact->error->view(); ?>

<?php if (!$contact->isFilledIn()): ?>

	<p><?php e(t('No contacts has been created')); ?>. <a href="select_contact.php?add=1"><?php e(t('Create contact')); ?></a>.</p>

<?php else: ?>
    <ul class="options">
        <li><a href="select_contact.php?add=1"><?php e(t('Create contact')); ?></a></li>
        <?php if (isset($_GET['last_contact_id']) && intval($_GET['last_contact_id']) != 0): ?>
        <li><a href="select_contact.php?contact_id=<?php e($_GET['last_contact_id']); ?>"><?php e(t('Show chosen')); ?></a></li>
        <?php endif; ?>

    </ul>

    <form action="select_contact.php" method="get" class="search-filter">
	<fieldset>
		<legend><?php e(t('Search')); ?></legend>

		<label for="query"><?php e(t('Search for')); ?>
			<input name="query" id="query" type="text" value="<?php e($contact->getDBQuery()->getFilter('search')); ?>" />
		</label>

		<?php if (is_array($used_keywords) AND count($used_keywords)): ?>
		<label for="keyword_id"><?php e(t('Show with keywords')); ?>
			<select name="keyword_id" id="keyword_id">
				<option value=""><?php e(t('None')); ?></option>
				<?php foreach ($used_keywords AS $k) { ?>
					<option value="<?php e($k['id']); ?>" <?php if ($k['id'] == $contact->getDBQuery()->getKeyword(0)) { echo ' selected="selected"'; }; ?>><?php e($k['keyword']); ?></option>
				<?php } ?>
			</select>
		</label>
		<?php endif; ?>

		<span><input type="submit" value="<?php e(t('Go ahead')); ?>" /></span>
	</fieldset>
    </form>

    <?php echo $contact->getDBQuery()->display('character'); ?>

    <form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">
    	<table summary="Kontakter" class="stripe">
    		<caption><?php e(t('Contacts')); ?></caption>
    		<thead>
    			<tr>
    				<th>&nbsp;</th>
    				<th><?php e(t('No.')); ?></th>
    				<th><?php e(t('Name')); ?></th>
    				<th><?php e(t('Email')); ?></th>
    			</tr>
    		</thead>
    		<tfoot>
    			<tr>
    				<td colspan="4"><?php echo $contact->getDBQuery()->display('paging'); ?></td>
    			</tr>
    		</tfoot>
    		<tbody>
    			<?php foreach ($contacts as $c) { ?>
    			<tr>
    				<td>
    					<input type="radio" value="<?php e($c['id']); ?>" name="selected" <?php if ($redirect->getParameter('contact_id') == $c['id']) print("checked=\"checked\""); ?> />
    				</td>
    				<td><?php e($c['number']); ?></td>
    				<td><a href="contact.php?id=<?php e($c['id']); ?>"><?php e($c['name']); ?></a></td>
    				<td><?php e($c['email']); ?></td>
    			</tr>
    			<?php } // end foreach
                ?>
    		</tbody>
    	</table>

    	<input type="submit" name="submit" value="<?php e(__('Choose')); ?>" /> <?php e(t('or')); ?> <a href="<?php e($redirect->getRedirect("index.php")); ?>"><?php e(t('cancel')); ?></a>
    </form>

<?php endif; ?>
<?php
$page->end();
*/
?>