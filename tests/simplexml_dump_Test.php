<?php

class simplexml_dump_Test extends simplexml_dump_bootstrap
{
	public function setUp()
	{
		$this->expected = "SimpleXML object (1 item)
[
	Element {
		Name: 'movies'
		String Content: '
				
			'
		Content in Default Namespace
			Children: 1 - 1 'movie'
			Attributes: 0
	}
]
";

		$this->expected_default_NS = "SimpleXML object (1 item)
[
	Element {
		Namespace: 'https://github.com/IMSoP/simplexml_debug'
		(Default Namespace)
		Name: 'movies'
		String Content: '
				
			'
		Content in Default Namespace
			Namespace URI: 'https://github.com/IMSoP/simplexml_debug'
			Children: 1 - 1 'movie'
			Attributes: 0
	}
]
";


		$this->expected_named_NS = "SimpleXML object (1 item)
[
	Element {
		Name: 'movies'
		String Content: '
				
			'
		Content in Namespace test
			Namespace URI: 'https://github.com/IMSoP/simplexml_debug'
			Children: 1 - 1 'movie'
			Attributes: 0
	}
]
";

		parent::setUp();
	}

	public function testDump()
	{
		ob_start();
		simplexml_dump($this->simpleXML);
		$return = ob_get_contents();
		ob_end_clean();

		$this->assertEquals($this->expected, $return);
	}

	public function testDumpReturn()
	{
		$return = simplexml_dump($this->simpleXML, true);
		$this->assertEquals($this->expected, $return);
	}

	public function testDumpWithDefaultNS()
	{
		$return = simplexml_dump($this->simpleXML_default_NS, true);
		$this->assertEquals($this->expected_default_NS, $return);
	}

	public function testDumpWithNamedNS()
	{
		$return = simplexml_dump($this->simpleXML_named_NS, true);
		$this->assertEquals($this->expected_named_NS, $return);
	}

	public function testDumpAttributeWithNamedNS()
	{
		$xml  = '<parent xmlns:ns="ns"><ns:child ns:foo="bar" /></parent>';
		$sxml = simplexml_load_string($xml);

		$return = simplexml_dump($sxml->children('ns', true)->child->attributes('ns'), true);

		$expected = "SimpleXML object (1 item)
[
	Attribute {
		Namespace: 'ns'
		Namespace Alias: 'ns'
		Name: 'foo'
		Value: 'bar'
	}
]
";

		$this->assertEquals($expected, $return);
	}

	public function testDumpMultipleAttributes()
	{
		$xml  = '<parent xmlns:ns="ns"><child ns:one="1" ns:two="2" ns:three="3" /></parent>';
		$sxml = simplexml_load_string($xml);

		$return = simplexml_dump($sxml->child, true);

		$expected = "SimpleXML object (1 item)
[
	Element {
		Namespace: 'ns'
		Namespace Alias: 'ns'
		Name: 'child'
		String Content: ''
		Content in Namespace ns
			Namespace URI: 'ns'
			Children: 0
			Attributes: 3 - 'one', 'two', 'three'
	}
]
";

		$this->assertEquals($expected, $return);
	}
}

?>
