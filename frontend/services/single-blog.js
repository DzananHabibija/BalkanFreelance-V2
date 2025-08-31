// --- CONFIG: tolerate /BalkanFreelance vs /balkanfreelance (and switch automatically) ---
(function () {
  const ORIGIN = window.location.origin;
  const lowerRoot = "/balkanfreelance";
  const upperRoot = "/BalkanFreelance";
  const pathname = window.location.pathname;

  const usesLower = pathname.toLowerCase().includes(lowerRoot + "/");
  const PRIMARY_ROOT = usesLower ? lowerRoot : upperRoot;
  const ALT_ROOT = usesLower ? upperRoot : lowerRoot;

  window.__BF_CFG__ = {
    // We'll try these in order until one works
    API_BASES: [ORIGIN + PRIMARY_ROOT + "/backend", ORIGIN + ALT_ROOT + "/backend"],
    UPLOAD_BASES: [ORIGIN + PRIMARY_ROOT + "/uploads", ORIGIN + ALT_ROOT + "/uploads"]
  };
})();

// --- Single Blog Module (Amiletti SPApp) ---
const SingleBlog = (function () {
  // We navigate to "#single-blog" and pass id via sessionStorage (avoids jQuery selector issues)
  function isSingleBlogRoute(hash) {
    return hash === "#single-blog" || /^#single-blog(\/|\?)/i.test(hash || "");
  }

  // Primary: sessionStorage (set by list click). Fallbacks support deep-links.
  function getCurrentBlogId() {
    try {
      const v = sessionStorage.getItem("bf_current_blog_id");
      if (v) return v;
    } catch (e) {}

    const hash = window.location.hash || "";
    // Deep-link support: "#single-blog/10"
    const m = hash.match(/^#single-blog\/([^\/\?\#]+)/i);
    if (m && m[1]) return decodeURIComponent(m[1]);

    // Legacy: "#single-blog?id=10"
    const qIndex = hash.indexOf("?");
    if (qIndex !== -1) {
      const params = new URLSearchParams(hash.substring(qIndex + 1));
      const legacyId = params.get("id");
      if (legacyId) return legacyId;
    }
    return null;
  }

  // Local SVG placeholder (no internet)
  function svgPlaceholder(text, w, h) {
    const safeText = (text || "").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    const svg =
      `<svg xmlns='http://www.w3.org/2000/svg' width='${w}' height='${h}'>` +
      `<rect width='100%' height='100%' fill='#e9ecef'/>` +
      `<text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' ` +
      `font-family='Arial,Helvetica,sans-serif' font-size='28' fill='#6c757d'>${safeText}</text>` +
      `</svg>`;
    return "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg);
  }

  function formatDate(input) {
    const d = new Date(input);
    if (isNaN(d.getTime())) return input || "";
    return d.toLocaleDateString("en-GB", { year: "numeric", month: "short", day: "2-digit" });
  }

  function getImageUrl(url) {
    if (!url || url.trim() === "") {
      return svgPlaceholder("No Image", 1200, 600);
    }
    if (url.startsWith("http://") || url.startsWith("https://")) {
      return url;
    }
    // Prefer the first uploads base; if it 404s, the <img> will still show, so we leave it.
    return window.__BF_CFG__.UPLOAD_BASES[0] + "/" + url;
  }

  // Plain-text to paragraphs
  function nl2p(text) {
    const esc = $("<div>").text(text || "").html(); // escape HTML
    return esc
      .split(/\n{2,}/)
      .map((p) => `<p>${p.replace(/\n/g, "<br>")}</p>`)
      .join("");
  }

  // Try both API bases and both endpoint styles: /blogs/{id} then /blogs?id={id}
  function fetchBlogById(id, onSuccess, onFail) {
    const bases = window.__BF_CFG__.API_BASES.slice(); // copy
    const pathsFor = (base) => [
      `${base}/blogs/${encodeURIComponent(id)}`,
      `${base}/blogs?id=${encodeURIComponent(id)}`
    ];

    function tryNextBase() {
      if (!bases.length) {
        onFail();
        return;
      }
      const base = bases.shift();
      const candidates = pathsFor(base);

      // Try /blogs/{id} first
      $.ajax({
        url: candidates[0],
        method: "GET",
        dataType: "json",
        success: function (blog) {
          onSuccess(blog);
        },
        error: function () {
          // Try /blogs?id={id}
          $.ajax({
            url: candidates[1],
            method: "GET",
            dataType: "json",
            success: function (blog) {
              onSuccess(blog);
            },
            error: function () {
              // Try next base (switch casing)
              tryNextBase();
            }
          });
        }
      });
    }

    tryNextBase();
  }

  function render() {
    const id = getCurrentBlogId();
    const $wrap = $("#single-blog-container");
    const $title = $("#blog-title");
    const $content = $("#blog-content");
    const $img = $("#blog-image");
    const $date = $("#blog-date");

    if (!id) {
      $wrap.html(`
        <div class="alert alert-warning">
          Missing blog ID.<br>
          <a class="btn btn-sm btn-outline-secondary mt-2" href="#blogs">&larr; Back to all blogs</a>
        </div>`);
      return;
    }

    // Loading placeholders
    $title.text("Loading...");
    $content.html(`<div class="text-muted">Please wait…</div>`);
    $img.attr("src", svgPlaceholder("Loading…", 1200, 600));
    $date.text("");

    fetchBlogById(
      id,
      function (blog) {
        if (!blog || !blog.id) {
          $wrap.html(`
            <div class="alert alert-info">
              Blog not found.<br>
              <a class="btn btn-sm btn-outline-secondary mt-2" href="#blogs">&larr; Back to all blogs</a>
            </div>`);
          return;
        }

        $title.text(blog.title || "Untitled");
        $img.attr("src", getImageUrl(blog.image_url)).attr("alt", blog.title || "Blog image");
        $date.text(formatDate(blog.published_at));

        if (blog.content && /<[^>]+>/.test(blog.content)) {
          $content.html(blog.content);
        } else {
          $content.html(nl2p(blog.content || ""));
        }
      },
      function () {
        $wrap.html(`
          <div class="alert alert-danger">
            Failed to load the blog post. Please try again later.<br>
            <a class="btn btn-sm btn-outline-secondary mt-2" href="#blogs">&larr; Back to all blogs</a>
          </div>`);
      }
    );
  }

  return {
    init: function () {
      if (isSingleBlogRoute(window.location.hash)) {
        render();
      }
      $(window).off(".singleBlog").on("hashchange.singleBlog", function () {
        if (isSingleBlogRoute(window.location.hash)) render();
      });
    },
    render
  };
})();

$(document).ready(function () {
  SingleBlog.init();
});
