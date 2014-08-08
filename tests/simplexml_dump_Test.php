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

		$this->expected_NS = "SimpleXML object (1 item)
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

	public function testDumpWithNS()
	{
		$return = simplexml_dump($this->simpleXML_NS, true);
		$this->assertEquals($this->expected_NS, $return);
	}
}

?>
