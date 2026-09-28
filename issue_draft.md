### Deskripsi Fitur
Implementasi sistem pengawasan ujian (*proctoring*) berbasis *webcam* secara *real-time* yang menghubungkan antarmuka Moodle (Siswa) dengan *Dashboard* Next.js (Pengawas). 

Sistem ini akan melakukan penangkapan gambar (*Webcam Frame*) dari browser siswa secara berkala saat ujian berlangsung dan mendistribusikannya ke *Dashboard* pengawas.

### Alur Sistem (Sequence Diagram)
```mermaid
sequenceDiagram
    autonumber
    actor Siswa as Browser Siswa (Moodle)
    participant Moodle as Server Moodle (PHP)
    participant NextJS as Server Next.js (API)
    participant DB as DB / Storage Next.js
    actor Pengawas as Dashboard Pengawas (Next.js UI)

    %% Inisialisasi Ujian
    Siswa->>Moodle: 1. Buka Ujian (Quiz Guard Plugin)
    Moodle-->>Siswa: 2. Render Halaman Ujian + JS Proctoring + Session Token

    %% Loop Capture Gambar
    loop Setiap N Detik (misal 5-10 detik)
        Siswa->>Siswa: 3. Capture Frame Webcam via MediaDevices API
        Siswa->>NextJS: 4. HTTP POST /api/proctoring/upload (Payload: Image + UserID + Token)
        
        activate NextJS
        NextJS->>NextJS: 5. Verifikasi Token & Process Image (AI/Face Detection)
        NextJS->>DB: 6. Simpan Metadata & File Gambar
        NextJS-->>Pengawas: 7. Emit Frame via WebSockets / SSE (Real-time Stream)
        NextJS-->>Siswa: 8. Response Status (200 OK)
        deactivate NextJS
    end

    %% Selesai Ujian
    Siswa->>Moodle: 9. Submit Jawaban Ujian
    Moodle->>NextJS: 10. Webhook: Quiz Completed (Opsional, via Observer Moodle)
```

### Kebutuhan Teknis (*Task Breakdown*)
- [ ] **Moodle (quizaccess_guard):**
  - Injeksi *script* JavaScript (JS Proctoring) menggunakan API Moodle ke dalam halaman `attempt.php`.
  - Generate *Session Token* aman untuk diberikan ke klien agar bisa dipakai menembak API Next.js.
  - (Opsional) Trigger *Webhook Event Observer* (`quiz_attempt_submitted`) ke Next.js.
- [ ] **Next.js (exam-guard):**
  - Pembuatan API *Endpoint* `POST /api/proctoring/upload` untuk menerima *frame* dan melakukan verifikasi token.
  - Implementasi *middleware* atau *service* untuk pemrosesan AI/*Face Detection* ringan.
  - Pembuatan skema *Database* (Prisma) dan *Storage* untuk mencatat Metadata dan log *screenshot*.
  - Pembuatan *channel* WebSocket / Server-Sent Events (SSE) untuk mendorong data ke Pengawas.
  - Menyesuaikan UI *Unified Proctor Cockpit* untuk merender *stream* gambar siswa secara *real-time*.
