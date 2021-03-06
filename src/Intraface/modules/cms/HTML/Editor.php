<?php
/**
 * Remember to include the javascript as well
 *
 * @package Intraface_CMS
 */
class Intraface_modules_cms_HTML_Editor
{
    public $allowed_tags;
    public $implemented_editors = array(
        'none', 'tinymce', 'wiki'
    );
    public $editor;
    public $options;

    /**
     * Constructor
     */
    function __construct($allowed_tags = '')
    {
        $this->allowed_tags = $allowed_tags;
    }

    function setEditor($editor)
    {
        if (!in_array($editor, $this->implemented_editors)) {
            throw new Exception($editor . 'editor not implemented');
        }

        $this->editor = $editor;
    }

    function get($textarea_attributes, $initial_value = '', $editor_attributes = array())
    {
        $output = '';
        switch ($this->editor) {
            case 'tinymce':
                // return tinymce textarea

                $blockformat = array();
                $button = array();


                if (!empty($editor_attributes['plugins']) and is_array($editor_attributes['plugins'])) {
                    if (in_array('save', $editor_attributes['plugins'])) {
                        $button[] = 'save';
                    }
                }

                $button[] = 'undo';
                $button[] = 'redo';

                if (!empty($this->allowed_tags) and is_array($this->allowed_tags)) {
                    if (in_array('p', $this->allowed_tags)) {
                        $blockformat[] = 'p';
                    }
                    if (in_array('h1', $this->allowed_tags)) {
                        $blockformat[] = 'h1';
                    }

                    if (in_array('h2', $this->allowed_tags)) {
                        $blockformat[] = 'h2';
                    }

                    if (in_array('h3', $this->allowed_tags)) {
                        $blockformat[] = 'h3';
                    }

                    if (in_array('h4', $this->allowed_tags)) {
                        $blockformat[] = 'h4';
                    }

                    if (in_array('blockquote', $this->allowed_tags)) {
                        $blockformat[] = 'blockquote';
                    }

                    if (in_array('strong', $this->allowed_tags)) {
                        $button[] = 'bold';
                    } elseif (in_array('b', $this->allowed_tags)) {
                        $button[] = 'bold';
                    }

                    if (in_array('em', $this->allowed_tags)) {
                        $button[] = 'italic';
                    } elseif (in_array('i', $this->allowed_tags)) {
                        $button[] = 'italic';
                    }

                    if (in_array('a', $this->allowed_tags)) {
                        $button[]= 'link';
                        $button[]= 'unlink';
                    }

                    if (in_array('ul', $this->allowed_tags)) {
                        $button[] = 'bullist';
                    }
                    if (in_array('ol', $this->allowed_tags)) {
                        $button[] = 'numlist';
                    }
                }

                if (!empty($blockformat)) {
                    $button[] = 'formatselect';
                }



                // link, unlink - hvis link er tilladte
                // bold, italic - hvis de er tilladte
                // formatselect - if blockformats !empty
                // bullist, numlist - if lists are availabel
                // spellchecker - if it is turned on

                // original list of buttons1: , bold, italic, formatselect, separator, bullist,numlist,separator,undo,redo,separator,link,unlink,separator,sub,sup,separator, tablecontrols, separator,charmap,separator,cleanup,code,spellchecker,separator,help,pasteword

                if (!empty($editor_attributes['plugins']) and is_array($editor_attributes['plugins'])) {
                    if (in_array('spellchecker', $editor_attributes['plugins'])) {
                        // $button[] = 'spellchecker';
                    }

                    if (in_array('table', $editor_attributes['plugins']) and in_array('table', $this->allowed_tags)) {
                        $button[] = 'tablecontrols';
                    }
                }

                $button[] = 'code';
                $button[] = 'pasteword';

                $output = '<textarea'.$this->_parseTextareaAttributes($textarea_attributes).'>'.htmlentities(utf8_decode($initial_value)).'</textarea>'."\n";
                $output .= '<script language="javascript" type="text/javascript">'."\n";
                $output .= 'tinyMCE.init({'."\n";
                $output .= '    mode : "exact",'."\n";
                $output .= '    elements : "'.$textarea_attributes['id'].'",'."\n";
                $output .= '    theme : "advanced",'."\n";
                if (!empty($editor_attributes['plugins']) and is_array($editor_attributes['plugins'])) {
                    $output .= '    plugins : "'.implode($editor_attributes['plugins'], ',').'",'."\n";
                }
                $output .= '    theme_advanced_buttons1 : "'.implode($button, ',').'",'."\n";
                $output .= '    theme_advanced_buttons2 : "",'."\n";
                $output .= '    theme_advanced_buttons3 : "",'."\n";
                $output .= '    theme_advanced_blockformats : "'.implode($blockformat, ',').'",'."\n";
                $output .= '    theme_advanced_toolbar_location : "top",'."\n";
                $output .= '    theme_advanced_toolbar_align : "left",'."\n";
                $output .= '    cleanup : true,'."\n";
                $output .= '    clean_on_startup : true,'."\n";
                $output .= '    verify_html : true,'."\n";
                $output .= '    apply_source_formatting : true,'."\n";
                $output .= '    relative_urls : false,'."\n";
                $output .= '    convert_urls : false,'."\n";
                $output .= '    entity_encoding : "raw",'."\n";
                $output .= '    remove_linebreaks : true'."\n";
                //$output .= '  spellchecker_languages : "+Danish=da, English=en"';

                $output .= '});'."\n";
                $output .= '</script>'."\n";

                break;
                /*
            case 'widgeditor':
                if (!empty($texarea_attributes['class'])) $texarea_attributes['class'] .= ' widgeditor';
                else $texarea_attributes['class'] .= 'widgeditor';
                $output = '<textarea'.$this->_parseTextareaAttributes($textarea_attributes).'>'.htmlentities($initial_value).'</textarea>';
            break;
            */
            case 'wiki':
                // fall through
            default:
                    // return ordinary textarea
                    $output = '<textarea'.$this->_parseTextareaAttributes($textarea_attributes).'>'.htmlspecialchars($initial_value).'</textarea>';
                break;
        }
        return $output;
    }

    function _parseTextareaAttributes($textarea_attributes)
    {
        $output = '';
        if (array_key_exists('id', $textarea_attributes)) {
            $output .= ' id="'.$textarea_attributes['id'].'"';
        }
        if (array_key_exists('name', $textarea_attributes)) {
            $output .= ' name="'.$textarea_attributes['name'].'"';
        }
        if (array_key_exists('cols', $textarea_attributes)) {
            $output .= ' cols="'.$textarea_attributes['cols'].'"';
        }
        if (array_key_exists('rows', $textarea_attributes)) {
            $output .= ' rows="'.$textarea_attributes['rows'].'"';
        }
        if (array_key_exists('class', $textarea_attributes)) {
            $output .= ' class="'.$textarea_attributes['class'].'"';
        }
        return $output;
    }
}
