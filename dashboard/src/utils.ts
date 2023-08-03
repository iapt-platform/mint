export function fullUrl(url: string): string {
  return process.env.REACT_APP_WEB_HOST + process.env.PUBLIC_URL + url;
}

export function PaliToEn(pali: string): string {
  let output: string = pali.toLowerCase();
  output = output.replaceAll(" ", "_");
  output = output.replaceAll("-", "_");
  output = output.replaceAll("ā", "a");
  output = output.replaceAll("ī", "i");
  output = output.replaceAll("ū", "u");
  output = output.replaceAll("ḍ", "d");
  output = output.replaceAll("ṭ", "t");
  output = output.replaceAll("ḷ", "l");
  return output;
}

export function PaliReal(inStr: string | undefined | null): string {
  if (typeof inStr !== "string") {
    return "";
  }
  const paliLetter = "abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
  let output: string = "";
  inStr = inStr.toLowerCase();
  inStr = inStr.replace(/ṁ/g, "ṃ");
  inStr = inStr.replace(/ŋ/g, "ṃ");
  for (const iterator of inStr) {
    if (paliLetter.includes(iterator)) {
      output += iterator;
    }
  }
  return output;
}
