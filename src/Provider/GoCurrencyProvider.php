<?php

namespace Swap\Provider;
use Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

class GoCurrencyProvider extends AbstractProvider
{
	const URL = 'http://www.gocurrency.com/v2/dorate.php?inV=1&from=%s&to=%s&Calculate=Convert';

/**
 * {@inheritdoc}
 */
	public function fetchRate(CurrencyPair $currencyPair)
	{
		$url = sprintf(self::URL, $currencyPair->getBaseCurrency(), $currencyPair->getQuoteCurrency());
		$content = $this->httpAdapter->get($url)->getBody()->getContents();
		$document = new \DOMDocument();
		@$document->loadHTML($content);
		$xpath = new \DOMXPath($document);
		$nodes = $xpath->query('//div[@id="converter_results"]/ul//li[position()=last()]');
		if (0 === $nodes->length) {
			throw new Exception('The currency is not supported or GoCurrency changed the response format');
		}
		$nodeContent = $nodes->item(0)->textContent;
		preg_match ("/1 {$currencyPair->getBaseCurrency()} = (.*) {$currencyPair->getQuoteCurrency()}/", $nodeContent, $matches);
		if (empty ($matches[1]) || !is_numeric($matches[1])) {
			throw new Exception('The currency is not supported or GoCurrency changed the response format');
		}
		return new Rate($matches[1], new \DateTime());
	}

}