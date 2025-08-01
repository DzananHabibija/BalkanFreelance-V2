// const API_BASE = "http://localhost/BalkanFreelance/backend";

// let table;
// let selectedUserId = null;

// $(document).ready(function () {
//   // Initialize DataTable
//   table = $('#usersTable').DataTable({
//     ajax: {
//       url: `${API_BASE}/users/`,
//       dataSrc: ''
//     },
//     columns: [
//       { data: 'id' },
//       { data: 'first_name' },
//       { data: 'last_name' },
//       { data: 'email' },
//       { data: 'country_id' },
//       { data: 'bio' },
//       { data: 'created_at' },
//       { data: 'balance' },
//       {
//         data: 'isAdmin',
//         render: val => val == 1 ? 'Admin' : 'User'
//       },
//       {
//         data: null,
//         render: data => `
//           <div class="btn-group btn-group-sm" role="group">
//         <button class="btn btn-warning editUserBtn" data-id="${data.id}">Edit</button>
//         <button class="btn btn-danger deleteUserBtn" data-id="${data.id}">Delete</button>
//          </div>`
//       }
//     ]
//   });

// const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));

// $('#usersTable tbody').on('click', '.editUserBtn', function () {
//   const data = table.row($(this).parents('tr')).data();
//   $('#editUserId').val(data.id);
//   $('#editFirstName').val(data.first_name);
//   $('#editLastName').val(data.last_name);
//   $('#editEmail').val(data.email);
//   $('#editCountryId').val(data.country_id);
//   $('#editBio').val(data.bio);
//   $('#editBalance').val(data.balance);
//   $('#editRole').val(data.isAdmin);
//   editModal.show();
// });

// $('#saveUserChangesBtn').click(function () {
//   const updatedUser = {
//     id: $('#editUserId').val(),
//     first_name: $('#editFirstName').val(),
//     last_name: $('#editLastName').val(),
//     email: $('#editEmail').val(),
//     country_id: $('#editCountryId').val(),
//     bio: $('#editBio').val(),
//     balance: $('#editBalance').val(),
//     isAdmin: $('#editRole').val()
//   };

//   $.post(`${API_BASE}/users/update`, updatedUser, function () {
//     editModal.hide();
//     table.ajax.reload(null, false);
//     toastr.success('User was edited successfully.');
//   }).fail(() => {
//     toastr.error('Failed to update user.');
//   });
// });

//   // Handle Delete Button
//   const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

//   $('#usersTable tbody').on('click', '.deleteUserBtn', function () {
//     selectedUserId = $(this).data('id');
//     deleteModal.show();

//   });

//   // Confirm Delete
//   $('#confirmDeleteBtn').click(function () {
//     $.ajax({
//       url: `${API_BASE}/users/delete/${selectedUserId}`,
//       type: 'DELETE',
//       success: function () {
//         deleteModal.hide();
//         table.ajax.reload(null, false); 
//         toastr.success('User was successfully deleted.');
//       },
//       error: function () {
//         alert('Failed to delete user.');
//       }
//     });
//   });
// });