function loadUserProfile(userId) {
  $.ajax({
    url: `${API_BASE}/user-profile/${userId}`,
    method: "GET",
    dataType: "json",
    success: function (res) {
      const user = res.user;
      const gigs = res.gigs;
      const currentUser = JSON.parse(localStorage.getItem("user"));

      let bioHtml = `<p id="bio-display"><strong>Bio:</strong> ${user.bio || "No bio provided"}</p>`;
      let bioControls = "";

      if (currentUser && parseInt(currentUser.id) === parseInt(user.id)) {
        bioControls = `
          <button class="btn btn-sm btn-outline-primary mb-2" onclick="enableBioEdit('${user.bio || ""}', ${user.id})">Change Bio</button>
        `;
      }

      // First render the static profile info
      $("#user-info").html(`
        <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
        <p><strong>Email:</strong> ${user.email}</p>
        <p><strong>Phone:</strong> <span id="phone-display">${user.phone_number || "Not provided"}</span></p>
        <div id="userRating"><span class="text-muted">Loading rating...</span></div>
        <p><strong>Balance:</strong> $<span id="userBalance">0.00</span></p>
        ${bioHtml}
        <div id="bio-controls">${bioControls}</div>
        ${currentUser && parseInt(currentUser.id) === parseInt(user.id) ? `
          <button class="btn btn-sm btn-outline-primary" onclick="enablePhoneEdit('${user.phone_number || ""}', ${user.id})">Change Phone</button>
        ` : ""}
      `);

      // ✅ Fetch and display user rating summary safely
      $.ajax({
        url: `${API_BASE}/reviews/summary/${user.id}`,
        method: "GET",
        success: function (summary) {
          const avg = summary && summary.average_rating ? parseFloat(summary.average_rating) : null;
          const total = summary && summary.total_reviews ? summary.total_reviews : 0;

          if (avg !== null && !isNaN(avg) && total > 0) {
            $("#userRating").html(`
              <p><strong>Rating:</strong> ⭐ ${avg.toFixed(1)} 
              (${total} review${total !== 1 ? "s" : ""})</p>
            `);
          } else {
            $("#userRating").html(`<p><strong>Rating:</strong> ⭐ N/A (no reviews yet)</p>`);
          }
        },
        error: function () {
          $("#userRating").html(`<p><strong>Rating:</strong> ⭐ N/A</p>`);
        }
      });

      loadUserBalance(user.id);

      if (!gigs.length) {
        $("#user-gigs").html(`<p class="text-muted">No gigs created by this user.</p>`);
        return;
      }

      const gigsHtml = gigs.map(gig => {
        let html = `
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title">${gig.title}</h5>
                <p><strong>Price:</strong> $${gig.price}</p>
                <p><strong>Status:</strong> ${gig.status}</p>
                <p><small class="text-muted">Created: ${gig.created_at}</small></p>
        `;

        if (currentUser && parseInt(currentUser.id) === parseInt(gig.user_id)) {
          if (gig.is_locked) {
            html += `
              <div class="mt-2 mb-2">
                <button class="btn btn-sm btn-warning me-2" disabled>Edit</button>
                <button class="btn btn-sm btn-danger" disabled>Delete</button>
                <div class="text-muted small">Locked: Freelancer approved but not paid yet</div>
              </div>
            `;
          } else {
            html += `
              <div class="mt-2 mb-2">
                <button class="btn btn-sm btn-warning me-2" onclick="openEditGigModal(${gig.id}, '${gig.title}', ${gig.price}, '${gig.status}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteGig(${gig.id})">Delete</button>
              </div>
            `;
          }

          html += `
            <div class="border-top pt-2" id="applications-${gig.id}">
              <div class="text-muted small">Loading applications...</div>
            </div>
          `;

          setTimeout(() => loadApplicationsForGig(gig.id, $(`#applications-${gig.id}`)), 0);
        }

        html += `</div></div></div>`;
        return html;
      }).join("");

      $("#user-gigs").html(gigsHtml);
    },
    error: function () {
      $("#user-info").html(`<div class="alert alert-danger">Failed to load user profile.</div>`);
    }
  });
}


