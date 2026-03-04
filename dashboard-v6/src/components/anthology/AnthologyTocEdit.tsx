import { Divider, Typography } from "antd";

import { useIntl } from "react-intl";
import ArticleSkeleton from "../article/components/ArticleSkeleton";
import ErrorResult from "../general/ErrorResult";
import AnthologyInfo from "./components/AnthologyInfo";
import { useAnthology } from "./hooks/useAnthology";
import EditableTocTree from "./EditableTocTree";

const { Title } = Typography;

interface Props {
  id?: string;
  channels?: string[];
  editorStudioName?: string;
  onArticleClick?: (anthologyId: string, id: string, target: string) => void;
}

const AnthologyTocEdit = ({ id, editorStudioName }: Props) => {
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

          <EditableTocTree
            studioName={data?.studio.realName}
            editorStudioName={editorStudioName}
            anthologyId={id}
            anthology={data}
          />
        </>
      )}
    </div>
  );
};

export default AnthologyTocEdit;
