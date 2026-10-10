<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white text-center rounded-top-4">
          <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Secure Payment</h4>
        </div>
        <div class="card-body p-4">
          <form>
            <!-- Cardholder Name -->
            <div class="mb-3">
              <label class="form-label">Cardholder Name</label>
              <input type="text" class="form-control" placeholder="Enter full name" required>
            </div>

            <!-- Card Number -->
            <div class="mb-3">
              <label class="form-label">Card Number</label>
              <div class="input-group">
                <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" required>
                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
              </div>
            </div>

            <!-- Expiry & CVV -->
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Expiry Date</label>
                <input type="text" class="form-control" placeholder="MM/YY" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">CVV</label>
                <input type="password" class="form-control" placeholder="***" maxlength="3" required>
              </div>
            </div>

            <!-- Amount -->
            <div class="mb-3">
              <label class="form-label">Amount</label>
              <input type="text" class="form-control" value="Rs. 1,000" readonly>
            </div>

            <!-- Pay Button -->
            <div class="d-grid">
              <button type="submit" class="btn btn-success btn-lg rounded-pill">
                <i class="fas fa-lock me-2"></i> Pay Securely
              </button>
            </div>
          </form>
        </div>
        <div class="card-footer text-center small text-muted">
          <i class="fas fa-shield-alt me-1"></i> Your payment is protected & encrypted
        </div>
      </div>
    </div>
  </div>
</div>

<!-- FontAwesome Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>
</html>