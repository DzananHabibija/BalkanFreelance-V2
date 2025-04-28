$(document).ready(function() {
    $.ajax({
        url: "http://localhost/BalkanFreelance/backend/gigs/",
        type: "GET",
        dataType: "json",
        success: function(data) {
            $gigsContainer = $(".gigsContainer");
            $gigsContainer.empty(); 

            $.each(data, function(index, gig) {

                var $col = $("<div class='col-md-3 mb-4 d-flex justify-content-center'></div>");


                var $gigCard = $("<div class='card' style='width: 18rem;'></div>");
                //var $gigImage = $("<img class='card-img-top' alt='Card image cap'>").attr("src", gig.image_url);
                var $gigImage = $("<img class='card-img-top' alt='Card image cap' src='../assets/freelance.jpg'>");
                var $cardBody = $("<div class='card-body'></div>");
                var $gigTitle = $("<h5 class='card-title'></h5>").text(gig.title);
                var $gigDescription = $("<p class='card-text'></p>").text(gig.description);
                var $gigPrice = $("<p class='card-text fw-bold'></p>").text("Price: " + gig.price + " $");
                var $gigButton = $("<a href='#' class='btn btn-success'>View Details</a>").attr("data-id", gig.id);

                $cardBody.append($gigTitle, $gigDescription, $gigPrice, $gigButton);
                $gigCard.append($gigImage, $cardBody);
                $col.append($gigCard);
                $gigsContainer.append($col);

                            
            });

        },
        error : function(xhr, status, error) {
            console.error("Error fetching gigs:", error);
            alert("Failed to load gigs. Please try again later.");
        }
    })



})