<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SSE Idle Timeout Test</title>
</head>
<body>
<h1>SSE Idle Timeout Test</h1>
<pre id="log"></pre>

<script>
let startTime = null;

function log(msg) {
    const logElem = document.getElementById("log");
    logElem.textContent += msg + "\n";
    logElem.scrollTop = logElem.scrollHeight;
}

function connect() {
    startTime = Date.now();
    log("Connecting...");

    const source = new EventSource("sse-idle-demo"); // Ganti sesuai endpoint SSE di server kamu

    source.addEventListener("message", (e) => {
        log("Message: " + e.data);
    });

    source.addEventListener("open", () => {
        log("Connected at " + new Date().toLocaleTimeString());
    });

    source.addEventListener("error", () => {
        const duration = ((Date.now() - startTime) / 1000).toFixed(1);
        log(`Disconnected after ${duration} seconds`);
        source.close();

        // Auto reconnect setelah 2 detik
        setTimeout(connect, 2000);
    });
}

connect();
</script>
</body>
</html>
