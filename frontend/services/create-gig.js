$(document).ready(function () {
  // Load categories
  $.ajax({
    url: 'http://localhost/BalkanFreelance/backend/categories',
    type: 'GET',
    success: function (categories) {
      const categorySelect = $('#category');
      categories.forEach(cat => {
        categorySelect.append(`<option value="${cat.id}">${cat.name}</option>`);
      });
    },
    error: function (xhr) {
      alert("Failed to load categories: " + xhr.responseText);
    }
  });

  $('#createGigForm').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append("title", $('#title').val());
    formData.append("description", $('#description').val());
    formData.append("content", $('#content').val());
    formData.append("category_id", $('#category').val());
    formData.append("tags", $('#tags').val());
    formData.append("price", $('#price').val());
    formData.append("user_id", Utils.get_from_localstorage("user")?.id);

    const imageFile = $('#gigImage')[0].files[0];
    if (imageFile) {
      formData.append("image", imageFile);
    }

    $.ajax({
      url: 'http://localhost/BalkanFreelance/backend/gigs/add',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        alert("Gig created successfully!");
        window.location.hash = "#home";
      },
      error: function (xhr) {
        alert("Error: " + xhr.responseText);
      }
    });
  });
});
