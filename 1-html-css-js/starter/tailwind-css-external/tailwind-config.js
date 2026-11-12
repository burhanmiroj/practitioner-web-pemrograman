// Dimuat SETELAH script CDN Tailwind
tailwind.config = {
  content: ["./index.html", "./app.js"],
  theme: {
    extend: {
      colors: {
        ink: "#16162a",
        paper: "#f1f2fb",
        cobalt: "#2a46ff",
        sun: "#ffd84d",
        mint: "#7be0b0",
        sky: "#a9bcff",
        coral: "#ff8f7a",
      },
      boxShadow: {
        hard: "4px 4px 0 0 #16162a",
        "hard-sm": "2px 2px 0 0 #16162a",
        "hard-lg": "8px 8px 0 0 #16162a",
      },
    },
  },
};