<!DOCTYPE html>
<html lang="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Перенаправление</title>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.location.href = '{{ $link }}'
            }, 500);
        })
    </script>
</head>
<body>
Перенаправление...
</body>
</html>
