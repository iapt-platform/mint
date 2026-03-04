import { Divider, Typography } from "antd";

import AnthologyTocTree from "../anthology/AnthologyTocTree";
import { useIntl } from "react-intl";
import ArticleSkeleton from "../article/components/ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import AnthologyInfo from "./components/AnthologyInfo";
import { useAnthology } from "./hooks/useAnthology";

const { Title } = Typography;

interface Props {
  id?: string;
  channels?: string[];
  onArticleClick?: (anthologyId: string, id: string, target: string) => void;
}

const AnthologyDetailWidget = ({ id, channels, onArticleClick }: Props) => {
  const { data, loading, errorCode } = useAnthology(id);
  const intl = useIntl();

  return (
    <div style={{ padding: 12 }}>
      {loading && <ArticleSkeleton />}
      {!loading && errorCode && <ErrorResult code={errorCode} />}
      {!loading && !errorCode && (
        <>
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

export default AnthologyDetailWidget;
