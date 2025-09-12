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

      $("#user-info").html(`
        <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
        <p><strong>Email:</strong> ${user.email}</p>
        <p><strong>Phone:</strong> <span id="phone-display">${user.phone_number || "Not provided"}</span></p>
        <p><strong>Balance:</strong> $<span id="userBalance">0.00</span></p>
        ${bioHtml}
        <div id="bio-controls">${bioControls}</div>
        ${currentUser && parseInt(currentUser.id) === parseInt(user.id) ? `
          <button class="btn btn-sm btn-outline-primary" onclick="enablePhoneEdit('${user.phone_number || ""}', ${user.id})">Change Phone</button>
        ` : ""}
      `);


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
          html += `
            <div class="mt-2 mb-2">
              <button class="btn btn-sm btn-warning me-2" onclick="openEditGigModal(${gig.id}, '${gig.title}', ${gig.price}, '${gig.status}')">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="deleteGig(${gig.id})">Delete</button>
            </div>
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

// Enable inline bio editing
function enableBioEdit(currentBio, userId) {
  $("#bio-display").replaceWith(`
    <div id="bio-edit">
      <textarea id="bio-input" class="form-control mb-2">${currentBio}</textarea>
      <button class="btn btn-sm btn-success me-2" onclick="saveBio(${userId})">Save</button>
      <button class="btn btn-sm btn-secondary" onclick="location.reload()">Cancel</button>
    </div>
  `);
  $("#bio-controls").remove(); // Remove the "Change Bio" button
}

// Save bio update
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

// Gig editing
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

      const list = applications.map(app => `
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong>${app.user_first_name} ${app.user_last_name}</strong> (${app.user_email})
            <div class="text-muted small">Applied on ${new Date(app.created_at).toLocaleDateString()}</div>
          </div>
          <div>
            <span class="badge bg-${getStatusBadgeClass(app.status)}">${app.status}</span>

            ${app.status === "pending" ? 
              `<button class="btn btn-sm btn-primary ms-2" onclick="approveApplicant(${gigId}, ${app.user_id})">Approve</button>` 
              : ""}

            ${app.status === "approved" ? 
              (app.paid === 1 
                ? `<span class="badge bg-success ms-2">Paid</span>` 
                : `<button class="btn btn-sm btn-success ms-2" onclick="payFreelancer(${gigId}, ${app.user_id})">Pay</button>`)
              : ""}
          </div>
        </li>
      `).join("");

      $(container).html(`<ul class="list-group">${list}</ul>`);
    },
    error: function () {
      toastr.error("Failed to load applications.");
    }
  });
}




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

function payFreelancer(gigId, userId) {
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
      location.reload();
    },
    error: function (xhr) {
      toastr.error("Payment failed: " + xhr.responseText);
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
          html += `
            <div class="mt-2 mb-2">
              <button class="btn btn-sm btn-warning me-2" onclick="openEditGigModal(${gig.id}, '${gig.title}', ${gig.price}, '${gig.status}')">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="deleteGig(${gig.id})">Delete</button>
            </div>
          `;
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


