<?php
namespace FileGrabber\Grabber;

class GetContentGrabber extends AbstractGrabber
{
    public function __construct()
    {
        if (!$this->isAvailable()) {
            throw new GrabberException('Can\'t use file_get_content because allow_url_fopen turned off.');
        }

        $this->grabberName = 'getcontent';
    }

    protected function isAvailable()
    {
        return ini_get('allow_url_fopen') ? true : false;
    }

    public function grabFile($fileUrl)
    {
        $file_content = file_get_contents($fileUrl);

        if (!$file_content) {
            throw new \LogicException("Unable to get data from: $fileUrl");
        }

        return $file_content;
    }

}