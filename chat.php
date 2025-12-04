<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Groq Chat</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0f0f1a;
      color: #e0e0e0;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header {
      background: #1a1a2e;
      padding: 1rem;
      text-align: center;
      font-size: 1.5rem;
      font-weight: bold;
      box-shadow: 0 2px 10px rgba(0, 255, 136, 0.2);
    }

    #chat-container {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .message {
      max-width: 80%;
      padding: 12px 16px;
      border-radius: 18px;
      line-height: 1.5;
      word-wrap: break-word;
    }

    .user {
      align-self: flex-end;
      background: #00ff88;
      color: #000;
      border-bottom-right-radius: 4px;
    }

    .bot {
      align-self: flex-start;
      background: #16213e;
      border: 1px solid #334466;
      border-bottom-left-radius: 4px;
    }

    .typing {
      font-style: italic;
      color: #00ff88;
    }

    #input-area {
      display: flex;
      padding: 1rem;
      background: #1a1a2e;
      gap: 10px;
      border-top: 1px solid #334466;
    }

    #prompt {
      flex: 1;
      padding: 14px 16px;
      border: none;
      border-radius: 30px;
      background: #16213e;
      color: #fff;
      font-size: 1rem;
      outline: none;
    }

    #prompt:focus {
      background: #1e2a44;
    }

    button {
      padding: 0 20px;
      background: #00ff88;
      color: #000;
      border: none;
      border-radius: 30px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background: #00cc70;
    }

    footer {
      text-align: center;
      padding: 0.5rem;
      font-size: 0.8rem;
      color: #666;
    }
  </style>
</head>

<body>

  <header>GROQ CHAT</header>

  <div id="chat-container">
    <div class="bot message">Halo! Groq siap menjawab pertanyaanmu.</div>

    <?php
    session_start();
    if (!isset($_SESSION['chat'])) $_SESSION['chat'] = [];

    if ($_POST['prompt'] ?? '' !== '') {
      $userMessage = trim($_POST['prompt']);
      if ($userMessage === '') exit;

      echo '<div class="user message">' . htmlspecialchars($userMessage) . '</div>';
      $_SESSION['chat'][] = ['sender' => 'user', 'text' => htmlspecialchars($userMessage)];

      echo '<div class="bot message typing">Groq sedang mengetik...</div>';
      flush();

      $response = @file_get_contents('http://localhost:3000/generate-text', false, stream_context_create([
        'http' => [
          'method' => 'POST',
          'header' => 'Content-Type: application/json',
          'content' => json_encode(['prompt' => $userMessage])
        ]
      ]));

      echo '<script>document.querySelector(".typing")?.remove();</script>';

      if ($response === false) {
        $botReply = 'Error: Backend tidak nyala atau tidak bisa diakses (pastikan node server jalan di port 3000)';
      } else {
        $data = json_decode($response, true);
        $botReply = $data['result'] ?? 'Error: Tidak ada jawaban dari server.';
      }

      $botReply = nl2br(htmlspecialchars($botReply));
      echo '<div class="bot message">' . $botReply . '</div>';
      $_SESSION['chat'][] = ['sender' => 'bot', 'text' => $botReply];
      flush();
    }

    foreach ($_SESSION['chat'] as $msg) {
      $class = $msg['sender'] === 'user' ? 'user' : 'bot';
      echo '<div class="' . $class . ' message">' . $msg['text'] . '</div>';
    }
    ?>
  </div>

  <form method="POST" id="input-area">
    <input type="text" name="prompt" id="prompt" placeholder="Ketik pesan di sini..." autocomplete="off" autofocus required />
    <button type="submit">Kirim</button>
  </form>

  <footer>Chat Groq via REST API lokal • by kamu</footer>

  <script>
    document.getElementById('chat-container').scrollTop = document.getElementById('chat-container').scrollHeight;
  </script>
</body>

</html>