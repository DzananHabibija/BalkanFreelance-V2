$(document).ready(function () {
  const urlParams = new URLSearchParams(window.location.search);
  const gigId = urlParams.get('id');

  if (!gigId) {
    $('#gigContainer').html(`<div class="alert alert-danger">Invalid Gig ID</div>`);
    return;
  }

  $.ajax({
    url: `http://localhost/BalkanFreelance/backend/gigs/${gigId}`,
    type: 'GET',
    success: function (gig) {
      $('#loadingSpinner').remove();

      const gigHtml = `
        <div class="card shadow-lg">
          <img src="${gig.image_url || 'https://via.placeholder.com/800x300?text=No+Image'}" class="card-img-top" alt="Gig Image">

          <div class="card-body">
            <h3 class="card-title">${gig.title}</h3>
            <p class="text-muted mb-1">Posted by: <strong>${gig.user_fullname || 'Anonymous'}</strong></p>
            <p class="text-muted mb-3">Category: ${gig.category_name || 'Uncategorized'}</p>

            <h5>Description</h5>
            <p>${gig.description}</p>

            <h5>Full Content</h5>
            <div class="markdown-body">${marked.parse(gig.content || "")}</div>

            <div class="mt-3">
              <span class="badge bg-info text-dark">Tags: ${gig.tags || 'None'}</span>
              <span class="badge bg-success ms-2">Price: $${gig.price}</span>
            </div>
          </div>
        </div>
      `;

      $('#gigContainer').html(gigHtml);
    },
    error: function (xhr) {
      $('#gigContainer').html(`<div class="alert alert-danger">Error loading gig: ${xhr.responseText}</div>`);
    }
  });
});
