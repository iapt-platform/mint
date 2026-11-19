import {
  Alert,
  Button,
  Checkbox,
  Empty,
  Flex,
  Pagination,
  Segmented,
  Spin,
  Tag,
  Typography,
  message,
  theme,
} from "antd";
import { CopyOutlined } from "@ant-design/icons";
import { useState } from "react";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import type { WordBook, WordParagraph } from "../../api/tipitaka-search";
import BookTree from "../../components/tipitaka/BookTree";
import type { WordFormGroup } from "../../api/pali-word-form";
import LayerTag from "./LayerTag";
import {
  DEFINITION_PER_PAGE,
  type useWordBooks,
  type useWordForms,
  type useWordParagraphs,
} from "./hooks";
import {
  DEFINITION_BOLDS,
  type DefinitionBold,
  type ISearchState,
} from "./state";

const { Text } = Typography;
const PALI_FONT = '"Noto Serif", Georgia, "Times New Roman", serif';
/** 第一组（输入的词本身）默认显示的词形数；其余词根默认折叠 */
const FORMS_SHOWN = 8;
/** 书单默认显示的条数 */
const BOOKS_SHOWN = 12;

type Ref = NonNullable<WordParagraph["refs"]>[number];

/** 页码引用的显示：「PTS Nam 2.299」 */
const refLabel = (r: Ref) =>
  [r.type, r.title, `${r.volume ? `${r.volume}.` : ""}${r.page}`]
    .filter(Boolean)
    .join(" ");

/** 复制用的引用：「WP 缩写 book:para; 各印本页码」（同 wikipali 插件的出处格式） */
const citationOf = (p: WordParagraph): string => {
  const refs = p.refs ?? [];
  const wp = refs.find((r) => r.type === "WP");
  const head = [wp?.title, `${p.book}:${p.para}`].filter(Boolean).join(" ");
  return [head, ...refs.filter((r) => r.type !== "WP").map(refLabel)].join(
    "; ",
  );
};

/* ─── 左栏：词形 ─── */

interface IFormsProps {
  groups: WordFormGroup[];
  selected: string[];
  onChange: (forms: string[]) => void;
}

const FormGroups = ({ groups, selected, onChange }: IFormsProps) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const [expanded, setExpanded] = useState<string[]>([]);
  const chosen = new Set(selected);

  const toggle = (word: string) =>
    onChange(
      chosen.has(word)
        ? selected.filter((w) => w !== word)
        : [...selected, word],
    );
  const toggleGroup = (g: WordFormGroup) => {
    const words = g.forms.map((f) => f.word);
    const all = words.every((w) => chosen.has(w));
    onChange(
      all
        ? selected.filter((w) => !words.includes(w))
        : [...selected, ...words.filter((w) => !chosen.has(w))],
    );
  };

  return (
    <Flex vertical gap={12}>
      {groups.map((g, i) => {
        const open = expanded.includes(g.base);
        const limit = i === 0 ? FORMS_SHOWN : 0;
        const shown = open ? g.forms : g.forms.slice(0, limit);
        const all = g.forms.every((f) => chosen.has(f.word));
        return (
          <div
            key={g.base}
            style={
              i > 0
                ? {
                    borderTop: `1px dashed ${token.colorBorder}`,
                    paddingTop: 8,
                  }
                : undefined
            }
          >
            <Flex justify="space-between" align="baseline">
              <Text strong style={{ fontFamily: PALI_FONT }}>
                {g.base}
              </Text>
              <Button
                size="small"
                type="link"
                style={{ padding: 0 }}
                onClick={() => toggleGroup(g)}
              >
                {intl.formatMessage({
                  id: all ? "search.forms.none" : "search.forms.all",
                })}
              </Button>
            </Flex>
            <Flex vertical gap={1}>
              {shown.map((f) => (
                <Checkbox
                  key={f.word}
                  checked={chosen.has(f.word)}
                  onChange={() => toggle(f.word)}
                >
                  <Flex justify="space-between" gap={6} style={{ width: 216 }}>
                    <Text style={{ fontFamily: PALI_FONT }} ellipsis>
                      {f.word}
                    </Text>
                    <Flex
                      gap={6}
                      style={{ fontVariantNumeric: "tabular-nums" }}
                    >
                      <Text type="secondary">{f.count.toLocaleString()}</Text>
                      <Text
                        strong
                        type="secondary"
                        style={{ width: 28, textAlign: "right" }}
                        title={intl.formatMessage({ id: "search.forms.bold" })}
                      >
                        {f.bold ? f.bold.toLocaleString() : ""}
                      </Text>
                    </Flex>
                  </Flex>
                </Checkbox>
              ))}
            </Flex>
            {g.forms.length > limit && (
              <Button
                size="small"
                type="link"
                style={{ padding: 0 }}
                onClick={() =>
                  setExpanded((e) =>
                    open ? e.filter((b) => b !== g.base) : [...e, g.base],
                  )
                }
              >
                {intl.formatMessage(
                  {
                    id: open
                      ? "search.show_less"
                      : limit
                        ? "search.show_more"
                        : "search.forms.expand",
                  },
                  { n: g.forms.length - limit },
                )}
              </Button>
            )}
          </div>
        );
      })}
    </Flex>
  );
};

