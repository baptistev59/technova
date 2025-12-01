/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.html.twig",
    "./assets/**/*.js",
    "./src/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        "technova-primary": "#1E88E5",
        "technova-secondary": "#64B5F6"
      },
      fontFamily: {
        inter: ["Inter", "sans-serif"]
      },
      borderRadius: {
        "technova": "20px"
      }
    }
  },
  plugins: []
};
