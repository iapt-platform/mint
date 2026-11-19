import { useCallback, useState } from "react";
import { theme } from "antd";
import { useIntl } from "react-intl";
import { useSearchParams } from "react-router";

import GrammarBook from "../../components/term/GrammarBook";
import {
  grammarBookSearch,
  parseGrammarBookSearch,
  type IGrammarBookState,
} from "../../components/term/grammarBookState";

/**
 * 语法手册（独立页面）
 * 与编辑器右边栏的语法手册面板是同一个组件。
 * 不在 workspace 布局里；主导航「工具 → 语法手册」弹窗的「在新标签页打开」跳到这里。
 * 检索词与选中的术语在地址栏里（?q=&id=&word=），刷新、分享都能重入。
 */
const Widget = () => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const [searchParams, setSearchParams] = useSearchParams();
  // 只在挂载时读一次；之后由组件回报、写回地址栏
  const [defaultState] = useState(() => parseGrammarBookSearch(searchParams));
  const onStateChange = useCallback(
    (state: IGrammarBookState) =>
      setSearchParams(grammarBookSearch(state), { replace: true }),
    [setSearchParams],
  );

  return (
    <div style={{ minHeight: "100vh", background: token.colorBgContainer }}>
      <div style={{ maxWidth: 960, margin: "0 auto", padding: 16 }}>
        <title>
          {intl.formatMessage({ id: "columns.library.palihandbook.title" })}
        </title>
        <GrammarBook
          defaultState={defaultState}
          onStateChange={onStateChange}
        />
      </div>
    </div>
  );
};

export default Widget;
