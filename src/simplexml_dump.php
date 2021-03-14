<?php

/**
 * Output a summary of the node or list of nodes referenced by a particular SimpleXML object
 * Rather than attempting a recursive inspection, presents statistics aimed at understanding
 * what your SimpleXML code is doing.
 *
 * @param SimpleXMLElement $sxml The object to inspect
 * @param boolean $return Default false. If true, return the "dumped" info rather than echoing it
 * @return null|string Nothing, or output, depending on $return param
 *
 * @author Rowan Collins
 * @see https://github.com/IMSoP/simplexml_debug
 * @license None. Do what you like with it, but please give me credit if you like it. :)
 * Equally, no warranty: don't blame me if your aircraft or nuclear power plant fails because of this code!
 */
function simplexml_dump(SimpleXMLElement $sxml, $return=false)
{
	$indent = "\t";

	// Get all the namespaces declared at the *root* of this document
	// All the items we're looking at are in the same document, so we only need do this once
	$doc_ns = $sxml->getDocNamespaces(false);
	
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
		$dom_item = dom_import_simplexml($item);

		// To what namespace does this element or attribute belong? Returns array( alias => URI )
		$item_ns_alias = $dom_item->prefix;
		$item_ns_uri = $dom_item->namespaceURI;

		if ( $dom_item instanceOf DOMAttr )
		{
			$dump .= $indent . 'Attribute {' . PHP_EOL;

			if ( ! is_null($item_ns_uri) )
			{
				$dump .= $indent . $indent . 'Namespace: \'' . $item_ns_uri . '\'' . PHP_EOL;
				if ( $item_ns_alias == '' )
				{
					$dump .= $indent . $indent . '(Default Namespace)' . PHP_EOL;
				}
				else
				{
					$dump .= $indent . $indent . 'Namespace Alias: \'' . $item_ns_alias . '\'' . PHP_EOL;
				}
			}

			$dump .= $indent . $indent . 'Name: \'' . $item->getName() . '\'' . PHP_EOL;
			$dump .= $indent . $indent . 'Value: \'' . (string)$item . '\'' . PHP_EOL;

			$dump .= $indent . '}' . PHP_EOL;
		}
		else
		{
			$dump .= $indent . 'Element {' . PHP_EOL;

			if ( ! is_null($item_ns_uri) )
			{
				$dump .= $indent . $indent . 'Namespace: \'' . $item_ns_uri . '\'' . PHP_EOL;
				if ( $item_ns_alias == '' )
				{
					$dump .= $indent . $indent . '(Default Namespace)' . PHP_EOL;
				}
				else
				{
					$dump .= $indent . $indent . 'Namespace Alias: \'' . $item_ns_alias . '\'' . PHP_EOL;
				}
			}

			$dump .= $indent . $indent . 'Name: \'' . $item->getName() . '\'' . PHP_EOL;
			// REMEMBER: ALWAYS CAST TO STRING! :)
			$dump .= $indent . $indent . 'String Content: \'' . (string)$item . '\'' . PHP_EOL;

			// Now some statistics about attributes and children, by namespace

			// This returns all namespaces used by this node and all its descendants,
			// 	whether declared in this node, in its ancestors, or in its descendants
			$all_ns = $item->getNamespaces(true);
			$has_default_namespace = isset($all_ns['']);

			// If the default namespace is never declared, we need to add a dummy entry for it
			// We also need to handle the odd fact that attributes are never assigned to the default namespace
			// The spec basically leaves their meaning undefined: https://www.w3.org/TR/xml-names/#defaulting
			if ( ! in_array(null, $all_ns, true) )
			{
				$all_ns[] = null;
			}

			// Prioritise "current" namespace by merging into onto the beginning of the list
			// (it will be added to the beginning and the duplicate entry dropped)
			$all_ns = array_unique(array_merge(
				array($item_ns_uri),
				$all_ns
			));

			foreach ( $all_ns as $ns_uri )
			{
				$children = $item->children($ns_uri);
				$attributes = $item->attributes($ns_uri);

				// Don't show children(null) if we have a default namespace defined
				if ( $has_default_namespace && $ns_uri === null )
				{
					$children = array();
				}

				// Don't show zero-counts, as they're not that useful
				if ( count($children) == 0 && count($attributes) == 0 )
				{
					continue;
				}

				$ns_label = ($ns_uri === null) ? 'Null Namespace' : "Namespace '$ns_uri'";
				$dump .= $indent . $indent . 'Content in ' . $ns_label . PHP_EOL;

				if ( count($children) > 0 )
				{
					// Count occurrence of child element names, rather than listing them all out
					$child_names = array();
					foreach ( $children as $sx_child )
					{
						// Below is a rather clunky way of saying $child_names[ $sx_child->getName() ]++;
						// 	which avoids Notices about unset array keys
						$child_node_name = $sx_child->getName();
						if ( array_key_exists($child_node_name, $child_names) )
						{
							$child_names[$child_node_name]++;
						}
						else
						{
							$child_names[$child_node_name] = 1;
						}
					}
					ksort($child_names);
					$child_name_output = array();
					foreach ( $child_names as $name => $count )
					{
						$child_name_output[] = "$count '$name'";
					}

					$dump .= $indent . $indent . $indent . 'Children: ' . count($children);
					$dump .= ' - ' . implode(', ', $child_name_output);
					$dump .= PHP_EOL;
				}

				if ( count($attributes) > 0 )
				{
					// Attributes can't be duplicated, but I'm going to put them in alphabetical order
					$attribute_names = array();
					foreach ( $attributes as $sx_attribute )
					{
						$attribute_names[] = "'" . $sx_attribute->getName() . "'";
					}
					ksort($attribute_names);
					$dump .= $indent . $indent . $indent . 'Attributes: ' . count($attributes);
					// Don't output a trailing " - " if there are no attributes
					if ( count($attributes) > 0 )
					{
						$dump .= ' - ' . implode(', ', $attribute_names);
					}
					$dump .= PHP_EOL;
				}
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
