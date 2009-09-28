<?php
class Intraface_Controller_Restricted extends k_Component
{
    protected $registry;
    protected $kernel;

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
        if (!empty($_GET['message']) AND in_array($_GET['message'], array('hide'))) {
			$this->getKernel()->setting->set('user', 'homepage.message', 'hide');
		}
        $smarty = new k_Template(dirname(__FILE__) . '/templates/restricted.tpl.php');
        return $smarty->render($this);
    }

    function getTranslation()
    {
        $language = $this->getKernel()->setting->get('user', 'language');

        // set the parameters to connect to your db
        $dbinfo = array(
            'hostspec' => DB_HOST,
            'database' => DB_NAME,
            'phptype'  => 'mysql',
            'username' => DB_USER,
            'password' => DB_PASS
        );

        if (!defined('LANGUAGE_TABLE_PREFIX')) {
            define('LANGUAGE_TABLE_PREFIX', 'core_translation_');
        }

        $params = array(
            'langs_avail_table' => LANGUAGE_TABLE_PREFIX.'langs',
            'strings_default_table' => LANGUAGE_TABLE_PREFIX.'i18n'
        );

        $translation = Translation2::factory('MDB2', $dbinfo, $params);
        //always check for errors. In this examples, error checking is omitted
        //to make the example concise.
        if (PEAR::isError($translation)) {
            trigger_error('Could not start Translation ' . $translation->getMessage(), E_USER_ERROR);
        }

        // set primary language
        $set_language = $translation->setLang($language);

        if (PEAR::isError($set_language)) {
            trigger_error($set_language->getMessage(), E_USER_ERROR);
        }

        // set the group of strings you want to fetch from
        // $translation->setPageID($page_id);

        // add a Lang decorator to provide a fallback language
        $translation = $translation->getDecorator('Lang');
        $translation->setOption('fallbackLang', 'uk');
        $translation = $translation->getDecorator('LogMissingTranslation');
        require_once("ErrorHandler/Observer/File.php");
        $translation->setOption('logger', array(new ErrorHandler_Observer_File(ERROR_LOG), 'update'));
        $translation = $translation->getDecorator('DefaultText');

        // %stringID% will be replaced with the stringID
        // %pageID_url% will be replaced with the pageID
        // %stringID_url% will replaced with a urlencoded stringID
        // %url% will be replaced with the targeted url
        //$this->translation->outputString = '%stringID% (%pageID_url%)'; //default: '%stringID%'
        $translation->outputString = '%stringID%';
        $translation->url = '';           //same as default
        $translation->emptyPrefix  = '';  //default: empty string
        $translation->emptyPostfix = '';  //default: empty string

        $this->getKernel()->translation = $translation;
        return $translation;
    }

    function getKernel()
    {
        if (is_object($this->kernel)) {
            return $this->kernel;
        }
    	return $this->kernel = $this->session()->get('kernel');
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function getLastView()
    {
		$last_view = $this->getKernel()->setting->get('user', 'homepage.last_view');
		$this->getKernel()->setting->set('user', 'homepage.last_view', date('Y-m-d H:i:s'));
    	return $last_view;
    }

    /*
    function wrapHtml($content)
    {
        return sprintf('<html><body><ul><li><a href="'.$this->url('/restricted/module').'">Moduler</a></li><li><a href="'.$this->url('/logout').'">Logout</a></li><li><a href="'.$this->url('/restricted/switchintranet').'">Switch Intranet</a></li></ul>%s</body></html>', $content);
    }
    */

    function wrapHtml($content)
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/wrapper.tpl.php');
        return $smarty->render($this, array('content' => $content));
    }



    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function t($phrase)
    {
        return $phrase;
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
        $this->usermenu[2]['url'] = url('/restricted/module');
        return $this->usermenu;
    }

    function getMenu()
    {
        $this->menu = array();
        $i = 0;
        $this->menu[$i]['name'] = $this->getTranslation()->get('dashboard');
        $this->menu[$i]['url'] = url('/restricted/');
        $i++;
        $this->db = new DB_Sql;
        $this->db->query("SELECT name, menu_label, name FROM module WHERE active = 1 AND show_menu = 1 ORDER BY menu_index");
        while ($this->db->nextRecord()) {
            if ($this->getKernel()->user->hasModuleAccess($this->db->f('name'))) {
                $this->menu[$i]['name'] = $this->getKernel()->translation->get($this->db->f('name'), $this->db->f('name'));
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
                       $this->submenu[$j]['name'] = $this->getKernel()->translation->get($all_submenu[$i]['label'], $this->primary_module->getName());
                       $this->submenu[$j]['url'] = $this->primary_module->getPath(). $all_submenu[$i]['url'];
                            $j++;
                    }
                }
            }
        }

        return $this->submenu;
    }
}