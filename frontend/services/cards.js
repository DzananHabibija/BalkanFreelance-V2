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

function loadAllGigs() {
    const user = JSON.parse(localStorage.getItem("user"));
    const excludeUserId = user?.id;

    $.ajax({
        url: excludeUserId
            ? `http://localhost/BalkanFreelance/backend/gigs?excludeUser=${excludeUserId}`
            : `http://localhost/BalkanFreelance/backend/gigs`,
        type: "GET",
        dataType: "json",
        success: function(data) {
            renderGigs(data);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching gigs:", error);
            alert("Failed to load gigs. Please try again later.");
        }
    });
}


$(document).ready(function() {
    loadAllGigs();
});

$(".search-input").on("input", function() {
    const searchText = $(this).val().toLowerCase().trim();

    if (searchText === "") {
        loadAllGigs();
    } else {
        $.ajax({
            url: "http://localhost/BalkanFreelance/backend/gigs/search/" + searchText,
            type: "GET",
            dataType: "json",
            success: function(data) {
                renderGigs(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching gigs:", error);
                alert("Failed to search gigs. Please try again later.");
            }
        });
    }
});
