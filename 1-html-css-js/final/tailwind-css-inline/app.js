/**
 * 
 * HCM Module : Basic Inventory Management System
 * LocalStorage based
 * 
 */
"use strict";

// ---------- helper ----------
const $ = (id) => document.getElementById(id);

// ambil data dari localStorage, kalau kosong atau rusak balikin array kosong
const getData = (key) => {
  try {
    return JSON.parse(localStorage.getItem(key)) ?? [];
  } catch {
    return [];
  }
};
const saveData = (key, data) => localStorage.setItem(key, JSON.stringify(data));

// ubah angka jadi tampilan rupiah, misal 15000 jadi Rp 15.000
const rupiah = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  maximumFractionDigits: 0,
});
const formatRupiah = (angka) => rupiah.format(angka);

// cari satu data berdasarkan id
const getById = (list, id) => list.find((item) => item.id === id) ?? null;

// biar tulisan aneh (misal ada tanda < >) tidak merusak tampilan tabel
const escapeHtml = (text) =>
  String(text)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");

// gambar abu-abu kalau produk belum punya gambar
const PLACEHOLDER_IMG =
  "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='48' height='48'><rect width='48' height='48' fill='%23cbd5e1'/></svg>";

// baris tabel yang muncul kalau datanya masih kosong
const emptyRow = (colspan, text) =>
  `<tr><td colspan="${colspan}" class="td-empty">${text}</td></tr>`;

// tombol edit dan hapus di tiap baris
const actionButtons = (id) => `
  <button class="btn btn-small btn-edit" data-action="edit" data-id="${id}">Edit</button>
  <button class="btn btn-small btn-delete" data-action="delete" data-id="${id}">Hapus</button>`;

// isi tabel dengan data, atau tampilkan pesan kalau kosong
const renderTable = (tbodyId, list, colspan, emptyText, rowFn) => {
  $(tbodyId).innerHTML =
    list.length === 0
      ? emptyRow(colspan, emptyText)
      : list.map(rowFn).join("");
};

// bikin tombol edit/hapus di tabel bisa diklik
const onTableAction = (tbodyId, { edit, remove }) => {
  $(tbodyId).addEventListener("click", (e) => {
    const button = e.target.closest("button");
    if (!button) return;
    const { action, id } = button.dataset;
    if (action === "edit") edit(Number(id));
    if (action === "delete") remove(Number(id));
  });
};

// simpan dari form: kalau id kosong berarti data baru, kalau ada berarti edit
// "baru" itu isian tambahan yang cuma dipakai pas data baru dibuat
const upsert = (list, id, data, baru = {}) => {
  if (id === "") return [...list, { id: Date.now(), ...baru, ...data }];
  const index = list.findIndex((item) => item.id === Number(id));
  return list.with(index, { ...list[index], ...data });
};

// kalau belum ada akun sama sekali, bikin akun admin dulu
if (getData("users").length === 0) {
  saveData("users", [{ username: "admin", password: "admin123" }]);
}

// ---------- login & logout ----------
const loginPage = $("login-page");
const appPage = $("app-page");

// tutup halaman login, buka aplikasi
const showApp = () => {
  loginPage.classList.add("hide");
  appPage.classList.remove("hide");
  $("user-name").innerText = `Halo, ${localStorage.getItem("login")}`;
  showPage("dashboard");
};

$("login-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const username = $("login-username").value;
  const password = $("login-password").value;
  const found = getData("users").some(
    (user) => user.username === username && user.password === password
  );

  const errorBox = $("login-error");
  if (found) {
    localStorage.setItem("login", username); // biar tetap login walau halaman di-refresh
    errorBox.classList.add("hide");
    showApp();
  } else {
    errorBox.innerText = "Username atau password salah.";
    errorBox.classList.remove("hide");
  }
});

$("btn-logout").addEventListener("click", () => {
  localStorage.removeItem("login");
  appPage.classList.add("hide");
  loginPage.classList.remove("hide");
});

// ---------- pindah halaman ----------
const pages = ["dashboard", "kategori", "produk", "penjualan"];
const menuButtons = document.querySelectorAll(".menu-item");

// tampilkan satu halaman, sembunyikan yang lain
const showPage = (name) => {
  pages.forEach((page) => {
    $(`page-${page}`).classList.toggle("hide", page !== name);
  });
  menuButtons.forEach((menu) => {
    menu.classList.toggle("menu-active", menu.dataset.page === name);
  });
  renderers[name](); // muat ulang isinya biar datanya selalu terbaru
};

menuButtons.forEach((menu) => {
  menu.addEventListener("click", () => showPage(menu.dataset.page));
});

// ---------- popup (modal) ----------
const modals = ["modal-kategori", "modal-produk", "modal-penjualan"];
const openModal = (id) => $(id).classList.remove("hide");
const closeModal = (id) => $(id).classList.add("hide");

// tombol "Batal" menutup semua popup
document.querySelectorAll(".btn-close").forEach((button) => {
  button.addEventListener("click", () => modals.forEach(closeModal));
});

