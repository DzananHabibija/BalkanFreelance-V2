function initAdminCategories() {
  const categoriesTable = $('#categoriesTable').DataTable({
    ajax: {
      url: `${API_BASE}/categories`,
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
      { data: 'name' },
      {
        data: null,
        render: data => `
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning editCategoryBtn" data-id="${data.id}">Edit</button>
            <button class="btn btn-danger deleteCategoryBtn" data-id="${data.id}">Delete</button>
          </div>`
      }
    ]
  });

  const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
  let selectedCategoryId = null;

  // Edit
  $('#categoriesTable tbody').on('click', '.editCategoryBtn', function () {
    const data = categoriesTable.row($(this).parents('tr')).data();
    $('#editCategoryId').val(data.id);
    $('#editCategoryName').val(data.name);
    editModal.show();
  });

  $('#saveCategoryChangesBtn').click(function () {
    const updated = {
      id: $('#editCategoryId').val(),
      name: $('#editCategoryName').val()
    };

    $.ajax({
      url: `${API_BASE}/categories/update`,
      method: 'POST',
      data: updated,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        editModal.hide();
        categoriesTable.ajax.reload(null, false);
        toastr.success('Category updated.');
      },
      error: function () {
        toastr.error('Update failed.');
      }
    });
  });

  // Delete
  $('#categoriesTable tbody').on('click', '.deleteCategoryBtn', function () {
    selectedCategoryId = $(this).data('id');
    deleteModal.show();
  });

  $('#confirmCategoryDeleteBtn').click(function () {
    $.ajax({
      url: `${API_BASE}/categories/delete/${selectedCategoryId}`,
      type: 'DELETE',
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        deleteModal.hide();
        categoriesTable.ajax.reload(null, false);
        toastr.success('Category deleted.');
      },
      error: function () {
        toastr.error('Deletion failed.');
      }
    });
  });

  // Add
  $('#addCategoryBtn').click(function () {
    $('#addCategoryModal').modal('show');
  });

  $('#createCategoryBtn').click(function () {
    const newCategory = {
      name: $('#addCategoryName').val()
    };

    if (!newCategory.name) {
      toastr.error('Please provide a category name.');
      return;
    }

    $.ajax({
      url: `${API_BASE}/categories/add`,
      method: 'POST',
      data: newCategory,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        $('#addCategoryModal').modal('hide');
        $('#categoriesTable').DataTable().ajax.reload(null, false);
        toastr.success('Category created successfully.');
      },
      error: function () {
        toastr.error('Failed to create category.');
      }
    });
  });
}
