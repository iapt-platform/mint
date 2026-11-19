import { Empty, Flex, Tabs, theme } from "antd";
import { useMemo, useState, type ReactNode } from "react";
import { useIntl } from "react-intl";
import { useSearchParams } from "react-router";
import DefinitionTab from "./DefinitionTab";
import FullTextTab from "./FullTextTab";
import PageTab from "./PageTab";
import SearchInput from "./SearchInput";
import TitleTab from "./TitleTab";
import {
  useTitleSearch,
  useFullTextSearch,
  usePageNumbers,
  useWordBooks,
  useWordForms,
  useWordParagraphs,
} from "./hooks";
import {
  SEARCH_TABS,
  looksLikePageNumber,
  parsePageQuery,
  type ISearchState,
  type SearchTab,
} from "./state";

interface IWidget {
  state: ISearchState;
  onChange: (patch: Partial<ISearchState>) => void;
  /** 搜索框右边的额外按钮（弹窗里放「在整页打开」「关闭」） */
  extra?: ReactNode;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
  autoFocus?: boolean;
}

/**
 * workspace 搜索面板：一个搜索框，下面四个标签页。弹窗与整页共用。
 *
 * - 全文：`/v3/search`，按分类（层级 / 藏别 / 其他）筛选
 * - 定义：`/v3/pali-word-forms` 展开词形 → `/v3/tipitaka-word-paragraphs`（+ `/aggregate` 按书统计）
 * - 标题：`/v3/tipitaka-titles?q=`
 * - 页码：`/v3/tipitaka-page-numbers/{page}`，PTS / 缅甸版 / VRI / 泰国版 / 其他与 CS 段落号
 *
 * 全文、定义、标题同时查询，标签上直接显示命中数。
 */
