"use strict";

var generateRequestBody = function(dataObject, boundary) {
    var boundaryMiddle = "--" + boundary + "\r\n";
    var boundaryLast = "--" + boundary + "--\r\n";
    var body = ["\r\n"];

    for (var fieldName in dataObject) {
        body.push("Content-Disposition: form-data; name=\"" + fieldName + "\"\r\n\r\n" + dataObject[fieldName] + "\r\n");
    }
    body = body.join(boundaryMiddle) + boundaryLast;

    return body;
};
