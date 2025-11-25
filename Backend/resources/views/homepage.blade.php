<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .custom-message {
            color: #155E95;
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
            color: #155E95;
            font-weight: bold;
        }

        .custom-container {
            margin-left: 5%;
        }
    </style>
</head>

<body>

    <div class="custom-flex-container">
        <div class="custom-container">
            <p class="custom-p"> D.I.E.T.S.</p>
            <p class="custom-message"> Server is Running...</p>
        </div>
        <img src="{{ asset('/svg/illustrations/server_cluster.svg') }}" alt="Server Cluster" class="custom-image">
    </div>

</body>

</html>
