

var Itav = {};
Itav.Ajax = {
    "ajaxAction": function (url) {
        return $.ajax({
            type: "POST",
            url: url,
            dataType: "json"
        });
    },
    "ajaxActionData": function (url, dat) {
        return $.ajax({
            type: "POST",
            url: url,
            data: {data: dat},
            dataType: "json"
        });
    },
    "ajaxActionDataLong": function (rqst, dat) {
        return $.ajax({
            type: "POST",
            url: $.baseUrl + rqst,
            data: {data: dat},
            dataType: "json",
            beforeSend: function () {
                window.loadingTimeout = setTimeout(function () {
                    $.loadingDialog.dialog('open');
                }, 3000);
            }
        });
    },
    "ajaxActionType": function (rqst, typ) {
        return $.ajax({
            type: "POST",
            url: $.baseUrl + rqst,
            dataType: typ
        });
    },
    "ajaxActionDataType": function (rqst, dat, typ) {
        return $.ajax({
            url: $.baseUrl + rqst,
            data: {data: dat},
            dataType: typ
        });
    },
    "progressHandlingFunction": function (e) {
        if (e.lengthComputable) {
            $('progress').attr({value: e.loaded, max: e.total});
        }
    },
    "ajaxActionFileData": function (rqst, dat) {
        return $.ajax({
            url: $.baseUrl + rqst, //Server script to process data
            type: 'POST',
            xhr: function () {  // Custom XMLHttpRequest
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) { // Check if upload property exists
                    myXhr.upload.addEventListener('progress', progressHandlingFunction, false); // For handling the progress of the upload
                }
                return myXhr;
            },
            //Ajax events
            //beforeSend: beforeSendHandler,
            //success: completeHandler,
            //error: errorHandler,
            // Form data
            data: dat,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false,
            contentType: false,
            processData: false
        });
    },
    "openSync": function (verb, url, data, target) {
        var form = document.createElement("form");
        form.action = url;
        form.method = verb;
        form.target = typeof (target) === 'undefined' ? "_self" : target;
        if (data) {
            for (var key in data) {
                var input = document.createElement("textarea");
                input.name = "data[" + key + "]";
                input.value = typeof data[key] === "object" ? JSON.stringify(data[key]) : data[key];
                form.appendChild(input);
            }
        }
        form.style.display = 'none';
        document.body.appendChild(form);
        form.submit();
    }
};


