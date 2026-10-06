<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Get help and support for Feetrack. We are here to assist our partners and customers with any questions or issues.">
    <meta name="keywords" content="Feetrack Support, Help Center, Customer Service, Contact Feetrack">
    <meta property="og:title" content="Feetrack Support">
    <meta property="og:description" content="Get help and support for Feetrack. We are here to assist our partners and customers with any questions or issues.">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#2563eb">
    <title>Feetrack - Support</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <style>
        .support-header {
            background: var(--bg-sidebar);
            color: var(--text-primary);
            padding: 80px 0 100px;
            text-align: center;
        }
        .support-header h1 {
            color: #fff;
            letter-spacing: -0.5px;
        }
        .support-header p {
            color: var(--sidebar-text);
        }
        .support-card {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 40px;
            margin-top: -60px;
            background: var(--bg-card);
        }
        .icon-box {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .accordion-button:not(.collapsed) {
            background-color: transparent;
            color: var(--primary);
            box-shadow: none;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        .accordion-button {
            padding-left: 0;
            padding-right: 0;
            color: var(--text-primary);
        }
        .accordion-body {
            padding-left: 0;
            padding-right: 0;
        }
    </style>
</head>
<body>

    <div class="support-header">
        <div class="container">
            <div class="mb-4 text-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-height: 55px; width: auto; object-fit: contain; filter: brightness(0) invert(1);">
            </div>
            <h1 class="fw-800">Feetrack Support</h1>
            <p class="lead mb-0">We are here to help you</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="support-card">
                    <h4 class="mb-4 fw-bold">Contact Us</h4>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="icon-box"><i class="bi bi-envelope"></i></div>
                            <h6 class="fw-bold mb-1">Email Support</h6>
                            <p class="text-muted small mb-2">Send us an email anytime and our team will get back to you within 24 hours.</p>
                            <a href="mailto:info@feetrack.in" class="fw-semibold text-decoration-none" style="color: var(--primary);">info@feetrack.in</a>
                        </div>
                        <div class="col-md-6">
                            <div class="icon-box"><i class="bi bi-telephone"></i></div>
                            <h6 class="fw-bold mb-1">Phone Support</h6>
                            <p class="text-muted small mb-2">Available Monday to Friday, 9:00 AM to 6:00 PM (IST).</p>
                            <a href="tel:+917619439990" class="fw-semibold text-decoration-none" style="color: var(--primary);">+91 76194 39990</a>
                        </div>
                    </div>

                    <hr class="my-5" style="border-color: var(--border-color);">

                    <h4 class="mb-4 fw-bold">Frequently Asked Questions</h4>
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 border-bottom rounded-0" style="border-color: var(--border-color) !important;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-600" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I delete my account?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small">
                                    To delete your account, open the Feetrack mobile app, go to Settings > Privacy > Delete Account. Alternatively, you can email our support team requesting account deletion.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 border-bottom rounded-0" style="border-color: var(--border-color) !important;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-600" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    I forgot my password, what should I do?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small">
                                    On the login screen of the app or website, click on "Forgot Password" and follow the instructions sent to your registered mobile number or email address.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 rounded-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-600" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How do I request a refund for a subscription?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small">
                                    Refund requests must be submitted within 7 days of purchase. Please contact our support team via email with your Invoice ID.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="text-muted small">&copy; {{ date('Y') }} Feetrack. All rights reserved.</p>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
