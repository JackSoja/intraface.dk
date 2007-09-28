<?php
/**
 * @package Intraface_CMS
 */

class CMS_Template_LongText extends CMS_TemplateSection {

    var $possible_allowed_html = array(
        'strong', 'a', 'em'
    );

    function __construct(& $cmspage, $id = 0) {
        $this->value['type'] = 'longtext';
        parent::__construct($cmspage, $id);
    }

    function load_section() {
        $this->value['size'] = $this->parameter->get('size');
        if ($this->parameter->get('html_format')) {
            $this->value['html_format'] = unserialize($this->parameter->get('html_format'));
            if (!is_array($this->value['html_format'])) {
                $this->value['html_format'] = array();
            }
        }
        else {
            $this->value['html_format'] = array();
        }
    }

    function validate_section(& $var) {
        $validator = new Validator($this->error);
        if (!empty($var['size'])) $validator->isNumeric($var['size'], 'error in size', 'allow empty');

        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    function save_section($var) {
        if (empty($var['html_format'])) array();
        if (empty($var['size'])) $var['size'] = 1000000;
        $this->addParameter('size', $var['size']);
        $this->addParameter('html_format', serialize($var['html_format']));
        return 1;
    }

}

?>