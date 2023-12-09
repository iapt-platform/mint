import { Statistic, StatisticCard } from "@ant-design/pro-components";
import { Modal } from "antd";
import { useEffect, useState } from "react";
import { IItem } from "./ChannelPickerTable";

interface IChannelInfoModal {
  open?: boolean;
  channel?: IItem;
  onClose?: Function;
}

export const ChannelInfoModal = ({
  open,
  channel,
  onClose,
}: IChannelInfoModal) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  useEffect(() => setIsModalOpen(open), [open]);
  return (
    <Modal
      destroyOnClose={true}
      width={300}
      title="统计"
      open={isModalOpen}
      onCancel={(e: React.MouseEvent<HTMLElement, MouseEvent>) => {
        setIsModalOpen(false);
        if (typeof onClose !== "undefined") {
          onClose();
        }
      }}
      footer={<></>}
    >
      <ChannelInfoWidget channel={channel} />
    </Modal>
  );
};
interface IWidget {
  channel?: IItem;
}
const ChannelInfoWidget = ({ channel }: IWidget) => {
  const [count, setCount] = useState<number>();
  useEffect(() => {
    const sentElement = document.querySelectorAll(".pcd_sent");
    let sentList: string[] = [];
    for (let index = 0; index < sentElement.length; index++) {
      const element = sentElement[index];
      const id = element.id.split("_")[1];
      sentList.push(id);
    }
    setCount(sentList.length);
  }, []);
  let totalStrLen = 0;
  let finalStrLen = 0;
  let finalSent = 0;
  channel?.final?.forEach((value) => {
    totalStrLen += value[0];
    if (value[1]) {
      finalStrLen += value[0];
      finalSent++;
    }
  });
  const final = channel?.final ? (finalSent * 100) / channel?.final?.length : 0;
  return (
    <>
      <StatisticCard
        title={"版本：" + channel?.title}
        statistic={{
          value: count,
          suffix: "句",
          description: (
            <Statistic title="完成度" value={Math.round(final) + "%"} />
          ),
        }}
        chart={<></>}
        footer={
          <>
            <Statistic
              value={totalStrLen}
              title="巴利字符"
              layout="horizontal"
            />
            <Statistic
              value={finalStrLen}
              title="译文字符"
              layout="horizontal"
            />
            <Statistic
              value={channel?.content_created_at}
              title="创建于"
              layout="horizontal"
            />
            <Statistic
              value={channel?.content_updated_at}
              title="最近更新"
              layout="horizontal"
            />
          </>
        }
      />
    </>
  );
};

export default ChannelInfoWidget;
