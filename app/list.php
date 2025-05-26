<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Πληροφορίες YouTube Βίντεο</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
      background: #f4f4f4;
    }
    form {
      background: white;
      padding: 20px;
      border-radius: 10px;
      max-width: 500px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    input[type="text"] {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      margin-top: 10px;
    }
    button {
      padding: 10px 20px;
      font-size: 16px;
      margin-top: 15px;
      cursor: pointer;
    }
    .result {
      margin-top: 30px;
      background: white;
      padding: 20px;
      border-radius: 10px;
      max-width: 500px;
    }
  </style>
</head>
<body>

<h2>Ανάλυση YouTube Βίντεο</h2>

<form action="get_video_info.php" method="post">
  <label for="video_url">Επικόλλησε το URL του YouTube βίντεο:</label>
  <input type="text" name="video_url" id="video_url" required placeholder="https://www.youtube.com/watch?v=...">
  <button type="submit">Ανάλυση</button>
</form>

</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_url'])) {
    $videoUrl = $_POST['video_url'];
    $command = escapeshellcmd("yt-dlp -j " . escapeshellarg($videoUrl));
    $output = shell_exec($command);

    if (!$output) {
        echo "⚠️ Το yt-dlp δεν επέστρεψε αποτελέσματα. Ενδέχεται να μην είναι εγκατεστημένο ή να υπάρχει πρόβλημα στην εκτέλεση.";
        exit;
    }

    $data = json_decode($output, true);
    if (!$data) {
        echo "⚠️ Σφάλμα κατά την αποκωδικοποίηση των δεδομένων JSON.";
        echo "<pre>Raw output:\n$output</pre>";
        exit;
    }

    echo "<h3>Αποτελέσματα:</h3>";
    echo "<p><strong>Τίτλος:</strong> " . htmlspecialchars($data['title']) . "</p>";
    echo "<p><strong>Video ID:</strong> " . htmlspecialchars($data['id']) . "</p>";
    echo "<p><strong>Ημερομηνία Ανάρτησης:</strong> " . date('Y-m-d', strtotime($data['upload_date'])) . "</p>";
    echo "<p><strong>Κανάλι:</strong> " . htmlspecialchars($data['uploader']) . "</p>";
}
?>
