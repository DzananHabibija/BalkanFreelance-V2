$(document).ready(function () {
    // Load categories into the dropdown
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

        const payload = {
    title: $('#title').val(),
    description: $('#description').val(),
    content: $('#content').val(),
    category_id: $('#category').val(),  // 👈 Use category ID, not name
    tags: $('#tags').val(),
    price: $('#price').val(),
    user_id: Utils.get_from_localstorage("user")?.id
    };


    $.ajax({
      url: 'http://localhost/BalkanFreelance/backend/gigs/add',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
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
