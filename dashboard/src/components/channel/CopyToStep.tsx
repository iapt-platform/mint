import { Steps } from "antd";
import { useEffect, useState } from "react";

import { ArticleType } from "../article/Article";
import { IChannel } from "./Channel";
import ChannelPickerTable from "./ChannelPickerTable";
import { useAppSelector } from "../../hooks";
import { sentenceList } from "../../reducers/sentence";
import ChannelSentDiff from "./ChannelSentDiff";
import CopyToResult from "./CopyToResult";

interface IWidget {
  initStep?: number;
  channel?: IChannel;
  type?: ArticleType;
  articleId?: string;
  sentence?: string[];
  stepChange?: Function;
  onClose?: Function;
}
const Widget = ({
  initStep = 0,
  channel,
  type,
  articleId,
  sentence,
  stepChange,
  onClose,
}: IWidget) => {
  const [current, setCurrent] = useState(0);
  const [destChannel, setDestChannel] = useState<IChannel>();
  const [copyPercent, setCopyPercent] = useState<number>();

  const sentences = useAppSelector(sentenceList);

  useEffect(() => {
    setCurrent(initStep);
  }, [initStep]);

  const next = () => {
    setCurrent(current + 1);
  };

  const prev = () => {
    setCurrent(current - 1);
  };
  const steps = [
    {
      title: "选择目标版本",
      key: "channel",
      content: (
        <ChannelPickerTable
          type="editable"
          multiSelect={false}
          onSelect={(e: IChannel) => {
            console.log(e);
            setDestChannel(e);
            next();
          }}
        />
      ),
    },
    {
      title: "文本比对",
      key: "diff",
      content: (
        <div>
          <ChannelSentDiff
            srcChannel={channel}
            destChannel={destChannel}
            sentences={sentences}
            goPrev={() => {
              prev();
            }}
            onSubmit={() => {
              next();
            }}
          />
        </div>
      ),
    },
    {
      title: "完成",
      key: "finish",
      content: (
        <CopyToResult
          onClose={() => {
            if (typeof onClose !== "undefined") {
              onClose();
            }
          }}
          onInit={() => {
            setCurrent(0);
          }}
        />
      ),
    },
  ];
  const items = steps.map((item) => ({ key: item.key, title: item.title }));

  const contentStyle: React.CSSProperties = {
    backgroundColor: "#fafafa",
    borderRadius: 5,
    border: `1px dashed gray`,
    marginTop: 16,
    height: 400,
    overflowY: "scroll",
  };
  return (
    <div>
      <Steps current={current} items={items} percent={copyPercent} />
      <div style={contentStyle}>{steps[current].content}</div>
    </div>
  );
};

export default Widget;
