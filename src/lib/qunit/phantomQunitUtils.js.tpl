var phantomQunitUtils = {
    ROOT_DIR : '##ROOT_DIR##',
    TEST_DIR : '##TEST_DIR##'
};

phantomQunitUtils.load = function(path){
    if (!path.match(/^file:\/\//) || !path.match(/^http:\/\//)) path = "file://" + path;
    $.ajax({
        url: path,
        dataType: 'script',
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("Message: " + errorThrown.message + " Line: " + errorThrown.line);
        }
    });

};

phantomQunitUtils.load(phantom.args[0]);