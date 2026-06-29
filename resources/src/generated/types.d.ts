declare namespace Hirasso {
  namespace HTMLObfuscator {
    export type ScriptSettings = {
      debug: boolean;
      revealStrategy: Hirasso.HTMLObfuscator.Enum.RevealStrategy;
      tagName: string;
      renderPlaceholders: boolean;
    };
    namespace Enum {
      export type Regex =
        | "[^\\s@]+@[^\\s@]+\\.[^\\s@]{2,}"
        | "[\\+\\d][\\d \\-\\(\\)\\.]{6,20}(?<!\\s)";
      export type RevealStrategy = "onload" | "oninteraction" | "none";
    }
  }
}
