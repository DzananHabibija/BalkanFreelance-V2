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
        ${bioHtml}
        <div id="bio-controls">${bioControls}</div>
      `);

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
            <div class="mt-2">
              <button class="btn btn-sm btn-warning me-2" onclick="openEditGigModal(${gig.id}, '${gig.title}', ${gig.price}, '${gig.status}')">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="deleteGig(${gig.id})">Delete</button>
            </div>
          `;
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
