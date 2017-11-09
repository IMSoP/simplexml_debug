<?php

class simplexml_dump_test extends PHPUnit_Framework_TestCase
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
                file_get_contents(__DIR__ . '/dump-output/' . $filename)
            );
        }

        return $test_cases;
    }

    /**
     * @dataProvider loadExamples
     */
    public function testDumpReturn($xml, $expected_output)
    {
        $return = simplexml_dump(simplexml_load_string($xml), true);
        $this->assertSame($expected_output, $return);
    }
}
