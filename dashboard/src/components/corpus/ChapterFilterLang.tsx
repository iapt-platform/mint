import { Select } from "antd";
import { DefaultOptionType } from "antd/lib/select";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { IChapterLangListResponse } from "../api/Corpus";

interface IWidget {
  onSelect?: Function;
}
const ChpterFilterLangWidget = ({ onSelect }: IWidget) => {
  const [lang, setLang] = useState<DefaultOptionType[]>([]);
  useEffect(() => {
    get<IChapterLangListResponse>(`/v2/progress?view=lang`).then((json) => {
      if (json.ok) {
        const langs = json.data.rows
          .filter((value) => value.lang !== "")
          .map((item) => {
            return {
              value: item.lang,
              label: `${item.lang}-${item.count}`,
            };
          });
        setLang(langs);
      }
    });
  }, []);
  return (
    <Select
      style={{ minWidth: 100 }}
      placeholder="Language"
      defaultValue={["zh"]}
      onChange={(value: string[]) => {
        console.log(`selected ${value}`);
        if (typeof onSelect !== "undefined") {
          onSelect(value);
        }
      }}
      options={lang}
    />
  );
};

export default ChpterFilterLangWidget;
