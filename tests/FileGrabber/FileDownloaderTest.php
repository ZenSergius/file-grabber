<?php
namespace {
    $testFunc_is_writable = false;
    $testFunc_chmod = false;
    $testFunc_mkdir = false;
}

namespace FileGrabber {

//use FileGrabber\FileDownloader as FileDownloader;
/*use FileGrabber\Grabber\GetContentGrabber as GetContentGrabber;
use FileGrabber\Grabber\CurlGrabber as CurlGrabber;
use FileGrabber\Grabber\GrabberException as GrabberException;*/

function is_writable($param) {
    global $testFunc_is_writable;
    if (isset($testFunc_is_writable) && $testFunc_is_writable === true) {
        return false;
    } else {
        return call_user_func('\is_writable', $param);
    }
}

function mkdir($param1, $param2, $param3) {
    global $testFunc_mkdir;
    if (isset($testFunc_mkdir) && $testFunc_mkdir === true) {
        return false;
    } else {
        return call_user_func('\mkdir', $param1, $param2, $param3);
    }
}

function chmod($param1, $param2) {
    global $testFunc_chmod;
    if (isset($testFunc_chmod) && $testFunc_chmod === true) {
        return false;
    } else {
        return call_user_func('\chmod', $param1, $param2);
    }
}

class FileDownloaderTest extends \PHPUnit_Framework_TestCase
{
    protected $fd_default;
    protected $fd_getcontent;
    protected $fd_curl;

    public function getFileContent($url)
    {
        if (ini_get('allow_url_fopen')) {
            $file_content = file_get_contents($url);
        } else if (extension_loaded('curl')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $file_content = curl_exec($ch);
            curl_close($ch);
        } else {
            $file_content = null;
            //no one of methods are available
        }

        return $file_content;
    }

    public function setUp()
    {
        global $testFunc_is_writable, $testFunc_chmod, $testFunc_mkdir;
        $testFunc_is_writable = false;
        $testFunc_chmod = false;
        $testFunc_mkdir = false;

        $this->fd_default = new FileDownloader();
        $this->fd_getcontent = new FileDownloader('', 'getcontent');
        $this->fd_curl = new FileDownloader('', 'curl');
    }

    public function tearDown()
    {
        unlink('images');
        unlink('images_sub_dir');
        unlimk('images-test');
    }

    public function testInstance()
    {
        $this->assertInstanceOf('FileGrabber\FileDownloader', $this->fd_default);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFileDownloaderInvalidMethod()
    {
        $fd = new FileDownloader('', 'invalid_grabber_method');
    }

    public function testSetProperMethod()
    {
        $this->assertNotNull($this->fd_default->getGrabberName());
        $this->assertAttributeInstanceOf('\finfo',
            'fileInfo', $this->fd_default);
        $this->assertEquals('getcontent', $this->fd_default->getGrabberName());
        $this->assertAttributeInstanceOf('FileGrabber\Grabber\AbstractGrabber',
            'grabber', $this->fd_default);
        
        $this->assertEquals('getcontent', $this->fd_getcontent->getGrabberName());
        $this->assertAttributeInstanceOf('FileGrabber\Grabber\AbstractGrabber',
            'grabber', $this->fd_getcontent);
        $this->assertAttributeInstanceOf('FileGrabber\Grabber\GetContentGrabber',
            'grabber', $this->fd_getcontent);

        $this->assertEquals('curl', $this->fd_curl->getGrabberName());
        $this->assertAttributeInstanceOf('FileGrabber\Grabber\AbstractGrabber',
            'grabber', $this->fd_curl);
        $this->assertAttributeInstanceOf('FileGrabber\Grabber\CurlGrabber',
            'grabber', $this->fd_curl);
    }

    public function testHasAttributes()
    {
        $this->assertObjectHasAttribute('grabber', $this->fd_default);
        $this->assertObjectHasAttribute('defaultSavePath', $this->fd_default);
    }

    public function testMethodAvailable()
    {
        $stub_content = $this->getMockBuilder('FileGrabber\Grabber\GetContentGrabber')
                     ->disableOriginalConstructor()
                     ->getMock();
        //$stub_content->method('isAvailable')->willReturn(false);
        $stub_content->method('getName')->willReturn(false);
        /*$stub_curl = $this->getMockBuilder('CurlGrabber')
                     ->disableOriginalConstructor()
                     ->getMock();
        $stub_curl->method('isAvailable')->willReturn(false);*/
        $fd = new FileDownloader();
        /*$this->assertAttributeInstanceOf('FileGrabber\Grabber\GetContentGrabber',
            'grabber', $fd);*/
        $this->assertEquals('getcontent', $fd->getGrabberName());
        $this->markTestIncomplete('Incomplete test for creating object');
    }

    public function testFixSlashes()
    {
        $this->fd_default->setDefaultDir('images_sub_dir');
        $this->assertEquals('images_sub_dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('/images/sub/dir/');
        $this->assertEquals('images/sub/dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('//images///sub///dir///');
        $this->assertEquals('images/sub/dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('/\\/images/\//sub/\/dir//\/');
        $this->assertEquals('images/sub/dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('\images\sub\dir\\');
        $this->assertEquals('images/sub/dir/', $this->fd_default->getDefaultDir());
    }

    public function testGetDefaultDir()
    {
        $this->assertEquals('images/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('images_new_dir');
        $this->assertEquals('images_new_dir/', $this->fd_default->getDefaultDir());
    }
    
    public function testDownloadFile()
    {
        $testUrl = 'https://www.wikipedia.org/portal/wikipedia.org/assets/img/Wikipedia-logo-v2_1x.png';
        $this->fd_default->download($testUrl);
        $testContent = $this->getFileContent($testUrl);
        file_put_contents('images/test_content.png', $testContent);
        $this->assertFileExists('images/Wikipedia-logo-v2_1x.png');
        $this->assertFileEquals('images/test_content.png', 'images/Wikipedia-logo-v2_1x.png');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWrongExtension()
    {
        $testUrl = 'https://www.wikipedia.org/';
        $this->fd_default->download($testUrl);
    }

    /**
     * $expectedException RuntimeException
     */
    public function testCreateDirpath1()
    {
        global $testFunc_is_writable, $testFunc_chmod;
        $testFunc_is_writable = true;
        $this->fd_default->setDefaultDir('test-path');
    }

    /**
     * $expectedException RuntimeException
     */
    public function testCreateDirpath2()
    {
        global $testFunc_is_writable, $testFunc_chmod;
        $testFunc_is_writable = true;
        $testFunc_chmod = true;
        $this->fd_default->setDefaultDir('test-path');
    }

    /**
     * $expectedException RuntimeException
     */
    public function testCreateDirpath3()
    {
        global $testFunc_is_writable, $testFunc_chmod, $testFunc_mkdir;
        $testFunc_is_writable = true;
        $testFunc_chmod = true;
        $testFunc_mkdir = true;
        $this->fd_default->setDefaultDir('images-test');
    }
}
}