// ---------- dashboard ----------
// angka ringkasan di halaman depan
const renderDashboard = () => {
  const produk = getData("produk");
  const penjualan = getData("penjualan");

  $("stat-kategori").innerText = getData("kategori").length;
  $("stat-produk").innerText = produk.length;
  $("stat-stok").innerText = produk.reduce((sum, item) => sum + item.stok, 0);
  $("stat-pendapatan").innerText = formatRupiah(
    penjualan.reduce((sum, item) => sum + item.total, 0)
  );
};

// ---------- kategori ----------
const renderKategori = () => {
  renderTable(
    "tbody-kategori",
    getData("kategori"),
    3,
    'Belum ada kategori. Klik "Tambah kategori".',
    (item, index) => `
      <tr>
        <td class="td">${index + 1}</td>
        <td class="td">${escapeHtml(item.nama)}</td>
        <td class="td">${actionButtons(item.id)}</td>
      </tr>`
  );
};

$("btn-add-kategori").addEventListener("click", () => {
  $("title-kategori").innerText = "Tambah kategori";
  $("kategori-id").value = "";
  $("kategori-nama").value = "";
  openModal("modal-kategori");
});

const editKategori = (id) => {
  const item = getById(getData("kategori"), id);
  $("title-kategori").innerText = "Edit kategori";
  $("kategori-id").value = item.id;
  $("kategori-nama").value = item.nama;
  openModal("modal-kategori");
};

const deleteKategori = (id) => {
  // kalau masih ada produk di kategori ini, jangan dihapus
  if (getData("produk").some((item) => item.kategoriId === id)) {
    alert("Kategori masih dipakai oleh produk, tidak bisa dihapus.");
    return;
  }
  if (!confirm("Yakin hapus kategori ini?")) return;

  saveData("kategori", getData("kategori").filter((item) => item.id !== id));
  renderKategori();
};

onTableAction("tbody-kategori", { edit: editKategori, remove: deleteKategori });

$("form-kategori").addEventListener("submit", (e) => {
  e.preventDefault();
  const nama = $("kategori-nama").value.trim();
  if (nama === "") {
    alert("Nama kategori wajib diisi.");
    return;
  }

  saveData("kategori", upsert(getData("kategori"), $("kategori-id").value, { nama }));
  closeModal("modal-kategori");
  renderKategori();
});

// ---------- produk ----------
const renderProduk = () => {
  const kategori = getData("kategori");

  renderTable(
    "tbody-produk",
    getData("produk"),
    7,
    'Belum ada produk. Klik "Tambah produk".',
    (item, index) => `
      <tr>
        <td class="td">${index + 1}</td>
        <td class="td"><img class="product-img" src="${escapeHtml(item.gambar || PLACEHOLDER_IMG)}" alt="${escapeHtml(item.nama)}"></td>
        <td class="td">${escapeHtml(item.nama)}</td>
        <td class="td">${escapeHtml(getById(kategori, item.kategoriId)?.nama ?? "-")}</td>
        <td class="td">${item.stok}</td>
        <td class="td">${formatRupiah(item.harga)}</td>
        <td class="td">${actionButtons(item.id)}</td>
      </tr>`
  );
};

// isi pilihan kategori di form produk
const fillKategoriSelect = (selectedId) => {
  $("produk-kategori").innerHTML = getData("kategori")
    .map(
      (item) =>
        `<option value="${item.id}" ${item.id === selectedId ? "selected" : ""}>${escapeHtml(item.nama)}</option>`
    )
    .join("");
};

$("btn-add-produk").addEventListener("click", () => {
  // produk harus punya kategori, jadi kategori harus ada dulu
  if (getData("kategori").length === 0) {
    alert("Tambahkan kategori terlebih dahulu.");
    return;
  }
  $("title-produk").innerText = "Tambah produk";
  ["id", "nama", "gambar", "stok", "harga"].forEach((field) => {
    $(`produk-${field}`).value = ""; // kosongkan isian form
  });
  fillKategoriSelect(0);
  openModal("modal-produk");
});

const editProduk = (id) => {
  const item = getById(getData("produk"), id);
  $("title-produk").innerText = "Edit produk";
  $("produk-id").value = item.id;
  $("produk-nama").value = item.nama;
  $("produk-gambar").value = item.gambar;
  $("produk-stok").value = item.stok;
  $("produk-harga").value = item.harga;
  fillKategoriSelect(item.kategoriId);
  openModal("modal-produk");
};

const deleteProduk = (id) => {
  // produk yang sudah pernah terjual jangan dihapus, nanti riwayat penjualannya hilang
  if (getData("penjualan").some((item) => item.produkId === id)) {
    alert("Produk sudah ada di data penjualan, tidak bisa dihapus.");
    return;
  }
  if (!confirm("Yakin hapus produk ini?")) return;

  saveData("produk", getData("produk").filter((item) => item.id !== id));
  renderProduk();
};

onTableAction("tbody-produk", { edit: editProduk, remove: deleteProduk });

