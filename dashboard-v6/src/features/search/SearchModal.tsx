import { Button, Modal, Tooltip } from "antd";
import { CloseOutlined, ExportOutlined } from "@ant-design/icons";
import { useState } from "react";
import { useIntl } from "react-intl";
import { fullUrl } from "../../utils";
import SearchPanel from "./SearchPanel";
import { initialSearchState, toSearchParams, type ISearchState } from "./state";

interface IWidget {
  open: boolean;
  onClose: () => void;
}

/**
 * workspace 主导航「搜索」打开的弹窗。
 *
 * 关闭后不销毁：再打开时检索词、筛选、页码都还在。
 * 「在新标签页打开」把当前条件带到 /search，地址可以收藏、分享。弹窗里的链接（包括结果）都在新标签页打开。
 */
const SearchModal = ({ open, onClose }: IWidget) => {
  const intl = useIntl();
  const [state, setState] = useState<ISearchState>(initialSearchState);

  // 浮动容器里的链接一律在新标签页打开，弹窗留着
  const openFullPage = () => {
    window.open(
      fullUrl(`/search?${toSearchParams(state).toString()}`),
      "_blank",
    );
  };

  return (
    <Modal
      open={open}
      onCancel={onClose}
      footer={null}
      closable={false}
      width="min(1160px, calc(100vw - 32px))"
      style={{ top: "5vh", paddingBottom: 0 }}
      styles={{
        container: {
          padding: "14px 0 0",
          height: "90vh",
          display: "flex",
          flexDirection: "column",
          overflow: "hidden",
        },
        body: { flex: 1, minHeight: 0 },
      }}
    >
      <SearchPanel
        state={state}
        onChange={(patch) => setState((s) => ({ ...s, ...patch }))}
        newTab
        autoFocus
        extra={
          <>
            <Tooltip
              title={intl.formatMessage({ id: "search.open_full_page" })}
            >
              <Button icon={<ExportOutlined />} onClick={openFullPage} />
            </Tooltip>
            <Button
              icon={<CloseOutlined />}
              onClick={onClose}
              aria-label="close"
            />
          </>
        }
      />
    </Modal>
  );
};

export default SearchModal;
