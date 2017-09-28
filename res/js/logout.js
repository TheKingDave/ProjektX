$(document).ready(function () {
    $.post("/_logout")
        .then(function (data) {
            try {
                data = JSON.parse(data);
            } catch(error) {
                console.log(error, data);
                showError("Internal server error #00002");
                return;
            }
            if(data["ok"] === true) {
                localStorage.clear();
                console.log(data);
                window.location.href = data["redirect"];
            } else {
                window.location.href = "/500";
            }
        })
});