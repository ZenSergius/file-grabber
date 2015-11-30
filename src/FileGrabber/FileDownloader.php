<?php
namespace FileGrabber;

use FileGrabber\Grabber\GetContentGrabber as GetContentGrabber;
use FileGrabber\Grabber\CurlGrabber as CurlGrabber;
use FileGrabber\Grabber\GrabberException as GrabberException;

class FileDownloader
{

    private $grabber;
    private $defaultSavePath = 'images/';
    private $fileInfo;

    public function __construct($defaultSavePath = null, $grabMethod = null)
    {
        $this->setDefaultDir($defaultSavePath);
        $this->setGrabber($grabMethod);
        $this->fileInfo = new \finfo();
    }

    public function setDefaultDir($defaultSavePath)
    {
        if ($defaultSavePath) {
            $defaultSavePath = $this->fixSlashes($defaultSavePath);
            $this->defaultSavePath = $defaultSavePath;
        }

        $this->createDirPath($this->defaultSavePath);
    }

    public function getDefaultDir()
    {
        return $this->defaultSavePath;
    }

    private function createDirPath($dirPath)
    {
        if (file_exists($dirPath)) {
            if (!is_writable($dirPath)) {
                if (!chmod($dirPath, 0766)) {//die('aaaaaa');
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

    public function getGrabberName()
    {
        return $this->grabber->getName();
    }

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

    protected function fixSlashes($path)
    {
        $fixedSlashes = preg_replace('~([/\\\\])+~', '/', $path);
        $fixedSlashes = trim($fixedSlashes, '/');
        $fixedSlashes .= '/';

        return $fixedSlashes;
    }
}