const SearchPanel = ({
  state,
  onChange,
  extra,
  newTab,
  autoFocus,
}: IWidget) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  // 输入框的草稿；检索词从外面变了（如浏览器后退）时跟着变
  const [draft, setDraft] = useState(state.q);
  const [syncedQ, setSyncedQ] = useState(state.q);
  if (syncedQ !== state.q) {
    setSyncedQ(state.q);
    setDraft(state.q);
  }

  // 从阅读页打开时，跳到段落 / 章节沿用当前阅读的 channel
  const [locationParams] = useSearchParams();
  const channel = locationParams.get("channel");

  // 输入的是页码（100、2.100）时全文不查：正文里的数字几乎都会命中，结果没有意义
  const isPageQuery = looksLikePageNumber(state.q);
  const fullText = useFullTextSearch(
    isPageQuery ? "" : state.q,
    state.mode,
    state.category,
    state.page,
  );
  const titles = useTitleSearch(state.q, state.layer, state.titlePage);
  const pageQuery = useMemo(() => parsePageQuery(state.q), [state.q]);
  const pageNumbers = usePageNumbers(pageQuery);

  // 定义：没勾选过就用输入词那一组（没有则第一组）的全部词形
  const forms = useWordForms(state.q);
  const words = useMemo(() => {
    if (state.forms) return state.forms;
    const groups = forms.data ?? [];
    const q = state.q.trim().toLowerCase();
    const group = groups.find((g) => g.base === q) ?? groups[0];
    return group ? group.forms.map((f) => f.word) : [];
  }, [state.forms, state.q, forms.data]);
  const bold = state.bold === "all" ? undefined : state.bold;
  const wordBooks = useWordBooks(words, bold);
  // 目录范围内的书：书的 tag 包含某个选中节点的全部 tag
  const scopeBooks = useMemo(() => {
    const books = wordBooks.data ?? [];
    if (state.scope.length === 0) return books;
    const nodes = state.scope.map((key) => key.split(","));
    return books.filter((b) =>
      nodes.some((tags) => tags.every((t) => (b.tags ?? []).includes(t))),
    );
  }, [wordBooks.data, state.scope]);
  // 最终的书过滤：勾了书就用勾的；只选了目录就用目录范围内的全部书（要等统计结果）
  let bookIds: number[] | null = state.bookIds;
  if (state.bookIds.length === 0 && state.scope.length > 0) {
    bookIds = wordBooks.data
      ? scopeBooks.map((b) => b.book_id ?? 0).filter(Boolean)
      : null;
  }
  const scopeEmpty =
    bookIds !== null && state.scope.length > 0 && bookIds.length === 0;
  const wordParagraphs = useWordParagraphs(
    words,
    bold,
    scopeEmpty ? null : bookIds,
    state.defPage,
  );

  const submit = (value: string) => {
    const q = value.trim();
    let tab = state.tab;
    if (looksLikePageNumber(q)) {
      tab = "page";
    } else if (tab === "page") {
      tab = "fulltext";
    }
    onChange({
      q,
      tab,
      page: 1,
      titlePage: 1,
      forms: undefined,
      scope: [],
      bookIds: [],
      defPage: 1,
      edition: undefined,
      pageScope: [],
    });
  };

  const count = (n?: number) =>
    n === undefined ? null : (
      <span
        style={{
          fontSize: token.fontSizeSM,
          lineHeight: "18px",
          padding: "0 7px",
          borderRadius: 9,
          background: token.colorFillSecondary,
          color: token.colorTextSecondary,
          fontVariantNumeric: "tabular-nums",
        }}
      >
        {n.toLocaleString()}
      </span>
    );
  const totals: Partial<Record<SearchTab, number | undefined>> = {
    fulltext: state.q && !isPageQuery ? fullText.meta?.total : undefined,
    title: state.q ? titles.meta?.total : undefined,
    page: pageQuery ? pageNumbers.meta?.total : undefined,
    definition:
      state.q && words.length ? wordParagraphs.meta?.total : undefined,
  };

  const pane = (tab: SearchTab): ReactNode => {
    if (!state.q) {
      return (
        <Empty
          style={{ marginTop: 64 }}
          image={Empty.PRESENTED_IMAGE_SIMPLE}
          description={intl.formatMessage({ id: "search.hint" })}
        />
      );
    }
    switch (tab) {
      case "fulltext":
        if (isPageQuery) {
          return (
            <Empty
              style={{ marginTop: 64 }}
              image={Empty.PRESENTED_IMAGE_SIMPLE}
              description={intl.formatMessage({ id: "search.fulltext.page" })}
            />
          );
        }
        return (
          <FullTextTab
            state={state}
            result={fullText}
            onChange={onChange}
            channel={channel}
            newTab={newTab}
          />
        );
      case "title":
        return (
          <TitleTab
            state={state}
            result={titles}
            onChange={onChange}
            channel={channel}
            newTab={newTab}
          />
        );
      case "definition":
        return (
          <DefinitionTab
            state={state}
            onChange={onChange}
            forms={forms}
            words={words}
            paragraphs={wordParagraphs}
            books={wordBooks}
            scopeBooks={scopeBooks}
            scopeEmpty={scopeEmpty}
            channel={channel}
            newTab={newTab}
          />
        );
      case "page":
        return (
          <PageTab
            state={state}
            onChange={onChange}
            result={pageNumbers}
            valid={pageQuery !== null}
            volume={pageQuery?.volume}
            channel={channel}
            newTab={newTab}
          />
        );
    }
  };

  return (
    <Flex vertical style={{ height: "100%", minHeight: 0 }}>
      <div
        style={{
          padding: "0 16px",
          borderBottom: `1px solid ${token.colorBorderSecondary}`,
        }}
      >
        <Flex gap={8} align="center">
          <SearchInput
            value={draft}
            onChange={setDraft}
            onSearch={submit}
            autoFocus={autoFocus}
          />
          {extra}
        </Flex>
        <Tabs
          activeKey={state.tab}
          onChange={(k) => onChange({ tab: k as SearchTab })}
          tabBarStyle={{ marginBottom: 0 }}
          items={SEARCH_TABS.map((t) => ({
            key: t,
            label: (
              <Flex gap={6} align="center">
                {intl.formatMessage({ id: `search.tab.${t}` })}
                {count(totals[t])}
              </Flex>
            ),
          }))}
        />
      </div>
      <div style={{ flex: 1, minHeight: 0 }}>{pane(state.tab)}</div>
    </Flex>
  );
};

export default SearchPanel;
