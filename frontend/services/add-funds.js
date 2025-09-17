$(document).ready(function () {
  const user = JSON.parse(localStorage.getItem("user"));
  if (!user || !user.id) {
    toastr.error("You must be logged in to view this page.");
    return;
  }

  // 🔄 Fill expiry year dropdown (current year to +10)
  const currentYear = new Date().getFullYear();
  for (let i = 0; i < 10; i++) {
    $('#expiryYear').append(`<option value="${currentYear + i}">${currentYear + i}</option>`);
  }

  // 🔁 Load current balance
  loadWalletBalance(user.id);

  // 💳 Handle top-up form submit
  $('#addFundsForm').on('submit', function (e) {
    e.preventDefault();

    const cardNumber = $('#cardNumber').val().trim();
    const cvc = $('#cvc').val().trim();
    const expiryMonth = $('#expiryMonth').val();
    const expiryYear = $('#expiryYear').val();
    const amount = parseFloat($('#amount').val());

    // 🛑 Validate card number
    if (!/^\d{4} ?\d{4} ?\d{4} ?\d{4}$/.test(cardNumber)) {
      toastr.warning("Enter a valid 16-digit credit card number.");
      return;
    }

    // 🛑 Validate CVC
    if (!/^\d{3}$/.test(cvc)) {
      toastr.warning("Enter a valid 3-digit CVC.");
      return;
    }

    // 🛑 Validate expiry
    if (!expiryMonth || !expiryYear) {
      toastr.warning("Select card expiry month and year.");
      return;
    }

    // 🛑 Validate amount
    if (isNaN(amount) || amount <= 0) {
      toastr.warning("Enter a valid amount.");
      return;
    }

    // ✅ Simulate top-up request
    $.ajax({
      url: `${API_BASE}/top-up`,
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ amount: amount }),
      success: function (res) {
        toastr.success("Funds added successfully!");
        $('#addFundsModal').modal('hide');
        $('#addFundsForm')[0].reset(); // Clear form
        loadWalletBalance(user.id);
      },
      error: function () {
        toastr.error("Failed to add funds. Please try again.");
      }
    });
  });
});

// 🔄 Load balance into Wallet view
function loadWalletBalance(userId) {
  $.ajax({
    url: `${API_BASE}/user/${userId}/balance`,
    method: 'GET',
    success: function (res) {
      $('#walletBalance').text(parseFloat(res.balance).toFixed(2));
    },
    error: function () {
      $('#walletBalance').text("0.00");
    }
  });
}
