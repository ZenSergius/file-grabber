<?php

/**
 * This file is part of the FileGrabber package.
 *
 * (c) Sergij Nazarenko <serg.progr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @package FileGrabber
 */

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

        /**
         * @covers GetContentGrabber::isAvailable
         * @global boolean $testFunc_extension_loaded
         */
        public function testAvailable()
        {
            global $testFunc_ini_get;
            $testFunc_ini_get = true;
            $this->setExpectedException('FileGrabber\Grabber\GrabberException');
            $grabber = new GetContentGrabber();
        }

        /**
         * @covers GetContentGrabber::grabFile
         */
        public function testGetContentByUrl()
        {
            $grabber = new GetContentGrabber();
            $this->setExpectedException('InvalidArgumentException');
            $grabber->grabFile('');
        }

    }

}