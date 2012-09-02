<?php

/**
 * Output a summary of the node or list of nodes referenced by a particular SimpleXML object
 * Rather than attempting a recursive inspection, presents statistics aimed at understanding
 * what your SimpleXML code is doing.
 *
 * @param SimpleXMLElement $sxml The object to inspect
 * @param boolean $return Default false. If true, return the "dumped" info rather than echoing it
 * @return null|string Nothing, or output, depending on $return param
 */
function simplexml_dump(SimpleXMLElement $sxml, $return=false)
{
	$indent = "\t";

	$dump = '';
	// Note that the header is added at the end, so we can add stats
	$dump .= '[' . PHP_EOL;

	// SimpleXML objects can be either a single node, or (more commonly) a list of 0 or more nodes
	// I haven't found a reliable way of distinguishing between the two cases
	// Note that for a single node, foreach($node) acts like foreach($node->children())
	// Numeric array indexes, however, operate consistently: $node[0] just returns the node
	$item_index = 0;
	while ( isset($sxml[$item_index]) )
	{
		$item = $sxml[$item_index];
		$item_index++;

		// It's surprisingly hard to find something which behaves consistently differently for an attribute and an element within SimpleXML
		// The below relies on the fact that the DOM makes a much clearer distinction
		// Note that this is not an expensive conversion, as we are only swapping PHP wrappers around an existing LibXML resource
		if ( dom_import_simplexml($item) instanceOf DOMAttr )
		{
			$dump .= $indent . 'Attribute {' . PHP_EOL;

			// To what namespace does this attribute belong? Returns array( alias => URI )
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
			$dump .= $indent . $indent . 'Value: \'' . (string)$item . '\'' . PHP_EOL;

			$dump .= $indent . '}' . PHP_EOL;
		}
		else
		{
			$dump .= $indent . 'Element {' . PHP_EOL;

			// To what namespace does this element belong? Returns array( alias => URI )
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
			// REMEMBER: ALWAYS CAST TO STRING! :)
			$dump .= $indent . $indent . 'String Content: \'' . (string)$item . '\'' . PHP_EOL;

			// Now some statistics about attributes and children, by namespace

			// This returns all namespaces used by this node and all its descendants,
			// 	whether declared in this node, in its ancestors, or in its descendants
			$all_ns = $item->getNamespaces(true);
			// If the default namespace is never declared, it will never show up using the below code
			// This will still mess up in the case where a parent element is missing the xmlns declaration,
			//	but a descendant adds it, because SimpleXML will look ahead and fill $all_ns[''] incorrectly
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

	if ( $return )
	{
		return $dump;
	}
	else
	{
		echo $dump;
	}
}