/* ─── 左栏：目录、书 ─── */

/** 目录节点下命中的段落数：书的 tag 包含节点的全部 tag 就算在这个节点下 */
const countInNode = (books: WordBook[], tags: string[]): number =>
  books
    .filter((b) => tags.every((t) => (b.tags ?? []).includes(t)))
    .reduce((sum, b) => sum + (b.paragraphs ?? 0), 0);

interface IBooksProps {
  result: ReturnType<typeof useWordBooks>;
  /** 目录范围内的书（没选目录就是全部） */
  books: WordBook[];
  selected: number[];
  onChange: (bookIds: number[]) => void;
}

const BookList = ({ result, books, selected, onChange }: IBooksProps) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const [showAll, setShowAll] = useState(false);
  const max = Math.max(1, ...books.map((b) => b.paragraphs ?? 0));
  const shown = showAll ? books : books.slice(0, BOOKS_SHOWN);

  return (
    <Spin spinning={result.loading}>
      <Text
        type="secondary"
        style={{ fontSize: token.fontSizeSM, letterSpacing: "0.04em" }}
      >
        {intl.formatMessage(
          { id: "search.books" },
          { n: books.length.toLocaleString() },
        )}
      </Text>
      <Flex vertical gap={0} style={{ marginTop: 4 }}>
        {shown.map((b) => (
          <div key={b.book_id}>
            <Checkbox
              checked={selected.includes(b.book_id ?? 0)}
              onChange={(e) =>
                onChange(
                  e.target.checked
                    ? [...selected, b.book_id ?? 0]
                    : selected.filter((id) => id !== b.book_id),
                )
              }
            >
              <Flex justify="space-between" gap={6} style={{ width: 216 }}>
                <Text
                  style={{ fontFamily: PALI_FONT }}
                  ellipsis={{ tooltip: b.title }}
                >
                  {b.title}
                </Text>
                <Text
                  type="secondary"
                  style={{ fontVariantNumeric: "tabular-nums" }}
                  title={intl.formatMessage(
                    { id: "search.books.tip" },
                    { n: b.occurrences },
                  )}
                >
                  {(b.paragraphs ?? 0).toLocaleString()}
                </Text>
              </Flex>
            </Checkbox>
            <div
              style={{
                height: 3,
                margin: "1px 0 3px 24px",
                borderRadius: 2,
                background: token.colorFillSecondary,
                overflow: "hidden",
              }}
            >
              <div
                style={{
                  width: `${((b.paragraphs ?? 0) / max) * 100}%`,
                  height: "100%",
                  background: token.colorPrimary,
                  opacity: 0.55,
                }}
              />
            </div>
          </div>
        ))}
      </Flex>
      {books.length > BOOKS_SHOWN && (
        <Button
          size="small"
          type="link"
          style={{ padding: 0 }}
          onClick={() => setShowAll((v) => !v)}
        >
          {intl.formatMessage(
            { id: showAll ? "search.show_less" : "search.show_more" },
            { n: books.length - BOOKS_SHOWN },
          )}
        </Button>
      )}
    </Spin>
  );
};

/* ─── 右栏：一条结果 ─── */

interface IHitProps {
  hit: WordParagraph;
  layer?: string | null;
  channel?: string | null;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
}

