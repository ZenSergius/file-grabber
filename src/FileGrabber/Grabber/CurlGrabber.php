<?php
namespace FileGrabber\Grabber;

class CurlGrabber extends AbstractGrabber
{
    public function __construct()
    {
        if (!$this->isAvailable()) {
            throw new GrabberException('Can\'t find curl extension, maybe it turned off.');
        }

        $this->grabberName = 'curl';
    }

    protected function isAvailable()
    {
        return extension_loaded('curl') ? true : false;
    }
    
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