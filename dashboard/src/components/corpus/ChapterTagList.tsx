import { message, Tag } from "antd";
import { useState, useEffect } from "react";

import { get } from "../../request";
import { IApiChapterTag, IApiResponseChapterTagList } from "../api/Corpus";

export interface ITagData {
  title: string;
  key: string;
  color?: string;
  description?: string;
}
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
            title: `${item.name}(${item.count})`,
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
          <Tag
            key={id}
            style={{ cursor: "pointer" }}
            onClick={() => {
              if (typeof onTagClick !== "undefined") {
                onTagClick(item.key);
              }
            }}
          >
            {item.title}
          </Tag>
        );
      })}
    </>
  );
};

export default Widget;
