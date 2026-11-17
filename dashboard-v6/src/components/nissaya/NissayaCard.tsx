import { useEffect, useState, type JSX } from "react";
import { Button, Modal, Popover, Skeleton, Typography, theme } from "antd";
import { EditOutlined, ReloadOutlined } from "@ant-design/icons";
import { Link } from "react-router";
import { useIntl } from "react-intl";

import { get as getLang } from "../../locales";

import NissayaCardTable from "./NissayaCardTable";

import { fetchNissayaCard, type NissayaCard } from "../../api/nissaya";
import MarkupView from "../markup/MarkupView";
import type { MarkupNode } from "../markup/types";
import TermModal from "../term/TermModal";

const { Paragraph, Title } = Typography;

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

// ---------------------------------------------------------------------------
// Public wrappers
// ---------------------------------------------------------------------------

interface INissayaCardModal {
  text?: string;
  trigger?: JSX.Element | string;
}

export const NissayaCardPop = ({ text, trigger }: INissayaCardModal) => {
  const intl = useIntl();
  const { token } = theme.useToken();

  return (
    <Popover
      style={{ width: 700 }}
      title={
        <div style={{ textAlign: "right" }}>
          <Link to={`/workspace/nissaya/ending/${text}`} target="_blank">
            {intl.formatMessage(
              { id: "buttons.open.in.new.tab" },
              { item: "" },
            )}
          </Link>
        </div>
      }
      content={<NissayaCardWidget text={text} cache hideEditButton />}
      placement="bottom"
      // 垂直方向始终贴在锚点下方：不翻转、不平移回视口，宁可超出可视范围也不遮挡锚点；
      // 横向仍自动适应窗口。autoAdjustOverflow 做不到——antd 在 shiftY 关闭时会
      // 强制打开 adjustY——所以自己给出 bottom 的对齐规则，offset 同 antd 默认算法
      builtinPlacements={{
        bottom: {
          points: ["tc", "bc"],
          offset: [0, token.sizePopupArrow / 2 + token.marginXXS],
          overflow: { adjustX: true, shiftX: true },
          htmlRegion: "visibleFirst",
        },
      }}
    >
      <Typography.Link>{trigger}</Typography.Link>
    </Popover>
  );
};

export const NissayaCardModal = ({ text, trigger }: INissayaCardModal) => {
  const [open, setOpen] = useState(false);

  return (
    <>
      <span onClick={() => setOpen(true)}>{trigger}</span>
      <Modal
        width={800}
        title="缅文语尾"
        open={open}
        onOk={() => setOpen(false)}
        onCancel={() => setOpen(false)}
        destroyOnHidden
      >
        <NissayaCardWidget text={text} />
      </Modal>
    </>
  );
};

// ---------------------------------------------------------------------------
// Core widget
// ---------------------------------------------------------------------------

interface IWidgetProps {
  text?: string;
  cache?: boolean;
  hideEditButton?: boolean;
}

const NissayaCardWidget = ({
  text,
  cache = false,
  hideEditButton = false,
}: IWidgetProps) => {
  // v3 /nissaya-cards（取代 v2 nissaya-card）：失败由 unwrap 统一提示
  const [card, setCard] = useState<NissayaCard>();
  const [loading, setLoading] = useState(false);
  // Incrementing this counter triggers a manual reload
  const [reloadTick, setReloadTick] = useState(0);

  useEffect(() => {
    if (!text) return;

    const uiLang = getLang();
    const cacheKey = `nissaya-card/${uiLang}/${text}`;

    // ── Cache hit (synchronous path) ────────────────────────────────────────
    if (cache) {
      const cached = sessionStorage.getItem(cacheKey);
      if (cached) {
        // 读缓存后在 effect 里同步 setState 是原有写法（改成惰性初始化要连同 text 变化一起处理）
        // eslint-disable-next-line react-hooks/set-state-in-effect
        setCard(JSON.parse(cached) as NissayaCard);
        return; // no network request needed
      }
    }

    // ── Network request ─────────────────────────────────────────────────────
    let cancelled = false;
    setLoading(true);

    fetchNissayaCard(text, uiLang)
      .then((data) => {
        if (cancelled || !data) return;
        setCard(data);
        if (cache) {
          sessionStorage.setItem(cacheKey, JSON.stringify(data));
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    // Cleanup: mark stale requests so their callbacks are ignored
    return () => {
      cancelled = true;
    };
  }, [cache, text, reloadTick]);

  const handleReload = () => {
    const uiLang = getLang();
    sessionStorage.removeItem(`nissaya-card/${uiLang}/${text}`);
    setReloadTick((t) => t + 1);
  };

  if (loading) {
    return <Skeleton title={{ width: 200 }} paragraph={{ rows: 4 }} active />;
  }

  return (
    <div style={{ maxWidth: 750 }}>
      {/* Header row */}
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Title level={4}>
          {card?.term?.word ?? card?.ending}
          {!hideEditButton && card?.term?.id && (
            <TermModal
              id={card.term.id}
              trigger={<Button type="link" icon={<EditOutlined />} />}
            />
          )}
        </Title>

        <Button type="link" icon={<ReloadOutlined />} onClick={handleReload} />
      </div>

      <Paragraph>{card?.term?.meaning}</Paragraph>
      <MarkupView
        className="pcd_article"
        nodes={card?.term?.body as MarkupNode[] | undefined}
      />
      {card?.relations && <NissayaCardTable data={card.relations} />}
    </div>
  );
};

export default NissayaCardWidget;
