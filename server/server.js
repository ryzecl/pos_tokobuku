import "dotenv/config";
import express from "express";
import cors from "cors";
import { Groq } from "groq-sdk";
import { execSync } from "child_process"; // ini yang benar untuk ESM
import mysql from "mysql2/promise";
// ==================== AMBIL IPv4 (Windows) ====================
function getIpv4FromIpconfig() {
  try {
    const output = execSync("ipconfig", { encoding: "utf8" });
    const regex = /(?:IPv4 Address|Alamat IPv4)[\. ]*:\s*([0-9]{1,3}(\.[0-9]{1,3}){3})/i;
    const match = output.match(regex);
    return match ? match[1].trim() : null;
  } catch (error) {
    console.error("Gagal ambil IP:", error.message);
    return null;
  }
}
const ipv4 = getIpv4FromIpconfig();

// ==================== KONEKSI MYSQL ====================
const pool = mysql.createPool({
  host: "localhost",
  database: "pos_daebook",
  user: "root",
  password: "",
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  charset: "utf8mb4",
});

async function query(sql, params = []) {
  const [rows] = await pool.execute(sql, params);
  return rows;
}

// ==================== CACHING DATA BUKU ====================
let cachedBuku = [];
let lastUpdated = null;

async function loadBukuFromDB() {
  try {
    const buku = await query(`
      SELECT id, kode_buku, nama_buku, harga_jual, stok, deskripsi
      FROM buku
      ORDER BY nama_buku
    `);
    cachedBuku = buku;
    lastUpdated = new Date();
    console.log(`Cache buku di-refresh: ${buku.length} buku (${lastUpdated.toLocaleString("id-ID")})`);
  } catch (error) {
    console.error("Gagal load data buku:", error.message);
  }
}

// Load pertama kali
await loadBukuFromDB();

// Refresh otomatis tiap 5 menit
setInterval(loadBukuFromDB, 5 * 60 * 1000);

// Pencarian di cache (super cepat)
function cariBukuDiCache(keyword = "", limit = 10) {
  if (!keyword || keyword.toLowerCase().trim() === "semua") {
    return cachedBuku.slice(0, limit);
  }
  const lower = keyword.toLowerCase();
  return cachedBuku
    .filter(b =>
      b.nama_buku.toLowerCase().includes(lower) ||
      b.kode_buku.toLowerCase().includes(lower)
    )
    .slice(0, limit);
}

// ==================== EXPRESS & CORS ====================
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

// ==================== TOOL CARI BUKU ====================
const tools = [
  {
    type: "function",
    function: {
      name: "cari_buku",
      description: "Mencari buku berdasarkan nama atau kode buku. Gunakan setiap kali customer tanya stok, harga, atau ketersediaan.",
      parameters: {
        type: "object",
        properties: {
          keyword: { type: "string", description: "Kata kunci (nama_buku/kode_buku). Jika ingin semua, isi 'semua'." },
          limit: { type: "integer", description: "Maksimal buku (default 10)." }
        },
        required: ["keyword"]
      }
    }
  }
];

// ==================== ENDPOINT CHAT ====================
app.post("/generate-text", async (req, res) => {
  const { prompt, messageHistory = [] } = req.body;
  if (!prompt?.trim()) return res.status(400).json({ error: "Prompt kosong!" });

  const messages = [
    {
      role: "system",
      content: `Kamu Dea, asisten toko buku DaeBook yang ramah dan santai. 
      Jawab dalam bahasa Indonesia natural. 
      Data buku terakhir di-update: ${lastUpdated ? lastUpdated.toLocaleString('id-ID') : 'sedang loading'}.
      Gunakan tool cari_buku saat customer tanya buku/stok/harga. 
      Format harga: Rp (contoh: Rp125.000).`,
    },
    ...messageHistory,
    { role: "user", content: prompt },
  ];

  try {
    const response1 = await groq.chat.completions.create({
      messages,
      model: "llama-3.1-8b-instant",
      temperature: 0.7,
      max_tokens: 1024,
      tools,
      tool_choice: "auto",
    });

    const message = response1.choices[0].message;

    if (message.tool_calls?.length > 0) {
      const toolCall = message.tool_calls[0];
      const args = JSON.parse(toolCall.function.arguments);
      const hasilBuku = cariBukuDiCache(args.keyword, args.limit || 10);

      const response2 = await groq.chat.completions.create({
        messages: [
          ...messages,
          message,
          {
            role: "tool",
            tool_call_id: toolCall.id,
            name: toolCall.function.name,
            content: JSON.stringify(hasilBuku),
          },
        ],
        model: "llama-3.1-8b-instant",
        temperature: 0.7,
        max_tokens: 1024,
      });

      res.json({ result: response2.choices[0].message.content });
    } else {
      res.json({ result: message.content });
    }
  } catch (error) {
    console.error("Error Groq:", error.message);
    res.status(500).json({ error: error.message });
  }
});

// ==================== ENDPOINT REFRESH CACHE (dipanggil dari PHP) ====================
app.post("/refresh-buku-cache", async (req, res) => {
  const secret = req.headers['x-secret-key'] || req.body.secret || "";
  if (secret !== process.env.CACHE_REFRESH_SECRET) {
    return res.status(403).json({ success: false, message: "Unauthorized" });
  }

  await loadBukuFromDB();
  res.json({
    success: true,
    message: "Cache berhasil di-refresh",
    updated_at: lastUpdated.toLocaleString('id-ID'),
    total_buku: cachedBuku.length
  });
});

// ==================== HALAMAN DEPAN ====================
app.get("/", (req, res) => {
  res.send(`
    <h1>DaeBook Assistant + Cache (Real-time via PHP)</h1>
    <p>Data buku: ${cachedBuku.length} item</p>
    <p>Terakhir update: ${lastUpdated ? lastUpdated.toLocaleString('id-ID') : 'loading...'}</p>
    <p>Chat super cepat + selalu akurat!</p>
  `);
});

// ==================== START SERVER ====================
const PORT = 3000;
app.listen(PORT, "0.0.0.0", () => {
  console.log(`Server DaeBook jalan mantap di:`);
  console.log(`   → http://localhost:${PORT}`);
  if (ipv4) console.log(`   → http://${ipv4}:${PORT}`);
});