const Hit = ({ hit, layer, channel, newTab }: IHitProps) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  // 去掉书系（level 0）与末尾的本段标题
  const crumbs = (hit.path ?? [])
    .filter((p) => (p.level ?? 0) > 0)
    .map((p) => p.title)
    .filter((t) => t && t !== hit.title);
  const to =
    `/workspace/tipitaka/para/${hit.id}` +
    (channel ? `?channel=${channel}` : "");

  const copy = () => {
    navigator.clipboard
      .writeText(citationOf(hit))
      .then(() => message.success(intl.formatMessage({ id: "search.copied" })))
      .catch(() =>
        message.error(intl.formatMessage({ id: "search.copy_failed" })),
      );
  };

  return (
    <div
      style={{
        padding: "14px 0",
        borderBottom: `1px solid ${token.colorBorderSecondary}`,
      }}
    >
      <Flex gap={6} align="center" wrap>
        <LayerTag layer={layer} />
        <Text
          type="secondary"
          style={{ fontFamily: PALI_FONT, fontSize: token.fontSizeSM }}
        >
          {crumbs.join(" › ")}
        </Text>
      </Flex>
      <Flex
        justify="space-between"
        align="baseline"
        gap={12}
        style={{ marginTop: 2 }}
      >
        <Flex gap={8} align="baseline" wrap style={{ minWidth: 0 }}>
          <Link
            to={to}
            target={newTab ? "_blank" : undefined}
            rel={newTab ? "noopener noreferrer" : undefined}
            style={{ fontFamily: PALI_FONT, fontWeight: 600, fontSize: 15 }}
          >
            {hit.title || hit.id}
          </Link>
          <Text code style={{ fontSize: token.fontSizeSM }}>
            {hit.id}
          </Text>
        </Flex>
        <Button size="small" type="text" icon={<CopyOutlined />} onClick={copy}>
          {intl.formatMessage({ id: "search.copy_citation" })}
        </Button>
      </Flex>
      {/* text 由服务端生成：只含 <b> 与 <mark>，其余已转义 */}
      <div
        className="search-definition-text"
        style={{
          fontFamily: PALI_FONT,
          fontSize: 15.5,
          lineHeight: 1.75,
          maxWidth: "78ch",
          marginTop: 4,
        }}
        dangerouslySetInnerHTML={{ __html: hit.text ?? "" }}
      />
      {(hit.refs ?? []).length > 0 && (
        <Flex gap={4} wrap style={{ marginTop: 6 }}>
          {hit.refs?.map((r) => (
            <Tag
              key={r.type}
              bordered
              style={{ fontFamily: "monospace", marginInlineEnd: 0 }}
            >
              {refLabel(r)}
            </Tag>
          ))}
        </Flex>
      )}
    </div>
  );
};

/* ─── 标签页 ─── */

interface IWidget {
  state: ISearchState;
  onChange: (patch: Partial<ISearchState>) => void;
  forms: ReturnType<typeof useWordForms>;
  /** 实际参与检索的词形（state.forms 或默认那一组） */
  words: string[];
  paragraphs: ReturnType<typeof useWordParagraphs>;
  books: ReturnType<typeof useWordBooks>;
  /** 目录范围内的书 */
  scopeBooks: WordBook[];
  /** 选了目录、但范围内一本命中的书都没有 */
  scopeEmpty: boolean;
  channel?: string | null;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
}

/**
 * 定义标签页：逐词表按词形精确匹配。左栏勾选词形、按书筛选；默认只看黑体——
 * 注释书用黑体标出正在解释的词，所以「只看黑体」就是找「哪里给这个词下了定义」。
 */
