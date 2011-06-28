<?php

class phantomQunitInitTask extends sfBaseTask {

    protected function configure() {
        $this->namespace = 'phantomQunit';
        $this->name = 'init';
        $this->briefDescription = 'Prepare files and dirs needed for phantomQunit';
        $this->detailedDescription = <<<EOF

EOF;

        parent::configure();
    }

    protected function execute($arguments = array(), $options = array()) {
        $rootDir = sfConfig::get('sf_root_dir');

        $testDirectory = sfConfig::get('phantomqunit_test_directory');
        if ($testDirectory[0] != "/"){
            $testDirectory = sfConfig::get("sf_root_dir") . "/" . $testDirectory;
        }

        //create the initial test directory
        $initialTestDir = $rootDir . '/test/phantomqunit';
        
        //put the dirs in the phantomQunitUtils.js file
        $runnerFile = $rootDir . "/plugins/phantomQunitPlugin/lib/qunit/run-phantomQunit.js";
        $data = file_get_contents($runnerFile . ".tpl");
        $data = preg_replace('/##ROOT_DIR##/', $rootDir, $data);
        $data = preg_replace('/##TEST_DIR##/', $testDirectory, $data);
        file_put_contents($runnerFile, $data);

        $utilsFile = $rootDir . "/plugins/phantomQunitPlugin/lib/qunit/phantomQunitUtils.js";
        $data = file_get_contents($utilsFile . ".tpl");
        $data = preg_replace('/##ROOT_DIR##/', $rootDir, $data);
        $data = preg_replace('/##TEST_DIR##/', $testDirectory, $data);
        file_put_contents($utilsFile, $data);

        $this->logSection('phantomQunit', "Initialized successfully!");
        
    }

    protected function _createDir($dir) {
        if (file_exists($dir)) {
            $this->logSection('phantomQunit', sprintf('Skipped existing dir %s', $dir));
            return false;
        }

        if (!mkdir($dir, 0777, true)) {
            throw new sfCommandException(sprintf('Failed to create target directory %s', $dir));
        }
        $this->logSection('phantomQunit', sprintf('Created dir %s', $dir));

        return true;
    }


}