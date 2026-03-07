import { Breadcrumb, Button, Space } from "antd";
import type { ArticleMode } from "../../api/Article";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

import "./article.css";
import ArticleHeader from "./components/ArticleHeader";
import ArticleLayout from "./components/ArticleLayout";
import { useTerm } from "./hooks/useTerm";

interface IWidget {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  headerExtra?: React.ReactNode;
  onEdit?: () => void;
}

const TypeTermWidget = ({
  channelId,
  id,
  mode = "read",
  headerExtra,
  onEdit,
}: IWidget) => {
  const { articleData, term, errorCode, loading } = useTerm({
    id,
    channelId,
    mode,
  });
  const currUser = useAppSelector(currentUser);

  const path = [
    { title: currUser?.nickName },
    { title: term?.channel?.name ?? "通用" },
    { title: term?.word },
  ];
  return (
    <div>
      <title>{articleData?.title}-百科</title>
      <ArticleHeader
        header={
          <Space>
            {headerExtra}
            <Breadcrumb
              items={path}
              style={{
                whiteSpace: "nowrap",
                width: "100%",
              }}
            />
          </Space>
        }
        action={
          <Button type="primary" onClick={onEdit}>
            Edit
          </Button>
        }
      />
      <ArticleLayout
        title={articleData?.title}
        subTitle={articleData?.subtitle}
        content={articleData?.content ?? ""}
        html={[articleData?.html ?? ""]}
        editor={articleData?.editor}
        created_at={articleData?.created_at}
        updated_at={articleData?.updated_at}
        loading={loading}
        errorCode={errorCode}
      />
    </div>
  );
};

export default TypeTermWidget;
