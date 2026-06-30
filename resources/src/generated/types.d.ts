declare namespace Hirasso {
  namespace HTMLObfuscator {
    export type ScriptSettings = {
      debug: boolean;
      tagName: string;
    };
    namespace Enum {
      export type Regex =
        | "[^\\s@]+@[^\\s@]+\\.[^\\s@]{2,}"
        | "[\\+\\d][\\d \\-\\(\\)\\.]{6,20}(?<!\\s)";
    }
  }
}