const DefinitionTab = ({
  state,
  onChange,
  forms,
  words,
  paragraphs,
  books,
  scopeBooks,
  scopeEmpty,
  channel,
  newTab,
}: IWidget) => {
  const intl = useIntl();
  const { token } = theme.useToken();

  if (/\s/.test(state.q.trim())) {
    return (
      <Empty
        style={{ marginTop: 64 }}
        image={Empty.PRESENTED_IMAGE_SIMPLE}
        description={intl.formatMessage({ id: "search.def.multiword" })}
      />
    );
  }
  if (forms.error) {
    return (
      <Alert
        type="warning"
        showIcon
        style={{ margin: 16 }}
        message={forms.error.message}
      />
    );
  }
  if (!forms.loading && forms.data && forms.data.length === 0) {
    return (
      <Empty
        style={{ marginTop: 64 }}
        image={Empty.PRESENTED_IMAGE_SIMPLE}
        description={intl.formatMessage({ id: "search.def.no_forms" })}
      />
    );
  }

  const layerOf = new Map(
    (books.data ?? []).map((b) => [`${b.book}`, b.layer]),
  );
  const total = paragraphs.meta?.total ?? 0;

  return (
    <Flex style={{ height: "100%", minHeight: 0 }}>
      <div
        style={{
          width: 288,
          flex: "none",
          overflowY: "auto",
          padding: "12px 12px 24px",
          borderRight: `1px solid ${token.colorBorderSecondary}`,
          background: token.colorFillQuaternary,
        }}
      >
        <Spin spinning={forms.loading}>
          <Flex vertical gap={20}>
            {words.length > 0 && (
              <div style={{ margin: "-10px -10px 0" }}>
                <BookTree
                  selectedKeys={state.scope}
                  count={(tags) => countInNode(books.data ?? [], tags)}
                  onChange={(keys) =>
                    onChange({
                      scope: keys.map(String),
                      bookIds: [],
                      defPage: 1,
                    })
                  }
                  onRootChange={() =>
                    onChange({ scope: [], bookIds: [], defPage: 1 })
                  }
                />
              </div>
            )}
            <FormGroups
              groups={forms.data ?? []}
              selected={words}
              onChange={(f) => onChange({ forms: f, bookIds: [], defPage: 1 })}
            />
            {words.length > 0 && (
              <BookList
                result={books}
                books={scopeBooks}
                selected={state.bookIds}
                onChange={(bookIds) => onChange({ bookIds, defPage: 1 })}
              />
            )}
          </Flex>
        </Spin>
      </div>
      <Flex vertical style={{ flex: 1, minWidth: 0 }}>
        <Flex
          gap={12}
          align="center"
          wrap
          style={{
            padding: "10px 16px",
            borderBottom: `1px solid ${token.colorBorderSecondary}`,
          }}
        >
          <Text strong style={{ fontVariantNumeric: "tabular-nums" }}>
            {intl.formatMessage(
              { id: "search.total" },
              { n: total.toLocaleString() },
            )}
          </Text>
          <Text type="secondary">
            {intl.formatMessage(
              { id: "search.forms.selected" },
              { n: words.length },
            )}
          </Text>
          <Segmented<DefinitionBold>
            size="small"
            value={state.bold}
            options={DEFINITION_BOLDS.map((b) => ({
              value: b,
              label: intl.formatMessage({ id: `search.bold.${b}` }),
            }))}
            onChange={(bold) => onChange({ bold, bookIds: [], defPage: 1 })}
          />
        </Flex>
        <div
          style={{
            flex: 1,
            minHeight: 0,
            overflowY: "auto",
            padding: "0 16px 24px",
          }}
        >
          {scopeEmpty ? (
            <Empty
              style={{ marginTop: 48 }}
              description={intl.formatMessage({ id: "search.def.scope_empty" })}
            />
          ) : words.length === 0 && !forms.loading ? (
            <Empty
              style={{ marginTop: 48 }}
              description={intl.formatMessage({ id: "search.def.pick_forms" })}
            />
          ) : paragraphs.error ? (
            <Alert
              type="warning"
              showIcon
              style={{ marginTop: 16 }}
              message={paragraphs.error.message}
            />
          ) : (
            <Spin spinning={paragraphs.loading}>
              {paragraphs.data && paragraphs.data.length === 0 ? (
                <Empty
                  style={{ marginTop: 48 }}
                  description={intl.formatMessage({ id: "search.empty" })}
                />
              ) : (
                (paragraphs.data ?? []).map((hit) => (
                  <Hit
                    key={hit.id}
                    hit={hit}
                    layer={layerOf.get(`${hit.book}`)}
                    channel={channel}
                    newTab={newTab}
                  />
                ))
              )}
              {total > DEFINITION_PER_PAGE && (
                <Flex justify="center" style={{ paddingTop: 16 }}>
                  <Pagination
                    size="small"
                    current={state.defPage}
                    pageSize={DEFINITION_PER_PAGE}
                    total={total}
                    showSizeChanger={false}
                    onChange={(defPage) => onChange({ defPage })}
                  />
                </Flex>
              )}
            </Spin>
          )}
        </div>
      </Flex>
    </Flex>
  );
};

export default DefinitionTab;
