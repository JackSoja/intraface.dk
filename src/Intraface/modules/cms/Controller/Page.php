<?php
class Intraface_modules_cms_Controller_Page extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_cms_Controller_PageEdit';
        } elseif ($name == 'section') {
            return 'Intraface_modules_cms_Controller_Sections';
        }

    }

    function renderHtml()
    {
        $module_cms = $this->getKernel()->module('cms');
        $module_cms->includeFile('HTML_Editor.php');
        $translation = $this->getKernel()->getTranslation('cms');

        $error = array();

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            $identifier_parts = explode(':', $redirect->get('identifier'));
            if ($identifier_parts[0] == 'picture') {
                $section = CMS_Section::factory($this->getKernel(), 'id', $identifier_parts[1]);
                $section->save(array('pic_id' => $redirect->getParameter('file_handler_id')));
            }
            return new k_SeeOther($this->url());

        }

        $cmspage = CMS_Page::factory($this->getKernel(), 'id', $this->name());
        $sections = $cmspage->getSections();

        if (!empty($sections) AND count($sections) == 1 AND array_key_exists(0, $sections) AND $sections[0]->get('type') == 'mixed') {
            return new k_SeeOther($this->url('section/' . $sections[0]->get('id')));
        };
        if ($this->getKernel()->setting->get('user', 'htmleditor') == 'tinymce') {
            $this->document->addScript($this->url('tinymce/jscripts/tiny_mce/tiny_mce.js'));
        }

        $data = array('cmspage' => $cmspage, 'sections' => $sections);
        $tpl = $this->template->create(dirname(__FILE__) . '/templates/page');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $module_cms = $this->getKernel()->module('cms');
        $module_cms->includeFile('HTML_Editor.php');
        $translation = $this->getKernel()->getTranslation('cms');

        $error = array();

        if (!empty($_POST['publish'])) {
            $cmspage = CMS_Page::factory($this->getKernel(), 'id', $_POST['id']);
            if ($cmspage->publish()) {
                return new k_SeeOther($this->url());
            }
        } elseif (!empty($_POST['unpublish'])) {
            $cmspage = CMS_Page::factory($this->getKernel(), 'id', $_POST['id']);
            if ($cmspage->unpublish()) {
                return new k_SeeOther($this->url());
            }
        }

        $files = '';
        if (isset($_POST['section']) && is_array($_POST['section'])) {
            foreach ($_POST['section'] AS $key=>$value) {
                $section = CMS_Section::factory($this->getKernel(), 'id', $key);

                if ($section->get('type') == 'picture') {

                    if (!empty($_FILES) && !is_array($files)) {
                        $filehandler = new FileHandler($this->getKernel());
                        $filehandler->createUpload();
                        $files = $filehandler->upload->getFiles();
                    }

                    if (is_array($files)) {
                        foreach ($files AS $file) {
                            if ($file->getProp('form_name') == 'new_picture_'.$key) {

                                $filehandler = new FileHandler($this->getKernel());
                                $filehandler->createUpload();
                                $filehandler->upload->setSetting('file_accessibility', 'public');
                                $pic_id = $filehandler->upload->upload($file);

                                if ($pic_id != 0) {
                                    $value['pic_id'] = $pic_id;
                                }

                                // Vi har fundet filen til som passer til dette felt, s� er der ikke nogen grund til at k�re videre.
                                break;
                            }
                        }
                    }

                    if (!isset($value['pic_id'])) $value['pic_id'] = 0;
                }
                if (!$section->save($value)) {
                    $error[$section->get('id')] = __('error in section') . ' ' . strtolower(implode($section->error->message, ', '));
                }
            }
        }
        if (empty($error) AND count($error) == 0) {
            if (!empty($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {

                // jeg skal bruge array_key, n�r der er klikket p� choose_file, for den indeholder section_id. Der b�r
                // kun kunne v�re en post i arrayet, s� key 0 m� v�re $section_id for vores fil
                $keys = array_keys($_POST['choose_file']);
                $section_id = $keys[0];

                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $module_filemanager = $this->getKernel()->useModule('filemanager');
                $redirect->setIdentifier('picture:'.$section_id);
                $url = $redirect->setDestination($module_filemanager->getPath().'select_file.php', $module_cms->getPath().'page.php?id='.$section->cmspage->get('id') . '&from_section_id=' . $section_id);

                $redirect->askParameter('file_handler_id');
                header('Location: '.$url);
                exit;
            } elseif (!empty($_POST['edit_html'])) {
                $keys = array_keys($_POST['edit_html']);
                return new k_SeeOther($this->url('../' . $id . '/section/' . $keys[0]));
            } elseif (!empty($_POST['close'])) {
                return new k_SeeOther($this->url('../', array('type' => $section->cmspage->get('type'), 'id' => $section->cmspage->cmssite->get('id'))));
            } else {
                return new k_SeeOther($this->url('../' . $section->cmspage->get('id')));
            }
        } else {
            $cmspage = $section->cmspage;
            $sections = $cmspage->getSections();

            $value = $_POST;
        }
        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}
