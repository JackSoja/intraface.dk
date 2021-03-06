<?php
/**
 * @package Intraface_CMS
 */
class Intraface_modules_cms_element_Filelist extends CMS_Element
{
    public $select_methods = array('single_file');

    function __construct($section, $id = 0)
    {
        $this->value['type'] = 'filelist';
        parent::__construct($section, $id);
        $this->section->kernel->useModule('filemanager');
    }

    function load_element()
    {
        $this->value['filelist_select_method'] = $this->parameter->get('filelist_select_method');
        $this->value['caption'] = $this->parameter->get('caption');

        if (false) { // benytter keyword

            // Dette skal lige implementeres, s� hvis man har filemanager, og har benyttet n�gleord, s�
            // skal array returneres ved hj�lp af Filemanager. V�r opm�rksom p� hvis en bruger der ikke har
            // Filemanager ser elementet, lavet af en der har filemanager. her i vis, skal der nok overrules om
            // brugeren har filemanager.
            $this->value['keyword_id'] = $this->parameter->get('keyword_id');

            $filemanager = new Intraface_modules_filemanager_FileManager($this->kernel);
            $filemanager->getDBQuery()->setKeyword($this->value['keyword_id']);
            $files = $filemanager->getList();
        } else { // Enkeltfiler
            $shared_filehandler = $this->kernel->useModule('filemanager');
            $shared_filehandler->includeFile('AppendFile.php');
            $append_file = new AppendFile($this->kernel, 'cms_element_filelist', $this->id);
            $files = $append_file->getList();
        }

        $i = 0;
        foreach ($files as $file) {
            if (isset($file['file_handler_id'])) {
                $id = $file['file_handler_id'];
                $append_file_id = $file['id'];
            } else {
                $id = $file['id'];
                $append_file_id = 0;
            }

            $filehandler = new FileHandler($this->kernel, $id);
            // @todo this should be avoided
            $filehandler->createInstance();
            // HACK lille hack - til at undg� at vi f�r filer med som ikke har beskrivelser (formentlig slettede filer)
            if (!$filehandler->get('description')) {
                continue;
            }
            $this->value['files'][$i] = $filehandler->get();
            $this->value['files'][$i]['append_file_id'] = $append_file_id;
            // $this->value['pictures'][$i]['show_uri'] = $file_uri;

            $i++;
        }

        return 1;
    }

    function validate_element($var)
    {
        $validator = new Intraface_Validator($this->error);
        $validator->isString($var['caption'], 'error in caption', '', 'allow_empty');

        /*
        if (!empty($var['files']) AND !is_array($var['files'])) {
            $this->error->set('error in files - has to be an array');
        }
        if (!empty($var['filelist_select_method']) AND !in_array($var['filelist_select_method'], $this->select_methods)) {
            $this->error->set('error in filelist_select_method');
        }
        */
        // egentlig b�r de enkelte v�rdier i arrayet ogs� valideres

        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    function save_element($var)
    {
        $var['caption'] = strip_tags($var['caption']);

        if (!$this->validate_element($var)) {
            return 0;
        }

        settype($var['caption'], 'string');
        $this->parameter->save('caption', $var['caption']);
        // $this->parameter->save('chosen_files', serialize($var['files']));
        settype($var['filelist_select_method'], 'string');
        $this->parameter->save('chosen_files', $var['filelist_select_method']);
        return true;
    }
}
