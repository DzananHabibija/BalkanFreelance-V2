function initAdminBlogs() {
  const blogsTable = $('#blogsTable').DataTable({
    ajax: {
      url: `${API_BASE}/blogs`,
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
      { data: 'admin_id' },
      { data: 'title' },
      { data: 'published_at' },
      {
        data: null,
        render: data => `
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning editBlogBtn" data-id="${data.id}">Edit</button>
            <button class="btn btn-danger deleteBlogBtn" data-id="${data.id}">Delete</button>
          </div>`
      }
    ]
  });

  const editModal = new bootstrap.Modal(document.getElementById('editBlogModal'));
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteBlogModal'));
  let selectedBlogId = null;

  // Edit
  $('#blogsTable tbody').on('click', '.editBlogBtn', function () {
    const data = blogsTable.row($(this).parents('tr')).data();
    $('#editBlogId').val(data.id);
    $('#editBlogTitle').val(data.title);
    $('#editBlogContent').val(data.content);
    $('#editBlogImage').val(data.image_url);
    $('#editBlogPublishedAt').val(data.published_at?.slice(0, 16));
    editModal.show();
  });

  $('#saveBlogChangesBtn').click(function () {
    const formData = new FormData();
    formData.append("id", $('#editBlogId').val());
    formData.append("title", $('#editBlogTitle').val());
    formData.append("content", $('#editBlogContent').val());
    formData.append("published_at", $('#editBlogPublishedAt').val());

    const imageFile = $('#editBlogImageFile')[0].files[0];
    if (imageFile) {
      formData.append("image", imageFile);
    }

    $.ajax({
      url: `${API_BASE}/blogs/update`,
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        $('#editBlogModal').modal('hide');
        blogsTable.ajax.reload(null, false);
        toastr.success('Blog updated successfully.');
      },
      error: function () {
        toastr.error('Failed to update blog.');
      }
    });
  });

  // Delete
  $('#blogsTable tbody').on('click', '.deleteBlogBtn', function () {
    selectedBlogId = $(this).data('id');
    deleteModal.show();
  });

  $('#confirmBlogDeleteBtn').click(function () {
    $.ajax({
      url: `${API_BASE}/blogs/delete/${selectedBlogId}`,
      type: 'DELETE',
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        deleteModal.hide();
        blogsTable.ajax.reload(null, false);
        toastr.success('Blog deleted.');
      },
      error: function () {
        toastr.error('Failed to delete blog.');
      }
    });
  });

  // Add
  $('#addBlogBtn').click(function () {
    $('#addBlogModal').modal('show');
  });

  $('#createBlogBtn').click(function () {
    const formData = new FormData();
    formData.append("admin_id", $('#addBlogAdminId').val());
    formData.append("title", $('#addBlogTitle').val());
    formData.append("content", $('#addBlogContent').val());
    formData.append("published_at", $('#addBlogPublishedAt').val());

    const imageFile = $('#addBlogImage')[0].files[0];
    if (imageFile) {
      formData.append("image", imageFile);
    }

    $.ajax({
      url: `${API_BASE}/blogs/add`,
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function (xhr) {
        const user = Utils.get_from_localstorage("user");
        if (user && user.token) {
          xhr.setRequestHeader("Authorization", "Bearer " + user.token);
        }
      },
      success: function () {
        $('#addBlogModal').modal('hide');
        $('#blogsTable').DataTable().ajax.reload(null, false);
        toastr.success('Blog created successfully.');
      },
      error: function () {
        toastr.error('Failed to create blog.');
      }
    });
  });
}
