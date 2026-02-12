<?php
/**
 * Kubernetes Pod Metadata Diagnostic App
 * Displays Pod metadata using Kubernetes Downward API
 */

// Helper to get environment variables with a fallback
function getEnvVar($var, $default = "N/A") {
    return getenv($var) ?: $default;
}

// Function to read pod labels from a file
function getPodLabels($filePath) {
    if (!file_exists($filePath)) {
        return ["Error" => "Labels file not found at $filePath"];
    }
    
    $labels = [];
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            // Labels are usually quoted in the downward API file
            $labels[trim($key)] = trim($value, '"');
        }
    }
    return $labels;
}

// Metadata from Environment Variables
$metadata = [
    "Node Name" => getEnvVar("NODE_NAME"),
    "Pod Name" => getEnvVar("POD_NAME"),
    "Pod Namespace" => getEnvVar("POD_NAMESPACE"),
    "Pod IP" => getEnvVar("POD_IP"),
    "Service Account" => getEnvVar("POD_SERVICE_ACCOUNT")
];

// Labels from Volume Mount
$labelsPath = "/etc/podinfo/labels";
$labels = getPodLabels($labelsPath);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kubernetes Pod Diagnostic Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <div class="k8s-logo">☸️</div>
            <h1>Pod Introspection Dashboard</h1>
            <p class="subtitle">Live Kubernetes Metadata via Downward API</p>
        </header>

        <main>
            <section class="card">
                <h2><span class="icon">📍</span> Runtime Context</h2>
                <table class="metadata-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metadata as $key => $value): ?>
                        <tr>
                            <td class="key"><?php echo htmlspecialchars($key); ?></td>
                            <td class="value">
                                <span class="badge <?php echo $value === 'N/A' ? 'badge-warn' : 'badge-success'; ?>">
                                    <?php echo htmlspecialchars($value); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="card">
                <h2><span class="icon">🏷️</span> Pod Labels</h2>
                <?php if (isset($labels["Error"])): ?>
                    <div class="alert alert-error">
                        <strong>Missing Downward API Volume:</strong> <?php echo $labels["Error"]; ?>
                    </div>
                <?php else: ?>
                    <table class="metadata-table">
                        <thead>
                            <tr>
                                <th>Label Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labels as $key => $value): ?>
                            <tr>
                                <td class="key"><?php echo htmlspecialchars($key); ?></td>
                                <td class="value"><code><?php echo htmlspecialchars($value); ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>

        <footer>
            <p>Generated at: <?php echo date('Y-m-d H:i:s T'); ?> | Resource: <?php echo htmlspecialchars($metadata["Pod Name"]); ?></p>
        </footer>
    </div>
</body>
</html>
