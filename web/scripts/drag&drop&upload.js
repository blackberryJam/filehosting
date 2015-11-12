"use strict";

var files = [];
var counter = 0;

var sendButton = document.getElementById("send-1");
var filelist = document.getElementById("filelist-1");
var dropbox = document.getElementById("dropbox-1");

function sendFile(file, i) {
    var formData = new FormData();
    formData.append("fileToUpload", file);
    var li = filelist.childNodes[i + 1];
    var fileName = li.innerHTML;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/index.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState != 4) {
            return;
        }
        if (counter === 1 & xhr.status === 200) {
            window.location.replace(xhr.responseText);
        }
        var aResponse = document.createElement("a");
        aResponse.setAttribute("href", xhr.responseText);
        aResponse.innerHTML = xhr.responseText;
        li.innerHTML = fileName + " || Файл загружен! Ссылка: ";
        li.appendChild(aResponse);
    };
    xhr.send(formData);
    li.innerHTML = "Upload in process..."
}

var func = function(event) {
    event.stopPropagation();
    event.preventDefault();
}
dropbox.addEventListener("dragenter", func);
dropbox.addEventListener("dragover", func);
dropbox.addEventListener("drop", function(event) {
    event.stopPropagation();
    event.preventDefault();

    var filesDropped = event.dataTransfer.files;
    for (var i = 0; i < filesDropped.length; i++) {
        files.push(filesDropped[i]);

        var li = document.createElement("li");
        li.innerHTML = filesDropped[i].name;
        filelist.appendChild(li);
    }
});

sendButton.addEventListener("click", function(event) {
    files.forEach(function(item, i, arr) {
        (function(item, counter){
            window.setTimeout(function() {
                sendFile(item, counter);
            }, counter * 1000);
        })(item, counter);
        counter++;
    });
    files = [];
});
