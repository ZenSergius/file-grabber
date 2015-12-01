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
 * Grabber class which implements structure of AbstractGrabber for getting the file content form the url.
 * This class uses the 'Curl' extension for grabbing files.
 *
 * @author Sergij Nazarenko <serg.progr@gmail.com>
 */
class CurlGrabber extends AbstractGrabber
{
    /**
     * Check if the 'curl' extension is available.
     * 
     * @throws GrabberException If 'curl' extension is not available.
     */
    public function __construct()
    {
        if (!$this->isAvailable()) {
            throw new GrabberException('Can\'t find curl extension, maybe it turned off.');
        }

        $this->grabberName = 'curl';
    }

    /**
     * {@inheritdoc}
     */
    protected function isAvailable()
    {
        return extension_loaded('curl') ? true : false;
    }

    /**
     * Grabs file content from given url.
     * 
     * @param string $fileUrl Url where the file should exists
     * @return string Content of the file from the url.
     * @throws \RuntimeException If can not initalize 'curl'
     * @throws \InvalidArgumentException If can not get the content form the url.
     */
    public function grabFile($fileUrl)
    {
        $ch = curl_init();

        if (!$ch)
        {
            throw new \RuntimeException(curl_error($ch));
        }

        curl_setopt($ch, CURLOPT_URL, $fileUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $file_content = curl_exec($ch);
        if ($file_content === FALSE)
        {
            throw new \InvalidArgumentException(curl_error($ch));
        }

        curl_close($ch);

        return $file_content;
    }

}