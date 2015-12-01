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
class GetContentGrabber extends AbstractGrabber
{
    /**
     * Check if the 'allow_url_fopen' is turned 'On'.
     *
     * @throws GrabberException If 'allow_url_fopen' extension is turned 'Off'.
     */
    public function __construct()
    {
        if (!$this->isAvailable()) {
            throw new GrabberException('Can\'t use file_get_content because allow_url_fopen turned off.');
        }

        $this->grabberName = 'getcontent';
    }

    /**
     * {@inheritdoc}
     */
    protected function isAvailable()
    {
        return ini_get('allow_url_fopen') ? true : false;
    }

    /**
     * Grabs file content from given url.
     *
     * @param string $fileUrl Url where the file should exists
     * @return string Content of the file from the url.
     * @throws \InvalidArgumentException If can not get the content form the url.
     */
    public function grabFile($fileUrl)
    {
        try {
            $file_content = file_get_contents($fileUrl);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException("Unable to get data from: $fileUrl");
        }

        return $file_content;
    }

}