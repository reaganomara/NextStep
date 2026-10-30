<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Talk to NextStep</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f8f7;
            color: #243b38;
            margin: 0;
            padding: 30px 16px;
        }

        .container {
            max-width: 650px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 4px 20px #00000010;
        }

        h1 {
            color: #27685d;
        }

        textarea {
            width: 100%;
            min-height: 130px;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #b9cfca;
            border-radius: 10px;
            font: inherit;
        }

        button {
            background: #27685d;
            color: white;
            border: 0;
            border-radius: 10px;
            padding: 12px 20px;
            margin-top: 14px;
            font: inherit;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .notice {
            font-size: 14px;
            line-height: 1.5;
            color: #526560;
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>How are you feeling right now?</h1>

        <p>
            Tell NextStep what's going on.
            You can describe your feelings in your own words.
        </p>

        <label for="message">
            What are you experiencing?
        </label>

        <textarea
            id="message"
            placeholder="I've been feeling anxious about..."
        ></textarea>

        <button type="button" disabled>
            Continue (Coming Soon)
        </button>

        <p class="notice">
            This is a development preview.
            Messages are not submitted or saved.
            NextStep does not diagnose medical conditions.
            If you are experiencing an emergency, call 911.
            For a mental health crisis in the U.S.,
            call or text 988.
        </p>
    </main>
</body>
</html>