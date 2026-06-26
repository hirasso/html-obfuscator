declare namespace Hirasso {
  namespace HTMLObfuscator {
    namespace Enum {
      export type RevealStrategy = "onload" | "oninteraction" | "manually";
    }
    namespace ScriptSettings {
      export type ScriptSettings = {
        debug: boolean;
        revealStrategy: Hirasso.HTMLObfuscator.Enum.RevealStrategy;
        tagName: string;
        renderPlaceholders: boolean;
      };
    }
  }
}
