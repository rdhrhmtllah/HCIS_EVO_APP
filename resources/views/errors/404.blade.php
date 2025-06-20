<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
            font-family: 'Arial', sans-serif;
            color: #333;
            text-align: center;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            font-size: 6rem;
            margin: 0;
            color: #555;
            animation: fadeIn 1s ease-out;
        }

        p {
            font-size: 1.5rem;
            margin-top: 10px;
            color: #777;
            animation: fadeIn 2s ease-out;
        }

        a {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #fff;
            color: #555;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            border: 1px solid #ccc;
            transition: all 0.3s;
        }

        a:hover {
            background-color: #f0f0f0;
            color: #333;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>404</h1>
        <p>Oops! Page Not Found.</p>
        {{-- <a href="/">Go Back to Home</a> --}}
    </div>
</body>

</html>