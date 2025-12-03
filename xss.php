<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XSS Testing Sandbox</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 40px;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 500px;
            margin: auto;
            box-shadow: 0px 4px 16px rgba(0,0,0,0.15);
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            margin-top: 15px;
            padding: 12px 20px;
            border: none;
            background: #007bff;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .output {
            margin-top: 25px;
            padding: 15px;
            border-radius: 8px;
            background: #e9ecef;
            min-height: 40px;
            font-size: 18px;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>XSS Testing Playground</h2>
    <p>Enter any payload. The output below is intentionally unsanitized for testing reflected XSS.</p>

    <form method="GET">
        <input type="text" name="xss" placeholder="Try: <script>alert('XSS')</script>">
        <button type="submit">Run</button>
    </form>

    <div class="output">
        <?php
            if (!empty($_GET['xss'])) {
                // Vulnerable output (intentional)
                echo $_GET['xss'];
            }
        ?>
    </div>
</div>

</body>
</html>
