<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Processing Payment...</title>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: transparent;
        }
        .loader {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            color: #333;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .error {
            color: #e74c3c;
            display: none;
            margin-top: 15px;
        }
        .btn-retry {
            display: none;
            margin-top: 15px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-retry:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p class="message">Redirecting to payment page...</p>
        <p class="error" id="errorMsg"></p>
    </div>

    <script>
        // Wait for SDK to load completely
        document.addEventListener('DOMContentLoaded', function() {
            initializePayment();
        });

        function initializePayment() {
            try {
                // Initialize Cashfree SDK
                const cashfree = Cashfree({
                    mode: "{{ $mode }}" // "sandbox" or "production"
                });

                // Checkout options
                let checkoutOptions = {
                    paymentSessionId: "{{ $paymentSessionId }}",
                    redirectTarget: "_self" // Opens in same window
                };

                console.log('Initializing Cashfree checkout...', checkoutOptions);

                // Open checkout page
                cashfree.checkout(checkoutOptions).then(function(result) {
                    console.log("Payment initiated successfully", result);
                    // Cashfree will automatically redirect to their checkout page
                }).catch(function(error) {
                    console.error("Checkout error:", error);
                    handleError("Payment initialization failed. Please try again.");
                });

            } catch (error) {
                console.error("Script error:", error);
                handleError("An error occurred while loading the payment page.");
            }
        }

        function handleError(message) {
            document.querySelector('.spinner').style.display = 'none';
            document.querySelector('.message').style.display = 'none';
            document.getElementById('errorMsg').textContent = message;
            document.getElementById('errorMsg').style.display = 'block';
            document.getElementById('retryBtn').style.display = 'inline-block';
        }

        // Fallback timeout
        setTimeout(function() {
            if (window.location.href === "{{ url()->current() }}") {
                console.warn("Checkout did not redirect in time");
            }
        }, 10000);
    </script>
</body>
</html>
