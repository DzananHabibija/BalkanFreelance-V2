function initAdminGigs() {
  console.log("🟢 initAdminGigs triggered");

  const gigsTable = $('#gigsTable').DataTable({
    ajax: {
      url: `${API_BASE}/gigs`,
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
      { data: 'user_id' },
      { data: 'title' },
      { data: 'category_id' },
      { data: 'tags' },
      { data: 'price' },
      { data: 'status', defaultContent: 'N/A' },
      { data: 'created_at' },
      {
        data: null,
        render: data => `
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning editGigBtn" data-id="${data.id}">Edit</button>
            <button class="btn btn-danger deleteGigBtn" data-id="${data.id}">Delete</button>
          </div>`
      }
    ]
  });

  const editModal = new bootstrap.Modal(document.getElementById('editGigModal'));
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteGigModal'));
  let selectedGigId = null;

  // Edit logic
  $('#gigsTable tbody').on('click', '.editGigBtn', function () {
    const data = gigsTable.row($(this).parents('tr')).data();
    $('#editGigId').val(data.id);
    $('#editGigTitle').val(data.title);
    $('#editGigDescription').val(data.description);
    $('#editGigTags').val(data.tags);
    $('#editGigPrice').val(data.price);
    $('#editGigStatus').val(data.status);
    editModal.show();
  });

  $('#saveGigChangesBtn').click(function () {
    const updatedGig = {
      id: $('#editGigId').val(),
      title: $('#editGigTitle').val(),
      description: $('#editGigDescription').val(),
      tags: $('#editGigTags').val(),
      price: $('#editGigPrice').val(),
      status: $('#editGigStatus').val()
    };

    $.ajax({
      url: `${API_BASE}/gigs/update`,
      method: 'POST',
      data: updatedGig,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        editModal.hide();
        gigsTable.ajax.reload(null, false);
        toastr.success('Gig updated successfully.');
      },
      error: function () {
        toastr.error('Failed to update gig.');
      }
    });
  });

  // Delete logic
  $('#gigsTable tbody').on('click', '.deleteGigBtn', function () {
    selectedGigId = $(this).data('id');
    deleteModal.show();
  });

  $('#confirmGigDeleteBtn').click(function () {
    $.ajax({
      url: `${API_BASE}/gigs/delete/${selectedGigId}`,
      type: 'DELETE',
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        deleteModal.hide();
        gigsTable.ajax.reload(null, false);
        toastr.success('Gig deleted successfully.');
      },
      error: function () {
        toastr.error('Failed to delete gig.');
      }
    });
  });

  // Add new gig modal
  $('#addGigBtn').click(function () {
    $('#addGigModal').modal('show');
  });

  $('#createGigBtn').click(function () {
    const newGig = {
      user_id: $('#addGigUserId').val(),
      title: $('#addGigTitle').val(),
      description: $('#addGigDescription').val(),
      content: $('#addGigContent').val(),
      category_id: $('#addGigCategoryId').val(),
      tags: $('#addGigTags').val(),
      price: $('#addGigPrice').val(),
      status: $('#addGigStatus').val()
    };

    if (!newGig.user_id || !newGig.title || !newGig.description || !newGig.price) {
      toastr.error('Please fill in all required fields.');
      return;
    }

    $.ajax({
      url: `${API_BASE}/gigs/add`,
      method: 'POST',
      data: newGig,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        $('#addGigModal').modal('hide');
        $('#gigsTable').DataTable().ajax.reload(null, false);
        toastr.success('Gig created successfully.');
      },
      error: function () {
        toastr.error('Failed to create gig.');
      }
    });
  });
}
