<?php
class Intraface_Controller_Restricted extends k_Component
{
    protected $mdb2;
    protected $db_sql;
    protected $kernel;
    protected $user_gateway;
    protected $kernel_gateway;
    protected $template;

    function __construct(DB_Sql $db_sql, MDB2_Driver_Common $mdb2, Intraface_UserGateway $gateway, Intraface_KernelGateway $kernel_gateway, k_TemplateFactory $template /*k_Registry $registry*/)
    {
        $this->mdb2 = $mdb2; // this is here to make sure set names utf8 is run as the first thing in the app
        $this->db_sql = $db_sql;
        $this->user_gateway = $gateway;
        $this->kernel_gateway = $kernel_gateway;
        $this->template = $template;
    }

    function document()
    {
        return $this->document;
    }

    protected function map($name)
    {
        if ($name == 'switchintranet') {
            return 'Intraface_Controller_SwitchIntranet';
        } elseif ($name == 'module') {
            return 'Intraface_Controller_ModuleGatekeeper';
        }
    }

    function dispatch()
    {
        if ($this->identity()->anonymous()) {
            throw new k_NotAuthorized();
        }

        return parent::dispatch();
    }

    function renderHtml()
    {
        $this->document->setTitle('Dashboard');

        $_advice[] = array();
        $_attention_needed[] = array();
        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
			$this->getKernel()->setting->set('user', 'homepage.message', 'hide');
		}

		$kernel = $this->getKernel();

        // getting stuff to show on the dashboard
        $modules = $this->getKernel()->getModules();

        foreach ($modules as $module) {

        	if (!$this->getKernel()->intranet->hasModuleAccess(intval($module['id']))) {
        		continue;
        	}
        	if (!$this->getKernel()->user->hasModuleAccess(intval($module['id']))) {
        		continue;
        	}

        	$module = $this->getKernel()->useModule($module['name']);
        	$frontpage_files = $module->getFrontpageFiles();

        	if (!is_array($frontpage_files) OR count($frontpage_files) == 0) {
        		continue;
        	}

        	foreach ($frontpage_files AS $file) {
        		$file = PATH_INCLUDE_MODULE . $module->getName() . '/' .$file;
        		if (file_exists($file)) {
        			include($file);
        		}
        	}
        }
        // Adds link for id user details is filled in. They are going to be in the top.
        if ($this->getKernel()->user->hasModuleAccess('controlpanel')) {
            if (!$this->getKernel()->user->isFilledIn()) {
            	$_advice[] = array(
            		'msg' => 'all information about you has not been filled in',
            		'link' => $this->url('core/restricted/module/controlpanel/user', array('edit')),
            		'module' => 'dashboard'
            	);
            }
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/restricted');
        return $smarty->render($this, array('_attention_needed' => $_attention_needed, '_advice' => $_advice));
    }

    /**
     * Hvad med at flytte det hele over i et userobject, der implementerer getIntranet()
     * I $intranet har vi s� de transiente gateways.
     *
     * class component_ShowProduct {
  	 *   protected $user_gateway;
     *   function __construct(UserGateway $user_gateway) {
     *     $this->user_gateway = $user_gateway;
     *   }
     *   function GET() {
     *     $user = $user_gateway->getByUsername($this->identity()->getUsername());
     *     return $this->render('product.tpl.php', array('product' => $user->getIntranet()->getProduct()));
     *   }
     * }
     *
     * http://pastie.org/683209
     *
     * @return object
     */
    function getKernel()
    {
        if (is_object($this->kernel)) {
            return $this->kernel;
        }
        try {
            return $this->kernel = $this->kernel_gateway->findByUserobject($this->user_gateway->findByUsername($this->identity()->user()));
        } catch (Exception $e) {
            return new k_SeeOther($this->url('/logout'));
        }
    }

    function getLastView()
    {
		$last_view = $this->getKernel()->setting->get('user', 'homepage.last_view');
		$this->getKernel()->setting->set('user', 'homepage.last_view', date('Y-m-d H:i:s'));
    	return $last_view;
    }

    function getUserMenu()
    {
        $this->usermenu = array();
        $this->usermenu[0]['name'] = $this->t('Logout');
        $this->usermenu[0]['url'] = $this->url('/logout');
        if (count($this->getKernel()->user->getIntranetList()) > 1) {
            $this->usermenu[1]['name'] = $this->t('Switch intranet');
            $this->usermenu[1]['url'] = $this->url('/restricted/switchintranet');
        }
        $this->usermenu[2]['name'] = $this->t('Modules');
        $this->usermenu[2]['url'] = $this->url('/restricted/module');
        return $this->usermenu;
    }

    function getMenu()
    {
        $this->menu = array();
        $i = 0;
        $this->menu[$i]['name'] = $this->t('dashboard');
        $this->menu[$i]['url'] = $this->url('/restricted/');
        $i++;
        $this->db = new DB_Sql;
        $this->db->query("SELECT name, menu_label, name FROM module WHERE active = 1 AND show_menu = 1 ORDER BY menu_index");
        while ($this->db->nextRecord()) {
            if ($this->getKernel()->user->hasModuleAccess($this->db->f('name'))) {
                $this->menu[$i]['name'] = $this->t($this->db->f('name'));
                $this->menu[$i]['url'] = $this->url('/restricted/module/' . $this->db->f("name"));
                $i++;
            }
        }
        return $this->menu;
    }

    function getSubmenu()
    {
        $this->primary_module = $this->kernel->getPrimaryModule();
        $this->submenu = array();
        if (is_object($this->primary_module)) {
            $all_submenu = $this->primary_module->getSubmenu();
            if (count($all_submenu) > 0) { // added to avoid error messages
                $j = 0;
                for ($i = 0, $max = count($all_submenu); $i < $max; $i++) {
                    $access = false;
                    if ($all_submenu[$i]['sub_access'] != '') {
                        $sub = explode(":", $all_submenu[$i]['sub_access']);

                        switch($sub[0]) {
                            case 'sub_access':
                                if ($this->getKernel()->user->hasSubAccess($this->primary_module->module_name, $sub[1])) {
                                    $access = true;
                                }
                                break;
                            case 'module':
                                if ($this->getKernel()->user->hasModuleAccess($sub[1])) {
                                    $access = true;
                                }
                                break;
                            default:
                                trigger_error('Der er ikke angivet om submenu skal tjekke efter sub_access eller module adgang, for undermenupunktet i Page->start();', E_USER_ERROR);
                                break;
                        }
                    } else {
                        $access = true;
                    }

                    if ($access) {
                       $this->submenu[$j]['name'] = $this->t($all_submenu[$i]['label']);
                       $this->submenu[$j]['url'] = $this->primary_module->getPath(). $all_submenu[$i]['url'];
                       $j++;
                    }
                }
            }
        }

        return $this->submenu;
    }

    function getIntranetName()
    {
        return $this->getKernel()->intranet->get('name');;
    }

    function wrapHtml($content)
    {
        $this->document->setTitle('Intraface.dk');

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/main');
        $content = $tpl->render($this, array('content' => $content));
        return new k_HttpResponse(200, $content, true);
        // return new k_HttpResponse(200, $this->getHeader() . $content . $this->getFooter(), true);
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }
    /*
    function getTranslation()
    {
        return $this->getKernel()->getTranslation();
    }
    */

    function getThemeKey()
    {
        return $this->getKernel()->setting->get('user', 'theme');
    }

    function getFontSize()
    {
         return $this->getKernel()->setting->get('user', 'ptextsize');
    }
}