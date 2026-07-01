import { defineConfig, Plugin } from "rolldown";
import { minify } from "terser";
import { replacePlugin } from "rolldown/plugins";

export default defineConfig([
  /** The unminified development build */
  {
    input: "resources/src",
    output: [
      {
        format: "es",
        file: "resources/dist/index.js",
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
        file: "resources/dist/index.min.js",
        codeSplitting: false,
        /** would run after terser, undoing it's changes */
        minify: false,
        comments: false,
        plugins: [terserPlugin()],
      },
    ],
    plugins: [
      replacePlugin({
        "process.env.NODE_ENV": JSON.stringify("production"),
      }),
    ],
  },
]);

function terserPlugin(): Plugin {
  return {
    name: "terser",
    async renderChunk(code: string) {
      const result = await minify(code, {
        mangle: {
          toplevel: true,
        },
        compress: {
          passes: 2,
          unsafe: true,
          unsafe_arrows: true,
          unsafe_comps: true,
          unsafe_methods: true,
          drop_console: true,
        },
        output: {
          max_line_len: 150,
        },
      });
      return { code: result.code!, map: null };
    },
  };
}
