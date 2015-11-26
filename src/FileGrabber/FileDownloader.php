<?php

namespace FileGrabber;

//require __DIR__ . '/Grabber/GetContentGrabber.php';
//require __DIR__ . '/Grabber/CurlGrabber.php';

class FileDownloader
{
    protected $fileGrabber = null;
    private $grabber;
    private $defaultSavePath = 'images';

    public function __construct($defaultSavePath, $grabMethod = 'getcontent')
    {
        $this->setOptions($defaultSavePath, $grabMethod);
    }

    public function setOptions($defaultSavePath, $grabMethod = 'getcontent')
    {
        $this->defaultSavePath = $defaultSavePath;

        switch ($grabMethod) {
            case 'getcontent':
                $this->grabber = new \FileGrabber\Grabber\GetContentGrabber;
                break;
            case 'curl':
                $this->grabber = new \FileGrabber\Grabber\CurlGrabber;
                break;
            default:
                throw new Exception("Incorrect grab method");
        }
    }

    public function download($fileUrl, $savePath = null)
    {
        if (!$savePath) {
            $savePath = $this->defaultSavePath;
        }

        $this->grabber->grabFile($fileUrl, $savePath);
    }
}