<?php

class simplexml_tree_Test extends simplexml_dump_bootstrap
{
	public function setUp()
	{
		$this->expected = "SimpleXML object (1 item)
[0] // <movies>
	->movie[0]
		->title[0]
		->characters[0]
			->character[0]
				->name[0]
				->actor[0]
			->character[1]
				->name[0]
				->actor[0]
		->plot[0]
		->great-lines[0]
			->line[0]
		->rating[0]
			['type']
		->rating[1]
			['type']
";

		$this->expected_default_NS = "SimpleXML object (1 item)
[0] // <movies>
	->movie[0]
		->title[0]
		->characters[0]
			->character[0]
				->name[0]
				->actor[0]
			->character[1]
				->name[0]
				->actor[0]
		->plot[0]
		->great-lines[0]
			->line[0]
		->rating[0]
			['type']
		->rating[1]
			['type']
";

		$this->expected_named_NS = "SimpleXML object (1 item)
[0] // <movies>
	->children('test', true)
		->movie[0]
			->title[0]
			->characters[0]
				->character[0]
					->name[0]
					->actor[0]
				->character[1]
					->name[0]
					->actor[0]
			->plot[0]
			->great-lines[0]
				->line[0]
			->rating[0]
				->attributes('', true)
					->type
			->rating[1]
				->attributes('', true)
					->type
";

		parent::setUp();
	}

	public function testTree()
	{
		ob_start();
		simplexml_tree($this->simpleXML);
		$return = ob_get_contents();
		ob_end_clean();

		$this->assertEquals($this->expected, $return);
	}

	public function testTreeIncludeStringContent()
	{
		ob_start();
		simplexml_tree($this->simpleXML, true);
		$return = ob_get_contents();
		ob_end_clean();

		$expected = "SimpleXML object (1 item)
[0] // <movies>
	(string) '' (9 chars)
	->movie[0]
		(string) '' (41 chars)
		->title[0]
			(string) 'PHP: Behind the...' (22 chars)
		->characters[0]
			(string) '' (20 chars)
			->character[0]
				(string) '' (23 chars)
				->name[0]
					(string) 'Ms. Coder' (9 chars)
				->actor[0]
					(string) 'Onlivia Actora' (14 chars)
			->character[1]
				(string) '' (23 chars)
				->name[0]
					(string) 'Mr. Coder' (9 chars)
				->actor[0]
					(string) 'El ActÓr' (9 chars)
		->plot[0]
			(string) 'So, this langua...' (174 chars)
		->great-lines[0]
			(string) '' (13 chars)
			->line[0]
				(string) 'PHP solves all ...' (30 chars)
		->rating[0]
			(string) '7' (1 chars)
			['type']
				(string) 'thumbs' (6 chars)
		->rating[1]
			(string) '5' (1 chars)
			['type']
				(string) 'stars' (5 chars)
";

		$this->assertEquals($expected, $return);
	}

	public function testTreeReturn()
	{
		$return = simplexml_tree($this->simpleXML, false, true);
		$this->assertEquals($this->expected, $return);
	}

	public function testTreeWithDefaultNS()
	{
		$return = simplexml_tree($this->simpleXML_default_NS, false, true);
		$this->assertEquals($this->expected_default_NS, $return);
	}

	public function testTreeWithNamedNS()
	{
		$return = simplexml_tree($this->simpleXML_named_NS, false, true);
		$this->assertEquals($this->expected_named_NS, $return);
	}
}

?>
