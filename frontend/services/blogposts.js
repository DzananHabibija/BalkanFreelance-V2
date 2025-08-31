$(document).ready(function () {
  if (window.location.hash.includes("blogs")) {
    loadBlogPosts();
  }
});

function loadBlogPosts() {
  $.ajax({
    url: "http://localhost/BalkanFreelance/backend/blogs", // Adjust if needed
    method: "GET",
    dataType: "json",
    success: function (blogs) {
      const $list = $("#blog-list");
      $list.empty();

      if (blogs.length === 0) {
        $list.html('<div class="alert alert-info">No blog posts available.</div>');
        return;
      }

      blogs.forEach((blog) => {
        const imageUrl = getImageUrl(blog.image_url);
        const contentPreview = blog.content ? blog.content.slice(0, 120) + "..." : "";

       const card = `
        <div class="card mb-4 blog-card" style="cursor: pointer;" onclick="openSingleBlog(${JSON.stringify(blog.id)})">
          <div class="row g-0 h-100">
            <div class="col-md-4">
              <img src="${imageUrl}" class="img-fluid card-img-left" alt="${blog.title}">
            </div>
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title">${blog.title}</h5>
                <p class="card-text">${contentPreview}</p>
                <p class="card-text"><small class="text-muted">Published on ${blog.published_at}</small></p>
              </div>
            </div>
          </div>
        </div>
      `;


        $list.append(card);
      });
    },
    error: function () {
      $("#blog-list").html('<div class="alert alert-danger">Failed to load blogs. Please try again later.</div>');
    }
  });
}

function getImageUrl(url) {
  if (!url || url.trim() === "") {
    return "https://via.placeholder.com/400x250?text=No+Image";
  }

  // If starts with http or https – return as is
  if (url.startsWith("http://") || url.startsWith("https://")) {
    return url;
  }

  // Otherwise treat as relative and append the uploads folder path
  return "http://localhost/BalkanFreelance/uploads/" + url;
}

// put this near your other functions
function openSingleBlog(id) {
  try {
    sessionStorage.setItem('bf_current_blog_id', String(id));
  } catch (e) {}
  window.location.hash = '#single-blog';
}
