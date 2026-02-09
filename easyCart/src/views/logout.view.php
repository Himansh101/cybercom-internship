<!DOCTYPE html>
<html>

<head>
    <title>Logging out...</title>
    <script>
        localStorage.clear();
        window.location.href = "index";
    </script>
    <meta http-equiv="refresh" content="2;url=index">
</head>

<body>
    <div
        style="font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background: #f8fafc; color: #1e293b;">
        <div
            style="width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top: 4px solid #2563eb; border-radius: 50%; animation: spin 1s linear infinite;">
        </div>
        <p style="margin-top: 20px; font-weight: 500;">Clearing session data...</p>
        <p style="font-size: 0.9rem; color: #64748b;">If you are not redirected, <a href="index"
                style="color: #2563eb; text-decoration: none;">click here</a>.</p>
    </div>
    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>