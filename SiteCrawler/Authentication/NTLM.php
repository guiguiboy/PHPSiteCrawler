<?php 

namespace SiteCrawler\Authentication;

use SiteCrawler\Authentication;

class NTLM extends Authentication
{
	/**
	 * Process
	 * Return curl handle
	 * 
	 * @return Ressource
	 */
	public function process()
	{
		$curl = curl_init();
		curl_setopt_array($curl, $this->curlOptions);
		curl_exec($curl);
		return $curl;
	}
}