import { useState, useEffect } from "react";

import type { IDictContentData, IApiDictContentData } from "./DictContent";
import { get } from "../../request";

import DictContent from "./DictContent";

interface IWidget {
  word: string | undefined;
  compact?: boolean;
}

const Widget = ({ word, compact = false }: IWidget) => {
  const defaultData: IDictContentData = {
    dictlist: [],
    words: [],
    caselist: [],
  };
  const [tableData, setTableData] = useState<IDictContentData>(defaultData);

  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    const url = `/v2/dict?word=${word}`;
    get<IApiDictContentData>(url)
      .then((json) => {
        setTableData(json.data);
      })
      .catch((error) => {
        console.error(error);
      });
  }, [word, setTableData]);

  return (
    <>
      <DictContent word={word} data={tableData} compact={compact} />
    </>
  );
};

export default Widget;
