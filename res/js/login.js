let error = $("#error");
let form = $('#loginForm');

let onceTryed = false;

$(document).ready(function () {
    form.submit(function (e) {
        e.preventDefault();
        validateForm(true);
    });
    $.map(form.serializeArray(), function (n) {
        form.find("[name='" + n['name'] + "']").keyup(function (n) {
            if(n.key === "Enter") {
                return;
            }
            if(onceTryed) {
                validateForm();
            }
        });
    });
    // TODO: Remove this debuging stuff
    //$("#user_name").val("TheKingDave");
    //$("#password").val("david");
});

function validateForm(submit) {
    if(!submit) {
        showError("");
    }
    let formData = getFormData(form);

    let constraints = {
        user: {
            presence: true
        },
        pwd: {
            presence: true
        }
    };

    let errors = validate(formData, constraints);
    if(showErrosOnForm(form, errors)) {
        showError("Plese enter all required information.");
    } else {
        if(submit) {
            submitForm();
        }
    }
    onceTryed = true;
}

function submitForm() {
    $.post("_login", getFormData(form))
        .then(function (data) {
            try {
                data = JSON.parse(data);
            } catch(error) {
                console.log(error, data);
                showError("Internal server error #00002");
                return;
            }
            if(data["ok"] === true) {
                console.log(data);
                localStorage.setItem("username", data["username"]);
                localStorage.setItem("userId", data["user"]);
                window.location.href = data["redirect"];
            } else {
                showError(data["error_msg"]);
            }
        }).fail(function (error) {
            console.log(error);
            showError("Internal server error #00003");
        }
    )
}

function showErrosOnForm(form, errors) {
    $.map(form.serializeArray(), function (n) {
        form.find("[name='" + n['name'] + "']").removeClass('invalid');
    });
    if(errors === undefined) {
        return false;
    }
    $.map(errors, function (n, i) {
        form.find("[name='" + i + "']").addClass('invalid');
    });
    return true;
}

function showError(msg) {
    error.text(msg);
}

/*
Solution from: https://stackoverflow.com/questions/11338774/serialize-form-data-to-json
 */
function getFormData($form){
    let unindexed_array = $form.serializeArray();
    let indexed_array = {};

    $.map(unindexed_array, function(n){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}