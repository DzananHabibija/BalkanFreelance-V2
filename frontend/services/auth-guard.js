$(document).ready(function () {
  const cookieObj = new URLSearchParams(document.cookie.replaceAll("&", "%26").replaceAll("; ", "&"));

  const id = cookieObj.get("id");
  const firstName = cookieObj.get("first_name");
  const lastName = cookieObj.get("last_name");
  const email = cookieObj.get("email");
  const is_admin = cookieObj.get("isAdmin");
  const jwt = cookieObj.get("jwt");

  if(id && jwt) {
      const user = {
      id: id,
      first_name: firstName,
      last_name: lastName,
      email: email,
      isAdmin: is_admin,
      token: jwt,
    };
    Utils.set_to_localstorage("user", user);
    Utils.set_to_localstorage("jwt", jwt);
  }
  const user = Utils.get_from_localstorage("user");
  const token = user?.token;

  if (!token) {
    alert("You must log in first.");
    window.location.href = "login/index.html";
  }

  $(function () {
    $(window).on("hashchange", function () {
      enforceAuthGuard();
    });

    function enforceAuthGuard() {
      const user = Utils.get_from_localstorage("user");
      const hash = window.location.hash;

      if (hash === "#admin") {
        if (!user || user["isAdmin"] != 1) {
          window.location = "#home";
        }
      }
    }

    enforceAuthGuard();
  });
});