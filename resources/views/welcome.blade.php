<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
    <title>Redirecting...</title>
    </head>
<body>
    <p>Redirecting to login page...</p>
    <script>
        window.location.href = "{{ route('login') }}";
    </script>
    </body>
</html>
