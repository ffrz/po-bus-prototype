<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SSE Test</title>
</head>
<body>
    <h1>Server Time Updates</h1>
    <div id="output"></div>

    <script>
        const output = document.getElementById('output');
        const source = new EventSource('/sse-demo');

        source.onmessage = function (event) {
            const data = JSON.parse(event.data);
            output.innerHTML += `<p>${data.time}</p>`;
        };

        source.onerror = function (err) {
            console.error("SSE Error:", err);
        };
    </script>
</body>
</html>
