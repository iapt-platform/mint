import { useState, useEffect } from "react";
import { Space, Typography } from "antd";
import MDEditor from "@uiw/react-md-editor";

import { get } from "../../request";
import type {
  IAnthologyDataResponse,
  IAnthologyResponse,
} from "../api/Article";
import type { IAnthologyData } from "./AnthologyCard";
import TocTree from "./TocTree";
import { useNavigate } from "react-router-dom";
import StudioName from "../auth/StudioName";
import TimeShow from "../general/TimeShow";

const { Title, Text } = Typography;

interface IWidgetAnthologyDetail {
  aid?: string;
  channels?: string[];
  onArticleSelect?: Function;
}
const Widget = ({ aid, channels, onArticleSelect }: IWidgetAnthologyDetail) => {
  const [tableData, setTableData] = useState<IAnthologyData>();
  const navigate = useNavigate();

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
      <Title level={4}>{tableData?.title}</Title>
      <div>
        <Text type="secondary">{tableData?.subTitle}</Text>
      </div>
      <Space>
        <StudioName data={tableData?.studio} />
        <TimeShow
          time={tableData?.updated_at}
          title="updated"
          showTitle={true}
        />
      </Space>
      <div>
        <MDEditor.Markdown source={tableData?.summary} />
      </div>
      <Title level={5}>目录</Title>

      <TocTree
        treeData={tableData?.articles}
        onSelect={(keys: string[]) => {
          if (typeof onArticleSelect !== "undefined") {
            onArticleSelect(keys);
          } else {
            navigate(`/article/article/${keys[0]}/read`);
          }
        }}
      />
    </>
  );
};

export default Widget;
