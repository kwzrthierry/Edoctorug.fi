<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Bill</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        .logo {
            margin-bottom: 20px;
            height: 60px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .back-button {
            position: absolute; /* Position it at the top left */
            top: 20px; /* Adjust as needed */
            left: 20px; /* Adjust as needed */
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 50%; /* Circular button */
            width: 40px; /* Button width */
            height: 40px; /* Button height */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .back-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .pay-bill-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.5s ease-in-out;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .animated {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }

        .pulse {
            animation-name: pulse;
            animation-duration: 1s;
            animation-iteration-count: infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale3d(1, 1, 1);
            }
            50% {
                transform: scale3d(1.05, 1.05, 1.05);
            }
        }

        .btn-primary {
            border-radius: 30px;
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #0056b3, #007bff);
        }

        /* Loading overlay styles */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: .4em;
        }

        .loading-text {
            margin-left: 10px;
            font-size: 1.5rem;
            font-weight: 500;
        }

    </style>
</head>
<body>
    <!-- Back to Home link -->
    <button onclick="goBack()" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </button>
    <!-- Pay bill form container -->
    <div class="pay-bill-container">
        <img src="../assets/images/client-01.png" alt="Logo" class="logo">
        <h3>Pay Bill</h3>
        <!-- Pay bill form -->
        <form id="payBillForm">
            <!-- Form group for biller -->
            <div class="form-group">
                <label for="biller">Biller</label>
                <select class="form-control" id="biller" name="biller" required></select>
            </div>
            <!-- Form group for payment item -->
            <div class="form-group">
                <label for="paymentItem">Payment Item</label>
                <select class="form-control" id="paymentItem" name="paymentItem" required></select>
            </div>
            <!-- Form group for customer ID -->
            <div class="form-group">
                <label for="customerId">Customer ID</label>
                <input type="text" class="form-control" id="customerId" name="customerId" required>
            </div>
            <!-- Form group for amount -->
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
            <!-- Button to submit the pay bill form -->
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="loading-text">Loading...</div>
    </div>


    <!-- Full jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS and FontAwesome JS -->
    <script>
        function goBack() {
            window.history.back();
        }

        function showLoading() {
            $('#loadingOverlay').show();
        }

        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        $(document).ready(function() {
            // Show loading overlay when the page loads
            showLoading();

            // Call fetchBillers function
            fetchBillers();

            // Handle biller selection change
            $('#biller').on('change', function() {
                var billerId = $(this).val();
                fetchPaymentItems(billerId);
            });

            // Handle bill payment form submission
            $('#payBillForm').on('submit', function(event) {
                event.preventDefault();
                validateCustomer();
            });

            function fetchBillers() {
                $.ajax({
                    url: 'get_billers.php',
                    method: 'GET',
                    dataType: 'json', // Expect JSON response
                    success: function(response) {
                        hideLoading(); // Hide loading overlay after fetching billers
                        if (response.error) {
                            alert(response.error);
                        } else {
                            var billers = response.BillerList.Category.flatMap(category => category.Billers);
                            populateDropdown('#biller', billers, 'Name', 'Id');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading(); // Hide loading overlay on error
                        console.error('Failed to fetch billers:', error);
                        alert('Failed to fetch billers. Please try again later.');
                    }
                });
            }

            function populateDropdown(selector, items, textKey, valueKey) {
                var dropdown = $(selector);
                dropdown.empty();
                dropdown.append('<option selected="true" disabled>Choose...</option>');
                dropdown.prop('selectedIndex', 0);

                items.forEach(function(item) {
                    dropdown.append($('<option></option>').attr('value', item[valueKey]).text(item[textKey]));
                });
            }

            function fetchPaymentItems(billerId) {
                showLoading(); // Show loading overlay while fetching payment items
                $.ajax({
                    url: 'get_payment_items.php',
                    method: 'POST',
                    dataType: 'json', // Expect JSON response
                    data: { billerId: billerId },
                    success: function(response) {
                        hideLoading(); // Hide loading overlay after fetching payment items
                        if (response.error) {
                            alert(response.error);
                        } else {
                            populateDropdown('#paymentItem', response.PaymentItems, 'Name', 'Id');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading(); // Hide loading overlay on error
                        console.error('Failed to fetch payment items:', error);
                        alert('Failed to fetch payment items. Please try again later.');
                    }
                });
            }

            function validateCustomer() {
                showLoading(); // Show loading overlay while validating customer
                const customerId = $('#customerId').val();
                const paymentCode = $('#paymentItem').val();
                
                $.ajax({
                    url: 'validate_customer.php',
                    method: 'POST',
                    data: { customerId: customerId, paymentCode: paymentCode },
                    success: function(response) {
                        const validation = JSON.parse(response);
                        hideLoading(); // Hide loading overlay after validation
                        if (validation.status === 'success') {
                            makePayment();
                        } else {
                            alert('Customer validation failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading(); // Hide loading overlay on error
                        console.error('Error validating customer:', error);
                    }
                });
            }

            function makePayment() {
                showLoading(); // Show loading overlay while making payment
                const paymentData = {
                    biller: $('#biller').val(),
                    amount: $('#amount').val(),
                    customerId: $('#customerId').val(),
                    paymentCode: $('#paymentItem').val()
                };

                $.ajax({
                    url: 'pay_bill.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(paymentData),
                    success: function(response) {
                        hideLoading(); // Hide loading overlay after payment
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Payment successful');
                        } else {
                            alert('Payment failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading(); // Hide loading overlay on error
                        console.error('Error making payment:', error);
                    }
                });
            }
        });
    </script>
</body>
</html>
