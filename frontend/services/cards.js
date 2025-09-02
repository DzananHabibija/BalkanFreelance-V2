// services/cards.js
// Responsible ONLY for rendering gig cards. Data fetching happens in gig-filters.js

function renderGigs(gigs) {
  const $gigsContainer = $(".gigsContainer");
  $gigsContainer.empty();

  $.each(gigs, function(index, gig) {
    const $col = $("<div class='col-md-3 mb-4 d-flex justify-content-center'></div>");
    const $gigCard = $("<div class='card gig-card' style='width: 18rem;'></div>");
    const $gigImage = $("<img class='card-img-top' alt='Card image cap' src='assets/freelance.jpg'>");
    const $cardBody = $("<div class='card-body'></div>");
    const $gigTitle = $("<h5 class='card-title'></h5>").text(gig.title);
    const $gigDescription = $("<p class='card-text'></p>").text(gig.description);
    const $gigPrice = $("<p class='card-text fw-bold'></p>").text("Price: " + gig.price + " $");
    const $gigButton = $(`<a href="pages/single-gig.html?id=${gig.id}" class="btn btn-success">View Details</a>`);

    $cardBody.append($gigTitle, $gigDescription, $gigPrice, $gigButton);
    $gigCard.append($gigImage, $cardBody);
    $col.append($gigCard);
    $gigsContainer.append($col);
  });
}

// No document.ready, no event bindings, no AJAX here.
// gig-filters.js will:
//  - populate categories
//  - build query params (q, min/max price, category, postedWithin)
//  - fetch /gigs?...
//  - call renderGigs(data)
