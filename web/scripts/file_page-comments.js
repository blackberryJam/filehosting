"use strict";

var renderCommentForm = function(container, targetButton) {
    var form = document.createElement("form");
    form.className = "comment-form-" + targetButton.dataset.position;
    form.setAttribute("id", "comment-form-" + targetButton.dataset.position);

    var divTextarea = document.createElement("div");
    var divButton = document.createElement("div");

    var textarea = document.createElement("textarea");
    textarea.className = "form-control";
    textarea.setAttribute("required", "");
    textarea.setAttribute("form", "comment-form-" + targetButton.dataset.position);
    textarea.setAttribute("name", "comment-body");

    var button = document.createElement("button");
    button.className = "btn btn-info comment-form-sender";
    button.innerHTML = "Send!";
    button.setAttribute("form", "comment-form-" + targetButton.dataset.position);
    button.setAttribute("type", "button");
    button.dataset.position = targetButton.dataset.position;

    divTextarea.appendChild(textarea);
    divButton.appendChild(button);
    form.appendChild(divTextarea);
    form.appendChild(divButton);
    container.appendChild(form);

    textarea.focus();
};

var getCommentFormToggle = function(targetButton) {
    var formContainer = document.getElementById("comment-form-container-" + targetButton.dataset.position);

    return function(event) {
        if (formContainer.childNodes.length !== 0) {
            var form = document.querySelector(".comment-form-" + targetButton.dataset.position);
            formContainer.removeChild(form);
            return;
        }

        renderCommentForm(formContainer, targetButton);
    };
};

var renderNewComment = function(commentObject) {
    var commentDiv = document.createElement("div");
    commentDiv.className = "depth-" + commentObject.depth + " comment parent-" + commentObject.parentPath;
    commentDiv.setAttribute("id", "comment-" + commentObject.path);
    commentDiv.dataset.number = commentObject.number;
    commentDiv.dataset.path = commentObject.path;

    var commentInfo = document.createElement("div");
    commentInfo.className = "comment-info";

    var p = document.createElement("p");

    var spanUser = document.createElement("span");
    spanUser.className = "comment-user";
    spanUser.innerHTML = commentObject.userName ? commentObject.userName : "Anonymous";

    var spanDate = document.createElement("span");
    spanDate.className = "comment-date";
    spanDate.innerHTML = commentObject.date;

    p.appendChild(spanUser);
    p.innerHTML += ", ";
    p.appendChild(spanDate);
    commentInfo.appendChild(p);

    var bodyDiv = document.createElement("div");
    bodyDiv.className = "comment-body";

    var bodyP = document.createElement("p");
    bodyP.innerHTML = commentObject.body;

    bodyDiv.appendChild(bodyP);

    if (commentObject.path.length <= 35) {
        var buttonDiv = document.createElement("div");

        var button = document.createElement("button");
        button.className = "btn-xs btn-default answer-form-renderer comment-form-renderer";
        button.dataset.position = commentObject.path;
        button.innerHTML = "Answer";
        button.setAttribute("type", "button");

        buttonDiv.appendChild(button);
    }

    var formContainer = document.createElement("div");
    formContainer.setAttribute("id", "comment-form-container-" + commentObject.path);

    commentDiv.appendChild(commentInfo);
    commentDiv.appendChild(bodyDiv);
    if (commentObject.path.length <= 35) {
        commentDiv.appendChild(buttonDiv);
    }
    commentDiv.appendChild(formContainer);

    var refNode = getActualNode(commentObject.parentPath);
    if (!refNode) {
        document.getElementById("comments-container").appendChild(commentDiv);
    } else {
        refNode.parentNode.insertBefore(commentDiv, refNode.nextSibling);
    }
};

var getActualNode = function(parentPath) {
    if (parentPath === "") {
        return;
    }

    var childs = document.querySelectorAll(".parent-" + parentPath);
    childs = Array.prototype.slice.call(childs);

    if (childs.length === 0) {
        return document.getElementById("comment-" + parentPath);
    }

    return getLastChildRecursive(childs);
};

var getLastChildRecursive = function(childs) {
    var maxNumber = 0;
    var lastChild = null;
    childs.forEach(function(child, i, childs) {
        if (child.dataset.number > maxNumber) {
            maxNumber = child.dataset.number;
            lastChild = child;
        }
    });

    var childChilds = document.querySelectorAll(".parent-" + lastChild.dataset.path);
    childChilds = Array.prototype.slice.call(childChilds);

    if (childChilds.length === 0) {
        return lastChild;
    }

    return getLastChildRecursive(childChilds);
};

var increaseCommentCounter = function() {
    var counter = document.getElementById("comments-counter");
    var num = parseInt(counter.innerHTML);
    num++;
    counter.innerHTML = String(num);
};

var sendCommentForm = function(targetButton) {
    var comment = createCommentObject(targetButton.dataset.position);
    var boundary = String(Math.random()).slice(2);
    var requestBody = generateRequestBody(comment, boundary);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/comment/send", true);
    xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + boundary);
    xhr.onreadystatechange = function(event) {
        if (xhr.readyState !== 4) {
            return;
        }

        if (xhr.status !== 200) {
            alert("Ошибка отправки формы.");
            return;
        }

        var response = JSON.parse(xhr.response);
        if (response === "validation_failed") {
            alert("Неверный формат комментария.");
            return;
        }

        renderNewComment(response);
        increaseCommentCounter();
    };
    xhr.send(requestBody);

    var formContainer = document.getElementById("comment-form-container-" + targetButton.dataset.position);
    formContainer.removeChild(document.getElementById("comment-form-" + targetButton.dataset.position));
};

var createCommentObject = function(position) {
    var parent = "";
    if (position !== "top" & position !== "bottom") {
        parent = position;
    }

    var textarea = document.querySelector("#comment-form-" + position + " textarea");

    var comment = {
        parent: parent,
        body: textarea.value,
        file: getFileId()
    };

    return comment;
};

var commentsBlock = document.getElementById("comments-block");
commentsBlock.addEventListener("click", function(event) {
    var target = event.target;

    var buttonType;
    var isFormSender = target.classList.contains("comment-form-sender");
    var isFormRenderer = target.classList.contains("comment-form-renderer");

    if (!isFormSender & !isFormRenderer) {
        return;
    } else {
        buttonType = isFormRenderer ? "comment-form-renderer" : "comment-form-sender";
    }

    switch (buttonType) {
        case "comment-form-renderer":
            var toggleForm = getCommentFormToggle(target);
            toggleForm();
            break;
        case "comment-form-sender":
            sendCommentForm(target);
            break;
    }
});
