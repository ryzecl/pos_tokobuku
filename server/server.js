import "dotenv/config";
import express from "express";
import cors from "cors";
import helmet from "helmet";
import rateLimit from "express-rate-limit";
import { Groq } from "groq-sdk";
import { execSync } from "child_process";
import mysql from "mysql2/promise";
import Fuse from "fuse.js";

// ==================== AMBIL IPv4 (Windows) ====================
function getIpv4FromIpconfig() {
  try {
    const output = execSync("ipconfig", { encoding: "utf8" });
    const regex =
      /(?:IPv4 Address|Alamat IPv4)[\. ]*:\s*([0-9]{1,3}(\.[0-9]{1,3}){3})/i;
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
let fuseIndex = null;
let lastUpdated = null;

async function loadBukuFromDB() {
  try {
    const buku = await query(`
      SELECT id, kode_buku, nama_buku, harga_jual, stok, deskripsi
      FROM buku
      ORDER BY nama_buku
    `);
    cachedBuku = buku;

    // Initialize Fuse
    fuseIndex = new Fuse(cachedBuku, {
      keys: ["nama_buku", "kode_buku"],
      threshold: 0.4, // Sensitivity (0.0 = exact, 1.0 = match anything)
      distance: 100,
    });

    lastUpdated = new Date();
    console.log(
      `Cache buku di-refresh: ${buku.length} buku (${lastUpdated.toLocaleString(
        "id-ID"
      )})`
    );
  } catch (error) {
    console.error("Gagal load data buku:", error.message);
  }
}

// Load pertama kali
await loadBukuFromDB();

// Refresh otomatis tiap 5 menit
setInterval(loadBukuFromDB, 5 * 60 * 1000);

// Pencarian di cache
function cariBukuDiCache(keyword = "", limit = 10) {
  let matches = [];
  let isFuzzy = false; // Flag to indicate if result comes from fuzzy search

  if (!keyword || keyword.toLowerCase().trim() === "semua") {
    matches = cachedBuku;
  } else {
    const lower = keyword.toLowerCase();

    // 1. Coba Exact / Substring Match dulu
    matches = cachedBuku.filter(
      (b) =>
        b.nama_buku.toLowerCase().includes(lower) ||
        b.kode_buku.toLowerCase().includes(lower)
    );

    // 2. Jika kosong, coba Fuzzy Search
    if (matches.length === 0 && fuseIndex) {
      const fuzzyResults = fuseIndex.search(keyword);
      matches = fuzzyResults.map((res) => res.item); // Fuse returns { item, refIndex }
      if (matches.length > 0) isFuzzy = true;
    }
  }

  const totalMatch = matches.length;
  const slicedData = matches.slice(0, limit);
  const sisa = totalMatch - slicedData.length;

  return {
    data: slicedData,
    total: totalMatch,
    ditampilkan: slicedData.length,
    sisa: sisa > 0 ? sisa : 0,
    is_recommendation: isFuzzy,
    pesan: isFuzzy
      ? `Tidak ditemukan hasil persis untuk '${keyword}'. Berikut adalah buku yang namanya mirip:`
      : sisa > 0
      ? `Menampilkan ${slicedData.length} dari total ${totalMatch} buku. Masih ada ${sisa} buku lain yang tidak ditampilkan.`
      : `Menampilkan semua ${totalMatch} buku yang ditemukan.`,
  };
}

// ==================== EXPRESS SETUP ====================
const app = express();

// Security headers
app.use(helmet());

// Rate limiting (30 request per menit per IP)
const limiter = rateLimit({
  windowMs: 1 * 60 * 1000, // 1 menit
  max: 30,
  standardHeaders: true,
  legacyHeaders: false,
  message:
    "Terlalu banyak permintaan, silakan coba lagi beberapa saat lagi ya 😊",
});
app.use("/generate-text", limiter); // hanya pada endpoint chat

// CORS ketat
app.use(
  cors({
    origin: [
      "http://localhost",
      "http://127.0.0.1",
      "http://localhost:80",
      "http://127.0.0.1:80",
      "http://localhost:8080",
      "http://127.0.0.1:8080",
      ipv4 ? `http://${ipv4}` : null,
      ipv4 ? `http://${ipv4}:80` : null,
      ipv4 ? `http://${ipv4}:8080` : null,
      "http://pos_tokobuku.test",
      "https://pos_tokobuku.test",
    ].filter(Boolean),
  })
);

app.use(express.json({ limit: "10mb" })); // batasi ukuran body

const groq = new Groq({
  apiKey: process.env.GROQ_API_KEY,
});

// ==================== TOOL CARI BUKU ====================
const tools = [
  {
    type: "function",
    function: {
      name: "cari_buku",
      description:
        "Mencari buku berdasarkan nama atau kode. Gunakan keyword='semua' jika user bertanya 'ada buku apa saja' atau ingin melihat katalog. Hasil akan dibatasi, namun akan ada info total buku.",
      parameters: {
        type: "object",
        properties: {
          keyword: {
            type: "string",
            description:
              "Kata kunci pencarian. Isi 'semua' untuk menampilkan semua buku.",
          },
          limit: {
            type: "integer",
            description:
              "Jumlah maksimal hasil yang ditampilkan (default: 10). Jangan set terlalu besar agar respon cepat.",
          },
        },
        required: ["keyword"],
      },
    },
  },
];

// ==================== ENDPOINT CHAT ====================
app.post("/generate-text", async (req, res) => {
  const { prompt, messageHistory = [] } = req.body;

  if (!prompt?.trim()) {
    return res.status(400).json({ error: "Prompt tidak boleh kosong!" });
  }

  // Batasi panjang prompt biar aman
  if (prompt.length > 1500) {
    return res
      .status(400)
      .json({ error: "Pesan terlalu panjang, maksimal 1500 karakter ya." });
  }

  const updateWaktu = lastUpdated
    ? lastUpdated.toLocaleString("id-ID")
    : "sedang loading";

  const messages = [
    {
      role: "system",
      content: `Kamu adalah Dea, asisten toko buku DaeBook yang ramah, profesional, dan santai.
Selalu jawab dalam bahasa Indonesia yang natural dan sopan, kamu juga adalah seorang wanita.
DATA BUKU HANYA BOLEH DIAMBIL DARI TOOL cari_buku. JANGAN PERNAH menebak atau mengada-ada judul, harga, atau stok.
Jika tool mengembalikan 'is_recommendation' = true, artinya buku yang dicari user tidak ketemu tapi sistem menemukan buku yang mirip. Katakan: "Mungkin maksud Anda buku [Nama Buku]?" atau kalimat sejenis sebelum menjelaskan detailnya.
Jika hasil pencarian kosong (dan tidak ada rekomendasi), katakan dengan jujur: "Maaf, buku yang Anda cari tidak tersedia di stok kami saat ini."
Format harga: Rp di depan dan pakai titik sebagai pemisah ribuan (contoh: Rp125.000).
Jika stok = 0, beri tahu "stok sedang kosong" atau "maaf stok habis".
Jika stok > 0, beri tahu jumlah stoknya.
Jika ada sisa buku yang tidak ditampilkan, sebutkan singkat ("dan ada X buku lainnya...").
Data buku terakhir di-update: ${updateWaktu}.`,
    },
    ...messageHistory,
    { role: "user", content: prompt },
  ];

  try {
    const response1 = await groq.chat.completions.create({
      messages,
      model: "llama-3.1-8b-instant",
      temperature: 0.6,
      max_tokens: 1024,
      tools,
      tool_choice: "auto",
    });

    const message = response1.choices[0].message;

    if (message.tool_calls?.length > 0) {
      const toolCall = message.tool_calls[0];
      const args = JSON.parse(toolCall.function.arguments);

      // Default limit 10 jika tidak diisi
      const limit = args.limit || 10;
      const hasilBuku = cariBukuDiCache(args.keyword || "", limit);

      const response2 = await groq.chat.completions.create({
        messages: [
          ...messages,
          message,
          {
            role: "tool",
            tool_call_id: toolCall.id,
            name: toolCall.function.name,
            content: JSON.stringify(hasilBuku, null, 2),
          },
        ],
        model: "llama-3.1-8b-instant",
        temperature: 0.6,
        max_tokens: 1024,
      });

      res.json({ result: response2.choices[0].message.content.trim() });
    } else {
      res.json({ result: message.content.trim() });
    }
  } catch (error) {
    console.error("Error Groq:", error.message);
    res.status(500).json({
      error:
        "Maaf, saat ini sedang ada gangguan pada server AI. Silakan coba lagi beberapa saat lagi.",
    });
  }
});

// ==================== ENDPOINT REFRESH CACHE (lebih aman) ====================
const allowedIps = ["127.0.0.1", "::1", "::ffff:127.0.0.1"];
if (ipv4) allowedIps.push(ipv4);

app.post("/refresh-buku-cache", async (req, res) => {
  const clientIp = req.ip || req.connection.remoteAddress;

  // IP whitelist (hanya dari mesin sendiri atau server PHP)
  if (!allowedIps.includes(clientIp)) {
    return res
      .status(403)
      .json({ success: false, message: "IP tidak diizinkan" });
  }

  const secret = req.headers["x-secret-key"] || req.body.secret || "";
  if (secret !== process.env.CACHE_REFRESH_SECRET) {
    return res.status(403).json({ success: false, message: "Unauthorized" });
  }

  await loadBukuFromDB();
  res.json({
    success: true,
    message: "Cache berhasil di-refresh",
    updated_at: lastUpdated.toLocaleString("id-ID"),
    total_buku: cachedBuku.length,
  });
});

// ==================== HALAMAN DEPAN ====================
app.get("/", (req, res) => {
  res.send(`
    <h1>DaeBook Assistant API</h1>
    <p>Status: <strong>Online & Aman</strong></p>
    <p>Data buku: ${cachedBuku.length} item</p>
    <p>Terakhir update: ${
      lastUpdated ? lastUpdated.toLocaleString("id-ID") : "loading..."
    }</p>
    <p>Keamanan: Helmet ✓ | Rate Limit ✓ | CORS ketat ✓ | Input validation ✓</p>
  `);
});

// ==================== START SERVER ====================
const PORT = 3000;
app.listen(PORT, "0.0.0.0", () => {
  console.log(`Server DaeBook Assistant berjalan aman di port ${PORT}`);
  console.log(`   → http://localhost:${PORT}`);
  if (ipv4) console.log(`   → http://${ipv4}:${PORT}`);
});
