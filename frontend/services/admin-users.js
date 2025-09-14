function initAdminUsers() {
  console.log("DataTables loaded?", typeof $.fn.dataTable);
  console.log("DataTables available in admin-users.js?", typeof $.fn.dataTable);

  let usersTable = $('#usersTable').DataTable({
    ajax: {
      url: `${API_BASE}/users/`,
      dataSrc: '',
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      }
    },
    columns: [
      { data: 'id' },
      { data: 'first_name' },
      { data: 'last_name' },
      { data: 'email' },
      { data: 'country_id' },
      { data: 'bio' },
      { data: 'created_at' },
      { data: 'balance' },
      {
        data: 'isAdmin',
        render: val => val == 1 ? 'Admin' : 'User'
      },
      {
        data: null,
        render: data => `
          <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-warning editUserBtn" data-id="${data.id}">Edit</button>
            <button class="btn btn-danger deleteUserBtn" data-id="${data.id}">Delete</button>
          </div>`
      }
    ]
  });

  const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
  let selectedUserId = null;

  // Edit button
  $('#usersTable tbody').on('click', '.editUserBtn', function () {
    const data = usersTable.row($(this).parents('tr')).data();
    $('#editUserId').val(data.id);
    $('#editFirstName').val(data.first_name);
    $('#editLastName').val(data.last_name);
    $('#editEmail').val(data.email);
    $('#editCountryId').val(data.country_id);
    $('#editBio').val(data.bio);
    $('#editBalance').val(data.balance);
    $('#editRole').val(data.isAdmin);
    editModal.show();
  });

  // Save edited user
  $('#saveUserChangesBtn').click(function () {
    const updatedUser = {
      id: $('#editUserId').val(),
      first_name: $('#editFirstName').val(),
      last_name: $('#editLastName').val(),
      email: $('#editEmail').val(),
      country_id: $('#editCountryId').val(),
      bio: $('#editBio').val(),
      balance: $('#editBalance').val(),
      isAdmin: $('#editRole').val()
    };

    $.ajax({
      url: `${API_BASE}/users/update`,
      method: 'POST',
      data: updatedUser,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        editModal.hide();
        usersTable.ajax.reload(null, false);
        toastr.success('User was edited successfully.');
      },
      error: function () {
        toastr.error('Failed to update user.');
      }
    });
  });

  // Delete button
  $('#usersTable tbody').on('click', '.deleteUserBtn', function () {
    selectedUserId = $(this).data('id');
    deleteModal.show();
  });

  // Confirm delete
  $('#confirmDeleteBtn').click(function () {
    $.ajax({
      url: `${API_BASE}/users/delete/${selectedUserId}`,
      type: 'DELETE',
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        deleteModal.hide();
        usersTable.ajax.reload(null, false);
        toastr.success('User was successfully deleted.');
      },
      error: function () {
        alert('Failed to delete user.');
      }
    });
  });

  // Add new user modal
  $('#addUserBtn').click(function () {
    $('#addUserModal').modal('show');
  });

  // Create new user
  $('#createUserBtn').click(function () {
    const newUser = {
      first_name: $('#addFirstName').val(),
      last_name: $('#addLastName').val(),
      email: $('#addEmail').val(),
      password: $('#addPassword').val(),
      country_id: $('#addCountryId').val(),
      phone_number: $('#addPhoneNumber').val(),
      bio: $('#addBio').val(),
      balance: $('#addBalance').val(),
      isAdmin: $('#addRole').val()
    };


    if (!newUser.first_name || !newUser.last_name || !newUser.email || !newUser.password) {
      toastr.error('Please fill in all required fields.');
      return;
    }

    $.ajax({
      url: `${API_BASE}/users/add`,
      method: 'POST',
      data: newUser,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        $('#addUserModal').modal('hide');
        $('#usersTable').DataTable().ajax.reload(null, false);
        toastr.success('User created successfully.');
      },
      error: function () {
        toastr.error('Failed to create user.');
      }
    });
  });
}
