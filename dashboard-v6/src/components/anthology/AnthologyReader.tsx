import { Button, Divider, Space, Typography } from "antd";
import { EditOutlined, ReloadOutlined } from "@ant-design/icons";

import AnthologyTocTree from "./AnthologyTocTree";
import { useIntl } from "react-intl";
import ArticleSkeleton from "../article/components/ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import AnthologyInfo from "./components/AnthologyInfo";
import { useAnthology } from "./hooks/useAnthology";
import ArticleHeader from "../article/components/ArticleHeader";
import type { TTarget } from "../../types";

const { Title } = Typography;

interface Props {
  id?: string;
  channels?: string[];
  onArticleClick?: (anthologyId: string, id: string, target?: TTarget) => void;
  onEdit?: () => void;
}

const AnthologyReader = ({ id, channels, onArticleClick, onEdit }: Props) => {
  const { data, loading, errorCode, refresh } = useAnthology(id);
  const intl = useIntl();

  return (
    <div style={{ padding: 12 }}>
      {loading && <ArticleSkeleton />}
      {!loading && errorCode && <ErrorResult code={errorCode} />}
      {!loading && !errorCode && (
        <>
          <ArticleHeader
            action={
              <Space>
                <Button type="primary" icon={<EditOutlined />} onClick={onEdit}>
                  edit
                </Button>
                <Button
                  type="link"
                  icon={<ReloadOutlined />}
                  onClick={refresh}
                />
              </Space>
            }
          />
          <AnthologyInfo data={data} />
          <Divider />
          <Title level={5}>
            {intl.formatMessage({ id: "labels.table-of-content" })}
          </Title>

          <AnthologyTocTree
            anthologyId={id}
            channels={channels}
            onClick={(anthologyId, id, target) =>
              onArticleClick?.(anthologyId, id, target)
            }
          />
        </>
      )}
    </div>
  );
};

export default AnthologyReader;
