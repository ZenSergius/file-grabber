<?php
namespace FileGrabber\Grabber;

class CurlGrabber extends AbstractGrabber
{
    public function __construct()
    {
        //TODO: or function_exists('curl_version')
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
        $ch = curl_init($fileUrl);

        if (!$ch)
        {
            throw new \LogicException(curl_error($ch));
        }
        
        //$file = fopen('images/'.'curl_'.$savePath, 'wb');
        //curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //CURLOPT_RETURNTRANSFER

        $file_content = curl_exec($ch);
        if ($file_content === FALSE)
        {
            throw new \LogicException(curl_error($ch));
        }

        curl_close($ch);
        //fclose($file);

        return $file_content;
    }

}