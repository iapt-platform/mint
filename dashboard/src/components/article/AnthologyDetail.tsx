import { useNavigate } from "react-router-dom";
import { useState, useEffect } from "react";
import { Space, Typography } from "antd";

import { get } from "../../request";
import type {
  IAnthologyDataResponse,
  IAnthologyResponse,
} from "../api/Article";
import type { IAnthologyData } from "./AnthologyCard";
import StudioName from "../auth/StudioName";
import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import AnthologyTocTree from "../anthology/AnthologyTocTree";

const { Title, Text, Paragraph } = Typography;

interface IWidgetAnthologyDetail {
  aid?: string;
  channels?: string[];
  onArticleSelect?: Function;
}
const AnthologyDetailWidget = ({
  aid,
  channels,
  onArticleSelect,
}: IWidgetAnthologyDetail) => {
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
    <div style={{ padding: 12 }}>
      <Title level={4}>{tableData?.title}</Title>
      <div>
        <Text type="secondary">{tableData?.subTitle}</Text>
      </div>
      <Paragraph>
        <Space>
          <StudioName data={tableData?.studio} />
          <TimeShow time={tableData?.updated_at} title="updated" />
        </Space>
      </Paragraph>
      <Paragraph>
        <Marked text={tableData?.summary} />
      </Paragraph>
      <Title level={5}>目录</Title>

      <AnthologyTocTree
        anthologyId={aid}
        onSelect={(keys: string[]) => {
          if (typeof onArticleSelect !== "undefined") {
            onArticleSelect(keys);
          } else {
            navigate(`/article/article/${keys[0]}?mode=read`);
          }
        }}
      />
    </div>
  );
};

export default AnthologyDetailWidget;
