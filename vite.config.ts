import { defineConfig } from "vite";
import { minify } from "terser";
import type { MinifyOptions } from "terser";
import type { Plugin } from "vite";

function terserPlugin(options: MinifyOptions): Plugin {
  return {
    name: "terser",
    async renderChunk(code: string) {
      const result = await minify(code, options);
      return { code: result.code!, map: result.map as string | null };
    },
  };
}

const terserOptions: MinifyOptions = {
  compress: {
    passes: 2,
    unsafe: true,
    unsafe_arrows: true,
    unsafe_comps: true,
    unsafe_methods: true,
    drop_console: true,
  },
};

export default defineConfig({
  root: "resources",
  build: {
    lib: {
      entry: ["src/html-obfuscator.ts"],
    },
    outDir: "dist",
    sourcemap: false,
    target: "es2020",
    rolldownOptions: {
      output: [
        {
          format: "iife",
          entryFileNames: "[name].js",
          name: "HtmlObfuscator",
          minify: false,
        },
        {
          format: "iife",
          entryFileNames: "[name].min.js",
          name: "HtmlObfuscator",
          minify: true,
          plugins: [terserPlugin(terserOptions)],
        },
      ],
    },
  },
});
