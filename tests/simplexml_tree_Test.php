<?php

class simplexml_tree_test extends PHPUnit_Framework_TestCase
{
    public function loadExamples()
    {
        $test_cases = array();

        foreach ( new DirectoryIterator(__DIR__ . '/dump-output') as $fileInfo )
        {
            if($fileInfo->isDot()) {
                continue;
            }

            $filename = $fileInfo->getFilename();

            $test_cases[$filename] = array(
                file_get_contents(__DIR__ . '/input/' . $filename . '.xml'),
                file_get_contents(__DIR__ . '/tree-output/' . $filename)
            );
        }

        return $test_cases;
    }

    /**
     * @dataProvider loadExamples
     */
    public function testTreeReturn($xml, $expected_output)
    {
        $return = simplexml_tree(simplexml_load_string($xml), false, true);
        $this->assertSame($expected_output, $return);
    }
}
