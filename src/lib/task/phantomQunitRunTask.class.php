<?php

/**
 * Base class for sfPhpunit tasks which generate test files from templates.
 *
 * @package    sfPhpunitPlugin
 * @subpackage task

 * @author     Pablo Godel <pgodel@gmail.com>
 * @author     Frank Stelzer <dev@frankstelzer.de>
 * @author     Maksim Kotlyar <mkotlar@ukr.net>
 */
class phantomQunitRunTask extends sfBaseTask {

    protected function configure() {
        $this->namespace = 'phantomQunit';
        $this->name = 'run';

        $this->briefDescription = 'Runs all (or one) qunit files with optional pdf';

        $this->detailedDescription = "";

        $this->addArguments(array(
            new sfCommandArgument('testFile', sfCommandArgument::OPTIONAL, 'Test file to run', null)
        ));

        $this->addOptions(array(
            new sfCommandOption('xml', null, sfCommandOption::PARAMETER_OPTIONAL, 'File to write junit format xml to', 0)
        ));
    }

    protected function execute($arguments = array(), $options = array()) {
        $startTime = microtime(true);
        $testResults = array();
        $testSummary = array();

        $testDirectory = sfConfig::get('phantomqunit_test_directory');
        if ($testDirectory[0] != "/") {
            $testDirectory = sfConfig::get("sf_root_dir") . "/" . $testDirectory;
        }

        $verbose = $arguments['testFile'] != null ? true : false;

        //setup the output writer and output the results, either as xml or to the console
        if ($options['xml']) {
            $this->outputWriter = new PhantomQunitXMLOutputWriter($options['xml'], $testSummary, $testResults, $verbose);
        } else {
            $this->outputWriter = new PhantomQunitConsoleOutputWriter($testSummary, $testResults, $verbose);
        }

        $this->outputWriter->initialize();

        //figure out which mode we're in, one test file or all test file
        if ($arguments['testFile']) {
            $this->runQunitOnFile($testDirectory . "/" . $arguments['testFile'] . ".js");
        } else {
            $directoryIterator = new RecursiveDirectoryIterator($testDirectory);
            $iterator = new RecursiveIteratorIterator($directoryIterator);
            $regex = new RegexIterator($iterator, '/^.+\.js$/i', RecursiveRegexIterator::GET_MATCH);

            foreach ($iterator as $path) {
                if (!$path->isDir()) {
                    $this->runQunitOnFile($path);
                }
            }
        }

        $this->outputWriter->finalize();
        echo "Tests completed in " . number_format(((microtime(true) - $startTime) * 1000), 3) . " ms.\n\n";
        echo "\n\n";
    }

    protected function runQunitOnFile($path) {
        $qunitDir = sfConfig::get('sf_plugins_dir') . '/phantomQunitPlugin/lib/qunit';

        $suite = array();
        $suite['name'] = basename($path, ".js");

        if (!file_exists($path)) {
            throw new Exception("$path does not exist!");
        }

        $qunitStartTime = microtime(true);
        $results = shell_exec("phantomjs $qunitDir/run-phantomQunit.js $path");
        $qunitEndTime = (microtime(true) - $qunitStartTime) * 1000;
        
        $results = explode("---delimiter GEwgqot8mAlcfxrLY7MC ---", $results);

        
        //an error has occurred when loading this file
        if (count($results) == 2 && $results[0] == "") {
            $decodedResult = json_decode($results[1], true);
            if ($decodedResult === false){
                $this->addErrorTestCaseToSuite($suite, array("There was an error decoding JSON from test runner."));
            }
            else {
                $suite['cases'] = $decodedResult['cases'];
                $suite['summary'] = $decodedResult['summary'];
            }
        } 
        else if (count($results) >= 2 && $results[0] != "") {
            $errors = explode("\n", trim($results[0]));
            $this->addErrorTestCaseToSuite($suite, $errors);
        }
        else {
            $this->addErrorTestCaseToSuite($suite, array("No input from qunit test runner.  Something seems really fishy."));
        }

        $suite['time'] = number_format($qunitEndTime, 3);
        $this->outputWriter->writeTestSuite($suite);
    }

    public function addErrorTestCaseToSuite(&$suite, $errors) {
        $suite['summary']['total'] = 1;
        $suite['summary']['failures'] = 0;
        $suite['summary']['errors'] = count($errors);
        $suite['cases'] = array(
            array(
                'passed' => 0,
                'failed' => 0,
                'total' => 1,
                'name' => 'error-case',
                'errors' => $errors
            )
        );
    }

}