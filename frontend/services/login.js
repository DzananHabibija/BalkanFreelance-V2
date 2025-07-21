
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
                //alert(response.message); // "Login successful"
                console.log("Response is: ",response);
                Utils.set_to_localstorage("user", response);
                //Utils.set_to_localstorage("user", response.user);
                Utils.set_to_localstorage("jwt", response.token);
                window.location = "../#home"; 
            },
            error: function(xhr) {
                //alert(xhr.responseText); // show backend error ("Invalid email or password")
                const errorMsg = xhr.responseText || "An error occurred";
                $('#loginError').text(errorMsg).show(); // Display error message from backend to the frontend
            }
        });
    });

       $('#googleLoginBtn').on('click', function () {
      $.ajax({
        url: 'http://localhost/BalkanFreelance/backend/google-login',
        method: 'GET',
        success: function (response) {
          if (response.authUrl) {
            window.location.href = response.authUrl;
          } else {
            alert("Unable to redirect to Google.");
          }
        },
        error: function () {
          alert("Something went wrong while preparing Google login.");
        }
      });
    });
});


var Utils = {
    set_to_localstorage: function(key, value) {
        window.localStorage.setItem(key, JSON.stringify(value));
      },
      get_from_localstorage: function(key) {
        return JSON.parse(window.localStorage.getItem(key));
      },
      // logout: function() {
      //   window.localStorage.clear();
      //   window.location = "/login/index.html";
      // }
    }