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

namespace FileGrabber\Grabber;

/**
 * Base Grabber class providing Grabber structure.
 * @author Sergij Nazarenko <serg.progr@gmail.com>
 */
abstract class AbstractGrabber
{
    protected $grabberName;
    
    /**
     * Grabs file content from given url.
     *
     * @param string $fileUrl Url where the file should exists
     * @return string Content of the file from the url.
     */
    abstract public function grabFile($fileUrl);

    /**
     * Checks if the service for grabbing contect is available.
     *
     * @return Boolean Return true if service is available
     */
    abstract protected function isAvailable();

    /**
     * Gets current grabber method's name.
     *
     * @return string Can return 'getcontent' or 'curl'.
     */
    public function getName()
    {
        return $this->grabberName;
    }
}