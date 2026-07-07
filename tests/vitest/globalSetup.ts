import { execSync } from "child_process";

export function setup() {
  execSync("composer generate-fixtures", { stdio: "inherit" });
}
