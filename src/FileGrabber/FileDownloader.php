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

namespace FileGrabber;

use FileGrabber\Grabber\GetContentGrabber as GetContentGrabber;
use FileGrabber\Grabber\CurlGrabber as CurlGrabber;
use FileGrabber\Grabber\GrabberException as GrabberException;

/**
 * File downloader. It provides the ability to download files from a remote host.
 * 
 * @author Sergij Nazarenko <serg.progr@gmail.com>
 */
class FileDownloader
{

    private $grabber;
    private $defaultSavePath = 'images/';

    /**
     * Create instance of FileDownloader with the default save path and the grabber method parameters.
     *
     * @param string $defaultSavePath
     * @param string $grabMethod Grabber method. Can be 'getcontent', 'curl' or null
     */
    public function __construct($defaultSavePath = null, $grabMethod = null)
    {
        $this->setDefaultDir($defaultSavePath);
        $this->setGrabber($grabMethod);
    }

    /**
     * Sets default save directory.
     *
     * @param string $defaultSavePath
     */
    public function setDefaultDir($defaultSavePath)
    {
        if ($defaultSavePath) {
            $defaultSavePath = $this->fixSlashes($defaultSavePath);
            $this->defaultSavePath = $defaultSavePath;
        }

        $this->createDirPath($this->defaultSavePath);
    }

    /**
     * Gets default save directory.
     *
     * @return string Default save directory
     */
    public function getDefaultDir()
    {
        return $this->defaultSavePath;
    }

    /**
     * Creates directories from the given path if they doesn't exists
     * 
     * @param string $dirPath Given path
     * @throws \RuntimeException If can't create directory from the given path or can't set the propper access rights
     */
    private function createDirPath($dirPath)
    {
        if (file_exists($dirPath)) {
            if (!is_writable($dirPath)) {
                if (!chmod($dirPath, 0766)) {
                    throw new \RuntimeException('Can\'t set directory \'' . $dirPath . '\'' . 'writable.');
                }
            }
        } else {
            if (!mkdir($dirPath, 0766, true)) {
                throw new \RuntimeException('Can\'t create default directory ' . $dirPath);
            }

            if (!is_writable($dirPath)) {
                if (!chmod($dirPath, 0766)) {
                    throw new \RuntimeException('Can not set directory \'' . $dirPath . '\'' . 'writable.');
                }
            }
        }
    }

    /**
     * Set available grabber method if param $grabMethod is empty or set grabber method of given value
     *
     * @param string $grabMethod Can be 'getcontent' or 'curl' or null
     * @throws \InvalidArgumentException If can't find given grabber method
     * @throws \RuntimeException If have no one available method
     */
    private function setGrabber($grabMethod = null)
    {
        if (!empty($grabMethod)) {
            switch ($grabMethod) {
                case 'getcontent':
                    $this->grabber = new GetContentGrabber();
                    break;
                case 'curl':
                    $this->grabber = new CurlGrabber();
                    break;
                default:
                    throw new \InvalidArgumentException("Incorrect grab argument: $grabMethod");
            }
        } else {
            //let the system to choise available grabber
            try {
                $this->grabber = new GetContentGrabber();
            } catch (GrabberException $exGrabber) {
                try {
                    $this->grabber = new CurlGrabber();
                } catch (GrabberException $exGrabber) {
                    throw new \RuntimeException('Can\'t use: curl or file_get_contents methods to grab a file. You have to turn allow_url_open to On or turn On Curl extension');
                }
            }
        }
    }

    /**
     * Get current grabber method's name.
     * 
     * @return string
     */
    public function getGrabberName()
    {
        return $this->grabber->getName();
    }

    /**
     * Download a file from a remote url and save the file into selected directory or into default directory
     *
     * @param string $fileUrl Url where the file should exists
     * @param string $savePath Can be empty, in this case the file will be saved into default directory
     * @return string Succesfully created filename with an extension without the path
     * @throws \RuntimeException If method can't download the file.
     */
    public function download($fileUrl, $savePath = null)
    {
        if (empty($savePath)) {
            $savePath = $this->defaultSavePath;
        } else {
            $savePath = $this->fixSlashes($savePath);
            $this->createDirPath($savePath);
        }

        $fileContent = $this->grabber->grabFile($fileUrl);
        $urlInfo = pathinfo($fileUrl);

        $fileExt = $this->getFileExtensionByContent($fileContent);

        if ($urlInfo['filename'] and preg_match('~^[-_\w\d]+$~isu', $urlInfo['filename'])) {
            $fileName = $urlInfo['filename'].$fileExt;
        } else {
            $fileName = substr(md5(uniqid(rand(), true)), 0, rand(7, 13));
            $fileName .= $fileExt;
        }

        if (false === file_put_contents($savePath . $fileName, $fileContent)) {
            throw new \RuntimeException('Can\'t save file \''.$fileName.'\' to the selected path \'' . $savePath . '\'');
        }

        return $fileName;
    }

    /**
     * Get the a file extension from the given content.
     * And return the extension if it is available for use.
     * Only available JPG, GIF, PNG.
     * 
     * @param string $fileContent Content of the file
     * @return string Return file extension.
     * @throws \InvalidArgumentException If the file extension is incorrect.
     */
    private function getFileExtensionByContent($fileContent)
    {
        $img = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $img->buffer($fileContent);
        $fileExt = '';

        $filesExtensions = array(
            'image/jpeg' => '.jpg',
            'image/gif' => '.gif',
            'image/png' => '.png'
        );

        if (isset($filesExtensions[$mime])) {
            $fileExt = $filesExtensions[$mime];
        } else {
            throw new \InvalidArgumentException('Incorrect file extension');
        }

        return $fileExt;
    }

    /**
     * Replace all multiple slashes and backslash in string with single slash.
     * Also, deletes the last slash.
     *
     * @param string $path
     * @return string
     */
    protected function fixSlashes($path)
    {
        $fixedSlashes = preg_replace('~([/\\\\])+~', '/', $path);
        $fixedSlashes = rtrim($fixedSlashes, '/');
        $fixedSlashes .= '/';

        return $fixedSlashes;
    }
}