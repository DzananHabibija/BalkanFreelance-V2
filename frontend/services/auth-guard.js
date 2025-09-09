$(document).ready(function () {
  const user = Utils.get_from_localstorage("user");
  const token = user?.token;

  if (!token) {
    alert("You must log in first.");
    window.location.href = "#login";
  }
});
