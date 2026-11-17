import { Breadcrumb, Button, Space } from "antd";
import { useIntl } from "react-intl";
import { EditOutlined, ShareAltOutlined } from "@ant-design/icons";
import type { ArticleMode } from "../../api/article";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import store from "../../store";
import { click, clickedTerm } from "../../reducers/term-click";
import { currentUser } from "../../reducers/current-user";

import "./article.css";
import ArticleHeader from "./components/ArticleHeader";
import ArticleLayout from "./components/ArticleLayout";
import { useTerm } from "./hooks/useTerm";
import TermShare from "../term/TermShare";
import { QualityCtl } from "../template/Quality";
import MarkupView from "../markup/MarkupView";
import type { MarkupNode } from "../markup/types";
import type { IUser } from "../../api/Auth";

interface IWidget {
  id?: string;
  mode?: ArticleMode | null;
  channelId?: string | null;
  headerExtra?: React.ReactNode;
  onEdit?: () => void;
  /** 点了正文里的某个术语（TermCtl 经 term-click 广播） */
  onTermClick?: (id: string) => void;
}

const TypeTermWidget = ({
  channelId,
  id,
  mode = "read",
  headerExtra,
  onEdit,
  onTermClick,
}: IWidget) => {
  const { term, errorCode, loading } = useTerm({
    id,
    channelId,
    mode,
  });
  const currUser = useAppSelector(currentUser);
  const termClicked = useAppSelector(clickedTerm);
  // 进页面前别处留下、没人接收的点击不算，只响应之后的
  const [staleClick] = useState(termClicked);
  const intl = useIntl();

  useEffect(() => {
    if (!onTermClick || !termClicked?.id || termClicked === staleClick) {
      return;
    }
    store.dispatch(click(null));
    onTermClick(termClicked.id);
  }, [termClicked, staleClick, onTermClick]);

  const path = [
    { title: currUser?.nickName },
    { title: term?.channel?.name ?? "通用" },
    { title: term?.word },
  ];
  return (
    <div>
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
          <Space>
            <Button type="primary" icon={<EditOutlined />} onClick={onEdit}>
              {intl.formatMessage({ id: "buttons.edit" })}
            </Button>
            {id ? (
              <TermShare
                id={id}
                trigger={<Button icon={<ShareAltOutlined />} />}
              />
            ) : undefined}
          </Space>
        }
      />
      <ArticleLayout
        title={term?.meaning}
        subTitle={term?.word}
        preTitle={
          term ? (
            <Space>
              {/* 质量为空按 pending 显示 */}
              <QualityCtl
                value={term.quality ?? "pending"}
                label={intl.formatMessage({
                  id: `term.fields.quality.${term.quality ?? "pending"}`,
                })}
              />
            </Space>
          ) : undefined
        }
        html={[]}
        nodes={[
          <MarkupView
            key="body"
            className="pcd_article"
            nodes={term?.body as MarkupNode[] | undefined}
          />,
        ]}
        editor={(term?.editor ?? undefined) as IUser | undefined}
        created_at={term?.created_at}
        updated_at={term?.updated_at}
        loading={loading}
        errorCode={errorCode}
      />
    </div>
  );
};

export default TypeTermWidget;
