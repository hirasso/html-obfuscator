// @ts-ignore will be replaced by rolldown at build time
export const debug = process.env.NODE_ENV === "development";

export const tagName = document.currentScript?.getAttribute("data-tagname") ?? "";
export const key = document.currentScript?.getAttribute("data-key") ?? "";