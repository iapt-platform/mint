import { Button } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";
import { useAppSelector } from "../../../hooks";
import { currentUser as _currentUser } from "../../../reducers/current-user";
import ChannelPicker from "../../channel/ChannelPicker";
import { IChannel } from "../../channel/Channel";
import SentCell from "./SentCell";
import { ISentence } from "../SentEdit";
interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
}
const Widget = ({ book, para, wordStart, wordEnd }: IWidget) => {
  const [channel, setChannel] = useState<IChannel>();
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);
  const [initSent, setInitSent] = useState<ISentence>();
  const user = useAppSelector(_currentUser);
  return channel ? (
    initSent ? (
      <SentCell data={initSent} isPr={false} />
    ) : (
      <></>
    )
  ) : (
    <>
      <Button
        type="dashed"
        style={{ width: 300 }}
        icon={<PlusOutlined />}
        onClick={() => {
          setChannelPickerOpen(true);
        }}
      >
        Add
      </Button>
      <ChannelPicker
        open={channelPickerOpen}
        onClose={() => setChannelPickerOpen(false)}
        onSelect={(channels: IChannel[]) => {
          setChannel(channels[0]);
          setChannelPickerOpen(false);
          if (typeof user === "undefined") {
            return;
          }
          const sentData: ISentence = {
            content: "",
            contentType: "markdown",
            html: "<spen></spen>",
            book: book,
            para: para,
            wordStart: wordStart,
            wordEnd: wordEnd,
            editor: {
              id: user.id,
              nickName: user.nickName,
              userName: user.realName,
            },
            channel: channels[0],
            updateAt: "",
          };
          setInitSent(sentData);
        }}
      />
    </>
  );
};

export default Widget;
