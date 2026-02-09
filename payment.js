// Generate Order ID sekali saja
const orderId = "PB-" + Date.now();
document.getElementById("orderId").innerText = orderId;

// Klik BAYAR
document.getElementById("payBtn").addEventListener("click", () => {
    const name   = document.getElementById("name").value.trim();
    const amount = document.getElementById("amount").value;

    if (!name || !amount) {
        alert("Nama dan nominal wajib diisi");
        return;
    }

    // Tampilkan Order ID & QR
    document.getElementById("showOrderId").innerText = orderId;
    document.getElementById("qrisBox").style.display = "block";

    // Status awal
    document.getElementById("status").innerHTML = `
        ⏳ Silakan scan QR GoBiz di bawah<br>
        <small>
            Nominal: <b>Rp ${Number(amount).toLocaleString("id-ID")}</b><br>
            Kasir GoBiz input Order ID
        </small>
    `;

    // Info tambahan setelah 3 detik
    setTimeout(() => {
        document.getElementById("status").innerHTML += `
            <br><small>⏱️ Jika sudah bayar, tunjukkan ke kasir</small>
        `;
    }, 3000);
});

// Klik KONFIRMASI
document.getElementById("confirmBtn").addEventListener("click", () => {
    document.getElementById("status").innerHTML = `
        ✅ <b>Pembayaran dikonfirmasi</b><br>
        Terima kasih 🙏
    `;

    const btn = document.getElementById("confirmBtn");
    btn.disabled = true;
    btn.innerText = "✔️ Sudah Dikonfirmasi";
});
