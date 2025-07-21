$(document).ready(function () {
  // Set dynamic section height if needed (optional, remove if unnecessary)
  $("main#spapp > section").height($(document).height() - 60);

  // Initialize SPA
  var app = $.spapp({
    defaultView: "#home",
    templateDir: "pages/", // directory where your views are stored
    pageNotFound: "404.html"
  });

  // ✅ Define real routes
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
  view: 'admin',
  load: 'admin-dashboard.html',
  onViewLoad: function () {
    console.log("✅ Admin view loaded");

    // Delay to make sure DOM is ready after SPApp injects the view
    setTimeout(function () {
      if ($.fn.DataTable.isDataTable('#usersTable')) {
        $('#usersTable').DataTable().destroy();
        $('#usersTable').empty();
      }

      $('#usersTable').DataTable({
        ajax: {
          url: 'http://localhost/BalkanFreelance/backend/users', // ✅ YOUR real endpoint
          dataSrc: function (json) {
            console.log("✅ /users API response:", json);
            return json;
          }
        },
       columns: [
  { data: 'id' },
  {
    data: null,
    render: function (data, type, row) {
      return (row.first_name || '') + ' ' + (row.last_name || '');
    }
  },
  { data: 'email', render: d => d || '—' },
  { data: 'country_id', render: d => d || '—' },
  { data: 'bio', render: d => d || '—' },
  { data: 'created_at', render: d => d || '—' },
  { data: 'balance', render: d => d || '0.00' },
  {
    data: 'isAdmin',
    render: d => d == 1 ? 'Admin' : 'User'
  },
  {
    data: null,
    render: function () {
      return '<button class="btn btn-sm btn-primary">Edit</button>';
    }
  }
]


      });
    }, 200); // Slight delay to wait for DOM injection (SPApp quirk)
  }
});


  // Run the app
  app.run();
});
