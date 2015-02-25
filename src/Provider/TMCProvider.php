<?php

namespace Swap\Provider;
use Swap\Exception\Exception;
use Swap\Model\CurrencyPair;
use Swap\Model\Rate;

class TMCProvider extends AbstractProvider
{
	const URL = 'http://themoneyconverter.com/%s/%s.aspx';

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
		$nodes = $xpath->query('//div[@class="switch-table"]/p/b');
		if (0 === $nodes->length) {
			throw new Exception('The currency is not supported or TMC changed the response format');
		}
		$bid = $nodes->item(0)->textContent;
		if (!is_numeric($bid)) {
			throw new Exception('The currency is not supported or TMC changed the response format');
		}
		return new Rate($bid, new \DateTime());
	}

}