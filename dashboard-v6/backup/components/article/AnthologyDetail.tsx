import { useState, useEffect } from "react";
import { Space, Typography, message } from "antd";
import { get } from "../../request";
import type {
  IAnthologyDataResponse,
  IAnthologyResponse,
} from "../../api/Article";
import type { IAnthologyData } from "./AnthologyCard";
import StudioName from "../auth/Studio";
import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import AnthologyTocTree from "../anthology/AnthologyTocTree";
import { useIntl } from "react-intl";

const { Title, Text, Paragraph } = Typography;

interface Props {
  aid?: string;
  channels?: string[];
  visible?: boolean;
  onArticleClick?: (anthologyId: string, id: string, target: string) => void;
  onTitle?: (title: string) => void;
  onLoading?: (loading: boolean) => void;
  onError?: (error: unknown, message?: string) => void;
}

const AnthologyDetailWidget = ({
  aid,
  channels,
  visible = true,
  onArticleClick,
  onLoading,
  onTitle,
  onError,
}: Props) => {
  const [data, setData] = useState<IAnthologyData>();
  const intl = useIntl();

  useEffect(() => {
    if (!aid) return;

    let active = true;

    const fetchData = async () => {
      try {
        onLoading?.(true);

        const res = await get<IAnthologyResponse>(`/v2/anthology/${aid}`);

        if (!active) return;

        if (!res.ok) {
          message.error(res.message);
          onError?.(res.data, res.message);
          return;
        }

        const item: IAnthologyDataResponse = res.data;

        const parsed: IAnthologyData = {
          id: item.uid,
          title: item.title,
          subTitle: item.subtitle,
          summary: item.summary,
          articles: [],
          studio: item.studio,
          created_at: item.created_at,
          updated_at: item.updated_at,
        };

        setData(parsed);
        onTitle?.(item.title);
      } catch (err) {
        if (active) {
          console.error(err);
          onError?.(err);
        }
      } finally {
        if (active && onLoading) {
          onLoading(false);
        }
      }
    };

    fetchData();

    return () => {
      active = false;
    };
  }, [aid, onError, onLoading, onTitle]);

  if (!visible || !data) return null;

  return (
    <div style={{ padding: 12 }}>
      <Title level={4}>{data.title}</Title>

      <Text type="secondary">{data.subTitle}</Text>

      <Paragraph>
        <Space>
          <StudioName data={data.studio} />
          <TimeShow updatedAt={data.updated_at} />
        </Space>
      </Paragraph>

      <Paragraph>
        <Marked text={data.summary} />
      </Paragraph>

      <Title level={5}>
        {intl.formatMessage({ id: "labels.table-of-content" })}
      </Title>

      <AnthologyTocTree
        anthologyId={aid}
        channels={channels}
        onClick={(anthologyId, id, target) =>
          onArticleClick?.(anthologyId, id, target)
        }
      />
    </div>
  );
};

export default AnthologyDetailWidget;
