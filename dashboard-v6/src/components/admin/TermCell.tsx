import { Button } from "antd";
import { PlusOutlined, TranslationOutlined } from "@ant-design/icons";
import type { ITerm } from "../../api/Term";
import TermModal from "../term/TermModal";

/** 术语译名：已有术语点开编辑，没有就「新建」。 */
const TermCell = ({
  term,
  word,
  channelId,
  reload,
  createLabel,
}: {
  term?: ITerm;
  word?: string;
  channelId?: string;
  reload: () => void;
  createLabel: string;
}) =>
  term ? (
    <TermModal
      trigger={
        <Button type="link" icon={<TranslationOutlined />}>
          {term.meaning}
        </Button>
      }
      id={term.id}
      channelId={channelId}
      onUpdate={reload}
    />
  ) : channelId && word ? (
    <TermModal
      trigger={
        <Button type="link" icon={<PlusOutlined />}>
          {createLabel}
        </Button>
      }
      word={word}
      channelId={channelId}
      onUpdate={reload}
    />
  ) : null;

export default TermCell;
