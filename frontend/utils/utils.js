var Utils = {
    set_to_localstorage: function(key, value) {
        window.localStorage.setItem(key, JSON.stringify(value));
      },
      get_from_localstorage: function(key) {
        return JSON.parse(window.localStorage.getItem(key));
      },
      logout: function() {
        $.ajax({
          url: 'http://localhost/BalkanFreelance/backend/auth/logout',
          method: 'POST',
          success: function (response) {
            window.localStorage.clear();
            window.location = "login/index.html";
          },
          error: function () {
            alert("Something went wrong while logging out!");
          }
        });
      }
}