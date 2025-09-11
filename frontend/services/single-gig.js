if (typeof SingleGig === 'undefined') {
  const SingleGig = (function () {
    const BASE_URL = "http://localhost/BalkanFreelance";

    function isSingleGigRoute(hash) {
      return hash === "#single-gig" || /^#single-gig(\/|\?)/i.test(hash || "");
    }

    function getCurrentGigId() {
      try {
        const stored = sessionStorage.getItem("bf_current_gig_id");
        if (stored) return stored;
      } catch (e) {}

      const hash = window.location.hash || "";
      const m = hash.match(/^#single-gig\/([^\/\?\#]+)/i);
      if (m && m[1]) return decodeURIComponent(m[1]);

      const qIndex = hash.indexOf("?");
      if (qIndex !== -1) {
        const params = new URLSearchParams(hash.substring(qIndex + 1));
        return params.get("id");
      }

      return null;
    }

    function render() {
      if (typeof $ === 'undefined') {
        console.error("jQuery ($) is not defined.");
        return;
      }

      const gigId = getCurrentGigId();
      const $container = $("#gigContainer");

      if (!gigId) {
        $container.html(`<div class="alert alert-danger">Invalid gig ID</div>`);
        return;
      }

      const token = localStorage.getItem("jwt")?.replace(/"/g, "");

      $.ajax({
        url: `${BASE_URL}/backend/gigs/full/${gigId}`,
        type: 'GET',
        headers: {
          'Authorization': 'Bearer ' + token
        },
        success: function (gig) {
          $('#loadingSpinner').remove();

          const imagePath = gig.gig_image_url?.startsWith("http")
            ? gig.gig_image_url
            : (gig.gig_image_url ? BASE_URL + gig.gig_image_url : 'https://via.placeholder.com/1200x500?text=No+Image');

          const createdAt = new Date(gig.created_at).toLocaleDateString();

          let parsedContent = "<em>No content available.</em>";
          try {
            if (typeof marked !== 'undefined') {
              parsedContent = marked.parse(gig.content || "");
            }
          } catch (err) {
            console.warn("Markdown parsing failed:", err);
          }

          const gigHtml = `
            <div class="card shadow border-0">
              <div class="card-body pb-0">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-uppercase fw-bold text-primary">${gig.category_name || 'Uncategorized'}</span>
                  <span class="text-muted">${createdAt}</span>
                </div>

                <h2 class="fw-bold">${gig.title}</h2>
                <p class="text-muted mb-3">by <strong>${gig.user_first_name && gig.user_last_name ? gig.user_first_name + ' ' + gig.user_last_name : 'Anonymous'}</strong></p>
              </div>

              <img src="${imagePath}" class="img-fluid rounded-3" alt="Gig Image" style="object-fit: cover; max-height: 400px;">

              <div class="card-body mt-4">
                <h5 class="fw-semibold">Description</h5>
                <p>${gig.description}</p>

                <hr>

                <h5 class="fw-semibold">Gig Content</h5>
                <div class="markdown-body mb-4">${parsedContent}</div>

                <div class="mb-3">
                  <span class="badge bg-${gig.status === 'open' ? 'success' : 'secondary'}">Status: ${gig.status || 'Open'}</span>
                </div>

                <div class="d-flex gap-3 mb-3">
                  <button class="btn btn-primary px-4" id="applyBtn">Apply</button>
                  <button class="btn btn-outline-danger px-4" id="favBtn">
                    <i class="fas fa-heart me-1"></i> Favorite
                  </button>
                </div>

                ${gig.tags ? `
                  <div class="mt-3">
                    ${gig.tags.split(',').map(tag => `<span class="badge bg-info text-dark me-2">${tag.trim()}</span>`).join('')}
                  </div>` : ''}
              </div>
            </div>
          `;

          $container.html(gigHtml);

          // Enhanced Apply Button Logic
        const currentUser = JSON.parse(localStorage.getItem("user"));
        const $applyBtn = $('#applyBtn');

      if (currentUser && currentUser.id === gig.user_id) {
        // Hide apply button for gig owner
        $applyBtn.remove();
      } else if (currentUser) {
        // Check application status for current user
        $.ajax({
          url: `${BASE_URL}/backend/gigs/${gig.id}/application-status/${currentUser.id}`,
          type: "GET",
          headers: { Authorization: "Bearer " + token },
          success: function (res) {
            const status = res?.status;

      if (status === "approved") {
        $applyBtn
          .addClass("btn-success")
          .removeClass("btn-primary")
          .html('<i class="fas fa-check-circle me-1"></i> Approved – Contact Owner')
          .prop("disabled", true);
      } else if (status === "pending") {
        $applyBtn
          .addClass("btn-warning")
          .removeClass("btn-primary")
          .text("Applied – Pending")
          .prop("disabled", true);
      } else if (status === "rejected") {
        $applyBtn
          .addClass("btn-secondary")
          .removeClass("btn-primary")
          .text("Application Rejected")
          .prop("disabled", true);
      } else {
        // Not applied yet → enable click to apply
        $applyBtn.on('click', function () {
          $.ajax({
            url: `${BASE_URL}/backend/gigs/${gig.id}/apply`,
            type: "POST",
            headers: { Authorization: "Bearer " + token },
            contentType: "application/json",
            data: JSON.stringify({ user_id: currentUser.id, cover_letter: "" }),
            success: function () {
              toastr.success("Application submitted!");
              $applyBtn
                .addClass("btn-warning")
                .removeClass("btn-primary")
                .text("Applied – Pending")
                .prop("disabled", true);
            },
            error: function (xhr) {
              toastr.error("Failed to apply: " + xhr.responseText);
            }
          });
              });
            }
          },
          error: function () {
            toastr.error("Could not check application status.");
          }
        });
      } else {
        $applyBtn.on('click', function () {
          toastr.warning("Please log in to apply.");
        });
      }



          $('#favBtn').on('click', function () {
            toastr.info("Added to favorites!");
          });
        },
        error: function (xhr) {
          console.error("Error loading gig:", xhr.responseText);
          $container.html(`<div class="alert alert-danger">Error loading gig: ${xhr.responseText}</div>`);
        }
      });
    }

    return {
      init: function () {
        if (isSingleGigRoute(window.location.hash)) render();
        $(window).off(".singleGig").on("hashchange.singleGig", function () {
          if (isSingleGigRoute(window.location.hash)) render();
        });
      },
      render
    };
  })();

  $(document).ready(function () {
    SingleGig.init();
  });
}
