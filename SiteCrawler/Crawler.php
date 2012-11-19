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
		
		foreach ($this->parseResponse($response) as $link)
			$this->crawl($link);
	}
	
	/**
	 * Parses the response and returns an array with urls.
	 * 
	 * @param string $response
	 * 
	 * @return array
	 */
	protected function parseResponse($response)
	{
		if ($response == "")
			return array();

		$links    = array();
		$document = new \DOMDocument();
		libxml_use_internal_errors(true);
		$document->loadHTML($response);
		libxml_use_internal_errors(false);
		
		foreach($document->getElementsByTagName('a') as $a) 
		{
			$link = $a->getAttribute('href');
			if (preg_match('/^.*\.(jpg|jpeg|png|gif|js|tiff|css|ico)$/i', $link))
				continue;
			if (preg_match('/^mailto:/i', $link))
				continue;

			if (!preg_match('|https?://|', $link))
				$link = $this->siteUrl . $link;
			
			if (!strstr($link, $this->siteUrl))
				continue;
			
        	$links[$link] = true;
		}
		return array_keys($links);
	}
}