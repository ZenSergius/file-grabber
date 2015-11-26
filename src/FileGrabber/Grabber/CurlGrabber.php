<?php
namespace FileGrabber\Grabber;

class CurlGrabber extends AbstractGrabber
{
    public function grabFile($fileUrl, $savePath)
    {
        $ch = curl_init($fileUrl);
        $file = fopen('images/'.'curl_'.$savePath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($file);
    }

}