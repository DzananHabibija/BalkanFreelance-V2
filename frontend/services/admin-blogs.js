function initAdminBlogs() {
  const blogsTable = $('#blogsTable').DataTable({
    ajax: {
      url: `${API_BASE}/blogs`,
      dataSrc: ''
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
    const updatedBlog = {
      id: $('#editBlogId').val(),
      title: $('#editBlogTitle').val(),
      content: $('#editBlogContent').val(),
      image_url: $('#editBlogImage').val(),
      published_at: $('#editBlogPublishedAt').val()
    };

    $.post(`${API_BASE}/blogs/update`, updatedBlog, function () {
      editModal.hide();
      blogsTable.ajax.reload(null, false);
      toastr.success('Blog updated successfully.');
    }).fail(() => {
      toastr.error('Failed to update blog.');
    });
  });

  $('#blogsTable tbody').on('click', '.deleteBlogBtn', function () {
    selectedBlogId = $(this).data('id');
    deleteModal.show();
  });

  $('#confirmBlogDeleteBtn').click(function () {
    $.ajax({
      url: `${API_BASE}/blogs/delete/${selectedBlogId}`,
      type: 'DELETE',
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
}




$('#addBlogBtn').click(function () {
  $('#addBlogModal').modal('show');
});

$('#createBlogBtn').click(function () {
  const newBlog = {
    admin_id: $('#addBlogAdminId').val(),
    title: $('#addBlogTitle').val(),
    content: $('#addBlogContent').val(),
    image_url: $('#addBlogImageUrl').val(),
    published_at: $('#addBlogPublishedAt').val()
  };

  if (!newBlog.admin_id || !newBlog.title || !newBlog.content) {
    toastr.error('Please fill in required fields (admin_id, title, content).');
    return;
  }

  $.post(`${API_BASE}/blogs/add`, newBlog, function () {
    $('#addBlogModal').modal('hide');
    $('#blogsTable').DataTable().ajax.reload(null, false);
    toastr.success('Blog created successfully.');
  }).fail(() => {
    toastr.error('Failed to create blog.');
  });
});
