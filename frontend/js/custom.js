$(document).ready(function () {
  // Set dynamic section height if needed (optional, remove if unnecessary)
  $("main#spapp > section").height($(document).height() - 60);

  // Initialize SPA
  var app = $.spapp({
    defaultView: "#home",
    templateDir: "pages/", // directory where your views are stored
    pageNotFound: "404.html"
  });

  app.route({
    view: 'home',
    load: 'home.html'
  });

  app.route({
    view: 'profile',
    load: 'profile.html'
  });

  app.route({
    view: 'blogs',
    load: 'blogposts.html'
  });

  app.route({
    view: 'create-gig',
    load: 'create-gig.html'
  });

  app.route({
    view: 'register',
    load: 'register.html'
  });

  app.route({
    view: 'login',
    load: '../login/index.html'
  });

  app.route({
    view: 'gig-details',
    load: 'gig-details.html'
  });

  app.route({
    view: 'single-gig',
    load: 'single-gig.html'
  });

  app.route({
    view: 'user-profile',
    load: 'user-profile.html' 
  });

  app.route({
  view: 'admin',
  load: 'admin-dashboard.html' 
  });

  app.route({
  view: 'add-funds',
  load: 'add-funds.html' 
  });

  app.route({
  view: 'fake-coinbase',
  load: 'fake-coinbase.html' 
  });

  app.route({
  view: 'single-blog',
  load: 'single-blog.html',
  onEnter: function () {
    // This runs when #single-blog is navigated to,
    // so we can read ?id=... and load the post.
      if (window.SingleBlog && typeof SingleBlog.render === 'function') {
      SingleBlog.render();
      }
    }
  });


  //  onEnter: function() {
  //   $.getScript('js/admin-dashboard.js');
  //   }

  // Run the app
  app.run();
});
