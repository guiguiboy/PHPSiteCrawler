<?php

$loader = require __DIR__.'/vendor/autoload.php';
$loader->add('SiteCrawler', __DIR__);

$crawlConfig = array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_USERAGENT => 'Mozilla/6.0 (Windows NT 6.2; WOW64; rv:16.0.1) Gecko/20121011 Firefox/16.0.1',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_COOKIEJAR => '/tmp/cookie.txt',
    CURLOPT_COOKIEFILE => '/tmp/cookie.txt',
);

$crawler = new SiteCrawler\Crawler('http://www.ecologic-france.com', $crawlConfig);
$crawler->process();