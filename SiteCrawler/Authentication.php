<?php 

namespace SiteCrawler;

abstract class Authentication
{
	protected $curlOptions = array();
	
	/**
	 * Constructor
	 * 
	 * @param array $curlOptions
	 */
	public function __construct(array $curlOptions)
	{
		$this->curlOptions = $this->getDefaultOptions() + $curlOptions;
	}

	/**
	 * Retourne les valeurs par défaut
	 * 
	 * @return array
	 */
	protected function getDefaultOptions()
	{
		return array(
			CURLOPT_HEADER => 1,
			CURLOPT_NOBODY => 1,
		);
	}
}