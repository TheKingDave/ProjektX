function onSubmit(token) {
    console.log("Register", token);
}

$(document).ready(function () {
    $("#submit").click(function () {
        grecaptcha.execute();
    });
});