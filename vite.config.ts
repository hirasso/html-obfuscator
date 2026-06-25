import { defineConfig } from "vite";

export default defineConfig({
  build: {
    lib: {
      entry: ["resources/html-obfuscator.js"],
      formats: ["iife"],
      name: "HtmlObfuscator",
      fileName: (format, entryName) => `${entryName}.min.js`,
    },
    outDir: "resources/dist",
    minify: "terser",
    terserOptions: {
      compress: {
        passes: 2,
        unsafe: true,
        unsafe_arrows: true,
        unsafe_comps: true,
        unsafe_methods: true,
        drop_console: true,
      },
    },
    sourcemap: false,
    target: "es2020",
  },
});
