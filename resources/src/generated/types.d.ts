declare namespace Hirasso {
namespace HTMLObfuscator {
export type ScriptSettings = {
debug: boolean,
revealTrigger: Hirasso.HTMLObfuscator.Enum.RevealTrigger,
tagName: string,
renderPlaceholders: boolean,
};
namespace Enum {
export type Regex = '[^\\s@]+@[^\\s@]+\\.[^\\s@]{2,}' | '[\\+\\d][\\d \\-\\(\\)\\.]{6,20}(?<!\\s)';
export type RevealTrigger = 'load' | 'interaction' | 'manual';
}
}
}