function enableBioEdit(currentBio, userId) {
  $("#bio-display").replaceWith(`
    <div id="bio-edit">
      <textarea id="bio-input" class="form-control mb-2">${currentBio}</textarea>
      <button class="btn btn-sm btn-success me-2" onclick="saveBio(${userId})">Save</button>
      <button class="btn btn-sm btn-secondary" onclick="location.reload()">Cancel</button>
    </div>
  `);
  $("#bio-controls").remove(); 
}

function saveBio(userId) {
  const newBio = $("#bio-input").val();
  $.ajax({
    url: `${API_BASE}/users/${userId}/bio`,
    type: "PUT",
    contentType: "application/json",
    data: JSON.stringify({ bio: newBio }),
    success: function () {
      toastr.success("Bio updated successfully");
      location.reload();
    },
    error: function () {
      toastr.error("Failed to update bio. Please try again.");
    }
  });
}

function openEditGigModal(id, title, price, status) {
  $("#edit-gig-id").val(id);
  $("#edit-gig-title").val(title);
  $("#edit-gig-price").val(price);
  $("#edit-gig-status").val(status);
  const modal = new bootstrap.Modal(document.getElementById('editGigModal'));
  modal.show();
}

function submitEditGig() {
  const id = $("#edit-gig-id").val();
  const title = $("#edit-gig-title").val();
  const price = $("#edit-gig-price").val();
  const status = $("#edit-gig-status").val();

  $.ajax({
    url: `${API_BASE}/gigs/${id}`,
    type: "PUT",
    contentType: "application/json",
    data: JSON.stringify({ title, price, status }),
    success: function () {
      toastr.success("Bio updated successfully");
      location.reload();
    },
    error: function () {
      toastr.error("Failed to update gig. Please try again.");
    }
  });
}

function deleteGig(id) {
  if (!confirm("Are you sure you want to delete this gig?")) return;

  $.ajax({
    url: `${API_BASE}/gigs/delete/${id}`,
    type: "DELETE",
    success: function () {
      toastr.success("Gig deleted successfully");
      location.reload();
    },
    error: function () {
      toastr.error("Failed to delete gig. Please try again.");
    }
  });
}

function loadUserBalance(userId) {
  $.ajax({
    url: `${API_BASE}/user/${userId}/balance`,
    method: "GET",
    success: function (res) {
      $("#userBalance").text(parseFloat(res.balance).toFixed(2));
    },
    error: function () {
      $("#userBalance").text("0.00");
    }
  });
}


function loadApplicationsForGig(gigId, container) {
  const token = localStorage.getItem("jwt")?.replace(/"/g, "");
  $.ajax({
    url: `${API_BASE}/gigs/${gigId}/applications`,
    type: "GET",
    headers: { Authorization: "Bearer " + token },
    success: function (applications) {
      if (!applications.length) {
        $(container).html(`<p class="text-muted">No applications yet.</p>`);
        return;
      }

      // Fetch rating summaries for all applicants in parallel
      const ratingRequests = applications.map(app => {
        return $.ajax({
          url: `${API_BASE}/reviews/summary/${app.user_id}`,
          method: "GET"
        }).then(summary => ({
          userId: app.user_id,
          summary: summary
        })).catch(() => ({
          userId: app.user_id,
          summary: { average_rating: null, total_reviews: 0 }
        }));
      });

      Promise.all(ratingRequests).then(results => {
        const ratingMap = {};
        results.forEach(r => {
          ratingMap[r.userId] = r.summary;
        });

        const list = applications.map(app => {
          const summary = ratingMap[app.user_id] || {};
          const avg = (summary && summary.average_rating) ? parseFloat(summary.average_rating).toFixed(1) : "N/A";
          const total = summary.total_reviews || 0;

          return `
            <li class="list-group-item application-entry"
                data-message="${escapeHtml(app.application_message || "No message")}"
                data-cv="${app.application_cv || ''}">

              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong>${app.user_first_name} ${app.user_last_name}</strong> (${app.user_email})
                  <div class="text-muted small">Applied on ${new Date(app.applied_at).toLocaleDateString()}</div>
                  <div class="text-warning small">
                    ⭐ ${avg} (${total} review${total !== 1 ? "s" : ""})
                  </div>
                </div>
                <div>
                  <span class="badge bg-${getStatusBadgeClass(app.status)}">${app.status}</span>
                  ${app.status === "approved" && app.paid === 1 ? `<span class="badge bg-success ms-2">Paid</span>` : ""}
                </div>
              </div>

              <div class="mt-2">
                ${app.status === "pending" ? `
                  <button class="btn btn-sm btn-primary me-2" onclick="event.stopPropagation(); approveApplicant(${gigId}, ${app.user_id})">Approve</button>
                  <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); rejectApplicant(${gigId}, ${app.user_id})">Reject</button>
                ` : ""}

                ${app.status === "approved" && app.paid !== 1 ? `
                  <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); payFreelancer(${gigId}, ${app.user_id})">Pay</button>
                ` : ""}
              </div>
            </li>
          `;
        }).join("");

        $(container).html(`<ul class="list-group">${list}</ul>`);
      });
    },
    error: function () {
      toastr.error("Failed to load applications.");
    }
  });
}




