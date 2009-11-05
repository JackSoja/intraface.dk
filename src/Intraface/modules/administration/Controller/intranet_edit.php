<?php
class Intraface_modules_administration_Controller_Intranet extends k_Component
{
    protected $registry;
    protected $intranetmaintenance;

    function renderHtml()
    {
        $modul = $kernel->module('administration');
        $shared_filehandler = $kernel->useShared('filehandler');
        $translation = $kernel->getTranslation('administration');

        if (isset($_GET['return_redirect_id'])) {
        	$redirect = Intraface_Redirect::factory($kernel, 'return');
        	$file_id = $redirect->getParameter('file_handler_id');

        	$intranet = new IntranetAdministration($kernel);
        	$filehandler = new FileHandler($kernel, intval($file_id));
        	if ($filehandler->get('id') != 0) {

        		$type = $filehandler->get('file_type');
        		if ($type['mime_type'] == 'image/jpeg' || $type['mime_type'] == 'image/pjpeg') {
        			$values = $intranet->get();
        			$values['pdf_header_file_id'] = $filehandler->get('id');
        			$intranet->update($values);
        		} else {
        			$filehandler->error->set('Header should be a .jpg image - got '. $filehandler->get('file_type'));
        		}
        	}
        }
    	$intranet = new IntranetAdministration($kernel);
    	$values = $intranet->get();
    	$address = $intranet->address->get();


        $smarty = new k_Template(dirname(__FILE__) . '/templates/intranet-edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {

        if (isset($_POST['submit']) || isset($_POST['choose_file'])) {

        	$intranet = new IntranetAdministration($kernel);
        	$values = $_POST;

        	$filehandler = new FileHandler($kernel);
        	$filehandler->createUpload();
        	if ($filehandler->upload->isUploadFile('new_pdf_header_file') && $id = $filehandler->upload->upload('new_pdf_header_file')) {
                $filehandler->load();

                $type = $filehandler->get('file_type');
                if ($type['mime_type'] == 'image/jpeg' || $type['mime_type'] == 'image/pjpeg') {
                    $values['pdf_header_file_id'] = $id;
                } else {
                    $intranet->error->set('Header should be a .jpg image - got ' . $type['mime_type']);
                    $filehandler->delete();
                }
        	}

        	if ($intranet->update($values)) {
        		$address_values = $_POST;
                $address_values['name'] = $_POST['address_name'];
        		if ($intranet->address->validate($address_values) && $intranet->address->save($address_values)) {
        			if (isset($_POST['choose_file']) && $kernel->user->hasModuleAccess('filemanager')) {
        				$module_filemanager = $kernel->useModule('filemanager');
        				$redirect = Intraface_Redirect::factory($kernel, 'go');
        	 			$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1');
        				$redirect->askParameter('file_handler_id');
        				header('Location: ' . $url);
        				exit;
        			} else {
        				header('Location: '.url('/main/controlpanel/intranet.php'));
        			}
        		}
                $values = $_POST;
                $address = $_POST;

        	} else {
        		$values = $_POST;
        		$address = $_POST;
        	}
        }
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }
}