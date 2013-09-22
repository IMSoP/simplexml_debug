<?php

/**
 * Output a tree-view of the node or list of nodes referenced by a particular SimpleXML object
 * Unlike simplexml_dump(), this processes the entire XML tree recursively, while attempting
 * 	to be more concise and readable than the XML itself.
 * Additionally, the output format is designed as a hint of the syntax needed to traverse the object.  
 *
 * @param SimpleXMLElement $sxml The object to inspect
 * @param boolean $include_string_content Default false. If true, will summarise textual content, 
  * 	as well as child elements and attribute names
 * @param boolean $return Default false. If true, return the "dumped" info rather than echoing it
 * @return null|string Nothing, or output, depending on $return param
 *
 * @author Rowan Collins
 * @see https://github.com/IMSoP/simplexml_debug
 * @license None. Do what you like with it, but please give me credit if you like it. :)
 * Equally, no warranty: don't blame me if your aircraft or nuclear power plant fails because of this code!  
 */
function simplexml_tree(SimpleXMLElement $sxml, $include_string_content=false, $return=false)
{
	$indent = "\t";
	$content_extract_size = 15;

	// Get all the namespaces declared at the *root* of this document
	// All the items we're looking at are in the same document, so we only need do this once
	$doc_ns = $sxml->getDocNamespaces(false);
	
	$dump = '';
	// Note that the header is added at the end, so we can add stats

	// The initial object passed in may be a single node or a list of nodes, so we need an outer loop first
	// Note that for a single node, foreach($node) acts like foreach($node->children())
	// Numeric array indexes, however, operate consistently: $node[0] just returns the node
	$root_item_index = 0;
	while ( isset($sxml[$root_item_index]) )
	{
		$root_item = $sxml[$root_item_index];
		
		// Special case if the root is actually an attribute
		// It's surprisingly hard to find something which behaves consistently differently for an attribute and an element within SimpleXML
		// The below relies on the fact that the DOM makes a much clearer distinction
		// Note that this is not an expensive conversion, as we are only swapping PHP wrappers around an existing LibXML resource
		if ( dom_import_simplexml($root_item) instanceOf DOMAttr )
		{
			// To what namespace does this attribute belong? Returns array( alias => URI )
			$ns = $root_item->getNamespaces(false);
			if ( key($ns) )
			{
				$dump .= key($ns) . ':';
			}
			$dump .=  $root_item->getName() . '="' . (string)$root_item . '"' . PHP_EOL;
		}
		else
		{
			// Display the root node as a numeric key reference, plus a hint as to its tag name 
			// e.g. '[42] // <Answer>'
			
			// To what namespace does this attribute belong? Returns array( alias => URI )
			$ns = $root_item->getNamespaces(false);
			if ( key($ns) )
			{
				$root_node_name = key($ns) . ':' . $root_item->getName();
			}
			else
			{
				$root_node_name = $root_item->getName();
			}
			$dump .=  "[$root_item_index] // <$root_node_name>" . PHP_EOL;
			
			// This function is effectively recursing depth-first through the tree,
			// but this is managed manually using a stack rather than actual recursion
			// Each item on the stack is of the form array(int $depth, SimpleXMLElement $element, string $header_row)
			$dump .= _simplexml_tree_recursively_process_node(
				$root_item, 1,
				$include_string_content, $indent, $content_extract_size
			);
		}
		
		$root_item_index++;
	}

	// Add on the header line, with the total number of items output
	$dump = 'SimpleXML object (' . $root_item_index . ' item' . ($root_item_index > 1 ? 's' : '') . ')' . PHP_EOL . $dump;

	if ( $return )
	{
		return $dump;
	}
	else
	{
		echo $dump;
	}
}

/**
 * "Private" function to perform the recursive part of simplexml_tree()
 * Do not call this function directly or rely on its function signature remaining stable
 */
