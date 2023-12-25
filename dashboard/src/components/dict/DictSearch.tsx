import { useState, useEffect } from "react";

import type { IDictContentData, IApiDictContentData } from "./DictContent";
import { get } from "../../request";

import DictContent from "./DictContent";
import { Skeleton } from "antd";

interface IWidget {
  word: string | undefined;
  compact?: boolean;
}

const DictSearchWidget = ({ word, compact = false }: IWidget) => {
  const [loading, setLoading] = useState(false);

  const defaultData: IDictContentData = {
    dictlist: [],
    words: [],
    caselist: [],
  };
  const [tableData, setTableData] = useState<IDictContentData>(defaultData);

  const fetchDict = (word: string) => {
    const url = `/v2/dict?word=${word}`;
    console.info("url", url);
    setLoading(true);
    get<IApiDictContentData>(url)
      .then((json) => {
        setTableData(json.data);
      })
      .finally(() => setLoading(false))
      .catch((error) => {
        console.error(error);
      });
  };
  useEffect(() => {
    if (typeof word === "undefined") {
      return;
    }
    fetchDict(word);
  }, [word, setTableData]);

  return (
    <>
      {loading ? (
        <div>
          <div>searching {word}</div>
          <Skeleton active />
        </div>
      ) : (
        <DictContent word={word} data={tableData} compact={compact} />
      )}
    </>
  );
};

export default DictSearchWidget;
