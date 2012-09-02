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
			$dump .= $indent . $indent . 'Structural Content:' . PHP_EOL;

			// If the default namespace is not declared, it will never show up using the below code
			// (This is probably bad XML, or at least bad practice, but the aim here is to leave nothing invisible)
			if ( ! array_key_exists('', $item->getDocNamespaces()) )
			{
				$dump .= $indent . $indent . $indent . '[Default, Undeclared Namespace]' . PHP_EOL;

				$dump .= $indent . $indent . $indent . $indent . 'Children: ' . count($item->children(NULL)) . PHP_EOL;
				$dump .= $indent . $indent . $indent . $indent . 'Attributes: ' . count($item->attributes(NULL)) . PHP_EOL;
			}

			$all_ns = $item->getNamespaces(true);
			foreach ( $all_ns as $ns_alias => $ns_uri )
			{
				$ns_label = (($ns_alias == '') ? '[Default Namespace]' : "Namespace $ns_alias");
				$dump .= $indent . $indent . $indent . $ns_label . PHP_EOL;

				$dump .= $indent . $indent . $indent . $indent . 'Namespace URI: \'' . $ns_uri . '\'' . PHP_EOL;
				$dump .= $indent . $indent . $indent . $indent . 'Children: ' . count($item->children($ns_uri)) . PHP_EOL;
				$dump .= $indent . $indent . $indent . $indent . 'Attributes: ' . count($item->attributes($ns_uri)) . PHP_EOL;
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
<?xml version="1.0"?>
<foo xmlns="http://example.com" xmlns:a="http://example.com/a" xmlns:b="http://example.com/b">
	<bar>some text</bar>
	<bar>ooo <![CDATA[some cdata yaha!]]></bar>
	<bar b:thing="Thing!" b:nowt="" />
	<deeper bob="jane">
		<a:bar />
		<a:bar />
	</deeper>
</foo>
XML;

$sx = simplexml_load_string($x);

simplexml_dump($sx);
simplexml_dump($sx->children());
simplexml_dump($sx->deeper);
simplexml_dump($sx->deeper->children('a', true));
simplexml_dump($sx->bar[2]->attributes('b', true));
simplexml_dump($sx->deeper['bob']);


