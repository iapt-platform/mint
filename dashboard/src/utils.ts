export function ApiFetch(
  url: string,
  method = "GET",
  data?: any
): Promise<Response> {
  const apiHost = process.env.REACT_APP_API_HOST;
  interface ajaxParam {
    method: string;
    body?: string;
    headers?: any;
  }
  let param: ajaxParam = {
    method: method,
  };
  if (typeof data !== "undefined") {
    param.body = JSON.stringify(data);
  }
  if (localStorage.getItem("token")) {
    param.headers = { token: localStorage.getItem("token") };
  }
  return new Promise((resolve, reject) => {
    let apiUrl = apiHost + url;
    console.log("api", apiUrl);
    fetch(apiUrl, param)
      .then((response) => response.json())
      .then((response) => {
        resolve(response);
      })
      .catch((error) => {
        reject(error);
      });
  });
}

export function ApiGetText(url: string): Promise<String> {
  const apiHost = process.env.REACT_APP_API_HOST
    ? process.env.REACT_APP_API_HOST
    : "http://localhost/api";
  return new Promise((resolve, reject) => {
    let apiUrl = apiHost + url;
    console.log("api", apiUrl);
    fetch(apiUrl)
      .then((response) => response.text())
      .then((response) => {
        resolve(response);
      })
      .catch((error) => {
        reject(error);
      });
  });
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
