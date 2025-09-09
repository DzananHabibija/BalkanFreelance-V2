console.log("✅ ajaxsetup.js loaded");

$.ajaxSetup({
  beforeSend: function (xhr) {
    const user = Utils.get_from_localstorage("user");
    if (user && user.token) {
      xhr.setRequestHeader("Authorization", "Bearer " + user.token);
      console.log("✅ Authorization header set:", user.token);
    } else {
      console.warn("⚠️ No token found in localStorage.");
    }
  }
});
