<?php
/**
 * Shared configuration for FileImport
 */

class SharedFileImport extends Shared {

    function __construct() {
        $this->shared_name = 'fileimport'; // Navn p� p� mappen med shared
        $this->active = 1; // Er shared aktivt
        
        $this->addPreloadFile('FileImport.php');
    }
}
?>