// Make application row clickable unless user clicks an internal button
$(document).on('click', '.application-entry', function (e) {
  if ($(e.target).is('button')) return; // prevent click if a button was clicked

  const message = $(this).data("message");
  const cv = $(this).data("cv");

  viewApplication(message, cv);
});



function approveApplicant(gigId, userId) {
  const token = localStorage.getItem("jwt")?.replace(/"/g, "");
  $.ajax({
    url: `${API_BASE}/gigs/${gigId}/approve/${userId}`,
    type: "POST",
    headers: { Authorization: "Bearer " + token },
    success: function () {
      toastr.success("Applicant approved!");
      location.reload();
    },
    error: function (xhr) {
      toastr.error("Failed to approve: " + xhr.responseText);
    }
  });
}

let selectedGigId = null;
let selectedUserId = null;
let selectedPrice = null;

function promptReview(gigId, reviewedUserId) {
  $('#reviewGigId').val(gigId);
  $('#reviewedUserId').val(reviewedUserId);
  $('#ratingModal').modal('show');
}


function payFreelancer(gigId, userId) {
  selectedGigId = gigId;
  selectedUserId = userId;

  $.ajax({
    url: `${API_BASE}/gigs/${gigId}`,
    method: "GET",
    dataType: "json",
    success: function (gig) {
      selectedPrice = parseFloat(gig.price);

      const payModal = new bootstrap.Modal(document.getElementById('payModal'));
      payModal.show();

      $('#walletPayBtn').off('click').on('click', function () {
        const token = localStorage.getItem("jwt")?.replace(/"/g, "");
        const currentUser = JSON.parse(localStorage.getItem("user"));

        $.ajax({
          url: `${API_BASE}/gigs/${gigId}/pay/${userId}`,
          type: "POST",
          headers: { Authorization: "Bearer " + token },
          contentType: "application/json",
          data: JSON.stringify({ payer_id: currentUser.id }),
          success: function () {
            toastr.success("Payment successful!");
            payModal.hide();
            promptReview(gigId, userId); // <-- ask for review
          }
          ,
          error: function (xhr) {
            toastr.error("Payment failed: " + xhr.responseText);
          }
        });
      });

      $('#cryptoPayBtn').off('click').on('click', function () {
        handleCryptoPayment();
      });

      loadPayPalSdk(() => {
        renderPayPalButton();
      });
    },
    error: function () {
      toastr.error("Failed to load gig data.");
    }
  });
}




function enablePhoneEdit(currentPhone, userId) {
  $("#phone-display").replaceWith(`
    <div id="phone-edit">
      <input id="phone-input" class="form-control form-control-sm mb-2" value="${currentPhone}">
      <button class="btn btn-sm btn-success me-2" onclick="savePhone(${userId})">Save</button>
      <button class="btn btn-sm btn-secondary" onclick="location.reload()">Cancel</button>
    </div>
  `);
}

function savePhone(userId) {
  const newPhone = $("#phone-input").val();
  $.ajax({
    url: `${API_BASE}/users/${userId}/phone`,
    type: "PUT",
    contentType: "application/json",
    data: JSON.stringify({ phone_number: newPhone }),
    success: function () {
      toastr.success("Phone updated successfully");
      location.reload();
    },
    error: function () {
      toastr.error("Failed to update phone. Please try again.");
    }
  });
}

function getStatusBadgeClass(status) {
  switch (status) {
    case "approved": return "success";
    case "pending": return "secondary";
    case "rejected": return "danger";
    default: return "light";
  }
}

const currentUser = JSON.parse(localStorage.getItem("user"));
const token = localStorage.getItem("jwt")?.replace(/"/g, "");
function loadFavorites(userId) {
  $.ajax({
    url: `${API_BASE}/favorites/${userId}`,
    method: "GET",
    dataType: "json",
    success: function (favorites) {
      if (!favorites.length) {
        $("#user-favorites").html(`<p class="text-muted">No favorite gigs yet.</p>`);
        return;
      }

      const currentUser = JSON.parse(localStorage.getItem("user"));

      const favHtml = favorites.map(gig => {
        let html = `
          <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm border-primary">
              <div class="card-body">
                <h5 class="card-title text-primary">${gig.title}</h5>
                <p><strong>Price:</strong> $${gig.price}</p>
                <p><strong>Status:</strong> ${gig.status}</p>
                <p><small class="text-muted">Created: ${gig.created_at}</small></p>
                <button class="btn btn-sm btn-outline-danger" onclick="removeFavorite(${currentUser.id}, ${gig.id})">
                  Remove Favorite
                </button>
        `;

        if (currentUser && parseInt(currentUser.id) === parseInt(gig.user_id)) {
          if (gig.is_locked) {
            html += `
              <div class="mt-2 mb-2">
                <button class="btn btn-sm btn-warning me-2" disabled>Edit</button>
                <button class="btn btn-sm btn-danger" disabled>Delete</button>
                <div class="text-muted small">Locked: Freelancer approved but not yet paid</div>
              </div>
            `;
          } else {
            html += `
              <div class="mt-2 mb-2">
                <button class="btn btn-sm btn-warning me-2" onclick="openEditGigModal(${gig.id}, '${gig.title}', ${gig.price}, '${gig.status}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteGig(${gig.id})">Delete</button>
              </div>
            `;
          }
        }


        html += `</div></div></div>`;
        return html;
      }).join("");

      $("#user-favorites").html(favHtml);
    },
    error: function () {
      $("#user-favorites").html(`<div class="alert alert-danger">Failed to load favorites.</div>`);
    }
  });
}

function removeFavorite(userId, gigId) {
  if (!confirm("Remove this gig from favorites?")) return;

  $.ajax({
    url: `${API_BASE}/favorites/delete/${currentUser.id}/${gigId}`,
    type: "DELETE",
    headers: { Authorization: "Bearer " + token },
    success: function () {
      toastr.success("Removed from favorites");
      loadFavorites(userId);
    },
    error: function () {
      toastr.error("Failed to remove favorite");
    }
  });
}

loadFavorites((currentUser.id));



function viewApplication(message, cvUrl) {
  $("#applicationMessage").text(message || "No message");

  if (cvUrl) {
    // Just strip the backend/ part if it's mistakenly included
    const cleanedUrl = cvUrl.replace(/^\/?backend\/?/, '').replace(/^\/+/, '');
    const cvDownloadUrl = `/` + cleanedUrl; // Relative to project root

    $("#applicationCv").html(
      `<a href="${cvDownloadUrl}" target="_blank" download class="btn btn-outline-primary">Download CV</a>`
    );
  } else {
    $("#applicationCv").html(`<span class="text-muted">No CV uploaded</span>`);
  }

  const modal = new bootstrap.Modal(document.getElementById("viewApplicationModal"));
  modal.show();
}


// helper for XSS safety
function escapeHtml(text) {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}




function renderPayPalButton() {
  $("#paypal-button-container").html(""); // clear old button

  paypal.Buttons({
    createOrder: function (data, actions) {

      return fetch(`${API_BASE}/paypal/create-order`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ amount: selectedPrice.toFixed(2) })
      })
      .then(res => res.json())
      .then(order => {
        return order.id;
      });
    },
    onApprove: function (data, actions) {
    console.log("Order approved:", data);


    return fetch(`${API_BASE}/paypal/capture-order/${data.orderID}`, {
      method: "POST"
    })
    .then(res => res.json())
    .then(details => {
      console.log("Capture successful:", details);
      toastr.success('Payment completed via PayPal');

      const currentUser = JSON.parse(localStorage.getItem("user"));

 
      $.ajax({
        url: `${API_BASE}/paypal/payment-success`,
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          sender_id: currentUser.id,
          receiver_id: selectedUserId,
          gig_id: selectedGigId,
          amount: selectedPrice
        }),
        success: function () {
          toastr.success("PayPal payment registered in system!");
          const modal = bootstrap.Modal.getInstance(document.getElementById('payModal'));
          modal.hide();
          promptReview(selectedGigId, selectedUserId);
        },
        error: function () {
          toastr.error("Failed to register PayPal payment.");
        }
      });
    })
    .catch(err => {
      console.error("Capture failed:", err);
      toastr.error("PayPal capture failed. Please check sandbox account setup.");
    });
  }
  }).render('#paypal-button-container');
}




