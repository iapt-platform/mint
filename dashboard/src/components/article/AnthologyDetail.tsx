import { useState, useEffect } from "react";
import { Typography } from "antd";
import MDEditor from "@uiw/react-md-editor";

import { get } from "../../request";
import type {
  IAnthologyDataResponse,
  IAnthologyResponse,
} from "../api/Article";
import type { IAnthologyData } from "./AnthologyCard";
import TocTree from "./TocTree";

const { Title, Text } = Typography;

const defaultData: IAnthologyData = {
  id: "",
  title: "",
  subTitle: "",
  summary: "",
  articles: [],
  studio: {
    id: "",
    studioName: "",
    nickName: "",
    avatar: "",
  },
  created_at: "",
  updated_at: "",
};
interface IWidgetAnthologyDetail {
  aid?: string;
  channels?: string[];
  onArticleSelect?: Function;
}
const Widget = ({ aid, channels, onArticleSelect }: IWidgetAnthologyDetail) => {
  const [tableData, setTableData] = useState(defaultData);

  useEffect(() => {
    console.log("useEffect");
    fetchData(aid);
  }, [aid]);

  function fetchData(id?: string) {
    get<IAnthologyResponse>(`/v2/anthology/${id}`)
      .then((response) => {
        const item: IAnthologyDataResponse = response.data;
        let newTree: IAnthologyData = {
          id: item.uid,
          title: item.title,
          subTitle: item.subtitle,
          summary: item.summary,
          articles: item.article_list.map((al) => {
            return {
              key: al.article,
              title: al.title,
              level: parseInt(al.level),
            };
          }),
          studio: item.studio,
          created_at: item.created_at,
          updated_at: item.updated_at,
        };
        setTableData(newTree);
        console.log("toc", newTree.articles);
      })
      .catch((error) => {
        console.error(error);
      });
  }
  return (
    <>
      <Title level={4}>{tableData.title}</Title>
      <div>
        <Text type="secondary">{tableData.subTitle}</Text>
      </div>
      <div>
        <MDEditor.Markdown source={tableData.summary} />
      </div>
      <Title level={5}>目录</Title>

      <TocTree
        treeData={tableData.articles}
        onSelect={(keys: string[]) => {
          if (typeof onArticleSelect !== "undefined") {
            onArticleSelect(keys);
          }
        }}
      />
    </>
  );
};

export default Widget;
