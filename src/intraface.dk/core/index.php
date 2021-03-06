<?php
$config_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . '/../config.local.php';

if (!file_exists($config_file)) {
    die('The config.local.php file is missing. Please create it.');
}

require_once $config_file;

/**
 * An error-handler which converts all errors (regardless of level) into exceptions.
 * It respects error_reporting settings.
 */
function intraface_exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

set_error_handler('intraface_exceptions_error_handler', error_reporting());

require_once dirname(__FILE__) . '/../../../vendor/autoload.php';
require_once 'Intraface/common.php';
spl_autoload_register('k_autoload');

class Intraface_AuthenticatedUser extends k_AuthenticatedUser
{
    protected $language;

    function __construct($name, k_Language $lang)
    {
        $this->language = $lang;
        parent::__construct($name);
    }

    function language()
    {
        return $this->language;
    }
}

// session_start();

class DanishLanguage implements k_Language
{
    function name()
    {
        return 'Danish';
    }

    function isoCode()
    {
        return 'dk';
    }
}

class EnglishLanguage implements k_Language
{
    function name()
    {
        return 'English';
    }

    function isoCode()
    {
        return 'uk';
    }
}

class Intraface_LanguageLoader implements k_LanguageLoader {
    // @todo The language will often not be set on runtime, e.g. an
    //       intranet where the user can chose him or her own language?
    //       How could one accommodate for this?
    function load(k_Context $context)
    {
        $supported = array("da" => true, "en-US" => true);

        if ($context->identity()->anonymous()) {
            $language = HTTP::negotiateLanguage($supported);
            if (PEAR::isError($language)) {
                // fallback language in case of unable to negotiate
                return new DanishLanguage();
            }

            if ($language == 'da') {
                return new DanishLanguage();
            }

        } elseif ($context->identity()->language() == 'da') {
            return new DanishLanguage();
        }

        // @todo at the moment the system does not take the
        //       settings in the system into account - only
        //       the way the browser is setup.
        $language = HTTP::negotiateLanguage($supported);
        if (PEAR::isError($language)) {
            // fallback language in case of unable to negotiate
            return new DanishLanguage();
        }

        if ($language == 'da') {
            return new DanishLanguage();
        }

        // fallback language
        return new EnglishLanguage();
    }
}

class k_Translation2Translator implements k_Translator
{
    protected $translation2;
    protected $page_id;
    protected $page;

    function __construct($lang, $page_id = NULL)
    {
        $factory = new Intraface_Factory;
        $cache = $factory->new_Translation2_Cache();

        if ($page_id == NULL) {
            $cache_key = 'common';
        } else {
            $cache_key = $page_id;
        }

        if ($data = $cache->get($cache_key, 'translation-'.$lang)) {
            $this->page = unserialize($data);
        } else {
            $translation2 = $factory->new_Translation2();
            $res = $translation2->setLang($lang);

            if (PEAR::isError($res)) {
                throw new Exception('Could not setLang():' . $res->getMessage());
            }

            $this->page = $translation2->getPage('common');
            if ($page_id != NULL) {
                $this->page = array_merge($this->page, $translation2->getPage($page_id));
            }

            $cache->save(serialize($this->page), $cache_key, 'translation-'.$lang);
        }

        $this->page_id = $page_id;
        $this->lang = $lang;
    }

    function translate($phrase, k_Language $language = null)
    {
        if (isset($this->page[$phrase])) {
            return utf8_encode($this->page[$phrase]);
        }

        $logger = new ErrorHandler_Observer_File(TRANSLATION_ERROR_LOG);
        $details = array(
                'date' => date('r'),
                'type' => 'Translation2',
                'message' => 'Missing translation for "'.$phrase.'" on pageID: "'.$this->page_id.'", LangID: "'.$this->lang.'"',
                'file' => '[unknown]',
                'line' => '[unknown]'
            );

        $logger->update($details);

        return $phrase;

    }

    public function get($phrase)
    {
        return $this->translate($phrase);
    }
}

class Intraface_TranslatorLoader implements k_TranslatorLoader
{
    function load(k_Context $context)
    {
        $subspace = explode('/', $context->subspace());
        if (count($subspace) > 3 && $subspace[1] == 'restricted' && $subspace[2] == 'module' && !empty($subspace[3])) {
            $module = $subspace[3];
        } else {
            $module = NULL;
        }
        return new k_Translation2Translator($context->language()->isoCode(), $module);
    }
}

class Intraface_IdentityLoader implements k_IdentityLoader
{
    function load(k_Context $context)
    {
        if ($context->session('intraface_identity')) {
            return $context->session('intraface_identity');
        }
        return new k_Anonymous();
    }
}

class NotAuthorizedComponent extends k_Component
{
    function dispatch()
    {
        // redirect to login-page
        return new k_TemporaryRedirect($this->url('/login', array('continue' => $this->requestUri())));
    }
}

class Intraface_Document extends k_Document
{
    public $options;
    function options()
    {
        if (empty($this->options)) return array();
        return $this->options;
    }
}

class Intraface_TemplateFactory extends k_DefaultTemplateFactory
{
    function create($filename)
    {
        $filename = $filename . '.tpl.php';
        $__template_filename__ = k_search_include_path($filename);
        if (!is_file($__template_filename__)) {
            throw new Exception("Failed opening '".$filename."' for inclusion. (include_path=".ini_get('include_path').")");
        }
        return new k_Template($__template_filename__);
    }
}

$components = new k_InjectorAdapter($bucket, new Intraface_Document);
$components->setImplementation('k_DefaultNotAuthorizedComponent', 'NotAuthorizedComponent');

/**
 * Translates a string.
 */
function __($str) {
  return $GLOBALS['k_current_context']->translator()->translate($str);
}

if (realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
    try {
        k()
        // Use container for wiring of components
        ->setComponentCreator($components)
        // Enable file logging
        ->setLog(K2_LOG)
        // Uncomment the next line to enable in-browser debugging
        //->setDebug(K2_DEBUG)
        // Dispatch request
        ->setIdentityLoader(new Intraface_IdentityLoader())
        ->setLanguageLoader(new Intraface_LanguageLoader())
        ->setTranslatorLoader(new Intraface_TranslatorLoader())
        ->run('Intraface_Controller_Index')
        ->out();
    } catch (Exception $e) {

        $render = new Ilib_Errorhandler_Handler_File(Log::factory('file', ERROR_LOG, 'INTRAFACE'));
        $render->handle($e);

        if (SERVER_STATUS != 'PRODUCTION') {
            $render = new Ilib_Errorhandler_Handler_Echo();
            $render->handle($e);
            die;
        }

        die('<h1>An error orrured!</h1> <P>We have been notified about the problem, but you are always welcome to contact us on support@intraface.dk.</p><p>We apologize for the inconvenience.</p> <pre style="color: white;">'.$e.'</pre>');
    }
}
