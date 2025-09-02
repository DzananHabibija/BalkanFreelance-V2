(function(){
  // --- Helpers --------------------------------------------------------------

  function getLoggedInUserId() {
    try {
      const user = JSON.parse(localStorage.getItem("user"));
      return user?.id || null;
    } catch(_) { return null; }
  }

  // Build query params from filters + search
  function buildQueryParams() {
    const params = new URLSearchParams();

    // exclude the logged-in user's gigs (keep your behavior)
    const excludeUserId = getLoggedInUserId();
    if (excludeUserId) params.set("excludeUser", excludeUserId);

    const q = $("#gigSearchInput").val().trim();
    if (q) params.set("q", q);

    const minPrice = $("#minPrice").val().trim();
    const maxPrice = $("#maxPrice").val().trim();
    const categoryId = $("#categoryId").val();
    const postedWithin = $("#postedWithin").val();

    if (minPrice !== "") params.set("min_price", minPrice);
    if (maxPrice !== "") params.set("max_price", maxPrice);
    if (categoryId) params.set("category_id", categoryId);
    if (postedWithin) params.set("posted_within", postedWithin);

    return params.toString();
  }

  // Fetch gigs with current filters/search
  function loadGigs() {
    const qs = buildQueryParams();
    $.ajax({
      url: `http://localhost/BalkanFreelance/backend/gigs${qs ? ("?" + qs) : ""}`,
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

  // Populate categories select
  function loadCategories() {
    // Adjust if your route differs
    return $.ajax({
      url: "http://localhost/BalkanFreelance/backend/categories",
      type: "GET",
      dataType: "json"
    }).then(function(categories){
      const $sel = $("#categoryId");
      $sel.find("option:not([value=''])").remove();
      categories.forEach(function(cat){
        $sel.append(`<option value="${cat.id}">${cat.name}</option>`);
      });
    }).catch(function(err){
      console.error("Failed to load categories", err);
    });
  }

  // Simple debounce for typing in search
  function debounce(fn, ms) {
    let t;
    return function(){
      clearTimeout(t);
      const args = arguments, ctx = this;
      t = setTimeout(function(){ fn.apply(ctx, args); }, ms);
    };
  }

  // --- Init bindings --------------------------------------------------------
  $(document).ready(function(){
    // Populate categories and then load gigs once
    loadCategories().always(loadGigs);

    // Apply filters
    $("#gigFilters").on("submit", function(e){
      e.preventDefault();
      loadGigs();
    });

    // Reset filters
    $("#resetFilters").on("click", function(){
      $("#gigFilters")[0].reset();
      $("#categoryId").val("");
      $("#postedWithin").val("");
      loadGigs();
    });

    // Search typing -> debounce
    $("#gigSearchInput").on("input", debounce(loadGigs, 300));
  });

  // Expose loadGigs if you ever need it elsewhere
  window.loadFilteredGigs = loadGigs;
})();
