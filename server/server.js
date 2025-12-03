import "dotenv/config";
import express from "express";
import cors from "cors";
import { Groq } from "groq-sdk";
import { execSync } from "child_process"; // ini yang benar untuk ESM

function getIpv4FromIpconfig() {
  try {
    const output = execSync("ipconfig", { encoding: "utf8" });

    const regex =
      /(?:IPv4 Address|Alamat IPv4)[\. ]*:\s*([0-9]{1,3}(\.[0-9]{1,3}){3})/i;
    const match = output.match(regex);

    return match ? match[1].trim() : null;
  } catch (error) {
    console.error("Gagal ambil IP dari ipconfig:", error.message);
    return null;
  }
}

const ipv4 = getIpv4FromIpconfig();
console.log(
  "IPv4 kamu:",
  ipv4 || "Tidak ditemukan (mungkin bukan Windows atau ipconfig gagal)"
);

const app = express();

app.use(
  cors({
    origin: [
    'http://localhost','http://127.0.0.1',
    'http://localhost:5500','http://127.0.0.1:5500',
    'http://localhost:80','http://127.0.0.1:80',
    'http://localhost:8080','http://127.0.0.1:8080',

    `http://${ipv4}`,`http://${ipv4}:80`,`http://${ipv4}:8080`,

    'http://pos_tokobuku.test','https://pos_tokobuku.test',
    ].filter(Boolean), 
  })
);

app.use(express.json());

const groq = new Groq({
  apiKey: process.env.GROQ_API_KEY,
});

app.post("/generate-text", async (req, res) => {
  const { prompt } = req.body;

  if (!prompt?.trim()) {
    return res.status(400).json({ error: "Prompt kosong bro!" });
  }

  try {
    const completion = await groq.chat.completions.create({
      messages: [
          { role: "system", content: "Kamu adalah asisten cerdas dan ramah. Jawab dalam bahasa yang sama dengan user." },
          { role: "user", content: prompt }
      ],
      model: "llama-3.1-8b-instant",
      temperature: 0.7,
      max_tokens: 2048,
    });

    const result = completion.choices[0]?.message?.content || "";
    res.json({ result });
  } catch (error) {
    console.error("Error Groq:", error.message);
    res.status(500).json({ error: error.message });
  }
});

app.get("/", (req, res) => {
  res.send('<h1>GROQ API SUDAH NYALA BRO!</h1><p>POST ke /generate-text → { "prompt": "apa aja" }</p>');
});

const PORT = 3000;
app.listen(PORT, "0.0.0.0", () => {
  console.log(`Server Groq jalan mantap di:`);
  console.log(`   → http://localhost:${PORT}`);
  if (ipv4) console.log(`   → http://${ipv4}:${PORT}`);
});
