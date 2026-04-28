import { useEffect, useState, type JSX } from "react";
import { App, Button, Modal, Popover, Skeleton, Typography } from "antd";
import { EditOutlined, ReloadOutlined } from "@ant-design/icons";
import { Link } from "react-router";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { get as getLang } from "../../locales";

import NissayaCardTable, { type INissayaRelation } from "./NissayaCardTable";

import type { ITerm } from "../../api/Term";
import MdView from "../general/MdView";
import TermModal from "../term/TermModal";

const { Paragraph, Title } = Typography;

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface INissayaCardData {
  row: INissayaRelation[];
  ending: ITerm;
}

interface INissayaCardResponse {
  ok: boolean;
  message: string;
  data: INissayaCardData;
}

// ---------------------------------------------------------------------------
// Public wrappers
// ---------------------------------------------------------------------------

interface INissayaCardModal {
  text?: string;
  trigger?: JSX.Element | string;
}

export const NissayaCardPop = ({ text, trigger }: INissayaCardModal) => (
  <Popover
    style={{ width: 700 }}
    content={<NissayaCardWidget text={text} cache hideEditButton />}
    placement="bottom"
  >
    <Typography.Link>{trigger}</Typography.Link>
  </Popover>
);

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
  const intl = useIntl();
  // antd v6: use App.useApp() instead of static message/notification
  const { message } = App.useApp();

  const [cardData, setCardData] = useState<INissayaRelation[]>();
  const [term, setTerm] = useState<ITerm>();
  const [loading, setLoading] = useState(false);
  // Incrementing this counter triggers a manual reload
  const [reloadTick, setReloadTick] = useState(0);

  useEffect(() => {
    if (!text) return;

    const uiLang = getLang();
    const cacheKey = `nissaya-ending/${uiLang}/${text}`;

    // ── Cache hit (synchronous path) ────────────────────────────────────────
    if (cache) {
      const cached = sessionStorage.getItem(cacheKey);
      if (cached) {
        const parsed: INissayaCardData = JSON.parse(cached);
        setCardData(parsed.row);
        setTerm(parsed.ending);
        return; // no network request needed
      }
    }

    // ── Network request ─────────────────────────────────────────────────────
    const url = `/api/v2/nissaya-card/${text}?lang=${uiLang}&content_type=json`;
    console.debug("api request", url);

    let cancelled = false;
    setLoading(true);

    get<INissayaCardResponse>(url)
      .then((json) => {
        if (cancelled) return;
        console.debug("api response", json);

        if (json.ok) {
          setCardData(json.data.row);
          setTerm(json.data.ending);
          if (cache) {
            sessionStorage.setItem(cacheKey, JSON.stringify(json.data));
          }
        } else {
          message.error(json.message);
        }
      })
      .catch((e: INissayaCardResponse) => {
        if (!cancelled) message.error(e.message);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    // Cleanup: mark stale requests so their callbacks are ignored
    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cache, text, reloadTick]);

  const handleReload = () => {
    const uiLang = getLang();
    sessionStorage.removeItem(`nissaya-ending/${uiLang}/${text}`);
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
          {term?.word}
          {!hideEditButton && (
            <TermModal
              id={term?.id}
              trigger={<Button type="link" icon={<EditOutlined />} />}
            />
          )}
        </Title>

        <div>
          <Link to={`/nissaya/ending/${term?.word}`} target="_blank">
            {intl.formatMessage(
              { id: "buttons.open.in.new.tab" },
              { item: "" }
            )}
          </Link>
          <Button
            type="link"
            icon={<ReloadOutlined />}
            onClick={handleReload}
          />
        </div>
      </div>

      <Paragraph>{term?.meaning}</Paragraph>
      <MdView html={term?.html} />
      {cardData && <NissayaCardTable data={cardData} />}
    </div>
  );
};

export default NissayaCardWidget;
