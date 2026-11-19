import {
  Alert,
  Card,
  Empty,
  Flex,
  Segmented,
  Spin,
  Typography,
  theme,
} from "antd";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import type { PageNumberHit } from "../../api/tipitaka-search";
import BookTree from "../../components/tipitaka/BookTree";
import LayerTag from "./LayerTag";
import type { usePageNumbers } from "./hooks";
import type { ISearchState } from "./state";

const { Text } = Typography;
const PALI_FONT = '"Noto Serif", Georgia, "Times New Roman", serif';

/** 版本的显示顺序（同后端排序） */
const EDITIONS = ["PTS", "My", "VRI", "Thai", "Other", "CS"] as const;
/** 印本页阅读页 id 的版本代号 */
const EDITION_CODE: Record<string, string> = {
  PTS: "P",
  My: "M",
  VRI: "V",
  Thai: "T",
  Other: "O",
};
const LAYERS = ["mula", "atthakatha", "tika", "anna", ""] as const;

/** 同一本书、同一卷的同一页合成一条（一页可能跨几段） */
interface IPageEntry {
  key: string;
  hit: PageNumberHit;
  paras: number[];
}

const group = (hits: PageNumberHit[]): IPageEntry[] => {
  const map = new Map<string, IPageEntry>();
  for (const hit of hits) {
    const key = `${hit.book_id}-${hit.volume ?? 0}-${hit.page}`;
    const entry = map.get(key);
    if (entry) {
      entry.paras.push(hit.para ?? 0);
    } else {
      map.set(key, { key, hit, paras: [hit.para ?? 0] });
    }
  }
  return [...map.values()];
};

/** 书的 tag 包含目录节点的全部 tag，就算在这个节点下（节点 key = tag 用逗号连起来） */
const inNode = (hit: PageNumberHit, tags: string[]): boolean =>
  tags.every((t) => (hit.tags ?? []).includes(t));

/** 点进去的阅读页：印本页（版本_版本书名_卷_页）或 CS 段落号（书_书首段_段落号） */
const linkOf = (e: IPageEntry, channel?: string | null): string => {
  const h = e.hit;
  const path =
    h.edition === "CS"
      ? `/workspace/tipitaka/cs-para/${h.book}_${h.book_para}_${h.page}`
      : `/workspace/tipitaka/page/${EDITION_CODE[h.edition ?? ""] ?? "O"}_${
          h.edition_title ?? ""
        }_${h.volume ?? 0}_${h.page}`;
  return path + (channel ? `?channel=${channel}` : "");
};

interface IWidget {
  state: ISearchState;
  onChange: (patch: Partial<ISearchState>) => void;
  result: ReturnType<typeof usePageNumbers>;
  /** 检索词能否解析成「页」或「卷.页」 */
  valid: boolean;
  /** 解析出的卷号 */
  volume?: number;
  channel?: string | null;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
}

/**
 * 页码标签页：一个页码在 PTS、缅甸版、VRI、泰国版、其他里各指一处或多处，另有 CS 段落号。
 * 先选版本，再按层级列出书；左栏目录树按书的 tag 过滤（在前端算，一个页码最多约一千处）。
 * 点一条打开那一页（印本页阅读页，有上一页 / 下一页）。
 */
