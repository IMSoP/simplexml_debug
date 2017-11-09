<?php

require_once 'src/simplexml_dump.php';
require_once 'src/simplexml_tree.php';

class simplexml_dump_bootstrap extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->simpleXML = simplexml_load_string('<?xml version="1.0" standalone="yes"?>
			<movies>
				<movie>
					<title>PHP: Behind the Parser</title>
					<characters>
						<character>
							<name>Ms. Coder</name>
							<actor>Onlivia Actora</actor>
						</character>
						<character>
							<name>Mr. Coder</name>
							<actor>El Act&#211;r</actor>
						</character>
					</characters>
					<plot>
						So, this language. It\'s like, a programming language.
						Or is it a scripting language? All is revealed in this
						thrilling horror spoof of a documentary.
					</plot>
					<great-lines>
						<line>PHP solves all my web problems</line>
					</great-lines>
					<rating type="thumbs">7</rating>
					<rating type="stars">5</rating>
				</movie>
			</movies>
		');

		$this->simpleXML_default_NS = simplexml_load_string('<?xml version="1.0" standalone="yes"?>
			<movies xmlns="https://github.com/IMSoP/simplexml_debug">
				<movie>
					<title>PHP: Behind the Parser</title>
					<characters>
						<character>
							<name>Ms. Coder</name>
							<actor>Onlivia Actora</actor>
						</character>
						<character>
							<name>Mr. Coder</name>
							<actor>El Act&#211;r</actor>
						</character>
					</characters>
					<plot>
						So, this language. It\'s like, a programming language.
						Or is it a scripting language? All is revealed in this
						thrilling horror spoof of a documentary.
					</plot>
					<great-lines>
						<line>PHP solves all my web problems</line>
					</great-lines>
					<rating type="thumbs">7</rating>
					<rating type="stars">5</rating>
				</movie>
			</movies>
		');

		$this->simpleXML_named_NS = simplexml_load_string('<?xml version="1.0" standalone="yes"?>
			<movies xmlns:test="https://github.com/IMSoP/simplexml_debug">
				<test:movie>
					<test:title>PHP: Behind the Parser</test:title>
					<test:characters>
						<test:character>
							<test:name>Ms. Coder</test:name>
							<test:actor>Onlivia Actora</test:actor>
						</test:character>
						<test:character>
							<test:name>Mr. Coder</test:name>
							<test:actor>El Act&#211;r</test:actor>
						</test:character>
					</test:characters>
					<test:plot>
						So, this language. It\'s like, a programming language.
						Or is it a scripting language? All is revealed in this
						thrilling horror spoof of a documentary.
					</test:plot>
					<test:great-lines>
						<test:line>PHP solves all my web problems</test:line>
					</test:great-lines>
					<test:rating type="thumbs">7</test:rating>
					<test:rating type="stars">5</test:rating>
				</test:movie>
			</movies>
		');
	}
}

?>
