<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Standard.php';
require_once 'Intraface/Kernel.php';
require_once 'Intraface/shared/filehandler/FileHandler.php';

class FakeFileHandlerIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeFileHandlerUser
{
    function get()
    {
        return 1;
    }
}

class FileHandlerTest extends PHPUnit_Framework_TestCase
{
    private $file_name = 'tester.jpg';

    function createKernel()
    {
        $kernel = new Kernel;
        $kernel->intranet = new FakeFileHandlerIntranet;
        $kernel->user = new FakeFileHandlerUser;
        return $kernel;
    }

    function createFileHandler()
    {
        return new FileHandler($this->createKernel());
    }

    function createFile()
    {
        $data = array('file_name' => $this->file_name);
        $filehandler = $this->createFileHandler();
        $this->assertTrue($filehandler->update($data) > 0);
        return $filehandler;
    }

    ////////////////////////////////////////////////////////////////

    function testConstruction()
    {
        $filehandler = $this->createFileHandler();
        $this->assertTrue(is_object($filehandler));
    }

    function testFactoryReturnsAValidFileHandlerObject()
    {
        $fh = $this->createFile();
        $accesskey = $fh->getAccessKey();
        $filehandler = FileHandler::factory($this->createKernel(), $accesskey);
        $this->assertTrue(is_object($filehandler));
    }

    function testUpdate()
    {
        $fh = $this->createFile();
        $this->assertEquals($this->file_name, $fh->get('file_name'));
    }

    function testDelete()
    {
        // @todo how do we test precisely that it is deleted
        $fh = $this->createFile();
        $id = $fh->getId();

        $fh = new FileHandler($this->createKernel(), $id);
        $this->assertTrue($fh->delete());
    }

    function testUnDelete()
    {
        // @todo how do we test precisely that it is undeleted
        $fh = $this->createFile();
        $fh->delete();
        $this->assertTrue($fh->undelete());

    }

    function testSave()
    {
        $fh = new FileHandler($this->createKernel());
        $id = $fh->save(dirname(__FILE__) . '/wideonball.jpg', 'Filename');
        $fh->error->view();
        $this->assertTrue($id > 0);
    }
}
?>