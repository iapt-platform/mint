import { useNavigate, useParams, useSearchParams } from "react-router";
import TypeArticle from "../../../components/article/TypeArticle";
import SplitLayout, {
  type RightToolbarTab,
} from "../../../components/general/SplitLayout";
import type { ArticleMode } from "../../../api/Article";
import AnthologyTocTree from "../../../components/anthology/AnthologyTocTree";

import {
  BugOutlined,
  SearchOutlined,
  CommentOutlined,
} from "@ant-design/icons";
import DictComponent from "../../../components/dict/DictComponent";

const Widget = () => {
  const { articleId, anthologyId } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");
  const anthology = searchParams.get("anthology");

  // ─────────────────────────────────────────────
  // 右边栏 tabs 配置
  // ─────────────────────────────────────────────

  const rightTabs: RightToolbarTab[] = [
    {
      key: "dict",
      icon: <SearchOutlined />,
      label: "字典",
      content: (
        <div className="dict_component">
          <DictComponent />
        </div>
      ),
    },
    {
      key: "search",
      icon: <CommentOutlined />,
      label: "搜索",
      content: (
        <div style={{ padding: 16 }}>
          <div style={{ marginTop: 12 }}></div>
        </div>
      ),
    },
    {
      key: "debug",
      icon: <BugOutlined />,
      label: "调试",
      content: (
        <div style={{ padding: 16 }}>
          <pre
            style={{
              marginTop: 8,
              fontSize: 12,
              background: "var(--ant-color-fill-quaternary, #f5f5f5)",
              borderRadius: 6,
              padding: 12,
              overflow: "auto",
            }}
          >
            {JSON.stringify(
              { env: "production", workers: 3, status: "running" },
              null,
              2
            )}
          </pre>
        </div>
      ),
    },
  ];

  return (
    <SplitLayout
      key="mode-a"
      sidebarTitle="table of content"
      sidebar={
        anthologyId ? (
          <AnthologyTocTree
            anthologyId={anthologyId}
            channels={channelId ? channelId.split("_") : undefined}
            onClick={(anthology, article, target) => {
              if (target && target === "_blank") {
                window.open(
                  `${window.location.origin}${import.meta.env.BASE_URL}workspace/anthology/${anthology}/${article}`,
                  "_blank"
                );
              } else {
                navigate(`/workspace/anthology/${anthology}/${article}`);
              }
            }}
          />
        ) : (
          <></>
        )
      }
      rightTabs={rightTabs}
    >
      {({ expandButton }) => (
        <TypeArticle
          articleId={articleId}
          mode={mode as ArticleMode}
          anthologyId={anthologyId ?? anthology}
          channelId={channelId}
          headerExtra={expandButton}
          onAnthologySelect={(id) => {
            navigate(`/workspace/anthology/${id}/${articleId}`);
          }}
          onArticleChange={(type, id) => {
            if (anthologyId) {
              if (type === "article") {
                navigate(`/workspace/anthology/${anthologyId}/${id}`);
              } else {
                navigate(`/workspace/${type}/${id}`);
              }
            } else {
              navigate(`/workspace/${type}/${id}`);
            }
          }}
        />
      )}
    </SplitLayout>
  );
};

export default Widget;