const PageTab = ({
  state,
  onChange,
  result,
  valid,
  volume,
  channel,
  newTab,
}: IWidget) => {
  const intl = useIntl();
  const { token } = theme.useToken();

  if (!valid) {
    return (
      <Empty
        style={{ marginTop: 64 }}
        image={Empty.PRESENTED_IMAGE_SIMPLE}
        description={intl.formatMessage({ id: "search.page.hint" })}
      />
    );
  }
  if (result.error) {
    return (
      <Alert
        type="warning"
        showIcon
        style={{ margin: 16 }}
        message={result.error.message}
      />
    );
  }

  const all = result.data ?? [];
  const nodes = state.pageScope.map((key) => key.split(","));
  // 版本的计数跟着目录范围走；目录树的计数跟着当前版本走
  const hits =
    nodes.length === 0
      ? all
      : all.filter((h) => nodes.some((tags) => inNode(h, tags)));
  const counts = new Map<string, number>();
  for (const h of hits) {
    counts.set(h.edition ?? "", (counts.get(h.edition ?? "") ?? 0) + 1);
  }
  const available = EDITIONS.filter((e) => counts.get(e));
  const edition =
    state.edition && counts.get(state.edition) ? state.edition : available[0];
  const entries = group(hits.filter((h) => h.edition === edition));
  // 没选目录时的当前版本（目录树计数用）；选了目录、当前版本却被筛空时退回全部版本的第一个
  const editionAll =
    edition ??
    (state.edition && all.some((h) => h.edition === state.edition)
      ? state.edition
      : EDITIONS.find((e) => all.some((h) => h.edition === e)));
  const treeEntries = group(all.filter((h) => h.edition === editionAll));

  return (
    <Flex style={{ height: "100%", minHeight: 0 }}>
      {all.length > 0 && (
        <div
          style={{
            width: 288,
            flex: "none",
            overflowY: "auto",
            padding: "2px 2px 24px",
            borderRight: `1px solid ${token.colorBorderSecondary}`,
            background: token.colorFillQuaternary,
          }}
        >
          <BookTree
            selectedKeys={state.pageScope}
            count={(tags) =>
              treeEntries.filter((e) => inNode(e.hit, tags)).length
            }
            onChange={(keys) => onChange({ pageScope: keys.map(String) })}
            onRootChange={() => onChange({ pageScope: [] })}
          />
        </div>
      )}
      <Flex vertical style={{ flex: 1, minWidth: 0, minHeight: 0 }}>
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
              { id: volume ? "search.page.total.volume" : "search.page.total" },
              {
                n: (result.meta?.total ?? 0).toLocaleString(),
                volume,
                page: parseInt(state.q.split(".").pop() ?? "", 10),
              },
            )}
          </Text>
          {available.length > 0 && (
            <Segmented<string>
              size="small"
              value={edition}
              options={available.map((e) => ({
                value: e,
                label: (
                  <Flex gap={6} align="center">
                    {intl.formatMessage({ id: `search.edition.${e}` })}
                    <Text
                      type="secondary"
                      style={{ fontSize: token.fontSizeSM }}
                    >
                      {counts.get(e)}
                    </Text>
                  </Flex>
                ),
              }))}
              onChange={(e) => onChange({ edition: e })}
            />
          )}
        </Flex>
        <div
          style={{
            flex: 1,
            minHeight: 0,
            overflowY: "auto",
            padding: "12px 16px 24px",
          }}
        >
          <Spin spinning={result.loading}>
            {result.data && hits.length === 0 ? (
              <Empty
                style={{ marginTop: 48 }}
                description={intl.formatMessage({
                  id:
                    all.length > 0 ? "search.page.scope_empty" : "search.empty",
                })}
              />
            ) : (
              <div
                style={{
                  display: "grid",
                  gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))",
                  gap: 12,
                }}
              >
                {LAYERS.map((layer) => {
                  const list = entries.filter(
                    (e) => (e.hit.layer ?? "") === layer,
                  );
                  if (list.length === 0) return null;
                  return (
                    <Card
                      key={layer || "none"}
                      size="small"
                      title={
                        <Flex justify="space-between" align="center">
                          {layer ? (
                            <LayerTag layer={layer} />
                          ) : (
                            <Text type="secondary">
                              {intl.formatMessage({ id: "search.layer.none" })}
                            </Text>
                          )}
                          <Text type="secondary" style={{ fontWeight: 400 }}>
                            {list.length}
                          </Text>
                        </Flex>
                      }
                      styles={{ body: { padding: "4px 0" } }}
                    >
                      {list.map((e) => (
                        <Link
                          key={e.key}
                          to={linkOf(e, channel)}
                          target={newTab ? "_blank" : undefined}
                          rel={newTab ? "noopener noreferrer" : undefined}
                          style={{ display: "block", padding: "4px 12px" }}
                        >
                          <Flex gap={8} align="baseline">
                            <Text
                              ellipsis={{ tooltip: e.hit.book_title }}
                              style={{
                                flex: 1,
                                minWidth: 0,
                                fontFamily: PALI_FONT,
                                color: "inherit",
                              }}
                            >
                              {e.hit.book_title}
                            </Text>
                            <Text
                              type="secondary"
                              style={{ fontFamily: "monospace", fontSize: 12 }}
                            >
                              {e.hit.edition === "CS"
                                ? `§${e.hit.page}`
                                : `${e.hit.volume ? `${e.hit.volume}.` : ""}${e.hit.page}`}
                            </Text>
                            <Text code style={{ fontSize: 11 }}>
                              {`${e.hit.book}-${e.paras.join(",")}`}
                            </Text>
                          </Flex>
                        </Link>
                      ))}
                    </Card>
                  );
                })}
              </div>
            )}
          </Spin>
        </div>
      </Flex>
    </Flex>
  );
};

export default PageTab;
