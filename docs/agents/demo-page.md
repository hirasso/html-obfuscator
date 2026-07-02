# Demo Page Spec

## Location
`demo/index.php` + `demo/style.css`

## Stack
- PHP using `obfuscate()` helper
- Pico.css from CDN (classless, prose-friendly)
- No JS framework

## Page structure

### Header
- `<h1>HTML Obfuscator</h1>`
- Link to `https://github.com/hirasso/html-obfuscator`
- TL;DR: "Require work from crawlers before revealing contact information"

### Contact block
- Mailto link: `mail@rassohilber.com` (textContent = email address)
- Tel link: `+49 176 200 20 805` (textContent = phone number)
- Both obfuscated via PHP, within prose text (not as a list).
- Hint: "Move your mouse or press any key to reveal"

### Details: What can non-JS crawlers see?
- `<details>` with raw obfuscated HTML in `<pre><code>`

### Details: What can JS crawlers see before interaction?
- `<details>` with prose only
- Text is invisible to the bot (decoded into a closed shadow root on connectedCallback)
- Links are non-functional (href is empty until user interaction)
- Reveal triggers: `pointermove`, `pointerdown`, `keydown`

### Description
- Prose explaining obfuscation is surprisingly effective
- Link to `https://spencermortensen.com/articles/email-obfuscation/`

### How it works
- Brief prose
- PHP snippet showing `obfuscate($html)`
- Resulting `<x-obfuscated>` HTML output

## Implementation notes
- Pre-generate raw obfuscated contact block with `->injectClientScript(false)` for the details display
- Reset `HTMLObfuscator::$hasInjectedFrontendScript = false` before the full-page obfuscation call