import { useIntl } from "react-intl";

import GrammarBook from "../../../components/term/GrammarBook";

/**
 * 语法手册（独立页面）
 * 与编辑器右边栏的语法手册面板是同一个组件。
 */
const Widget = () => {
  const intl = useIntl();
  return (
    <div style={{ maxWidth: 960, margin: "0 auto", padding: 16 }}>
      <title>
        {intl.formatMessage({ id: "columns.library.palihandbook.title" })}
      </title>
      <GrammarBook />
    </div>
  );
};

export default Widget;
