$.ajaxSetup({
    beforeSend: function (xhr) {
        if (Utils.get_from_localstorage("user")) {
          xhr.setRequestHeader(
            "Authorization",
            Utils.get_from_localstorage("user").token
          );
        }
      }
    });

