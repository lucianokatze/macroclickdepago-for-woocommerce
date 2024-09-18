<?php

class SHA256Encript
{
    public function Generate($ipAddress, $secretKey, $comercio, $sucursal, $amount)
    {
        $ipAddress = $this->getRealIpAddr();

        $input = sprintf("%s*%s*%s*%s*%s", $ipAddress, $comercio, $sucursal, $amount, $secretKey);
        $inputArray = utf8_encode($input);
          $hashedArray = unpack('C*', hash( "sha256", $inputArray, true));
        $string = null;
        for ($i = 1; $i <= count($hashedArray); $i++) {
            $string .= str_pad(strtolower(dechex($hashedArray[$i])), 2, '0', STR_PAD_LEFT);
        }
        return $string;
    }

    private function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            return $_SERVER['HTTP_CLIENT_IP'];

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        return $_SERVER['REMOTE_ADDR'];
    }
}