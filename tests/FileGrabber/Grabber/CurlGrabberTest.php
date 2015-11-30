<?php

namespace {
    $testFunc_extension_loaded = false;
}
namespace FileGrabber\Grabber {

    function extension_loaded($param)
    {
        global $testFunc_extension_loaded;
        if (isset($testFunc_extension_loaded) && $testFunc_extension_loaded === true) {
            return false;
        } else {
            return call_user_func('\extension_loaded', $param);
        }
    }

    class CurlGrabberTest extends \PHPUnit_Framework_TestCase
    {

        public function setUp()
        {
            global $testFunc_extension_loaded;
            $testFunc_extension_loaded = false;
        }

        public function testAvailable()
        {
            global $testFunc_extension_loaded;
            $testFunc_extension_loaded = true;
            $this->setExpectedException('FileGrabber\Grabber\GrabberException');
            $grabber = new CurlGrabber();
        }

        public function testCurlByUrl()
        {
            $grabber = new CurlGrabber();
            $this->setExpectedException('InvalidArgumentException');
            $grabber->grabFile('');
        }

    }

}