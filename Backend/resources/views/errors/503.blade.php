<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .custom-message {
            color: #464847;
            font-size: 4rem;
            font-weight: bold;
            font-family: Tahoma, sans-serif;
        }

        .custom-image {
            float: right;
            max-width: 50%;
        }

        .custom-flex-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 95vh;
        }

        .custom-p {
            font-size: 2rem;
            font-family: Tahoma, sans-serif;
            color: #6c63ff;
            font-weight: bold;
        }

        .custom-container {
            margin-left: 5%;
        }

        .custom-p2 {
            font-size: 1rem;
            color: #464847;
        }
    </style>
</head>

<body>

    <div class="custom-flex-container">
        <div class="custom-container">
            <p class="custom-p">Dietary Information and Engagement Technology System (DIETS)</p>
            <!-- <p class="custom-message"> Currently Under Construction...</p> -->
             <p class="custom-message">System Maintenance</p>
            <p class="custom-p2">We apologize for the inconvenience. Our team is performing system maintenance to enhance performance and reliability.</p>
            <p class="custom-p2">For other inquiries or suggestions you may call MIS at 202.</p> 
        </div>
        <img src="{{ asset('/svg/illustrations/bug_fixing.svg') }}" alt="Bug Fixing" class="custom-image">
    </div>

</body>

</html>
