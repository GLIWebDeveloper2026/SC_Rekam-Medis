/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2FA791', // Hijau Toska Utama
          light: '#E4F5F1',   // Hijau Toska Muda / Highlight
          dark: '#248674',
        },
        secondary: {
          DEFAULT: '#4A90E2', // Biru Muda Sekunder
        },
        surface: {
          DEFAULT: '#FFFFFF', // Putih Base
          off: '#F7F9FA',     // Off-White / Abu Sangat Muda
        },
        dark: {
          DEFAULT: '#2E2E2E', // Abu Gelap Teks Utama
          muted: '#7A7A7A',   // Abu Netral Teks Sekunder
        },
        alert: {
          DEFAULT: '#E4574C', // Merah Lembut
        },
        success: {
          DEFAULT: '#3FB27F', // Hijau Sukses
        }
      },
      fontFamily: {
        heading: ['Poppins', 'sans-serif'],
        body: ['Inter', 'Nunito Sans', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
