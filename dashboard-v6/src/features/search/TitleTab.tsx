import { Alert, Flex, Segmented, Table, Typography, theme } from "antd";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import type { ChapterHit, ChapterLayer } from "../../api/tipitaka-search";
import LayerTag from "./LayerTag";
import { TITLE_PER_PAGE, type useChapterSearch } from "./hooks";
import type { ISearchState } from "./state";

const { Text } = Typography;
const PALI_FONT = '"Noto Serif", Georgia, "Times New Roman", serif';
const LAYERS: ChapterLayer[] = ["mula", "atthakatha", "tika", "anna"];

interface IWidget {
  state: ISearchState;
  result: ReturnType<typeof useChapterSearch>;
  onChange: (patch: Partial<ISearchState>) => void;
  channel?: string | null;
  /** 链接在新标签页打开（在弹窗等浮动容器里时） */
  newTab?: boolean;
}

/** 标题标签页：按章节名定位（/v3/tipitaka-chapters?q=），可按层级筛选 */
const TitleTab = ({ state, result, onChange, channel, newTab }: IWidget) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const { loading, data, meta, error } = result;
  const num = (n?: number) => (
    <span style={{ fontVariantNumeric: "tabular-nums" }}>
      {n?.toLocaleString() ?? "—"}
    </span>
  );

  return (
    <Flex vertical style={{ height: "100%", minHeight: 0 }}>
      <Flex
        gap={12}
        align="center"
        wrap
        style={{
          padding: "10px 16px",
          borderBottom: `1px solid ${token.colorBorderSecondary}`,
        }}
      >
        <Text strong>
          {intl.formatMessage(
            { id: "search.total.chapters" },
            { n: (meta?.total ?? 0).toLocaleString() },
          )}
        </Text>
        <Text type="secondary">
          {intl.formatMessage({ id: "search.facet.layer" })}
        </Text>
        <Segmented<ChapterLayer | "">
          size="small"
          value={state.layer ?? ""}
          options={[
            { value: "", label: intl.formatMessage({ id: "search.all" }) },
            ...LAYERS.map((l) => ({
              value: l,
              label: intl.formatMessage({ id: `search.layer.${l}` }),
            })),
          ]}
          onChange={(v) => onChange({ layer: v || undefined, titlePage: 1 })}
        />
      </Flex>
      <div
        style={{
          flex: 1,
          minHeight: 0,
          overflowY: "auto",
          padding: "0 16px 16px",
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
          <Table<ChapterHit>
            size="small"
            loading={loading}
            dataSource={data}
            rowKey={(r) => `${r.book}-${r.para}`}
            scroll={{ x: 640 }}
            locale={{ emptyText: intl.formatMessage({ id: "search.empty" }) }}
            pagination={{
              current: state.titlePage,
              pageSize: TITLE_PER_PAGE,
              total: meta?.total ?? 0,
              showSizeChanger: false,
              size: "small",
              hideOnSinglePage: true,
              onChange: (titlePage) => onChange({ titlePage }),
            }}
            columns={[
              {
                title: intl.formatMessage({ id: "search.col.chapter" }),
                key: "title",
                render: (_, r) => {
                  const to =
                    `/workspace/tipitaka/chapter/${r.book}-${r.para}` +
                    (channel ? `?channel=${channel}` : "");
                  return (
                    <div>
                      <Link
                        to={to}
                        target={newTab ? "_blank" : undefined}
                        rel={newTab ? "noopener noreferrer" : undefined}
                        style={{ fontFamily: PALI_FONT, fontWeight: 600 }}
                      >
                        {r.title}
                      </Link>
                      {(r.translations ?? []).length > 0 && (
                        <Text type="secondary" style={{ marginInlineStart: 8 }}>
                          {r.translations?.map((t) => t.title).join(" / ")}
                        </Text>
                      )}
                      <div>
                        <Text
                          type="secondary"
                          style={{
                            fontFamily: PALI_FONT,
                            fontSize: token.fontSizeSM,
                          }}
                        >
                          {(r.path ?? [])
                            .slice(1)
                            .map((p) => p.title)
                            .join(" › ")}
                        </Text>
                      </div>
                    </div>
                  );
                },
              },
              {
                title: intl.formatMessage({ id: "search.facet.layer" }),
                key: "layer",
                width: 80,
                render: (_, r) => <LayerTag layer={r.layer} />,
              },
              {
                title: intl.formatMessage({ id: "search.col.coord" }),
                key: "coord",
                width: 100,
                render: (_, r) => <Text code>{`${r.book}-${r.para}`}</Text>,
              },
              {
                title: intl.formatMessage({ id: "search.col.paragraphs" }),
                key: "paragraphs",
                align: "right",
                width: 80,
                render: (_, r) => num(r.paragraphs),
              },
              {
                title: intl.formatMessage({ id: "search.col.chars" }),
                key: "chars",
                align: "right",
                width: 100,
                render: (_, r) => num(r.chars),
              },
            ]}
          />
        )}
      </div>
    </Flex>
  );
};

export default TitleTab;
