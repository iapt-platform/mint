import { message } from "antd";
import { useState, useEffect } from "react";

import { get } from "../../request";
import { IApiChapterTag, IApiResponseChapterTagList } from "../api/Corpus";

import ChapterTag, { ITagData } from "./ChapterTag";

interface IWidget {
  max?: number;
  onTagClick?: Function;
}
const Widget = ({ max, onTagClick }: IWidget) => {
  const [tableData, setTableData] = useState<ITagData[]>([]);

  useEffect(() => {
    console.log("useEffect");
    fetchData();
  }, []);

  function fetchData() {
    get(`/v2/progress?view=chapter-tag`)
      .then((response) => {
        const json = response as unknown as IApiResponseChapterTagList;
        const tags: IApiChapterTag[] = json.data.rows;
        let newTags: ITagData[] = tags.map((item) => {
          return {
            key: item.name,
            title: item.name,
            count: item.count,
          };
        });
        setTableData(newTags);
      })
      .catch((error) => {
        message.error(error);
      });
  }
  let iTag = max ? max : tableData.length;
  if (iTag > tableData.length) {
    iTag = tableData.length;
  }
  return (
    <>
      {tableData.map((item, id) => {
        return (
          <ChapterTag
            data={item}
            key={id}
            onTagClick={(key: string) => {
              if (typeof onTagClick !== "undefined") {
                onTagClick(key);
              }
            }}
          />
        );
      })}
    </>
  );
};

export default Widget;
