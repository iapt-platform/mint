import {
  Alert,
  Button,
  Empty,
  Flex,
  Pagination,
  Segmented,
  Spin,
  Typography,
  message,
  theme,
} from "antd";
import { CopyOutlined } from "@ant-design/icons";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import type { SearchHit, SearchMode } from "../../api/tipitaka-search";
import CategoryFacets from "./CategoryFacets";
import Highlight from "./Highlight";
import LayerTag from "./LayerTag";
import { layerOf } from "./categories";
import { FULLTEXT_PER_PAGE, type useFullTextSearch } from "./hooks";
import { SEARCH_MODES, type ISearchState } from "./state";

const { Text } = Typography;

/** 索引里的 path 是「书系/书名/章/…/本段标题」：去掉开头的书系和末尾的本段标题 */
const crumbsOf = (path?: string | null): string[] => {
  const parts = (path ?? "").split("/").filter(Boolean);
  return parts.slice(1, -1);
};

const PALI_FONT = '"Noto Serif", Georgia, "Times New Roman", serif';

interface IHitProps {
  hit: SearchHit;
  channel?: string | null;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
}

const Hit = ({ hit, channel, newTab }: IHitProps) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const crumbs = crumbsOf(hit.path);
  const coord = hit.related_id ?? "";
  const to =
    `/workspace/tipitaka/para/${coord}` +
    (channel ? `?channel=${channel}` : "");

  const copy = () => {
    const text =
      [...crumbs, hit.title].filter(Boolean).join(" › ") + ` (${coord})`;
    navigator.clipboard
      .writeText(text)
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
      <Flex gap={6} align="center" wrap style={{ fontSize: token.fontSizeSM }}>
        <LayerTag layer={layerOf(hit.category)} />
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
            {hit.title || coord}
          </Link>
          <Text code style={{ fontSize: token.fontSizeSM }}>
            {coord}
          </Text>
        </Flex>
        <Button size="small" type="text" icon={<CopyOutlined />} onClick={copy}>
          {intl.formatMessage({ id: "search.copy_citation" })}
        </Button>
      </Flex>
      <div
        style={{
          fontFamily: PALI_FONT,
          fontSize: 15.5,
          lineHeight: 1.75,
          maxWidth: "78ch",
          marginTop: 4,
        }}
      >
        {hit.highlight ? (
          <>
            …<Highlight text={hit.highlight} />…
          </>
        ) : (
          <>{(hit.content ?? "").slice(0, 240)}…</>
        )}
      </div>
      {(hit.bold?.length ?? 0) > 0 && (
        <Text type="secondary" style={{ fontSize: token.fontSizeSM }}>
          {intl.formatMessage({ id: "search.bold_in_para" })}
          <span style={{ fontFamily: PALI_FONT, fontWeight: 600 }}>
            {hit.bold?.slice(0, 6).join(" · ")}
          </span>
        </Text>
      )}
    </div>
  );
};

interface IWidget {
  state: ISearchState;
  result: ReturnType<typeof useFullTextSearch>;
  onChange: (patch: Partial<ISearchState>) => void;
  /** 当前阅读的 channel：跳到段落时沿用 */
  channel?: string | null;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
}

/** 全文标签页：左栏分类筛选，右栏匹配方式 + 结果 + 分页 */
const FullTextTab = ({ state, result, onChange, channel, newTab }: IWidget) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const { loading, data, meta, error } = result;
  const total = meta?.total ?? 0;
  const lastPage = meta?.last_page ?? 1;

  return (
    <Flex style={{ height: "100%", minHeight: 0 }}>
      <div
        style={{
          width: 232,
          flex: "none",
          overflowY: "auto",
          padding: "12px 12px 24px",
          borderRight: `1px solid ${token.colorBorderSecondary}`,
          background: token.colorFillQuaternary,
        }}
      >
        <CategoryFacets
          buckets={meta?.facets?.category ?? []}
          selected={state.category}
          onChange={(category) => onChange({ category, page: 1 })}
        />
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
              {
                id:
                  meta?.total_relation === "gte"
                    ? "search.total.gte"
                    : "search.total",
              },
              { n: total.toLocaleString() },
            )}
          </Text>
          <Text type="secondary">
            {intl.formatMessage({ id: "search.mode" })}
          </Text>
          <Segmented<SearchMode>
            size="small"
            value={state.mode}
            options={SEARCH_MODES.map((m) => ({
              value: m,
              label: intl.formatMessage({ id: `search.mode.${m}` }),
              title: intl.formatMessage({ id: `search.mode.${m}.tip` }),
            }))}
            onChange={(mode) => onChange({ mode, page: 1 })}
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
          {error ? (
            <Alert
              type="warning"
              showIcon
              style={{ marginTop: 16 }}
              message={error.message}
            />
          ) : (
            <Spin spinning={loading}>
              {data && data.length === 0 ? (
                <Empty
                  style={{ marginTop: 48 }}
                  description={intl.formatMessage({ id: "search.empty" })}
                />
              ) : (
                (data ?? []).map((hit) => (
                  <Hit
                    key={hit.id}
                    hit={hit}
                    channel={channel}
                    newTab={newTab}
                  />
                ))
              )}
              {total > FULLTEXT_PER_PAGE && (
                <Flex justify="center" style={{ paddingTop: 16 }}>
                  <Pagination
                    size="small"
                    current={state.page}
                    pageSize={FULLTEXT_PER_PAGE}
                    // 翻页深度受 OpenSearch 限制，超出 last_page 的页取不到
                    total={Math.min(total, lastPage * FULLTEXT_PER_PAGE)}
                    showSizeChanger={false}
                    onChange={(page) => onChange({ page })}
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

export default FullTextTab;