function loadPayPalSdk(callback) {
  if (window.paypal) {
    callback();
    return;
  }

  $.ajax({
    url: `${API_BASE}/config/paypal`,
    method: "GET",
    success: function (res) {
      const script = document.createElement("script");
      script.src = `https://www.sandbox.paypal.com/sdk/js?client-id=${res.clientId}&currency=USD&intent=capture`;
      script.onload = callback;
      document.head.appendChild(script);
    },
    error: function () {
      toastr.error("Failed to load PayPal SDK.");
    }
  });
}


function rejectApplicant(gigId, userId) {
  const token = localStorage.getItem("jwt")?.replace(/"/g, "");
  $.ajax({
    url: `${API_BASE}/gigs/${gigId}/reject/${userId}`,
    type: "POST",
    headers: { Authorization: "Bearer " + token },
    success: function () {
      toastr.info("Applicant rejected.");
      location.reload();
    },
    error: function (xhr) {
      toastr.error("Failed to reject: " + xhr.responseText);
    }
  });
}


function simulateCryptoWebhook(paymentData) {
  $.ajax({
    url: `${API_BASE}/crypto/webhook`,
    method: "POST",
    contentType: "application/json",
    headers: {
      "X_CC_WEBHOOK_SIGNATURE": "fake_signature"
    },
    data: JSON.stringify({
      event: {
        type: "charge:confirmed",
        data: {
          metadata: {
            sender_id: paymentData.sender_id,
            receiver_id: paymentData.receiver_id,
            gig_id: paymentData.gig_id
          },
          pricing: {
            local: {
              amount: paymentData.amount
            }
          }
        }
      }
    }),
    success: function () {
      toastr.success("Simulated crypto payment completed!");
      const modal = bootstrap.Modal.getInstance(document.getElementById('payModal'));
      modal.hide();
      promptReview(paymentData.gig_id, paymentData.receiver_id);
    },
    error: function () {
      toastr.error("Simulated crypto webhook failed.");
    }
  });
}

function handleCryptoPayment() {
  const currentUser = JSON.parse(localStorage.getItem("user"));

  const paymentData = {
    amount: selectedPrice,
    gig_id: selectedGigId,
    sender_id: currentUser.id,
    receiver_id: selectedUserId
  };

  $.ajax({
    url: `${API_BASE}/crypto/create-payment`,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(paymentData),
    success: function (res) {
      const popup = window.open(res.payment_url, "_blank");

      const timer = setInterval(() => {
        if (popup.closed) {
          clearInterval(timer);
          simulateCryptoWebhook(paymentData);
        }
      }, 500);
    },
    error: function () {
      toastr.error("Failed to initiate crypto payment.");
    }
  });
}


$('#reviewForm').on('submit', function (e) {
  e.preventDefault();
  const token = localStorage.getItem("jwt")?.replace(/"/g, "");
  const currentUser = JSON.parse(localStorage.getItem("user"));

  const data = {
    reviewer_id: currentUser.id,
    reviewed_id: $('#reviewedUserId').val(),
    gig_id: $('#reviewGigId').val(),
    rating: parseInt($('#rating').val()),
    review_comment: $('#reviewComment').val()
  };

  $.ajax({
    url: `${API_BASE}/reviews`,
    method: "POST",
    contentType: "application/json",
    headers: { Authorization: `Bearer ${token}` },
    data: JSON.stringify(data),
    success: function () {
      toastr.success("Review submitted!");
      $('#ratingModal').modal('hide');
      location.reload();
    },
    error: function (xhr) {
      toastr.error("Failed to submit review: " + (xhr.responseJSON?.error || "Unknown error"));
    }
  });
});
