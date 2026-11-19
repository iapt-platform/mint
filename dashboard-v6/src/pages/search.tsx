import { theme } from "antd";
import { useIntl } from "react-intl";
import { useSearchParams } from "react-router";
import SearchPanel from "../features/search/SearchPanel";
import {
  parseSearchParams,
  toSearchParams,
  type ISearchState,
} from "../features/search/state";

/**
 * /search：整页的搜索（不在 workspace 布局里）。条件全部在地址栏里（q、tab、mode、category…），
 * 可以收藏、分享，也是 workspace 搜索弹窗「在整页打开」的去处。
 */
const Widget = () => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const [searchParams, setSearchParams] = useSearchParams();
  const state = parseSearchParams(searchParams);

  const onChange = (patch: Partial<ISearchState>) =>
    setSearchParams(toSearchParams({ ...state, ...patch }), {
      replace: !patch.q,
    });

  // 标签页标题：「检索词 - 标签页 - 搜索」，没有检索词时只写「搜索」
  const name = intl.formatMessage({ id: "search.title" });
  const title = state.q
    ? `${state.q} - ${intl.formatMessage({ id: `search.tab.${state.tab}` })} - ${name}`
    : name;

  return (
    <div
      style={{
        height: "100vh",
        boxSizing: "border-box",
        padding: 12,
        background: token.colorBgLayout,
      }}
    >
      <title>{title}</title>
      <div
        style={{
          height: "100%",
          maxWidth: 1400,
          margin: "0 auto",
          background: token.colorBgContainer,
          borderRadius: token.borderRadiusLG,
          paddingTop: 14,
          overflow: "hidden",
        }}
      >
        <SearchPanel state={state} onChange={onChange} autoFocus={!state.q} />
      </div>
    </div>
  );
};

export default Widget;
