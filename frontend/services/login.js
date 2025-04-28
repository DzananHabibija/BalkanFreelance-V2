$(document).ready(function() {
    $("#loginBtn").click(function() {
        var email = $("#email").val().trim();
        var password = $("#password").val().trim();

        if (email === "" || password === "") {
            alert("Please fill all fields");
            return;
        }

        $.ajax({
            url: "http://localhost/BalkanFreelance/backend/auth/login", 
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                email: email,
                password: password
            }),
            success: function(response) {
                alert(response.message); // "Login successful"
                window.location.href = "index.html"; 
            },
            error: function(xhr) {
                alert(xhr.responseText); // show backend error ("Invalid email or password")
            }
        });
    });
});
