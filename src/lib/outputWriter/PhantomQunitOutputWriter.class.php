<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PhantomQunitXMLWriter
 *
 * @author jnankin
 */
abstract class PhantomQunitOutputWriter {

    protected $verboseMode = true;

    protected $testSummary;
    protected $testResults;

    public function __construct($testSummary, $testResults, $verboseMode = true){
        $this->testSummary = $testSummary;
        $this->testResults = $testResults;

        $this->verboseMode = $verboseMode;
    }

    abstract public function initialize();
    abstract public function finalize();

    abstract public function writeTestSuite($suite);
    abstract public function writeTestCase($case);
    abstract protected function writeTestFailures($failure);
    abstract protected function writeTestErrors($error);




}
?>
