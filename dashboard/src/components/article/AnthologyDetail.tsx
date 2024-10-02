import { useState, useEffect } from "react";
import { Space, Typography, message } from "antd";

import { get } from "../../request";
import type {
  IAnthologyDataResponse,
  IAnthologyResponse,
} from "../api/Article";
import type { IAnthologyData } from "./AnthologyCard";
import StudioName from "../auth/Studio";
import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import AnthologyTocTree from "../anthology/AnthologyTocTree";
import { useIntl } from "react-intl";

const { Title, Text, Paragraph } = Typography;

interface IWidgetAnthologyDetail {
  aid?: string;
  channels?: string[];
  visible?: boolean;
  onArticleSelect?: Function;
  onArticleClick?: Function;
  onLoad?: Function;
  onTitle?: Function;
  onLoading?: Function;
  onError?: Function;
}
const AnthologyDetailWidget = ({
  aid,
  channels,
  visible = true,
  onArticleSelect,
  onArticleClick,
  onLoading,
  onTitle,
  onError,
}: IWidgetAnthologyDetail) => {
  const [tableData, setTableData] = useState<IAnthologyData>();
  const intl = useIntl();

  useEffect(() => {
    fetchData(aid);
  }, [aid]);

  function fetchData(id?: string) {
    const url = `/v2/anthology/${id}`;
    console.info("api request", url);
    if (typeof onLoading !== "undefined") {
      onLoading(true);
    }
    get<IAnthologyResponse>(url)
      .then((response) => {
        console.info("api response", response);
        if (response.ok) {
          const item: IAnthologyDataResponse = response.data;
          let newTree: IAnthologyData = {
            id: item.uid,
            title: item.title,
            subTitle: item.subtitle,
            summary: item.summary,
            articles: [],
            studio: item.studio,
            created_at: item.created_at,
            updated_at: item.updated_at,
          };
          setTableData(newTree);
          if (typeof onTitle !== "undefined") {
            onTitle(item.title);
          }
          console.log("toc", newTree.articles);
        } else {
          if (typeof onError !== "undefined") {
            onError(response.data, response.message);
          }
          message.error(response.message);
        }
      })
      .finally(() => {
        if (typeof onLoading !== "undefined") {
          onLoading(false);
        }
      })
      .catch((error) => {
        console.error(error);
        if (typeof onError !== "undefined") {
          onError(error, "");
        }
      });
  }
  return (
    <div style={{ padding: 12, visibility: visible ? "visible" : "hidden" }}>
      <Title level={4}>{tableData?.title}</Title>
      <div>
        <Text type="secondary">{tableData?.subTitle}</Text>
      </div>
      <Paragraph>
        <Space>
          <StudioName data={tableData?.studio} />
          <TimeShow updatedAt={tableData?.updated_at} />
        </Space>
      </Paragraph>
      <Paragraph>
        <Marked text={tableData?.summary} />
      </Paragraph>
      <Title level={5}>
        {intl.formatMessage({
          id: "labels.table-of-content",
        })}
      </Title>
      <AnthologyTocTree
        anthologyId={aid}
        channels={channels}
        onClick={(anthologyId: string, id: string, target: string) => {
          if (typeof onArticleClick !== "undefined") {
            onArticleClick(anthologyId, id, target);
          }
        }}
      />
    </div>
  );
};

export default AnthologyDetailWidget;
