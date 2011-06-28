<?php

class PhantomQunitXMLOutputWriter extends PhantomQunitOutputWriter {

    private $fh;

    public function __construct($filename, $testSummary, $testResults) {
        parent::__construct($testSummary, $testResults);
        $this->fh = fopen($filename, "w");

        if ($this->fh === false) {
            throw new Exception("Cannot open file to write out xml: $filename");
        }
    }

    public function initialize() {
        fwrite($this->fh, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<testsuites>\n");
    }

    public function writeTestSuite($suite) {
        fwrite($this->fh, '<testsuite name="' . htmlspecialchars($suite['name']) . '" tests="' . htmlspecialchars($suite['summary']['total']) . '" failures="' . htmlspecialchars($suite['summary']['failures']) . '" errors="' . htmlspecialchars($suite['summary']['errors']) . '" time="' . htmlspecialchars($suite['time']) . '">' . "\n");

        foreach ($suite['cases'] as $case) {
            $this->writeTestCase($case);
        }

        fwrite($this->fh, '</testsuite>' . "\n");
    }

    public function writeTestCase($case) {
        $name = $case['name'];
        if (isset($case['module']) && $case['module'] != 'none')
            $name = $case['module'] . ".$name";

        fwrite($this->fh, '<testcase name="' . htmlspecialchars($name) . '" assertions="' . htmlspecialchars($case['total']) . '">' . "\n");

        if (isset($case['errors'])) $this->writeTestErrors($case['errors']);
        if (isset($case['failures'])) $this->writeTestFailures($case['failures']);

        fwrite($this->fh, '</testcase>' . "\n");
    }

    protected function writeTestFailures($failures) {
        foreach ($failures as $failure) {
            $source = "";
            if (isset($failure['source']) && strlen($failure['source']) > 0){
                $source = "Encountered at " . $failure['source'];
            }
            fwrite($this->fh, '<failure>' . htmlspecialchars("Expected " . $failure['expected'] . " but got " . $failure['actual'] . ". $source") . "</failure>\n");
        }
    }

    protected function writeTestErrors($errors) {
        foreach ($errors as $error) {
            fwrite($this->fh, '<error>' . htmlspecialchars($error) . "</error>\n");
        }
    }

    public function finalize() {
        fwrite($this->fh, "</testsuites>");
        fclose($this->fh);
    }

}

?>
