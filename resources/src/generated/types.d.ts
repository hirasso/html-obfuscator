declare namespace Hirasso {
  namespace HTMLObfuscator {
    export type ScriptSettings = {
      debug: boolean;
      revealStrategy: Hirasso.HTMLObfuscator.Enum.RevealStrategy;
      tagName: string;
      renderPlaceholders: boolean;
    };
    namespace Enum {
      export type RevealStrategy = "onload" | "oninteraction" | "manually";
    }
  }
}
