<?php
namespace {
    $testFunc_is_writable = false;
    $testFunc_chmod = false;
    $testFunc_mkdir = false;
    $testFunc_file_put_contents = false;
}

namespace FileGrabber {

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

function file_put_contents($param1, $param2) {
    global $testFunc_file_put_contents;
    if (isset($testFunc_file_put_contents) && $testFunc_file_put_contents === true) {
        return false;
    } else {
        return call_user_func('\file_put_contents', $param1, $param2);
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
        }

        return $file_content;
    }

    public function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function setUp()
    {
        global $testFunc_is_writable, $testFunc_chmod, $testFunc_mkdir, $testFunc_file_put_contents;
        $testFunc_is_writable = false;
        $testFunc_chmod = false;
        $testFunc_mkdir = false;
        $testFunc_file_put_contents = false;

        $this->fd_default = new FileDownloader();
        $this->fd_getcontent = new FileDownloader('', 'getcontent');
        $this->fd_curl = new FileDownloader('', 'curl');
    }

    public function tearDown()
    {
        @$this->delTree('images');
        @$this->delTree('images_new_dir');
        @$this->delTree('images_sub_dir');
        @$this->delTree('images_test');
        @$this->delTree('images_test2');
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

    public function testFixSlashes()
    {
        $this->fd_default->setDefaultDir('images_sub_dir');
        $this->assertEquals('images_sub_dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('/images_test/sub/dir/');
        $this->assertEquals('images_test/sub/dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('//images_test///sub///dir///');
        $this->assertEquals('images_test/sub/dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('/\\/images_test/\//sub/\/dir//\/');
        $this->assertEquals('images_test/sub/dir/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('\images_test\sub\dir\\');
        $this->assertEquals('images_test/sub/dir/', $this->fd_default->getDefaultDir());
    }

    public function testGetDefaultDir()
    {
        $this->assertEquals('images/', $this->fd_default->getDefaultDir());
        $this->fd_default->setDefaultDir('images_new_dir');
        $this->assertEquals('images_new_dir/', $this->fd_default->getDefaultDir());
    }
    
    public function testDownloadFileDefault()
    {
        $testUrl = 'https://www.wikipedia.org/portal/wikipedia.org/assets/img/Wikipedia-logo-v2_1x.png';
        $file_name1 = $this->fd_default->download($testUrl);
        $testContent = $this->getFileContent($testUrl);
        file_put_contents('images/test_content.png', $testContent);
        $this->assertFileExists('images/Wikipedia-logo-v2_1x.png');
        $this->assertFileEquals('images/test_content.png', 'images/Wikipedia-logo-v2_1x.png');
        $this->assertEquals('Wikipedia-logo-v2_1x.png', $file_name1);

        $testUrl2 = 'http://www.englishpage.com/images/logoA.gif';
        $file_name2 = $this->fd_default->download($testUrl2, 'images/custom/subdir');
        $testContent2 = $this->getFileContent($testUrl2);
        file_put_contents('images/test_content2.png', $testContent2);
        $this->assertFileExists('images/custom/subdir/logoA.gif');
        $this->assertFileEquals('images/test_content2.png', 'images/custom/subdir/logoA.gif');
        $this->assertEquals('logoA.gif', $file_name2);
    }
    
    public function testDownloadFileContent()
    {
        $testUrl = 'https://www.wikipedia.org/portal/wikipedia.org/assets/img/Wikipedia-logo-v2_1x.png';
        $file_name1 = $this->fd_getcontent->download($testUrl);
        $testContent = $this->getFileContent($testUrl);
        file_put_contents('images/test_content.png', $testContent);
        $this->assertFileExists('images/Wikipedia-logo-v2_1x.png');
        $this->assertFileEquals('images/test_content.png', 'images/Wikipedia-logo-v2_1x.png');
        $this->assertEquals('Wikipedia-logo-v2_1x.png', $file_name1);

        $testUrl2 = 'http://www.englishpage.com/images/logoA.gif';
        $file_name2 = $this->fd_getcontent->download($testUrl2, 'images/custom/subdir');
        $testContent2 = $this->getFileContent($testUrl2);
        file_put_contents('images/test_content2.png', $testContent2);
        $this->assertFileExists('images/custom/subdir/logoA.gif');
        $this->assertFileEquals('images/test_content2.png', 'images/custom/subdir/logoA.gif');
        $this->assertEquals('logoA.gif', $file_name2);
    }

    public function testDownloadFileCurl()
    {
        $testUrl = 'https://www.wikipedia.org/portal/wikipedia.org/assets/img/Wikipedia-logo-v2_1x.png';
        $file_name1 = $this->fd_curl->download($testUrl);
        $testContent = $this->getFileContent($testUrl);
        file_put_contents('images/test_content.png', $testContent);
        $this->assertFileExists('images/Wikipedia-logo-v2_1x.png');
        $this->assertFileEquals('images/test_content.png', 'images/Wikipedia-logo-v2_1x.png');
        $this->assertEquals('Wikipedia-logo-v2_1x.png', $file_name1);

        $testUrl2 = 'http://www.englishpage.com/images/logoA.gif';
        $file_name2 = $this->fd_curl->download($testUrl2, 'images/custom/subdir');
        $testContent2 = $this->getFileContent($testUrl2);
        file_put_contents('images/test_content2.png', $testContent2);
        $this->assertFileExists('images/custom/subdir/logoA.gif');
        $this->assertFileEquals('images/test_content2.png', 'images/custom/subdir/logoA.gif');
        $this->assertEquals('logoA.gif', $file_name2);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWrongExtension()
    {
        $testUrl = 'https://www.wikipedia.org/';
        $this->fd_default->download($testUrl);
    }

    public function testCreateDirpath1()
    {
        global $testFunc_is_writable;
        $testFunc_is_writable = true;
        $this->fd_default->setDefaultDir('images_exists');
    }

    public function testCreateDirpath2()
    {
        global $testFunc_chmod, $testFunc_is_writable;
        $testFunc_is_writable = true;
        $testFunc_chmod = true;
        $this->setExpectedException('RuntimeException');
        $this->fd_default->setDefaultDir('images_exists');
    }

    public function testCreateDirpath3()
    {
        global $testFunc_is_writable, $testFunc_chmod, $testFunc_mkdir;
        $testFunc_is_writable = true;
        $testFunc_chmod = true;
        $testFunc_mkdir = true;
        $this->setExpectedException('RuntimeException');
        $this->fd_default->setDefaultDir('images_test');
    }

    public function testCreateDirpath4()
    {
        global $testFunc_is_writable, $testFunc_chmod;
        $testFunc_is_writable = true;
        $testFunc_chmod = true;
        $this->setExpectedException('RuntimeException');
        $this->fd_default->setDefaultDir('images_test2');
    }

    public function testCreateDirpath5()
    {
        global $testFunc_is_writable;
        $testFunc_is_writable = true;
        $this->fd_default->setDefaultDir('images_test2');
    }

    public function testFilePutContentsError()
    {
        global $testFunc_file_put_contents;
        $testFunc_file_put_contents = true;
        $testUrl = 'http://www.englishpage.com/images/logoA.gif';
        $this->setExpectedException('RuntimeException');
        $this->fd_default->download($testUrl);
    }

    public function testFilenameGenerator()
    {
        $testUrl='https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcQPlX4Pm4SggadSIpK1scen1q-vRMjF0-1lncaJpIpb_231KyPQLA';
        $filename = $this->fd_default->download($testUrl);
        $file_data = pathinfo($filename);
        $this->assertRegExp('~^[a-f0-9]+\.jpg$~i', $filename);
        $this->assertNotEmpty($filename);
    }

    public function testNoUrl1()
    {
        $this->setExpectedException('InvalidArgumentException');
        $filename = $this->fd_getcontent->download('');
    }

    public function testIncorrectUrl1()
    {
        $this->setExpectedException('InvalidArgumentException');
        $filename = $this->fd_getcontent->download('incorrect_site.com');
    }

    public function testNoUrl2()
    {
        $this->setExpectedException('InvalidArgumentException');
        $filename = $this->fd_curl->download('');
    }

    public function testIncorrectUrl2()
    {
        $this->setExpectedException('InvalidArgumentException');
        $filename = $this->fd_curl->download('incorrect_site.com');
    }
}
}