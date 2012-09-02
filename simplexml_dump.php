<?php

function simplexml_dump(SimpleXMLElement $sxml)
{
	$indent = "\t";

	$dump = '';
	// Note that the header is added at the end, so we can add stats
	$dump .= '[' . PHP_EOL;

	$item_index = 0;
	while ( isset($sxml[$item_index]) )
	{
		$item = $sxml[$item_index];
		$item_index++;

		// It's surprisingly hard to find something which behaves consistently differently for an attribute and an element within SimpleXML
		// The below relies on the fact that the DOM makes a much clearer distinction
		if ( dom_import_simplexml($item) instanceOf DOMAttr )
		{
			$dump .= $indent . 'Attribute {' . PHP_EOL;

			$ns = $item->getNamespaces(false);
			if ( $ns )
			{
				$dump .= $indent . $indent . 'Namespace: \'' . reset($ns) . '\'' . PHP_EOL;
				$dump .= $indent . $indent . 'Namespace Alias: \'' . key($ns) . '\'' . PHP_EOL;
			}

			$dump .= $indent . $indent . 'Name: \'' . $item->getName() . '\'' . PHP_EOL;
			$dump .= $indent . $indent . 'Value: \'' . (string)$item . '\'' . PHP_EOL;

			$dump .= $indent . '}' . PHP_EOL;
		}
		else
		{
			$dump .= $indent . 'Element {' . PHP_EOL;

			$ns = $item->getNamespaces(false);
			if ( $ns )
			{
				$dump .= $indent . $indent . 'Namespace: \'' . reset($ns) . '\'' . PHP_EOL;
				if ( key($ns) == '' )
				{
					$dump .= $indent . $indent . '(Default Namespace)' . PHP_EOL;
				}
				else
				{
					$dump .= $indent . $indent . 'Namespace Alias: \'' . key($ns) . '\'' . PHP_EOL;
				}
			}

			$dump .= $indent . $indent . 'Name: \'' . $item->getName() . '\'' . PHP_EOL;
			$dump .= $indent . $indent . 'String Content: \'' . (string)$item . '\'' . PHP_EOL;

			// Now some statistics about attributes and children, by namespace

			$all_ns = $item->getNamespaces(true);
			// If the default namespace is never declared, it will never show up using the below code
			// This will still mess up in the case where a parent element is missing the xmlns declaration,
			//	but a child adds it, because SimpleXML will look ahead and fill $all_ns[''] incorrectly
			if ( ! array_key_exists('', $all_ns) )
			{
				$all_ns[''] = NULL;
			}

			foreach ( $all_ns as $ns_alias => $ns_uri )
			{
				$children = count($item->children($ns_uri));
				$attributes = count($item->attributes($ns_uri));

				// Don't show zero-counts, as they're not that useful
				if ( $children == 0 && $attributes == 0 )
				{
					continue;
				}

				$ns_label = (($ns_alias == '') ? 'Default Namespace' : "Namespace $ns_alias");
				$dump .= $indent . $indent . 'Content in ' . $ns_label . PHP_EOL;

				if ( ! is_null($ns_uri) )
				{
					$dump .= $indent . $indent . $indent . 'Namespace URI: \'' . $ns_uri . '\'' . PHP_EOL;
				}
				$dump .= $indent . $indent . $indent . 'Children: ' . $children . PHP_EOL;
				$dump .= $indent . $indent . $indent . 'Attributes: ' . $attributes . PHP_EOL;
			}

			$dump .= $indent . '}' . PHP_EOL;
		}
	}
	$dump .= ']' . PHP_EOL;

	// Add on the header line, with the total number of items output
	$dump = 'SimpleXML object (' . $item_index . ' item' . ($item_index > 1 ? 's' : '') . ')' . PHP_EOL . $dump;

	echo $dump;
}

$x = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <loofah />
  <soapenv:Body>
    <requestContactResponse xmlns="http://webservice.foo.com">
      <requestContactReturn>
        <errorCode xsi:nil="true"/>
        <errorDesc xsi:nil="true"/>
        <id>744</id>
        <soapenv:id>744</soapenv:id>
      </requestContactReturn>
    </requestContactResponse>
  </soapenv:Body>
</Envelope>
XML;

$sx = simplexml_load_string($x);

simplexml_dump($sx);
simplexml_dump($sx->children(NULL));
simplexml_dump($sx->children("soapenv", true)->Body);
simplexml_dump($sx->children("soapenv", true)->Body->children(NULL)->requestContactResponse->requestContactReturn->id);
simplexml_dump($sx->children("soapenv", true)->Body->children(NULL)->requestContactResponse->requestContactReturn->children('soapenv', true)->id);

