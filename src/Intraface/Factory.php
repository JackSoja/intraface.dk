<?php
class Intraface_Factory
{
    protected $config;

    function __construct($config = null)
    {
        $this->config = $config;
    }

    function new_Intraface_Kernel()
    {
        return new Intraface_Kernel(session_id());
    }

    /**
     * @deprecated when everything i konstrukt2
     * @param $container
     * @return MDB2_Driver_Common
     */
    function new_MDB2($container)
    {
        return $this->new_MDB2_Driver_Common($container);
    }

    function new_MDB2_Driver_Common($container)
    {
        $db = MDB2::singleton(DB_DSN, array('persistent' => true));
        if (PEAR::isError($db)) {
            throw new Exception($db->getMessage());
        }

        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $db->setOption('debug', MDB2_DEBUG);
        $db->setOption('portability', MDB2_PORTABILITY_NONE);
        $res = $db->setCharset('latin1');
        if (PEAR::isError($res)) {
            throw new Exception($res->getUserInfo());
        }

        if ($db->getOption('debug')) {
            $db->setOption('log_line_break', "\n\n\n\n\t");

            $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
            $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

            register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
            register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
        }

        return $db;
    }

    function new_DB_Sql($container)
    {
        return new DB_Sql(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    function new_Translation2($container)
    {
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
            throw new Exception('Could not start Translation ' . $translation->getMessage());
        }

        // set the group of strings you want to fetch from
        // $translation->setPageID($page_id);

        // add a Lang decorator to provide a fallback language
        $translation = $translation->getDecorator('Lang');
        $translation->setOption('fallbackLang', 'uk');
        $translation = $translation->getDecorator('LogMissingTranslation');
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
        return $translation;
    }

    function new_Intraface_Auth($container)
    {
        return new Intraface_Auth(session_id());
    }

    /*
    function new_Intraface_Page($container)
    {
        return new Intraface_Page($this->config->kernel, $this->new_DB_Sql($container));
    }
    */

    /////////////////////////////////////////////////////////////////////

    function new_k_Template($container)
    {
        $smarty = new k_Template($this->config->template_dir);
        return $smarty;
    }

    function new_k_Registry($container)
    {
    	$registry = new k_Registry();
        /*
    	$registry->registerConstructor('doctrine', create_function(
            '$className, $args, $registry',
            'return Doctrine_Manager::connection(DB_DSN);'
        ));
        */
        /*
        $registry->registerConstructor('category_gateway', create_function(
          '$className, $args, $registry',
          'return new Intraface_modules_shop_Shop_Gateway;'
        ));
        */

        /*
        $registry->registerConstructor('kernel', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["kernel"];'
        ));
        */

        /*
        $registry->registerConstructor('intranet', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["intranet"];'
        ));
        */

        /*
        $registry->registerConstructor('db', create_function(
          '$className, $args, $registry',
          'return $GLOBALS["db"];'
        ));
        */
        /*
        $registry->registerConstructor('page', create_function(
          '$className, $args, $registry',
          'return new Intraface_Page($registry->get("kernel"));'
        ));
        */

        return $registry;
    }

    function new_Doctrine_Connection_Common()
    {
        Doctrine_Manager::getInstance()->setAttribute("use_dql_callbacks", true);
        Doctrine_Manager::getInstance()->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_TYPES | Doctrine::VALIDATE_CONSTRAINTS);
        return Doctrine_Manager::connection(DB_DSN);
    }
    /*
    function new_Intraface_Doctrine_Intranet()
    {
        Intraface_Doctrine_Intranet::singleton($kernel->intranet->getId());
    }


    ///////////////////////////////////////////////////////////////////////

    function new_Intraface_modules_product_Gateway($container)
    {
        return new Intraface_modules_product_Gateway($GLOBALS["kernel"]);
    }
    */
}