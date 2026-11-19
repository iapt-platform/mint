import { useEffect, useRef } from "react";
import { theme } from "antd";
import { useIntl } from "react-intl";
import { useNavigate, useParams } from "react-router";

import Dictionary from "../../components/dict/Dictionary";
import { useAppSelector } from "../../hooks";
import { lookupWord } from "../../reducers/command";

/**
 * 字典（独立页面）
 * 查的词放在路由里（/dictionary/:word），刷新、分享、前进后退都能重入。
 * 不在 workspace 布局里；主导航「工具 → 字典」弹窗的「在新标签页打开」跳到这里。
 */
const Widget = () => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const navigate = useNavigate();
  const { word } = useParams();

  // 回车时 AutoComplete 的 onSelect 与 Input.Search 的 onSearch 会接连触发同一个词，
  // 而此时 word 还是旧值，所以用 ref 记住刚跳过去的词，避免历史记录里压两条。
  const lastWord = useRef(word);
  useEffect(() => {
    lastWord.current = word;
  }, [word]);
  const search = (value?: string) => {
    if (!value || value === lastWord.current) return;
    lastWord.current = value;
    navigate(`/dictionary/${encodeURIComponent(value)}`);
  };

  // 结果里的巴利词点击查词走的是 redux 的 lookup，这里跟上它。
  // 只响应挂载之后的变化，避免进页面时被 redux 里残留的旧词带跑。
  const lookup = useAppSelector(lookupWord);
  const prevLookup = useRef(lookup);
  useEffect(() => {
    if (lookup === prevLookup.current) return;
    prevLookup.current = lookup;
    if (typeof lookup === "string") search(lookup);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [lookup]);

  return (
    <div style={{ minHeight: "100vh", background: token.colorBgContainer }}>
      {/* React 19 的 <title> 只接受单个字符串子节点 */}
      <title>{`${word ? `${word} - ` : ""}${intl.formatMessage({ id: "columns.library.dict.title" })}`}</title>
      <Dictionary word={word} onSearch={search} />
    </div>
  );
};

export default Widget;
