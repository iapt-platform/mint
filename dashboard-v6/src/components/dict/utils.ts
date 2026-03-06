import type {
  IDictRequest,
  IDictResponse,
  IPreferenceRequest,
  IPreferenceResponse,
  IUserDictCreate,
} from "../../api/dict";
import { post, put } from "../../request";

export const UserWbwPost = (data: IDictRequest[], view: string) => {
  let wordData: IDictRequest[] = data;
  data.forEach((value: IDictRequest) => {
    if (value.parent && value.type !== "") {
      if (!value.type?.includes("base") && value.type !== ".ind.") {
        let pFactors = "";
        let pFm;
        const orgFactors = value.factors?.split("+");
        if (
          orgFactors &&
          orgFactors.length > 0 &&
          orgFactors[orgFactors.length - 1].includes("[")
        ) {
          pFactors = orgFactors.slice(0, -1).join("+");
          pFm = value.factormean
            ?.split("+")
            .slice(0, orgFactors.length - 1)
            .join("+");
        }
        let grammar = value.grammar?.split("$").slice(0, 1).join("");
        if (value.type?.includes(".v")) {
          grammar = "";
        }
        wordData.push({
          word: value.parent,
          type: "." + value.type?.replaceAll(".", "") + ":base.",
          grammar: grammar,
          mean: value.mean,
          parent: value.parent2 ?? undefined,
          factors: pFactors,
          factormean: pFm,
          confidence: value.confidence,
          language: value.language,
          status: value.status,
        });
      }
    }

    if (value.factors && value.factors.split("+").length > 0) {
      const fm = value.factormean?.split("+");
      const factors: IDictRequest[] = [];
      value.factors.split("+").forEach((factor: string, index: number) => {
        const currWord = factor.replaceAll("-", "");
        console.debug("currWord", currWord);
        const meaning = fm ? (fm[index].replaceAll("-", "") ?? null) : null;
        if (meaning) {
          factors.push({
            word: currWord,
            type: ".part.",
            grammar: "",
            mean: meaning,
            confidence: value.confidence,
            language: value.language,
            status: value.status,
          });
        }

        const subFactorsMeaning: string[] = fm ? fm[index].split("-") : [];
        factor.split("-").forEach((subFactor, index1) => {
          if (subFactorsMeaning[index1] && subFactorsMeaning[index1] !== "") {
            factors.push({
              word: subFactor,
              type: ".part.",
              grammar: "",
              mean: subFactorsMeaning[index1],
              confidence: value.confidence,
              language: value.language,
              status: value.status,
            });
          }
        });
      });
      wordData = [...wordData, ...factors];
    }
  });
  return post<IUserDictCreate, IDictResponse>("/api/v2/userdict", {
    view: view,
    data: JSON.stringify(wordData),
  });
};

export const setValue = async (id: string, value: number) => {
  const url = `/api/v2/dict-preference/${id}`;
  const values: IPreferenceRequest = {
    confidence: value,
  };
  console.debug("api request", url, values);

  const result = await put<IPreferenceRequest, IPreferenceResponse>(
    url,
    values
  );
  return result;
};
