<?php

namespace SiteCrawler;

class Crawler
{
	protected $curlOptions = array();
	
	protected $authentication;
	
	protected $siteUrl;
	
	protected $urlBuffer = array();
	
	protected $alreadyVisited = array();
	
	protected $callbacks = array();
	
	/**
	 * Constructor
	 * 
	 * @param string $siteUrl
	 * @param array  $curlOptions
	 */
	public function __construct($siteUrl, $curlOptions = array())
	{
		$this->curlOptions = $curlOptions;
		$this->siteUrl = $siteUrl;
	}
	
	/**
	 * Sets Authentication method
	 * 
	 * @param Authentication $authentication
	 */
	public function setAuthentication(Authentication $authentication)
	{
		$this->authentication = $authentication;
	}

	/**
	 * Process GET
	 * 
	 * @param string $page
	 */
	protected function get($page)
	{
		curl_setopt($this->curl, CURLOPT_URL, $page);
		$resp = curl_exec($this->curl);
		return $resp;
	}

	/**
	 * Adds a callable
	 * 
	 * @param \Closure $callable
	 */
	public function addCallback(\Closure $callable)
	{
		$this->callbacks[] = $callable;
	}

	/**
	 * Processes the crawling
	 * 
	 * If an AUthentication is injected, then process it and get the curl
	 * Else do the curl_init with parameters
	 */
	public function process()
	{
		if ($this->authentication instanceof Authentication) {
			$this->curl = ($this->authentication->process());
		} else {
			$this->curl = curl_init();
		}
		
		curl_setopt_array($this->curl, $this->curlOptions);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_HEADER, 0);
		curl_setopt($this->curl, CURLOPT_COOKIESESSION, TRUE); 
		curl_setopt($this->curl, CURLOPT_NOBODY, false); 
		$this->crawl($this->siteUrl);
	}

	/**
	 * Crawl
	 * 
	 * @param string $page
	 */
	protected function crawl($page)
	{
		if (isset($this->alreadyVisited[$page]))
			return;
		
		printf("GET %s \n", $page);
		
		$response = $this->get($page);
		
		$this->alreadyVisited[$page] = true;
		
		foreach ($this->callbacks as $callback)
			$callback($response);
		
		$matches = array();
		preg_match_all('^<.*(src|href)=[\'"]{1}([[:alnum:]/_\.-]+)[\'"]{1}.*^i', $response, $matches);
		
		foreach ($matches[2] as $match)
		{
			if (preg_match('/^.*\.(jpg|jpeg|png|gif|js|tiff|css|ico)$/i', $match))
				continue;

			if (preg_match('|http[s]?://|', $match)) {
				$this->crawl($match);
			} else {
				$this->crawl($this->siteUrl . $match);
			}
		}
	}
}