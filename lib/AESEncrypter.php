<?php
 class AESEncrypter
{
    public static function EncryptString($plainText, $phrase)
    {
	   if(strlen($phrase) < 32)
	   {
		   while(strlen($phrase) < 32)
		   {
			   $phrase .= $phrase;
		   }
		   $phrase = substr($phrase,0,32);
	   }
	   if(strlen($phrase) > 32)
	   {
		   $phrase = substr($phrase,0,32);	   
	   }
       $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
       $string = openssl_encrypt($plainText,"aes-256-cbc",$phrase, OPENSSL_RAW_DATA , $iv);
       return base64_encode($iv.$string);
    }

	public static function DecryptString($plainText, $phrase)
	{
		if(strlen($phrase) < 32)
		{
			while(strlen($phrase) < 32)
			{
				$phrase .= $phrase;
			}
			$phrase = substr($phrase,0,32);
		}
		if(strlen($phrase) > 32)
		{
			$phrase = substr($phrase,0,32);	   
		}
		
		$plainText = base64_decode($plainText);
		$encodedData = substr($plainText, openssl_cipher_iv_length('aes-256-cbc'));
		$iv = substr($plainText,0,openssl_cipher_iv_length('aes-256-cbc'));
		$decrypted = openssl_decrypt($encodedData, "aes-256-cbc", $phrase, OPENSSL_RAW_DATA, $iv);
		return $decrypted;
	}
}