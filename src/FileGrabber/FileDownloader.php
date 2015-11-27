<?php

namespace FileGrabber;

use FileGrabber\Grabber\GetContentGrabber as GetContentGrabber;
use FileGrabber\Grabber\CurlGrabber as CurlGrabber;

class FileDownloader
{
    protected $fileGrabber = null;
    private $grabber;
    private $defaultSavePath = 'images/';

    public function __construct($defaultSavePath, $grabMethod = 'getcontent')
    {
        $this->setOptions($defaultSavePath, $grabMethod);
    }

    public function setOptions($defaultSavePath, $grabMethod = 'getcontent')
    {
        $this->defaultSavePath = $defaultSavePath;

        switch ($grabMethod) {
            case 'getcontent':
                $this->grabber = new GetContentGrabber();
                break;
            case 'curl':
                $this->grabber = new CurlGrabber();
                break;
            default:
                throw new InvalidArgumentException("Incorrect grab argument: $grabMethod");
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