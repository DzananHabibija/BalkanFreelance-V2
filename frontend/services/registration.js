
$(document).ready(function() {
    $('#registerBtn').on('click', function() {
        var firstName = $('#firstName').val().trim();
        var lastName = $('#lastName').val().trim();
        var email = $('#email').val().trim();
        var password = $('#password').val();
        var repeatPassword = $('#repeatPassword').val();
        var countryId = $('#countrySelect').val();
        var phoneNumber = $('#phoneNumber').val().trim();

        if (!firstName || !lastName || !email || !password || !repeatPassword || !countryId || !phoneNumber) {
            alert('Please fill in all fields!');
            return;
        }

        if (password !== repeatPassword) {
            alert('Passwords do not match!');
            return;
        }

        $.ajax({
            url: 'http://localhost/BalkanFreelance/backend/auth/register', 
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                email: email,
                password: password,
                country_id: countryId,
                phone_number: phoneNumber
            }),
            success: function(response) {
                alert('Registration successful!');
                console.log(response);
                window.location.href = '../login/index.html';
            },
            error: function(xhr, status, error) {
                //alert('Error: ' + xhr.responseText);
                const errorMsg = xhr.responseText || "Registration failed";
                $('#registerError').text(errorMsg).show(); //error message from backend to the frontend
            }
        });
    });
});

