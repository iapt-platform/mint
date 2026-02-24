export type MapTable = Record<string, string>;

export function buildConverter(map: MapTable) {
  const keys = Object.keys(map).sort((a, b) => b.length - a.length);

  return (input: string) => {
    if (!input) return "";

    input = input.normalize("NFC").toLowerCase();

    let out = "";
    let i = 0;

    while (i < input.length) {
      let matched = false;

      for (const k of keys) {
        if (input.startsWith(k, i)) {
          out += map[k];
          i += k.length;
          matched = true;
          break;
        }
      }

      if (!matched) {
        out += input[i++];
      }
    }

    return out;
  };
}