function _simplexml_tree_recursively_process_node($item, $depth, $include_string_content, $indent, $content_extract_size)
{
	$dump = '';
	
	if ( $include_string_content )
	{
		// Show a chunk of the beginning of the content string, collapsing whitespace HTML-style
		$string_content = (string)$item;
		
		$string_extract = preg_replace('/\s+/', ' ', trim($string_content));
		if ( strlen($string_extract) > $content_extract_size )
		{
			$string_extract = substr($string_extract, 0, $content_extract_size)
				. '...';
		}
		
		if ( strlen($string_content) > 0 )
		{
			$dump .= str_repeat($indent, $depth)
				. '(string) '
				. "'$string_extract'"
				. ' (' . strlen($string_content) . ' chars)'
				 . PHP_EOL;
		}
	}
		
	// To what namespace does this element belong? Returns array( alias => URI )
	$item_ns = $item->getNamespaces(false);
	if ( ! $item_ns )
	{
		$item_ns = array('' => NULL);
	}
	
	// This returns all namespaces used by this node and all its descendants,
	// 	whether declared in this node, in its ancestors, or in its descendants
	$all_ns = $item->getNamespaces(true);
	// If the default namespace is never declared, it will never show up using the below code
	if ( ! array_key_exists('', $all_ns) )
	{
		$all_ns[''] = NULL;
	}
	
	// Prioritise "current" namespace by merging into onto the beginning of the list
	// (it will be added to the beginning and the duplicate entry dropped)  
	$all_ns = array_merge($item_ns, $all_ns);
	
	foreach ( $all_ns as $ns_alias => $ns_uri )
	{
		$children = $item->children($ns_alias, true);
		$attributes = $item->attributes($ns_alias, true);
		
		// If things are in the current namespace, display them a bit differently
		$is_current_namespace = ( $ns_uri == reset($item_ns) );
		
		if ( count($attributes) > 0 )
		{
			if ( ! $is_current_namespace )
			{
				$dump .= str_repeat($indent, $depth)
					. "->attributes('$ns_alias', true)" . PHP_EOL;
			}
			
			foreach ( $attributes as $sx_attribute )
			{
				// Output the attribute
				if ( $is_current_namespace )
				{
					// In current namespace
					// e.g. ['attribName']
					$dump .= str_repeat($indent, $depth)
						. "['" . $sx_attribute->getName() . "']"
						. PHP_EOL;
					$string_display_depth = $depth+1;
				}
				else
				{
					// After a call to ->attributes()
					// e.g. ->attribName
					$dump .= str_repeat($indent, $depth+1)
						. '->' . $sx_attribute->getName()
						. PHP_EOL;
					$string_display_depth = $depth+2;
				}
				
				if ( $include_string_content )
				{
					// Show a chunk of the beginning of the content string, collapsing whitespace HTML-style
					$string_content = (string)$sx_attribute;
					
					$string_extract = preg_replace('/\s+/', ' ', trim($string_content));
					if ( strlen($string_extract) > $content_extract_size )
					{
						$string_extract = substr($string_extract, 0, $content_extract_size)
							. '...';
					}
					
					$dump .= str_repeat($indent, $string_display_depth)
						. '(string) '
						. "'$string_extract'"
						. ' (' . strlen($string_content) . ' chars)'
						 . PHP_EOL;
				}
			}
		}
		
		if ( count($children) > 0 )
		{
			if ( $is_current_namespace )
			{
				$display_depth = $depth;
			}
			else
			{
				$dump .= str_repeat($indent, $depth)
					. "->children('$ns_alias', true)" . PHP_EOL;
				$display_depth = $depth + 1;
			}
			
			// Recurse through the children with headers showing how to access them
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
				
				// e.g. ->Foo[0]
				$dump .= str_repeat($indent, $display_depth)
					. '->' . $sx_child->getName()
					. '[' . ($child_names[$child_node_name]-1) . ']'
					. PHP_EOL;
				
				$dump .= _simplexml_tree_recursively_process_node(
					$sx_child, $display_depth+1,
					$include_string_content, $indent, $content_extract_size
				);
			}
		}
	}
	
	return $dump;
}
