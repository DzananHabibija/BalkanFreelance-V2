function initAdminCategories() {
  const categoriesTable = $('#categoriesTable').DataTable({
    ajax: {
      url: `${API_BASE}/categories`,
      dataSrc: ''
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

    $.post(`${API_BASE}/categories/update`, updated, function () {
      editModal.hide();
      categoriesTable.ajax.reload(null, false);
      toastr.success('Category updated.');
    }).fail(() => {
      toastr.error('Update failed.');
    });
  });

  $('#categoriesTable tbody').on('click', '.deleteCategoryBtn', function () {
    selectedCategoryId = $(this).data('id');
    deleteModal.show();
  });

  $('#confirmCategoryDeleteBtn').click(function () {
    $.ajax({
      url: `${API_BASE}/categories/delete/${selectedCategoryId}`,
      type: 'DELETE',
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
}




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

  $.post(`${API_BASE}/categories/add`, newCategory, function () {
    $('#addCategoryModal').modal('hide');
    $('#categoriesTable').DataTable().ajax.reload(null, false);
    toastr.success('Category created successfully.');
  }).fail(() => {
    toastr.error('Failed to create category.');
  });
});
