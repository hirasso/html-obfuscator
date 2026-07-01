import { defineConfig, Plugin } from "rolldown";
import { minify } from "terser";
import type { MinifyOptions } from "terser";
import { replacePlugin } from "rolldown/plugins";

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

export default defineConfig([
  /** The unminified development build */
  {
    input: "resources/src",
    output: [
      {
        format: "es",
        entryFileNames: "[name].js",
        dir: "resources/dist",
        codeSplitting: false,
        minify: false,
        comments: true,
      },
    ],
    plugins: [
      replacePlugin({
        "process.env.NODE_ENV": JSON.stringify("development"),
      }),
    ],
  },
  /** The aggressively minified production build */
  {
    input: "resources/src",
    output: [
      {
        format: "es",
        entryFileNames: "[name].min.js",
        codeSplitting: false,
        dir: "resources/dist",
        minify: true,
        plugins: [terserPlugin(terserOptions)],
      },
    ],
  },
]);
