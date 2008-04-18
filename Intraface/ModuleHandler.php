<?php
/**
 * Module
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @author  Sune Jensen <sj@sunet.dk>
 * @since   0.1.0
 * @version @package-version@
 */
require_once 'Intraface/Main.php';

class Intraface_ModuleHandler
{
    private $modules = array();

    public function __construct()
    {
    }

    function setPrimaryModule($module_name)
    {
        if (!empty($this->primary_module_object) AND is_object($this->primary_module_object)) {
            trigger_error('Det prim�re modul er allerede sat', E_USER_ERROR);
        } else {
            $module = $this->useModule($module_name);

            if (is_object($module)) {
                $this->primary_module_name = $module_name;

                // Finder afh�ngige moduller - Dette kunne flyttes til useModule, hvorfor er den egentlig ikke det? /Sune 06-07-2006
                $dependent_modules = $module->getDependentModules();

                for ($i = 0, $max = count($dependent_modules); $i < $max; $i++) {
                    $no_use = $this->useModule($dependent_modules[$i]);
                }

                return $module;
            } else {
                // @todo Den fejlmeddelse er egentlig irrelevant, da useModul ikke enten returnere et objekt eller trigger_error.
                trigger_error('Du har ikke adgang til modulet', E_USER_ERROR);
                return false;
            }
        }

    }

    /**
     * useModule()
     *
     * @todo Need to handle access
     *
     * @param  string  $module_name
     * @param  boolean $ignore_user_access
     *
     * @return object  $module
     */
    public function useModule($module_name, $ignore_user_access = false)
    {
        if (!ereg("^[a-z0-9]+$", $module_name)) {
            throw new Exception('module name invalid');
        }

        if (!empty($this->modules[$module_name]) AND is_object($this->modules[$module_name])) {
            return $this->modules[$module_name];
        }

        $this->modules[$module_name] = $module_name;

        $main_class_name = "Main".ucfirst($module_name);
        $main_class_path = PATH_INCLUDE_MODULE.$module_name."/".$main_class_name.".php";

        if (file_exists($main_class_path)) {
            require_once($main_class_path);
            $object = new $main_class_name;
            $object->load($this);
            $this->modules[$module_name] = $object;
             return $object;
        } else {
            trigger_error('ModuleHandler: ' . $main_class_path . ' does not exist', E_USER_ERROR);
            return false;
        }

        //return true;

        /*
        if (!is_object($this->user)) {
            // Det er et weblogin.
            if ($this->intranet->hasModuleAccess($module_name)) {
                $access = true;
            }
        }
        elseif ($ignore_user_access) {
            // Skal kun kontrollere om intranettet har adgang, for at benytte modullet
            if ($this->intranet->hasModuleAccess($module_name)) {
                $access = true;
            }
        }
        else {
            // Almindelig login
            if ($this->user->hasModuleAccess($module_name)) {
                $access = true;
            }
        }

        if ($access == true) {
            $main_class_name = "Main".ucfirst($module_name);
            $main_class_path = PATH_INCLUDE_MODULE.$module_name."/".$main_class_name.".php";

            if (file_exists($main_class_path)) {
                require_once($main_class_path);
                $object = new $main_class_name;
                $object->load($this);
                $this->modules[$module_name] = $object;

                return $object;
            }
            else {
                trigger_error($main_class_path.' eksisterer ikke', E_USER_ERROR);
            }
        }
        else {
            trigger_error('Du mangler adgang til et modul for at kunne se denne side: '.$module_name, E_USER_ERROR);
            // Det her kan jeg ikke lige finde ud af, om den skal returnere nul eller den skal returnere fejl!
            // Det fungere fint n�r den returnere fejl. Hvis det laves om, skal der i hvertfald rettes i /debtor/debtorFactory.php /Sune (21/3 2005)
            // return(0);
        }
        */
    }

    /**
     * getModule()
     *
     * @param string $module_name
     *
     * @return object
     */
    function getModule($name)
    {
        if (is_object($this->modules[$name])) {
            return($this->modules[$name]);
        } else {
            trigger_error('getModule() module ' . $name . ' not loaded', E_USER_ERROR);
        }
    }

    /**
     * getModules()
     *
     * @param string $order_by
     *
     * @return array
     */
    static public function getModules($db, $order_by = 'frontpage_index')
    {
        $modules = array();

        if ($order_by != '') {
            $order_by = "ORDER BY ".$db->quoteIdentifier($order_by, 'text');
        }

        $i = 0;
        $result = $db->query("SELECT id, menu_label, name, show_menu FROM module WHERE active = 1 ".$order_by);
        if (PEAR::isError($result)) {
            trigger_error($result->getUserInfo(), E_USER_ERROR);
        }
        while ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $modules[$i]['id'] = $row['id'];
            $modules[$i]['name'] = $row['name'];
            $modules[$i]['menu_label'] = $row['menu_label'];
            $modules[$i]['show_menu'] = $row['show_menu'];

            $j = 0;
            $result_sub = $db->query("SELECT id, description FROM module_sub_access WHERE active = 1 AND module_id = ".$db->quote($row["id"], 'integer')." ORDER BY description");
            if (PEAR::isError($result_sub)) {
                trigger_error($result_sub->getUserInfo(), E_USER_ERROR);
            }

            while ($row_sub = $result_sub->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                $modules[$i]['sub_access'][$j]['id'] = $row_sub['id'];
                $modules[$i]['sub_access'][$j]['description'] = $row_sub['description'];
                $j++;
            }

            $i++;
        }
        return $modules;

    }

    /**
     * Function to check whether the module has been registered
     * Made temporarily for /main/index.php
     */
    static public function exists($db, $module_id)
    {
        if (is_numeric($module_id)) {
            trigger_error("Not yet implemented!", E_USER_ERROR);
            exit;
        } else {
            $result = $db->query('SELECT id FROM module WHERE name = '.$db->quote($module_id, 'text'));
            if (PEAR::isError($result)) {
                trigger_error('Error in query: '.$result->getUserInfo(), E_USER_ERROR);
                exit;
            }

            if ($result->numRows() > 0) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}