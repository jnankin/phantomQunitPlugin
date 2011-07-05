var phantomQunitUtils = {
    ROOT_DIR : '##ROOT_DIR##',
    TEST_DIR : '##TEST_DIR##'
};

if (phantom.state.length === 0) {
    if (phantom.args.length === 0 || phantom.args.length > 2) {
        console.log('Usage: run-qunit.js URL');
        phantom.exit();
    } else {
        phantom.state = 'run-qunit';
        phantom.open(phantomQunitUtils.ROOT_DIR + "/plugins/phantomQunitPlugin/lib/qunit/testRunner.htm");
    }
} else {
    setInterval(function() {
        var el = document.getElementById('qunit-testresult');
        if (phantom.state !== 'finish') {
            if (el && el.innerText.match('completed')) {
                phantom.state = 'finish';
                jsonifyQunit();
                phantom.exit(0);
            }
        }
    }, 100);
}


function jsonifyQunit(){
    try {
        var result = {};
        result.summary = {};
        result.cases = [];

        result.summary['passed'] = $("#qunit-testresult .passed").text();
        result.summary['total'] = $("#qunit-testresult .total").text();
        result.summary['failures'] = $("#qunit-testresult .failed").text();
        result.summary['errors'] = 0;

        $("#qunit-tests > li").each(function(idx, li){

            var currentTest = {};
            currentTest['name'] = $(li).find('.test-name').text();

            if ($(li).find('.module-name').length > 0){
                currentTest['module'] = $(li).find('.module-name').text();
            }
            else {
                currentTest['module'] = "none";
            }


            if ($(li).find('.counts').length > 0){
                var counts = $(li).find('.counts').text().match(/^\(([0-9]+), ([0-9]+), ([0-9]+)\)$/);
                if (counts) {
                    currentTest['failed'] = counts[1];
                    currentTest['passed'] = counts[2];
                    currentTest['total'] = counts[3];
                }
            }

            if ($(li).find('li.fail').length > 0){
                currentTest['failures'] = [];
                $(li).find('li.fail').each(function(idx, value){
                    var failure = {};
                    failure['expected'] = $(value).find('tr.test-expected pre').text();
                    failure['actual'] = $(value).find('tr.test-actual pre').text();
                    failure['source'] = $(value).find('tr.test-source pre').text();
                   currentTest['failures'].push(failure);
                });
            }

            result.cases.push(currentTest);
        });


        console.log("---delimiter GEwgqot8mAlcfxrLY7MC ---");
        console.log(JSON.stringify(result));
    } catch (e) {
    }

    return result;
}