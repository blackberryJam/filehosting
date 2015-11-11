"use strict";

var sendRemoveFileForm = function(data) {
    var boundary = String(Math.random()).slice(2);
    var body = generateRequestBody(data, boundary);

    var xhr = new XMLHttpRequest()
    xhr.open("POST", "/file/" + data.fileId + "/remove", true);
    xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + boundary);
    xhr.onreadystatechange = function(event) {
        if (xhr.readyState !== 4) {
            return;
        }

        if (xhr.status !== 200) {
            alert("Ошибка удаления файла.");
            return;
        }

        if (JSON.parse(xhr.responseText) === "ok") {
            window.location.reload();
        }
    };
    xhr.send(body);
};

var removeFileButton = document.getElementById("remove-file-button");
removeFileButton.addEventListener("click", function(event) {
    var data = {
        fileId: getFileId()
    };
    sendRemoveFileForm(data);
});
