<?php

namespace {
    $testFunc_ini_get = false;
}
namespace FileGrabber\Grabber {

    function ini_get($param)
    {
        global $testFunc_ini_get;
        if (isset($testFunc_ini_get) && $testFunc_ini_get === true) {
            return false;
        } else {
            return call_user_func('\ini_get', $param);
        }
    }

    class GetContentGrabberTest extends \PHPUnit_Framework_TestCase
    {

        public function setUp()
        {
            global $testFunc_ini_get;
            $testFunc_ini_get = false;
        }

        public function testAvailable()
        {
            global $testFunc_ini_get;
            $testFunc_ini_get = true;
            $this->setExpectedException('FileGrabber\Grabber\GrabberException');
            $grabber = new GetContentGrabber();
        }

        public function testGetContentByUrl()
        {
            $grabber = new GetContentGrabber();
            $this->setExpectedException('InvalidArgumentException');
            $grabber->grabFile('');
        }

    }

}