declare namespace Hirasso {
  namespace HTMLObfuscator {
    export type ScriptSettings = {
      tagName: string;
    };
    namespace Enum {
      export type Regex =
        | "[^\\s@]+@[^\\s@]+\\.[^\\s@]{2,}"
        | "[\\+\\d][\\d \\-\\(\\)\\.]{6,20}(?<!\\s)";
    }
  }
}