$("form-produk").addEventListener("submit", (e) => {
  e.preventDefault();
  const nama = $("produk-nama").value.trim();
  const stok = $("produk-stok").value;
  const harga = $("produk-harga").value;

  if (nama === "" || stok === "" || harga === "") {
    alert("Nama, stok, dan harga wajib diisi.");
    return;
  }

  const dataForm = {
    nama,
    kategoriId: Number($("produk-kategori").value),
    gambar: $("produk-gambar").value.trim(),
    stok: Number(stok),
    harga: Number(harga),
  };

  saveData("produk", upsert(getData("produk"), $("produk-id").value, dataForm));
  closeModal("modal-produk");
  renderProduk();
});

// ---------- penjualan ----------
// tiap penjualan otomatis mengurangi stok produk
const renderPenjualan = () => {
  renderTable(
    "tbody-penjualan",
    getData("penjualan"),
    6,
    'Belum ada penjualan. Klik "Tambah penjualan".',
    (item, index) => `
      <tr>
        <td class="td">${index + 1}</td>
        <td class="td">${item.tanggal}</td>
        <td class="td">${escapeHtml(item.produkNama)}</td>
        <td class="td">${item.qty}</td>
        <td class="td">${formatRupiah(item.total)}</td>
        <td class="td">${actionButtons(item.id)}</td>
      </tr>`
  );
};

// isi pilihan produk di form penjualan (sekalian tampilkan sisa stok)
const fillProdukSelect = (selectedId) => {
  $("penjualan-produk").innerHTML = getData("produk")
    .map(
      (item) =>
        `<option value="${item.id}" ${item.id === selectedId ? "selected" : ""}>${escapeHtml(item.nama)} (stok: ${item.stok})</option>`
    )
    .join("");
};

// hitung total harga otomatis waktu produk atau qty diubah
const hitungTotal = () => {
  const produk = getById(getData("produk"), Number($("penjualan-produk").value));
  const qty = Number($("penjualan-qty").value);
  $("penjualan-total").value =
    produk && qty > 0 ? formatRupiah(produk.harga * qty) : "";
};

$("penjualan-produk").addEventListener("change", hitungTotal);
$("penjualan-qty").addEventListener("input", hitungTotal);

$("btn-add-penjualan").addEventListener("click", () => {
  if (getData("produk").length === 0) {
    alert("Tambahkan produk terlebih dahulu.");
    return;
  }
  $("title-penjualan").innerText = "Tambah penjualan";
  $("penjualan-id").value = "";
  $("penjualan-qty").value = "";
  $("penjualan-total").value = "";
  fillProdukSelect(0);
  openModal("modal-penjualan");
});

const editPenjualan = (id) => {
  const item = getById(getData("penjualan"), id);
  $("title-penjualan").innerText = "Edit penjualan";
  $("penjualan-id").value = item.id;
  fillProdukSelect(item.produkId);
  $("penjualan-qty").value = item.qty;
  hitungTotal();
  openModal("modal-penjualan");
};

const deletePenjualan = (id) => {
  if (!confirm("Yakin hapus penjualan ini? Stok produk akan dikembalikan.")) return;

  const penjualan = getData("penjualan");
  const produk = getData("produk");

  // penjualan dibatalkan, jadi stoknya balik lagi
  const hapus = getById(penjualan, id);
  const p = getById(produk, hapus.produkId);
  if (p) p.stok += hapus.qty;

  saveData("produk", produk);
  saveData("penjualan", penjualan.filter((item) => item.id !== id));
  renderPenjualan();
};

onTableAction("tbody-penjualan", { edit: editPenjualan, remove: deletePenjualan });

$("form-penjualan").addEventListener("submit", (e) => {
  e.preventDefault();
  const id = $("penjualan-id").value;
  const produkId = Number($("penjualan-produk").value);
  const qty = Number($("penjualan-qty").value);

  if (qty <= 0) {
    alert("Qty harus lebih dari 0.");
    return;
  }

  const penjualan = getData("penjualan");
  const produk = getData("produk");

  // kalau lagi edit, balikin dulu stok dari penjualan yang lama
  if (id !== "") {
    const lama = getById(penjualan, Number(id));
    const produkLama = getById(produk, lama.produkId);
    if (produkLama) produkLama.stok += lama.qty;
  }

  // stok harus cukup, kalau nggak ya batal (belum ada yang tersimpan jadi aman)
  const p = getById(produk, produkId);
  if (qty > p.stok) {
    alert(`Stok tidak cukup. Sisa stok: ${p.stok}`);
    return;
  }
  p.stok -= qty;

  const dataForm = { produkId, produkNama: p.nama, qty, total: p.harga * qty };
  const tanggal = new Date().toLocaleDateString("id-ID"); // tanggal cuma diisi sekali, pas data dibuat

  saveData("produk", produk);
  saveData("penjualan", upsert(penjualan, id, dataForm, { tanggal }));
  closeModal("modal-penjualan");
  renderPenjualan();
});

// ---------- mulai ----------
// daftar fungsi yang dipanggil showPage() waktu pindah halaman
const renderers = {
  dashboard: renderDashboard,
  kategori: renderKategori,
  produk: renderProduk,
  penjualan: renderPenjualan,
};

// kalau sebelumnya sudah login, langsung masuk tanpa isi form lagi
if (localStorage.getItem("login") !== null) {
  showApp();
}