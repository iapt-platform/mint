import { Modal, Space } from "antd";
import { Link } from "react-router";
import TermEdit from "./TermEdit";
import type { ITermDataResponse } from "../../api/Term";
import useMergedState from "../../hooks/useMergedState"; // 确保路径正确

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  id?: string;
  word?: string;
  tags?: string[];
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  community?: boolean;
  onUpdate?: (value: ITermDataResponse) => void;
  onClose?: () => void;
}

const TermModalWidget = (props: IWidget) => {
  const {
    trigger,
    open: propsOpen,
    onUpdate,
    onClose,
    studioName,
    ...restEditProps // 其余属性透传给 TermEdit
  } = props;

  // 统一管理状态：优先使用 props.open，否则使用内部 state
  const [isModalOpen, setIsModalOpen] = useMergedState(false, {
    value: propsOpen,
    onChange: (val) => {
      if (!val) onClose?.();
    },
  });

  const close = () => setIsModalOpen(false);
  const show = () => setIsModalOpen(true);

  return (
    <>
      {trigger && <span onClick={show}>{trigger}</span>}

      <Modal
        style={{ top: 20 }}
        width={760}
        title={
          <Space>
            <span>术语</span>
            {studioName && (
              <Link
                to={`/workspace/editor/wiki/${restEditProps.id}`}
                target="_blank"
                style={{ fontSize: "12px", fontWeight: "normal" }}
              >
                在 Studio 中打开
              </Link>
            )}
          </Space>
        }
        footer={null}
        mask={{ closable: false }}
        destroyOnHidden // 关闭时销毁内部组件，防止数据残留
        open={isModalOpen}
        onCancel={close}
      >
        <TermEdit
          {...restEditProps}
          studioName={studioName}
          onUpdate={(value: ITermDataResponse) => {
            close();
            onUpdate?.(value);
          }}
        />
      </Modal>
    </>
  );
};

export default TermModalWidget;
