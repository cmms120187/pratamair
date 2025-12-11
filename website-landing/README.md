# TPM CMMS - Landing Page

Website landing page interaktif untuk mempromosikan sistem Computerized Maintenance Management System (CMMS) berbasis Total Productive Maintenance (TPM).

## Fitur

- ✅ **Desain Modern & Responsif** - Tampil sempurna di semua perangkat (desktop, tablet, mobile)
- ✅ **Animasi Interaktif** - Animasi smooth dan menarik untuk meningkatkan user engagement
- ✅ **Simulasi Sistem** - Demo interaktif fitur-fitur utama sistem
- ✅ **Colorful Design** - Desain colorful dengan gradient yang menarik
- ✅ **Performance Optimized** - Loading cepat dan optimized untuk SEO

## Struktur Folder

```
website-landing/
├── index.html          # Halaman utama
├── css/
│   └── style.css       # Stylesheet utama
├── js/
│   └── main.js         # JavaScript untuk interaktivitas
└── README.md           # Dokumentasi
```

## Cara Menggunakan

1. **Buka file `index.html`** di browser untuk melihat website landing page
2. Atau **serve dengan web server lokal**:
   ```bash
   # Menggunakan PHP built-in server
   php -S localhost:8000
   
   # Atau menggunakan Python
   python -m http.server 8000
   
   # Atau menggunakan Node.js (http-server)
   npx http-server -p 8000
   ```
3. Buka browser dan akses `http://localhost:8000/website-landing`

## Bagian-Bagian Website

### 1. Navigation Bar
- Sticky navigation yang mengikuti scroll
- Hamburger menu untuk mobile
- Smooth scroll ke section yang dituju

### 2. Hero Section
- Judul dan deskripsi produk
- Call-to-action buttons
- Statistik counter animation
- Preview dashboard dengan chart

### 3. Features Section
- 6 kartu fitur utama:
  - Dashboard Real-Time
  - Preventive Maintenance
  - Downtime Management
  - MTTR & MTBF Analysis
  - Skill Matrix
  - Asset Location Management

### 4. Interactive Demo Section
- Tab untuk berbagai demo:
  - **Dashboard Demo**: Statistik dan chart real-time
  - **Preventive Maintenance Demo**: PM schedule dengan filter dan progress bar
  - **Downtime Tracking Demo**: Tabel downtime dengan status
  - **Reports & Analytics Demo**: Laporan performa dan analisis

### 5. Benefits Section
- Manfaat implementasi sistem
- ROI yang dapat dicapai
- Statistik peningkatan performa

### 6. Pricing Section
- 3 paket pricing:
  - **Starter**: Rp 25jt/bulan
  - **Professional**: Rp 50jt/bulan (Featured)
  - **Enterprise**: Custom pricing

### 7. Contact Section
- Form kontak untuk konsultasi
- Informasi kontak perusahaan

### 8. Footer
- Link navigasi
- Social media links
- Copyright information

## Teknologi yang Digunakan

- **HTML5** - Struktur halaman
- **CSS3** - Styling dengan gradient, animations, dan responsive design
- **JavaScript (Vanilla)** - Interaktivitas dan animations
- **Chart.js** - Library untuk chart dan visualisasi data
- **Font Awesome** - Icons
- **Google Fonts** - Typography (Inter font family)

## Fitur Interaktif

1. **Stats Counter Animation** - Angka statistik yang count up saat muncul di viewport
2. **Interactive Charts** - Chart.js untuk visualisasi data
3. **Tab Navigation** - Switch antara berbagai demo fitur
4. **Filter System** - Filter PM schedule berdasarkan status
5. **Progress Bar Animation** - Animated progress bars untuk PM completion
6. **Scroll Animations** - Fade in dan slide up saat scroll
7. **Parallax Effects** - Parallax background effect
8. **Form Validation** - Form kontak dengan validation dan feedback

## Customization

### Mengubah Warna
Edit CSS variables di `css/style.css`:
```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #f59e0b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    /* ... */
}
```

### Mengubah Konten
Edit teks di `index.html` sesuai kebutuhan perusahaan

### Menambah Chart Data
Edit data chart di `js/main.js` di fungsi `initDemoCharts()` dan `initReportCharts()`

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Tips Optimasi

1. **Optimize Images**: Jika menambahkan gambar, gunakan format WebP atau kompres gambar
2. **Lazy Loading**: Implementasi lazy loading untuk gambar dan konten berat
3. **CDN**: Gunakan CDN untuk library eksternal (Chart.js, Font Awesome)
4. **Minify**: Minify CSS dan JS untuk production
5. **Caching**: Set HTTP caching headers untuk static assets

## Support

Untuk pertanyaan atau dukungan, hubungi tim development.

---

**Dibuat dengan ❤️ untuk TPM CMMS**

