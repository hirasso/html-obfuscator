import { describe, it, expect } from "vitest";
import { decode } from "../../resources/src/decode.js";
import fixtures from "./fixtures/decode.json";

describe("decode", () => {
  for (const { strategy, plain, encoded } of fixtures) {
    it(`${strategy}: "${plain}"`, () => {
      expect(decode(encoded)).toBe(plain);
    });
  }
});
