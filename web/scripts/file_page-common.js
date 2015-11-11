"use strict";

var getFileId = function() {
    var path = window.location.pathname;
    path = path.split("/");
    return path[2];
};
