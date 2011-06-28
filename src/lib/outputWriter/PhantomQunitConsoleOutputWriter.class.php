<?php

class PhantomQunitConsoleOutputWriter extends PhantomQunitOutputWriter {

    private $summary = array('suites' => 0, 'total' => 0, 'failures' => 0, 'errors' => 0, 'errorEncountered' => false);
    private $lineWidth = 70;
    private $tabWidth = 2;

    const GREENWHITE = "\033[1;97m\033[42m";
    const REDWHITE = "\033[1;97m\033[41m";
    const NORMAL = "\033[0m";
    const RED = "\033[0;91m";
    const GREEN = "\033[0;92m";

    public function initialize() {
        //noop
    }

    public function writeTestSuite($suite) {
        $startColor = "";
        $endColor = "";

        if ($suite['summary']['failures'] > 0 || $suite['summary']['errors'] > 0){
            $problems = array();
            if ($suite['summary']['failures']) $problems[] = $suite['summary']['failures'] . " failures";
            if ($suite['summary']['errors']) $problems[] = $suite['summary']['errors'] . " errors";
            $status = implode(", ", $problems);
            $this->summary['errorEncountered'] = true;

            $startColor = self::REDWHITE;
            $endColor = self::NORMAL;
        }
        else {
            $startColor = self::GREEN;
            $endColor = self::NORMAL;
            $status = "ok";
        }

        printf("%'.-{$this->lineWidth}s$startColor%s\n$endColor", $suite['name'], $status);

        $this->summary['total'] += $suite['summary']['total'];
        $this->summary['failures'] += $suite['summary']['failures'];
        $this->summary['errors'] += $suite['summary']['errors'];
        $this->summary['suites']++;

        if ($this->verboseMode){
            foreach ($suite['cases'] as $case) {
                $this->writeTestCase($case);
            }
        }
    }

    public function writeTestCase($case) {
        $tabWidth = $this->tabWidth;
        $lineWidth = $this->lineWidth - $tabWidth;

        $startColor = "";
        $endColor = "";
        $name = $case['name'];
        if (isset($case['module']) && $case['module'] != 'none')
            $name = $case['module'] . ".$name";

        if (
                (isset($case['failures']) && count($case['failures']) > 0) ||
                (isset($case['errors']) && count($case['errors']) > 0)
           ){
            $status = "problem";
            $startColor = self::RED;
            $endColor = self::NORMAL;
        }
        else {
            $startColor = self::GREEN;
            $endColor = self::NORMAL;
            $status = "ok";
        }

        printf("%-{$tabWidth}s%'.-{$lineWidth}s$startColor%s$endColor\n", "", $name, $status);

        if (isset($case['errors']))
            $this->writeTestErrors($case['errors']);
        if (isset($case['failures']))
            $this->writeTestFailures($case['failures']);
    }

    protected function writeTestFailures($failures) {
        $tabWidth = $this->tabWidth * 2;
        $lineWidth = $this->lineWidth - $tabWidth;

        $startColor = self::RED;
        $endColor = self::NORMAL;

        foreach ($failures as $failure) {
            $source = "";
            if (isset($failure['source']) && strlen($failure['source']) > 0) {
                $source = "Encountered at " . $failure['source'];
            }

            printf("$startColor%-{$tabWidth}s%-{$lineWidth}s$endColor\n", "", "Failure: Expected " . $failure['expected'] . " but got " . $failure['actual'] . ". $source");
            
        }
    }

    protected function writeTestErrors($errors) {
        $tabWidth = $this->tabWidth * 2;
        $lineWidth = $this->lineWidth - $tabWidth;

        $startColor = self::RED;
        $endColor = self::NORMAL;

        foreach ($errors as $error) {
            $source = "";
            if (isset($failure['source']) && strlen($failure['source']) > 0) {
                $source = "Encountered at " . $failure['source'];
            }

            printf("$startColor%-{$tabWidth}s%-{$lineWidth}s$endColor\n", "", "Error: $error");

        }
    }


    public function finalize() {
        echo "\n\n";

        $startColor = self::GREENWHITE;
        $endColor = self::NORMAL;

        $message = "All tests successful!";
        if ($this->summary['errorEncountered']){
            $startColor = self::REDWHITE;
            $endColor = self::NORMAL;

            $message = "There were errors or failures encountered when running tests.  Search and destroy those bugs!";
        }
        echo $startColor . $message . "\n";
        echo "TestSuites=" . $this->summary['suites'] . " Tests=" . $this->summary['total'] . " Failures=" . $this->summary['failures'] . " Errors=" . $this->summary['errors'] . "$endColor\n";
    }

    
}

?>
