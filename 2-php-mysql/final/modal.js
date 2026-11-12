// modal.js — buka/tutup modal berbasis atribut data-*
// Dipakai di halaman produk, penjualan, dan kategori.
//
// Tombol pembuka : data-modal="id-modal" data-title="Judul" data-fill='{"nama":"..."}'
//                  (opsional) data-alert="pesan" -> tampilkan alert, modal tidak dibuka
// Kotak modal    : [data-modal-box]  (disembunyikan dengan class "hidden")
// Judul modal    : [data-modal-title]
// Pesan error    : [data-modal-error]
// Tombol tutup   : [data-close-modal]

(function () {
  'use strict';

  // Isi field form berdasarkan atribut name dari objek data-fill
  function isiForm(form, data) {
    Object.keys(data).forEach(function (nama) {
      var el = form.elements[nama];
      if (!el) return;
      el.value = data[nama] == null ? '' : data[nama];
      // supaya script lain (mis. hitung total penjualan) ikut jalan
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  function bukaModal(tombol) {
    var pesan = tombol.getAttribute('data-alert');
    if (pesan) {
      alert(pesan);
      return;
    }

    var box = document.getElementById(tombol.getAttribute('data-modal'));
    if (!box) return;

    var form = box.querySelector('form');

    // judul
    var judul = box.querySelector('[data-modal-title]');
    var teksJudul = tombol.getAttribute('data-title');
    if (judul && teksJudul) judul.textContent = teksJudul;

    // buang pesan error lama dari percobaan sebelumnya
    var err = box.querySelector('[data-modal-error]');
    if (err) err.remove();

    // isi form
    if (form) {
      form.reset();
      var raw = tombol.getAttribute('data-fill');
      if (raw) {
        try {
          isiForm(form, JSON.parse(raw));
        } catch (e) {
          console.error('data-fill bukan JSON yang valid:', e);
        }
      }
    }

    box.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // fokus ke input pertama yang terlihat
    var pertama = box.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
    if (pertama) pertama.focus();
  }

  function tutupModal(box) {
    box.classList.add('hidden');
    if (!document.querySelector('[data-modal-box]:not(.hidden)')) {
      document.body.classList.remove('overflow-hidden');
    }
  }

  // Event delegation: tetap jalan walau script dimuat di mana saja
  document.addEventListener('click', function (e) {
    var pembuka = e.target.closest('[data-modal]');
    if (pembuka) {
      e.preventDefault();
      bukaModal(pembuka);
      return;
    }

    var penutup = e.target.closest('[data-close-modal]');
    if (penutup) {
      var boxTutup = penutup.closest('[data-modal-box]');
      if (boxTutup) tutupModal(boxTutup);
      return;
    }

    // klik area gelap di luar form = tutup
    if (e.target.matches('[data-modal-box]')) {
      tutupModal(e.target);
    }
  });

  // Tekan Esc = tutup modal yang sedang terbuka
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[data-modal-box]:not(.hidden)').forEach(tutupModal);
  });

  // Kalau modal terbuka otomatis (validasi server gagal), kunci scroll & fokus
  document.addEventListener('DOMContentLoaded', function () {
    var terbuka = document.querySelector('[data-modal-box]:not(.hidden)');
    if (!terbuka) return;
    document.body.classList.add('overflow-hidden');
    var pertama = terbuka.querySelector('input:not([type="hidden"]):not([readonly]), select, textarea');
    if (pertama) pertama.focus();
  });
})();